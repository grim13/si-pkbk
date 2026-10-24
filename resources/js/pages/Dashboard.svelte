<script module lang="ts">
    import { dashboard } from '@/routes';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    };
</script>

<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import {
        AlertCircle,
        ArrowRight,
        BookOpen,
        Calendar,
        CheckCircle2,
        ClipboardCheck,
        Clock,
        Eye,
        FileText,
        GraduationCap,
        Images,
        MapPin,
        Shield,
        User,
        UserCheck,
        Users,
    } from '@lucide/svelte';
    import AppHead from '@/components/AppHead.svelte';
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
    import {
        Table,
        TableBody,
        TableCell,
        TableHead,
        TableHeader,
        TableRow,
    } from '@/components/ui/table';

    let {
        role = 'umum',
        dashboardData = {},
    }: {
        role?: 'administrator' | 'wali' | 'pekerja_sosial' | 'instruktur' | 'kepala_seksi' | 'umum';
        dashboardData?: Record<string, any>;
    } = $props();

    const user = $derived((page.props.auth as any)?.user);
    const stats = $derived(dashboardData?.stats ?? {});

    const roleTitles: Record<string, { title: string; subtitle: string }> = {
        administrator: {
            title: 'Dashboard Administrator',
            subtitle:
                'Ringkasan operasional pendaftaran peserta, jadwal pembelajaran, manajemen pengguna, dan galeri dokumentasi.',
        },
        wali: {
            title: 'Dashboard Wali Peserta',
            subtitle:
                'Pantau status pendaftaran, perkembangan kehadiran & evaluasi belajar anak, serta dokumentasi galeri kegiatan.',
        },
        pekerja_sosial: {
            title: 'Dashboard Pekerja Sosial',
            subtitle:
                'Kelola asesmen calon peserta rehabilitasi dan verifikasi (approval) laporan pertemuan pembelajaran instruktur.',
        },
        instruktur: {
            title: 'Dashboard Instruktur Pembelajaran',
            subtitle:
                'Ringkasan jadwal kelas yang Anda ampu, pencatatan presensi & nilai kualitatif peserta, serta status verifikasi laporan.',
        },
        kepala_seksi: {
            title: 'Dashboard Kepala Seksi',
            subtitle:
                'Pantau penentuan kelulusan peserta pasca-asesmen serta laporan pertemuan pembelajaran yang telah di-ACC beserta galeri foto.',
        },
        umum: {
            title: 'Dashboard SI-PKBK',
            subtitle: 'Selamat datang di Sistem Informasi Pelayanan dan Rehabilitasi Sosial.',
        },
    };

    const statusPendaftaranLabels: Record<string, string> = {
        draft: 'Draft',
        menunggu_verifikasi: 'Menunggu Verifikasi',
        perbaikan_berkas: 'Perbaikan Berkas',
        asesmen: 'Tahap Asesmen',
        selesai_asesmen: 'Menunggu Kelulusan',
        diterima: 'Diterima / Aktif',
        ditolak: 'Ditolak',
    };

    const statusPertemuanLabels: Record<string, string> = {
        draft: 'Draft',
        pengajuan: 'Menunggu Approval',
        perbaikan: 'Perlu Perbaikan',
        acc: 'Disetujui (ACC)',
    };

    const nilaiLabels: Record<string, string> = {
        sangat_baik: 'Sangat Baik',
        baik: 'Baik',
        cukup: 'Cukup',
        kurang: 'Kurang',
    };

    function formatDate(dateString?: string | null) {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    }

    function formatTime(time?: string | null) {
        if (!time) return '-';
        return time.slice(0, 5);
    }

    function formatDisabilitas(jd?: any): string {
        if (!jd) return 'Umum';
        if (Array.isArray(jd)) {
            return jd.length > 0 ? jd.map((d) => d.nama).join(', ') : 'Umum';
        }
        return jd.nama || 'Umum';
    }
</script>

<AppHead title={roleTitles[role]?.title ?? 'Dashboard'} />

