<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Asesmen Klien",
                href: "/pekerja-sosial/asesmen",
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

    let { peserta } = $props();
    let search = $state("");

    let filteredPeserta = $derived(
        peserta.filter(
            (item: any) =>
                item.nama.toLowerCase().includes(search.toLowerCase()) ||
                (item.no_pendaftaran &&
                    item.no_pendaftaran.toLowerCase().includes(search.toLowerCase())),
        ),
    );

    function formatStatus(status: string) {
        if (!status) return "-";
        return status.replace("_", " ").toUpperCase();
    }
</script>

<AppHead title="Daftar Asesmen" />

<div class="p-6 w-full">
    <div class="mb-4 flex items-center justify-between">
        <div class="w-full max-w-sm">
            <Input
                type="search"
                placeholder="Cari no pendaftaran atau nama..."
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
                    <TableHead>Tanggal Lahir</TableHead>
                    <TableHead>Pendidikan</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead class="text-right">Aksi</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                {#if filteredPeserta.length === 0}
                    <TableRow>
                        <TableCell colspan="6" class="text-center h-24">
                            Tidak ada data peserta untuk asesmen.
                        </TableCell>
                    </TableRow>
                {:else}
                    {#each filteredPeserta as item}
                        <TableRow>
                            <TableCell class="font-medium">{item.no_pendaftaran}</TableCell>
                            <TableCell>{item.nama}</TableCell>
                            <TableCell>{item.tanggal_lahir}</TableCell>
                            <TableCell>{item.pendidikan}</TableCell>
                            <TableCell>
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-semibold
                                    {item.status_pendaftaran === 'asesmen'
                                        ? 'bg-indigo-100 text-indigo-800'
                                        : ''}
                                    {item.status_pendaftaran === 'diterima'
                                        ? 'bg-green-100 text-green-800'
                                        : ''}
                                    {item.status_pendaftaran === 'ditolak'
                                        ? 'bg-red-100 text-red-800'
                                        : ''}
                                "
                                >
                                    {formatStatus(item.status_pendaftaran)}
                                </span>
                            </TableCell>
                            <TableCell class="text-right">
                                <Button variant="outline" size="sm" asChild>
                                    <Link href={`/pekerja-sosial/asesmen/${item.id}`}>
                                        Isi / Lihat Asesmen
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
