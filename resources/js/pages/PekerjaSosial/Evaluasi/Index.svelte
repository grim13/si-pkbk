<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Evaluasi Kemandirian",
                href: "/pekerja-sosial/evaluasi",
            },
        ],
    };
</script>

<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import { Badge } from "@/components/ui/badge";
    import { Button } from "@/components/ui/button";
    import { Input } from "@/components/ui/input";
    import * as Table from "@/components/ui/table";
    import { Link, router } from "@inertiajs/svelte";
    import { Calendar, CheckCircle2, Clock, GraduationCap } from "@lucide/svelte";

    let { peserta = [] } = $props();
    let search = $state("");
    let statusFilter = $state("semua");

    const filterOptions = [
        { value: "semua", label: "Semua" },
        { value: "diterima", label: "Dalam Pembinaan" },
        { value: "pengajuan_lepas_mandiri", label: "Menunggu Keputusan" },
        { value: "lepas_mandiri", label: "Lepas Mandiri" },
    ];

    let filteredPeserta = $derived(
        peserta.filter((item: any) => {
            const q = search.toLowerCase().trim();
            const matchSearch =
                !q ||
                item.nama?.toLowerCase().includes(q) ||
                item.no_pendaftaran?.toLowerCase().includes(q) ||
                item.nik?.toLowerCase().includes(q) ||
                item.jenis_disabilitas?.nama?.toLowerCase().includes(q) ||
                item.wali?.user?.name?.toLowerCase().includes(q);

            const matchStatus =
                statusFilter === "semua" ||
                item.status_pendaftaran === statusFilter;

            return matchSearch && matchStatus;
        }),
    );

    function countStatus(status: string) {
        if (status === "semua") return peserta.length;
        return peserta.filter((p: any) => p.status_pendaftaran === status)
            .length;
    }

    function formatDate(date: string | null) {
        if (!date) return "-";
        return new Date(date).toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
    }

    function statusLabel(status: string) {
        switch (status) {
            case "diterima":
                return "DITERIMA / PEMBINAAN";
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

    function openDetail(id: number) {
        router.visit(`/pekerja-sosial/evaluasi/${id}`);
    }
</script>

<AppHead title="Evaluasi Kesiapan Lepas Kemandirian" />

<div class="p-6 w-full space-y-6">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight">
                Evaluasi Kesiapan Lepas Kemandirian
            </h1>
            <p class="text-sm text-muted-foreground">
                Daftar seluruh klien berstatus diterima beserta lama pembinaan dan jumlah kelas yang sudah dihadiri. Klik klien untuk melihat rekap nilai mata pelajaran dan mengajukan lepas mandiri ke Kepala Seksi.
            </p>
        </div>
    </div>

    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex flex-wrap gap-2">
            {#each filterOptions as opt (opt.value)}
                <Button
                    size="sm"
                    variant={statusFilter === opt.value ? "default" : "outline"}
                    onclick={() => (statusFilter = opt.value)}
                >
                    {opt.label}
                    <span class="ml-1.5 rounded-full bg-background/20 px-1.5 text-xs">
                        {countStatus(opt.value)}
                    </span>
                </Button>
            {/each}
        </div>
        <div class="w-full max-w-sm">
            <Input
                id="search-evaluasi"
                type="search"
                placeholder="Cari no pendaftaran, NIK, nama klien, atau disabilitas..."
                bind:value={search}
            />
        </div>
    </div>

    <div class="rounded-md border bg-card">
        <Table.Root>
            <Table.Header>
                <Table.Row>
                    <Table.Head>No. Pendaftaran</Table.Head>
                    <Table.Head>Nama Klien</Table.Head>
                    <Table.Head>Jenis Disabilitas</Table.Head>
                    <Table.Head>Tanggal Mulai Pembinaan</Table.Head>
                    <Table.Head>Lama Pembinaan</Table.Head>
                    <Table.Head>Kelas Dihadiri</Table.Head>
                    <Table.Head>Status & Keterangan</Table.Head>
                    <Table.Head class="text-right">Aksi</Table.Head>
                </Table.Row>
            </Table.Header>
            <Table.Body>
                {#if filteredPeserta.length === 0}
                    <Table.Row>
                        <Table.Cell colspan={8} class="h-24 text-center text-muted-foreground">
                            Tidak ada data klien untuk dievaluasi.
                        </Table.Cell>
                    </Table.Row>
                {:else}
                    {#each filteredPeserta as item (item.id)}
                        <Table.Row
                            class="cursor-pointer hover:bg-muted/50 transition-colors"
                            onclick={() => openDetail(item.id)}
                        >
                            <Table.Cell class="font-medium">
                                {item.no_pendaftaran || "-"}
                            </Table.Cell>
                            <Table.Cell>
                                <div class="font-semibold">{item.nama}</div>
                                <div class="text-xs text-muted-foreground">
                                    NIK: {item.nik || "-"} · Wali: {item.wali?.user?.name || "-"}
                                </div>
                            </Table.Cell>
                            <Table.Cell>
                                <Badge variant="outline">
                                    {item.jenis_disabilitas?.nama || "-"}
                                </Badge>
                            </Table.Cell>
                            <Table.Cell class="whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 text-sm">
                                    <Calendar class="h-3.5 w-3.5 text-muted-foreground" />
                                    {formatDate(item.tanggal_mulai_diterima)}
                                </div>
                            </Table.Cell>
                            <Table.Cell class="whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 font-medium text-sm">
                                    <Clock class="h-3.5 w-3.5 text-primary" />
                                    <span>{item.lama_pembinaan || "-"}</span>
                                </div>
                                {#if item.tanggal_mulai_diterima}
                                    <div class="text-xs text-muted-foreground">
                                        {item.lama_pembinaan_hari ?? 0} hari pembinaan
                                    </div>
                                {/if}
                            </Table.Cell>
                            <Table.Cell class="whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800 dark:bg-green-950/60 dark:text-green-300">
                                    <CheckCircle2 class="h-3.5 w-3.5" />
                                    {item.hadir_count ?? 0} Kelas Hadir
                                </div>
                                {#if (item.izin_count ?? 0) > 0 || (item.absen_count ?? 0) > 0}
                                    <div class="mt-1 text-[11px] text-muted-foreground">
                                        Izin: {item.izin_count ?? 0} · Absen: {item.absen_count ?? 0}
                                    </div>
                                {/if}
                            </Table.Cell>
                            <Table.Cell class="max-w-xs">
                                <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {statusBadgeClass(item.status_pendaftaran)}">
                                    {statusLabel(item.status_pendaftaran)}
                                </span>
                                {#if item.status_pendaftaran === "diterima" && item.keterangan_keputusan_lepas_mandiri}
                                    <div class="mt-1 text-xs font-medium text-red-600 dark:text-red-400">
                                        Ditolak Kepala Seksi: {item.keterangan_keputusan_lepas_mandiri}
                                    </div>
                                {:else if item.status_pendaftaran === "pengajuan_lepas_mandiri" && item.keterangan_pengajuan_lepas_mandiri}
                                    <div class="mt-1 text-xs text-muted-foreground truncate">
                                        Pengajuan: {item.keterangan_pengajuan_lepas_mandiri}
                                    </div>
                                {:else if item.status_pendaftaran === "lepas_mandiri" && item.keterangan_keputusan_lepas_mandiri}
                                    <div class="mt-1 text-xs text-emerald-700 dark:text-emerald-400 truncate">
                                        Keputusan: {item.keterangan_keputusan_lepas_mandiri}
                                    </div>
                                {/if}
                            </Table.Cell>
                            <Table.Cell
                                class="text-right"
                                onclick={(e) => e.stopPropagation()}
                            >
                                <Button variant="outline" size="sm" asChild>
                                    <Link
                                        id={`evaluasi-klien-${item.id}`}
                                        href={`/pekerja-sosial/evaluasi/${item.id}`}
                                    >
                                        <GraduationCap class="mr-1.5 h-4 w-4" />
                                        {item.status_pendaftaran === "diterima"
                                            ? "Evaluasi & Ajukan"
                                            : "Detail Evaluasi"}
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