<div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
    <!-- Header Sambutan Sesuai Role -->
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <Heading
                title={`Selamat Datang, ${user?.name ?? 'Pengguna'}`}
                description={roleTitles[role]?.subtitle ?? roleTitles.umum.subtitle}
            />
        </div>
        <div class="flex items-center gap-2">
            <Badge variant="outline" class="px-3 py-1 text-xs font-medium">
                {roleTitles[role]?.title ?? 'Dashboard'}
            </Badge>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- 1. DASHBOARD ADMINISTRATOR                 -->
    <!-- ========================================== -->
    {#if role === 'administrator'}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card class="border-amber-200 bg-amber-50/40 dark:border-amber-900/40 dark:bg-amber-950/20">
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-amber-800 dark:text-amber-300">
                            Menunggu Verifikasi Berkas
                        </p>
                        <p class="mt-1 text-2xl font-bold text-amber-900 dark:text-amber-200">
                            {stats.menunggu_verifikasi ?? 0}
                        </p>
                        <p class="mt-0.5 text-[11px] text-muted-foreground">
                            Perbaikan berkas: {stats.perbaikan_berkas ?? 0}
                        </p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">
                        <FileText class="size-5" />
                    </div>
                </CardContent>
            </Card>

            <Card class="border-emerald-200 bg-emerald-50/40 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-emerald-800 dark:text-emerald-300">
                            Peserta Diterima / Aktif
                        </p>
                        <p class="mt-1 text-2xl font-bold text-emerald-900 dark:text-emerald-200">
                            {stats.peserta_diterima ?? 0}
                        </p>
                        <p class="mt-0.5 text-[11px] text-muted-foreground">
                            Dari {stats.total_pendaftaran ?? 0} total pendaftar
                        </p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                        <UserCheck class="size-5" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">
                            Jadwal & Mata Pelajaran
                        </p>
                        <p class="mt-1 text-2xl font-bold">
                            {stats.total_jadwal ?? 0}
                            <span class="text-xs font-normal text-muted-foreground">jadwal</span>
                        </p>
                        <p class="mt-0.5 text-[11px] text-muted-foreground">
                            {stats.total_mata_pelajaran ?? 0} mapel &bull; {stats.total_pertemuan_acc ?? 0} pertemuan ACC
                        </p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <Calendar class="size-5" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">
                            Galeri & Pengguna Sistem
                        </p>
                        <p class="mt-1 text-2xl font-bold">
                            {stats.total_galeri ?? 0}
                            <span class="text-xs font-normal text-muted-foreground">
                                album ({stats.total_foto ?? 0} foto)
                            </span>
                        </p>
                        <p class="mt-0.5 text-[11px] text-muted-foreground">
                            {stats.total_users ?? 0} akun ({stats.total_wali ?? 0} wali)
                        </p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                        <Images class="size-5" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Pintasan Cepat Admin -->
        <div class="flex flex-wrap gap-2">
            <Button size="sm" onclick={() => router.visit('/admin/pendaftaran')}>
                <FileText class="mr-1.5 size-4" />
                Verifikasi Pendaftaran
            </Button>
            <Button size="sm" variant="outline" onclick={() => router.visit('/admin/galeri')}>
                <Images class="mr-1.5 size-4" />
                Kelola Galeri Pembelajaran
            </Button>
            <Button size="sm" variant="outline" onclick={() => router.visit('/jadwal')}>
                <Calendar class="mr-1.5 size-4" />
                Jadwal Pembelajaran
            </Button>
            <Button size="sm" variant="outline" onclick={() => router.visit('/mata-pelajaran')}>
                <BookOpen class="mr-1.5 size-4" />
                Mata Pelajaran
            </Button>
            <Button size="sm" variant="outline" onclick={() => router.visit('/user-master')}>
                <Users class="mr-1.5 size-4" />
                User Master
            </Button>
            <Button size="sm" variant="outline" onclick={() => router.visit('/role-master')}>
                <Shield class="mr-1.5 size-4" />
                Role Master
            </Button>
        </div>

        <div class="grid gap-6 lg:grid-cols-12">
            <!-- Tabel Pendaftaran Terbaru -->
            <Card class="lg:col-span-7">
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="text-base">Pendaftaran Peserta Terbaru</CardTitle>
                        <CardDescription>
                            Prioritas pendaftaran yang menunggu verifikasi berkas administrasi.
                        </CardDescription>
                    </div>
                    <Button
                        variant="ghost"
                        size="sm"
                        onclick={() => router.visit('/admin/pendaftaran')}
                    >
                        Lihat Semua
                        <ArrowRight class="ml-1.5 size-3.5" />
                    </Button>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>No. Daftar</TableHead>
                                    <TableHead>Nama Calon Peserta</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {#if (dashboardData?.recent_pendaftaran ?? []).length === 0}
                                    <TableRow>
                                        <TableCell colspan={4} class="h-24 text-center text-muted-foreground">
                                            Belum ada data pendaftaran peserta.
                                        </TableCell>
                                    </TableRow>
                                {:else}
                                    {#each dashboardData.recent_pendaftaran as item (item.id)}
                                        <TableRow>
                                            <TableCell class="font-mono text-xs">
                                                {item.no_pendaftaran ?? '-'}
                                            </TableCell>
                                            <TableCell>
                                                <div class="font-medium">{item.nama}</div>
                                                <div class="text-xs text-muted-foreground">
                                                    Wali: {item.wali?.user?.name ?? '-'}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant={item.status_pendaftaran === 'menunggu_verifikasi'
                                                        ? 'default'
                                                        : 'outline'}
                                                    class="text-[11px]"
                                                >
                                                    {statusPendaftaranLabels[item.status_pendaftaran] ??
                                                        item.status_pendaftaran}
                                                </Badge>
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onclick={() =>
                                                        router.visit(`/admin/pendaftaran/${item.id}`)}
                                                >
                                                    Periksa
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

            <!-- Galeri Dokumentasi Terbaru -->
            <Card class="lg:col-span-5">
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="text-base">Galeri Pembelajaran Terbaru</CardTitle>
                        <CardDescription>Album dokumentasi kegiatan kelas terkini.</CardDescription>
                    </div>
                    <Button
                        variant="ghost"
                        size="sm"
                        onclick={() => router.visit('/admin/galeri')}
                    >
                        Kelola Galeri
                        <ArrowRight class="ml-1.5 size-3.5" />
                    </Button>
                </CardHeader>
                <CardContent>
                    {#if (dashboardData?.recent_galeri ?? []).length === 0}
                        <div class="flex h-36 flex-col items-center justify-center rounded-lg border border-dashed text-center text-xs text-muted-foreground">
                            Belum ada galeri dokumentasi yang diunggah.
                        </div>
                    {:else}
                        <div class="space-y-3">
                            {#each dashboardData.recent_galeri as g (g.id)}
                                <div class="flex items-center justify-between gap-3 rounded-lg border p-2.5">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        {#if g.foto?.[0]}
                                            <img
                                                src={`/storage/${g.foto[0].file_path}`}
                                                alt={g.judul}
                                                class="size-12 shrink-0 rounded-md object-cover"
                                            />
                                        {:else}
                                            <div class="flex size-12 shrink-0 items-center justify-center rounded-md bg-muted text-muted-foreground">
                                                <Images class="size-5" />
                                            </div>
                                        {/if}
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold">{g.judul}</p>
                                            <p class="truncate text-xs text-muted-foreground">
                                                {g.pertemuan?.jadwal?.mata_pelajaran?.nama ?? '-'} &bull; {g.foto_count ?? g.foto?.length ?? 0} foto
                                            </p>
                                        </div>
                                    </div>
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        onclick={() => router.visit(`/admin/galeri/${g.id}`)}
                                    >
                                        <Eye class="size-4" />
                                    </Button>
                                </div>
                            {/each}
                        </div>
                    {/if}
                </CardContent>
            </Card>
        </div>

    <!-- ========================================== -->
    <!-- 2. DASHBOARD WALI PESERTA                  -->
    <!-- ========================================== -->
    {:else if role === 'wali'}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">
                            Anak / Peserta Terdaftar
                        </p>
                        <p class="mt-1 text-2xl font-bold">{stats.total_peserta ?? 0}</p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <User class="size-5" />
                    </div>
                </CardContent>
            </Card>

            <Card class="border-emerald-200 bg-emerald-50/40 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-emerald-800 dark:text-emerald-300">
                            Peserta Diterima & Aktif
                        </p>
                        <p class="mt-1 text-2xl font-bold text-emerald-900 dark:text-emerald-200">
                            {stats.peserta_diterima ?? 0}
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
                            Total Kehadiran Kelas
                        </p>
                        <p class="mt-1 text-2xl font-bold">
                            {stats.total_hadir ?? 0}
                            <span class="text-xs font-normal text-muted-foreground">pertemuan</span>
                        </p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                        <BookOpen class="size-5" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">
                            Galeri Kegiatan Anak
                        </p>
                        <p class="mt-1 text-2xl font-bold">
                            {stats.total_galeri ?? 0}
                            <span class="text-xs font-normal text-muted-foreground">album</span>
                        </p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300">
                        <Images class="size-5" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="flex flex-wrap gap-2">
            <Button size="sm" onclick={() => router.visit('/pendaftaran')}>
                <FileText class="mr-1.5 size-4" />
                Kelola Pendaftaran Anak
            </Button>
            <Button size="sm" variant="outline" onclick={() => router.visit('/galeri')}>
                <Images class="mr-1.5 size-4" />
                Lihat Galeri Pembelajaran
            </Button>
        </div>

        <!-- Status Pendaftaran Anak -->
        <Card>
            <CardHeader class="flex flex-row items-center justify-between">
                <div>
                    <CardTitle class="text-base">Status Pendaftaran & Kehadiran Anak</CardTitle>
                    <CardDescription>
                        Informasi tahapan pendaftaran, jadwal asesmen, dan rekap kehadiran belajar.
                    </CardDescription>
                </div>
                <Button variant="ghost" size="sm" onclick={() => router.visit('/pendaftaran')}>
                    Detail Pendaftaran
                    <ArrowRight class="ml-1.5 size-3.5" />
                </Button>
            </CardHeader>
            <CardContent>
                {#if (dashboardData?.peserta_list ?? []).length === 0}
                    <div class="flex flex-col items-center justify-center rounded-lg border border-dashed py-10 text-center">
                        <p class="text-sm font-medium">Belum ada peserta yang didaftarkan</p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Silakan ajukan pendaftaran peserta baru melalui menu Pendaftaran.
                        </p>
                        <Button class="mt-3" size="sm" onclick={() => router.visit('/pendaftaran')}>
                            Mulai Pendaftaran
                        </Button>
                    </div>
                {:else}
                    <div class="grid gap-4 md:grid-cols-2">
                        {#each dashboardData.peserta_list as p (p.id)}
                            <div class="flex flex-col justify-between rounded-xl border p-4">
                                <div>
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <span class="font-mono text-xs text-muted-foreground">
                                                No. Daftar: {p.no_pendaftaran ?? '-'}
                                            </span>
                                            <h4 class="text-base font-bold">{p.nama}</h4>
                                        </div>
                                        <Badge
                                            variant={p.status_pendaftaran === 'diterima'
                                                ? 'default'
                                                : 'outline'}
                                            class={p.status_pendaftaran === 'diterima'
                                                ? 'bg-emerald-600 hover:bg-emerald-600'
                                                : ''}
                                        >
                                            {statusPendaftaranLabels[p.status_pendaftaran] ??
                                                p.status_pendaftaran}
                                        </Badge>
                                    </div>

                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        {#if p.jenis_disabilitas?.nama}
                                            <Badge variant="secondary" class="text-xs">
                                                {p.jenis_disabilitas.nama}
                                            </Badge>
                                        {/if}
                                        <Badge variant="outline" class="text-xs">
                                            Hadir: {p.hadir_count ?? 0}
                                        </Badge>
                                        <Badge variant="outline" class="text-xs">
                                            Izin: {p.izin_count ?? 0}
                                        </Badge>
                                    </div>

                                    {#if p.jadwal_asesmen && p.status_pendaftaran === 'asesmen'}
                                        <div class="mt-3 rounded-md border border-blue-200 bg-blue-50/50 p-2.5 text-xs dark:border-blue-900/40 dark:bg-blue-950/20">
                                            <strong>Jadwal Asesmen:</strong> {formatDate(p.jadwal_asesmen)}
                                            {#if p.tempat_asesmen}
                                                di {p.tempat_asesmen}
                                            {/if}
                                        </div>
                                    {/if}

                                    {#if p.catatan_verifikasi && p.status_pendaftaran === 'perbaikan_berkas'}
                                        <div class="mt-3 rounded-md border border-rose-200 bg-rose-50/50 p-2.5 text-xs text-rose-800 dark:border-rose-900/40 dark:bg-rose-950/20 dark:text-rose-200">
                                            <strong>Catatan Perbaikan:</strong> {p.catatan_verifikasi}
                                        </div>
                                    {/if}
                                </div>

                                <div class="mt-4 flex justify-end border-t pt-3">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        onclick={() =>
                                            router.visit(`/pendaftaran?pendaftaran_id=${p.id}`)}
                                    >
                                        Lihat Status Pendaftaran
                                    </Button>
                                </div>
                            </div>
                        {/each}
                    </div>
                {/if}
            </CardContent>
        </Card>

        <div class="grid gap-6 lg:grid-cols-12">
            <!-- Riwayat Evaluasi Pembelajaran Anak -->
            <Card class="lg:col-span-7">
                <CardHeader>
                    <CardTitle class="text-base">Riwayat Pembelajaran & Evaluasi Anak</CardTitle>
                    <CardDescription>
                        Rekap kehadiran dan penilaian kualitatif dari pertemuan yang telah diverifikasi.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Tanggal</TableHead>
                                    <TableHead>Peserta & Mata Pelajaran</TableHead>
                                    <TableHead>Presensi</TableHead>
                                    <TableHead>Nilai Evaluasi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {#if (dashboardData?.riwayat_pembelajaran ?? []).length === 0}
                                    <TableRow>
                                        <TableCell colspan={4} class="h-24 text-center text-muted-foreground">
                                            Belum ada riwayat pertemuan pembelajaran terverifikasi.
                                        </TableCell>
                                    </TableRow>
                                {:else}
                                    {#each dashboardData.riwayat_pembelajaran as r (r.id)}
                                        <TableRow>
                                            <TableCell class="text-xs font-medium">
                                                {formatDate(r.pertemuan?.tanggal)}
                                            </TableCell>
                                            <TableCell>
                                                <div class="font-medium">
                                                    {r.pertemuan?.jadwal?.mata_pelajaran?.nama ?? '-'}
                                                </div>
                                                <div class="text-xs text-muted-foreground">
                                                    Peserta: {r.pendaftaran_peserta?.nama ?? '-'}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant={r.presensi === 'hadir' ? 'default' : 'outline'}
                                                    class={r.presensi === 'hadir'
                                                        ? 'bg-emerald-600 hover:bg-emerald-600'
                                                        : ''}
                                                >
                                                    {r.presensi?.toUpperCase()}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <span class="text-xs font-medium">
                                                    {nilaiLabels[r.nilai] ?? r.nilai ?? '-'}
                                                </span>
                                                {#if r.keterangan}
                                                    <p class="line-clamp-1 text-[11px] text-muted-foreground">
                                                        {r.keterangan}
                                                    </p>
                                                {/if}
                                            </TableCell>
                                        </TableRow>
                                    {/each}
                                {/if}
                            </TableBody>
                        </Table>
                    </div>
                </CardContent>
            </Card>

            <!-- Galeri Foto Kegiatan Anak -->
            <Card class="lg:col-span-5">
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="text-base">Galeri Dokumentasi Anak</CardTitle>
                        <CardDescription>
                            Foto kegiatan dari pertemuan di mana anak Anda hadir.
                        </CardDescription>
                    </div>
                    <Button variant="ghost" size="sm" onclick={() => router.visit('/galeri')}>
                        Semua Galeri
                        <ArrowRight class="ml-1.5 size-3.5" />
                    </Button>
                </CardHeader>
                <CardContent>
                    {#if (dashboardData?.recent_galeri ?? []).length === 0}
                        <div class="flex h-36 flex-col items-center justify-center rounded-lg border border-dashed p-4 text-center text-xs text-muted-foreground">
                            Belum ada galeri foto pertemuan yang tersedia. Galeri dapat dilihat apabila anak terdaftar di kelas dan tercatat hadir.
                        </div>
                    {:else}
                        <div class="space-y-3">
                            {#each dashboardData.recent_galeri as g (g.id)}
                                <div class="flex items-center justify-between gap-3 rounded-lg border p-2.5">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        {#if g.foto?.[0]}
                                            <img
                                                src={`/storage/${g.foto[0].file_path}`}
                                                alt={g.judul}
                                                class="size-12 shrink-0 rounded-md object-cover"
                                            />
                                        {:else}
                                            <div class="flex size-12 shrink-0 items-center justify-center rounded-md bg-muted text-muted-foreground">
                                                <Images class="size-5" />
                                            </div>
                                        {/if}
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold">{g.judul}</p>
                                            <p class="truncate text-xs text-muted-foreground">
                                                {g.pertemuan?.jadwal?.mata_pelajaran?.nama ?? '-'} &bull; {g.foto_count ?? g.foto?.length ?? 0} foto
                                            </p>
                                        </div>
                                    </div>
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        onclick={() => router.visit(`/galeri/${g.id}`)}
                                    >
                                        Buka
                                    </Button>
                                </div>
                            {/each}
                        </div>
                    {/if}
                </CardContent>
            </Card>
        </div>

    <!-- ========================================== -->
    <!-- 3. DASHBOARD PEKERJA SOSIAL                -->
    <!-- ========================================== -->
    {:else if role === 'pekerja_sosial'}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card class="border-amber-200 bg-amber-50/40 dark:border-amber-900/40 dark:bg-amber-950/20">
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-amber-800 dark:text-amber-300">
                            Klien Menunggu Asesmen
                        </p>
                        <p class="mt-1 text-2xl font-bold text-amber-900 dark:text-amber-200">
                            {stats.menunggu_asesmen ?? 0}
                        </p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">
                        <FileText class="size-5" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">
                            Asesmen Selesai
                        </p>
                        <p class="mt-1 text-2xl font-bold">{stats.selesai_asesmen ?? 0}</p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <UserCheck class="size-5" />
                    </div>
                </CardContent>
            </Card>

            <Card class="border-blue-200 bg-blue-50/40 dark:border-blue-900/40 dark:bg-blue-950/20">
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-blue-800 dark:text-blue-300">
                            Pembelajaran Menunggu Approval
                        </p>
                        <p class="mt-1 text-2xl font-bold text-blue-900 dark:text-blue-200">
                            {stats.pertemuan_menunggu_acc ?? 0}
                        </p>
                        <p class="mt-0.5 text-[11px] text-muted-foreground">
                            Perbaikan: {stats.pertemuan_perbaikan ?? 0}
                        </p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">
                        <ClipboardCheck class="size-5" />
                    </div>
                </CardContent>
            </Card>

            <Card class="border-emerald-200 bg-emerald-50/40 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-emerald-800 dark:text-emerald-300">
                            Pembelajaran Telah Di-ACC
                        </p>
                        <p class="mt-1 text-2xl font-bold text-emerald-900 dark:text-emerald-200">
                            {stats.pertemuan_acc ?? 0}
                        </p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                        <CheckCircle2 class="size-5" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="flex flex-wrap gap-2">
            <Button size="sm" onclick={() => router.visit('/pekerja-sosial/asesmen')}>
                <FileText class="mr-1.5 size-4" />
                Menu Asesmen Klien
            </Button>
            <Button
                size="sm"
                variant="outline"
                onclick={() => router.visit('/pekerja-sosial/pembelajaran')}
            >
                <ClipboardCheck class="mr-1.5 size-4" />
                Approval Pembelajaran
            </Button>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Daftar Klien Asesmen -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="text-base">Daftar Klien Asesmen</CardTitle>
                        <CardDescription>
                            Calon peserta yang siap diasesmen atau baru selesai asesmen.
                        </CardDescription>
                    </div>
                    <Button
                        variant="ghost"
                        size="sm"
                        onclick={() => router.visit('/pekerja-sosial/asesmen')}
                    >
                        Semua Klien
                        <ArrowRight class="ml-1.5 size-3.5" />
                    </Button>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nama Klien</TableHead>
                                    <TableHead>Jadwal Asesmen</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {#if (dashboardData?.klien_asesmen ?? []).length === 0}
                                    <TableRow>
                                        <TableCell colspan={4} class="h-24 text-center text-muted-foreground">
                                            Tidak ada klien yang menunggu asesmen.
                                        </TableCell>
                                    </TableRow>
                                {:else}
                                    {#each dashboardData.klien_asesmen as k (k.id)}
                                        <TableRow>
                                            <TableCell>
                                                <div class="font-medium">{k.nama}</div>
                                                <div class="text-xs text-muted-foreground">
                                                    {k.jenis_disabilitas?.nama ?? 'Belum ditentukan'}
                                                </div>
                                            </TableCell>
                                            <TableCell class="text-xs">
                                                {formatDate(k.jadwal_asesmen)}
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant={k.status_pendaftaran === 'asesmen'
                                                        ? 'default'
                                                        : 'outline'}
                                                    class="text-[11px]"
                                                >
                                                    {statusPendaftaranLabels[k.status_pendaftaran] ??
                                                        k.status_pendaftaran}
                                                </Badge>
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onclick={() =>
                                                        router.visit(`/pekerja-sosial/asesmen/${k.id}`)}
                                                >
                                                    Asesmen
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

            <!-- Daftar Approval Pertemuan Pembelajaran -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="text-base">Verifikasi Pertemuan Pembelajaran</CardTitle>
                        <CardDescription>
                            Laporan pertemuan dari instruktur yang membutuhkan verifikasi.
                        </CardDescription>
                    </div>
                    <Button
                        variant="ghost"
                        size="sm"
                        onclick={() => router.visit('/pekerja-sosial/pembelajaran')}
                    >
                        Semua Laporan
                        <ArrowRight class="ml-1.5 size-3.5" />
                    </Button>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Tanggal & Mapel</TableHead>
                                    <TableHead>Instruktur</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {#if (dashboardData?.pertemuan_approval ?? []).length === 0}
                                    <TableRow>
                                        <TableCell colspan={4} class="h-24 text-center text-muted-foreground">
                                            Belum ada pengajuan pertemuan pembelajaran.
                                        </TableCell>
                                    </TableRow>
                                {:else}
                                    {#each dashboardData.pertemuan_approval as pt (pt.id)}
                                        <TableRow>
                                            <TableCell>
                                                <div class="font-medium">
                                                    {pt.jadwal?.mata_pelajaran?.nama ?? '-'}
                                                </div>
                                                <div class="text-xs text-muted-foreground">
                                                    {formatDate(pt.tanggal)} &bull; Hadir: {pt.hadir_count ?? 0}/{pt.total_peserta ?? 0}
                                                </div>
                                            </TableCell>
                                            <TableCell class="text-xs">
                                                {pt.instruktur?.name ?? '-'}
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant={pt.status === 'pengajuan' ? 'default' : 'outline'}
                                                    class="text-[11px]"
                                                >
                                                    {statusPertemuanLabels[pt.status] ?? pt.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onclick={() =>
                                                        router.visit(`/pekerja-sosial/pembelajaran/${pt.id}`)}
                                                >
                                                    Tinjau
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

    <!-- ========================================== -->
    <!-- 4. DASHBOARD INSTRUKTUR                    -->
    <!-- ========================================== -->
    {:else if role === 'instruktur'}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">
                            Jadwal Kelas Diampu
                        </p>
                        <p class="mt-1 text-2xl font-bold">{stats.total_jadwal ?? 0}</p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <Calendar class="size-5" />
                    </div>
                </CardContent>
            </Card>

            <Card class="border-amber-200 bg-amber-50/40 dark:border-amber-900/40 dark:bg-amber-950/20">
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-amber-800 dark:text-amber-300">
                            Draft / Perlu Perbaikan
                        </p>
                        <p class="mt-1 text-2xl font-bold text-amber-900 dark:text-amber-200">
                            {stats.perlu_tindakan ?? 0}
                        </p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">
                        <AlertCircle class="size-5" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">
                            Menunggu Verifikasi Peksos
                        </p>
                        <p class="mt-1 text-2xl font-bold">{stats.menunggu_acc ?? 0}</p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                        <Clock class="size-5" />
                    </div>
                </CardContent>
            </Card>

            <Card class="border-emerald-200 bg-emerald-50/40 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-emerald-800 dark:text-emerald-300">
                            Pertemuan Disetujui (ACC)
                        </p>
                        <p class="mt-1 text-2xl font-bold text-emerald-900 dark:text-emerald-200">
                            {stats.disetujui_acc ?? 0}
                        </p>
                        <p class="mt-0.5 text-[11px] text-muted-foreground">
                            Dari {stats.total_pertemuan ?? 0} total pertemuan
                        </p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                        <CheckCircle2 class="size-5" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="flex flex-wrap gap-2">
            <Button size="sm" onclick={() => router.visit('/instruktur/pembelajaran')}>
                <BookOpen class="mr-1.5 size-4" />
                Kelola Proses Pembelajaran
            </Button>
        </div>

        <div class="grid gap-6 lg:grid-cols-12">
            <!-- Jadwal Kelas yang Diampu Instruktur -->
            <Card class="lg:col-span-5">
                <CardHeader>
                    <CardTitle class="text-base">Jadwal Kelas Anda</CardTitle>
                    <CardDescription>
                        Pilih kelas untuk mencatat presensi dan penilaian peserta.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    {#if (dashboardData?.jadwal_list ?? []).length === 0}
                        <div class="flex h-32 items-center justify-center rounded-lg border border-dashed text-xs text-muted-foreground">
                            Belum ada jadwal kelas yang ditugaskan kepada Anda.
                        </div>
                    {:else}
                        <div class="space-y-3">
                            {#each dashboardData.jadwal_list as j (j.id)}
                                <div class="flex items-center justify-between gap-3 rounded-lg border p-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold">
                                            {j.mata_pelajaran?.nama ?? 'Mata Pelajaran'}
                                        </p>
                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                            <span class="inline-flex items-center gap-1">
                                                <Calendar class="size-3" />
                                                {j.hari} ({j.tahun})
                                            </span>
                                            <span class="inline-flex items-center gap-1">
                                                <Clock class="size-3" />
                                                {formatTime(j.jam_mulai)} - {formatTime(j.jam_selesai)}
                                            </span>
                                            {#if j.ruangan}
                                                <span class="inline-flex items-center gap-1">
                                                    <MapPin class="size-3" />
                                                    {j.ruangan}
                                                </span>
                                            {/if}
                                        </div>
                                        <div class="mt-1.5 flex items-center gap-1.5">
                                            <Badge variant="outline" class="text-[10px]">
                                                {formatDisabilitas(j.mata_pelajaran?.jenis_disabilitas)}
                                            </Badge>
                                            <Badge variant="secondary" class="text-[10px]">
                                                {j.pertemuan_count ?? 0} pertemuan
                                            </Badge>
                                        </div>
                                    </div>
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        onclick={() =>
                                            router.visit(`/instruktur/pembelajaran/jadwal/${j.id}`)}
                                    >
                                        Input
                                    </Button>
                                </div>
                            {/each}
                        </div>
                    {/if}
                </CardContent>
            </Card>

            <!-- Riwayat Pertemuan & Status Verifikasi -->
            <Card class="lg:col-span-7">
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="text-base">Pertemuan Pembelajaran Terbaru</CardTitle>
                        <CardDescription>
                            Pantau status pengajuan dan catatan verifikasi dari Pekerja Sosial.
                        </CardDescription>
                    </div>
                    <Button
                        variant="ghost"
                        size="sm"
                        onclick={() => router.visit('/instruktur/pembelajaran')}
                    >
                        Lihat Semua
                        <ArrowRight class="ml-1.5 size-3.5" />
                    </Button>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Tanggal & Kelas</TableHead>
                                    <TableHead>Presensi</TableHead>
                                    <TableHead>Status & Catatan</TableHead>
                                    <TableHead class="text-right">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {#if (dashboardData?.recent_pertemuan ?? []).length === 0}
                                    <TableRow>
                                        <TableCell colspan={4} class="h-24 text-center text-muted-foreground">
                                            Belum ada pertemuan pembelajaran yang dicatat.
                                        </TableCell>
                                    </TableRow>
                                {:else}
                                    {#each dashboardData.recent_pertemuan as pt (pt.id)}
                                        <TableRow>
                                            <TableCell>
                                                <div class="font-medium">
                                                    {pt.jadwal?.mata_pelajaran?.nama ?? '-'}
                                                </div>
                                                <div class="text-xs text-muted-foreground">
                                                    {formatDate(pt.tanggal)}
                                                </div>
                                            </TableCell>
                                            <TableCell class="text-xs">
                                                Hadir: {pt.hadir_count ?? 0} / {pt.total_peserta ?? 0}
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant={pt.status === 'perbaikan'
                                                        ? 'destructive'
                                                        : pt.status === 'acc'
                                                          ? 'default'
                                                          : 'outline'}
                                                    class={pt.status === 'acc'
                                                        ? 'bg-emerald-600 hover:bg-emerald-600'
                                                        : ''}
                                                >
                                                    {statusPertemuanLabels[pt.status] ?? pt.status}
                                                </Badge>
                                                {#if pt.catatan_pekerja_sosial}
                                                    <p class="mt-1 line-clamp-1 max-w-[200px] text-[11px] text-muted-foreground">
                                                        Catatan: {pt.catatan_pekerja_sosial}
                                                    </p>
                                                {/if}
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onclick={() =>
                                                        router.visit(
                                                            `/instruktur/pembelajaran/jadwal/${pt.jadwal_id}?pertemuan_id=${pt.id}`,
                                                        )}
                                                >
                                                    Buka
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

    <!-- ========================================== -->
    <!-- 5. DASHBOARD KEPALA SEKSI                  -->
    <!-- ========================================== -->
    {:else if role === 'kepala_seksi'}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card class="border-amber-200 bg-amber-50/40 dark:border-amber-900/40 dark:bg-amber-950/20">
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-amber-800 dark:text-amber-300">
                            Menunggu Keputusan Kelulusan
                        </p>
                        <p class="mt-1 text-2xl font-bold text-amber-900 dark:text-amber-200">
                            {stats.menunggu_kelulusan ?? 0}
                        </p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">
                        <GraduationCap class="size-5" />
                    </div>
                </CardContent>
            </Card>

            <Card class="border-emerald-200 bg-emerald-50/40 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-emerald-800 dark:text-emerald-300">
                            Peserta Diterima / Lulus
                        </p>
                        <p class="mt-1 text-2xl font-bold text-emerald-900 dark:text-emerald-200">
                            {stats.peserta_diterima ?? 0}
                        </p>
                        <p class="mt-0.5 text-[11px] text-muted-foreground">
                            Ditolak: {stats.peserta_ditolak ?? 0}
                        </p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                        <UserCheck class="size-5" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">
                            Pertemuan Pembelajaran ACC
                        </p>
                        <p class="mt-1 text-2xl font-bold">{stats.total_pertemuan_acc ?? 0}</p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <BookOpen class="size-5" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">
                            Galeri Dokumentasi ACC
                        </p>
                        <p class="mt-1 text-2xl font-bold">
                            {stats.total_foto ?? 0}
                            <span class="text-xs font-normal text-muted-foreground">
                                foto ({stats.total_galeri ?? 0} album)
                            </span>
                        </p>
                    </div>
                    <div class="flex size-10 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                        <Images class="size-5" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="flex flex-wrap gap-2">
            <Button size="sm" onclick={() => router.visit('/kepala-seksi/kelulusan')}>
                <GraduationCap class="mr-1.5 size-4" />
                Penentuan Kelulusan Peserta
            </Button>
            <Button
                size="sm"
                variant="outline"
                onclick={() => router.visit('/kepala-seksi/pembelajaran')}
            >
                <BookOpen class="mr-1.5 size-4" />
                Data Pembelajaran (ACC) & Galeri
            </Button>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Peserta Menunggu Penentuan Kelulusan -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="text-base">Penentuan Kelulusan Peserta</CardTitle>
                        <CardDescription>
                            Peserta yang telah selesai asesmen dan menunggu keputusan.
                        </CardDescription>
                    </div>
                    <Button
                        variant="ghost"
                        size="sm"
                        onclick={() => router.visit('/kepala-seksi/kelulusan')}
                    >
                        Semua Peserta
                        <ArrowRight class="ml-1.5 size-3.5" />
                    </Button>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nama Peserta</TableHead>
                                    <TableHead>Disabilitas</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead class="text-right">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {#if (dashboardData?.peserta_kelulusan ?? []).length === 0}
                                    <TableRow>
                                        <TableCell colspan={4} class="h-24 text-center text-muted-foreground">
                                            Belum ada peserta pada tahap penentuan kelulusan.
                                        </TableCell>
                                    </TableRow>
                                {:else}
                                    {#each dashboardData.peserta_kelulusan as pk (pk.id)}
                                        <TableRow>
                                            <TableCell>
                                                <div class="font-medium">{pk.nama}</div>
                                                <div class="font-mono text-xs text-muted-foreground">
                                                    {pk.no_pendaftaran ?? '-'}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline" class="text-[11px]">
                                                    {pk.jenis_disabilitas?.nama ?? '-'}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant={pk.status_pendaftaran === 'selesai_asesmen'
                                                        ? 'default'
                                                        : 'outline'}
                                                    class="text-[11px]"
                                                >
                                                    {statusPendaftaranLabels[pk.status_pendaftaran] ??
                                                        pk.status_pendaftaran}
                                                </Badge>
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onclick={() =>
                                                        router.visit(`/kepala-seksi/kelulusan/${pk.id}`)}
                                                >
                                                    Tinjau
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

            <!-- Pertemuan Pembelajaran ACC & Galeri Terbaru -->
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <div>
                        <CardTitle class="text-base">Pembelajaran Terverifikasi (ACC)</CardTitle>
                        <CardDescription>
                            Laporan pembelajaran yang sudah di-ACC oleh Pekerja Sosial beserta galeri foto.
                        </CardDescription>
                    </div>
                    <Button
                        variant="ghost"
                        size="sm"
                        onclick={() => router.visit('/kepala-seksi/pembelajaran')}
                    >
                        Lihat Semua
                        <ArrowRight class="ml-1.5 size-3.5" />
                    </Button>
                </CardHeader>
                <CardContent>
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Tanggal & Mapel</TableHead>
                                    <TableHead>Presensi</TableHead>
                                    <TableHead>Galeri</TableHead>
                                    <TableHead class="text-right">Aksi</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {#if (dashboardData?.recent_pertemuan_acc ?? []).length === 0}
                                    <TableRow>
                                        <TableCell colspan={4} class="h-24 text-center text-muted-foreground">
                                            Belum ada pertemuan pembelajaran yang berstatus ACC.
                                        </TableCell>
                                    </TableRow>
                                {:else}
                                    {#each dashboardData.recent_pertemuan_acc as pt (pt.id)}
                                        {@const totalFoto = (pt.galeri ?? []).reduce(
                                            (acc: number, g: any) => acc + (g.foto?.length ?? 0),
                                            0,
                                        )}
                                        <TableRow>
                                            <TableCell>
                                                <div class="font-medium">
                                                    {pt.jadwal?.mata_pelajaran?.nama ?? '-'}
                                                </div>
                                                <div class="text-xs text-muted-foreground">
                                                    {formatDate(pt.tanggal)} &bull; {pt.instruktur?.name ?? '-'}
                                                </div>
                                            </TableCell>
                                            <TableCell class="text-xs">
                                                Hadir: {pt.hadir_count ?? 0}/{pt.total_peserta ?? 0}
                                            </TableCell>
                                            <TableCell>
                                                {#if totalFoto > 0}
                                                    <Badge variant="default" class="gap-1 text-[11px]">
                                                        <Images class="size-3" />
                                                        {totalFoto} Foto
                                                    </Badge>
                                                {:else}
                                                    <Badge variant="secondary" class="text-[11px]">
                                                        -
                                                    </Badge>
                                                {/if}
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <Button
                                                    size="sm"
                                                    variant="outline"
                                                    onclick={() =>
                                                        router.visit(`/kepala-seksi/pembelajaran/${pt.id}`)}
                                                >
                                                    Detail
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

    <!-- ========================================== -->
    <!-- 6. FALLBACK UMUM                           -->
    <!-- ========================================== -->
    {:else}
        <div class="grid gap-4 sm:grid-cols-3">
            <Card>
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">Peserta Aktif</p>
                        <p class="mt-1 text-2xl font-bold">{stats.total_peserta_diterima ?? 0}</p>
                    </div>
                    <Users class="size-5 text-primary" />
                </CardContent>
            </Card>
            <Card>
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">Jadwal Kelas</p>
                        <p class="mt-1 text-2xl font-bold">{stats.total_jadwal ?? 0}</p>
                    </div>
                    <Calendar class="size-5 text-primary" />
                </CardContent>
            </Card>
            <Card>
                <CardContent class="flex items-center justify-between pt-5">
                    <div>
                        <p class="text-xs font-medium text-muted-foreground">Pertemuan Terverifikasi</p>
                        <p class="mt-1 text-2xl font-bold">{stats.total_pertemuan_acc ?? 0}</p>
                    </div>
                    <CheckCircle2 class="size-5 text-primary" />
                </CardContent>
            </Card>
        </div>
    {/if}
</div>
