<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Jadwal Pembelajaran",
                href: "/jadwal",
            },
        ],
    };
</script>

<script lang="ts">
    import { router, useForm, Link } from "@inertiajs/svelte";
    import { untrack } from "svelte";
    import { Button } from "@/components/ui/button";
    import { Input } from "@/components/ui/input";
    import { Label } from "@/components/ui/label";
    import { Textarea } from "@/components/ui/textarea";
    import * as Dialog from "@/components/ui/dialog";
    import * as Table from "@/components/ui/table";
    import * as Select from "@/components/ui/select";
    import { Badge } from "@/components/ui/badge";
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from "@/components/ui/card";
    import AppHead from "@/components/AppHead.svelte";
    import * as DropdownMenu from "@/components/ui/dropdown-menu";
    import {
        MoreVertical,
        Edit,
        Trash2,
        Plus,
        Clock,
        MapPin,
        User,
        CalendarDays,
        List,
        Printer,
    } from "@lucide/svelte";

    let {
        jadwalList = [],
        mataPelajaranList = [],
        instrukturList = [],
        hariOptions = ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"],
        availableYears = [],
        filters = {},
    } = $props();

    let tahun = $state(untrack(() => String(filters.tahun || new Date().getFullYear())));
    let hariFilter = $state(untrack(() => filters.hari || "semua"));
    let search = $state(untrack(() => filters.search || ""));
    let viewMode = $state<"board" | "table">("board");
    let searchTimeout: ReturnType<typeof setTimeout>;

    function applyFilters() {
        router.get(
            "/jadwal",
            {
                tahun: Number(tahun),
                hari: hariFilter === "semua" ? "" : hariFilter,
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

    const displayedDays = $derived(
        hariFilter === "semua"
            ? hariOptions
            : hariOptions.filter((h: string) => h === hariFilter),
    );

    function jadwalByDay(day: string) {
        return jadwalList.filter((j: any) => j.hari === day);
    }

    function formatTime(t: string) {
        if (!t) return "-";
        return t.slice(0, 5);
    }

    // Create / Edit Modal
    let isDialogOpen = $state(false);
    let isEditing = $state(false);
    let currentId = $state<number | null>(null);

    const form = useForm({
        mata_pelajaran_id: "",
        instruktur_id: "",
        tahun: Number(untrack(() => filters.tahun || new Date().getFullYear())),
        hari: "Senin",
        jam_mulai: "08:00",
        jam_selesai: "10:00",
        ruangan: "",
        keterangan: "",
    });

    function openCreateDialog(defaultHari?: string) {
        isEditing = false;
        currentId = null;
        form.mata_pelajaran_id = "";
        form.instruktur_id =
            instrukturList.length === 1 ? String(instrukturList[0].id) : "";
        form.tahun = Number(tahun);
        form.hari = defaultHari || (hariFilter !== "semua" ? hariFilter : "Senin");
        form.jam_mulai = "08:00";
        form.jam_selesai = "10:00";
        form.ruangan = "";
        form.keterangan = "";
        form.clearErrors();
        isDialogOpen = true;
    }

    function openEditDialog(item: any) {
        isEditing = true;
        currentId = item.id;
        form.mata_pelajaran_id = item.mata_pelajaran_id
            ? String(item.mata_pelajaran_id)
            : "";
        form.instruktur_id = item.instruktur_id
            ? String(item.instruktur_id)
            : "";
        form.tahun = Number(item.tahun);
        form.hari = item.hari;
        form.jam_mulai = formatTime(item.jam_mulai);
        form.jam_selesai = formatTime(item.jam_selesai);
        form.ruangan = item.ruangan || "";
        form.keterangan = item.keterangan || "";
        form.clearErrors();
        isDialogOpen = true;
    }

    function submit() {
        form.transform((data) => ({
            ...data,
            mata_pelajaran_id: data.mata_pelajaran_id
                ? Number(data.mata_pelajaran_id)
                : null,
            instruktur_id: data.instruktur_id
                ? Number(data.instruktur_id)
                : null,
            tahun: Number(data.tahun),
        }));

        if (isEditing && currentId) {
            form.put(`/jadwal/${currentId}`, {
                onSuccess: () => {
                    isDialogOpen = false;
                },
            });
        } else {
            form.post("/jadwal", {
                onSuccess: () => {
                    isDialogOpen = false;
                },
            });
        }
    }

    function deleteJadwal(item: any) {
        const mapelNama = item.mata_pelajaran?.nama || "Jadwal ini";
        if (
            confirm(
                `Apakah Anda yakin ingin menghapus jadwal "${mapelNama}" pada hari ${item.hari}?`,
            )
        ) {
            router.delete(`/jadwal/${item.id}`);
        }
    }
</script>

<AppHead title={`Jadwal Pembelajaran ${tahun}`} />

<div class="p-6 w-full space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight">
                Jadwal Pembelajaran Mingguan — Tahun {tahun}
            </h1>
            <p class="text-sm text-muted-foreground">
                Kelola jadwal pembelajaran mingguan berdasarkan tahun, hari, mata pelajaran, dan instruktur.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="inline-flex rounded-lg border bg-muted p-1">
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-colors {viewMode ===
                    'board'
                        ? 'bg-background shadow-sm text-foreground'
                        : 'text-muted-foreground hover:text-foreground'}"
                    onclick={() => (viewMode = "board")}
                >
                    <CalendarDays class="h-3.5 w-3.5" />
                    Papan Mingguan
                </button>
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs font-medium transition-colors {viewMode ===
                    'table'
                        ? 'bg-background shadow-sm text-foreground'
                        : 'text-muted-foreground hover:text-foreground'}"
                    onclick={() => (viewMode = "table")}
                >
                    <List class="h-3.5 w-3.5" />
                    Tabel Daftar
                </button>
            </div>

            <Button variant="outline" asChild>
                <Link href={`/jadwal/cetak?tahun=${tahun}`}>
                    <Printer class="mr-2 h-4 w-4" />
                    Cetak Jadwal
                </Link>
            </Button>

            <Button onclick={() => openCreateDialog()}>
                <Plus class="mr-2 h-4 w-4" />
                Tambah Jadwal
            </Button>
        </div>
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
            <Label class="text-xs text-muted-foreground">Filter Hari</Label>
            <Select.Root
                type="single"
                value={hariFilter}
                onValueChange={(val) => {
                    hariFilter = val;
                    applyFilters();
                }}
            >
                <Select.Trigger class="w-full">
                    {hariFilter === "semua" ? "Semua Hari (Senin - Sabtu)" : hariFilter}
                </Select.Trigger>
                <Select.Content>
                    <Select.Item value="semua" label="Semua Hari (Senin - Sabtu)">
                        Semua Hari (Senin - Sabtu)
                    </Select.Item>
                    {#each hariOptions as h (h)}
                        <Select.Item value={h} label={h}>{h}</Select.Item>
                    {/each}
                </Select.Content>
            </Select.Root>
        </div>

        <div class="space-y-1.5">
            <Label class="text-xs text-muted-foreground">Pencarian</Label>
            <Input
                type="search"
                placeholder="Cari mata pelajaran, instruktur, ruangan..."
                value={search}
                oninput={handleSearchInput}
            />
        </div>
    </div>

    {#if viewMode === "board"}
        <!-- Weekly Schedule Board -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            {#each displayedDays as day (day)}
                {@const items = jadwalByDay(day)}
                <Card class="flex flex-col">
                    <CardHeader class="flex flex-row items-center justify-between border-b py-3 px-4">
                        <div class="flex items-center gap-2">
                            <CardTitle class="text-base font-bold">{day}</CardTitle>
                            <Badge variant="secondary" class="text-xs">
                                {items.length} sesi
                            </Badge>
                        </div>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-7 w-7"
                            title={`Tambah jadwal hari ${day}`}
                            onclick={() => openCreateDialog(day)}
                        >
                            <Plus class="h-4 w-4" />
                        </Button>
                    </CardHeader>
                    <CardContent class="flex-1 p-4 space-y-3">
                        {#if items.length === 0}
                            <div class="py-8 text-center text-xs text-muted-foreground">
                                Belum ada jadwal pada hari {day}.
                            </div>
                        {:else}
                            {#each items as item (item.id)}
                                <div class="rounded-lg border bg-muted/30 p-3 space-y-2 transition-colors hover:bg-muted/50">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <div class="inline-flex items-center gap-1 text-xs font-semibold text-primary">
                                                <Clock class="h-3.5 w-3.5" />
                                                {formatTime(item.jam_mulai)} - {formatTime(item.jam_selesai)}
                                            </div>
                                            <h4 class="font-semibold text-sm mt-0.5">
                                                {item.mata_pelajaran?.nama || "-"}
                                            </h4>
                                        </div>
                                        <DropdownMenu.Root>
                                            <DropdownMenu.Trigger>
                                                <Button variant="ghost" size="icon" class="h-7 w-7">
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
                                                    onclick={() => deleteJadwal(item)}
                                                >
                                                    <Trash2 class="mr-2 h-4 w-4" />
                                                    Hapus
                                                </DropdownMenu.Item>
                                            </DropdownMenu.Content>
                                        </DropdownMenu.Root>
                                    </div>

                                    <div class="space-y-1 text-xs text-muted-foreground">
                                        <div class="flex items-center gap-1.5">
                                            <User class="h-3.5 w-3.5 shrink-0" />
                                            <span>{item.instruktur?.name || "-"}</span>
                                        </div>
                                        {#if item.ruangan}
                                            <div class="flex items-center gap-1.5">
                                                <MapPin class="h-3.5 w-3.5 shrink-0" />
                                                <span>{item.ruangan}</span>
                                            </div>
                                        {/if}
                                    </div>

                                    {#if item.mata_pelajaran?.jenis_disabilitas?.length}
                                        <div class="flex flex-wrap gap-1 pt-1">
                                            {#each item.mata_pelajaran.jenis_disabilitas as jd (jd.id)}
                                                <Badge variant="outline" class="text-[10px] px-1.5 py-0">
                                                    {jd.nama}
                                                </Badge>
                                            {/each}
                                        </div>
                                    {/if}

                                    {#if item.keterangan}
                                        <p class="text-xs text-muted-foreground border-t pt-1.5">
                                            {item.keterangan}
                                        </p>
                                    {/if}
                                </div>
                            {/each}
                        {/if}
                    </CardContent>
                </Card>
            {/each}
        </div>
    {:else}
        <!-- Table View -->
        <div class="rounded-md border bg-card">
            <Table.Root>
                <Table.Header>
                    <Table.Row>
                        <Table.Head>Hari</Table.Head>
                        <Table.Head>Jam</Table.Head>
                        <Table.Head>Mata Pelajaran</Table.Head>
                        <Table.Head>Instruktur</Table.Head>
                        <Table.Head>Jenis Disabilitas</Table.Head>
                        <Table.Head>Ruangan</Table.Head>
                        <Table.Head>Keterangan</Table.Head>
                        <Table.Head class="text-right">Aksi</Table.Head>
                    </Table.Row>
                </Table.Header>
                <Table.Body>
                    {#if jadwalList.length === 0}
                        <Table.Row>
                            <Table.Cell colspan={8} class="h-24 text-center text-muted-foreground">
                                Belum ada data jadwal untuk tahun {tahun}.
                            </Table.Cell>
                        </Table.Row>
                    {:else}
                        {#each jadwalList as item (item.id)}
                            <Table.Row>
                                <Table.Cell class="font-medium">{item.hari}</Table.Cell>
                                <Table.Cell class="whitespace-nowrap">
                                    {formatTime(item.jam_mulai)} - {formatTime(item.jam_selesai)}
                                </Table.Cell>
                                <Table.Cell class="font-semibold">
                                    {item.mata_pelajaran?.nama || "-"}
                                </Table.Cell>
                                <Table.Cell>
                                    {item.instruktur?.name || "-"}
                                </Table.Cell>
                                <Table.Cell>
                                    {#if item.mata_pelajaran?.jenis_disabilitas?.length}
                                        <div class="flex flex-wrap gap-1">
                                            {#each item.mata_pelajaran.jenis_disabilitas as jd (jd.id)}
                                                <Badge variant="secondary">{jd.nama}</Badge>
                                            {/each}
                                        </div>
                                    {:else}
                                        -
                                    {/if}
                                </Table.Cell>
                                <Table.Cell>{item.ruangan || "-"}</Table.Cell>
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
                                                onclick={() => deleteJadwal(item)}
                                            >
                                                <Trash2 class="mr-2 h-4 w-4" />
                                                Hapus
                                            </DropdownMenu.Item>
                                        </DropdownMenu.Content>
                                    </DropdownMenu.Root>
                                </Table.Cell>
                            </Table.Row>
                        {/each}
                    {/if}
                </Table.Body>
            </Table.Root>
        </div>
    {/if}
</div>

<Dialog.Root bind:open={isDialogOpen}>
    <Dialog.Content class="sm:max-w-[500px]">
        <Dialog.Header>
            <Dialog.Title>
                {isEditing ? "Edit Jadwal Pembelajaran" : "Tambah Jadwal Pembelajaran"}
            </Dialog.Title>
            <Dialog.Description>
                Atur mata pelajaran, instruktur, tahun, hari, serta jam mulai dan selesai pembelajaran.
            </Dialog.Description>
        </Dialog.Header>

        <form
            onsubmit={(e) => {
                e.preventDefault();
                submit();
            }}
        >
            <div class="grid gap-4 py-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="tahun">Tahun <span class="text-red-500">*</span></Label>
                        <Input
                            id="tahun"
                            type="number"
                            min="2000"
                            max="2100"
                            bind:value={form.tahun}
                        />
                        {#if form.errors.tahun}
                            <span class="text-sm text-destructive">{form.errors.tahun}</span>
                        {/if}
                    </div>

                    <div class="grid gap-2">
                        <Label for="hari">Hari <span class="text-red-500">*</span></Label>
                        <Select.Root
                            type="single"
                            value={form.hari}
                            onValueChange={(val) => {
                                form.hari = val;
                            }}
                        >
                            <Select.Trigger class="w-full">
                                {form.hari || "Pilih Hari"}
                            </Select.Trigger>
                            <Select.Content>
                                {#each hariOptions as h (h)}
                                    <Select.Item value={h} label={h}>{h}</Select.Item>
                                {/each}
                            </Select.Content>
                        </Select.Root>
                        {#if form.errors.hari}
                            <span class="text-sm text-destructive">{form.errors.hari}</span>
                        {/if}
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="mata_pelajaran_id">Mata Pelajaran <span class="text-red-500">*</span></Label>
                    <Select.Root
                        type="single"
                        value={form.mata_pelajaran_id}
                        onValueChange={(val) => {
                            form.mata_pelajaran_id = val;
                        }}
                    >
                        <Select.Trigger class="w-full">
                            {#if form.mata_pelajaran_id}
                                {@const selectedMapel = mataPelajaranList.find(
                                    (m: any) => String(m.id) === String(form.mata_pelajaran_id),
                                )}
                                {selectedMapel?.nama || "Pilih Mata Pelajaran"}
                            {:else}
                                Pilih Mata Pelajaran
                            {/if}
                        </Select.Trigger>
                        <Select.Content>
                            {#each mataPelajaranList as mapel (mapel.id)}
                                <Select.Item
                                    value={String(mapel.id)}
                                    label={mapel.nama}
                                >
                                    {mapel.nama}
                                </Select.Item>
                            {/each}
                        </Select.Content>
                    </Select.Root>
                    {#if form.errors.mata_pelajaran_id}
                        <span class="text-sm text-destructive">{form.errors.mata_pelajaran_id}</span>
                    {/if}
                </div>

                <div class="grid gap-2">
                    <Label for="instruktur_id">Instruktur <span class="text-red-500">*</span></Label>
                    <Select.Root
                        type="single"
                        value={form.instruktur_id}
                        onValueChange={(val) => {
                            form.instruktur_id = val;
                        }}
                    >
                        <Select.Trigger class="w-full">
                            {instrukturList.find(
                                (u: any) => String(u.id) === String(form.instruktur_id),
                            )?.name || "Pilih Instruktur"}
                        </Select.Trigger>
                        <Select.Content>
                            {#each instrukturList as instruktur (instruktur.id)}
                                <Select.Item
                                    value={String(instruktur.id)}
                                    label={instruktur.name}
                                >
                                    {instruktur.name} ({instruktur.email})
                                </Select.Item>
                            {/each}
                        </Select.Content>
                    </Select.Root>
                    {#if instrukturList.length === 0}
                        <span class="text-xs text-muted-foreground">
                            Belum ada user dengan role <strong>instruktur</strong>. Tambahkan melalui menu User Master.
                        </span>
                    {/if}
                    {#if form.errors.instruktur_id}
                        <span class="text-sm text-destructive">{form.errors.instruktur_id}</span>
                    {/if}
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="jam_mulai">Jam Mulai <span class="text-red-500">*</span></Label>
                        <Input
                            id="jam_mulai"
                            type="time"
                            bind:value={form.jam_mulai}
                        />
                        {#if form.errors.jam_mulai}
                            <span class="text-sm text-destructive">{form.errors.jam_mulai}</span>
                        {/if}
                    </div>

                    <div class="grid gap-2">
                        <Label for="jam_selesai">Jam Selesai <span class="text-red-500">*</span></Label>
                        <Input
                            id="jam_selesai"
                            type="time"
                            bind:value={form.jam_selesai}
                        />
                        {#if form.errors.jam_selesai}
                            <span class="text-sm text-destructive">{form.errors.jam_selesai}</span>
                        {/if}
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="ruangan">Ruangan (Opsional)</Label>
                    <Input
                        id="ruangan"
                        placeholder="Contoh: Ruang Keterampilan 1"
                        bind:value={form.ruangan}
                    />
                    {#if form.errors.ruangan}
                        <span class="text-sm text-destructive">{form.errors.ruangan}</span>
                    {/if}
                </div>

                <div class="grid gap-2">
                    <Label for="keterangan">Keterangan (Opsional)</Label>
                    <Textarea
                        id="keterangan"
                        placeholder="Catatan tambahan jadwal..."
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
