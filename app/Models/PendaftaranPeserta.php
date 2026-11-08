<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PendaftaranPeserta extends Model
{
    /**
     * Bobot numerik untuk nilai kualitatif pembelajaran.
     *
     * @var array<string, int>
     */
    public const NILAI_SCORE_MAP = [
        'sangat_baik' => 4,
        'baik' => 3,
        'cukup' => 2,
        'kurang' => 1,
    ];

    protected $table = 'pendaftaran_peserta';

    protected $fillable = [
        'wali_id',
        'jenis_disabilitas_id',
        'nik',
        'no_pendaftaran',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat_asal',
        'pendidikan',
        'keterampilan_yang_diminati',
        'status_pendaftaran',
        'tanggal_mulai_diterima',
        'keterangan_pengajuan_lepas_mandiri',
        'tanggal_pengajuan_lepas_mandiri',
        'keterangan_keputusan_lepas_mandiri',
        'tanggal_lepas_mandiri',
        'catatan_verifikasi',
        'jadwal_asesmen',
        'tempat_asesmen',
        // Fields for Asesmen
        'jenis_kelamin',
        'agama',
        'status_perkawinan',
        'tinggi_badan',
        'berat_badan',
        'bentuk_keadaan_mata',
        'pendengaran',
        'golongan_darah',
        'nomor_induk_klien',
        'disabilitas_netra_sejak',
        'penyebab_disabilitas',
        'tempat_cacat',
        'pengaruh_cacat_tingkah_laku',
        'dirawat_di',
        'gradasi_kecacatan',
    ];

    protected $casts = [
        'jadwal_asesmen' => 'datetime',
        'tanggal_mulai_diterima' => 'datetime',
        'tanggal_pengajuan_lepas_mandiri' => 'datetime',
        'tanggal_lepas_mandiri' => 'datetime',
    ];

    public function wali(): BelongsTo
    {
        return $this->belongsTo(Wali::class);
    }

    public function jenisDisabilitas(): BelongsTo
    {
        return $this->belongsTo(JenisDisabilitas::class, 'jenis_disabilitas_id');
    }

    public function berkas(): HasMany
    {
        return $this->hasMany(PendaftaranPesertaBerkas::class);
    }

    public function asesmen(): HasOne
    {
        return $this->hasOne(Asesmen::class);
    }

    public function pertemuanPembelajaranPeserta(): HasMany
    {
        return $this->hasMany(PertemuanPembelajaranPeserta::class, 'pendaftaran_peserta_id');
    }

    /**
     * Hitung durasi lama pembinaan dari tanggal mulai diterima hingga hari ini.
     *
     * @return array{label: string, total_days: int}
     */
    public function calculateLamaPembinaan(DateTimeInterface|string|null $endDate = null): array
    {
        if (! $this->tanggal_mulai_diterima) {
            return [
                'label' => '-',
                'total_days' => 0,
            ];
        }

        $start = Carbon::parse($this->tanggal_mulai_diterima)->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->startOfDay() : now()->startOfDay();

        if ($start->greaterThan($end)) {
            return [
                'label' => '0 Hari',
                'total_days' => 0,
            ];
        }

        $totalDays = (int) $start->diffInDays($end);
        $diff = $start->diff($end);

        $parts = [];
        if ($diff->y > 0) {
            $parts[] = $diff->y.' Tahun';
        }
        if ($diff->m > 0) {
            $parts[] = $diff->m.' Bulan';
        }
        if ($diff->d > 0) {
            $parts[] = $diff->d.' Hari';
        }

        if (empty($parts)) {
            $parts[] = '0 Hari';
        }

        return [
            'label' => implode(' ', $parts),
            'total_days' => $totalDays,
        ];
    }

    /**
     * Bentuk struktur ringkasan seluruh pembelajaran peserta (rata-rata per mata pelajaran,
     * komentar terakhir instruktur, serta riwayat detail setiap pertemuan).
     *
     * @return array{summary: array<string, mixed>, mata_pelajaran_list: list<array<string, mixed>>}
     */
    public function buildRingkasanPembelajaran(): array
    {
        $lamaPembinaan = $this->calculateLamaPembinaan();

        $riwayatDetail = PertemuanPembelajaranPeserta::with([
            'pertemuan.jadwal.mataPelajaran:id,nama,keterangan',
            'pertemuan.instruktur:id,name',
            'pertemuan.pekerjaSosial:id,name',
        ])
            ->where('pendaftaran_peserta_id', $this->id)
            ->whereHas('pertemuan', fn ($q) => $q->where('status', 'acc'))
            ->get()
            ->sortByDesc(fn (PertemuanPembelajaranPeserta $detail): string => sprintf(
                '%s-%010d-%010d',
                $detail->pertemuan?->tanggal?->format('Y-m-d') ?? '0000-00-00',
                (int) $detail->pertemuan_pembelajaran_id,
                (int) $detail->id,
            ))
            ->values();

        $mataPelajaranEvaluasi = $riwayatDetail
            ->groupBy(fn (PertemuanPembelajaranPeserta $detail): int => (int) ($detail->pertemuan?->jadwal?->mata_pelajaran_id ?? 0))
            ->map(function ($details, int $mapelId): array {
                /** @var PertemuanPembelajaranPeserta|null $firstDetail */
                $firstDetail = $details->first();
                $mapel = $firstDetail?->pertemuan?->jadwal?->mataPelajaran;

                $hadirDetails = $details->where('presensi', 'hadir')->values();
                $gradedDetails = $hadirDetails->isNotEmpty() ? $hadirDetails : $details;

                $scores = $gradedDetails
                    ->map(fn (PertemuanPembelajaranPeserta $d): ?int => self::NILAI_SCORE_MAP[$d->nilai ?? ''] ?? null)
                    ->filter(fn (?int $score): bool => $score !== null)
                    ->values();

                $avgScore = $scores->isNotEmpty()
                    ? round((float) $scores->avg(), 2)
                    : null;
                $avgNilaiKey = self::scoreToNilaiKey($avgScore);

                /** @var PertemuanPembelajaranPeserta|null $latestCommentDetail */
                $latestCommentDetail = $hadirDetails->first(fn (PertemuanPembelajaranPeserta $d): bool => self::resolveKomentarInstruktur($d) !== null)
                    ?? $details->first(fn (PertemuanPembelajaranPeserta $d): bool => self::resolveKomentarInstruktur($d) !== null)
                    ?? $hadirDetails->first()
                    ?? $details->first();

                $riwayat = $details
                    ->map(fn (PertemuanPembelajaranPeserta $d): array => [
                        'id' => $d->id,
                        'pertemuan_id' => $d->pertemuan_pembelajaran_id,
                        'tanggal' => $d->pertemuan?->tanggal?->format('Y-m-d'),
                        'hari' => $d->pertemuan?->jadwal?->hari,
                        'jam_mulai' => $d->pertemuan?->jadwal?->jam_mulai,
                        'jam_selesai' => $d->pertemuan?->jadwal?->jam_selesai,
                        'ruangan' => $d->pertemuan?->jadwal?->ruangan,
                        'tahun' => $d->pertemuan?->jadwal?->tahun,
                        'instruktur_nama' => $d->pertemuan?->instruktur?->name,
                        'presensi' => $d->presensi,
                        'nilai' => $d->nilai,
                        'nilai_skor' => self::NILAI_SCORE_MAP[$d->nilai ?? ''] ?? null,
                        'nilai_label' => self::nilaiLabel($d->nilai),
                        'komentar_instruktur' => self::resolveKomentarInstruktur($d),
                        'keterangan_peserta' => $d->keterangan,
                        'keterangan_pertemuan' => $d->pertemuan?->keterangan,
                        'catatan_pekerja_sosial' => $d->pertemuan?->catatan_pekerja_sosial,
                    ])
                    ->values()
                    ->all();

                return [
                    'mata_pelajaran_id' => $mapelId,
                    'nama_mata_pelajaran' => $mapel?->nama ?? 'Mata Pelajaran',
                    'keterangan_mata_pelajaran' => $mapel?->keterangan,
                    'total_pertemuan' => $details->count(),
                    'total_hadir' => $hadirDetails->count(),
                    'total_izin' => $details->where('presensi', 'izin')->count(),
                    'total_absen' => $details->where('presensi', 'absen')->count(),
                    'rata_rata_skor' => $avgScore,
                    'rata_rata_nilai' => $avgNilaiKey,
                    'rata_rata_label' => self::nilaiLabel($avgNilaiKey),
                    'komentar_terakhir' => $latestCommentDetail ? self::resolveKomentarInstruktur($latestCommentDetail) : null,
                    'tanggal_terakhir' => $latestCommentDetail?->pertemuan?->tanggal?->format('Y-m-d'),
                    'instruktur_terakhir' => $latestCommentDetail?->pertemuan?->instruktur?->name,
                    'nilai_terakhir' => $latestCommentDetail?->nilai,
                    'nilai_terakhir_label' => self::nilaiLabel($latestCommentDetail?->nilai),
                    'riwayat' => $riwayat,
                ];
            })
            ->sortBy('nama_mata_pelajaran')
            ->values()
            ->all();

        $allHadirDetails = $riwayatDetail->where('presensi', 'hadir')->values();
        $allGradedDetails = $allHadirDetails->isNotEmpty() ? $allHadirDetails : $riwayatDetail;
        $allScores = $allGradedDetails
            ->map(fn (PertemuanPembelajaranPeserta $d): ?int => self::NILAI_SCORE_MAP[$d->nilai ?? ''] ?? null)
            ->filter(fn (?int $score): bool => $score !== null)
            ->values();

        $overallAvgScore = $allScores->isNotEmpty()
            ? round((float) $allScores->avg(), 2)
            : null;
        $overallAvgKey = self::scoreToNilaiKey($overallAvgScore);

        return [
            'summary' => [
                'lama_pembinaan' => $lamaPembinaan['label'],
                'lama_pembinaan_hari' => $lamaPembinaan['total_days'],
                'total_mata_pelajaran' => count($mataPelajaranEvaluasi),
                'total_pertemuan' => $riwayatDetail->count(),
                'total_hadir' => $allHadirDetails->count(),
                'total_izin' => $riwayatDetail->where('presensi', 'izin')->count(),
                'total_absen' => $riwayatDetail->where('presensi', 'absen')->count(),
                'rata_rata_skor' => $overallAvgScore,
                'rata_rata_nilai' => $overallAvgKey,
                'rata_rata_label' => self::nilaiLabel($overallAvgKey),
            ],
            'mata_pelajaran_list' => $mataPelajaranEvaluasi,
        ];
    }

    public static function scoreToNilaiKey(?float $score): ?string
    {
        if ($score === null) {
            return null;
        }

        if ($score >= 3.5) {
            return 'sangat_baik';
        }

        if ($score >= 2.5) {
            return 'baik';
        }

        if ($score >= 1.5) {
            return 'cukup';
        }

        return 'kurang';
    }

    public static function nilaiLabel(?string $nilai): string
    {
        return match ($nilai) {
            'sangat_baik' => 'Sangat Baik',
            'baik' => 'Baik',
            'cukup' => 'Cukup',
            'kurang' => 'Kurang',
            default => '-',
        };
    }

    public static function resolveKomentarInstruktur(PertemuanPembelajaranPeserta $detail): ?string
    {
        $pesertaComment = trim((string) ($detail->keterangan ?? ''));
        if ($pesertaComment !== '') {
            return $pesertaComment;
        }

        $pertemuanComment = trim((string) ($detail->pertemuan?->keterangan ?? ''));
        if ($pertemuanComment !== '') {
            return $pertemuanComment;
        }

        return null;
    }
}
