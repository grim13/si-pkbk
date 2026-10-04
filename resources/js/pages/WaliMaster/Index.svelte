<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Wali Master",
                href: "/wali-master",
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
    import * as Dialog from "@/components/ui/dialog";
    import * as Table from "@/components/ui/table";
    import AppHead from "@/components/AppHead.svelte";
    import * as DropdownMenu from "@/components/ui/dropdown-menu";
    import { MoreVertical, Edit, Trash2 } from "@lucide/svelte";

    let { walis, filters } = $props();

    // State for search and sort
    let search = $state(untrack(() => filters.search || ""));
    let sortField = $state(untrack(() => filters.sortField || "id"));
    let sortDirection = $state(untrack(() => filters.sortDirection || "desc"));
    let searchTimeout: ReturnType<typeof setTimeout>;

    function applyFilters() {
        router.get(
            "/wali-master",
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
        }, 500); // Debounce 500ms
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
    let currentWaliId = $state<number | null>(null);

    const form = useForm({
        name: "",
        email: "",
        nik: "",
        alamat: "",
        tempat_lahir: "",
        tanggal_lahir: "",
        nomor_hp: "",
    });

    function openEditDialog(wali: any) {
        currentWaliId = wali.id;
        form.name = wali.user?.name || "";
        form.email = wali.user?.email || "";
        form.nik = wali.nik;
        form.alamat = wali.alamat;
        form.tempat_lahir = wali.tempat_lahir;
        form.tanggal_lahir = wali.tanggal_lahir;
        form.nomor_hp = wali.nomor_hp;
        form.clearErrors();
        isDialogOpen = true;
    }

    function submit() {
        if (currentWaliId) {
            form.put(`/wali-master/${currentWaliId}`, {
                onSuccess: () => {
                    isDialogOpen = false;
                },
            });
        }
    }

    function deleteWali(wali: any) {
        if (confirm(`Apakah Anda yakin ingin menghapus data wali untuk user ${wali.user?.name}?`)) {
            router.delete(`/wali-master/${wali.id}`);
        }
    }
</script>

<AppHead title="Wali Master" />

