<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Proses Pembelajaran",
                href: "/instruktur/pembelajaran",
            },
            {
                title: "Presensi & Penilaian Pertemuan",
                href: "#",
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
    import * as Select from "@/components/ui/select";
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
        Lock,
        AlertCircle,
        CheckCircle2,
        Save,
        Send,
    } from "@lucide/svelte";

    let {
        jadwal,
        tanggal,
        pertemuan = null,
        pesertaList = [],
    } = $props();

    let selectedTanggal = $state(untrack(() => tanggal));

    const isLocked = $derived(
        pertemuan?.status === "pengajuan" || pertemuan?.status === "acc",
    );

    const presensiChoices = [
        { value: "hadir", label: "Hadir", activeClass: "bg-green-600 text-white border-green-600" },
        { value: "izin", label: "Izin", activeClass: "bg-amber-500 text-white border-amber-500" },
        { value: "absen", label: "Absen", activeClass: "bg-red-600 text-white border-red-600" },
    ];

    const nilaiChoices = [
        { value: "sangat_baik", label: "Sangat Baik" },
        { value: "baik", label: "Baik" },
        { value: "cukup", label: "Cukup" },
        { value: "kurang", label: "Kurang" },
    ];

    function buildInitialRows() {
        const detailMap = new Map<number, any>();
        if (pertemuan?.peserta_detail) {
            for (const d of pertemuan.peserta_detail) {
                detailMap.set(Number(d.pendaftaran_peserta_id), d);
            }
        }

        return pesertaList.map((p: any) => {
            const existing = detailMap.get(Number(p.id));
            return {
                pendaftaran_peserta_id: p.id,
                presensi: existing?.presensi || "",
                nilai: existing?.nilai || "",
                keterangan: existing?.keterangan || "",
            };
        });
    }

    const form = useForm({
        tanggal: untrack(() => tanggal),
        status: "draft",
        keterangan: untrack(() => pertemuan?.keterangan || ""),
        peserta: untrack(() => buildInitialRows()),
    });

    function handleDateChange(e: Event) {
        const newDate = (e.target as HTMLInputElement).value;
        selectedTanggal = newDate;
        if (!newDate) return;
        router.get(`/instruktur/pembelajaran/jadwal/${jadwal.id}`, {
            tanggal: newDate,
        });
    }

    function markAllHadir() {
        if (isLocked) return;
        form.peserta = form.peserta.map((row: any) => ({
            ...row,
            presensi: row.presensi || "hadir",
        }));
    }

    function submitForm(targetStatus: "draft" | "pengajuan") {
        if (isLocked) return;
        form.tanggal = selectedTanggal;
        form.status = targetStatus;
        form.post(`/instruktur/pembelajaran/jadwal/${jadwal.id}`, {
            preserveScroll: true,
        });
    }

    function formatTime(t: string | null) {
        if (!t) return "-";
        return t.slice(0, 5);
    }

    function formatDate(date: string | null) {
        if (!date) return "-";
        return new Date(date).toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
    }

    function statusBadgeClass(status: string | undefined) {
        switch (status) {
            case "draft":
                return "bg-slate-100 text-slate-800";
            case "pengajuan":
                return "bg-blue-100 text-blue-800";
            case "perbaikan":
                return "bg-amber-100 text-amber-800";
            case "acc":
                return "bg-green-100 text-green-800";
            default:
                return "bg-slate-100 text-slate-700";
        }
    }

    function statusLabel(status: string | undefined) {
        if (!status) return "BELUM DISIMPAN (DRAFT BARU)";
        return status.toUpperCase();
    }
</script>

<AppHead title={`Presensi & Penilaian — ${jadwal.mata_pelajaran?.nama || "Pembelajaran"}`} />

