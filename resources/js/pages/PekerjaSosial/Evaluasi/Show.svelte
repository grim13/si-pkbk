<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Evaluasi Kemandirian",
                href: "/pekerja-sosial/evaluasi",
            },
            {
                title: "Rekap Pembelajaran & Pengajuan Lepas Mandiri",
                href: "#",
            },
        ],
    };
</script>

<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import { Badge } from "@/components/ui/badge";
    import { Button } from "@/components/ui/button";
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from "@/components/ui/card";
    import * as Dialog from "@/components/ui/dialog";
    import { Label } from "@/components/ui/label";
    import * as Table from "@/components/ui/table";
    import { Textarea } from "@/components/ui/textarea";
    import { Link, useForm } from "@inertiajs/svelte";
    import { untrack } from "svelte";
    import {
        AlertCircle,
        ArrowLeft,
        BookOpen,
        Calendar,
        CheckCircle2,
        Clock,
        Eye,
        MessageSquareText,
        Send,
        Star,
        User,
    } from "@lucide/svelte";

    let {
        pendaftaran,
        mataPelajaranList = [],
        summary = {},
    } = $props();

    let dialogOpen = $state(false);
    let selectedMapel = $state<any>(null);

    const form = useForm({
        keterangan: untrack(
            () => pendaftaran.keterangan_pengajuan_lepas_mandiri || "",
        ),
    });

    function openDetailDialog(mapel: any) {
        selectedMapel = mapel;
        dialogOpen = true;
    }

    function submitPengajuan(e: Event) {
        e.preventDefault();
        form.post(`/pekerja-sosial/evaluasi/${pendaftaran.id}`);
    }

    function formatDate(date: string | null) {
        if (!date) return "-";
        return new Date(date).toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
    }

    function formatFullDate(date: string | null) {
        if (!date) return "-";
        return new Date(date).toLocaleDateString("id-ID", {
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

    function statusLabel(status: string) {
        switch (status) {
            case "diterima":
                return "DITERIMA / DALAM PEMBINAAN";
            case "pengajuan_lepas_mandiri":
                return "PENGAJUAN LEPAS MANDIRI";
            case "lepas_mandiri":
                return "LEPAS MANDIRI";
            default:
                return status?.replaceAll("_", " ").toUpperCase() || "-";
        }
    }

    function statusBadgeClass(status: string) {
        switch (status) {
            case "diterima":
                return "bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300";
            case "pengajuan_lepas_mandiri":
                return "bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300";
            case "lepas_mandiri":
                return "bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300";
            default:
                return "bg-slate-100 text-slate-800";
        }
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

<AppHead title={`Evaluasi Klien — ${pendaftaran.nama}`} />

<div class="p-6 w-full space-y-6">
    <!-- Header Identitas Klien -->
    <div class="flex flex-col gap-4 rounded-xl border bg-card p-5 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-start gap-4">
            <Button variant="outline" size="icon" asChild>
                <Link href="/pekerja-sosial/evaluasi" aria-label="Kembali">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl font-bold tracking-tight">
                        {pendaftaran.nama}
                    </h1>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {statusBadgeClass(pendaftaran.status_pendaftaran)}">
                        {statusLabel(pendaftaran.status_pendaftaran)}
                    </span>
                    {#if pendaftaran.jenis_disabilitas?.nama}
                        <Badge variant="secondary">
                            {pendaftaran.jenis_disabilitas.nama}
                        </Badge>
                    {/if}
                </div>
                <p class="text-xs text-muted-foreground">
                    No. Pendaftaran: <strong>{pendaftaran.no_pendaftaran || "-"}</strong>
                    · NIK: <strong>{pendaftaran.nik || "-"}</strong>
                    · Wali: <strong>{pendaftaran.wali?.user?.name || "-"}</strong>
                    · Minat Keterampilan: <strong>{pendaftaran.keterampilan_yang_diminati || "-"}</strong>
                </p>
            </div>
        </div>
    </div>

    <!-- Banner Catatan Penolakan / Status Pengajuan -->
    {#if pendaftaran.status_pendaftaran === "diterima" && pendaftaran.keterangan_keputusan_lepas_mandiri}
        <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50/70 p-4 text-sm text-red-900 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-200">
            <AlertCircle class="mt-0.5 h-5 w-5 shrink-0 text-red-600" />
            <div class="space-y-1">
                <p class="font-semibold">
                    Pengajuan Lepas Mandiri Sebelumnya Ditolak oleh Kepala Seksi
                </p>
                <p>
                    <strong>Keterangan Penolakan Kepala Seksi:</strong> {pendaftaran.keterangan_keputusan_lepas_mandiri}
                </p>
                <p class="text-xs opacity-80">
                    Klien dikembalikan ke status Diterima (Dalam Pembinaan). Anda dapat mengajukan kembali setelah evaluasi lanjutan.
                </p>
            </div>
        </div>
    {:else if pendaftaran.status_pendaftaran === "pengajuan_lepas_mandiri"}
        <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50/70 p-4 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-200">
            <Clock class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
            <div class="space-y-1">
                <p class="font-semibold">
                    Menunggu Persetujuan Kepala Seksi (Pengajuan Lepas Mandiri)
                </p>
                <p>
                    <strong>Keterangan Pengajuan Pekerja Sosial:</strong> {pendaftaran.keterangan_pengajuan_lepas_mandiri || "-"}
                </p>
                {#if pendaftaran.tanggal_pengajuan_lepas_mandiri}
                    <p class="text-xs opacity-80">
                        Diajukan pada {formatFullDate(pendaftaran.tanggal_pengajuan_lepas_mandiri)}
                    </p>
                {/if}
            </div>
        </div>
    {:else if pendaftaran.status_pendaftaran === "lepas_mandiri"}
        <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 text-sm text-emerald-900 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200">
            <CheckCircle2 class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />
            <div class="space-y-1">
                <p class="font-semibold">
                    Klien Telah Dinyatakan Lepas Mandiri oleh Kepala Seksi
                </p>
                {#if pendaftaran.keterangan_pengajuan_lepas_mandiri}
                    <p>
                        <strong>Keterangan Pengajuan Pekerja Sosial:</strong> {pendaftaran.keterangan_pengajuan_lepas_mandiri}
                    </p>
                {/if}
                <p>
                    <strong>Keterangan Keputusan Kepala Seksi:</strong> {pendaftaran.keterangan_keputusan_lepas_mandiri || "-"}
                </p>
                {#if pendaftaran.tanggal_lepas_mandiri}
                    <p class="text-xs opacity-80">
                        Disetujui pada {formatFullDate(pendaftaran.tanggal_lepas_mandiri)}
                    </p>
                {/if}
            </div>
        </div>
    {/if}

    <!-- Ringkasan Pembinaan & Pembelajaran -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card>
            <CardContent class="p-4 flex items-start gap-3">
                <div class="rounded-lg bg-primary/10 p-2.5 text-primary">
                    <Calendar class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Tanggal Mulai Pembinaan</p>
                    <p class="mt-1 text-base font-bold">
                        {formatDate(pendaftaran.tanggal_mulai_diterima)}
                    </p>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardContent class="p-4 flex items-start gap-3">
                <div class="rounded-lg bg-blue-500/10 p-2.5 text-blue-600">
                    <Clock class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Lama Pembinaan</p>
                    <p class="mt-1 text-base font-bold">
                        {summary.lama_pembinaan || pendaftaran.lama_pembinaan || "-"}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {summary.lama_pembinaan_hari ?? pendaftaran.lama_pembinaan_hari ?? 0} hari berjalan
                    </p>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardContent class="p-4 flex items-start gap-3">
                <div class="rounded-lg bg-emerald-500/10 p-2.5 text-emerald-600">
                    <CheckCircle2 class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Kelas Dihadiri</p>
                    <p class="mt-1 text-base font-bold">
                        {summary.total_hadir ?? 0} Kelas Hadir
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {summary.total_mata_pelajaran ?? 0} mata pelajaran · Izin: {summary.total_izin ?? 0} · Absen: {summary.total_absen ?? 0}
                    </p>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardContent class="p-4 flex items-start gap-3">
                <div class="rounded-lg bg-amber-500/10 p-2.5 text-amber-600">
                    <Star class="h-5 w-5" />
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Rata-Rata Nilai Keseluruhan</p>
                    <div class="mt-1 flex items-center gap-2">
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {nilaiBadgeClass(summary.rata_rata_nilai)}">
                            {summary.rata_rata_label || "-"}
                        </span>
                        {#if summary.rata_rata_skor !== null && summary.rata_rata_skor !== undefined}
                            <span class="text-sm font-bold">
                                ({Number(summary.rata_rata_skor).toFixed(2)} / 4.00)
                            </span>
                        {/if}
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>

    <!-- Tabel Rata-Rata Pembelajaran per Mata Pelajaran & Komentar Terakhir -->
    <Card>
        <CardHeader class="border-b py-4 px-6">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <CardTitle class="text-base">
                        Rekap Nilai Rata-Rata & Komentar Terakhir Setiap Mata Pelajaran
                    </CardTitle>
                    <p class="text-xs text-muted-foreground mt-0.5">
                        Klik pada baris mata pelajaran atau tombol Lihat Detail untuk membuka dialog seluruh riwayat pembelajaran, nilai, dan komentar instruktur.
                    </p>
                </div>
            </div>
        </CardHeader>
        <CardContent class="overflow-x-auto p-0">
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
                            <Table.Cell colspan={6} class="h-28 text-center text-muted-foreground">
                                Klien belum memiliki riwayat pertemuan pembelajaran yang disetujui (ACC).
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
                                        {mapel.total_hadir} Kelas Dihadiri
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        Total {mapel.total_pertemuan} pertemuan
                                        {#if mapel.total_izin > 0 || mapel.total_absen > 0}
                                            (Izin: {mapel.total_izin}, Absen: {mapel.total_absen})
                                        {/if}
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
                                <Table.Cell class="max-w-md">
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
                                            Pertemuan terakhir: {formatDate(mapel.tanggal_terakhir)}
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
                                        id={`detail-mapel-${mapel.mata_pelajaran_id}`}
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
        </CardContent>
    </Card>

    <!-- Form Pengajuan Lepas Kemandirian ke Kepala Seksi -->
    {#if pendaftaran.status_pendaftaran !== "lepas_mandiri"}
        <Card>
            <CardHeader class="border-b py-4 px-6">
                <CardTitle class="text-base">
                    Pengajuan Kesiapan Lepas Kemandirian ke Kepala Seksi
                </CardTitle>
                <p class="text-xs text-muted-foreground mt-0.5">
                    Berikan catatan evaluasi kesiapan klien untuk diajukan lepas mandiri kepada Kepala Seksi.
                </p>
            </CardHeader>
            <CardContent class="p-6">
                <form onsubmit={submitPengajuan} class="space-y-4">
                    <div class="space-y-2">
                        <Label for="keterangan-pengajuan">
                            Keterangan Pengajuan Lepas Mandiri <span class="text-destructive">*</span>
                        </Label>
                        <Textarea
                            id="keterangan-pengajuan"
                            rows={4}
                            placeholder="Tuliskan hasil evaluasi perkembangan pembelajaran dan alasan pengajuan lepas mandiri klien..."
                            bind:value={form.keterangan}
                        />
                        {#if form.errors.keterangan}
                            <p class="text-xs text-destructive">
                                {form.errors.keterangan}
                            </p>
                        {/if}
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <Button
                            id="btn-ajukan-lepas-mandiri"
                            type="submit"
                            disabled={form.processing}
                        >
                            <Send class="mr-2 h-4 w-4" />
                            {pendaftaran.status_pendaftaran === "pengajuan_lepas_mandiri"
                                ? "Perbarui Pengajuan Lepas Mandiri"
                                : "Ajukan Lepas Mandiri ke Kepala Seksi"}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    {/if}
</div>

<!-- Dialog Detail Seluruh Pembelajaran, Nilai, dan Komentar Instruktur -->
<Dialog.Root bind:open={dialogOpen}>
    <Dialog.Content class="sm:max-w-4xl max-h-[85vh] flex flex-col">
        <Dialog.Header>
            <Dialog.Title>
                Detail Pembelajaran — {selectedMapel?.nama_mata_pelajaran || "Mata Pelajaran"}
            </Dialog.Title>
            <Dialog.Description>
                Riwayat seluruh pertemuan pembelajaran, nilai kualitatif, dan komentar instruktur untuk klien <strong>{pendaftaran.nama}</strong>.
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
