<?php

namespace App\Http\Controllers;

use App\Models\Galeri;
use App\Models\GaleriFoto;
use App\Models\Jadwal;
use App\Models\MataPelajaran;
use App\Models\PendaftaranPeserta;
use App\Models\PertemuanPembelajaran;
use App\Models\PertemuanPembelajaranPeserta;
use App\Models\User;
use App\Models\Wali;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $role = $this->resolvePrimaryRole($user);

        return Inertia::render('Dashboard', [
            'role' => $role,
            'dashboardData' => match ($role) {
                'administrator' => $this->getAdminData(),
                'wali' => $this->getWaliData($user),
                'pekerja_sosial' => $this->getPekerjaSosialData(),
                'instruktur' => $this->getInstrukturData($user),
                'kepala_seksi' => $this->getKepalaSeksiData(),
                default => $this->getGeneralData(),
            },
        ]);
    }

    private function resolvePrimaryRole(User $user): string
    {
        if ($user->hasRole('administrator') || $user->can('access.manage') || $user->can('pendaftaran.manage')) {
            return 'administrator';
        }

        if ($user->hasRole('kepala_seksi') || $user->can('pendaftaran.kelulusan')) {
            return 'kepala_seksi';
        }

        if ($user->hasRole('pekerja_sosial') || $user->can('asesmen.do') || $user->can('pembelajaran.approve')) {
            return 'pekerja_sosial';
        }

        if ($user->hasRole('instruktur') || $user->can('pembelajaran.do')) {
            return 'instruktur';
        }

        if ($user->hasRole('wali')) {
            return 'wali';
        }

        return 'umum';
    }

    /**
     * @return array<string, mixed>
     */
    private function getAdminData(): array
    {
        $pendaftaranQuery = PendaftaranPeserta::where('status_pendaftaran', '!=', 'draft');

        $stats = [
            'total_users' => User::count(),
            'total_wali' => Wali::count(),
            'total_pendaftaran' => (clone $pendaftaranQuery)->count(),
            'menunggu_verifikasi' => PendaftaranPeserta::where('status_pendaftaran', 'menunggu_verifikasi')->count(),
            'perbaikan_berkas' => PendaftaranPeserta::where('status_pendaftaran', 'perbaikan_berkas')->count(),
            'tahap_asesmen' => PendaftaranPeserta::whereIn('status_pendaftaran', ['asesmen', 'selesai_asesmen'])->count(),
            'peserta_diterima' => PendaftaranPeserta::where('status_pendaftaran', 'diterima')->count(),
            'total_mata_pelajaran' => MataPelajaran::count(),
            'total_jadwal' => Jadwal::count(),
            'total_pertemuan_acc' => PertemuanPembelajaran::where('status', 'acc')->count(),
            'total_galeri' => Galeri::count(),
            'total_foto' => GaleriFoto::count(),
        ];

        $recentPendaftaran = PendaftaranPeserta::with(['wali.user:id,name', 'jenisDisabilitas:id,nama'])
            ->where('status_pendaftaran', '!=', 'draft')
            ->orderByRaw("CASE status_pendaftaran WHEN 'menunggu_verifikasi' THEN 0 WHEN 'perbaikan_berkas' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        $recentGaleri = Galeri::with([
            'pertemuan.jadwal.mataPelajaran:id,nama',
            'pertemuan.instruktur:id,name',
            'foto',
        ])
            ->withCount('foto')
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        return [
            'stats' => $stats,
            'recent_pendaftaran' => $recentPendaftaran,
            'recent_galeri' => $recentGaleri,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getWaliData(User $user): array
    {
        $wali = $user->wali;

        if (! $wali) {
            return [
                'stats' => [
                    'total_peserta' => 0,
                    'peserta_diterima' => 0,
                    'total_hadir' => 0,
                    'total_galeri' => 0,
                ],
                'peserta_list' => [],
                'riwayat_pembelajaran' => [],
                'recent_galeri' => [],
            ];
        }

        $pesertaList = PendaftaranPeserta::with(['jenisDisabilitas:id,nama', 'berkas'])
            ->withCount([
                'pertemuanPembelajaranPeserta as hadir_count' => fn ($q) => $q->where('presensi', 'hadir')
                    ->whereHas('pertemuan', fn ($pq) => $pq->where('status', 'acc')),
                'pertemuanPembelajaranPeserta as izin_count' => fn ($q) => $q->where('presensi', 'izin')
                    ->whereHas('pertemuan', fn ($pq) => $pq->where('status', 'acc')),
                'pertemuanPembelajaranPeserta as absen_count' => fn ($q) => $q->where('presensi', 'absen')
                    ->whereHas('pertemuan', fn ($pq) => $pq->where('status', 'acc')),
            ])
            ->where('wali_id', $wali->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $pesertaIds = $pesertaList->pluck('id')->all();

        $totalHadir = empty($pesertaIds)
            ? 0
            : PertemuanPembelajaranPeserta::whereIn('pendaftaran_peserta_id', $pesertaIds)
                ->where('presensi', 'hadir')
                ->whereHas('pertemuan', fn ($q) => $q->where('status', 'acc'))
                ->count();

        $riwayatPembelajaran = empty($pesertaIds)
            ? []
            : PertemuanPembelajaranPeserta::with([
                'pendaftaranPeserta:id,nama,no_pendaftaran',
                'pertemuan.jadwal.mataPelajaran:id,nama',
                'pertemuan.instruktur:id,name',
            ])
                ->whereIn('pendaftaran_peserta_id', $pesertaIds)
                ->whereHas('pertemuan', fn ($q) => $q->where('status', 'acc'))
                ->orderByDesc('id')
                ->limit(6)
                ->get();

        $galeriQuery = Galeri::accessibleByWali($wali->id);
        $totalGaleri = (clone $galeriQuery)->count();

        $recentGaleri = $galeriQuery
            ->with([
                'pertemuan.jadwal.mataPelajaran:id,nama',
                'pertemuan.instruktur:id,name',
                'foto',
            ])
            ->withCount('foto')
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        return [
            'stats' => [
                'total_peserta' => $pesertaList->count(),
                'peserta_diterima' => $pesertaList->where('status_pendaftaran', 'diterima')->count(),
                'total_hadir' => $totalHadir,
                'total_galeri' => $totalGaleri,
            ],
            'peserta_list' => $pesertaList,
            'riwayat_pembelajaran' => $riwayatPembelajaran,
            'recent_galeri' => $recentGaleri,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getPekerjaSosialData(): array
    {
        $stats = [
            'menunggu_asesmen' => PendaftaranPeserta::where('status_pendaftaran', 'asesmen')->count(),
            'selesai_asesmen' => PendaftaranPeserta::whereIn('status_pendaftaran', ['selesai_asesmen', 'diterima', 'ditolak'])->count(),
            'pertemuan_menunggu_acc' => PertemuanPembelajaran::where('status', 'pengajuan')->count(),
            'pertemuan_perbaikan' => PertemuanPembelajaran::where('status', 'perbaikan')->count(),
            'pertemuan_acc' => PertemuanPembelajaran::where('status', 'acc')->count(),
        ];

        $klienMenungguAsesmen = PendaftaranPeserta::with(['wali.user:id,name', 'jenisDisabilitas:id,nama'])
            ->whereIn('status_pendaftaran', ['asesmen', 'selesai_asesmen'])
            ->orderByRaw("CASE status_pendaftaran WHEN 'asesmen' THEN 0 ELSE 1 END")
            ->orderBy('jadwal_asesmen', 'asc')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        $pertemuanMenungguApproval = PertemuanPembelajaran::with([
            'jadwal.mataPelajaran.jenisDisabilitas:id,nama',
            'instruktur:id,name',
        ])
            ->withCount([
                'pesertaDetail as total_peserta',
                'pesertaDetail as hadir_count' => fn ($q) => $q->where('presensi', 'hadir'),
            ])
            ->whereIn('status', ['pengajuan', 'perbaikan', 'acc'])
            ->orderByRaw("CASE status WHEN 'pengajuan' THEN 0 WHEN 'perbaikan' THEN 1 ELSE 2 END")
            ->orderBy('tanggal', 'desc')
            ->limit(6)
            ->get();

        return [
            'stats' => $stats,
            'klien_asesmen' => $klienMenungguAsesmen,
            'pertemuan_approval' => $pertemuanMenungguApproval,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getInstrukturData(User $user): array
    {
        $instrukturId = (int) $user->id;

        $jadwalList = Jadwal::with(['mataPelajaran.jenisDisabilitas:id,nama'])
            ->withCount('pertemuan')
            ->where('instruktur_id', $instrukturId)
            ->orderBy('tahun', 'desc')
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('jam_mulai')
            ->get();

        $pertemuanBase = PertemuanPembelajaran::where('instruktur_id', $instrukturId);

        $stats = [
            'total_jadwal' => $jadwalList->count(),
            'total_pertemuan' => (clone $pertemuanBase)->count(),
            'perlu_tindakan' => (clone $pertemuanBase)->whereIn('status', ['draft', 'perbaikan'])->count(),
            'menunggu_acc' => (clone $pertemuanBase)->where('status', 'pengajuan')->count(),
            'disetujui_acc' => (clone $pertemuanBase)->where('status', 'acc')->count(),
        ];

        $recentPertemuan = PertemuanPembelajaran::with([
            'jadwal.mataPelajaran.jenisDisabilitas:id,nama',
            'pekerjaSosial:id,name',
        ])
            ->withCount([
                'pesertaDetail as total_peserta',
                'pesertaDetail as hadir_count' => fn ($q) => $q->where('presensi', 'hadir'),
            ])
            ->where('instruktur_id', $instrukturId)
            ->orderByRaw("CASE status WHEN 'perbaikan' THEN 0 WHEN 'draft' THEN 1 WHEN 'pengajuan' THEN 2 ELSE 3 END")
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        return [
            'stats' => $stats,
            'jadwal_list' => $jadwalList,
            'recent_pertemuan' => $recentPertemuan,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getKepalaSeksiData(): array
    {
        $stats = [
            'menunggu_kelulusan' => PendaftaranPeserta::where('status_pendaftaran', 'selesai_asesmen')->count(),
            'peserta_diterima' => PendaftaranPeserta::where('status_pendaftaran', 'diterima')->count(),
            'peserta_ditolak' => PendaftaranPeserta::where('status_pendaftaran', 'ditolak')->count(),
            'total_pertemuan_acc' => PertemuanPembelajaran::where('status', 'acc')->count(),
            'total_galeri' => Galeri::whereHas('pertemuan', fn ($q) => $q->where('status', 'acc'))->count(),
            'total_foto' => GaleriFoto::whereHas('galeri.pertemuan', fn ($q) => $q->where('status', 'acc'))->count(),
        ];

        $pesertaMenungguKelulusan = PendaftaranPeserta::with([
            'wali.user:id,name',
            'jenisDisabilitas:id,nama',
            'asesmen.pekerja:id,name',
        ])
            ->whereIn('status_pendaftaran', ['selesai_asesmen', 'diterima', 'ditolak'])
            ->orderByRaw("CASE status_pendaftaran WHEN 'selesai_asesmen' THEN 0 ELSE 1 END")
            ->orderBy('updated_at', 'desc')
            ->limit(6)
            ->get();

        $recentPertemuanAcc = PertemuanPembelajaran::with([
            'jadwal.mataPelajaran.jenisDisabilitas:id,nama',
            'instruktur:id,name',
            'pekerjaSosial:id,name',
            'galeri.foto',
        ])
            ->withCount([
                'pesertaDetail as total_peserta',
                'pesertaDetail as hadir_count' => fn ($q) => $q->where('presensi', 'hadir'),
                'galeri as galeri_count',
            ])
            ->where('status', 'acc')
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        return [
            'stats' => $stats,
            'peserta_kelulusan' => $pesertaMenungguKelulusan,
            'recent_pertemuan_acc' => $recentPertemuanAcc,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getGeneralData(): array
    {
        return [
            'stats' => [
                'total_peserta_diterima' => PendaftaranPeserta::where('status_pendaftaran', 'diterima')->count(),
                'total_jadwal' => Jadwal::count(),
                'total_pertemuan_acc' => PertemuanPembelajaran::where('status', 'acc')->count(),
            ],
        ];
    }
}