<div class="p-6 w-full space-y-6">
    <!-- Top Bar -->
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <Button variant="ghost" size="sm" class="-ml-2" asChild>
                    <Link href={`/instruktur/pembelajaran?tahun=${jadwal.tahun}`}>
                        <ArrowLeft class="mr-1.5 h-4 w-4" />
                        Kembali ke Daftar
                    </Link>
                </Button>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {statusBadgeClass(pertemuan?.status)}">
                    {statusLabel(pertemuan?.status)}
                </span>
            </div>
            <h1 class="text-xl font-bold tracking-tight">
                {jadwal.mata_pelajaran?.nama || "Mata Pelajaran"}
            </h1>
            <div class="flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                <span class="inline-flex items-center gap-1">
                    <Calendar class="h-3.5 w-3.5" />
                    Hari {jadwal.hari} (Tahun {jadwal.tahun})
                </span>
                <span class="inline-flex items-center gap-1">
                    <Clock class="h-3.5 w-3.5" />
                    {formatTime(jadwal.jam_mulai)} - {formatTime(jadwal.jam_selesai)}
                </span>
                {#if jadwal.ruangan}
                    <span class="inline-flex items-center gap-1">
                        <MapPin class="h-3.5 w-3.5" />
                        {jadwal.ruangan}
                    </span>
                {/if}
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            {#each jadwal.mata_pelajaran?.jenis_disabilitas || [] as jd (jd.id)}
                <Badge variant="secondary">{jd.nama}</Badge>
            {/each}
        </div>
    </div>

    <!-- Status Banners -->
    {#if isLocked}
        <div class="flex items-start gap-3 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
            <Lock class="h-5 w-5 shrink-0 mt-0.5 text-blue-600" />
            <div>
                <p class="font-semibold">
                    Pertemuan dalam status {statusLabel(pertemuan?.status)} — Data Terkunci
                </p>
                <p class="text-xs mt-0.5 text-blue-800">
                    Pada status <strong>Pengajuan</strong> atau <strong>ACC</strong>, instruktur tidak dapat mengubah atau memperbaiki presensi maupun nilai peserta.
                </p>
                {#if pertemuan?.catatan_pekerja_sosial}
                    <p class="text-xs mt-2 font-medium">
                        Catatan Pekerja Sosial: "{pertemuan.catatan_pekerja_sosial}"
                    </p>
                {/if}
            </div>
        </div>
    {:else if pertemuan?.status === "perbaikan"}
        <div class="flex items-start gap-3 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
            <AlertCircle class="h-5 w-5 shrink-0 mt-0.5 text-amber-600" />
            <div>
                <p class="font-semibold">Permintaan Perbaikan dari Pekerja Sosial</p>
                <p class="text-xs mt-0.5">
                    {pertemuan.catatan_pekerja_sosial || pertemuan.keterangan || "Silakan perbaiki presensi atau penilaian peserta kemudian ajukan kembali."}
                </p>
            </div>
        </div>
    {/if}

    <!-- Pengaturan Tanggal & Keterangan Pertemuan -->
    <Card>
        <CardContent class="p-4 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="space-y-1.5">
                <Label for="tanggal">
                    Tanggal Pembelajaran (Tahun {jadwal.tahun}) <span class="text-red-500">*</span>
                </Label>
                <Input
                    id="tanggal"
                    type="date"
                    min={`${jadwal.tahun}-01-01`}
                    max={`${jadwal.tahun}-12-31`}
                    value={selectedTanggal}
                    onchange={handleDateChange}
                />
                {#if form.errors.tanggal}
                    <p class="text-xs text-destructive">{form.errors.tanggal}</p>
                {/if}
                <p class="text-[11px] text-muted-foreground">
                    Mengubah tanggal akan memuat peserta yang sudah diterima pada/sebelum tanggal tersebut.
                </p>
            </div>

            <div class="space-y-1.5 md:col-span-2">
                <Label for="keterangan-pertemuan">Keterangan Pertemuan (Materi / Catatan Pertemuan)</Label>
                <Textarea
                    id="keterangan-pertemuan"
                    rows={2}
                    disabled={isLocked}
                    placeholder="Contoh: Materi pengenalan dasar dan latihan praktik..."
                    bind:value={form.keterangan}
                />
                {#if form.errors.keterangan}
                    <p class="text-xs text-destructive">{form.errors.keterangan}</p>
                {/if}
            </div>
        </CardContent>
    </Card>

    <!-- Tabel Presensi & Penilaian Kualitatif (Satu Tabel) -->
    <Card>
        <CardHeader class="flex flex-col gap-2 border-b py-4 px-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <CardTitle class="text-base">
                    Tabel Presensi & Penilaian Kualitatif Peserta
                </CardTitle>
                <p class="text-xs text-muted-foreground mt-0.5">
                    1. Pilih presensi (Hadir / Izin / Absen) &rarr; 2. Pilih nilai kualitatif (Sangat Baik, Baik, Cukup, Kurang) &rarr; 3. Isi keterangan (wajib jika nilai <strong>Kurang</strong>).
                </p>
            </div>
            {#if !isLocked && pesertaList.length > 0}
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onclick={markAllHadir}
                >
                    <CheckCircle2 class="mr-1.5 h-3.5 w-3.5" />
                    Tandai Belum Terisi sebagai Hadir
                </Button>
            {/if}
        </CardHeader>

        <CardContent class="p-0">
            {#if form.errors.peserta}
                <div class="m-4 rounded-lg border border-destructive/40 bg-destructive/10 p-3 text-xs text-destructive font-medium">
                    {form.errors.peserta}
                </div>
            {/if}

            <Table.Root>
                <Table.Header>
                    <Table.Row>
                        <Table.Head class="w-12">No</Table.Head>
                        <Table.Head>Peserta</Table.Head>
                        <Table.Head>Disabilitas</Table.Head>
                        <Table.Head>Tgl. Mulai Diterima</Table.Head>
                        <Table.Head>Presensi <span class="text-red-500">*</span></Table.Head>
                        <Table.Head class="w-48">Nilai Kualitatif <span class="text-red-500">*</span></Table.Head>
                        <Table.Head>Keterangan Nilai</Table.Head>
                    </Table.Row>
                </Table.Header>
                <Table.Body>
                    {#if pesertaList.length === 0}
                        <Table.Row>
                            <Table.Cell colspan={7} class="h-28 text-center text-muted-foreground">
                                Tidak ada peserta berstatus <strong>Diterima</strong> untuk jenis disabilitas mata pelajaran ini yang tanggal mulai diterimanya sebelum/pada <strong>{formatDate(selectedTanggal)}</strong>.
                            </Table.Cell>
                        </Table.Row>
                    {:else}
                        {#each pesertaList as peserta, idx (peserta.id)}
                            {@const row = form.peserta[idx]}
                            {@const presensiErr = (form.errors as Record<string, string>)[`peserta.${idx}.presensi`]}
                            {@const nilaiErr = (form.errors as Record<string, string>)[`peserta.${idx}.nilai`]}
                            {@const ketErr = (form.errors as Record<string, string>)[`peserta.${idx}.keterangan`]}

                            {#if row}
                                <Table.Row class="align-top">
                                    <Table.Cell class="font-medium pt-4">{idx + 1}</Table.Cell>
                                    <Table.Cell class="pt-3">
                                        <div class="font-semibold text-sm">{peserta.nama}</div>
                                        <div class="text-xs text-muted-foreground">
                                            No. Reg: {peserta.no_pendaftaran || "-"}
                                        </div>
                                    </Table.Cell>
                                    <Table.Cell class="pt-3.5">
                                        <Badge variant="outline">
                                            {peserta.jenis_disabilitas?.nama || "-"}
                                        </Badge>
                                    </Table.Cell>
                                    <Table.Cell class="pt-4 text-xs whitespace-nowrap text-muted-foreground">
                                        {formatDate(peserta.tanggal_mulai_diterima)}
                                    </Table.Cell>

                                    <!-- Presensi -->
                                    <Table.Cell class="pt-3">
                                        <div class="inline-flex rounded-md border bg-muted/30 p-0.5 gap-1">
                                            {#each presensiChoices as pOpt (pOpt.value)}
                                                <button
                                                    type="button"
                                                    disabled={isLocked}
                                                    class="px-2.5 py-1 rounded text-xs font-medium transition-colors border {row.presensi === pOpt.value
                                                        ? pOpt.activeClass
                                                        : 'border-transparent text-muted-foreground hover:text-foreground'} disabled:opacity-60"
                                                    onclick={() => {
                                                        if (!isLocked) {
                                                            row.presensi = pOpt.value;
                                                        }
                                                    }}
                                                >
                                                    {pOpt.label}
                                                </button>
                                            {/each}
                                        </div>
                                        {#if presensiErr}
                                            <p class="text-xs text-destructive mt-1">{presensiErr}</p>
                                        {/if}
                                    </Table.Cell>

                                    <!-- Nilai Kualitatif -->
                                    <Table.Cell class="pt-3">
                                        <Select.Root
                                            type="single"
                                            disabled={isLocked || !row.presensi}
                                            value={row.nilai}
                                            onValueChange={(val) => {
                                                row.nilai = val;
                                            }}
                                        >
                                            <Select.Trigger class="w-44">
                                                {#if !row.presensi}
                                                    Pilih presensi dulu
                                                {:else if row.nilai}
                                                    {nilaiChoices.find((n) => n.value === row.nilai)?.label || "Pilih Nilai"}
                                                {:else}
                                                    Pilih Nilai
                                                {/if}
                                            </Select.Trigger>
                                            <Select.Content>
                                                {#each nilaiChoices as nOpt (nOpt.value)}
                                                    <Select.Item value={nOpt.value} label={nOpt.label}>
                                                        {nOpt.label}
                                                    </Select.Item>
                                                {/each}
                                            </Select.Content>
                                        </Select.Root>
                                        {#if nilaiErr}
                                            <p class="text-xs text-destructive mt-1">{nilaiErr}</p>
                                        {/if}
                                    </Table.Cell>

                                    <!-- Keterangan Nilai -->
                                    <Table.Cell class="pt-3 min-w-[240px]">
                                        <Input
                                            type="text"
                                            disabled={isLocked || !row.presensi}
                                            placeholder={row.nilai === "kurang"
                                                ? "Wajib diisi karena nilai Kurang *"
                                                : "Keterangan tambahan (opsional)"}
                                            class={row.nilai === "kurang" && !row.keterangan?.trim()
                                                ? "border-amber-500 focus-visible:ring-amber-500"
                                                : ""}
                                            bind:value={row.keterangan}
                                        />
                                        {#if row.nilai === "kurang"}
                                            <span class="text-[11px] font-medium text-amber-700">
                                                * Wajib diisi untuk nilai Kurang
                                            </span>
                                        {/if}
                                        {#if ketErr}
                                            <p class="text-xs text-destructive mt-0.5">{ketErr}</p>
                                        {/if}
                                    </Table.Cell>
                                </Table.Row>
                            {/if}
                        {/each}
                    {/if}
                </Table.Body>
            </Table.Root>
        </CardContent>
    </Card>

    <!-- Action Buttons -->
    {#if !isLocked && pesertaList.length > 0}
        <div class="flex flex-wrap items-center justify-end gap-3 border-t pt-4">
            <Button
                type="button"
                variant="outline"
                disabled={form.processing}
                onclick={() => submitForm("draft")}
            >
                <Save class="mr-2 h-4 w-4" />
                {pertemuan?.status === "perbaikan" ? "Simpan Perbaikan" : "Simpan Draft"}
            </Button>

            <Button
                type="button"
                disabled={form.processing}
                onclick={() => submitForm("pengajuan")}
            >
                <Send class="mr-2 h-4 w-4" />
                Simpan & Ajukan ke Pekerja Sosial
            </Button>
        </div>
    {/if}
</div>
