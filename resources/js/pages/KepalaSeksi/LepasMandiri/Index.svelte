<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Evaluasi Lepas Mandiri",
                href: "/kepala-seksi/lepas-mandiri",
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
        { value: "pengajuan_lepas_mandiri", label: "Menunggu Keputusan" },
        { value: "lepas_mandiri", label: "Lepas Mandiri" },
        { value: "diterima", label: "Dalam Pembinaan / Ditolak" },
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
            case "pengajuan_lepas_mandiri":
                return "MENUNGGU KEPUTUSAN";
            case "lepas_mandiri":
                return "LEPAS MANDIRI";
            case "diterima":
                return "DITERIMA / PEMBINAAN";
            default:
                return status?.replaceAll("_", " ").toUpperCase() || "-";
        }
    }

    function statusBadgeClass(status: string) {
        switch (status) {
            case "pengajuan_lepas_mandiri":
                return "bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300";
            case "lepas_mandiri":
                return "bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300";
            case "diterima":
                return "bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300";
            default:
                return "bg-slate-100 text-slate-800";
        }
    }

    function openDetail(id: number) {
        router.visit(`/kepala-seksi/lepas-mandiri/${id}`);
    }
</script>

<AppHead title="Evaluasi Lepas Mandiri — Kepala Seksi" />

<div class="p-6 w-full space-y-6">
    <div>
        <h1 class="text-xl font-bold tracking-tight">
            Persetujuan Evaluasi Lepas Kemandirian
        </h1>
        <p class="text-sm text-muted-foreground">
            Tinjau pengajuan lepas mandiri dari Pekerja Sosial, periksa rekap nilai pembelajaran klien, dan berikan keputusan Terima (Lepas Mandiri) atau Tolak (Kembalikan ke Diterima) beserta keterangannya.
        </p>
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
                id="search-lepas-mandiri"
                type="search"
                placeholder="Cari no pendaftaran, NIK, atau nama klien..."
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
                    <Table.Head>Lama Pembinaan</Table.Head>
                    <Table.Head>Kelas Dihadiri</Table.Head>
                    <Table.Head>Keterangan Pengajuan</Table.Head>
                    <Table.Head>Status & Keputusan</Table.Head>
                    <Table.Head class="text-right">Aksi</Table.Head>
                </Table.Row>
            </Table.Header>
            <Table.Body>
                {#if filteredPeserta.length === 0}
                    <Table.Row>
                        <Table.Cell colspan={8} class="h-24 text-center text-muted-foreground">
                            Tidak ada data pengajuan atau evaluasi lepas mandiri.
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
                                <div class="inline-flex items-center gap-1.5 font-medium text-sm">
                                    <Clock class="h-3.5 w-3.5 text-primary" />
                                    <span>{item.lama_pembinaan || "-"}</span>
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    Mulai: {formatDate(item.tanggal_mulai_diterima)}
                                </div>
                            </Table.Cell>
                            <Table.Cell class="whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800 dark:bg-green-950/60 dark:text-green-300">
                                    <CheckCircle2 class="h-3.5 w-3.5" />
                                    {item.hadir_count ?? 0} Kelas Hadir
                                </div>
                            </Table.Cell>
                            <Table.Cell class="max-w-xs text-xs">
                                {#if item.keterangan_pengajuan_lepas_mandiri}
                                    <div class="font-medium line-clamp-2">
                                        {item.keterangan_pengajuan_lepas_mandiri}
                                    </div>
                                    {#if item.tanggal_pengajuan_lepas_mandiri}
                                        <div class="mt-0.5 text-muted-foreground">
                                            Tgl. Pengajuan: {formatDate(item.tanggal_pengajuan_lepas_mandiri)}
                                        </div>
                                    {/if}
                                {:else}
                                    <span class="text-muted-foreground">-</span>
                                {/if}
                            </Table.Cell>
                            <Table.Cell class="max-w-xs">
                                <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold {statusBadgeClass(item.status_pendaftaran)}">
                                    {statusLabel(item.status_pendaftaran)}
                                </span>
                                {#if item.keterangan_keputusan_lepas_mandiri}
                                    <div class="mt-1 text-xs text-muted-foreground truncate">
                                        Keputusan: {item.keterangan_keputusan_lepas_mandiri}
                                    </div>
                                {/if}
                            </Table.Cell>
                            <Table.Cell
                                class="text-right"
                                onclick={(e) => e.stopPropagation()}
                            >
                                <Button
                                    variant={item.status_pendaftaran === "pengajuan_lepas_mandiri"
                                        ? "default"
                                        : "outline"}
                                    size="sm"
                                    asChild
                                >
                                    <Link
                                        id={`detail-lepas-mandiri-${item.id}`}
                                        href={`/kepala-seksi/lepas-mandiri/${item.id}`}
                                    >
                                        <GraduationCap class="mr-1.5 h-4 w-4" />
                                        {item.status_pendaftaran === "pengajuan_lepas_mandiri"
                                            ? "Tinjau & Putuskan"
                                            : "Detail"}
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
