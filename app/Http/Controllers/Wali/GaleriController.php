<?php

namespace App\Http\Controllers\Wali;

use App\Http\Controllers\Controller;
use App\Models\Galeri;
use App\Models\Jadwal;
use App\Models\PendaftaranPeserta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GaleriController extends Controller
{
    public function index(Request $request): Response
    {
        $wali = auth()->user()?->wali;
        if (! $wali) {
            abort(403, 'Anda bukan wali.');
        }

        $tahunInput = $request->input('tahun', '');
        $tahun = $tahunInput !== '' && $tahunInput !== null ? (int) $tahunInput : null;

        $query = Galeri::query()
            ->accessibleByWali($wali->id)
            ->with([
                'foto',
                'pertemuan.jadwal.mataPelajaran.jenisDisabilitas:id,nama',
                'pertemuan.instruktur:id,name',
                'pertemuan.pesertaDetail' => function ($q) use ($wali) {
                    $q->where('presensi', 'hadir')
                        ->whereHas('pendaftaranPeserta', function ($pq) use ($wali) {
                            $pq->where('wali_id', $wali->id)
                                ->where('status_pendaftaran', 'diterima');
                        })
                        ->with('pendaftaranPeserta:id,wali_id,nama,no_pendaftaran,jenis_disabilitas_id');
                },
            ])
            ->withCount('foto');

        if ($tahun !== null) {
            $query->whereHas('pertemuan.jadwal', fn ($q) => $q->where('tahun', $tahun));
        }

        if ($request->filled('peserta_id')) {
            $pesertaId = (int) $request->input('peserta_id');
            $query->whereHas('pertemuan.pesertaDetail', function ($q) use ($wali, $pesertaId) {
                $q->where('presensi', 'hadir')
                    ->where('pendaftaran_peserta_id', $pesertaId)
                    ->whereHas('pendaftaranPeserta', function ($pq) use ($wali) {
                        $pq->where('wali_id', $wali->id)
                            ->where('status_pendaftaran', 'diterima');
                    });
            });
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', '%'.$search.'%')
                    ->orWhere('keterangan', 'like', '%'.$search.'%')
                    ->orWhereHas('pertemuan.jadwal.mataPelajaran', fn ($mq) => $mq->where('nama', 'like', '%'.$search.'%'))
                    ->orWhereHas('pertemuan.instruktur', fn ($iq) => $iq->where('name', 'like', '%'.$search.'%'));
            });
        }

        $galeriList = $query
            ->orderByDesc('id')
            ->get();

        $pesertaList = PendaftaranPeserta::with('jenisDisabilitas:id,nama')
            ->where('wali_id', $wali->id)
            ->where('status_pendaftaran', 'diterima')
            ->orderBy('nama')
            ->get(['id', 'wali_id', 'no_pendaftaran', 'nama', 'jenis_disabilitas_id', 'status_pendaftaran']);

        $currentYear = (int) now()->year;
        $dbYears = Jadwal::query()->distinct()->pluck('tahun')->map(fn ($y) => (int) $y)->all();
        $availableYears = collect([$currentYear - 1, $currentYear, $currentYear + 1, ...$dbYears])
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return Inertia::render('Wali/Galeri/Index', [
            'galeriList' => $galeriList,
            'pesertaList' => $pesertaList,
            'availableYears' => $availableYears,
            'filters' => [
                'tahun' => $tahun !== null ? (string) $tahun : '',
                'peserta_id' => $request->input('peserta_id', ''),
                'search' => $request->input('search', ''),
            ],
        ]);
    }

    public function show(Galeri $galeri): Response
    {
        $wali = auth()->user()?->wali;
        if (! $wali) {
            abort(403, 'Anda bukan wali.');
        }

        $isAccessible = Galeri::query()
            ->accessibleByWali($wali->id)
            ->whereKey($galeri->id)
            ->exists();

        if (! $isAccessible) {
            abort(403, 'Anda tidak memiliki akses untuk melihat galeri pembelajaran ini karena peserta tidak terdaftar di kelas tersebut atau tidak hadir.');
        }

        $galeri->load([
            'foto',
            'pertemuan.jadwal.mataPelajaran.jenisDisabilitas:id,nama',
            'pertemuan.instruktur:id,name',
            'pertemuan.pesertaDetail' => function ($q) use ($wali) {
                $q->where('presensi', 'hadir')
                    ->whereHas('pendaftaranPeserta', function ($pq) use ($wali) {
                        $pq->where('wali_id', $wali->id)
                            ->where('status_pendaftaran', 'diterima');
                    })
                    ->with('pendaftaranPeserta:id,wali_id,nama,no_pendaftaran,jenis_disabilitas_id');
            },
        ]);

        return Inertia::render('Wali/Galeri/Show', [
            'galeri' => $galeri,
        ]);
    }
}
