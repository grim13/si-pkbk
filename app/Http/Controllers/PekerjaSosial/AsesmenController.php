<?php

namespace App\Http\Controllers\PekerjaSosial;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\AsesmenSeksi;
use App\Models\JenisDisabilitas;
use App\Models\PendaftaranPeserta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AsesmenController extends Controller
{
    /**
     * Display a listing of participants ready for assessment or already assessed.
     */
    public function index()
    {
        $peserta = PendaftaranPeserta::with(['wali', 'jenisDisabilitas'])
            ->whereIn('status_pendaftaran', ['asesmen', 'selesai_asesmen', 'diterima', 'ditolak'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('PekerjaSosial/Asesmen/Index', [
            'peserta' => $peserta,
        ]);
    }

    /**
     * Show the assessment form for a specific participant.
     */
    public function show(PendaftaranPeserta $pendaftaran)
    {
        // Pastikan pendaftaran sudah di tahap asesmen atau lebih
        if (! in_array($pendaftaran->status_pendaftaran, ['asesmen', 'selesai_asesmen', 'diterima', 'ditolak'])) {
            return redirect()->route('pekerja-sosial.asesmen.index')->with('error', 'Peserta belum masuk tahap asesmen.');
        }

        $pendaftaran->load(['wali.user', 'wali.anggotaKeluarga', 'jenisDisabilitas']);

        // Ambil data asesmen jika sudah ada, atau buat instance kosong (tanpa save)
        $asesmen = $pendaftaran->asesmen()->with('jawaban')->first();
        if (! $asesmen) {
            $asesmen = new Asesmen;
        }

        $seksi = AsesmenSeksi::with('pertanyaan')->orderBy('urutan')->get();
        $jenisDisabilitasList = JenisDisabilitas::orderBy('id')->get();

        return Inertia::render('PekerjaSosial/Asesmen/Form', [
            'pendaftaran' => $pendaftaran,
            'asesmen' => $asesmen,
            'seksiMaster' => $seksi,
            'jenisDisabilitasList' => $jenisDisabilitasList,
        ]);
    }

    /**
     * Store or update the assessment data.
     */
    public function store(Request $request, PendaftaranPeserta $pendaftaran)
    {
        $request->validate([
            'status' => 'required|in:draft,selesai',
            'pendaftaran.nama' => 'required|string',
            'pendaftaran.nik' => 'required|string',
            'pendaftaran.jenis_kelamin' => 'required|string|in:Laki-laki,Perempuan',
            'pendaftaran.tempat_lahir' => 'required|string',
            'pendaftaran.tanggal_lahir' => 'required|date',
            'pendaftaran.agama' => 'required|string',
            'pendaftaran.pendidikan' => 'required|string',
            'pendaftaran.alamat_asal' => 'required|string',
            'pendaftaran.jenis_disabilitas_id' => 'required|exists:jenis_disabilitas,id',
            'wali.nama' => 'required|string',
            'wali.nomor_hp' => 'required|string',
            'wali.tempat_lahir' => 'required|string',
            'wali.tanggal_lahir' => 'required|date',
            'wali.alamat' => 'required|string',
        ], [
            'pendaftaran.nama.required' => 'Nama lengkap klien wajib diisi.',
            'pendaftaran.nik.required' => 'NIK klien wajib diisi.',
            'pendaftaran.jenis_kelamin.required' => 'Jenis kelamin klien wajib dipilih.',
            'pendaftaran.jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'pendaftaran.tempat_lahir.required' => 'Tempat lahir klien wajib diisi.',
            'pendaftaran.tanggal_lahir.required' => 'Tanggal lahir klien wajib diisi.',
            'pendaftaran.agama.required' => 'Agama klien wajib diisi.',
            'pendaftaran.pendidikan.required' => 'Pendidikan terakhir klien wajib diisi.',
            'pendaftaran.alamat_asal.required' => 'Alamat lengkap klien wajib diisi.',
            'pendaftaran.jenis_disabilitas_id.required' => 'Jenis disabilitas klien wajib dipilih.',
            'pendaftaran.jenis_disabilitas_id.exists' => 'Jenis disabilitas yang dipilih tidak valid.',
            'wali.nama.required' => 'Nama lengkap wali wajib diisi.',
            'wali.nomor_hp.required' => 'Nomor HP wali wajib diisi.',
            'wali.tempat_lahir.required' => 'Tempat lahir wali wajib diisi.',
            'wali.tanggal_lahir.required' => 'Tanggal lahir wali wajib diisi.',
            'wali.alamat.required' => 'Alamat lengkap wali wajib diisi.',
        ]);

        $data = $request->except(['jawaban', 'status', 'pendaftaran', 'wali', 'anggota_keluarga']);
        $data['status'] = $request->status;
        $data['pekerja_sosial_id'] = auth()->id();
        $data['tanggal_asesmen'] = $request->tanggal_asesmen ?: now();
        $data['tujuan_bimbingan_sekolah'] = false;

        // Update Pendaftaran Peserta (Identitas Klien)
        if ($request->has('pendaftaran') && is_array($request->pendaftaran)) {
            $pendaftaranData = $request->pendaftaran;
            foreach (['tinggi_badan', 'berat_badan'] as $numericField) {
                if (array_key_exists($numericField, $pendaftaranData) && $pendaftaranData[$numericField] === '') {
                    $pendaftaranData[$numericField] = null;
                }
            }
            $pendaftaran->update($pendaftaranData);
        }

        // Update Wali (Identitas Keluarga)
        if ($request->has('wali') && is_array($request->wali) && $pendaftaran->wali) {
            $waliData = $request->wali;
            if (isset($waliData['nama']) && $pendaftaran->wali->user) {
                $pendaftaran->wali->user->update(['name' => $waliData['nama']]);
            }
            unset($waliData['nama']);
            if (empty($waliData['nik'])) {
                unset($waliData['nik']);
            }
            foreach (['penghasilan_perbulan', 'tanggungan_keluarga'] as $numericField) {
                if (array_key_exists($numericField, $waliData) && $waliData[$numericField] === '') {
                    $waliData[$numericField] = null;
                }
            }
            $pendaftaran->wali->update($waliData);
        }

        // Update Anggota Keluarga (Opsional)
        if ($request->has('anggota_keluarga') && is_array($request->anggota_keluarga) && $pendaftaran->wali) {
            $pendaftaran->wali->anggotaKeluarga()->delete();
            foreach ($request->anggota_keluarga as $ak) {
                if (! empty($ak['nama']) && ! empty($ak['status'])) {
                    $pendaftaran->wali->anggotaKeluarga()->create([
                        'nama' => $ak['nama'],
                        'status' => $ak['status'],
                        'tanggal_lahir' => ! empty($ak['tanggal_lahir']) ? $ak['tanggal_lahir'] : null,
                        'pendidikan' => $ak['pendidikan'] ?? null,
                        'pekerjaan' => $ak['pekerjaan'] ?? null,
                    ]);
                }
            }
        }

        $asesmen = $pendaftaran->asesmen()->updateOrCreate(
            ['pendaftaran_peserta_id' => $pendaftaran->id],
            $data
        );

        // Simpan jawaban (Opsional)
        if ($request->has('jawaban') && is_array($request->jawaban)) {
            foreach ($request->jawaban as $pertanyaan_id => $jawaban) {
                $yaTidak = $jawaban['jawaban_ya_tidak'] ?? null;
                $text = isset($jawaban['jawaban_text']) && $jawaban['jawaban_text'] !== '' ? $jawaban['jawaban_text'] : null;
                $number = isset($jawaban['jawaban_number']) && $jawaban['jawaban_number'] !== '' ? $jawaban['jawaban_number'] : null;
                $keterangan = isset($jawaban['keterangan']) && $jawaban['keterangan'] !== '' ? $jawaban['keterangan'] : null;

                if ($yaTidak === null && $text === null && $number === null && $keterangan === null) {
                    $asesmen->jawaban()->where('asesmen_pertanyaan_id', $pertanyaan_id)->delete();

                    continue;
                }

                $asesmen->jawaban()->updateOrCreate(
                    ['asesmen_pertanyaan_id' => $pertanyaan_id],
                    [
                        'jawaban_ya_tidak' => $yaTidak,
                        'jawaban_text' => $text,
                        'jawaban_number' => $number,
                        'keterangan' => $keterangan,
                    ]
                );
            }
        }

        if ($request->status === 'selesai' && $pendaftaran->status_pendaftaran === 'asesmen') {
            $pendaftaran->update(['status_pendaftaran' => 'selesai_asesmen']);
        }

        return redirect()->route('pekerja-sosial.asesmen.show', $pendaftaran->id)
            ->with('success', 'Data asesmen berhasil disimpan.');
    }
}
