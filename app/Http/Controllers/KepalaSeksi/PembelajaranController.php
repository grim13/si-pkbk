<?php

namespace App\Http\Controllers\KepalaSeksi;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\PertemuanPembelajaran;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PembelajaranController extends Controller
{
    public function index(Request $request): Response
    {
        $currentYear = (int) now()->year;
        $tahun = $request->input('tahun', '');
        $jadwalId = $request->input('jadwal_id', '');
        $search = $request->input('search', '');

        $query = PertemuanPembelajaran::with([
            'jadwal.mataPelajaran.jenisDisabilitas:id,nama',
            'instruktur:id,name,email',
            'pekerjaSosial:id,name',
            'galeri.foto',
        ])
            ->withCount([
                'pesertaDetail as total_peserta',
                'pesertaDetail as hadir_count' => fn ($q) => $q->where('presensi', 'hadir'),
                'pesertaDetail as izin_count' => fn ($q) => $q->where('presensi', 'izin'),
                'pesertaDetail as absen_count' => fn ($q) => $q->where('presensi', 'absen'),
                'galeri as galeri_count',
            ])
            ->where('status', 'acc');

        if ($tahun !== '' && $tahun !== null) {
            $query->whereHas('jadwal', fn ($q) => $q->where('tahun', (int) $tahun));
        }

        if ($jadwalId !== '' && $jadwalId !== null) {
            $query->where('jadwal_id', (int) $jadwalId);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->string('search')->toString();
            $query->where(function ($q) use ($searchTerm) {
                $q->where('keterangan', 'like', '%'.$searchTerm.'%')
                    ->orWhere('catatan_pekerja_sosial', 'like', '%'.$searchTerm.'%')
                    ->orWhereHas('instruktur', fn ($iq) => $iq->where('name', 'like', '%'.$searchTerm.'%'))
                    ->orWhereHas('jadwal.mataPelajaran', fn ($mq) => $mq->where('nama', 'like', '%'.$searchTerm.'%'));
            });
        }

        $pertemuanList = $query
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $jadwalList = Jadwal::with(['mataPelajaran.jenisDisabilitas:id,nama', 'instruktur:id,name'])
            ->whereHas('pertemuan', fn ($q) => $q->where('status', 'acc'))
            ->orderBy('tahun', 'desc')
            ->orderBy('hari')
            ->get();

        $dbYears = Jadwal::query()->distinct()->pluck('tahun')->map(fn ($y) => (int) $y)->all();
        $availableYears = collect([$currentYear - 1, $currentYear, $currentYear + 1, ...$dbYears])
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return Inertia::render('KepalaSeksi/Pembelajaran/Index', [
            'pertemuanList' => $pertemuanList,
            'jadwalList' => $jadwalList,
            'availableYears' => $availableYears,
            'filters' => [
                'tahun' => (string) $tahun,
                'jadwal_id' => (string) $jadwalId,
                'search' => (string) $search,
            ],
        ]);
    }

    public function show(PertemuanPembelajaran $pertemuan): Response
    {
        if ($pertemuan->status !== 'acc') {
            abort(403, 'Pertemuan pembelajaran belum disetujui (ACC) oleh Pekerja Sosial.');
        }

        $pertemuan->load([
            'jadwal.mataPelajaran.jenisDisabilitas:id,nama',
            'instruktur:id,name,email',
            'pekerjaSosial:id,name',
            'pesertaDetail.pendaftaranPeserta.jenisDisabilitas:id,nama',
            'pesertaDetail.pendaftaranPeserta.wali.user:id,name',
            'galeri.foto',
            'galeri.creator:id,name',
        ]);

        return Inertia::render('KepalaSeksi/Pembelajaran/Show', [
            'pertemuan' => $pertemuan,
        ]);
    }
}
