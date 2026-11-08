<script lang="ts">
    import { Badge } from "@/components/ui/badge";
    import { Button } from "@/components/ui/button";
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from "@/components/ui/card";
    import * as Dialog from "@/components/ui/dialog";
    import * as Table from "@/components/ui/table";
    import {
        BookOpen,
        Calendar,
        CheckCircle2,
        Clock,
        Eye,
        GraduationCap,
        MessageSquareText,
        Star,
        User,
    } from "@lucide/svelte";

    let { pendaftaran, jenis_berkas, onFixDocs } = $props();

    let dialogOpen = $state(false);
    let selectedMapel = $state<any>(null);

    const ringkasan = $derived(pendaftaran?.ringkasan_pembelajaran ?? null);
    const summary = $derived(ringkasan?.summary ?? {});
    const mataPelajaranList = $derived(ringkasan?.mata_pelajaran_list ?? []);

    function openDetailDialog(mapel: any) {
        selectedMapel = mapel;
        dialogOpen = true;
    }

    function formatDate(dateStr: string | null) {
        if (!dateStr) return "-";
        const d = new Date(dateStr);
        return (
            d.toLocaleDateString("id-ID", {
                weekday: "long",
                year: "numeric",
                month: "long",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            }) + " WIB"
        );
    }

    function formatShortDate(dateStr: string | null) {
        if (!dateStr) return "-";
        return new Date(dateStr).toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
    }

    function formatFullDate(dateStr: string | null) {
        if (!dateStr) return "-";
        return new Date(dateStr).toLocaleDateString("id-ID", {
            weekday: "long",
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
    }

    function formatTime(t: string | null) {
        if (!t) return "-";
        return t.slice(0, 5);
    }

    function nilaiBadgeClass(nilai: string | null) {
        switch (nilai) {
            case "sangat_baik":
                return "bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300";
            case "baik":
                return "bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300";
            case "cukup":
                return "bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300";
            case "kurang":
                return "bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300";
            default:
                return "bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300";
        }
    }

    function presensiBadgeClass(presensi: string | null) {
        switch (presensi) {
            case "hadir":
                return "bg-green-100 text-green-800 dark:bg-green-950/60 dark:text-green-300";
            case "izin":
                return "bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300";
            case "absen":
                return "bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300";
            default:
                return "bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300";
        }
    }

    function presensiLabel(presensi: string | null) {
        switch (presensi) {
            case "hadir":
                return "HADIR";
            case "izin":
                return "IZIN";
            case "absen":
                return "ABSEN";
            default:
                return presensi?.toUpperCase() || "-";
        }
    }
</script>

{#if pendaftaran}
    <Card>
        {#if pendaftaran.status_pendaftaran === "verifikasi_berkas"}
            <CardHeader>
                <CardTitle class="text-center text-blue-600 text-2xl mb-2"
                    >Pendaftaran Berhasil Dikirim!</CardTitle
                >
            </CardHeader>
            <CardContent class="text-center">
                <p class="mb-4">
                    Data pendaftaran atas nama <strong
                        >{pendaftaran.nama}</strong
                    >
                    sedang dalam tahap <strong>Verifikasi Berkas</strong>.
                </p>
                <p class="text-muted-foreground">
                    Admin kami sedang memeriksa dokumen yang Anda unggah. Anda
                    akan mendapatkan pemberitahuan lebih lanjut di sini setelah
                    proses verifikasi selesai.
                </p>
            </CardContent>
        {:else if pendaftaran.status_pendaftaran === "perbaikan_berkas"}
            <CardHeader>
                <CardTitle class="text-center text-orange-600 text-2xl mb-2"
                    >Perbaikan Berkas Diperlukan</CardTitle
                >
            </CardHeader>
            <CardContent class="text-center space-y-4">
                <p>
                    Beberapa dokumen pendaftaran atas nama <strong
                        >{pendaftaran.nama}</strong
                    > memerlukan perbaikan.
                </p>
                <p class="text-muted-foreground">
                    Silakan periksa kembali berkas Anda dan unggah ulang dokumen
                    yang sesuai dengan catatan dari admin.
                </p>
                <div class="mt-6">
                    <Button
                        onclick={onFixDocs}
                        size="lg"
                        class="w-full sm:w-auto"
                    >
                        Mulai Perbaikan Berkas
                    </Button>
                </div>
            </CardContent>
        {:else if pendaftaran.status_pendaftaran === "asesmen"}
            <CardHeader>
                <CardTitle class="text-center text-primary text-2xl mb-2"
                    >Jadwal Asesmen</CardTitle
                >
            </CardHeader>
            <CardContent>
                <div class="text-center mb-6">
                    <p class="mb-4">
                        Berkas Anda telah valid. Data pendaftaran atas nama <strong
                            >{pendaftaran.nama}</strong
                        >
                        kini masuk ke tahap <strong>Asesmen</strong>.
                    </p>
                    <p class="text-muted-foreground">
                        Silakan datang ke panti dengan membawa seluruh berkas
                        asli (rangkap 2) untuk melakukan tahap wawancara
                        asesmen.
                    </p>
                </div>

                <div
                    class="bg-primary/10 border border-primary/25 rounded-md p-6 text-center mb-8"
                >
                    <h4 class="font-bold text-primary mb-2">
                        Jadwal & Tempat Asesmen
                    </h4>
                    <p class="text-lg font-semibold">
                        {formatDate(pendaftaran.jadwal_asesmen)}
                    </p>
                    <p class="text-primary mt-1 font-medium">
                        {pendaftaran.tempat_asesmen}
                    </p>
                    <p class="text-destructive mt-1 font-medium">
                        Bawa semua berkas rangkap 2
                    </p>
                </div>

                <div class="bg-slate-50 border rounded-lg p-6 mb-8 text-left">
                    <h3 class="text-lg font-bold mb-4 border-b pb-2">
                        Detail Data Pendaftaran
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-muted-foreground block text-xs"
                                >No. Pendaftaran</span
                            >
                            <span class="font-medium"
                                >{pendaftaran.no_pendaftaran}</span
                            >
                        </div>
                        <div>
                            <span class="text-muted-foreground block text-xs"
                                >NIK</span
                            >
                            <span class="font-medium">{pendaftaran.nik}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground block text-xs"
                                >Nama Lengkap</span
                            >
                            <span class="font-medium">{pendaftaran.nama}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground block text-xs"
                                >Tempat, Tanggal Lahir</span
                            >
                            <span class="font-medium"
                                >{pendaftaran.tempat_lahir}, {pendaftaran.tanggal_lahir}</span
                            >
                        </div>
                        <div class="md:col-span-2">
                            <span class="text-muted-foreground block text-xs"
                                >Keterampilan yang Diminati</span
                            >
                            <span class="font-medium"
                                >{pendaftaran.keterampilan_yang_diminati}</span
                            >
                        </div>
                    </div>
                </div>

                <div class="text-left">
                    <h3 class="text-lg font-bold mb-4 border-b pb-2">
                        Dokumen Terlampir
                    </h3>
                    <div class="space-y-3 text-sm">
                        {#each jenis_berkas as jenis}
                            {@const file = pendaftaran.berkas?.find(
                                (b: any) =>
                                    b.jenis_berkas_pendaftaran_id === jenis.id,
                            )}
                            <div
                                class="flex justify-between items-center p-3 border rounded-md {file
                                    ? 'bg-green-50/30 border-green-100'
                                    : 'bg-gray-50'}"
                            >
                                <span class="font-medium"
                                    >{jenis.nama_berkas}</span
                                >
                                {#if file}
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded-md font-semibold"
                                            >Valid</span
                                        >
                                        <a
                                            href={`/storage/${file.file_path}`}
                                            target="_blank"
                                            rel="noreferrer"
                                            class="text-blue-600 hover:underline text-xs font-medium flex items-center gap-1"
                                        >
                                            Lihat File
                                        </a>
                                    </div>
                                {:else}
                                    <span class="text-red-500 text-xs"
                                        >Belum Diunggah</span
                                    >
                                {/if}
                            </div>
                        {/each}
                    </div>
                </div>
            </CardContent>
        {:else if pendaftaran.status_pendaftaran === "selesai_asesmen"}
            <CardHeader>
                <CardTitle class="text-center text-amber-600 text-2xl mb-2"
                    >Asesmen Selesai</CardTitle
                >
            </CardHeader>
            <CardContent class="text-center">
                <p class="mb-4">
                    Tahap asesmen untuk peserta atas nama <strong
                        >{pendaftaran.nama}</strong
                    >
                    telah selesai dilaksanakan.
                </p>
                <p class="text-muted-foreground">
                    Saat ini pendaftaran sedang menunggu keputusan kelulusan dari Kepala Seksi.
                </p>
            </CardContent>
        {:else if pendaftaran.status_pendaftaran === "diterima" || pendaftaran.status_pendaftaran === "pengajuan_lepas_mandiri"}
            <CardHeader>
                <CardTitle class="text-center text-green-600 text-2xl mb-2"
                    >Selamat, Diterima!</CardTitle
                >
            </CardHeader>
            <CardContent class="text-center">
                <p class="mb-4">
                    Peserta atas nama <strong>{pendaftaran.nama}</strong> telah
                    <strong>Lolos dan Diterima</strong> sebagai Klien Panti.
                </p>
                {#if pendaftaran.tanggal_mulai_diterima}
                    <p class="mb-4 text-sm font-medium text-green-700">
                        Tanggal Mulai Diterima: {formatDate(
                            pendaftaran.tanggal_mulai_diterima,
                        )}
                    </p>
                {/if}
                {#if pendaftaran.status_pendaftaran === "pengajuan_lepas_mandiri"}
                    <div class="mx-auto mt-4 max-w-xl rounded-lg border border-amber-200 bg-amber-50/70 p-4 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-200">
                        <p class="font-semibold">
                            Sedang Dalam Tahap Pengajuan Evaluasi Lepas Mandiri
                        </p>
                        <p class="mt-1 text-xs">
                            Pekerja Sosial telah mengajukan evaluasi kesiapan lepas mandiri kepada Kepala Seksi dan sedang menunggu persetujuan.
                        </p>
                    </div>
                {:else}
                    <p class="text-muted-foreground">
                        Silakan menunggu arahan lebih lanjut atau hubungi pihak
                        panti terkait jadwal kedatangan dan asrama.
                    </p>
                {/if}
            </CardContent>
        {:else if pendaftaran.status_pendaftaran === "lepas_mandiri"}
            <CardHeader class="border-b bg-emerald-50/50 dark:bg-emerald-950/20">
                <div class="flex flex-col items-center text-center gap-2 py-2">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                        <GraduationCap class="h-6 w-6" />
                    </div>
                    <CardTitle class="text-emerald-700 dark:text-emerald-300 text-2xl">
                        Selamat, Telah Dinyatakan Lepas Mandiri!
                    </CardTitle>
                    <p class="text-sm text-muted-foreground max-w-2xl">
                        Peserta atas nama <strong>{pendaftaran.nama}</strong> telah menyelesaikan masa pembinaan dan disetujui (ACC) oleh Kepala Seksi untuk <strong>Lepas Mandiri</strong>.
                    </p>
                </div>
            </CardHeader>
            <CardContent class="p-6 space-y-6">
                <!-- Catatan Evaluasi & Keputusan Lepas Mandiri -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 rounded-xl border bg-muted/30 p-4 text-sm">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Evaluasi Pekerja Sosial
                        </p>
                        <p class="font-medium">
                            {pendaftaran.keterangan_pengajuan_lepas_mandiri || "-"}
                        </p>
                        {#if pendaftaran.tanggal_pengajuan_lepas_mandiri}
                            <p class="text-xs text-muted-foreground">
                                Diajukan pada {formatShortDate(pendaftaran.tanggal_pengajuan_lepas_mandiri)}
                            </p>
                        {/if}
                    </div>
                    <div class="space-y-1">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Keputusan Kepala Seksi (ACC Lepas Mandiri)
                        </p>
                        <p class="font-medium text-emerald-700 dark:text-emerald-400">
                            {pendaftaran.keterangan_keputusan_lepas_mandiri || "-"}
                        </p>
                        {#if pendaftaran.tanggal_lepas_mandiri}
                            <p class="text-xs text-muted-foreground">
                                Disetujui pada {formatShortDate(pendaftaran.tanggal_lepas_mandiri)}
                            </p>
                        {/if}
                    </div>
                </div>

                <!-- Statistik Ringkasan Pembinaan & Pembelajaran Anak -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-xl border p-4 flex items-start gap-3">
                        <div class="rounded-lg bg-primary/10 p-2 text-primary">
                            <Calendar class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Mulai Pembinaan</p>
                            <p class="mt-0.5 text-sm font-bold">
                                {formatShortDate(pendaftaran.tanggal_mulai_diterima)}
                            </p>
                        </div>
                    </div>

                    <div class="rounded-xl border p-4 flex items-start gap-3">
                        <div class="rounded-lg bg-blue-500/10 p-2 text-blue-600">
                            <Clock class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Lama Pembinaan</p>
                            <p class="mt-0.5 text-sm font-bold">
                                {summary.lama_pembinaan || pendaftaran.lama_pembinaan || "-"}
                            </p>
                            <p class="text-[11px] text-muted-foreground">
                                {summary.lama_pembinaan_hari ?? pendaftaran.lama_pembinaan_hari ?? 0} hari
                            </p>
                        </div>
                    </div>

                    <div class="rounded-xl border p-4 flex items-start gap-3">
                        <div class="rounded-lg bg-emerald-500/10 p-2 text-emerald-600">
                            <CheckCircle2 class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Kelas Dihadiri</p>
                            <p class="mt-0.5 text-sm font-bold">
                                {summary.total_hadir ?? 0} Kelas Hadir
                            </p>
                            <p class="text-[11px] text-muted-foreground">
                                {summary.total_mata_pelajaran ?? 0} mata pelajaran
                            </p>
                        </div>
                    </div>

                    <div class="rounded-xl border p-4 flex items-start gap-3">
                        <div class="rounded-lg bg-amber-500/10 p-2 text-amber-600">
                            <Star class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Rata-Rata Nilai</p>
                            <div class="mt-1 flex items-center gap-1.5">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold {nilaiBadgeClass(summary.rata_rata_nilai)}">
                                    {summary.rata_rata_label || "-"}
                                </span>
                                {#if summary.rata_rata_skor !== null && summary.rata_rata_skor !== undefined}
                                    <span class="text-xs font-bold">
                                        ({Number(summary.rata_rata_skor).toFixed(2)})
                                    </span>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Ringkasan Semua Pembelajaran Anak per Mata Pelajaran -->
                <div class="space-y-3">
                    <div>
                        <h3 class="text-base font-bold">
                            Ringkasan Semua Pembelajaran Anak ({pendaftaran.nama})
                        </h3>
                        <p class="text-xs text-muted-foreground">
                            Rekap kehadiran, nilai rata-rata setiap mata pelajaran, dan komentar terakhir instruktur. Klik mata pelajaran atau tombol Detail untuk melihat seluruh pertemuan.
                        </p>
                    </div>

                    <div class="overflow-x-auto rounded-md border">
                        <Table.Root>
                            <Table.Header>
                                <Table.Row>
                                    <Table.Head class="w-12">No</Table.Head>
                                    <Table.Head>Mata Pelajaran</Table.Head>
                                    <Table.Head>Kehadiran Kelas</Table.Head>
                                    <Table.Head>Nilai Rata-Rata</Table.Head>
                                    <Table.Head>Komentar Terakhir Instruktur</Table.Head>
                                    <Table.Head class="text-right">Aksi</Table.Head>
                                </Table.Row>
                            </Table.Header>
                            <Table.Body>
                                {#if mataPelajaranList.length === 0}
                                    <Table.Row>
                                        <Table.Cell colspan={6} class="h-24 text-center text-muted-foreground">
                                            Belum ada data pertemuan pembelajaran.
                                        </Table.Cell>
                                    </Table.Row>
                                {:else}
                                    {#each mataPelajaranList as mapel, idx (mapel.mata_pelajaran_id)}
                                        <Table.Row
                                            class="cursor-pointer hover:bg-muted/50 transition-colors"
                                            onclick={() => openDetailDialog(mapel)}
                                        >
                                            <Table.Cell class="font-medium">{idx + 1}</Table.Cell>
                                            <Table.Cell>
                                                <div class="flex items-start gap-2">
                                                    <BookOpen class="mt-0.5 h-4 w-4 shrink-0 text-primary" />
                                                    <div>
                                                        <div class="font-semibold">
                                                            {mapel.nama_mata_pelajaran}
                                                        </div>
                                                        {#if mapel.keterangan_mata_pelajaran}
                                                            <div class="text-xs text-muted-foreground">
                                                                {mapel.keterangan_mata_pelajaran}
                                                            </div>
                                                        {/if}
                                                    </div>
                                                </div>
                                            </Table.Cell>
                                            <Table.Cell class="whitespace-nowrap">
                                                <div class="text-sm font-medium text-green-700 dark:text-green-400">
                                                    {mapel.total_hadir} Kelas Hadir
                                                </div>
                                                <div class="text-xs text-muted-foreground">
                                                    Total {mapel.total_pertemuan} pertemuan
                                                </div>
                                            </Table.Cell>
                                            <Table.Cell class="whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {nilaiBadgeClass(mapel.rata_rata_nilai)}">
                                                        {mapel.rata_rata_label}
                                                    </span>
                                                    {#if mapel.rata_rata_skor !== null && mapel.rata_rata_skor !== undefined}
                                                        <span class="text-xs font-semibold text-muted-foreground">
                                                            ({Number(mapel.rata_rata_skor).toFixed(2)} / 4.00)
                                                        </span>
                                                    {/if}
                                                </div>
                                            </Table.Cell>
                                            <Table.Cell class="max-w-sm">
                                                {#if mapel.komentar_terakhir}
                                                    <div class="flex items-start gap-1.5 text-sm">
                                                        <MessageSquareText class="mt-0.5 h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                                                        <span class="line-clamp-2 font-medium">
                                                            {mapel.komentar_terakhir}
                                                        </span>
                                                    </div>
                                                {:else}
                                                    <span class="text-xs italic text-muted-foreground">
                                                        Belum ada komentar
                                                    </span>
                                                {/if}
                                                {#if mapel.tanggal_terakhir || mapel.instruktur_terakhir}
                                                    <div class="mt-1 text-[11px] text-muted-foreground">
                                                        {formatShortDate(mapel.tanggal_terakhir)}
                                                        {#if mapel.instruktur_terakhir}
                                                            · Instruktur: {mapel.instruktur_terakhir}
                                                        {/if}
                                                    </div>
                                                {/if}
                                            </Table.Cell>
                                            <Table.Cell
                                                class="text-right whitespace-nowrap"
                                                onclick={(e) => e.stopPropagation()}
                                            >
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onclick={() => openDetailDialog(mapel)}
                                                >
                                                    <Eye class="mr-1.5 h-4 w-4" />
                                                    Detail ({mapel.total_pertemuan})
                                                </Button>
                                            </Table.Cell>
                                        </Table.Row>
                                    {/each}
                                {/if}
                            </Table.Body>
                        </Table.Root>
                    </div>
                </div>
            </CardContent>
        {:else if pendaftaran.status_pendaftaran === "ditolak"}
            <CardHeader>
                <CardTitle class="text-center text-red-600 text-2xl mb-2"
                    >Mohon Maaf, Pendaftaran Ditolak</CardTitle
                >
            </CardHeader>
            <CardContent class="text-center">
                <p class="mb-4">
                    Pendaftaran peserta atas nama <strong
                        >{pendaftaran.nama}</strong
                    >
                    saat ini <strong>Tidak Lolos (Ditolak)</strong>.
                </p>
                <p class="text-muted-foreground">
                    Terima kasih telah mendaftar. Keputusan ini mutlak dari
                    hasil asesmen dan kuota panti.
                </p>
            </CardContent>
        {/if}
    </Card>
{/if}

<!-- Dialog Detail Seluruh Pembelajaran Anak untuk Wali -->
<Dialog.Root bind:open={dialogOpen}>
    <Dialog.Content class="sm:max-w-6xl w-[95vw] max-h-[85vh] flex flex-col">
        <Dialog.Header>
            <Dialog.Title>
                Detail Pembelajaran — {selectedMapel?.nama_mata_pelajaran || "Mata Pelajaran"}
            </Dialog.Title>
            <Dialog.Description>
                Riwayat seluruh pertemuan pembelajaran, nilai kualitatif, dan komentar instruktur untuk <strong>{pendaftaran?.nama}</strong>.
            </Dialog.Description>
        </Dialog.Header>

        {#if selectedMapel}
            <div class="grid grid-cols-1 gap-3 rounded-lg border bg-muted/40 p-3 sm:grid-cols-3 text-sm">
                <div>
                    <span class="text-xs text-muted-foreground">Mata Pelajaran</span>
                    <p class="font-semibold">{selectedMapel.nama_mata_pelajaran}</p>
                </div>
                <div>
                    <span class="text-xs text-muted-foreground">Rekap Kehadiran</span>
                    <p class="font-semibold">
                        Hadir: {selectedMapel.total_hadir} · Izin: {selectedMapel.total_izin} · Absen: {selectedMapel.total_absen}
                    </p>
                </div>
                <div>
                    <span class="text-xs text-muted-foreground">Nilai Rata-Rata</span>
                    <div class="mt-0.5 flex items-center gap-2">
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {nilaiBadgeClass(selectedMapel.rata_rata_nilai)}">
                            {selectedMapel.rata_rata_label}
                        </span>
                        {#if selectedMapel.rata_rata_skor !== null && selectedMapel.rata_rata_skor !== undefined}
                            <span class="text-xs font-semibold">
                                ({Number(selectedMapel.rata_rata_skor).toFixed(2)} / 4.00)
                            </span>
                        {/if}
                    </div>
                </div>
            </div>

            <div class="overflow-y-auto rounded-md border flex-1">
                <Table.Root>
                    <Table.Header>
                        <Table.Row>
                            <Table.Head class="w-12">No</Table.Head>
                            <Table.Head>Tanggal & Jadwal</Table.Head>
                            <Table.Head>Instruktur</Table.Head>
                            <Table.Head>Presensi</Table.Head>
                            <Table.Head>Nilai</Table.Head>
                            <Table.Head>Komentar Instruktur</Table.Head>
                        </Table.Row>
                    </Table.Header>
                    <Table.Body>
                        {#if !selectedMapel.riwayat?.length}
                            <Table.Row>
                                <Table.Cell colspan={6} class="h-20 text-center text-muted-foreground">
                                    Tidak ada riwayat pertemuan.
                                </Table.Cell>
                            </Table.Row>
                        {:else}
                            {#each selectedMapel.riwayat as item, i (item.id)}
                                <Table.Row>
                                    <Table.Cell class="font-medium">{i + 1}</Table.Cell>
                                    <Table.Cell class="whitespace-nowrap">
                                        <div class="font-semibold text-sm">
                                            {formatFullDate(item.tanggal)}
                                        </div>
                                        <div class="text-xs text-muted-foreground">
                                            {item.hari || "-"}, {formatTime(item.jam_mulai)} - {formatTime(item.jam_selesai)}
                                            {#if item.ruangan}
                                                · {item.ruangan}
                                            {/if}
                                        </div>
                                    </Table.Cell>
                                    <Table.Cell class="whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5 text-sm">
                                            <User class="h-3.5 w-3.5 text-muted-foreground" />
                                            {item.instruktur_nama || "-"}
                                        </div>
                                    </Table.Cell>
                                    <Table.Cell class="whitespace-nowrap">
                                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {presensiBadgeClass(item.presensi)}">
                                            {presensiLabel(item.presensi)}
                                        </span>
                                    </Table.Cell>
                                    <Table.Cell class="whitespace-nowrap">
                                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {nilaiBadgeClass(item.nilai)}">
                                            {item.nilai_label}
                                        </span>
                                    </Table.Cell>
                                    <Table.Cell class="text-sm">
                                        {#if item.komentar_instruktur}
                                            <p class="font-medium">{item.komentar_instruktur}</p>
                                            {#if item.keterangan_peserta && item.keterangan_pertemuan && item.keterangan_peserta !== item.keterangan_pertemuan}
                                                <p class="mt-0.5 text-xs text-muted-foreground">
                                                    Topik kelas: {item.keterangan_pertemuan}
                                                </p>
                                            {/if}
                                        {:else}
                                            <span class="text-muted-foreground">-</span>
                                        {/if}
                                    </Table.Cell>
                                </Table.Row>
                            {/each}
                        {/if}
                    </Table.Body>
                </Table.Root>
            </div>
        {/if}

        <Dialog.Footer>
            <Button variant="outline" onclick={() => (dialogOpen = false)}>
                Tutup
            </Button>
        </Dialog.Footer>
    </Dialog.Content>
</Dialog.Root>
