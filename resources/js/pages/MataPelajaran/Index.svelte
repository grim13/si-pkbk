<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Mata Pelajaran",
                href: "/mata-pelajaran",
            },
        ],
    };
</script>

<script lang="ts">
    import { router, useForm } from "@inertiajs/svelte";
    import { untrack } from "svelte";
    import { Button } from "@/components/ui/button";
    import { Input } from "@/components/ui/input";
    import { Label } from "@/components/ui/label";
    import { Textarea } from "@/components/ui/textarea";
    import { Checkbox } from "@/components/ui/checkbox";
    import * as Dialog from "@/components/ui/dialog";
    import * as Table from "@/components/ui/table";
    import { Badge } from "@/components/ui/badge";
    import AppHead from "@/components/AppHead.svelte";
    import * as DropdownMenu from "@/components/ui/dropdown-menu";
    import { MoreVertical, Edit, Trash2 } from "@lucide/svelte";
    import DataTable from "@/components/DataTable.svelte";

    let {
        mataPelajaran,
        jenisDisabilitasList = [],
        filters = {},
    } = $props();

    let search = $state(untrack(() => filters.search || ""));
    let sortField = $state(untrack(() => filters.sortField || "id"));
    let sortDirection = $state(untrack(() => filters.sortDirection || "desc"));
    let perPage = $state(untrack(() => filters.perPage || "10"));

    function applyFilters() {
        router.get(
            "/mata-pelajaran",
            { search, sortField, sortDirection, perPage },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }

    function handleFilterChange(newFilters: { search: string; perPage: string }) {
        search = newFilters.search;
        perPage = newFilters.perPage;
        applyFilters();
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

    let isDialogOpen = $state(false);
    let isEditing = $state(false);
    let currentId = $state<number | null>(null);

    const form = useForm({
        nama: "",
        keterangan: "",
        jenis_disabilitas_ids: [] as number[],
    });

    function openCreateDialog() {
        isEditing = false;
        currentId = null;
        form.nama = "";
        form.keterangan = "";
        form.jenis_disabilitas_ids = [];
        form.clearErrors();
        isDialogOpen = true;
    }

    function openEditDialog(item: any) {
        isEditing = true;
        currentId = item.id;
        form.nama = item.nama || "";
        form.keterangan = item.keterangan || "";
        form.jenis_disabilitas_ids = item.jenis_disabilitas
            ? item.jenis_disabilitas.map((jd: any) => Number(jd.id))
            : [];
        form.clearErrors();
        isDialogOpen = true;
    }

    function toggleDisabilitas(id: number, checked: boolean) {
        if (checked) {
            if (!form.jenis_disabilitas_ids.includes(id)) {
                form.jenis_disabilitas_ids = [...form.jenis_disabilitas_ids, id];
            }
        } else {
            form.jenis_disabilitas_ids = form.jenis_disabilitas_ids.filter(
                (item) => item !== id,
            );
        }
    }

    function submit() {
        if (isEditing && currentId) {
            form.put(`/mata-pelajaran/${currentId}`, {
                onSuccess: () => {
                    isDialogOpen = false;
                },
            });
        } else {
            form.post("/mata-pelajaran", {
                onSuccess: () => {
                    isDialogOpen = false;
                },
            });
        }
    }

    function deleteItem(item: any) {
        if (
            confirm(
                `Apakah Anda yakin ingin menghapus mata pelajaran "${item.nama}"?`,
            )
        ) {
            router.delete(`/mata-pelajaran/${item.id}`);
        }
    }
</script>

<AppHead title="Mata Pelajaran" />

<div class="p-6 w-full space-y-4">
    <div>
        <h1 class="text-xl font-bold tracking-tight">Master Mata Pelajaran</h1>
        <p class="text-sm text-muted-foreground">
            Kelola daftar mata pelajaran serta kategori jenis disabilitas.
        </p>
    </div>

    <DataTable
        data={mataPelajaran}
        {filters}
        onFilterChange={handleFilterChange}
        searchPlaceholder="Cari mata pelajaran atau keterangan..."
        columnsCount={5}
    >
        <Button onclick={openCreateDialog}>Tambah Mata Pelajaran</Button>

        {#snippet header()}
            <Table.Head
                class="cursor-pointer select-none w-16"
                onclick={() => handleSort("id")}
            >
                ID {sortField === "id"
                    ? sortDirection === "asc"
                        ? "↑"
                        : "↓"
                    : ""}
            </Table.Head>
            <Table.Head
                class="cursor-pointer select-none"
                onclick={() => handleSort("nama")}
            >
                Mata Pelajaran {sortField === "nama"
                    ? sortDirection === "asc"
                        ? "↑"
                        : "↓"
                    : ""}
            </Table.Head>
            <Table.Head>Jenis Disabilitas</Table.Head>
            <Table.Head>Keterangan</Table.Head>
            <Table.Head class="text-right">Aksi</Table.Head>
        {/snippet}

        {#snippet row(item)}
            <Table.Cell class="font-medium">{item.id}</Table.Cell>
            <Table.Cell class="font-semibold">{item.nama}</Table.Cell>
            <Table.Cell>
                {#if item.jenis_disabilitas && item.jenis_disabilitas.length > 0}
                    <div class="flex flex-wrap gap-1">
                        {#each item.jenis_disabilitas as jd (jd.id)}
                            <Badge variant="secondary">{jd.nama}</Badge>
                        {/each}
                    </div>
                {:else}
                    <span class="text-muted-foreground text-sm">-</span>
                {/if}
            </Table.Cell>
            <Table.Cell class="max-w-xs truncate">
                {item.keterangan || "-"}
            </Table.Cell>
            <Table.Cell class="text-right">
                <DropdownMenu.Root>
                    <DropdownMenu.Trigger>
                        <Button variant="ghost" size="icon">
                            <MoreVertical class="h-4 w-4" />
                        </Button>
                    </DropdownMenu.Trigger>
                    <DropdownMenu.Content align="end">
                        <DropdownMenu.Item onclick={() => openEditDialog(item)}>
                            <Edit class="mr-2 h-4 w-4" />
                            Edit
                        </DropdownMenu.Item>
                        <DropdownMenu.Item
                            class="text-destructive focus:bg-destructive/10 focus:text-destructive"
                            onclick={() => deleteItem(item)}
                        >
                            <Trash2 class="mr-2 h-4 w-4" />
                            Hapus
                        </DropdownMenu.Item>
                    </DropdownMenu.Content>
                </DropdownMenu.Root>
            </Table.Cell>
        {/snippet}
    </DataTable>
</div>

<Dialog.Root bind:open={isDialogOpen}>
    <Dialog.Content class="sm:max-w-[480px]">
        <Dialog.Header>
            <Dialog.Title>
                {isEditing ? "Edit Mata Pelajaran" : "Tambah Mata Pelajaran"}
            </Dialog.Title>
            <Dialog.Description>
                Lengkapi nama mata pelajaran serta jenis disabilitas yang sesuai.
            </Dialog.Description>
        </Dialog.Header>

        <form
            onsubmit={(e) => {
                e.preventDefault();
                submit();
            }}
        >
            <div class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Label for="nama">Nama Mata Pelajaran <span class="text-red-500">*</span></Label>
                    <Input
                        id="nama"
                        placeholder="Contoh: Pijat Shiatsu"
                        bind:value={form.nama}
                    />
                    {#if form.errors.nama}
                        <span class="text-sm text-destructive">{form.errors.nama}</span>
                    {/if}
                </div>

                <div class="grid gap-2">
                    <Label>Jenis Disabilitas <span class="text-red-500">*</span></Label>
                    <div class="grid grid-cols-1 gap-2 rounded-md border p-3">
                        {#each jenisDisabilitasList as jd (jd.id)}
                            <label
                                for={`disabilitas-${jd.id}`}
                                class="flex items-center space-x-2 text-sm cursor-pointer"
                            >
                                <Checkbox
                                    id={`disabilitas-${jd.id}`}
                                    checked={form.jenis_disabilitas_ids.includes(Number(jd.id))}
                                    onCheckedChange={(v) =>
                                        toggleDisabilitas(Number(jd.id), !!v)}
                                />
                                <span>{jd.nama}</span>
                            </label>
                        {/each}
                    </div>
                    {#if form.errors.jenis_disabilitas_ids}
                        <span class="text-sm text-destructive">
                            {form.errors.jenis_disabilitas_ids}
                        </span>
                    {/if}
                </div>

                <div class="grid gap-2">
                    <Label for="keterangan">Keterangan (Opsional)</Label>
                    <Textarea
                        id="keterangan"
                        placeholder="Deskripsi singkat materi pembelajaran..."
                        bind:value={form.keterangan}
                    />
                    {#if form.errors.keterangan}
                        <span class="text-sm text-destructive">{form.errors.keterangan}</span>
                    {/if}
                </div>
            </div>

            <Dialog.Footer>
                <Button
                    type="button"
                    variant="outline"
                    onclick={() => (isDialogOpen = false)}
                >
                    Batal
                </Button>
                <Button type="submit" disabled={form.processing}>
                    {form.processing ? "Menyimpan..." : "Simpan"}
                </Button>
            </Dialog.Footer>
        </form>
    </Dialog.Content>
</Dialog.Root>
