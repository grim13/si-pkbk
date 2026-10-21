<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JadwalController extends Controller
{
    private const HARI_OPTIONS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    public function index(Request $request): Response
    {
        $currentYear = (int) now()->year;
        $tahun = (int) $request->input('tahun', $currentYear);

        $query = Jadwal::with([
            'instruktur:id,name,email',
            'mataPelajaran.jenisDisabilitas:id,nama',
        ])->where('tahun', $tahun);

        if ($request->filled('hari') && in_array($request->hari, self::HARI_OPTIONS, true)) {
            $query->where('hari', $request->hari);
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('ruangan', 'like', '%'.$search.'%')
                    ->orWhere('keterangan', 'like', '%'.$search.'%')
                    ->orWhereHas('mataPelajaran', function ($mq) use ($search) {
                        $mq->where('nama', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('instruktur', function ($iq) use ($search) {
                        $iq->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        $jadwalList = $query
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('jam_mulai')
            ->get();

        $dbYears = Jadwal::query()->distinct()->pluck('tahun')->map(fn ($y) => (int) $y)->all();
        $availableYears = collect([$currentYear - 1, $currentYear, $currentYear + 1, $tahun, ...$dbYears])
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        $mataPelajaranList = MataPelajaran::with(['jenisDisabilitas:id,nama'])
            ->orderBy('nama')
            ->get();

        $instrukturList = User::role('instruktur')
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('Jadwal/Index', [
            'jadwalList' => $jadwalList,
            'mataPelajaranList' => $mataPelajaranList,
            'instrukturList' => $instrukturList,
            'hariOptions' => self::HARI_OPTIONS,
            'availableYears' => $availableYears,
            'filters' => [
                'tahun' => $tahun,
                'hari' => $request->input('hari', ''),
                'search' => $request->input('search', ''),
            ],
        ]);
    }

    public function cetak(Request $request): Response
    {
        $tahun = (int) $request->input('tahun', now()->year);

        $jadwalList = Jadwal::with([
            'instruktur:id,name',
            'mataPelajaran.jenisDisabilitas:id,nama',
        ])
            ->where('tahun', $tahun)
            ->orderBy('jam_mulai')
            ->orderBy('jam_selesai')
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 WHEN 'Sabtu' THEN 6 ELSE 7 END")
            ->orderBy('id')
            ->get();

        return Inertia::render('Jadwal/Cetak', [
            'jadwalList' => $jadwalList,
            'tahun' => $tahun,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        Jadwal::create($validated);

        return back()->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function update(Request $request, Jadwal $jadwal): RedirectResponse
    {
        $validated = $this->validateRequest($request);

        $jadwal->update($validated);

        return back()->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Jadwal $jadwal): RedirectResponse
    {
        $jadwal->delete();

        return back()->with('success', 'Jadwal berhasil dihapus.');
    }

    /**
     * @return array{mata_pelajaran_id: int, instruktur_id: int, tahun: int, hari: string, jam_mulai: string, jam_selesai: string, ruangan?: string|null, keterangan?: string|null}
     */
    private function validateRequest(Request $request): array
    {
        $request->merge([
            'jam_mulai' => is_string($request->jam_mulai) ? substr($request->jam_mulai, 0, 5) : $request->jam_mulai,
            'jam_selesai' => is_string($request->jam_selesai) ? substr($request->jam_selesai, 0, 5) : $request->jam_selesai,
        ]);

        return $request->validate([
            'mata_pelajaran_id' => ['required', 'integer', 'exists:mata_pelajaran,id'],
            'instruktur_id' => [
                'required',
                'integer',
                'exists:users,id',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $user = User::find($value);
                    if (! $user || ! $user->hasRole('instruktur')) {
                        $fail('User yang dipilih bukan merupakan instruktur.');
                    }
                },
            ],
            'tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
            'hari' => ['required', 'string', 'in:'.implode(',', self::HARI_OPTIONS)],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'ruangan' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
        ], [
            'mata_pelajaran_id.required' => 'Mata pelajaran wajib dipilih.',
            'mata_pelajaran_id.exists' => 'Mata pelajaran yang dipilih tidak valid.',
            'instruktur_id.required' => 'Instruktur wajib dipilih.',
            'instruktur_id.exists' => 'Instruktur yang dipilih tidak valid.',
            'tahun.required' => 'Tahun jadwal wajib diisi.',
            'hari.required' => 'Hari wajib dipilih.',
            'hari.in' => 'Hari harus antara Senin sampai Sabtu.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_mulai.date_format' => 'Format jam mulai harus HH:MM.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'jam_selesai.date_format' => 'Format jam selesai harus HH:MM.',
            'jam_selesai.after' => 'Jam selesai harus lebih akhir dari jam mulai.',
        ]);
    }
}
