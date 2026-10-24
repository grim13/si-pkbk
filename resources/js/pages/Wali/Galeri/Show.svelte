<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Galeri Pembelajaran",
                href: "/galeri",
            },
            {
                title: "Detail Galeri",
                href: "",
            },
        ],
    };
</script>

<script lang="ts">
    import { Link } from "@inertiajs/svelte";
    import AppHead from "@/components/AppHead.svelte";
    import { Button } from "@/components/ui/button";
    import { Badge } from "@/components/ui/badge";
    import * as Dialog from "@/components/ui/dialog";
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from "@/components/ui/card";
    import {
        ArrowLeft,
        Calendar,
        Clock,
        User,
        BookOpen,
        CheckCircle2,
    } from "@lucide/svelte";

    let { galeri } = $props();

    let selectedFoto = $state<any>(null);

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

<AppHead title={`Galeri: ${galeri.judul}`} />

<div class="p-6 w-full space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight">{galeri.judul}</h1>
            {#if galeri.keterangan}
                <p class="text-sm text-muted-foreground mt-1">
                    {galeri.keterangan}
                </p>
            {/if}
        </div>
        <Button variant="outline" size="sm" asChild>
            <Link href="/galeri">
                <ArrowLeft class="mr-2 h-4 w-4" />
                Kembali ke Galeri
            </Link>
        </Button>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Info Pertemuan Pembelajaran -->
        <div class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Informasi Pembelajaran</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3 text-sm">
                    <div class="flex items-start gap-2">
                        <BookOpen class="h-4 w-4 text-primary mt-0.5 shrink-0" />
                        <div>
                            <div class="text-xs text-muted-foreground">Mata Pelajaran</div>
                            <div class="font-semibold">
                                {galeri.pertemuan?.jadwal?.mata_pelajaran?.nama || "-"}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start gap-2">
                        <Calendar class="h-4 w-4 text-primary mt-0.5 shrink-0" />
                        <div>
                            <div class="text-xs text-muted-foreground">Tanggal Pertemuan</div>
                            <div class="font-medium">
                                {formatDate(galeri.pertemuan?.tanggal)}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start gap-2">
                        <Clock class="h-4 w-4 text-primary mt-0.5 shrink-0" />
                        <div>
                            <div class="text-xs text-muted-foreground">Jadwal Kelas</div>
                            <div class="font-medium">
                                {galeri.pertemuan?.jadwal?.hari || "-"}, {formatTime(galeri.pertemuan?.jadwal?.jam_mulai)} - {formatTime(galeri.pertemuan?.jadwal?.jam_selesai)}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start gap-2">
                        <User class="h-4 w-4 text-primary mt-0.5 shrink-0" />
                        <div>
                            <div class="text-xs text-muted-foreground">Instruktur</div>
                            <div class="font-medium">
                                {galeri.pertemuan?.instruktur?.name || "-"}
                            </div>
                        </div>
                    </div>

                    {#if galeri.pertemuan?.peserta_detail?.length}
                        <div class="border-t pt-3 space-y-2">
                            <div class="text-xs text-muted-foreground">
                                Peserta Didik Anda yang Hadir:
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                {#each galeri.pertemuan.peserta_detail as detail (detail.id)}
                                    <Badge variant="secondary" class="bg-green-100 text-green-800 inline-flex items-center gap-1">
                                        <CheckCircle2 class="h-3 w-3" />
                                        #{detail.pendaftaran_peserta?.no_pendaftaran} {detail.pendaftaran_peserta?.nama}
                                    </Badge>
                                {/each}
                            </div>
                        </div>
                    {/if}
                </CardContent>
            </Card>
        </div>

        <!-- Koleksi Foto Galeri -->
        <div class="lg:col-span-2">
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">
                        Dokumentasi Foto ({galeri.foto?.length || 0} Foto)
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                        {#each galeri.foto || [] as foto (foto.id)}
                            <button
                                type="button"
                                class="group relative aspect-square overflow-hidden rounded-xl border bg-muted"
                                onclick={() => (selectedFoto = foto)}
                            >
                                <img
                                    src={`/storage/${foto.file_path}`}
                                    alt={foto.nama_file || galeri.judul}
                                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                />
                            </button>
                        {/each}
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</div>

<Dialog.Root
    open={!!selectedFoto}
    onOpenChange={(open) => {
        if (!open) selectedFoto = null;
    }}
>
    <Dialog.Content class="sm:max-w-[800px]">
        <Dialog.Header>
            <Dialog.Title>{selectedFoto?.nama_file || galeri.judul}</Dialog.Title>
        </Dialog.Header>
        {#if selectedFoto}
            <div class="overflow-hidden rounded-lg bg-black/5 flex items-center justify-center">
                <img
                    src={`/storage/${selectedFoto.file_path}`}
                    alt={selectedFoto.nama_file || galeri.judul}
                    class="max-h-[75vh] w-auto object-contain"
                />
            </div>
        {/if}
    </Dialog.Content>
</Dialog.Root>
