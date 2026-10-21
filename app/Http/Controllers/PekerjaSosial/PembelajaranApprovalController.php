<?php

namespace App\Http\Controllers\PekerjaSosial;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\PertemuanPembelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PembelajaranApprovalController extends Controller
{
    public function index(Request $request): Response
    {
        $currentYear = (int) now()->year;
        $tahun = (int) $request->input('tahun', $currentYear);

        $query = PertemuanPembelajaran::with([
            'jadwal.mataPelajaran.jenisDisabilitas:id,nama',
            'instruktur:id,name,email',
            'pekerjaSosial:id,name',
        ])
            ->withCount([
                'pesertaDetail as total_peserta',
                'pesertaDetail as hadir_count' => fn ($q) => $q->where('presensi', 'hadir'),
                'pesertaDetail as izin_count' => fn ($q) => $q->where('presensi', 'izin'),
                'pesertaDetail as absen_count' => fn ($q) => $q->where('presensi', 'absen'),
            ])
            ->whereIn('status', ['pengajuan', 'perbaikan', 'acc'])
            ->whereHas('jadwal', fn ($q) => $q->where('tahun', $tahun));

        if ($request->filled('status') && in_array($request->status, ['pengajuan', 'perbaikan', 'acc'], true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'like', '%'.$search.'%')
                    ->orWhereHas('instruktur', fn ($iq) => $iq->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('jadwal.mataPelajaran', fn ($mq) => $mq->where('nama', 'like', '%'.$search.'%'));
            });
        }

        $pertemuanList = $query
            ->orderByRaw("CASE status WHEN 'pengajuan' THEN 0 WHEN 'perbaikan' THEN 1 WHEN 'acc' THEN 2 ELSE 3 END")
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $dbYears = Jadwal::query()->distinct()->pluck('tahun')->map(fn ($y) => (int) $y)->all();
        $availableYears = collect([$currentYear - 1, $currentYear, $currentYear + 1, $tahun, ...$dbYears])
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return Inertia::render('PekerjaSosial/Pembelajaran/Index', [
            'pertemuanList' => $pertemuanList,
            'availableYears' => $availableYears,
            'filters' => [
                'tahun' => $tahun,
                'status' => $request->input('status', ''),
                'search' => $request->input('search', ''),
            ],
        ]);
    }

    public function show(PertemuanPembelajaran $pertemuan): Response
    {
        $pertemuan->load([
            'jadwal.mataPelajaran.jenisDisabilitas:id,nama',
            'instruktur:id,name,email',
            'pekerjaSosial:id,name',
            'pesertaDetail.pendaftaranPeserta.jenisDisabilitas:id,nama',
        ]);

        return Inertia::render('PekerjaSosial/Pembelajaran/Show', [
            'pertemuan' => $pertemuan,
        ]);
    }

    public function update(Request $request, PertemuanPembelajaran $pertemuan): RedirectResponse
    {
        $statusInput = $request->input('status', $request->input('keputusan'));
        $keteranganInput = $request->input('keterangan', $request->input('catatan_pekerja_sosial'));

        $request->merge([
            'status' => $statusInput,
            'keterangan' => $keteranganInput,
        ]);

        $validated = $request->validate([
            'status' => ['required', 'in:perbaikan,acc'],
            'keterangan' => ['nullable', 'string'],
        ], [
            'status.required' => 'Keputusan approval (Perbaikan atau ACC) wajib dipilih.',
            'status.in' => 'Status keputusan harus perbaikan atau acc.',
        ]);

        if ($pertemuan->status === 'draft') {
            return back()
                ->withErrors(['status' => 'Pertemuan yang masih berstatus draft belum dapat diverifikasi.'])
                ->with('error', 'Pertemuan yang masih berstatus draft belum dapat diverifikasi.');
        }

        $note = isset($validated['keterangan']) && trim((string) $validated['keterangan']) !== ''
            ? trim((string) $validated['keterangan'])
            : null;

        $updateData = [
            'status' => $validated['status'],
            'catatan_pekerja_sosial' => $note,
            'pekerja_sosial_id' => auth()->id(),
            'approved_at' => $validated['status'] === 'acc' ? now() : null,
        ];

        if ($note !== null) {
            $updateData['keterangan'] = $note;
        }

        $pertemuan->update($updateData);

        $label = $validated['status'] === 'acc' ? 'DISETUJUI (ACC)' : 'DIMINTA PERBAIKAN';

        return redirect()
            ->route('pekerja-sosial.pembelajaran.index', ['tahun' => $pertemuan->jadwal?->tahun ?? now()->year])
            ->with('success', "Pertemuan pembelajaran berhasil {$label}.");
    }
}
