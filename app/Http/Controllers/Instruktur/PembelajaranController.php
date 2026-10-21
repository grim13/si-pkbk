<?php

namespace App\Http\Controllers\Instruktur;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\PendaftaranPeserta;
use App\Models\PertemuanPembelajaran;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PembelajaranController extends Controller
{
    private const PRESENSI_OPTIONS = ['hadir', 'izin', 'absen'];

    private const NILAI_OPTIONS = ['sangat_baik', 'baik', 'cukup', 'kurang'];

    public function index(Request $request): Response
    {
        $currentYear = (int) now()->year;
        $tahun = (int) $request->input('tahun', $currentYear);
        $instrukturId = (int) auth()->id();

        $jadwalList = Jadwal::with([
            'mataPelajaran.jenisDisabilitas:id,nama',
        ])
            ->withCount('pertemuan')
            ->where('instruktur_id', $instrukturId)
            ->where('tahun', $tahun)
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('jam_mulai')
            ->get();

        $pertemuanQuery = PertemuanPembelajaran::with([
            'jadwal.mataPelajaran.jenisDisabilitas:id,nama',
            'pekerjaSosial:id,name',
        ])
            ->withCount([
                'pesertaDetail as total_peserta',
                'pesertaDetail as hadir_count' => fn ($q) => $q->where('presensi', 'hadir'),
                'pesertaDetail as izin_count' => fn ($q) => $q->where('presensi', 'izin'),
                'pesertaDetail as absen_count' => fn ($q) => $q->where('presensi', 'absen'),
            ])
            ->where('instruktur_id', $instrukturId)
            ->whereHas('jadwal', fn ($q) => $q->where('tahun', $tahun));

        if ($request->filled('jadwal_id')) {
            $pertemuanQuery->where('jadwal_id', (int) $request->input('jadwal_id'));
        }

        if ($request->filled('status') && in_array($request->status, ['draft', 'pengajuan', 'perbaikan', 'acc'], true)) {
            $pertemuanQuery->where('status', $request->status);
        }

        $pertemuanList = $pertemuanQuery
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $dbYears = Jadwal::query()
            ->where('instruktur_id', $instrukturId)
            ->distinct()
            ->pluck('tahun')
            ->map(fn ($y) => (int) $y)
            ->all();

        $availableYears = collect([$currentYear - 1, $currentYear, $currentYear + 1, $tahun, ...$dbYears])
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return Inertia::render('Instruktur/Pembelajaran/Index', [
            'jadwalList' => $jadwalList,
            'pertemuanList' => $pertemuanList,
            'availableYears' => $availableYears,
            'filters' => [
                'tahun' => $tahun,
                'jadwal_id' => $request->input('jadwal_id', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    public function show(Request $request, Jadwal $jadwal): Response|RedirectResponse
    {
        if ((int) $jadwal->instruktur_id !== (int) auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke jadwal ini.');
        }

        $jadwal->load([
            'mataPelajaran.jenisDisabilitas:id,nama',
            'instruktur:id,name',
        ]);

        $defaultDate = (int) now()->year === (int) $jadwal->tahun
            ? now()->format('Y-m-d')
            : sprintf('%04d-01-01', $jadwal->tahun);

        $tanggalInput = $request->input('tanggal', $defaultDate);

        try {
            $parsedDate = Carbon::parse($tanggalInput);
        } catch (\Throwable) {
            $parsedDate = Carbon::parse($defaultDate);
        }

        if ((int) $parsedDate->year !== (int) $jadwal->tahun) {
            $parsedDate = Carbon::createFromDate((int) $jadwal->tahun, $parsedDate->month, min($parsedDate->day, 28));
        }

        $tanggal = $parsedDate->format('Y-m-d');

        $pertemuan = PertemuanPembelajaran::with([
            'pesertaDetail',
            'pekerjaSosial:id,name',
        ])
            ->where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        $eligiblePeserta = $this->getEligiblePeserta($jadwal, $tanggal);

        // Jika pertemuan sudah ada, sertakan juga peserta yang sudah terekam pada pertemuan tersebut
        if ($pertemuan && $pertemuan->pesertaDetail->isNotEmpty()) {
            $recordedIds = $pertemuan->pesertaDetail->pluck('pendaftaran_peserta_id')->all();
            $missingIds = array_diff($recordedIds, $eligiblePeserta->pluck('id')->all());
            if (! empty($missingIds)) {
                $extraPeserta = PendaftaranPeserta::with(['jenisDisabilitas:id,nama'])
                    ->whereIn('id', $missingIds)
                    ->get();
                $eligiblePeserta = $eligiblePeserta->concat($extraPeserta)->sortBy('nama')->values();
            }
        }

        return Inertia::render('Instruktur/Pembelajaran/Form', [
            'jadwal' => $jadwal,
            'tanggal' => $tanggal,
            'pertemuan' => $pertemuan,
            'pesertaList' => $eligiblePeserta,
            'presensiOptions' => self::PRESENSI_OPTIONS,
            'nilaiOptions' => self::NILAI_OPTIONS,
        ]);
    }

    public function store(Request $request, Jadwal $jadwal): RedirectResponse
    {
        if ((int) $jadwal->instruktur_id !== (int) auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke jadwal ini.');
        }

        $jadwal->load('mataPelajaran.jenisDisabilitas:id,nama');

        $targetStatus = $request->input('status', $request->input('action', 'draft'));
        $request->merge(['status' => $targetStatus]);

        $request->validate([
            'tanggal' => [
                'required',
                'date_format:Y-m-d',
                function (string $attribute, mixed $value, \Closure $fail) use ($jadwal): void {
                    if ((int) Carbon::parse($value)->year !== (int) $jadwal->tahun) {
                        $fail("Tanggal pembelajaran harus berada pada tahun jadwal ({$jadwal->tahun}).");
                    }
                },
            ],
            'status' => ['required', 'in:draft,pengajuan'],
            'keterangan' => ['nullable', 'string'],
        ], [
            'tanggal.required' => 'Tanggal pembelajaran wajib diisi.',
            'tanggal.date_format' => 'Format tanggal pembelajaran tidak valid.',
            'status.in' => 'Status pertemuan tidak valid.',
        ]);

        $tanggal = $request->string('tanggal')->toString();

        $existingPertemuan = PertemuanPembelajaran::query()
            ->where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        if ($existingPertemuan && $existingPertemuan->isLocked()) {
            return back()
                ->withErrors(['status' => 'Pada status pengajuan atau ACC, instruktur tidak dapat mengubah atau memperbaiki presensi dan nilai.'])
                ->with('error', 'Pada status pengajuan atau ACC, instruktur tidak dapat mengubah atau memperbaiki presensi dan nilai.');
        }

        $eligiblePeserta = $this->getEligiblePeserta($jadwal, $tanggal);
        $eligibleIds = $eligiblePeserta->pluck('id')->map(fn ($id) => (int) $id)->all();

        $request->validate([
            'peserta' => ['required', 'array', 'min:1'],
            'peserta.*.pendaftaran_peserta_id' => [
                'required',
                'integer',
                Rule::in($eligibleIds),
            ],
            'peserta.*.presensi' => ['required', 'string', Rule::in(self::PRESENSI_OPTIONS)],
            'peserta.*.nilai' => ['required', 'string', Rule::in(self::NILAI_OPTIONS)],
            'peserta.*.keterangan' => ['nullable', 'string'],
        ], [
            'peserta.required' => 'Data presensi dan penilaian peserta wajib diisi.',
            'peserta.min' => 'Minimal harus ada 1 peserta yang memenuhi syarat pada pertemuan ini.',
            'peserta.*.pendaftaran_peserta_id.in' => 'Peserta tidak sesuai dengan kualifikasi jadwal atau tanggal mulai diterima.',
            'peserta.*.presensi.required' => 'Presensi peserta wajib dipilih (Hadir, Izin, atau Absen).',
            'peserta.*.presensi.in' => 'Pilihan presensi tidak valid.',
            'peserta.*.nilai.required' => 'Nilai kualitatif peserta wajib dipilih.',
            'peserta.*.nilai.in' => 'Pilihan nilai kualitatif tidak valid.',
        ]);

        // Validasi keterangan wajib diisi jika nilai === 'kurang', dan kelengkapan seluruh peserta
        $pesertaInput = $request->input('peserta', []);
        $customErrors = [];

        foreach ($pesertaInput as $index => $row) {
            if (($row['nilai'] ?? null) === 'kurang' && trim((string) ($row['keterangan'] ?? '')) === '') {
                $customErrors["peserta.{$index}.keterangan"] = 'Keterangan wajib diisi apabila nilai kualitatif peserta adalah Kurang.';
            }
        }

        if ($targetStatus === 'pengajuan') {
            $submittedIds = collect($pesertaInput)
                ->pluck('pendaftaran_peserta_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->all();

            if (count($submittedIds) < count($eligibleIds)) {
                $customErrors['peserta'] = 'Seluruh peserta pada jadwal ini harus diisi presensi dan nilainya sebelum melakukan pengajuan.';
            }
        }

        if (! empty($customErrors)) {
            return back()->withErrors($customErrors)->withInput();
        }

        DB::transaction(function () use ($jadwal, $tanggal, $targetStatus, $request, $pesertaInput, $existingPertemuan): void {
            $newStatus = $targetStatus === 'pengajuan'
                ? 'pengajuan'
                : ($existingPertemuan && $existingPertemuan->status === 'perbaikan' ? 'perbaikan' : 'draft');

            $pertemuan = PertemuanPembelajaran::updateOrCreate(
                [
                    'jadwal_id' => $jadwal->id,
                    'tanggal' => $tanggal,
                ],
                [
                    'instruktur_id' => auth()->id(),
                    'status' => $newStatus,
                    'keterangan' => $request->input('keterangan'),
                ]
            );

            $submittedPesertaIds = [];

            foreach ($pesertaInput as $row) {
                $pesertaId = (int) $row['pendaftaran_peserta_id'];
                $submittedPesertaIds[] = $pesertaId;

                $pertemuan->pesertaDetail()->updateOrCreate(
                    ['pendaftaran_peserta_id' => $pesertaId],
                    [
                        'presensi' => $row['presensi'],
                        'nilai' => $row['nilai'],
                        'keterangan' => isset($row['keterangan']) && trim((string) $row['keterangan']) !== ''
                            ? trim((string) $row['keterangan'])
                            : null,
                    ]
                );
            }

            $pertemuan->pesertaDetail()
                ->whereNotIn('pendaftaran_peserta_id', $submittedPesertaIds)
                ->delete();
        });

        $message = $targetStatus === 'pengajuan'
            ? 'Pertemuan pembelajaran berhasil diajukan ke Pekerja Sosial.'
            : 'Data presensi dan penilaian berhasil disimpan.';

        return redirect()
            ->route('instruktur.pembelajaran.show', ['jadwal' => $jadwal->id, 'tanggal' => $tanggal])
            ->with('success', $message);
    }

    public function ajukan(PertemuanPembelajaran $pertemuan): RedirectResponse
    {
        if ((int) $pertemuan->instruktur_id !== (int) auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke pertemuan ini.');
        }

        if ($pertemuan->isLocked()) {
            return back()
                ->withErrors(['status' => 'Pertemuan sudah dalam status pengajuan atau ACC.'])
                ->with('error', 'Pertemuan sudah dalam status pengajuan atau ACC.');
        }

        $pertemuan->load(['jadwal.mataPelajaran.jenisDisabilitas', 'pesertaDetail']);
        $tanggal = $pertemuan->tanggal->format('Y-m-d');
        $eligiblePeserta = $this->getEligiblePeserta($pertemuan->jadwal, $tanggal);

        if ($eligiblePeserta->isEmpty() || $pertemuan->pesertaDetail->count() < $eligiblePeserta->count()) {
            return back()
                ->withErrors(['peserta' => 'Lengkapi presensi dan penilaian seluruh peserta sebelum melakukan pengajuan.'])
                ->with('error', 'Lengkapi presensi dan penilaian seluruh peserta sebelum melakukan pengajuan.');
        }

        foreach ($pertemuan->pesertaDetail as $detail) {
            if (! $detail->presensi || ! $detail->nilai) {
                return back()
                    ->withErrors(['peserta' => 'Semua peserta wajib memiliki presensi dan nilai kualitatif.'])
                    ->with('error', 'Semua peserta wajib memiliki presensi dan nilai kualitatif.');
            }

            if ($detail->nilai === 'kurang' && trim((string) $detail->keterangan) === '') {
                return back()
                    ->withErrors(['peserta' => 'Peserta dengan nilai Kurang wajib diisi keterangannya.'])
                    ->with('error', 'Peserta dengan nilai Kurang wajib diisi keterangannya.');
            }
        }

        $pertemuan->update([
            'status' => 'pengajuan',
        ]);

        return back()->with('success', 'Pertemuan pembelajaran berhasil diajukan ke Pekerja Sosial.');
    }

    /**
     * Ambil daftar peserta yang berstatus diterima, sesuai jenis disabilitas mata pelajaran,
     * dan tanggal pembelajaran lebih besar dari tanggal mulai diterima.
     *
     * @return Collection<int, PendaftaranPeserta>
     */
    private function getEligiblePeserta(Jadwal $jadwal, string $tanggal): Collection
    {
        $jenisDisabilitasIds = $jadwal->mataPelajaran
            ? $jadwal->mataPelajaran->jenisDisabilitas->pluck('id')->all()
            : [];

        if (empty($jenisDisabilitasIds)) {
            return new Collection;
        }

        return PendaftaranPeserta::with(['jenisDisabilitas:id,nama'])
            ->where('status_pendaftaran', 'diterima')
            ->whereNotNull('tanggal_mulai_diterima')
            ->whereDate('tanggal_mulai_diterima', '<=', $tanggal)
            ->whereIn('jenis_disabilitas_id', $jenisDisabilitasIds)
            ->orderBy('nama')
            ->get([
                'id',
                'no_pendaftaran',
                'nik',
                'nama',
                'jenis_disabilitas_id',
                'status_pendaftaran',
                'tanggal_mulai_diterima',
            ]);
    }
}
