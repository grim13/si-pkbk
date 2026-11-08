<?php

namespace App\Http\Controllers\KepalaSeksi;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranPeserta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LepasMandiriController extends Controller
{
    /**
     * Status pendaftaran klien yang tampil di menu evaluasi lepas mandiri Kepala Seksi.
     *
     * @var list<string>
     */
    private const STATUS_LIST = [
        'pengajuan_lepas_mandiri',
        'lepas_mandiri',
        'diterima',
    ];

    /**
     * Tampilkan daftar pengajuan dan riwayat evaluasi lepas mandiri klien untuk Kepala Seksi.
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
            ->orderByRaw("CASE status_pendaftaran WHEN 'pengajuan_lepas_mandiri' THEN 0 WHEN 'lepas_mandiri' THEN 1 WHEN 'diterima' THEN 2 ELSE 3 END")
            ->orderBy('tanggal_pengajuan_lepas_mandiri', 'desc')
            ->orderBy('updated_at', 'desc')
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

        return Inertia::render('KepalaSeksi/LepasMandiri/Index', [
            'peserta' => $peserta,
        ]);
    }

    /**
     * Tampilkan detail evaluasi pembelajaran klien, keterangan pengajuan Pekerja Sosial,
     * serta form persetujuan/penolakan lepas mandiri oleh Kepala Seksi.
     */
    public function show(PendaftaranPeserta $pendaftaran): Response|RedirectResponse
    {
        if (! in_array($pendaftaran->status_pendaftaran, self::STATUS_LIST, true)) {
            return redirect()
                ->route('kepala-seksi.lepas-mandiri.index')
                ->with('error', 'Klien belum masuk dalam tahap pembinaan atau pengajuan lepas mandiri.');
        }

        $pendaftaran->load([
            'wali.user:id,name,email',
            'jenisDisabilitas:id,nama',
            'asesmen.pekerja:id,name',
        ]);

        $ringkasan = $pendaftaran->buildRingkasanPembelajaran();

        return Inertia::render('KepalaSeksi/LepasMandiri/Show', [
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
     * Terima (ubah status ke lepas_mandiri) atau Tolak (kembalikan ke status diterima)
     * pengajuan lepas mandiri klien disertai keterangan keputusan.
     */
    public function update(Request $request, PendaftaranPeserta $pendaftaran): RedirectResponse
    {
        $rawKeputusan = $request->input('keputusan', $request->input('status'));
        $normalizedKeputusan = match ($rawKeputusan) {
            'terima', 'lepas_mandiri' => 'lepas_mandiri',
            'tolak', 'diterima' => 'diterima',
            default => $rawKeputusan,
        };

        $rawKeterangan = $request->input(
            'keterangan',
            $request->input('keterangan_keputusan_lepas_mandiri', $request->input('catatan'))
        );

        $request->merge([
            'keputusan' => $normalizedKeputusan,
            'keterangan' => $rawKeterangan,
        ]);

        $validated = $request->validate([
            'keputusan' => ['required', 'in:lepas_mandiri,diterima'],
            'keterangan' => ['required', 'string'],
        ], [
            'keputusan.required' => 'Keputusan persetujuan (Terima atau Tolak) wajib dipilih.',
            'keputusan.in' => 'Keputusan harus berupa lepas_mandiri (terima) atau diterima (tolak).',
            'keterangan.required' => 'Keterangan keputusan wajib diisi.',
        ]);

        if (! in_array($pendaftaran->status_pendaftaran, self::STATUS_LIST, true)) {
            return back()
                ->withErrors(['keputusan' => 'Klien belum berada pada tahap yang dapat diputuskan lepas mandiri.'])
                ->with('error', 'Klien belum berada pada tahap yang dapat diputuskan lepas mandiri.');
        }

        $isApproved = $validated['keputusan'] === 'lepas_mandiri';

        $pendaftaran->update([
            'status_pendaftaran' => $isApproved ? 'lepas_mandiri' : 'diterima',
            'keterangan_keputusan_lepas_mandiri' => trim((string) $validated['keterangan']),
            'tanggal_lepas_mandiri' => $isApproved ? now() : null,
        ]);

        $label = $isApproved
            ? 'dinyatakan LEPAS MANDIRI'
            : 'ditolak pengajuan lepas mandirinya dan dikembalikan ke status DITERIMA';

        return redirect()
            ->route('kepala-seksi.lepas-mandiri.index')
            ->with('success', "Klien {$pendaftaran->nama} {$label}.");
    }
}
