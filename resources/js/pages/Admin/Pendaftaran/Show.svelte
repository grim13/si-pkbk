<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Verifikasi Pendaftaran",
                href: "/admin/pendaftaran",
            },
            {
                title: "Detail Pendaftaran",
                href: "",
            },
        ],
    };
</script>

<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import { Button } from "@/components/ui/button";
    import { Label } from "@/components/ui/label";
    import { Input } from "@/components/ui/input";
    import { Textarea } from "@/components/ui/textarea";
    import { useForm, Link } from "@inertiajs/svelte";
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from "@/components/ui/card";
    import InputError from "@/components/InputError.svelte";
    import { ArrowLeft } from "@lucide/svelte";
    import { untrack } from "svelte";

    let { pendaftaran } = $props();

    // Prepare initial files for verification form
    let initialBerkas = untrack(() => {
        let berkas = {};
        if (pendaftaran.berkas) {
            pendaftaran.berkas.forEach((b) => {
                berkas[b.id] = {
                    status:
                        b.status === "diterima"
                            ? "diterima"
                            : b.status === "perbaikan"
                              ? "perbaikan"
                              : "diterima",
                    keterangan_perbaikan: b.keterangan_perbaikan || "",
                };
            });
        }
        return berkas;
    });

    let initialJadwal = untrack(() => {
        if (pendaftaran.jadwal_asesmen) {
            try {
                const d = new Date(pendaftaran.jadwal_asesmen);
                d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
                return d.toISOString().slice(0, 16);
            } catch (e) {}
        }
        return "";
    });

    let form = useForm(untrack(() => ({
        action: "acc",
        catatan_verifikasi: pendaftaran.catatan_verifikasi || "",
        berkas: initialBerkas,
        jadwal_asesmen: initialJadwal,
        tempat_asesmen:
            pendaftaran.tempat_asesmen ||
            "PANTI SOSIAL REHABILITASI PENYANDANG DISABILITAS SENSORIK (PSR-PDS)",
    })));

    function submitVerify(action) {
        form.action = action;
        form.post(`/admin/pendaftaran/${pendaftaran.id}/verify`);
    }

    function formatStatus(status) {
        return status.replace("_", " ").toUpperCase();
    }
</script>

<AppHead title={`Detail Pendaftaran: ${pendaftaran.no_pendaftaran}`} />

