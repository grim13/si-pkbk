<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import {
        BookOpen,
        Calendar,
        CheckCircle2,
        Clock,
        Eye,
        Filter,
        Image as ImageIcon,
        Images,
        MapPin,
        Search,
        UserCheck,
        Users,
    } from '@lucide/svelte';
    import Heading from '@/components/Heading.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import {
        Table,
        TableBody,
        TableCell,
        TableHead,
        TableHeader,
        TableRow,
    } from '@/components/ui/table';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import type { BreadcrumbItem } from '@/types';

    interface GaleriFotoItem {
        id: number;
        file_path: string;
        nama_file?: string | null;
        keterangan?: string | null;
    }

    interface GaleriItem {
        id: number;
        judul: string;
        keterangan?: string | null;
        foto?: GaleriFotoItem[];
    }

    interface PertemuanItem {
        id: number;
        jadwal_id: number;
        tanggal: string;
        status: 'acc';
        keterangan?: string | null;
        catatan_pekerja_sosial?: string | null;
        approved_at?: string | null;
        total_peserta?: number;
        hadir_count?: number;
        izin_count?: number;
        absen_count?: number;
        galeri_count?: number;
        galeri?: GaleriItem[];
        instruktur?: {
            id: number;
            name: string;
            email?: string;
        };
        pekerja_sosial?: {
            id: number;
            name: string;
        } | null;
        jadwal?: {
            id: number;
            hari: string;
            tahun: number;
            jam_mulai: string;
            jam_selesai: string;
            ruangan?: string | null;
            mata_pelajaran?: {
                id: number;
                nama: string;
                jenis_disabilitas?:
                    | {
                          id: number;
                          nama: string;
                      }
                    | {
                          id: number;
                          nama: string;
                      }[];
            };
        };
    }

    interface JadwalOption {
        id: number;
        hari: string;
        tahun: number;
        mata_pelajaran?: {
            id: number;
            nama: string;
            jenis_disabilitas?:
                | {
                      id: number;
                      nama: string;
                  }
                | {
                      id: number;
                      nama: string;
                  }[];
        };
        instruktur?: {
            id: number;
            name: string;
        };
    }

    let {
        pertemuanList = [],
        jadwalList = [],
        availableYears = [],
        filters = { tahun: '', jadwal_id: '', search: '' },
    }: {
        pertemuanList: PertemuanItem[];
        jadwalList: JadwalOption[];
        availableYears: number[];
        filters: {
            tahun: string;
            jadwal_id: string;
            search: string;
        };
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Data Pembelajaran (ACC)', href: '/kepala-seksi/pembelajaran' },
    ];

    let filterTahun = $state('');
    let filterJadwalId = $state('');
    let searchQuery = $state('');

    $effect(() => {
        filterTahun = filters?.tahun ? String(filters.tahun) : '';
        filterJadwalId = filters?.jadwal_id ? String(filters.jadwal_id) : '';
        searchQuery = filters?.search ?? '';
    });

    function applyFilter() {
        router.get(
            '/kepala-seksi/pembelajaran',
            {
                tahun: filterTahun || undefined,
                jadwal_id: filterJadwalId || undefined,
                search: searchQuery || undefined,
            },
            { preserveState: true, replace: true },
        );
    }

    function resetFilter() {
        filterTahun = '';
        filterJadwalId = '';
        searchQuery = '';
        router.get('/kepala-seksi/pembelajaran', {}, { preserveState: true, replace: true });
    }

    function formatDate(dateString?: string | null) {
        if (!dateString) {
            return '-';
        }
        return new Date(dateString).toLocaleDateString('id-ID', {
            weekday: 'short',
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    }

    function formatTime(time?: string) {
        if (!time) {
            return '-';
        }
        return time.slice(0, 5);
    }

    function formatDisabilitas(
        jd?:
            | { id: number; nama: string }
            | { id: number; nama: string }[]
            | null,
    ): string {
        if (!jd) return 'Umum';
        if (Array.isArray(jd)) {
            return jd.length > 0 ? jd.map((d) => d.nama).join(', ') : 'Umum';
        }
        return jd.nama || 'Umum';
    }

    function getTotalFotoCount(item: PertemuanItem): number {
        if (!item.galeri || item.galeri.length === 0) {
            return 0;
        }
        return item.galeri.reduce((sum, g) => sum + (g.foto?.length ?? 0), 0);
    }

    function getFirstPhotos(item: PertemuanItem, max = 3): GaleriFotoItem[] {
        if (!item.galeri) {
            return [];
        }
        const all: GaleriFotoItem[] = [];
        for (const g of item.galeri) {
            for (const f of g.foto ?? []) {
                all.push(f);
                if (all.length >= max) {
                    return all;
                }
            }
        }
        return all;
    }

    let totalPertemuanAcc = $derived(pertemuanList.length);
    let totalDenganGaleri = $derived(
        pertemuanList.filter((p) => (p.galeri_count ?? p.galeri?.length ?? 0) > 0).length,
    );
    let totalFotoDokumentasi = $derived(
        pertemuanList.reduce((acc, p) => acc + getTotalFotoCount(p), 0),
    );
</script>

<svelte:head>
    <title>Data Pembelajaran (ACC) - Kepala Seksi</title>
</svelte:head>

<AppLayout {breadcrumbs}>
    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <Heading
                title="Data Pembelajaran (ACC Pekerja Sosial)"
                description="Daftar seluruh pertemuan pembelajaran yang telah diverifikasi dan disetujui (ACC) oleh Pekerja Sosial beserta rekap nilai, presensi, dan galeri dokumentasi foto."
            />
        </div>

        <!-- Ringkasan Statistik -->
        <div class="grid gap-4 sm:grid-cols-3">
            <Card class="border-emerald-200 bg-emerald-50/40 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-emerald-700 dark:text-emerald-300">
                            Total Pertemuan Disetujui (ACC)
                        </p>
                        <p class="mt-1 text-2xl font-bold text-emerald-800 dark:text-emerald-200">
                            {totalPertemuanAcc}
                        </p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                        <CheckCircle2 class="size-5" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">
                            Pertemuan Memiliki Galeri Foto
                        </p>
                        <p class="mt-1 text-2xl font-bold">
                            {totalDenganGaleri}
                            <span class="text-xs font-normal text-muted-foreground">
                                dari {totalPertemuanAcc} pertemuan
                            </span>
                        </p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <Images class="size-5" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">
                            Total Foto Dokumentasi
                        </p>
                        <p class="mt-1 text-2xl font-bold">
                            {totalFotoDokumentasi}
                            <span class="text-xs font-normal text-muted-foreground">foto</span>
                        </p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                        <ImageIcon class="size-5" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Filter Bar -->
        <Card>
            <CardContent class="pt-6">
                <div class="grid gap-4 md:grid-cols-12 md:items-end">
                    <div class="space-y-1.5 md:col-span-2">
                        <label for="filter-tahun" class="text-xs font-medium text-muted-foreground">
                            Tahun Jadwal
                        </label>
                        <select
                            id="filter-tahun"
                            bind:value={filterTahun}
                            onchange={applyFilter}
                            class="border-input bg-background ring-offset-background focus-visible:ring-ring flex h-10 w-full rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:outline-none"
                        >
                            <option value="">Semua Tahun</option>
                            {#each availableYears as year (year)}
                                <option value={String(year)}>Tahun {year}</option>
                            {/each}
                        </select>
                    </div>

                    <div class="space-y-1.5 md:col-span-4">
                        <label for="filter-jadwal" class="text-xs font-medium text-muted-foreground">
                            Filter Kelas / Mata Pelajaran
                        </label>
                        <select
                            id="filter-jadwal"
                            bind:value={filterJadwalId}
                            onchange={applyFilter}
                            class="border-input bg-background ring-offset-background focus-visible:ring-ring flex h-10 w-full rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:outline-none"
                        >
                            <option value="">Semua Kelas Pembelajaran</option>
                            {#each jadwalList as j (j.id)}
                                <option value={String(j.id)}>
                                    {j.mata_pelajaran?.nama ?? 'Mata Pelajaran'} ({formatDisabilitas(j.mata_pelajaran?.jenis_disabilitas)}) - {j.hari} ({j.tahun})
                                </option>
                            {/each}
                        </select>
                    </div>

                    <div class="space-y-1.5 md:col-span-4">
                        <label for="filter-search" class="text-xs font-medium text-muted-foreground">
                            Cari Mata Pelajaran / Instruktur / Materi
                        </label>
                        <div class="relative">
                            <Search class="absolute top-2.5 left-2.5 size-4 text-muted-foreground" />
                            <Input
                                id="filter-search"
                                placeholder="Ketik kata kunci pencarian..."
                                bind:value={searchQuery}
                                onkeydown={(e) => e.key === 'Enter' && applyFilter()}
                                class="pl-9"
                            />
                        </div>
                    </div>

                    <div class="flex gap-2 md:col-span-2">
                        <Button variant="secondary" onclick={applyFilter} class="flex-1">
                            <Filter class="mr-2 size-4" />
                            Filter
                        </Button>
                        {#if filterTahun || filterJadwalId || searchQuery}
                            <Button variant="ghost" onclick={resetFilter}>Reset</Button>
                        {/if}
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Tabel Pertemuan Pembelajaran ACC -->
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-base">
                    <BookOpen class="size-5 text-primary" />
                    Daftar Pertemuan Pembelajaran Terverifikasi ({pertemuanList.length})
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Tanggal Pertemuan</TableHead>
                                <TableHead>Mata Pelajaran & Kelas</TableHead>
                                <TableHead>Instruktur</TableHead>
                                <TableHead>Rekap Presensi</TableHead>
                                <TableHead>Diverifikasi (ACC) Oleh</TableHead>
                                <TableHead>Galeri Foto</TableHead>
                                <TableHead class="text-right">Aksi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {#if pertemuanList.length === 0}
                                <TableRow>
                                    <TableCell colspan={7} class="h-32 text-center text-muted-foreground">
                                        Belum ada data pertemuan pembelajaran yang berstatus ACC.
                                    </TableCell>
                                </TableRow>
                            {:else}
                                {#each pertemuanList as item (item.id)}
                                    {@const totalFoto = getTotalFotoCount(item)}
                                    {@const thumbPhotos = getFirstPhotos(item, 3)}
                                    <TableRow>
                                        <TableCell class="font-medium">
                                            <div class="flex items-center gap-1.5">
                                                <Calendar class="size-3.5 text-primary" />
                                                <span>{formatDate(item.tanggal)}</span>
                                            </div>
                                            <div class="mt-0.5 flex items-center gap-2 text-xs text-muted-foreground">
                                                <span class="inline-flex items-center gap-1">
                                                    <Clock class="size-3" />
                                                    {item.jadwal?.hari ?? '-'}, {formatTime(item.jadwal?.jam_mulai)} - {formatTime(item.jadwal?.jam_selesai)}
                                                </span>
                                                {#if item.jadwal?.ruangan}
                                                    <span class="inline-flex items-center gap-1">
                                                        <MapPin class="size-3" />
                                                        {item.jadwal.ruangan}
                                                    </span>
                                                {/if}
                                            </div>
                                        </TableCell>

                                        <TableCell>
                                            <div class="font-semibold">
                                                {item.jadwal?.mata_pelajaran?.nama ?? '-'}
                                            </div>
                                            <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                                <Badge variant="outline" class="text-[11px]">
                                                    {formatDisabilitas(item.jadwal?.mata_pelajaran?.jenis_disabilitas)}
                                                </Badge>
                                                {#if item.keterangan}
                                                    <span class="line-clamp-1 max-w-[220px] text-xs text-muted-foreground">
                                                        {item.keterangan}
                                                    </span>
                                                {/if}
                                            </div>
                                        </TableCell>

                                        <TableCell>
                                            <div class="font-medium">{item.instruktur?.name ?? '-'}</div>
                                            <div class="text-xs text-muted-foreground">
                                                {item.instruktur?.email ?? ''}
                                            </div>
                                        </TableCell>

                                        <TableCell>
                                            <div class="flex flex-wrap items-center gap-1 text-xs">
                                                <Badge
                                                    variant="outline"
                                                    class="border-emerald-200 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300"
                                                >
                                                    Hadir: {item.hadir_count ?? 0}
                                                </Badge>
                                                <Badge
                                                    variant="outline"
                                                    class="border-amber-200 bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300"
                                                >
                                                    Izin: {item.izin_count ?? 0}
                                                </Badge>
                                                <Badge
                                                    variant="outline"
                                                    class="border-rose-200 bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-300"
                                                >
                                                    Absen: {item.absen_count ?? 0}
                                                </Badge>
                                            </div>
                                            <div class="mt-1 flex items-center gap-1 text-[11px] text-muted-foreground">
                                                <Users class="size-3" />
                                                Total: {item.total_peserta ?? 0} peserta
                                            </div>
                                        </TableCell>

                                        <TableCell>
                                            <div class="flex items-center gap-1.5 text-sm font-medium text-emerald-700 dark:text-emerald-400">
                                                <UserCheck class="size-3.5" />
                                                <span>{item.pekerja_sosial?.name ?? 'Pekerja Sosial'}</span>
                                            </div>
                                            {#if item.approved_at}
                                                <div class="text-[11px] text-muted-foreground">
                                                    ACC: {formatDate(item.approved_at)}
                                                </div>
                                            {/if}
                                        </TableCell>

                                        <TableCell>
                                            {#if (item.galeri_count ?? item.galeri?.length ?? 0) > 0}
                                                <div class="flex items-center gap-2">
                                                    {#if thumbPhotos.length > 0}
                                                        <div class="flex -space-x-2 overflow-hidden">
                                                            {#each thumbPhotos as photo (photo.id)}
                                                                <img
                                                                    src={`/storage/${photo.file_path}`}
                                                                    alt={photo.nama_file ?? 'Foto Galeri'}
                                                                    class="inline-block size-8 rounded-md border-2 border-background object-cover"
                                                                />
                                                            {/each}
                                                        </div>
                                                    {/if}
                                                    <div>
                                                        <Badge variant="default" class="gap-1 bg-primary/90 text-[11px]">
                                                            <Images class="size-3" />
                                                            {totalFoto} Foto
                                                        </Badge>
                                                        <div class="mt-0.5 text-[11px] text-muted-foreground">
                                                            {item.galeri_count ?? item.galeri?.length ?? 0} album galeri
                                                        </div>
                                                    </div>
                                                </div>
                                            {:else}
                                                <Badge variant="secondary" class="text-xs text-muted-foreground">
                                                    Belum ada galeri
                                                </Badge>
                                            {/if}
                                        </TableCell>

                                        <TableCell class="text-right">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                onclick={() => router.visit(`/kepala-seksi/pembelajaran/${item.id}`)}
                                            >
                                                <Eye class="mr-1.5 size-3.5" />
                                                Lihat Detail
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                {/each}
                            {/if}
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
    </div>
</AppLayout>
