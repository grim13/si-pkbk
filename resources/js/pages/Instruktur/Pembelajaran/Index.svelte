<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Proses Pembelajaran",
                href: "/instruktur/pembelajaran",
            },
        ],
    };
</script>

<script lang="ts">
    import { router, Link } from "@inertiajs/svelte";
    import { untrack } from "svelte";
    import AppHead from "@/components/AppHead.svelte";
    import { Button } from "@/components/ui/button";
    import { Input } from "@/components/ui/input";
    import { Label } from "@/components/ui/label";
    import { Badge } from "@/components/ui/badge";
    import * as Select from "@/components/ui/select";
    import * as Table from "@/components/ui/table";
    import * as Dialog from "@/components/ui/dialog";
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from "@/components/ui/card";
    import {
        Calendar,
        Clock,
        MapPin,
        Plus,
        FileEdit,
        Eye,
        Send,
        BookOpen,
    } from "@lucide/svelte";

    let {
        jadwalList = [],
        pertemuanList = [],
        availableYears = [],
        filters = {},
    } = $props();

    let tahun = $state(
        untrack(() => String(filters.tahun || new Date().getFullYear())),
    );
    let jadwalFilter = $state(untrack(() => String(filters.jadwal_id || "semua")));
    let statusFilter = $state(untrack(() => filters.status || "semua"));

    let isDateDialogOpen = $state(false);
    let selectedJadwal = $state<any>(null);
    let selectedTanggal = $state("");

    function applyFilters() {
        router.get(
            "/instruktur/pembelajaran",
            {
                tahun: Number(tahun),
                jadwal_id: jadwalFilter === "semua" ? "" : Number(jadwalFilter),
                status: statusFilter === "semua" ? "" : statusFilter,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }

    function openCatatPertemuan(jadwal: any) {
        selectedJadwal = jadwal;
        const now = new Date();
        if (now.getFullYear() === Number(jadwal.tahun)) {
            selectedTanggal = now.toISOString().slice(0, 10);
        } else {
            selectedTanggal = `${jadwal.tahun}-01-01`;
        }
        isDateDialogOpen = true;
    }

    function goToPertemuanForm() {
        if (!selectedJadwal || !selectedTanggal) return;
        isDateDialogOpen = false;
        router.get(`/instruktur/pembelajaran/jadwal/${selectedJadwal.id}`, {
            tanggal: selectedTanggal,
        });
    }

    function formatTime(t: string | null) {
        if (!t) return "-";
        return t.slice(0, 5);
    }

    function formatDate(date: string | null) {
        if (!date) return "-";
        return new Date(date).toLocaleDateString("id-ID", {
            weekday: "long",
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
    }

    function statusBadgeClass(status: string) {
        switch (status) {
            case "draft":
                return "bg-slate-100 text-slate-800";
            case "pengajuan":
                return "bg-blue-100 text-blue-800";
            case "perbaikan":
                return "bg-amber-100 text-amber-800";
            case "acc":
                return "bg-green-100 text-green-800";
            default:
                return "bg-slate-100 text-slate-800";
        }
    }

    function statusLabel(status: string) {
        switch (status) {
            case "draft":
                return "DRAFT";
            case "pengajuan":
                return "PENGAJUAN";
            case "perbaikan":
                return "PERBAIKAN";
            case "acc":
                return "ACC";
            default:
                return status?.toUpperCase() || "-";
        }
    }

    function ajukanPertemuan(pertemuan: any) {
        if (
            confirm(
                "Ajukan pertemuan ini ke Pekerja Sosial? Setelah diajukan, presensi dan nilai tidak dapat diubah kecuali diminta perbaikan.",
            )
        ) {
            router.post(`/instruktur/pembelajaran/${pertemuan.id}/ajukan`);
        }
    }
</script>

<AppHead title={`Proses Pembelajaran Tahun ${tahun}`} />

<div class="p-6 w-full space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight">
                Pencatatan Proses Pembelajaran — Tahun {tahun}
            </h1>
            <p class="text-sm text-muted-foreground">
                Pilih jadwal mengajar Anda sesuai tahun, catat presensi dan penilaian kualitatif peserta per tanggal pertemuan, lalu ajukan ke Pekerja Sosial.
            </p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="grid grid-cols-1 gap-3 rounded-xl border bg-card p-4 sm:grid-cols-3">
        <div class="space-y-1.5">
            <Label class="text-xs text-muted-foreground">Tahun Jadwal</Label>
            <Select.Root
                type="single"
                value={tahun}
                onValueChange={(val) => {
                    tahun = val;
                    jadwalFilter = "semua";
                    applyFilters();
                }}
            >
                <Select.Trigger class="w-full">
                    Tahun {tahun}
                </Select.Trigger>
                <Select.Content>
                    {#each availableYears as y (y)}
                        <Select.Item value={String(y)} label={`Tahun ${y}`}>
                            Tahun {y}
                        </Select.Item>
                    {/each}
                </Select.Content>
            </Select.Root>
        </div>

        <div class="space-y-1.5">
            <Label class="text-xs text-muted-foreground">Filter Jadwal / Mata Pelajaran</Label>
            <Select.Root
                type="single"
                value={jadwalFilter}
                onValueChange={(val) => {
                    jadwalFilter = val;
                    applyFilters();
                }}
            >
                <Select.Trigger class="w-full">
                    {#if jadwalFilter === "semua"}
                        Semua Jadwal Mengajar
                    {:else}
                        {@const j = jadwalList.find((item: any) => String(item.id) === jadwalFilter)}
                        {j ? `${j.hari} (${formatTime(j.jam_mulai)}) - ${j.mata_pelajaran?.nama}` : "Semua Jadwal"}
                    {/if}
                </Select.Trigger>
                <Select.Content>
                    <Select.Item value="semua" label="Semua Jadwal Mengajar">
                        Semua Jadwal Mengajar
                    </Select.Item>
                    {#each jadwalList as j (j.id)}
                        <Select.Item
                            value={String(j.id)}
                            label={`${j.hari} (${formatTime(j.jam_mulai)}) - ${j.mata_pelajaran?.nama}`}
                        >
                            {j.hari} ({formatTime(j.jam_mulai)}-{formatTime(j.jam_selesai)}) — {j.mata_pelajaran?.nama}
                        </Select.Item>
                    {/each}
                </Select.Content>
            </Select.Root>
        </div>

        <div class="space-y-1.5">
            <Label class="text-xs text-muted-foreground">Filter Status Pertemuan</Label>
            <Select.Root
                type="single"
                value={statusFilter}
                onValueChange={(val) => {
                    statusFilter = val;
                    applyFilters();
                }}
            >
                <Select.Trigger class="w-full">
                    {statusFilter === "semua" ? "Semua Status" : statusLabel(statusFilter)}
                </Select.Trigger>
                <Select.Content>
                    <Select.Item value="semua" label="Semua Status">Semua Status</Select.Item>
                    <Select.Item value="draft" label="DRAFT">DRAFT</Select.Item>
                    <Select.Item value="pengajuan" label="PENGAJUAN">PENGAJUAN</Select.Item>
                    <Select.Item value="perbaikan" label="PERBAIKAN">PERBAIKAN</Select.Item>
                    <Select.Item value="acc" label="ACC">ACC</Select.Item>
                </Select.Content>
            </Select.Root>
        </div>
    </div>

    <!-- Daftar Jadwal Mengajar Tahun Ini -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold flex items-center gap-2">
                <BookOpen class="h-4 w-4 text-primary" />
                Jadwal Mengajar Anda (Tahun {tahun})
            </h2>
            <span class="text-xs text-muted-foreground">
                {jadwalList.length} jadwal ditemukan
            </span>
        </div>

        {#if jadwalList.length === 0}
            <Card>
                <CardContent class="py-8 text-center text-sm text-muted-foreground">
                    Belum ada jadwal mengajar yang ditugaskan kepada Anda pada tahun {tahun}.
                </CardContent>
            </Card>
        {:else}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                {#each jadwalList as jadwal (jadwal.id)}
                    <Card class="flex flex-col justify-between">
                        <CardHeader class="pb-2">
                            <div class="flex items-center justify-between gap-2">
                                <Badge variant="secondary" class="font-semibold">
                                    {jadwal.hari}
                                </Badge>
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-primary">
                                    <Clock class="h-3.5 w-3.5" />
                                    {formatTime(jadwal.jam_mulai)} - {formatTime(jadwal.jam_selesai)}
                                </span>
                            </div>
                            <CardTitle class="text-base mt-2">
                                {jadwal.mata_pelajaran?.nama || "-"}
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3 pt-0">
                            <div class="flex flex-wrap gap-1">
                                {#if jadwal.mata_pelajaran?.jenis_disabilitas?.length}
                                    {#each jadwal.mata_pelajaran.jenis_disabilitas as jd (jd.id)}
                                        <Badge variant="outline" class="text-[11px]">
                                            {jd.nama}
                                        </Badge>
                                    {/each}
                                {/if}
                            </div>

                            {#if jadwal.ruangan}
                                <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                    <MapPin class="h-3.5 w-3.5 shrink-0" />
                                    <span>{jadwal.ruangan}</span>
                                </div>
                            {/if}

                            <div class="flex items-center justify-between border-t pt-3">
                                <span class="text-xs text-muted-foreground">
                                    {jadwal.pertemuan_count || 0} pertemuan tercatat
                                </span>
                                <Button
                                    size="sm"
                                    onclick={() => openCatatPertemuan(jadwal)}
                                >
                                    <Plus class="mr-1.5 h-3.5 w-3.5" />
                                    Catat Pertemuan
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                {/each}
            </div>
        {/if}
    </div>

    <!-- Riwayat Pertemuan Pembelajaran Per Tanggal -->
    <div class="space-y-3">
        <h2 class="text-base font-semibold flex items-center gap-2">
            <Calendar class="h-4 w-4 text-primary" />
            Daftar Pertemuan Pembelajaran Per Tanggal (Tahun {tahun})
        </h2>

        <div class="rounded-md border bg-card">
            <Table.Root>
                <Table.Header>
                    <Table.Row>
                        <Table.Head>Tanggal Pembelajaran</Table.Head>
                        <Table.Head>Jadwal & Mata Pelajaran</Table.Head>
                        <Table.Head>Disabilitas</Table.Head>
                        <Table.Head>Rekap Presensi</Table.Head>
                        <Table.Head>Status</Table.Head>
                        <Table.Head>Keterangan / Catatan</Table.Head>
                        <Table.Head class="text-right">Aksi</Table.Head>
                    </Table.Row>
                </Table.Header>
                <Table.Body>
                    {#if pertemuanList.length === 0}
                        <Table.Row>
                            <Table.Cell colspan={7} class="h-24 text-center text-muted-foreground">
                                Belum ada catatan pertemuan pembelajaran pada tahun {tahun}. Klik <strong>Catat Pertemuan</strong> pada salah satu jadwal di atas.
                            </Table.Cell>
                        </Table.Row>
                    {:else}
                        {#each pertemuanList as item (item.id)}
                            <Table.Row>
                                <Table.Cell class="font-medium whitespace-nowrap">
                                    {formatDate(item.tanggal)}
                                </Table.Cell>
                                <Table.Cell>
                                    <div class="font-semibold">
                                        {item.jadwal?.mata_pelajaran?.nama || "-"}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {item.jadwal?.hari}, {formatTime(item.jadwal?.jam_mulai)} - {formatTime(item.jadwal?.jam_selesai)}
                                    </div>
                                </Table.Cell>
                                <Table.Cell>
                                    <div class="flex flex-wrap gap-1">
                                        {#each item.jadwal?.mata_pelajaran?.jenis_disabilitas || [] as jd (jd.id)}
                                            <Badge variant="outline" class="text-[10px]">
                                                {jd.nama}
                                            </Badge>
                                        {/each}
                                    </div>
                                </Table.Cell>
                                <Table.Cell class="text-xs whitespace-nowrap">
                                    <span class="font-medium text-green-700">Hadir: {item.hadir_count ?? 0}</span>
                                    <span class="mx-1 text-muted-foreground">•</span>
                                    <span class="font-medium text-amber-700">Izin: {item.izin_count ?? 0}</span>
                                    <span class="mx-1 text-muted-foreground">•</span>
                                    <span class="font-medium text-red-700">Absen: {item.absen_count ?? 0}</span>
                                </Table.Cell>
                                <Table.Cell>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {statusBadgeClass(item.status)}">
                                        {statusLabel(item.status)}
                                    </span>
                                </Table.Cell>
                                <Table.Cell class="max-w-xs text-xs">
                                    {#if item.catatan_pekerja_sosial}
                                        <div class="font-medium text-amber-800">
                                            Catatan Peksos: {item.catatan_pekerja_sosial}
                                        </div>
                                    {/if}
                                    {#if item.keterangan}
                                        <div class="text-muted-foreground truncate">
                                            {item.keterangan}
                                        </div>
                                    {:else if !item.catatan_pekerja_sosial}
                                        -
                                    {/if}
                                </Table.Cell>
                                <Table.Cell class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        {#if item.status === "draft" || item.status === "perbaikan"}
                                            <Button variant="outline" size="sm" asChild>
                                                <Link
                                                    href={`/instruktur/pembelajaran/jadwal/${item.jadwal_id}?tanggal=${item.tanggal?.slice(0, 10)}`}
                                                >
                                                    <FileEdit class="mr-1.5 h-3.5 w-3.5" />
                                                    {item.status === "perbaikan" ? "Perbaiki" : "Edit Presensi & Nilai"}
                                                </Link>
                                            </Button>
                                            <Button
                                                size="sm"
                                                onclick={() => ajukanPertemuan(item)}
                                            >
                                                <Send class="mr-1.5 h-3.5 w-3.5" />
                                                Ajukan
                                            </Button>
                                        {:else}
                                            <Button variant="outline" size="sm" asChild>
                                                <Link
                                                    href={`/instruktur/pembelajaran/jadwal/${item.jadwal_id}?tanggal=${item.tanggal?.slice(0, 10)}`}
                                                >
                                                    <Eye class="mr-1.5 h-3.5 w-3.5" />
                                                    Lihat Detail
                                                </Link>
                                            </Button>
                                        {/if}
                                    </div>
                                </Table.Cell>
                            </Table.Row>
                        {/each}
                    {/if}
                </Table.Body>
            </Table.Root>
        </div>
    </div>
</div>

<!-- Dialog Pilih Tanggal Pertemuan -->
<Dialog.Root bind:open={isDateDialogOpen}>
    <Dialog.Content class="sm:max-w-[425px]">
        <Dialog.Header>
            <Dialog.Title>Pilih Tanggal Pembelajaran</Dialog.Title>
            <Dialog.Description>
                {selectedJadwal?.mata_pelajaran?.nama || ""} — Hari {selectedJadwal?.hari} (Tahun {selectedJadwal?.tahun})
            </Dialog.Description>
        </Dialog.Header>

        <form
            onsubmit={(e) => {
                e.preventDefault();
                goToPertemuanForm();
            }}
        >
            <div class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="tanggal-pertemuan">
                        Tanggal Pembelajaran <span class="text-red-500">*</span>
                    </Label>
                    <Input
                        id="tanggal-pertemuan"
                        type="date"
                        required
                        min={`${selectedJadwal?.tahun}-01-01`}
                        max={`${selectedJadwal?.tahun}-12-31`}
                        bind:value={selectedTanggal}
                    />
                    <span class="text-xs text-muted-foreground">
                        Peserta yang tampil adalah peserta berstatus <strong>Diterima</strong> sesuai disabilitas mata pelajaran yang tanggal mulai diterimanya sebelum/pada tanggal pembelajaran.
                    </span>
                </div>
            </div>

            <Dialog.Footer>
                <Button
                    type="button"
                    variant="outline"
                    onclick={() => (isDateDialogOpen = false)}
                >
                    Batal
                </Button>
                <Button type="submit">
                    Buka Lembar Presensi & Penilaian
                </Button>
            </Dialog.Footer>
        </form>
    </Dialog.Content>
</Dialog.Root>
