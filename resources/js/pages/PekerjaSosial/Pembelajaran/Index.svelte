<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Approval Pembelajaran",
                href: "/pekerja-sosial/pembelajaran",
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

    let {
        pertemuanList = [],
        availableYears = [],
        filters = {},
    } = $props();

    let tahun = $state(
        untrack(() => String(filters.tahun || new Date().getFullYear())),
    );
    let statusFilter = $state(untrack(() => filters.status || "semua"));
    let search = $state(untrack(() => filters.search || ""));
    let searchTimeout: ReturnType<typeof setTimeout>;

    const statusTabs = [
        { value: "semua", label: "Semua" },
        { value: "pengajuan", label: "Menunggu Approval" },
        { value: "perbaikan", label: "Perlu Perbaikan" },
        { value: "acc", label: "Disetujui (ACC)" },
    ];

    function applyFilters() {
        router.get(
            "/pekerja-sosial/pembelajaran",
            {
                tahun: Number(tahun),
                status: statusFilter === "semua" ? "" : statusFilter,
                search,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }

    function handleSearchInput(e: Event) {
        search = (e.target as HTMLInputElement).value;
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 400);
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
</script>

<AppHead title={`Approval Pembelajaran — Tahun ${tahun}`} />

<div class="p-6 w-full space-y-6">
    <div>
        <h1 class="text-xl font-bold tracking-tight">
            Approval Pertemuan Pembelajaran — Tahun {tahun}
        </h1>
        <p class="text-sm text-muted-foreground">
            Verifikasi dan berikan persetujuan (ACC) atau permintaan perbaikan pada laporan presensi dan penilaian pembelajaran dari instruktur.
        </p>
    </div>

    <!-- Filter Bar -->
    <div class="grid grid-cols-1 gap-3 rounded-xl border bg-card p-4 sm:grid-cols-3">
        <div class="space-y-1.5">
            <Label class="text-xs text-muted-foreground">Filter Tahun</Label>
            <Select.Root
                type="single"
                value={tahun}
                onValueChange={(val) => {
                    tahun = val;
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
            <Label class="text-xs text-muted-foreground">Filter Status</Label>
            <Select.Root
                type="single"
                value={statusFilter}
                onValueChange={(val) => {
                    statusFilter = val;
                    applyFilters();
                }}
            >
                <Select.Trigger class="w-full">
                    {statusTabs.find((t) => t.value === statusFilter)?.label || "Semua"}
                </Select.Trigger>
                <Select.Content>
                    {#each statusTabs as tab (tab.value)}
                        <Select.Item value={tab.value} label={tab.label}>
                            {tab.label}
                        </Select.Item>
                    {/each}
                </Select.Content>
            </Select.Root>
        </div>

        <div class="space-y-1.5">
            <Label class="text-xs text-muted-foreground">Pencarian</Label>
            <Input
                type="search"
                placeholder="Cari mata pelajaran, instruktur, keterangan..."
                value={search}
                oninput={handleSearchInput}
            />
        </div>
    </div>

    <!-- Table -->
    <div class="rounded-md border bg-card">
        <Table.Root>
            <Table.Header>
                <Table.Row>
                    <Table.Head>Tanggal Pertemuan</Table.Head>
                    <Table.Head>Mata Pelajaran & Jadwal</Table.Head>
                    <Table.Head>Instruktur</Table.Head>
                    <Table.Head>Disabilitas</Table.Head>
                    <Table.Head>Rekap Presensi</Table.Head>
                    <Table.Head>Status</Table.Head>
                    <Table.Head>Keterangan</Table.Head>
                    <Table.Head class="text-right">Aksi</Table.Head>
                </Table.Row>
            </Table.Header>
            <Table.Body>
                {#if pertemuanList.length === 0}
                    <Table.Row>
                        <Table.Cell colspan={8} class="h-24 text-center text-muted-foreground">
                            Tidak ada data pengajuan pertemuan pembelajaran untuk tahun {tahun}.
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
                                {item.instruktur?.name || "-"}
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
                                        Review: {item.catatan_pekerja_sosial}
                                    </div>
                                {/if}
                                {#if item.keterangan && item.keterangan !== item.catatan_pekerja_sosial}
                                    <div class="text-muted-foreground truncate">
                                        {item.keterangan}
                                    </div>
                                {:else if !item.catatan_pekerja_sosial}
                                    -
                                {/if}
                            </Table.Cell>
                            <Table.Cell class="text-right">
                                <Button
                                    variant={item.status === "pengajuan" ? "default" : "outline"}
                                    size="sm"
                                    asChild
                                >
                                    <Link href={`/pekerja-sosial/pembelajaran/${item.id}`}>
                                        {item.status === "pengajuan" ? "Verifikasi & Approval" : "Detail"}
                                    </Link>
                                </Button>
                            </Table.Cell>
                        </Table.Row>
                    {/each}
                {/if}
            </Table.Body>
        </Table.Root>
    </div>
</div>