<div class="p-6 w-full">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold tracking-tight">
                Pendaftaran: {pendaftaran.no_pendaftaran}
            </h2>
            <p class="text-sm text-muted-foreground">
                Lakukan verifikasi berkas dan jadwalkan asesmen.
            </p>
        </div>
        <Button variant="outline" size="sm" asChild>
            <Link href="/admin/pendaftaran">
                <ArrowLeft class="mr-2 h-4 w-4" />
                Kembali
            </Link>
        </Button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Biodata -->
        <div class="md:col-span-1 space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Data Klien</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4 text-sm">
                    <div>
                        <span class="text-muted-foreground block text-xs"
                            >Status Pendaftaran</span
                        >
                        <span
                            class="font-semibold px-2 py-0.5 rounded-full bg-slate-100"
                            >{formatStatus(
                                pendaftaran.status_pendaftaran,
                            )}</span
                        >
                    </div>
                    <div>
                        <span class="text-muted-foreground block text-xs"
                            >NIK</span
                        >
                        <span class="font-medium">{pendaftaran.nik}</span>
                    </div>
                    <div>
                        <span class="text-muted-foreground block text-xs"
                            >Nama Lengkap</span
                        >
                        <span class="font-medium">{pendaftaran.nama}</span>
                    </div>
                    <div>
                        <span class="text-muted-foreground block text-xs"
                            >Tempat, Tanggal Lahir</span
                        >
                        <span class="font-medium"
                            >{pendaftaran.tempat_lahir}, {pendaftaran.tanggal_lahir}</span
                        >
                    </div>
                    <div>
                        <span class="text-muted-foreground block text-xs"
                            >Alamat Asal</span
                        >
                        <span class="font-medium"
                            >{pendaftaran.alamat_asal}</span
                        >
                    </div>
                    <div>
                        <span class="text-muted-foreground block text-xs"
                            >Pendidikan Terakhir</span
                        >
                        <span class="font-medium">{pendaftaran.pendidikan}</span
                        >
                    </div>
                    <div>
                        <span class="text-muted-foreground block text-xs"
                            >Keterampilan yang Diminati</span
                        >
                        <span class="font-medium"
                            >{pendaftaran.keterampilan_yang_diminati}</span
                        >
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Data Wali</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4 text-sm">
                    <div>
                        <span class="text-muted-foreground block text-xs"
                            >Nama Wali</span
                        >
                        <span class="font-medium"
                            >{pendaftaran.wali?.user?.name || "-"}</span
                        >
                    </div>
                    <div>
                        <span class="text-muted-foreground block text-xs"
                            >Email Wali</span
                        >
                        <span class="font-medium"
                            >{pendaftaran.wali?.user?.email || "-"}</span
                        >
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Dokumen & Verifikasi -->
        <div class="md:col-span-2 space-y-6">
            <form onsubmit={(e) => e.preventDefault()}>
                <Card>
                    <CardHeader>
                        <CardTitle>Verifikasi Dokumen Berkas</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-6">
                            {#each pendaftaran.berkas as berkas}
                                <div
                                    class="border p-5 rounded-lg bg-card text-card-foreground shadow-sm"
                                >
                                    <div
                                        class="flex justify-between items-start mb-4"
                                    >
                                        <div>
                                            <h4 class="font-semibold">
                                                {berkas.jenis_berkas_pendaftaran
                                                    .nama_berkas}
                                            </h4>
                                            <p
                                                class="text-xs text-muted-foreground mt-1"
                                            >
                                                Diupload pada: {new Date(
                                                    berkas.created_at,
                                                ).toLocaleString()}
                                            </p>
                                        </div>
                                        <a
                                            href={`/storage/${berkas.file_path}`}
                                            target="_blank"
                                            rel="noreferrer"
                                            class="text-sm bg-primary/10 text-primary px-3 py-1.5 rounded-md font-medium hover:bg-primary/20 transition-colors"
                                        >
                                            Lihat File
                                        </a>
                                    </div>
                                    <div
                                        class="grid grid-cols-1 gap-4 pt-4 border-t"
                                    >
                                        <div
                                            class="flex flex-wrap items-center gap-6"
                                        >
                                            <Label
                                                class="flex items-center space-x-2.5 cursor-pointer"
                                            >
                                                <input
                                                    type="radio"
                                                    bind:group={
                                                        form.berkas[berkas.id]
                                                            .status
                                                    }
                                                    value="diterima"
                                                    class="w-4 h-4 text-primary accent-primary focus:ring-primary cursor-pointer"
                                                />
                                                <span class="font-medium"
                                                    >Valid / Diterima</span
                                                >
                                            </Label>
                                            <Label
                                                class="flex items-center space-x-2.5 cursor-pointer"
                                            >
                                                <input
                                                    type="radio"
                                                    bind:group={
                                                        form.berkas[berkas.id]
                                                            .status
                                                    }
                                                    value="perbaikan"
                                                    class="w-4 h-4 text-orange-500 accent-orange-500 focus:ring-orange-500 cursor-pointer"
                                                />
                                                <span
                                                    class="font-medium text-orange-600 dark:text-orange-400"
                                                    >Perlu Perbaikan</span
                                                >
                                            </Label>
                                        </div>
                                        {#if form.berkas[berkas.id].status === "perbaikan"}
                                            <div class="mt-2">
                                                <Label
                                                    class="text-xs font-semibold text-orange-600 dark:text-orange-400"
                                                    >Catatan Perbaikan Dokumen
                                                    Ini</Label
                                                >
                                                <Input
                                                    bind:value={
                                                        form.berkas[berkas.id]
                                                            .keterangan_perbaikan
                                                    }
                                                    placeholder="Jelaskan apa yang salah..."
                                                    class="mt-1.5 border-orange-200 dark:border-orange-800 focus-visible:ring-orange-500"
                                                />
                                            </div>
                                        {/if}
                                    </div>
                                </div>
                            {/each}
                        </div>
                    </CardContent>
                </Card>

                <Card class="mt-6 border-indigo-200">
                    <CardHeader>
                        <CardTitle>Keputusan & Tindak Lanjut</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div>
                            <Label>Catatan Verifikasi (Umum)</Label>
                            <Textarea
                                bind:value={form.catatan_verifikasi}
                                placeholder="Opsional: Tambahkan pesan umum untuk wali pendaftar"
                                class="mt-1"
                            />
                        </div>

                        <!-- Panel jika ACC (Asesmen) -->
                        <div
                            class="p-4 border border-green-200 bg-green-50 rounded-md space-y-4"
                        >
                            <h4 class="font-semibold text-green-800">
                                Jika Diterima ke Tahap Asesmen (ACC Semua):
                            </h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <Label>Jadwal Asesmen (Tanggal & Jam)</Label
                                    >
                                    <Input
                                        type="datetime-local"
                                        bind:value={form.jadwal_asesmen}
                                        class="mt-1"
                                    />
                                    <InputError
                                        message={form.errors.jadwal_asesmen}
                                    />
                                </div>
                                <div>
                                    <Label>Tempat Asesmen</Label>
                                    <Input
                                        bind:value={form.tempat_asesmen}
                                        class="mt-1"
                                    />
                                    <InputError
                                        message={form.errors.tempat_asesmen}
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-4 pt-4 border-t">
                            {#if Object.values(form.berkas).some((b: any) => b.status === 'perbaikan' && b.keterangan_perbaikan.trim() !== '')}
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="flex-1 text-orange-600 border-orange-200 hover:bg-orange-50"
                                    onclick={() => submitVerify("revisi")}
                                    disabled={form.processing}
                                >
                                    Minta Perbaikan Berkas
                                </Button>
                            {/if}
                            <Button
                                type="button"
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white"
                                onclick={() => submitVerify("acc")}
                                disabled={form.processing}
                            >
                                ACC & Jadwalkan Asesmen
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </form>
        </div>
    </div>
</div>
