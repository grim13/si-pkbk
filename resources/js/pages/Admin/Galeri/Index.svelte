<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Galeri Pembelajaran",
                href: "/admin/galeri",
            },
        ],
    };
</script>

<script lang="ts">
    import { router, useForm, Link } from "@inertiajs/svelte";
    import { untrack } from "svelte";
    import AppHead from "@/components/AppHead.svelte";
    import { Button } from "@/components/ui/button";
    import { Input } from "@/components/ui/input";
    import { Label } from "@/components/ui/label";
    import { Textarea } from "@/components/ui/textarea";
    import { Badge } from "@/components/ui/badge";
    import * as Dialog from "@/components/ui/dialog";
    import * as Select from "@/components/ui/select";
    import * as DropdownMenu from "@/components/ui/dropdown-menu";
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from "@/components/ui/card";
    import {
        Plus,
        MoreVertical,
        Edit,
        Trash2,
        Eye,
        Calendar,
        Clock,
        User,
        BookOpen,
        Images,
        Upload,
        X,
    } from "@lucide/svelte";

    let {
        galeriList = [],
        pertemuanList = [],
        jadwalList = [],
        availableYears = [],
        filters = {},
    } = $props();

    let tahun = $state(untrack(() => String(filters.tahun || "semua")));
    let jadwalFilter = $state(untrack(() => String(filters.jadwal_id || "semua")));
    let search = $state(untrack(() => filters.search || ""));
    let searchTimeout: ReturnType<typeof setTimeout>;

    function applyFilters() {
        router.get(
            "/admin/galeri",
            {
                tahun: tahun === "semua" ? "" : Number(tahun),
                jadwal_id: jadwalFilter === "semua" ? "" : Number(jadwalFilter),
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

    function formatTime(t: string | null | undefined) {
        if (!t) return "-";
        return t.slice(0, 5);
    }

    function formatDate(date: string | null | undefined) {
        if (!date) return "-";
        return new Date(date).toLocaleDateString("id-ID", {
            weekday: "long",
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
    }

    function formatPertemuanLabel(p: any) {
        if (!p) return "Pilih Pertemuan Pembelajaran";
        const tanggalStr = p.tanggal ? p.tanggal.slice(0, 10) : "-";
        const mapelNama = p.jadwal?.mata_pelajaran?.nama || "-";
        const instrukturNama = p.instruktur?.name || "-";
        const hadirCount = p.hadir_count ?? 0;
        return `${tanggalStr} — ${mapelNama} (${instrukturNama}) • ${hadirCount} Hadir`;
    }

    // Create / Edit Dialog
    let isDialogOpen = $state(false);
    let isEditing = $state(false);
    let currentGaleri = $state<any>(null);
    let previewUrls = $state<{ name: string; url: string }[]>([]);

    const form = useForm({
        pertemuan_pembelajaran_id: "",
        judul: "",
        keterangan: "",
        fotos: [] as File[],
        hapus_foto_ids: [] as number[],
    });

    function clearPreviews() {
        for (const p of previewUrls) {
            URL.revokeObjectURL(p.url);
        }
        previewUrls = [];
    }

    function openCreateDialog() {
        isEditing = false;
        currentGaleri = null;
        clearPreviews();
        form.pertemuan_pembelajaran_id =
            pertemuanList.length === 1 ? String(pertemuanList[0].id) : "";
        form.judul = "";
        form.keterangan = "";
        form.fotos = [];
        form.hapus_foto_ids = [];
        form.clearErrors();
        isDialogOpen = true;
    }

    function openEditDialog(item: any) {
        isEditing = true;
        currentGaleri = item;
        clearPreviews();
        form.pertemuan_pembelajaran_id = item.pertemuan_pembelajaran_id
            ? String(item.pertemuan_pembelajaran_id)
            : "";
        form.judul = item.judul || "";
        form.keterangan = item.keterangan || "";
        form.fotos = [];
        form.hapus_foto_ids = [];
        form.clearErrors();
        isDialogOpen = true;
    }

    function handleFilesChange(e: Event) {
        const input = e.target as HTMLInputElement;
        if (!input.files) return;
        const selectedFiles = Array.from(input.files);
        form.fotos = [...form.fotos, ...selectedFiles];

        const newPreviews = selectedFiles.map((f) => ({
            name: f.name,
            url: URL.createObjectURL(f),
        }));
        previewUrls = [...previewUrls, ...newPreviews];
        input.value = "";
    }

    function removeSelectedFile(index: number) {
        const target = previewUrls[index];
        if (target) {
            URL.revokeObjectURL(target.url);
        }
        previewUrls = previewUrls.filter((_, idx) => idx !== index);
        form.fotos = form.fotos.filter((_, idx) => idx !== index);
    }

    function toggleRemoveExistingFoto(fotoId: number) {
        if (form.hapus_foto_ids.includes(fotoId)) {
            form.hapus_foto_ids = form.hapus_foto_ids.filter((id) => id !== fotoId);
        } else {
            form.hapus_foto_ids = [...form.hapus_foto_ids, fotoId];
        }
    }

    function submitForm() {
        form.transform((data) => ({
            ...data,
            pertemuan_pembelajaran_id: data.pertemuan_pembelajaran_id
                ? Number(data.pertemuan_pembelajaran_id)
                : null,
        }));

        if (isEditing && currentGaleri) {
            form.post(`/admin/galeri/${currentGaleri.id}`, {
                forceFormData: true,
                onSuccess: () => {
                    isDialogOpen = false;
                    clearPreviews();
                },
            });
        } else {
            form.post("/admin/galeri", {
                forceFormData: true,
                onSuccess: () => {
                    isDialogOpen = false;
                    clearPreviews();
                },
            });
        }
    }

    function deleteGaleri(item: any) {
        if (
            confirm(
                `Apakah Anda yakin ingin menghapus galeri "${item.judul}" beserta seluruh fotonya?`,
            )
        ) {
            router.delete(`/admin/galeri/${item.id}`);
        }
    }
</script>

<AppHead title="Galeri Pembelajaran" />

<div class="p-6 w-full space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight">
                Manajemen Galeri Pembelajaran
            </h1>
            <p class="text-sm text-muted-foreground">
                Unggah beberapa foto sekaligus pada satu galeri yang terhubung dengan pertemuan pembelajaran instruktur di kelas.
            </p>
        </div>

        <Button onclick={openCreateDialog}>
            <Plus class="mr-2 h-4 w-4" />
            Tambah Galeri
        </Button>
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
                    {tahun === "semua" ? "Semua Tahun" : `Tahun ${tahun}`}
                </Select.Trigger>
                <Select.Content>
                    <Select.Item value="semua" label="Semua Tahun">
                        Semua Tahun
                    </Select.Item>
                    {#each availableYears as y (y)}
                        <Select.Item value={String(y)} label={`Tahun ${y}`}>
                            Tahun {y}
                        </Select.Item>
                    {/each}
                </Select.Content>
            </Select.Root>
        </div>

        <div class="space-y-1.5">
            <Label class="text-xs text-muted-foreground">Filter Kelas / Jadwal</Label>
            <Select.Root
                type="single"
                value={jadwalFilter}
                onValueChange={(val) => {
                    jadwalFilter = val;
                    applyFilters();
                }}
            >
                <Select.Trigger class="w-full">
                    {#if jadwalFilter === "semua"}
                        Semua Kelas / Jadwal
                    {:else}
                        {@const j = jadwalList.find((item: any) => String(item.id) === jadwalFilter)}
                        {j ? `${j.hari} (${formatTime(j.jam_mulai)}) - ${j.mata_pelajaran?.nama}` : "Semua Kelas"}
                    {/if}
                </Select.Trigger>
                <Select.Content>
                    <Select.Item value="semua" label="Semua Kelas / Jadwal">
                        Semua Kelas / Jadwal
                    </Select.Item>
                    {#each jadwalList as j (j.id)}
                        <Select.Item
                            value={String(j.id)}
                            label={`${j.hari} (${formatTime(j.jam_mulai)}) - ${j.mata_pelajaran?.nama}`}
                        >
                            {j.hari} ({formatTime(j.jam_mulai)}-{formatTime(j.jam_selesai)}) — {j.mata_pelajaran?.nama} ({j.instruktur?.name || "-"})
                        </Select.Item>
                    {/each}
                </Select.Content>
            </Select.Root>
        </div>

        <div class="space-y-1.5">
            <Label class="text-xs text-muted-foreground">Pencarian</Label>
            <Input
                type="search"
                placeholder="Cari judul galeri, mata pelajaran, instruktur..."
                value={search}
                oninput={handleSearchInput}
            />
        </div>
    </div>

    {#if galeriList.length === 0}
        <Card>
            <CardContent class="py-12 text-center space-y-3">
                <Images class="mx-auto h-10 w-10 text-muted-foreground/60" />
                <div class="space-y-1">
                    <h3 class="text-sm font-semibold">Belum Ada Galeri Pembelajaran</h3>
                    <p class="text-xs text-muted-foreground max-w-md mx-auto">
                        Tambahkan galeri dokumentasi kegiatan pembelajaran agar wali peserta yang terdaftar dan hadir di kelas dapat melihat foto kegiatan.
                    </p>
                </div>
                <Button size="sm" onclick={openCreateDialog}>
                    <Plus class="mr-1.5 h-4 w-4" />
                    Tambah Galeri Pertama
                </Button>
            </CardContent>
        </Card>
    {:else}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            {#each galeriList as item (item.id)}
                {@const coverFoto = item.foto?.[0]}
                <Card class="flex flex-col overflow-hidden py-0 gap-0">
                    <div class="relative aspect-video bg-muted overflow-hidden">
                        {#if coverFoto}
                            <img
                                src={`/storage/${coverFoto.file_path}`}
                                alt={item.judul}
                                class="h-full w-full object-cover transition-transform duration-300 hover:scale-105"
                            />
                        {:else}
                            <div class="flex h-full w-full items-center justify-center text-muted-foreground">
                                <Images class="h-10 w-10" />
                            </div>
                        {/if}
                        <div class="absolute top-3 right-3">
                            <Badge class="bg-black/75 text-white hover:bg-black/80">
                                <Images class="mr-1 h-3 w-3" />
                                {item.foto_count ?? item.foto?.length ?? 0} Foto
                            </Badge>
                        </div>
                    </div>

                    {#if item.foto && item.foto.length > 1}
                        <div class="flex gap-1.5 overflow-x-auto border-t border-b bg-muted/30 px-4 py-2">
                            {#each item.foto.slice(0, 5) as foto (foto.id)}
                                <img
                                    src={`/storage/${foto.file_path}`}
                                    alt={foto.nama_file || item.judul}
                                    class="h-10 w-14 shrink-0 rounded object-cover border"
                                />
                            {/each}
                            {#if item.foto.length > 5}
                                <div class="flex h-10 w-14 shrink-0 items-center justify-center rounded border bg-muted text-xs font-semibold text-muted-foreground">
                                    +{item.foto.length - 5}
                                </div>
                            {/if}
                        </div>
                    {/if}

                    <CardHeader class="pt-4 pb-2">
                        <div class="flex items-start justify-between gap-2">
                            <CardTitle class="text-base line-clamp-1">
                                {item.judul}
                            </CardTitle>
                            <DropdownMenu.Root>
                                <DropdownMenu.Trigger>
                                    <Button variant="ghost" size="icon" class="h-8 w-8 shrink-0">
                                        <MoreVertical class="h-4 w-4" />
                                    </Button>
                                </DropdownMenu.Trigger>
                                <DropdownMenu.Content align="end">
                                    <DropdownMenu.Item
                                        onclick={() =>
                                            router.visit(`/admin/galeri/${item.id}`)}
                                    >
                                        <Eye class="mr-2 h-4 w-4" />
                                        Lihat Detail
                                    </DropdownMenu.Item>
                                    <DropdownMenu.Item onclick={() => openEditDialog(item)}>
                                        <Edit class="mr-2 h-4 w-4" />
                                        Edit Galeri
                                    </DropdownMenu.Item>
                                    <DropdownMenu.Item
                                        class="text-destructive focus:bg-destructive/10 focus:text-destructive"
                                        onclick={() => deleteGaleri(item)}
                                    >
                                        <Trash2 class="mr-2 h-4 w-4" />
                                        Hapus Galeri
                                    </DropdownMenu.Item>
                                </DropdownMenu.Content>
                            </DropdownMenu.Root>
                        </div>

                        {#if item.keterangan}
                            <p class="text-xs text-muted-foreground line-clamp-2">
                                {item.keterangan}
                            </p>
                        {/if}
                    </CardHeader>

                    <CardContent class="flex-1 space-y-2 pb-5 text-xs text-muted-foreground">
                        <div class="rounded-lg border bg-muted/30 p-3 space-y-1.5">
                            <div class="font-semibold text-foreground flex items-center gap-1.5">
                                <BookOpen class="h-3.5 w-3.5 text-primary shrink-0" />
                                <span>{item.pertemuan?.jadwal?.mata_pelajaran?.nama || "-"}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <Calendar class="h-3.5 w-3.5 shrink-0" />
                                <span>{formatDate(item.pertemuan?.tanggal)}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <Clock class="h-3.5 w-3.5 shrink-0" />
                                <span>
                                    {item.pertemuan?.jadwal?.hari || "-"}, {formatTime(item.pertemuan?.jadwal?.jam_mulai)} - {formatTime(item.pertemuan?.jadwal?.jam_selesai)}
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <User class="h-3.5 w-3.5 shrink-0" />
                                <span>Instruktur: {item.pertemuan?.instruktur?.name || "-"}</span>
                            </div>

                            {#if item.pertemuan?.jadwal?.mata_pelajaran?.jenis_disabilitas?.length}
                                <div class="flex flex-wrap gap-1 pt-1">
                                    {#each item.pertemuan.jadwal.mata_pelajaran.jenis_disabilitas as jd (jd.id)}
                                        <Badge variant="outline" class="text-[10px] px-1.5 py-0">
                                            {jd.nama}
                                        </Badge>
                                    {/each}
                                </div>
                            {/if}
                        </div>
                    </CardContent>
                </Card>
            {/each}
        </div>
    {/if}
</div>

<Dialog.Root bind:open={isDialogOpen}>
    <Dialog.Content
        class="w-[92vw] max-w-3xl sm:max-w-3xl flex flex-col overflow-hidden p-6"
        style="width: 92vw; max-width: 48rem; max-height: 88vh; display: flex; flex-direction: column; overflow: hidden;"
    >
        <div class="shrink-0 pb-3 border-b">
            <Dialog.Header>
                <Dialog.Title>
                    {isEditing ? "Edit Galeri Pembelajaran" : "Tambah Galeri Pembelajaran"}
                </Dialog.Title>
                <Dialog.Description>
                    Pilih sesi pertemuan pembelajaran yang dilakukan instruktur di kelas, lalu unggah beberapa foto dokumentasi kegiatan.
                </Dialog.Description>
            </Dialog.Header>
        </div>

        <form
            class="flex flex-col flex-1 min-h-0 overflow-hidden"
            style="display: flex; flex-direction: column; flex: 1 1 auto; min-height: 0; overflow: hidden;"
            onsubmit={(e) => {
                e.preventDefault();
                submitForm();
            }}
        >
            <div
                class="space-y-4 py-4 pr-2 flex-1 min-h-0 overflow-y-auto"
                style="flex: 1 1 auto; min-height: 0; max-height: calc(88vh - 165px); overflow-y: auto;"
            >
                <div class="grid gap-2">
                    <Label for="pertemuan_pembelajaran_id">
                        Pertemuan Pembelajaran di Kelas <span class="text-red-500">*</span>
                    </Label>
                    <Select.Root
                        type="single"
                        value={form.pertemuan_pembelajaran_id}
                        onValueChange={(val) => {
                            form.pertemuan_pembelajaran_id = val;
                        }}
                    >
                        <Select.Trigger class="w-full">
                            {#if form.pertemuan_pembelajaran_id}
                                {@const selectedPertemuan = pertemuanList.find(
                                    (p: any) =>
                                        String(p.id) === String(form.pertemuan_pembelajaran_id),
                                )}
                                {formatPertemuanLabel(selectedPertemuan)}
                            {:else}
                                Pilih Pertemuan Pembelajaran
                            {/if}
                        </Select.Trigger>
                        <Select.Content>
                            {#each pertemuanList as p (p.id)}
                                <Select.Item
                                    value={String(p.id)}
                                    label={formatPertemuanLabel(p)}
                                >
                                    {formatPertemuanLabel(p)}
                                </Select.Item>
                            {/each}
                        </Select.Content>
                    </Select.Root>
                    {#if pertemuanList.length === 0}
                        <span class="text-xs text-muted-foreground">
                            Belum ada pertemuan pembelajaran yang tercatat oleh instruktur.
                        </span>
                    {/if}
                    {#if form.errors.pertemuan_pembelajaran_id}
                        <span class="text-sm text-destructive">
                            {form.errors.pertemuan_pembelajaran_id}
                        </span>
                    {/if}
                </div>

                <div class="grid gap-2">
                    <Label for="judul">
                        Judul Galeri <span class="text-red-500">*</span>
                    </Label>
                    <Input
                        id="judul"
                        placeholder="Contoh: Dokumentasi Latihan Orientasi & Mobilitas Pertemuan 1"
                        bind:value={form.judul}
                    />
                    {#if form.errors.judul}
                        <span class="text-sm text-destructive">{form.errors.judul}</span>
                    {/if}
                </div>

                <div class="grid gap-2">
                    <Label for="keterangan">Keterangan / Deskripsi (Opsional)</Label>
                    <Textarea
                        id="keterangan"
                        placeholder="Catatan kegiatan pada pertemuan pembelajaran ini..."
                        bind:value={form.keterangan}
                    />
                    {#if form.errors.keterangan}
                        <span class="text-sm text-destructive">{form.errors.keterangan}</span>
                    {/if}
                </div>

                {#if isEditing && currentGaleri?.foto?.length}
                    <div class="grid gap-1.5">
                        <div class="flex items-center justify-between">
                            <Label>
                                Foto Saat Ini ({currentGaleri.foto.length} foto — klik untuk menandai hapus)
                            </Label>
                            {#if form.hapus_foto_ids.length > 0}
                                <span class="text-xs font-medium text-destructive">
                                    {form.hapus_foto_ids.length} foto ditandai hapus
                                </span>
                            {/if}
                        </div>
                        <div
                            class="rounded-lg border bg-muted/20 p-2 overflow-y-auto"
                            style="max-height: 152px; overflow-y: auto;"
                        >
                            <div
                                style="display: grid; grid-template-columns: repeat(auto-fill, minmax(64px, 1fr)); gap: 8px;"
                            >
                                {#each currentGaleri.foto as foto (foto.id)}
                                    {@const markedForDelete = form.hapus_foto_ids.includes(foto.id)}
                                    <button
                                        type="button"
                                        onclick={() => toggleRemoveExistingFoto(foto.id)}
                                        style="height: 64px; width: 100%;"
                                        class="group relative overflow-hidden rounded-md border text-left transition-all {markedForDelete
                                            ? 'ring-2 ring-destructive opacity-50'
                                            : 'hover:ring-2 hover:ring-primary'}"
                                        title={markedForDelete ? "Batal hapus foto ini" : "Klik untuk menandai hapus"}
                                    >
                                        <img
                                            src={`/storage/${foto.file_path}`}
                                            alt={foto.nama_file || "Foto Galeri"}
                                            class="h-full w-full object-cover"
                                        />
                                        <div class="absolute top-1 right-1 rounded-full bg-black/70 p-0.5 text-white">
                                            <X class="h-2.5 w-2.5" />
                                        </div>
                                        {#if markedForDelete}
                                            <div class="absolute inset-x-0 bottom-0 bg-destructive px-1 py-0.5 text-[9px] font-semibold text-white text-center leading-tight">
                                                Hapus
                                            </div>
                                        {/if}
                                    </button>
                                {/each}
                            </div>
                        </div>
                    </div>
                {/if}

                <div class="grid gap-2">
                    <Label for="fotos">
                        {isEditing ? "Tambah Foto Baru (Multiple)" : "Upload Foto Galeri (Multiple)"}
                        {#if !isEditing}
                            <span class="text-red-500">*</span>
                        {/if}
                    </Label>
                    <label
                        for="fotos"
                        class="flex cursor-pointer flex-col items-center justify-center gap-1.5 rounded-lg border-2 border-dashed p-4 text-center transition-colors hover:bg-muted/50"
                    >
                        <Upload class="h-5 w-5 text-muted-foreground" />
                        <div class="text-xs">
                            <span class="font-semibold text-primary">Klik untuk memilih beberapa foto</span>
                            <p class="text-muted-foreground mt-0.5">
                                Format JPG, JPEG, PNG, atau WEBP (Maks. 5MB per foto)
                            </p>
                        </div>
                        <input
                            id="fotos"
                            type="file"
                            multiple
                            accept="image/jpeg,image/png,image/webp"
                            class="hidden"
                            onchange={handleFilesChange}
                        />
                    </label>

                    {#if previewUrls.length > 0}
                        <div class="grid gap-1.5 pt-1">
                            <span class="text-xs font-medium text-muted-foreground">
                                Foto Baru Dipilih ({previewUrls.length} foto):
                            </span>
                            <div
                                class="rounded-lg border bg-muted/20 p-2 overflow-y-auto"
                                style="max-height: 152px; overflow-y: auto;"
                            >
                                <div
                                    style="display: grid; grid-template-columns: repeat(auto-fill, minmax(64px, 1fr)); gap: 8px;"
                                >
                                    {#each previewUrls as preview, idx (preview.url)}
                                        <div
                                            class="relative overflow-hidden rounded-md border bg-muted"
                                            style="height: 64px; width: 100%;"
                                        >
                                            <img
                                                src={preview.url}
                                                alt={preview.name}
                                                class="h-full w-full object-cover"
                                            />
                                            <button
                                                type="button"
                                                onclick={() => removeSelectedFile(idx)}
                                                class="absolute top-1 right-1 rounded-full bg-black/70 p-0.5 text-white hover:bg-destructive"
                                                title="Batal pilih foto ini"
                                            >
                                                <X class="h-2.5 w-2.5" />
                                            </button>
                                        </div>
                                    {/each}
                                </div>
                            </div>
                        </div>
                    {/if}

                    {#if form.errors.fotos}
                        <span class="text-sm text-destructive">{form.errors.fotos}</span>
                    {/if}
                </div>
            </div>

            <div class="shrink-0 border-t pt-4 mt-2">
                <Dialog.Footer>
                    <Button
                        type="button"
                        variant="outline"
                        onclick={() => (isDialogOpen = false)}
                    >
                        Batal
                    </Button>
                    <Button type="submit" disabled={form.processing}>
                        {form.processing ? "Menyimpan..." : "Simpan Galeri"}
                    </Button>
                </Dialog.Footer>
            </div>
        </form>
    </Dialog.Content>
</Dialog.Root>
