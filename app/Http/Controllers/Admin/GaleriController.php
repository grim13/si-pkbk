<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Galeri;
use App\Models\GaleriFoto;
use App\Models\Jadwal;
use App\Models\PertemuanPembelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class GaleriController extends Controller
{
    public function index(Request $request): Response
    {
        $currentYear = (int) now()->year;
        $tahunInput = $request->input('tahun', '');
        $tahun = $tahunInput !== '' && $tahunInput !== null ? (int) $tahunInput : null;

        $query = Galeri::with([
            'foto',
            'creator:id,name',
            'pertemuan.jadwal.mataPelajaran.jenisDisabilitas:id,nama',
            'pertemuan.instruktur:id,name',
        ])->withCount('foto');

        if ($tahun !== null) {
            $query->whereHas('pertemuan.jadwal', fn ($q) => $q->where('tahun', $tahun));
        }

        if ($request->filled('jadwal_id')) {
            $jadwalId = (int) $request->input('jadwal_id');
            $query->whereHas('pertemuan', fn ($q) => $q->where('jadwal_id', $jadwalId));
        }

        if ($request->filled('pertemuan_pembelajaran_id')) {
            $query->where('pertemuan_pembelajaran_id', (int) $request->input('pertemuan_pembelajaran_id'));
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

        $pertemuanList = PertemuanPembelajaran::with([
            'jadwal.mataPelajaran.jenisDisabilitas:id,nama',
            'instruktur:id,name',
        ])
            ->withCount([
                'pesertaDetail as total_peserta',
                'pesertaDetail as hadir_count' => fn ($q) => $q->where('presensi', 'hadir'),
            ])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $jadwalList = Jadwal::with([
            'mataPelajaran:id,nama',
            'instruktur:id,name',
        ])
            ->orderBy('tahun', 'desc')
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('jam_mulai')
            ->get();

        $dbYears = Jadwal::query()->distinct()->pluck('tahun')->map(fn ($y) => (int) $y)->all();
        $availableYears = collect([$currentYear - 1, $currentYear, $currentYear + 1, ...$dbYears])
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return Inertia::render('Admin/Galeri/Index', [
            'galeriList' => $galeriList,
            'pertemuanList' => $pertemuanList,
            'jadwalList' => $jadwalList,
            'availableYears' => $availableYears,
            'filters' => [
                'tahun' => $tahun !== null ? (string) $tahun : '',
                'jadwal_id' => $request->input('jadwal_id', ''),
                'pertemuan_pembelajaran_id' => $request->input('pertemuan_pembelajaran_id', ''),
                'search' => $request->input('search', ''),
            ],
        ]);
    }

    public function show(Galeri $galeri): Response
    {
        $galeri->load([
            'foto',
            'creator:id,name',
            'pertemuan.jadwal.mataPelajaran.jenisDisabilitas:id,nama',
            'pertemuan.instruktur:id,name',
            'pertemuan.pesertaDetail.pendaftaranPeserta:id,wali_id,nama,no_pendaftaran,jenis_disabilitas_id',
        ]);

        return Inertia::render('Admin/Galeri/Show', [
            'galeri' => $galeri,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normalizeRequest($request);

        $validated = $request->validate([
            'pertemuan_pembelajaran_id' => ['required', 'integer', 'exists:pertemuan_pembelajaran,id'],
            'judul' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'fotos' => ['required', 'array', 'min:1'],
            'fotos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'pertemuan_pembelajaran_id.required' => 'Pertemuan pembelajaran wajib dipilih.',
            'pertemuan_pembelajaran_id.exists' => 'Pertemuan pembelajaran yang dipilih tidak valid.',
            'judul.required' => 'Judul galeri wajib diisi.',
            'fotos.required' => 'Minimal pilih 1 foto untuk diunggah ke galeri.',
            'fotos.min' => 'Minimal pilih 1 foto untuk diunggah ke galeri.',
            'fotos.*.image' => 'File yang diunggah harus berupa gambar.',
            'fotos.*.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'fotos.*.max' => 'Ukuran setiap foto maksimal 5 MB.',
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $galeri = Galeri::create([
                'pertemuan_pembelajaran_id' => $validated['pertemuan_pembelajaran_id'],
                'judul' => $validated['judul'],
                'keterangan' => $validated['keterangan'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->file('fotos', []) as $file) {
                $path = $file->store('galeri_pembelajaran', 'public');

                $galeri->foto()->create([
                    'file_path' => $path,
                    'nama_file' => $file->getClientOriginalName(),
                ]);
            }
        });

        return back()->with('success', 'Galeri pembelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, Galeri $galeri): RedirectResponse
    {
        $this->normalizeRequest($request);

        $validated = $request->validate([
            'pertemuan_pembelajaran_id' => ['required', 'integer', 'exists:pertemuan_pembelajaran,id'],
            'judul' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'fotos' => ['nullable', 'array'],
            'fotos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'hapus_foto_ids' => ['nullable', 'array'],
            'hapus_foto_ids.*' => ['integer'],
        ], [
            'pertemuan_pembelajaran_id.required' => 'Pertemuan pembelajaran wajib dipilih.',
            'pertemuan_pembelajaran_id.exists' => 'Pertemuan pembelajaran yang dipilih tidak valid.',
            'judul.required' => 'Judul galeri wajib diisi.',
            'fotos.*.image' => 'File yang diunggah harus berupa gambar.',
            'fotos.*.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WEBP.',
            'fotos.*.max' => 'Ukuran setiap foto maksimal 5 MB.',
        ]);

        $hapusIds = collect($validated['hapus_foto_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        $existingCount = $galeri->foto()->count();
        $deletingCount = empty($hapusIds)
            ? 0
            : $galeri->foto()->whereIn('id', $hapusIds)->count();
        $newFiles = $request->file('fotos', []);
        $remainingCount = ($existingCount - $deletingCount) + count($newFiles);

        if ($remainingCount < 1) {
            return back()
                ->withErrors(['fotos' => 'Galeri harus memiliki minimal 1 foto.'])
                ->withInput();
        }

        DB::transaction(function () use ($galeri, $validated, $hapusIds, $newFiles): void {
            $galeri->update([
                'pertemuan_pembelajaran_id' => $validated['pertemuan_pembelajaran_id'],
                'judul' => $validated['judul'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            if (! empty($hapusIds)) {
                $fotosToDelete = $galeri->foto()->whereIn('id', $hapusIds)->get();
                foreach ($fotosToDelete as $foto) {
                    Storage::disk('public')->delete($foto->file_path);
                    $foto->delete();
                }
            }

            foreach ($newFiles as $file) {
                $path = $file->store('galeri_pembelajaran', 'public');

                $galeri->foto()->create([
                    'file_path' => $path,
                    'nama_file' => $file->getClientOriginalName(),
                ]);
            }
        });

        return back()->with('success', 'Galeri pembelajaran berhasil diperbarui.');
    }

    public function destroy(Galeri $galeri): RedirectResponse
    {
        $galeri->load('foto');

        foreach ($galeri->foto as $foto) {
            Storage::disk('public')->delete($foto->file_path);
        }

        $galeri->delete();

        return back()->with('success', 'Galeri pembelajaran berhasil dihapus.');
    }

    public function destroyFoto(Galeri $galeri, GaleriFoto $foto): RedirectResponse
    {
        if ((int) $foto->galeri_id !== (int) $galeri->id) {
            abort(404);
        }

        if ($galeri->foto()->count() <= 1) {
            return back()->withErrors([
                'foto' => 'Minimal harus tersisa 1 foto dalam galeri.',
            ]);
        }

        Storage::disk('public')->delete($foto->file_path);
        $foto->delete();

        return back()->with('success', 'Foto berhasil dihapus dari galeri.');
    }

    private function normalizeRequest(Request $request): void
    {
        if ($request->filled('pertemuan_id') && ! $request->filled('pertemuan_pembelajaran_id')) {
            $request->merge([
                'pertemuan_pembelajaran_id' => $request->input('pertemuan_id'),
            ]);
        }

        if ($request->hasFile('photos') && ! $request->hasFile('fotos')) {
            $request->files->set('fotos', $request->file('photos'));
        }
    }
}
