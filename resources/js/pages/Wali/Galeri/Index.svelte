<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Galeri Pembelajaran",
                href: "/galeri",
            },
        ],
    };
</script>

<script lang="ts">
    import { router, Link } from "@inertiajs/svelte";
    import { untrack } from "svelte";
    import AppHead from "@/components/AppHead.svelte";
    import { Button } from "@/components/ui/button";
    import { Input } from "@/components/ui/input";
    import { Label } from "@/components/ui/label";
    import { Badge } from "@/components/ui/badge";
    import * as Select from "@/components/ui/select";
    import {
        Card,
        CardContent,
        CardFooter,
        CardHeader,
        CardTitle,
    } from "@/components/ui/card";
    import {
        Images,
        Calendar,
        Clock,
        User,
        BookOpen,
        Eye,
        CheckCircle2,
    } from "@lucide/svelte";

    let {
        galeriList = [],
        pesertaList = [],
        availableYears = [],
        filters = {},
    } = $props();

    let tahun = $state(untrack(() => String(filters.tahun || "semua")));
    let pesertaFilter = $state(untrack(() => String(filters.peserta_id || "semua")));
    let search = $state(untrack(() => filters.search || ""));
    let searchTimeout: ReturnType<typeof setTimeout>;

    function applyFilters() {
        router.get(
            "/galeri",
            {
                tahun: tahun === "semua" ? "" : Number(tahun),
                peserta_id: pesertaFilter === "semua" ? "" : Number(pesertaFilter),
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
</script>

<AppHead title="Galeri Pembelajaran" />

<div class="p-6 w-full space-y-6">
    <div>
        <h1 class="text-xl font-bold tracking-tight">
            Galeri Kegiatan Pembelajaran
        </h1>
        <p class="text-sm text-muted-foreground">
            Dokumentasi foto kegiatan pembelajaran di kelas untuk peserta didik Anda yang terdaftar dan hadir pada pertemuan tersebut.
        </p>
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
            <Label class="text-xs text-muted-foreground">Filter Peserta Didik</Label>
            <Select.Root
                type="single"
                value={pesertaFilter}
                onValueChange={(val) => {
                    pesertaFilter = val;
                    applyFilters();
                }}
            >
                <Select.Trigger class="w-full">
                    {#if pesertaFilter === "semua"}
                        Semua Peserta Didik Anda
                    {:else}
                        {@const p = pesertaList.find((item: any) => String(item.id) === pesertaFilter)}
                        {p ? `#${p.no_pendaftaran} - ${p.nama}` : "Semua Peserta"}
                    {/if}
                </Select.Trigger>
                <Select.Content>
                    <Select.Item value="semua" label="Semua Peserta Didik Anda">
                        Semua Peserta Didik Anda
                    </Select.Item>
                    {#each pesertaList as p (p.id)}
                        <Select.Item
                            value={String(p.id)}
                            label={`#${p.no_pendaftaran} - ${p.nama}`}
                        >
                            #{p.no_pendaftaran} — {p.nama}
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
                        Galeri hanya dapat dilihat apabila peserta didik Anda telah terdaftar di kelas tersebut dan tercatat hadir pada pertemuan pembelajaran.
                    </p>
                </div>
            </CardContent>
        </Card>
    {:else}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            {#each galeriList as item (item.id)}
                {@const coverFoto = item.foto?.[0]}
                <Card class="flex flex-col overflow-hidden py-0 gap-0">
                    <Link href={`/galeri/${item.id}`} class="block relative aspect-video bg-muted overflow-hidden">
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
                    </Link>

                    <CardHeader class="pt-4 pb-2">
                        <CardTitle class="text-base line-clamp-1">
                            {item.judul}
                        </CardTitle>
                        {#if item.keterangan}
                            <p class="text-xs text-muted-foreground line-clamp-2">
                                {item.keterangan}
                            </p>
                        {/if}
                    </CardHeader>

                    <CardContent class="flex-1 space-y-3 text-xs text-muted-foreground">
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
                        </div>

                        {#if item.pertemuan?.peserta_detail?.length}
                            <div class="space-y-1">
                                <span class="text-[11px] font-medium text-foreground">
                                    Peserta Anda yang Hadir:
                                </span>
                                <div class="flex flex-wrap gap-1.5">
                                    {#each item.pertemuan.peserta_detail as detail (detail.id)}
                                        <Badge variant="secondary" class="bg-green-100 text-green-800 text-[11px] inline-flex items-center gap-1">
                                            <CheckCircle2 class="h-3 w-3" />
                                            {detail.pendaftaran_peserta?.nama || "Peserta"}
                                        </Badge>
                                    {/each}
                                </div>
                            </div>
                        {/if}
                    </CardContent>

                    <CardFooter class="border-t py-3 mt-3">
                        <Button
                            variant="outline"
                            size="sm"
                            class="w-full"
                            onclick={() => router.visit(`/galeri/${item.id}`)}
                        >
                            <Eye class="mr-1.5 h-3.5 w-3.5" />
                            Lihat Semua Foto ({item.foto_count ?? item.foto?.length ?? 0})
                        </Button>
                    </CardFooter>
                </Card>
            {/each}
        </div>
    {/if}
</div>
