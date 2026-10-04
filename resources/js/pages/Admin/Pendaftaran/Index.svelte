<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Verifikasi Pendaftaran",
                href: "/admin/pendaftaran",
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
    import { router, Link } from "@inertiajs/svelte";
    import { untrack } from "svelte";

    let { pendaftarans, filters } = $props();

    let search = $state(untrack(() => filters?.search || ""));
    let sortField = $state(untrack(() => filters?.sortField || "created_at"));
    let sortDirection = $state(untrack(() => filters?.sortDirection || "desc"));
    let searchTimeout: ReturnType<typeof setTimeout>;

    function applyFilters() {
        router.get(
            "/admin/pendaftaran",
            { search, sortField, sortDirection },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }

    function handleSearch(e: Event) {
        const value = (e.target as HTMLInputElement).value;
        search = value;
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 500);
    }

    function handleSort(field: string) {
        if (sortField === field) {
            sortDirection = sortDirection === "asc" ? "desc" : "asc";
        } else {
            sortField = field;
            sortDirection = "asc";
        }
        applyFilters();
    }

    function formatStatus(status) {
        if (!status) return "-";
        return status.replace('_', ' ').toUpperCase();
    }
</script>

<AppHead title="Verifikasi Pendaftaran" />

<div class="p-6 w-full">
    <div class="mb-4 flex items-center justify-between">
        <div class="w-full max-w-sm">
            <Input
                type="search"
                placeholder="Cari no pendaftaran, nama, atau NIK..."
                value={search}
                oninput={handleSearch}
            />
        </div>
    </div>

    <div class="rounded-md border bg-card">
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead
                        class="cursor-pointer select-none"
                        onclick={() => handleSort("no_pendaftaran")}
                    >
                        No. Pendaftaran {sortField === "no_pendaftaran" ? (sortDirection === "asc" ? "↑" : "↓") : ""}
                    </TableHead>
                    <TableHead
                        class="cursor-pointer select-none"
                        onclick={() => handleSort("nama")}
                    >
                        Nama Klien {sortField === "nama" ? (sortDirection === "asc" ? "↑" : "↓") : ""}
                    </TableHead>
                    <TableHead
                        class="cursor-pointer select-none"
                        onclick={() => handleSort("tanggal_lahir")}
                    >
                        Tanggal Lahir {sortField === "tanggal_lahir" ? (sortDirection === "asc" ? "↑" : "↓") : ""}
                    </TableHead>
                    <TableHead>Pendidikan</TableHead>
                    <TableHead
                        class="cursor-pointer select-none"
                        onclick={() => handleSort("status_pendaftaran")}
                    >
                        Status {sortField === "status_pendaftaran" ? (sortDirection === "asc" ? "↑" : "↓") : ""}
                    </TableHead>
                    <TableHead class="text-right">Aksi</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {#if !pendaftarans.data || pendaftarans.data.length === 0}
                    <TableRow>
                        <TableCell colspan="6" class="text-center h-24">Tidak ada data pendaftaran aktif.</TableCell>
                    </TableRow>
                {:else}
                    {#each pendaftarans.data as item}
                        <TableRow>
                            <TableCell class="font-medium">{item.no_pendaftaran}</TableCell>
                            <TableCell>{item.nama}</TableCell>
                            <TableCell>{item.tanggal_lahir}</TableCell>
                            <TableCell>{item.pendidikan}</TableCell>
                            <TableCell>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    {item.status_pendaftaran === 'verifikasi_berkas' ? 'bg-blue-100 text-blue-800' : ''}
                                    {item.status_pendaftaran === 'perbaikan_berkas' ? 'bg-orange-100 text-orange-800' : ''}
                                    {item.status_pendaftaran === 'asesmen' ? 'bg-indigo-100 text-indigo-800' : ''}
                                    {item.status_pendaftaran === 'diterima' ? 'bg-green-100 text-green-800' : ''}
                                    {item.status_pendaftaran === 'ditolak' ? 'bg-red-100 text-red-800' : ''}
                                ">
                                    {formatStatus(item.status_pendaftaran)}
                                </span>
                            </TableCell>
                            <TableCell class="text-right">
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={`/admin/pendaftaran/${item.id}`}>Detail / Verifikasi</Link>
                                </Button>
                            </TableCell>
                        </TableRow>
                    {/each}
                {/if}
            </TableBody>
        </Table>
    </div>

    {#if pendaftarans.links}
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-muted-foreground">
                Menampilkan {pendaftarans.from || 0} hingga {pendaftarans.to || 0} dari total {pendaftarans.total}
                data.
            </div>
            <div class="flex items-center gap-1">
                {#each pendaftarans.links as link}
                    <!-- eslint-disable-next-line svelte/valid-compile -->
                    <Button
                        variant={link.active ? "default" : "outline"}
                        size="sm"
                        disabled={!link.url}
                        class={!link.url ? "opacity-50 cursor-not-allowed" : ""}
                        onclick={() => {
                            if (link.url)
                                router.get(
                                    link.url,
                                    { search, sortField, sortDirection },
                                    { preserveState: true },
                                );
                        }}
                    >
                        {@html link.label}
                    </Button>
                {/each}
            </div>
        </div>
    {/if}
</div>
