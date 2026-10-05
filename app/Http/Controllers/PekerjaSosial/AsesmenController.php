<?php

namespace App\Http\Controllers\PekerjaSosial;

use App\Http\Controllers\Controller;
use App\Models\Asesmen;
use App\Models\AsesmenSeksi;
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
        $peserta = PendaftaranPeserta::with('wali')
            ->whereIn('status_pendaftaran', ['asesmen', 'diterima', 'ditolak'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('PekerjaSosial/Asesmen/Index', [
            'peserta' => $peserta
        ]);
    }

    /**
     * Show the assessment form for a specific participant.
     */
    public function show(PendaftaranPeserta $pendaftaran)
    {
        // Pastikan pendaftaran sudah di tahap asesmen atau lebih
        if (!in_array($pendaftaran->status_pendaftaran, ['asesmen', 'diterima', 'ditolak'])) {
            return redirect()->route('pekerja-sosial.asesmen.index')->with('error', 'Peserta belum masuk tahap asesmen.');
        }

        $pendaftaran->load(['wali.user', 'wali.anggotaKeluarga']);

        // Ambil data asesmen jika sudah ada, atau buat instance kosong (tanpa save)
        $asesmen = $pendaftaran->asesmen()->with('jawaban')->first();
        if (!$asesmen) {
            $asesmen = new Asesmen();
        }

        $seksi = AsesmenSeksi::with('pertanyaan')->orderBy('urutan')->get();

        return Inertia::render('PekerjaSosial/Asesmen/Form', [
            'pendaftaran' => $pendaftaran,
            'asesmen' => $asesmen,
            'seksiMaster' => $seksi
        ]);
    }

    /**
     * Store or update the assessment data.
     */
    public function store(Request $request, PendaftaranPeserta $pendaftaran)
    {
        // Validasi struktur (hanya validasi dasar, sisanya fleksibel karena panjang)
        $request->validate([
            'status' => 'required|in:draft,selesai',
            // Kita bisa menambahkan validasi lain sesuai kebutuhan
        ]);

        $data = $request->except(['jawaban', 'status', 'pendaftaran', 'wali', 'anggota_keluarga']);
        $data['status'] = $request->status;
        $data['pekerja_sosial_id'] = auth()->id();
        $data['tanggal_asesmen'] = $request->tanggal_asesmen ?? now();

        // Update Pendaftaran Peserta (Identitas Klien)
        if ($request->has('pendaftaran') && is_array($request->pendaftaran)) {
            $pendaftaran->update($request->pendaftaran);
        }

        // Update Wali (Identitas Keluarga)
        if ($request->has('wali') && is_array($request->wali)) {
            $waliData = $request->wali;
            if (isset($waliData['nama']) && $pendaftaran->wali && $pendaftaran->wali->user) {
                $pendaftaran->wali->user->update(['name' => $waliData['nama']]);
            }
            unset($waliData['nama']); // Hapus atribut nama karena bukan kolom di tabel wali
            $pendaftaran->wali->update($waliData);
        }

        // Update Anggota Keluarga
        if ($request->has('anggota_keluarga') && is_array($request->anggota_keluarga)) {
            // Delete existing ones not in the request, or simply delete all and recreate
            $pendaftaran->wali->anggotaKeluarga()->delete();
            foreach ($request->anggota_keluarga as $ak) {
                // If it's a valid row
                if (!empty($ak['nama']) && !empty($ak['status'])) {
                    $pendaftaran->wali->anggotaKeluarga()->create([
                        'nama' => $ak['nama'],
                        'status' => $ak['status'],
                        'tanggal_lahir' => $ak['tanggal_lahir'] ?? null,
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

        // Simpan jawaban
        if ($request->has('jawaban') && is_array($request->jawaban)) {
            foreach ($request->jawaban as $pertanyaan_id => $jawaban) {
                $asesmen->jawaban()->updateOrCreate(
                    ['asesmen_pertanyaan_id' => $pertanyaan_id],
                    [
                        'jawaban_ya_tidak' => $jawaban['jawaban_ya_tidak'] ?? null,
                        'jawaban_text' => $jawaban['jawaban_text'] ?? null,
                        'jawaban_number' => $jawaban['jawaban_number'] ?? null,
                        'keterangan' => $jawaban['keterangan'] ?? null,
                    ]
                );
            }
        }

        return redirect()->route('pekerja-sosial.asesmen.show', $pendaftaran->id)
            ->with('success', 'Data asesmen berhasil disimpan.');
    }
}
