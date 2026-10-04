<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "User Master",
                href: "/user-master",
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
    import * as Select from "@/components/ui/select";
    import { Badge } from "@/components/ui/badge";
    import AppHead from "@/components/AppHead.svelte";
    import * as DropdownMenu from "@/components/ui/dropdown-menu";
    import { MoreVertical, Edit, Trash2 } from "@lucide/svelte";

    let { users, roles, filters } = $props();

    // State for search and sort
    let search = $state(untrack(() => filters.search || ""));
    let sortField = $state(untrack(() => filters.sortField || "id"));
    let sortDirection = $state(untrack(() => filters.sortDirection || "desc"));
    let searchTimeout: ReturnType<typeof setTimeout>;

    function applyFilters() {
        router.get(
            "/user-master",
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

    // State for Create/Edit Modal
    let isDialogOpen = $state(false);
    let isEditing = $state(false);
    let currentUserId = $state<number | null>(null);

    const form = useForm({
        name: "",
        email: "",
        password: "",
        role: "",
    });

    function openCreateDialog() {
        isEditing = false;
        currentUserId = null;
        form.reset();
        form.clearErrors();
        isDialogOpen = true;
    }

    function openEditDialog(user: any) {
        isEditing = true;
        currentUserId = user.id;
        form.name = user.name;
        form.email = user.email;
        form.password = "";
        form.role =
            user.roles && user.roles.length > 0 ? user.roles[0].name : "";
        form.clearErrors();
        isDialogOpen = true;
    }

    function submit() {
        if (isEditing && currentUserId) {
            form.put(`/user-master/${currentUserId}`, {
                onSuccess: () => {
                    isDialogOpen = false;
                },
            });
        } else {
            form.post("/user-master", {
                onSuccess: () => {
                    isDialogOpen = false;
                },
            });
        }
    }

    function deleteUser(user: any) {
        if (confirm(`Apakah Anda yakin ingin menghapus user ${user.name}?`)) {
            router.delete(`/user-master/${user.id}`);
        }
    }
</script>

<AppHead title="User Master" />

<div class="p-6 w-full">
    <div class="mb-4 flex items-center justify-between">
        <div class="w-full max-w-sm">
            <Input
                type="search"
                placeholder="Cari nama atau email..."
                value={search}
                oninput={handleSearch}
            />
        </div>
        <Button onclick={openCreateDialog}>Tambah User</Button>
    </div>

    <div class="rounded-md border bg-card">
        <Table.Root>
            <Table.Header>
                <Table.Row>
                    <Table.Head
                        class="cursor-pointer select-none"
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
                        onclick={() => handleSort("name")}
                    >
                        Nama {sortField === "name"
                            ? sortDirection === "asc"
                                ? "↑"
                                : "↓"
                            : ""}
                    </Table.Head>
                    <Table.Head
                        class="cursor-pointer select-none"
                        onclick={() => handleSort("email")}
                    >
                        Email {sortField === "email"
                            ? sortDirection === "asc"
                                ? "↑"
                                : "↓"
                            : ""}
                    </Table.Head>
                    <Table.Head>Role</Table.Head>
                    <Table.Head class="text-right">Aksi</Table.Head>
                </Table.Row>
            </Table.Header>
            <Table.Body>
                {#each users.data as user (user.id)}
                    <Table.Row>
                        <Table.Cell class="font-medium">{user.id}</Table.Cell>
                        <Table.Cell>{user.name}</Table.Cell>
                        <Table.Cell>{user.email}</Table.Cell>
                        <Table.Cell>
                            {#if user.roles && user.roles.length > 0}
                                {#each user.roles as role}
                                    <Badge variant="secondary" class="mr-1"
                                        >{role.name}</Badge
                                    >
                                {/each}
                            {:else}
                                <span class="text-muted-foreground text-sm"
                                    >-</span
                                >
                            {/if}
                        </Table.Cell>
                        <Table.Cell class="text-right">
                            <DropdownMenu.Root>
                                <DropdownMenu.Trigger>
                                    <Button variant="ghost" size="icon">
                                        <MoreVertical class="h-4 w-4" />
                                    </Button>
                                </DropdownMenu.Trigger>
                                <DropdownMenu.Content align="end">
                                    <DropdownMenu.Item
                                        onclick={() => openEditDialog(user)}
                                    >
                                        <Edit class="mr-2 h-4 w-4" />
                                        Edit
                                    </DropdownMenu.Item>
                                    <DropdownMenu.Item
                                        class="text-destructive focus:bg-destructive/10 focus:text-destructive"
                                        onclick={() => deleteUser(user)}
                                    >
                                        <Trash2 class="mr-2 h-4 w-4" />
                                        Hapus
                                    </DropdownMenu.Item>
                                </DropdownMenu.Content>
                            </DropdownMenu.Root>
                        </Table.Cell>
                    </Table.Row>
                {:else}
                    <Table.Row>
                        <Table.Cell colspan={5} class="h-24 text-center">
                            Tidak ada data.
                        </Table.Cell>
                    </Table.Row>
                {/each}
            </Table.Body>
        </Table.Root>
    </div>

    <div class="mt-4 flex items-center justify-between">
        <div class="text-sm text-muted-foreground">
            Menampilkan {users.from || 0} hingga {users.to || 0} dari total {users.total}
            data.
        </div>
        <div class="flex items-center gap-1">
            {#each users.links as link}
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
            <Dialog.Title
                >{isEditing ? "Edit User" : "Tambah User"}</Dialog.Title
            >
            <Dialog.Description>
                {isEditing
                    ? "Ubah data user di bawah ini. Kosongkan password jika tidak ingin diubah."
                    : "Masukkan data user baru beserta role-nya."}
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
                    <Label for="name">Nama</Label>
                    <Input id="name" bind:value={form.name} />
                    {#if form.errors.name}
                        <span class="text-sm text-destructive"
                            >{form.errors.name}</span
                        >
                    {/if}
                </div>
                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" bind:value={form.email} />
                    {#if form.errors.email}
                        <span class="text-sm text-destructive"
                            >{form.errors.email}</span
                        >
                    {/if}
                </div>
                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        bind:value={form.password}
                    />
                    {#if form.errors.password}
                        <span class="text-sm text-destructive"
                            >{form.errors.password}</span
                        >
                    {/if}
                </div>
                <div class="grid gap-2">
                    <Label for="role">Role</Label>
                    <Select.Root
                        type="single"
                        value={form.role}
                        onValueChange={(val) => {
                            form.role = val;
                        }}
                    >
                        <Select.Trigger class="w-full">
                            {form.role || "Pilih Role"}
                        </Select.Trigger>
                        <Select.Content>
                            {#each roles as role}
                                <Select.Item value={role.name} label={role.name}
                                    >{role.name}</Select.Item
                                >
                            {/each}
                        </Select.Content>
                    </Select.Root>
                    {#if form.errors.role}
                        <span class="text-sm text-destructive"
                            >{form.errors.role}</span
                        >
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
