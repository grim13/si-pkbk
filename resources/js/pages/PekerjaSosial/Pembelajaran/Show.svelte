<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Approval Pembelajaran",
                href: "/pekerja-sosial/pembelajaran",
            },
            {
                title: "Detail & Approval Pertemuan",
                href: "#",
            },
        ],
    };
</script>

<script lang="ts">
    import { useForm, Link } from "@inertiajs/svelte";
    import { untrack } from "svelte";
    import AppHead from "@/components/AppHead.svelte";
    import { Button } from "@/components/ui/button";
    import { Label } from "@/components/ui/label";
    import { Textarea } from "@/components/ui/textarea";
    import { Badge } from "@/components/ui/badge";
    import * as Table from "@/components/ui/table";
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
        MapPin,
        User,
        CheckCircle2,
        RotateCcw,
    } from "@lucide/svelte";

    let { pertemuan } = $props();

    const form = useForm({
        status: "acc",
        keterangan: untrack(
            () => pertemuan.catatan_pekerja_sosial || pertemuan.keterangan || "",
        ),
    });

    function submitDecision(targetStatus: "perbaikan" | "acc") {
        form.status = targetStatus;
        form.post(`/pekerja-sosial/pembelajaran/${pertemuan.id}`);
    }

    function formatTime(t: string | null) {
        if (!t) return "-";
        return t.slice(0, 5);
    }

    function formatDate(date: string | null) {
        if (!date) return "-";
        return new Date(date).toLocaleDateString("id-ID", {
            weekday: "long",
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
    }

    function presensiLabel(presensi: string) {
        switch (presensi) {
            case "hadir":
                return "HADIR";
            case "izin":
                return "IZIN";
            case "absen":
                return "ABSEN";
            default:
                return presensi?.toUpperCase() || "-";
        }
    }

    function presensiBadgeClass(presensi: string) {
        switch (presensi) {
            case "hadir":
                return "bg-green-100 text-green-800";
            case "izin":
                return "bg-amber-100 text-amber-800";
            case "absen":
                return "bg-red-100 text-red-800";
            default:
                return "bg-slate-100 text-slate-800";
        }
    }

    function nilaiLabel(nilai: string | null) {
        switch (nilai) {
            case "sangat_baik":
                return "Sangat Baik";
            case "baik":
                return "Baik";
            case "cukup":
                return "Cukup";
            case "kurang":
                return "Kurang";
            default:
                return "-";
        }
    }

    function nilaiBadgeClass(nilai: string | null) {
        switch (nilai) {
            case "sangat_baik":
                return "bg-emerald-100 text-emerald-800";
            case "baik":
                return "bg-blue-100 text-blue-800";
            case "cukup":
                return "bg-amber-100 text-amber-800";
            case "kurang":
                return "bg-red-100 text-red-800";
            default:
                return "bg-slate-100 text-slate-800";
        }
    }

    function statusBadgeClass(status: string) {
        switch (status) {
            case "pengajuan":
                return "bg-blue-100 text-blue-800";
            case "perbaikan":
                return "bg-amber-100 text-amber-800";
            case "acc":
                return "bg-green-100 text-green-800";
            default:
                return "bg-slate-100 text-slate-800";
        }
    }
</script>

<AppHead title={`Approval Pertemuan — ${pertemuan.jadwal?.mata_pelajaran?.nama || "Pembelajaran"}`} />

<div class="p-6 w-full space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <Button variant="ghost" size="sm" class="-ml-2" asChild>
                    <Link href={`/pekerja-sosial/pembelajaran?tahun=${pertemuan.jadwal?.tahun || ""}`}>
                        <ArrowLeft class="mr-1.5 h-4 w-4" />
                        Kembali ke Daftar Approval
                    </Link>
                </Button>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {statusBadgeClass(pertemuan.status)}">
                    {pertemuan.status?.toUpperCase()}
                </span>
            </div>
            <h1 class="text-xl font-bold tracking-tight">
                {pertemuan.jadwal?.mata_pelajaran?.nama || "Mata Pelajaran"}
            </h1>
            <div class="flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                <span class="inline-flex items-center gap-1 font-medium text-foreground">
                    <Calendar class="h-3.5 w-3.5" />
                    {formatDate(pertemuan.tanggal)}
                </span>
                <span class="inline-flex items-center gap-1">
                    <Clock class="h-3.5 w-3.5" />
                    {pertemuan.jadwal?.hari}, {formatTime(pertemuan.jadwal?.jam_mulai)} - {formatTime(pertemuan.jadwal?.jam_selesai)}
                </span>
                <span class="inline-flex items-center gap-1">
                    <User class="h-3.5 w-3.5" />
                    Instruktur: {pertemuan.instruktur?.name || "-"}
                </span>
                {#if pertemuan.jadwal?.ruangan}
                    <span class="inline-flex items-center gap-1">
                        <MapPin class="h-3.5 w-3.5" />
                        {pertemuan.jadwal.ruangan}
                    </span>
                {/if}
            </div>
        </div>

        <div class="flex flex-wrap gap-1.5">
            {#each pertemuan.jadwal?.mata_pelajaran?.jenis_disabilitas || [] as jd (jd.id)}
                <Badge variant="secondary">{jd.nama}</Badge>
            {/each}
        </div>
    </div>

    <!-- Keterangan Pertemuan -->
    <Card>
        <CardContent class="p-4 grid grid-cols-1 gap-4 md:grid-cols-2 text-sm">
            <div>
                <p class="text-xs font-medium text-muted-foreground">Keterangan Pertemuan</p>
                <p class="mt-1">{pertemuan.keterangan || "-"}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-muted-foreground">Riwayat Approval Pekerja Sosial</p>
                {#if pertemuan.pekerja_sosial}
                    <p class="mt-1">
                        Oleh <strong>{pertemuan.pekerja_sosial.name}</strong>
                        {#if pertemuan.approved_at}
                            ({formatDate(pertemuan.approved_at)})
                        {/if}
                    </p>
                {:else}
                    <p class="mt-1 text-muted-foreground">Belum diverifikasi</p>
                {/if}
            </div>
        </CardContent>
    </Card>

    <!-- Tabel Presensi & Penilaian Peserta -->
    <Card>
        <CardHeader class="border-b py-4 px-6">
            <CardTitle class="text-base">
                Daftar Presensi & Nilai Kualitatif Peserta ({pertemuan.peserta_detail?.length || 0} Peserta)
            </CardTitle>
        </CardHeader>
        <CardContent class="p-0">
            <Table.Root>
                <Table.Header>
                    <Table.Row>
                        <Table.Head class="w-12">No</Table.Head>
                        <Table.Head>No. Pendaftaran</Table.Head>
                        <Table.Head>Nama Peserta</Table.Head>
                        <Table.Head>Jenis Disabilitas</Table.Head>
                        <Table.Head>Presensi</Table.Head>
                        <Table.Head>Nilai Kualitatif</Table.Head>
                        <Table.Head>Keterangan Nilai</Table.Head>
                    </Table.Row>
                </Table.Header>
                <Table.Body>
                    {#if !pertemuan.peserta_detail?.length}
                        <Table.Row>
                            <Table.Cell colspan={7} class="h-24 text-center text-muted-foreground">
                                Tidak ada data peserta pada pertemuan ini.
                            </Table.Cell>
                        </Table.Row>
                    {:else}
                        {#each pertemuan.peserta_detail as detail, idx (detail.id)}
                            <Table.Row>
                                <Table.Cell class="font-medium">{idx + 1}</Table.Cell>
                                <Table.Cell>{detail.pendaftaran_peserta?.no_pendaftaran || "-"}</Table.Cell>
                                <Table.Cell class="font-semibold">
                                    {detail.pendaftaran_peserta?.nama || "-"}
                                </Table.Cell>
                                <Table.Cell>
                                    <Badge variant="outline">
                                        {detail.pendaftaran_peserta?.jenis_disabilitas?.nama || "-"}
                                    </Badge>
                                </Table.Cell>
                                <Table.Cell>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {presensiBadgeClass(detail.presensi)}">
                                        {presensiLabel(detail.presensi)}
                                    </span>
                                </Table.Cell>
                                <Table.Cell>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {nilaiBadgeClass(detail.nilai)}">
                                        {nilaiLabel(detail.nilai)}
                                    </span>
                                </Table.Cell>
                                <Table.Cell class="text-sm">
                                    {detail.keterangan || "-"}
                                </Table.Cell>
                            </Table.Row>
                        {/each}
                    {/if}
                </Table.Body>
            </Table.Root>
        </CardContent>
    </Card>

    <!-- Form Keputusan Approval Pekerja Sosial -->
    <Card>
        <CardHeader class="border-b py-4 px-6">
            <CardTitle class="text-base">
                Keputusan Approval Pekerja Sosial
            </CardTitle>
        </CardHeader>
        <CardContent class="p-6 space-y-4">
            <div class="space-y-2">
                <Label for="keterangan-approval">
                    Keterangan / Catatan Evaluasi Pertemuan
                </Label>
                <Textarea
                    id="keterangan-approval"
                    rows={3}
                    placeholder="Isi keterangan persetujuan atau alasan permintaan perbaikan untuk instruktur..."
                    bind:value={form.keterangan}
                />
                {#if form.errors.keterangan}
                    <p class="text-xs text-destructive">{form.errors.keterangan}</p>
                {/if}
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 pt-2">
                <Button
                    type="button"
                    variant="outline"
                    class="border-amber-500 text-amber-700 hover:bg-amber-50"
                    disabled={form.processing}
                    onclick={() => submitDecision("perbaikan")}
                >
                    <RotateCcw class="mr-2 h-4 w-4" />
                    Minta Perbaikan
                </Button>

                <Button
                    type="button"
                    class="bg-green-600 hover:bg-green-700 text-white"
                    disabled={form.processing}
                    onclick={() => submitDecision("acc")}
                >
                    <CheckCircle2 class="mr-2 h-4 w-4" />
                    Setujui (ACC) Pertemuan
                </Button>
            </div>
        </CardContent>
    </Card>
</div>
