<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Penentuan Kelulusan",
                href: "/kepala-seksi/kelulusan",
            },
        ],
    };
</script>

<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import { Button } from "@/components/ui/button";
    import { Input } from "@/components/ui/input";
    import {
        Table,
        TableBody,
        TableCell,
        TableHead,
        TableHeader,
        TableRow,
    } from "@/components/ui/table";
    import { Link } from "@inertiajs/svelte";

    let { peserta } = $props();
    let search = $state("");
    let statusFilter = $state("semua");

    const filterOptions = [
        { value: "semua", label: "Semua" },
        { value: "selesai_asesmen", label: "Menunggu Keputusan" },
        { value: "diterima", label: "Diterima" },
        { value: "ditolak", label: "Ditolak" },
    ];

    let filteredPeserta = $derived(
        peserta.filter((item: any) => {
            const q = search.toLowerCase();
            const matchSearch =
                item.nama.toLowerCase().includes(q) ||
                (item.no_pendaftaran &&
                    item.no_pendaftaran.toLowerCase().includes(q)) ||
                (item.nik && item.nik.toLowerCase().includes(q));
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

    function formatStatus(status: string) {
        if (status === "selesai_asesmen") return "MENUNGGU KEPUTUSAN";
        return status ? status.replaceAll("_", " ").toUpperCase() : "-";
    }

    function statusClass(status: string) {
        switch (status) {
            case "selesai_asesmen":
                return "bg-amber-100 text-amber-800";
            case "diterima":
                return "bg-green-100 text-green-800";
            case "ditolak":
                return "bg-red-100 text-red-800";
            default:
                return "bg-slate-100 text-slate-800";
        }
    }

    function formatDate(date: string | null) {
        if (!date) return "-";
        return new Date(date).toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
    }
</script>

<AppHead title="Penentuan Kelulusan" />

<div class="p-6 w-full">
    <div class="mb-6">
        <h1 class="text-xl font-bold tracking-tight">Penentuan Kelulusan</h1>
        <p class="text-sm text-muted-foreground">
            Daftar peserta yang telah menyelesaikan asesmen. Tinjau data dan
            tentukan kelulusan.
        </p>
    </div>

    <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex flex-wrap gap-2">
            {#each filterOptions as opt}
                <Button
                    id={`filter-${opt.value}`}
                    size="sm"
                    variant={statusFilter === opt.value ? "default" : "outline"}
                    onclick={() => (statusFilter = opt.value)}
                >
                    {opt.label}
                    <span
                        class="ml-1.5 rounded-full bg-background/20 px-1.5 text-xs"
                        >{countStatus(opt.value)}</span
                    >
                </Button>
            {/each}
        </div>
        <div class="w-full max-w-sm">
            <Input
                id="search-kelulusan"
                type="search"
                placeholder="Cari no pendaftaran, NIK, atau nama..."
                bind:value={search}
            />
        </div>
    </div>

    <div class="rounded-md border bg-card">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>No. Pendaftaran</TableHead>
                    <TableHead>Nama Klien</TableHead>
                    <TableHead>Wali</TableHead>
                    <TableHead>Keterampilan Diminati</TableHead>
                    <TableHead>Tgl. Asesmen</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead class="text-right">Aksi</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {#if filteredPeserta.length === 0}
                    <TableRow>
                        <TableCell colspan={7} class="text-center h-24">
                            Tidak ada data peserta.
                        </TableCell>
                    </TableRow>
                {:else}
                    {#each filteredPeserta as item (item.id)}
                        <TableRow>
                            <TableCell class="font-medium"
                                >{item.no_pendaftaran}</TableCell
                            >
                            <TableCell>{item.nama}</TableCell>
                            <TableCell>{item.wali?.user?.name || "-"}</TableCell>
                            <TableCell>{item.keterampilan_yang_diminati}</TableCell>
                            <TableCell
                                >{formatDate(item.asesmen?.tanggal_asesmen)}</TableCell
                            >
                            <TableCell>
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-semibold {statusClass(
                                        item.status_pendaftaran,
                                    )}"
                                >
                                    {formatStatus(item.status_pendaftaran)}
                                </span>
                            </TableCell>
                            <TableCell class="text-right">
                                <Button variant="outline" size="sm" asChild>
                                    <Link
                                        id={`detail-${item.id}`}
                                        href={`/kepala-seksi/kelulusan/${item.id}`}
                                    >
                                        {item.status_pendaftaran ===
                                        "selesai_asesmen"
                                            ? "Tinjau & Putuskan"
                                            : "Detail"}
                                    </Link>
                                </Button>
                            </TableCell>
                        </TableRow>
                    {/each}
                {/if}
            </TableBody>
        </Table>
    </div>
</div>
