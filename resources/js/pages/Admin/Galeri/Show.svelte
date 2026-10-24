<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Galeri Pembelajaran",
                href: "/admin/galeri",
            },
            {
                title: "Detail Galeri",
                href: "",
            },
        ],
    };
</script>

<script lang="ts">
    import { Link, router } from "@inertiajs/svelte";
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
        Trash2,
        Users,
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

    function deleteFoto(foto: any) {
        if (confirm("Hapus foto ini dari galeri?")) {
            router.delete(`/admin/galeri/${galeri.id}/foto/${foto.id}`);
        }
    }

    const hadirPeserta = $derived(
        (galeri.pertemuan?.peserta_detail || []).filter(
            (d: any) => d.presensi === "hadir",
        ),
    );
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
            <Link href="/admin/galeri">
                <ArrowLeft class="mr-2 h-4 w-4" />
                Kembali ke Daftar Galeri
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
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-base flex items-center gap-2">
                        <Users class="h-4 w-4 text-primary" />
                        Peserta yang Hadir ({hadirPeserta.length})
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    {#if hadirPeserta.length === 0}
                        <p class="text-xs text-muted-foreground">
                            Tidak ada peserta yang tercatat hadir pada pertemuan ini.
                        </p>
                    {:else}
                        <div class="space-y-2">
                            {#each hadirPeserta as detail (detail.id)}
                                <div class="flex items-center justify-between rounded-md border px-3 py-2 text-xs">
                                    <div>
                                        <span class="font-mono font-bold text-primary mr-1">
                                            #{detail.pendaftaran_peserta?.no_pendaftaran || "-"}
                                        </span>
                                        <span class="font-medium">
                                            {detail.pendaftaran_peserta?.nama || "-"}
                                        </span>
                                    </div>
                                    <Badge variant="secondary" class="bg-green-100 text-green-800">
                                        Hadir
                                    </Badge>
                                </div>
                            {/each}
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
                        Daftar Foto ({galeri.foto?.length || 0} Foto)
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                        {#each galeri.foto || [] as foto (foto.id)}
                            <div class="group relative aspect-square overflow-hidden rounded-xl border bg-muted">
                                <button
                                    type="button"
                                    class="h-full w-full"
                                    onclick={() => (selectedFoto = foto)}
                                >
                                    <img
                                        src={`/storage/${foto.file_path}`}
                                        alt={foto.nama_file || galeri.judul}
                                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                    />
                                </button>
                                {#if (galeri.foto?.length || 0) > 1}
                                    <Button
                                        variant="destructive"
                                        size="icon"
                                        class="absolute top-2 right-2 h-7 w-7 opacity-0 transition-opacity group-hover:opacity-100"
                                        title="Hapus foto ini"
                                        onclick={() => deleteFoto(foto)}
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                {/if}
                            </div>
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
