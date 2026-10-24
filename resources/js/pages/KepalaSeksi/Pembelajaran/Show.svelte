<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Data Pembelajaran (ACC)', href: '/kepala-seksi/pembelajaran' },
            { title: 'Detail Pertemuan', href: '#' },
        ],
    };
</script>

<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import {
        ArrowLeft,
        BookOpen,
        Calendar,
        CheckCircle2,
        ChevronLeft,
        ChevronRight,
        Clock,
        Download,
        FileText,
        Image as ImageIcon,
        Images,
        MapPin,
        User,
        UserCheck,
        Users,
    } from '@lucide/svelte';
    import Heading from '@/components/Heading.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardDescription,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import * as Dialog from '@/components/ui/dialog';
    import {
        Table,
        TableBody,
        TableCell,
        TableHead,
        TableHeader,
        TableRow,
    } from '@/components/ui/table';

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
        created_at?: string;
        creator?: {
            id: number;
            name: string;
        } | null;
        foto?: GaleriFotoItem[];
    }

    interface PesertaDetailItem {
        id: number;
        pendaftaran_peserta_id: number;
        presensi: 'hadir' | 'izin' | 'absen';
        nilai?: 'sangat_baik' | 'baik' | 'cukup' | 'kurang' | null;
        nilai_kualitatif?: 'sangat_baik' | 'baik' | 'cukup' | 'kurang' | null;
        keterangan?: string | null;
        pendaftaran_peserta?: {
            id: number;
            no_pendaftaran?: string | null;
            nama?: string;
            nama_lengkap?: string;
            jenis_disabilitas?: {
                id: number;
                nama: string;
            };
            wali?: {
                id: number;
                user?: {
                    id: number;
                    name: string;
                };
            };
        };
    }

    interface PertemuanDetail {
        id: number;
        jadwal_id: number;
        tanggal: string;
        status: 'acc';
        keterangan?: string | null;
        catatan_pekerja_sosial?: string | null;
        approved_at?: string | null;
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
        peserta_detail?: PesertaDetailItem[];
        galeri?: GaleriItem[];
    }

    let {
        pertemuan,
    }: {
        pertemuan: PertemuanDetail;
    } = $props();

    function formatDate(dateString?: string | null) {
        if (!dateString) {
            return '-';
        }
        return new Date(dateString).toLocaleDateString('id-ID', {
            weekday: 'long',
            day: '2-digit',
            month: 'long',
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

    const presensiLabels: Record<string, string> = {
        hadir: 'Hadir',
        izin: 'Izin',
        absen: 'Absen',
    };

    const nilaiLabels: Record<string, string> = {
        sangat_baik: 'Sangat Baik',
        baik: 'Baik',
        cukup: 'Cukup',
        kurang: 'Kurang',
    };

    let pesertaList = $derived(pertemuan.peserta_detail ?? []);
    let hadirCount = $derived(pesertaList.filter((d) => d.presensi === 'hadir').length);
    let izinCount = $derived(pesertaList.filter((d) => d.presensi === 'izin').length);
    let absenCount = $derived(pesertaList.filter((d) => d.presensi === 'absen').length);

    let galeriList = $derived(pertemuan.galeri ?? []);

    interface FlatPhoto {
        id: number;
        file_path: string;
        nama_file?: string | null;
        keterangan?: string | null;
        galeriJudul: string;
    }

    let allPhotos = $derived<FlatPhoto[]>(
        galeriList.flatMap((g) =>
            (g.foto ?? []).map((f) => ({
                ...f,
                galeriJudul: g.judul,
            })),
        ),
    );

    let lightboxOpen = $state(false);
    let activePhotoIndex = $state(0);
    let activePhoto = $derived(allPhotos[activePhotoIndex] ?? null);

    function openLightboxByPhotoId(photoId: number) {
        const idx = allPhotos.findIndex((p) => p.id === photoId);
        activePhotoIndex = idx >= 0 ? idx : 0;
        lightboxOpen = true;
    }

    function prevPhoto() {
        if (allPhotos.length === 0) return;
        activePhotoIndex = (activePhotoIndex - 1 + allPhotos.length) % allPhotos.length;
    }

    function nextPhoto() {
        if (allPhotos.length === 0) return;
        activePhotoIndex = (activePhotoIndex + 1) % allPhotos.length;
    }
</script>

<svelte:head>
    <title>Detail Pembelajaran #{pertemuan.id} - Kepala Seksi</title>
</svelte:head>

<div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div class="space-y-1">
                <Button
                    variant="ghost"
                    size="sm"
                    class="-ml-2 mb-1 text-muted-foreground"
                    onclick={() => router.visit('/kepala-seksi/pembelajaran')}
                >
                    <ArrowLeft class="mr-1.5 size-4" />
                    Kembali ke Daftar Pembelajaran
                </Button>
                <Heading
                    title={pertemuan.jadwal?.mata_pelajaran?.nama ?? 'Detail Pertemuan Pembelajaran'}
                    description="Laporan lengkap hasil pembelajaran, rekapitulasi presensi dan penilaian peserta, serta dokumentasi galeri foto."
                />
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <Badge
                    variant="default"
                    class="bg-emerald-600 px-3 py-1 text-xs hover:bg-emerald-600"
                >
                    <CheckCircle2 class="mr-1.5 size-3.5" />
                    Disetujui (ACC) Pekerja Sosial
                </Badge>
                <Badge variant="outline" class="px-3 py-1 text-xs">
                    <Images class="mr-1.5 size-3.5 text-primary" />
                    {allPhotos.length} Foto Dokumentasi
                </Badge>
            </div>
        </div>

        <!-- Informasi Pertemuan & Verifikasi -->
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-base">
                    <BookOpen class="size-5 text-primary" />
                    Informasi Pertemuan Pembelajaran
                </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg border p-3.5">
                        <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                            <BookOpen class="size-3.5" />
                            <span>Mata Pelajaran & Disabilitas</span>
                        </div>
                        <div class="mt-1 font-semibold">
                            {pertemuan.jadwal?.mata_pelajaran?.nama ?? '-'}
                        </div>
                        <div class="mt-1">
                            <Badge variant="outline" class="text-[11px]">
                                {formatDisabilitas(pertemuan.jadwal?.mata_pelajaran?.jenis_disabilitas)}
                            </Badge>
                        </div>
                    </div>

                    <div class="rounded-lg border p-3.5">
                        <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                            <Calendar class="size-3.5" />
                            <span>Tanggal & Waktu Pertemuan</span>
                        </div>
                        <div class="mt-1 font-semibold">{formatDate(pertemuan.tanggal)}</div>
                        <div class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                            <span class="inline-flex items-center gap-1">
                                <Clock class="size-3" />
                                {pertemuan.jadwal?.hari ?? '-'}, {formatTime(pertemuan.jadwal?.jam_mulai)} - {formatTime(pertemuan.jadwal?.jam_selesai)} WIB
                            </span>
                            {#if pertemuan.jadwal?.ruangan}
                                <span class="inline-flex items-center gap-1">
                                    <MapPin class="size-3" />
                                    {pertemuan.jadwal.ruangan}
                                </span>
                            {/if}
                        </div>
                    </div>

                    <div class="rounded-lg border p-3.5">
                        <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                            <User class="size-3.5" />
                            <span>Instruktur & Pekerja Sosial</span>
                        </div>
                        <div class="mt-1 text-sm font-semibold">
                            Instruktur: {pertemuan.instruktur?.name ?? '-'}
                        </div>
                        <div class="mt-1 flex items-center gap-1 text-xs font-medium text-emerald-700 dark:text-emerald-400">
                            <UserCheck class="size-3.5" />
                            <span>
                                ACC oleh: {pertemuan.pekerja_sosial?.name ?? 'Pekerja Sosial'}
                                {#if pertemuan.approved_at}
                                    ({formatDate(pertemuan.approved_at)})
                                {/if}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-lg border bg-muted/20 p-3.5 text-sm">
                        <div class="mb-1 flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                            <FileText class="size-3.5" />
                            <span>Keterangan / Materi Pertemuan</span>
                        </div>
                        <p class="leading-relaxed">
                            {pertemuan.keterangan || 'Tidak ada catatan materi khusus pada pertemuan ini.'}
                        </p>
                    </div>

                    <div class="rounded-lg border border-emerald-200 bg-emerald-50/40 p-3.5 text-sm dark:border-emerald-900/40 dark:bg-emerald-950/20">
                        <div class="mb-1 flex items-center gap-1.5 text-xs font-medium text-emerald-800 dark:text-emerald-300">
                            <CheckCircle2 class="size-3.5" />
                            <span>Catatan Verifikasi Pekerja Sosial</span>
                        </div>
                        <p class="leading-relaxed text-emerald-900 dark:text-emerald-200">
                            {pertemuan.catatan_pekerja_sosial || 'Telah diverifikasi dan disetujui (ACC) oleh Pekerja Sosial.'}
                        </p>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Rekap Presensi & Tabel Peserta -->
        <Card>
            <CardHeader class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <div>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <Users class="size-5 text-primary" />
                        Daftar Presensi & Penilaian Kualitatif Peserta ({pesertaList.length})
                    </CardTitle>
                    <CardDescription>
                        Data kehadiran dan hasil evaluasi peserta pada pertemuan pembelajaran ini.
                    </CardDescription>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Badge
                        variant="outline"
                        class="border-emerald-200 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300"
                    >
                        Hadir: {hadirCount}
                    </Badge>
                    <Badge
                        variant="outline"
                        class="border-amber-200 bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300"
                    >
                        Izin: {izinCount}
                    </Badge>
                    <Badge
                        variant="outline"
                        class="border-rose-200 bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-300"
                    >
                        Absen: {absenCount}
                    </Badge>
                </div>
            </CardHeader>
            <CardContent>
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-12 text-center">No</TableHead>
                                <TableHead>No. Pendaftaran</TableHead>
                                <TableHead>Nama Peserta</TableHead>
                                <TableHead>Jenis Disabilitas</TableHead>
                                <TableHead>Presensi</TableHead>
                                <TableHead>Nilai Kualitatif</TableHead>
                                <TableHead>Keterangan Evaluasi</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {#if pesertaList.length === 0}
                                <TableRow>
                                    <TableCell colspan={7} class="h-24 text-center text-muted-foreground">
                                        Tidak ada data peserta pada pertemuan ini.
                                    </TableCell>
                                </TableRow>
                            {:else}
                                {#each pesertaList as detail, idx (detail.id)}
                                    <TableRow>
                                        <TableCell class="text-center font-medium">
                                            {idx + 1}
                                        </TableCell>
                                        <TableCell class="font-mono text-xs">
                                            {detail.pendaftaran_peserta?.no_pendaftaran ?? '-'}
                                        </TableCell>
                                        <TableCell class="font-medium">
                                            {detail.pendaftaran_peserta?.nama ?? detail.pendaftaran_peserta?.nama_lengkap ?? '-'}
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="outline">
                                                {detail.pendaftaran_peserta?.jenis_disabilitas?.nama ?? '-'}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            {#if detail.presensi === 'hadir'}
                                                <Badge
                                                    variant="default"
                                                    class="bg-emerald-600 hover:bg-emerald-600"
                                                >
                                                    {presensiLabels[detail.presensi]}
                                                </Badge>
                                            {:else if detail.presensi === 'izin'}
                                                <Badge
                                                    variant="secondary"
                                                    class="bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300"
                                                >
                                                    {presensiLabels[detail.presensi]}
                                                </Badge>
                                            {:else}
                                                <Badge variant="destructive">
                                                    {presensiLabels[detail.presensi] ?? detail.presensi}
                                                </Badge>
                                            {/if}
                                        </TableCell>
                                        <TableCell>
                                            {@const nilaiKey = detail.nilai ?? detail.nilai_kualitatif ?? ''}
                                            <Badge variant="outline" class="font-medium">
                                                {nilaiLabels[nilaiKey] || nilaiKey || '-'}
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-sm text-muted-foreground">
                                            {detail.keterangan || '-'}
                                        </TableCell>
                                    </TableRow>
                                {/each}
                            {/if}
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>

        <!-- Galeri Foto Pembelajaran -->
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-base">
                    <Images class="size-5 text-primary" />
                    Galeri Foto Pembelajaran ({galeriList.length} Album &bull; {allPhotos.length} Foto)
                </CardTitle>
                <CardDescription>
                    Dokumentasi foto kegiatan pada pertemuan pembelajaran ini yang diunggah oleh Administrator.
                </CardDescription>
            </CardHeader>
            <CardContent>
                {#if galeriList.length === 0}
                    <div class="flex flex-col items-center justify-center rounded-xl border border-dashed py-12 text-center">
                        <div class="flex size-12 items-center justify-center rounded-full bg-muted text-muted-foreground">
                            <ImageIcon class="size-6" />
                        </div>
                        <h3 class="mt-3 text-sm font-semibold">Belum Ada Galeri Foto</h3>
                        <p class="mt-1 max-w-md text-xs text-muted-foreground">
                            Belum ada dokumentasi galeri foto yang diunggah untuk pertemuan pembelajaran ini.
                        </p>
                    </div>
                {:else}
                    <div class="space-y-6">
                        {#each galeriList as galeri (galeri.id)}
                            <div class="rounded-xl border p-4">
                                <div class="mb-4 flex flex-col justify-between gap-2 border-b pb-3 sm:flex-row sm:items-center">
                                    <div>
                                        <h4 class="font-semibold text-foreground">{galeri.judul}</h4>
                                        {#if galeri.keterangan}
                                            <p class="mt-0.5 text-xs text-muted-foreground">
                                                {galeri.keterangan}
                                            </p>
                                        {/if}
                                    </div>
                                    <div class="flex items-center gap-2">
                                        {#if galeri.creator?.name}
                                            <span class="text-xs text-muted-foreground">
                                                Oleh: {galeri.creator.name}
                                            </span>
                                        {/if}
                                        <Badge variant="secondary" class="text-xs">
                                            <Images class="mr-1 size-3" />
                                            {galeri.foto?.length ?? 0} Foto
                                        </Badge>
                                    </div>
                                </div>

                                {#if !galeri.foto || galeri.foto.length === 0}
                                    <p class="py-4 text-center text-xs text-muted-foreground">
                                        Tidak ada foto dalam album galeri ini.
                                    </p>
                                {:else}
                                    <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                                        {#each galeri.foto as foto, idx (foto.id)}
                                            <button
                                                type="button"
                                                onclick={() => openLightboxByPhotoId(foto.id)}
                                                class="group relative aspect-4/3 overflow-hidden rounded-lg border bg-muted text-left focus:ring-2 focus:ring-primary focus:outline-none"
                                            >
                                                <img
                                                    src={`/storage/${foto.file_path}`}
                                                    alt={foto.nama_file ?? `Foto ${idx + 1}`}
                                                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                                />
                                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 transition-opacity group-hover:opacity-100"></div>
                                                <div class="absolute right-2 bottom-2 left-2 flex items-center justify-between text-white opacity-0 transition-opacity group-hover:opacity-100">
                                                    <span class="truncate text-xs font-medium">
                                                        {foto.nama_file ?? `Foto #${idx + 1}`}
                                                    </span>
                                                    <Badge variant="secondary" class="text-[10px]">
                                                        Perbesar
                                                    </Badge>
                                                </div>
                                            </button>
                                        {/each}
                                    </div>
                                {/if}
                            </div>
                        {/each}
                    </div>
                {/if}
            </CardContent>
        </Card>
    </div>

    <!-- Lightbox Pratinjau Foto -->
    <Dialog.Root bind:open={lightboxOpen}>
        <Dialog.Content class="sm:max-w-4xl">
            <Dialog.Header>
                <Dialog.Title>
                    {activePhoto?.galeriJudul ?? 'Dokumentasi Foto'} — Foto {activePhotoIndex + 1} dari {allPhotos.length}
                </Dialog.Title>
                {#if activePhoto?.nama_file}
                    <Dialog.Description>{activePhoto.nama_file}</Dialog.Description>
                {/if}
            </Dialog.Header>

            {#if activePhoto}
                <div class="relative flex max-h-[70vh] items-center justify-center overflow-hidden rounded-lg bg-black/95">
                    <img
                        src={`/storage/${activePhoto.file_path}`}
                        alt={activePhoto.nama_file ?? 'Foto Galeri'}
                        class="max-h-[70vh] w-auto object-contain"
                    />

                    {#if allPhotos.length > 1}
                        <Button
                            type="button"
                            variant="secondary"
                            size="icon"
                            class="absolute left-3 rounded-full opacity-90 hover:opacity-100"
                            onclick={prevPhoto}
                        >
                            <ChevronLeft class="size-5" />
                        </Button>
                        <Button
                            type="button"
                            variant="secondary"
                            size="icon"
                            class="absolute right-3 rounded-full opacity-90 hover:opacity-100"
                            onclick={nextPhoto}
                        >
                            <ChevronRight class="size-5" />
                        </Button>
                    {/if}
                </div>

                <Dialog.Footer class="flex items-center justify-between sm:justify-between">
                    <div class="text-xs text-muted-foreground">
                        Gunakan tombol panah kiri/kanan untuk berpindah foto.
                    </div>
                    <div class="flex gap-2">
                        <a
                            href={`/storage/${activePhoto.file_path}`}
                            download={activePhoto.nama_file ?? `foto-${activePhoto.id}.jpg`}
                            target="_blank"
                            rel="noreferrer"
                            class="border-input bg-background hover:bg-accent hover:text-accent-foreground inline-flex h-9 items-center justify-center rounded-md border px-3 text-sm font-medium transition-colors"
                        >
                            <Download class="mr-2 size-4" />
                            Unduh Foto
                        </a>
                        <Button type="button" variant="secondary" onclick={() => (lightboxOpen = false)}>
                            Tutup
                        </Button>
                    </div>
                </Dialog.Footer>
            {/if}
        </Dialog.Content>
    </Dialog.Root>