<div class="p-6 w-full">
    <div class="mb-4 flex items-center justify-between">
        <div class="w-full max-w-sm">
            <Input
                type="search"
                placeholder="Cari nama atau NIK..."
                value={search}
                oninput={handleSearch}
            />
        </div>
    </div>

    <div class="rounded-md border bg-card">
        <Table.Root>
            <Table.Header>
                <Table.Row>
                    <Table.Head class="cursor-pointer select-none" onclick={() => handleSort("id")}>
                        ID {sortField === "id" ? (sortDirection === "asc" ? "↑" : "↓") : ""}
                    </Table.Head>
                    <Table.Head>Nama (User)</Table.Head>
                    <Table.Head class="cursor-pointer select-none" onclick={() => handleSort("nik")}>
                        NIK {sortField === "nik" ? (sortDirection === "asc" ? "↑" : "↓") : ""}
                    </Table.Head>
                    <Table.Head>Alamat</Table.Head>
                    <Table.Head>No. HP</Table.Head>
                    <Table.Head class="text-right">Aksi</Table.Head>
                </Table.Row>
            </Table.Header>
            <Table.Body>
                {#each walis.data as wali (wali.id)}
                    <Table.Row>
                        <Table.Cell class="font-medium">{wali.id}</Table.Cell>
                        <Table.Cell>{wali.user?.name}</Table.Cell>
                        <Table.Cell>{wali.nik}</Table.Cell>
                        <Table.Cell>{wali.alamat}</Table.Cell>
                        <Table.Cell>{wali.nomor_hp}</Table.Cell>
                        <Table.Cell class="text-right">
                            <DropdownMenu.Root>
                                <DropdownMenu.Trigger>
                                    <Button variant="ghost" size="icon">
                                        <MoreVertical class="h-4 w-4" />
                                    </Button>
                                </DropdownMenu.Trigger>
                                <DropdownMenu.Content align="end">
                                    <DropdownMenu.Item onclick={() => openEditDialog(wali)}>
                                        <Edit class="mr-2 h-4 w-4" />
                                        Edit
                                    </DropdownMenu.Item>
                                    <DropdownMenu.Item class="text-destructive focus:bg-destructive/10 focus:text-destructive" onclick={() => deleteWali(wali)}>
                                        <Trash2 class="mr-2 h-4 w-4" />
                                        Hapus
                                    </DropdownMenu.Item>
                                </DropdownMenu.Content>
                            </DropdownMenu.Root>
                        </Table.Cell>
                    </Table.Row>
                {:else}
                    <Table.Row>
                        <Table.Cell colspan={6} class="h-24 text-center">
                            Tidak ada data.
                        </Table.Cell>
                    </Table.Row>
                {/each}
            </Table.Body>
        </Table.Root>
    </div>

    <div class="mt-4 flex items-center justify-between">
        <div class="text-sm text-muted-foreground">
            Menampilkan {walis.from || 0} hingga {walis.to || 0} dari total {walis.total} data.
        </div>
        <div class="flex items-center gap-1">
            {#each walis.links as link}
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
</div>

<Dialog.Root bind:open={isDialogOpen}>
    <Dialog.Content class="sm:max-w-[425px]">
        <Dialog.Header>
            <Dialog.Title>Edit Data Wali</Dialog.Title>
            <Dialog.Description>
                Ubah data wali di bawah ini.
            </Dialog.Description>
        </Dialog.Header>
        <form
            onsubmit={(e) => {
                e.preventDefault();
                submit();
            }}
        >
            <div class="grid gap-4 py-4 max-h-[60vh] overflow-y-auto pr-2">
                <div class="grid gap-2">
                    <Label for="name">Nama</Label>
                    <Input id="name" type="text" bind:value={form.name} />
                    {#if form.errors.name}
                        <span class="text-sm text-destructive">{form.errors.name}</span>
                    {/if}
                </div>
                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" bind:value={form.email} />
                    {#if form.errors.email}
                        <span class="text-sm text-destructive">{form.errors.email}</span>
                    {/if}
                </div>
                <div class="grid gap-2">
                    <Label for="nik">NIK</Label>
                    <Input id="nik" type="text" bind:value={form.nik} />
                    {#if form.errors.nik}
                        <span class="text-sm text-destructive">{form.errors.nik}</span>
                    {/if}
                </div>
                <div class="grid gap-2">
                    <Label for="alamat">Alamat</Label>
                    <Input id="alamat" type="text" bind:value={form.alamat} />
                    {#if form.errors.alamat}
                        <span class="text-sm text-destructive">{form.errors.alamat}</span>
                    {/if}
                </div>
                <div class="grid gap-2">
                    <Label for="tempat_lahir">Tempat Lahir</Label>
                    <Input id="tempat_lahir" type="text" bind:value={form.tempat_lahir} />
                    {#if form.errors.tempat_lahir}
                        <span class="text-sm text-destructive">{form.errors.tempat_lahir}</span>
                    {/if}
                </div>
                <div class="grid gap-2">
                    <Label for="tanggal_lahir">Tanggal Lahir</Label>
                    <Input id="tanggal_lahir" type="date" bind:value={form.tanggal_lahir} />
                    {#if form.errors.tanggal_lahir}
                        <span class="text-sm text-destructive">{form.errors.tanggal_lahir}</span>
                    {/if}
                </div>
                <div class="grid gap-2">
                    <Label for="nomor_hp">Nomor HP</Label>
                    <Input id="nomor_hp" type="text" bind:value={form.nomor_hp} />
                    {#if form.errors.nomor_hp}
                        <span class="text-sm text-destructive">{form.errors.nomor_hp}</span>
                    {/if}
                </div>
            </div>
            <Dialog.Footer>
                <Button type="submit" disabled={form.processing}>
                    {form.processing ? "Menyimpan..." : "Simpan"}
                </Button>
            </Dialog.Footer>
        </form>
    </Dialog.Content>
</Dialog.Root>
