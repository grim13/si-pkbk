<?php

namespace App\Http\Controllers\PekerjaSosial;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranPeserta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EvaluasiKemandirianController extends Controller
{
    /**
     * Status pendaftaran klien yang tampil pada menu evaluasi kemandirian.
     *
     * @var list<string>
     */
    private const STATUS_LIST = [
        'diterima',
        'pengajuan_lepas_mandiri',
        'lepas_mandiri',
    ];

    /**
     * Tampilkan daftar seluruh klien yang berstatus diterima / dalam proses evaluasi lepas mandiri
     * beserta lama pembinaan dan jumlah kelas yang sudah dihadiri.
     */
    public function index(): Response
    {
        $peserta = PendaftaranPeserta::with([
            'wali.user:id,name',
            'jenisDisabilitas:id,nama',
        ])
            ->withCount([
                'pertemuanPembelajaranPeserta as hadir_count' => fn ($q) => $q
                    ->where('presensi', 'hadir')
                    ->whereHas('pertemuan', fn ($pq) => $pq->where('status', 'acc')),
                'pertemuanPembelajaranPeserta as izin_count' => fn ($q) => $q
                    ->where('presensi', 'izin')
                    ->whereHas('pertemuan', fn ($pq) => $pq->where('status', 'acc')),
                'pertemuanPembelajaranPeserta as absen_count' => fn ($q) => $q
                    ->where('presensi', 'absen')
                    ->whereHas('pertemuan', fn ($pq) => $pq->where('status', 'acc')),
                'pertemuanPembelajaranPeserta as total_pertemuan_count' => fn ($q) => $q
                    ->whereHas('pertemuan', fn ($pq) => $pq->where('status', 'acc')),
            ])
            ->whereIn('status_pendaftaran', self::STATUS_LIST)
            ->orderByRaw("CASE status_pendaftaran WHEN 'diterima' THEN 0 WHEN 'pengajuan_lepas_mandiri' THEN 1 WHEN 'lepas_mandiri' THEN 2 ELSE 3 END")
            ->orderBy('tanggal_mulai_diterima', 'asc')
            ->orderBy('nama', 'asc')
            ->get()
            ->map(function (PendaftaranPeserta $item): array {
                $lamaPembinaan = $item->calculateLamaPembinaan();

                return [
                    ...$item->toArray(),
                    'lama_pembinaan' => $lamaPembinaan['label'],
                    'lama_pembinaan_hari' => $lamaPembinaan['total_days'],
                ];
            })
            ->values()
            ->all();

        return Inertia::render('PekerjaSosial/Evaluasi/Index', [
            'peserta' => $peserta,
        ]);
    }

    /**
     * Tampilkan rekap rata-rata nilai pembelajaran setiap mata pelajaran, komentar terakhir,
     * serta riwayat detail seluruh pertemuan dan komentar instruktur untuk klien terkait.
     */
    public function show(PendaftaranPeserta $pendaftaran): Response|RedirectResponse
    {
        if (! in_array($pendaftaran->status_pendaftaran, self::STATUS_LIST, true)) {
            return redirect()
                ->route('pekerja-sosial.evaluasi.index')
                ->with('error', 'Hanya klien dengan status diterima yang dapat dievaluasi.');
        }

        $pendaftaran->load([
            'wali.user:id,name,email',
            'jenisDisabilitas:id,nama',
            'asesmen.pekerja:id,name',
        ]);

        $ringkasan = $pendaftaran->buildRingkasanPembelajaran();

        return Inertia::render('PekerjaSosial/Evaluasi/Show', [
            'pendaftaran' => [
                ...$pendaftaran->toArray(),
                'lama_pembinaan' => $ringkasan['summary']['lama_pembinaan'],
                'lama_pembinaan_hari' => $ringkasan['summary']['lama_pembinaan_hari'],
            ],
            'mataPelajaranList' => $ringkasan['mata_pelajaran_list'],
            'summary' => $ringkasan['summary'],
        ]);
    }

    /**
     * Ajukan klien untuk evaluasi lepas kemandirian ke Kepala Seksi beserta keterangan.
     */
    public function store(Request $request, PendaftaranPeserta $pendaftaran): RedirectResponse
    {
        $keteranganInput = $request->input(
            'keterangan',
            $request->input('keterangan_pengajuan_lepas_mandiri', $request->input('catatan'))
        );

        $request->merge([
            'keterangan' => $keteranganInput,
        ]);

        $validated = $request->validate([
            'keterangan' => ['required', 'string'],
        ], [
            'keterangan.required' => 'Keterangan pengajuan lepas mandiri wajib diisi.',
        ]);

        if (! in_array($pendaftaran->status_pendaftaran, ['diterima', 'pengajuan_lepas_mandiri'], true)) {
            return back()
                ->withErrors(['status' => 'Hanya klien dengan status diterima yang dapat diajukan lepas mandiri.'])
                ->with('error', 'Hanya klien dengan status diterima yang dapat diajukan lepas mandiri.');
        }

        $pendaftaran->update([
            'status_pendaftaran' => 'pengajuan_lepas_mandiri',
            'keterangan_pengajuan_lepas_mandiri' => trim((string) $validated['keterangan']),
            'tanggal_pengajuan_lepas_mandiri' => now(),
        ]);

        return redirect()
            ->route('pekerja-sosial.evaluasi.show', $pendaftaran->id)
            ->with('success', "Pengajuan lepas mandiri untuk klien {$pendaftaran->nama} berhasil dikirim ke Kepala Seksi.");
    }
}
