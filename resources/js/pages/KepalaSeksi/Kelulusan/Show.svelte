<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Penentuan Kelulusan",
                href: "/kepala-seksi/kelulusan",
            },
            {
                title: "Detail Peserta",
                href: "",
            },
        ],
    };
</script>

<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import { Button } from "@/components/ui/button";
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from "@/components/ui/card";
    import * as Dialog from "@/components/ui/dialog";
    import { useForm, Link } from "@inertiajs/svelte";
    import { ArrowLeft, CheckCircle2, XCircle } from "@lucide/svelte";

    let { pendaftaran, seksiMaster } = $props();

    const asesmen = $derived(pendaftaran.asesmen);
    const wali = $derived(pendaftaran.wali);

    let activeTab = $state<"data" | "berkas" | "asesmen">("data");
    let confirmOpen = $state(false);

    let form = useForm({ keputusan: "" });

    function openConfirm(keputusan: "diterima" | "ditolak") {
        form.keputusan = keputusan;
        confirmOpen = true;
    }

    function submitKeputusan() {
        form.post(`/kepala-seksi/kelulusan/${pendaftaran.id}`, {
            onFinish: () => (confirmOpen = false),
        });
    }

    // ---------- Helpers ----------
    function val(v: any) {
        if (v === null || v === undefined || v === "") return "-";
        return v;
    }

    function yesNo(v: any) {
        if (v === null || v === undefined) return "-";
        return v ? "Ya" : "Tidak";
    }

    function formatDate(date: string | null, withTime = false) {
        if (!date) return "-";
        const opts: Intl.DateTimeFormatOptions = {
            day: "2-digit",
            month: "long",
            year: "numeric",
        };
        if (withTime) {
            opts.hour = "2-digit";
            opts.minute = "2-digit";
        }
        return new Date(date).toLocaleString("id-ID", opts);
    }

    function formatRupiah(v: any) {
        if (v === null || v === undefined || v === "") return "-";
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            maximumFractionDigits: 0,
        }).format(Number(v));
    }

    function formatStatus(status: string) {
        if (status === "selesai_asesmen") return "MENUNGGU KEPUTUSAN";
        return status ? status.replaceAll("_", " ").toUpperCase() : "-";
    }

    function statusClass(status: string) {
        switch (status) {
            case "selesai_asesmen":
                return "bg-amber-100 text-amber-800";
            case "diterima":
                return "bg-green-100 text-green-800";
            case "ditolak":
                return "bg-red-100 text-red-800";
            case "perbaikan":
                return "bg-orange-100 text-orange-800";
            default:
                return "bg-slate-100 text-slate-800";
        }
    }

    function parseList(v: any): string[] {
        if (!v) return [];
        if (Array.isArray(v)) return v;
        try {
            const parsed = JSON.parse(v);
            return Array.isArray(parsed) ? parsed : [];
        } catch {
            return [];
        }
    }

    function getJawaban(pertanyaanId: number) {
        return asesmen?.jawaban?.find(
            (j: any) => j.asesmen_pertanyaan_id === pertanyaanId,
        );
    }

    function formatJawaban(p: any) {
        const j = getJawaban(p.id);
        if (!j) return "-";
        if (p.tipe_jawaban === "ya_tidak") return yesNo(j.jawaban_ya_tidak);
        if (p.tipe_jawaban === "number")
            return j.jawaban_number !== null && j.jawaban_number !== undefined
                ? `${j.jawaban_number}${p.satuan ? " " + p.satuan : ""}`
                : "-";
        return val(j.jawaban_text ?? j.jawaban_number ?? null);
    }

    function checkedLabels(items: { key: string; label: string }[]) {
        const res = items.filter((i) => asesmen?.[i.key]).map((i) => i.label);
        return res.length ? res.join(", ") : "-";
    }

    // ---------- Field groups ----------
    const identitasKlien = $derived([
        ["NIK", pendaftaran.nik],
        ["Nama Lengkap", pendaftaran.nama],
        ["Jenis Kelamin", pendaftaran.jenis_kelamin],
        [
            "Tempat, Tanggal Lahir",
            `${pendaftaran.tempat_lahir}, ${formatDate(pendaftaran.tanggal_lahir)}`,
        ],
        ["Agama", pendaftaran.agama],
        ["Status Perkawinan", pendaftaran.status_perkawinan],
        ["Alamat Asal", pendaftaran.alamat_asal],
        ["Pendidikan Terakhir", pendaftaran.pendidikan],
        ["Keterampilan Diminati", pendaftaran.keterampilan_yang_diminati],
        ["Nomor Induk Klien", pendaftaran.nomor_induk_klien],
    ]);

    const kondisiFisik = $derived([
        [
            "Tinggi Badan",
            pendaftaran.tinggi_badan ? `${pendaftaran.tinggi_badan} cm` : null,
        ],
        [
            "Berat Badan",
            pendaftaran.berat_badan ? `${pendaftaran.berat_badan} kg` : null,
        ],
        ["Golongan Darah", pendaftaran.golongan_darah],
        ["Bentuk/Keadaan Mata", pendaftaran.bentuk_keadaan_mata],
        ["Pendengaran", pendaftaran.pendengaran],
        ["Disabilitas Netra Sejak", pendaftaran.disabilitas_netra_sejak],
        ["Penyebab Disabilitas", pendaftaran.penyebab_disabilitas],
        ["Tempat Cacat", pendaftaran.tempat_cacat],
        ["Dirawat Di", pendaftaran.dirawat_di],
        ["Gradasi Kecacatan", pendaftaran.gradasi_kecacatan],
        [
            "Pengaruh Cacat thd Tingkah Laku",
            pendaftaran.pengaruh_cacat_tingkah_laku,
        ],
    ]);

    const dataWali = $derived([
        ["Nama", wali?.user?.name],
        ["Email", wali?.user?.email],
        ["NIK", wali?.nik],
        ["No. HP", wali?.nomor_hp],
        [
            "Tempat, Tanggal Lahir",
            wali ? `${wali.tempat_lahir}, ${formatDate(wali.tanggal_lahir)}` : null,
        ],
        ["Alamat", wali?.alamat],
        ["Agama", wali?.agama],
        ["Pendidikan Terakhir", wali?.pendidikan_terakhir],
        ["Status dalam Keluarga", wali?.status_dalam_keluarga],
        ["Pekerjaan", wali?.pekerjaan],
        ["Penghasilan / Bulan", formatRupiah(wali?.penghasilan_perbulan)],
        ["Tanggungan Keluarga", wali?.tanggungan_keluarga],
        ["Rumah Tempat Tinggal", wali?.rumah_tempat_tinggal],
        ["Keadaan Lingkungan", wali?.keadaan_lingkungan],
    ]);

    const pendidikanAsesmen = $derived([
        ["TK", asesmen?.pendidikan_tk],
        ["SD", asesmen?.pendidikan_sd],
        ["SMP", asesmen?.pendidikan_smp],
        ["SMA", asesmen?.pendidikan_sma],
        ["Perguruan Tinggi", asesmen?.pendidikan_pt],
        ["Kursus", asesmen?.kursus_nama],
        ["Kursus Diberikan Oleh", asesmen?.kursus_diberikan_oleh],
        ["Tempat Kursus", asesmen?.kursus_tempat],
        ["Lama Kursus", asesmen?.kursus_lama],
    ]);

    const sikapAsesmen = $derived([
        ["Sikap Klien", asesmen?.sikap_klien],
        ["Sikap Keluarga", asesmen?.sikap_keluarga],
        ["Sikap Masyarakat", asesmen?.sikap_masyarakat],
        ["Keluhan / Hambatan", asesmen?.keluhan_hambatan],
    ]);

    const kelahiranAsesmen = $derived([
        ["Bayi Kuning", yesNo(asesmen?.bayi_kuning)],
        ["Penanganan Kuning", asesmen?.bayi_kuning_penanganan],
        [
            "Lama Disinar UV",
            asesmen?.bayi_kuning_uv_hari
                ? `${asesmen.bayi_kuning_uv_hari} hari`
                : null,
        ],
        ["Bayi Menghisap ASI", yesNo(asesmen?.bayi_hisap_asi)],
        [
            "Lama ASI",
            asesmen?.lama_asi_bulan
                ? `${asesmen.lama_asi_bulan} bulan`
                : asesmen?.lama_asi_lainnya,
        ],
        [
            "Kejang / Stuip",
            `${yesNo(asesmen?.masalah_kejang_stuip)}${asesmen?.masalah_kejang_stuip_ket ? " — " + asesmen.masalah_kejang_stuip_ket : ""}`,
        ],
        [
            "Diare",
            `${yesNo(asesmen?.masalah_diare)}${asesmen?.masalah_diare_ket ? " — " + asesmen.masalah_diare_ket : ""}`,
        ],
        [
            "Masalah Lainnya",
            `${yesNo(asesmen?.masalah_lainnya)}${asesmen?.masalah_lainnya_ket ? " — " + asesmen.masalah_lainnya_ket : ""}`,
        ],
    ]);

    const lainLain = $derived([
        [
            "Maksud & Tujuan",
            checkedLabels([
                { key: "tujuan_bimbingan_keterampilan", label: "Bimbingan Keterampilan" },
                { key: "tujuan_bimbingan_sekolah", label: "Bimbingan Sekolah" },
            ]),
        ],
        [
            "Keterampilan Dipilih",
            checkedLabels([
                { key: "keterampilan_praktis_produktif", label: "Praktis Produktif" },
                { key: "keterampilan_massage_pijat", label: "Massage / Pijat" },
            ]),
        ],
        [
            "Sumber Informasi",
            checkedLabels([
                { key: "info_dokter_rs", label: "Dokter / RS" },
                { key: "info_petugas_sosial_kec", label: "Petugas Sosial Kecamatan" },
                { key: "info_dinas_sosial", label: "Dinas Sosial" },
                { key: "info_psm", label: "PSM" },
                { key: "info_teman", label: "Teman" },
            ]),
        ],
    ]);

    const kesimpulan = $derived([
        ["Latar Belakang", asesmen?.kesimpulan_latar_belakang],
        ["Pengaruh Kecacatan", asesmen?.kesimpulan_pengaruh_kecacatan],
        ["Keinginan & Harapan", asesmen?.kesimpulan_keinginan_harapan],
        ["Bentuk Bantuan", asesmen?.kesimpulan_bentuk_bantuan],
    ]);

    const tabs = [
        { key: "data", label: "Data Pendaftaran" },
        { key: "berkas", label: "Berkas" },
        { key: "asesmen", label: "Hasil Asesmen" },
    ] as const;

    // Kelas untuk tata letak compact (masonry)
    const cardCls = "mb-4 break-inside-avoid gap-3 py-4";
    const headCls = "px-4";
    const contentCls = "px-4";
    const titleCls = "text-sm font-semibold";
</script>

{#snippet fieldGrid(items: any[][], cols = 2)}
    <dl
        class="grid gap-x-4 gap-y-2.5 text-sm {cols === 2
            ? 'grid-cols-2'
            : 'grid-cols-1'}"
    >
        {#each items as [label, value]}
            <div class="min-w-0">
                <dt class="text-[11px] uppercase tracking-wide text-muted-foreground">
                    {label}
                </dt>
                <dd class="font-medium break-words whitespace-pre-line">
                    {val(value)}
                </dd>
            </div>
        {/each}
    </dl>
{/snippet}

{#snippet section(title: string, items: any[][], cols = 2, extraCls = "")}
    <Card class="{cardCls} {extraCls}">
        <CardHeader class={headCls}>
            <CardTitle class={titleCls}>{title}</CardTitle>
        </CardHeader>
        <CardContent class={contentCls}>{@render fieldGrid(items, cols)}</CardContent>
    </Card>
{/snippet}

<AppHead title={`Kelulusan: ${pendaftaran.no_pendaftaran}`} />

<div class="p-6 w-full">
    <!-- Header + Keputusan -->
    <div
        class="mb-4 flex flex-col gap-4 rounded-xl border bg-card p-4 lg:flex-row lg:items-center lg:justify-between"
    >
        <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" asChild>
                <Link href="/kepala-seksi/kelulusan" aria-label="Kembali">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
            </Button>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-lg font-bold tracking-tight">
                        {pendaftaran.nama}
                    </h1>
                    <span
                        class="px-2.5 py-0.5 rounded-full text-xs font-semibold {statusClass(
                            pendaftaran.status_pendaftaran,
                        )}"
                    >
                        {formatStatus(pendaftaran.status_pendaftaran)}
                    </span>
                </div>
                <p class="text-xs text-muted-foreground">
                    {pendaftaran.no_pendaftaran} · NIK {pendaftaran.nik} · Asesmen
                    {formatDate(asesmen?.tanggal_asesmen)} oleh {val(
                        asesmen?.pekerja?.name,
                    )}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            {#if pendaftaran.status_pendaftaran !== "selesai_asesmen"}
                <span class="text-xs text-muted-foreground mr-1">
                    Keputusan sudah dibuat, masih dapat diubah.
                </span>
            {/if}
            <Button
                id="btn-tolak"
                variant="outline"
                class="text-red-600 border-red-200 hover:bg-red-50"
                disabled={form.processing ||
                    pendaftaran.status_pendaftaran === "ditolak"}
                onclick={() => openConfirm("ditolak")}
            >
                <XCircle class="mr-2 h-4 w-4" />
                Tolak
            </Button>
            <Button
                id="btn-terima"
                class="bg-green-600 hover:bg-green-700 text-white"
                disabled={form.processing ||
                    pendaftaran.status_pendaftaran === "diterima"}
                onclick={() => openConfirm("diterima")}
            >
                <CheckCircle2 class="mr-2 h-4 w-4" />
                Terima
            </Button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="mb-4 inline-flex rounded-lg border bg-muted p-1">
        {#each tabs as tab}
            <button
                id={`tab-${tab.key}`}
                type="button"
                class="rounded-md px-4 py-1.5 text-sm font-medium transition-colors {activeTab ===
                tab.key
                    ? 'bg-background shadow-sm text-foreground'
                    : 'text-muted-foreground hover:text-foreground'}"
                onclick={() => (activeTab = tab.key)}
            >
                {tab.label}
                {#if tab.key === "berkas"}
                    <span class="ml-1 text-xs text-muted-foreground"
                        >({pendaftaran.berkas?.length || 0})</span
                    >
                {/if}
            </button>
        {/each}
    </div>

    {#if activeTab === "data"}
        <div class="columns-1 gap-4 md:columns-2 2xl:columns-3">
            {@render section("Identitas Klien", identitasKlien)}
            {@render section("Kondisi Fisik & Disabilitas", kondisiFisik)}
            {@render section("Data Wali / Keluarga", dataWali)}

            <Card class={cardCls}>
                <CardHeader class={headCls}>
                    <CardTitle class={titleCls}>Anggota Keluarga</CardTitle>
                </CardHeader>
                <CardContent class={contentCls}>
                    {#if wali?.anggota_keluarga?.length}
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b text-left text-[11px] uppercase tracking-wide text-muted-foreground">
                                        <th class="py-1.5 pr-3">Nama</th>
                                        <th class="py-1.5 pr-3">Status</th>
                                        <th class="py-1.5 pr-3">Tgl. Lahir</th>
                                        <th class="py-1.5 pr-3">Pendidikan</th>
                                        <th class="py-1.5">Pekerjaan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {#each wali.anggota_keluarga as ak}
                                        <tr class="border-b last:border-0">
                                            <td class="py-1.5 pr-3 font-medium">{ak.nama}</td>
                                            <td class="py-1.5 pr-3">{val(ak.status)}</td>
                                            <td class="py-1.5 pr-3 whitespace-nowrap">{formatDate(ak.tanggal_lahir)}</td>
                                            <td class="py-1.5 pr-3">{val(ak.pendidikan)}</td>
                                            <td class="py-1.5">{val(ak.pekerjaan)}</td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        </div>
                    {:else}
                        <p class="text-sm text-muted-foreground">Tidak ada data anggota keluarga.</p>
                    {/if}
                </CardContent>
            </Card>

            {@render section(
                "Verifikasi & Jadwal Asesmen",
                [
                    ["Jadwal Asesmen", formatDate(pendaftaran.jadwal_asesmen, true)],
                    ["Tempat Asesmen", pendaftaran.tempat_asesmen],
                    ["Catatan Verifikasi", pendaftaran.catatan_verifikasi],
                ],
                1,
            )}
        </div>
    {:else if activeTab === "berkas"}
        {#if pendaftaran.berkas?.length}
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                {#each pendaftaran.berkas as berkas}
                    <div class="flex flex-col justify-between gap-3 rounded-lg border bg-card p-4">
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <h4 class="font-semibold text-sm">
                                    {berkas.jenis_berkas_pendaftaran?.nama_berkas}
                                </h4>
                                <span
                                    class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-semibold {statusClass(
                                        berkas.status,
                                    )}"
                                >
                                    {formatStatus(berkas.status)}
                                </span>
                            </div>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                Diupload: {formatDate(berkas.created_at, true)}
                            </p>
                            {#if berkas.keterangan_perbaikan}
                                <p class="mt-1 text-xs text-orange-600">
                                    Catatan: {berkas.keterangan_perbaikan}
                                </p>
                            {/if}
                        </div>
                        <a
                            href={`/storage/${berkas.file_path}`}
                            target="_blank"
                            rel="noreferrer"
                            class="self-start text-sm bg-primary/10 text-primary px-3 py-1.5 rounded-md font-medium hover:bg-primary/20 transition-colors"
                        >
                            Lihat File
                        </a>
                    </div>
                {/each}
            </div>
        {:else}
            <p class="text-sm text-muted-foreground">Tidak ada berkas.</p>
        {/if}
    {:else if !asesmen}
        <Card>
            <CardContent class="py-10 text-center text-sm text-muted-foreground">
                Data asesmen tidak ditemukan.
            </CardContent>
        </Card>
    {:else}
        <div class="columns-1 gap-4 md:columns-2 2xl:columns-3">
            {@render section("Kesimpulan Asesmen", kesimpulan, 1, "border-primary/40")}
            {@render section("Latar Belakang Pendidikan", pendidikanAsesmen)}
            {@render section("Sikap Emosional / Mental", sikapAsesmen, 1)}
            {@render section("Masalah Setelah Kelahiran", kelahiranAsesmen)}

            <Card class={cardCls}>
                <CardHeader class={headCls}>
                    <CardTitle class={titleCls}>Perkembangan Masa Balita (Tambahan)</CardTitle>
                </CardHeader>
                <CardContent class="{contentCls} space-y-2.5 text-sm">
                    <div>
                        <span class="text-[11px] uppercase tracking-wide text-muted-foreground">
                            Hal-hal yang Menyolok
                        </span>
                        {#if parseList(asesmen.hal_hal_menyolok).length}
                            <div class="mt-1 flex flex-wrap gap-1.5">
                                {#each parseList(asesmen.hal_hal_menyolok) as item}
                                    <span class="rounded-md bg-muted px-2 py-0.5 text-xs">{item}</span>
                                {/each}
                            </div>
                        {:else}
                            <p class="font-medium">-</p>
                        {/if}
                    </div>
                    {@render fieldGrid([
                        ["Catatan Tambahan", asesmen.catatan_tambahan_balita],
                        ["Pendapat Sekolah", asesmen.pendapat_sekolah],
                    ])}
                </CardContent>
            </Card>

            {#each seksiMaster as seksi}
                {#if seksi.pertanyaan?.length}
                    <Card class={cardCls}>
                        <CardHeader class={headCls}>
                            <CardTitle class={titleCls}>{seksi.nama}</CardTitle>
                        </CardHeader>
                        <CardContent class={contentCls}>
                            <div class="divide-y">
                                {#each seksi.pertanyaan as p}
                                    {@const j = getJawaban(p.id)}
                                    <div class="flex gap-3 py-1.5 text-sm">
                                        <span class="w-5 shrink-0 text-xs text-muted-foreground pt-0.5">{p.nomor}.</span>
                                        <div class="flex-1 min-w-0">
                                            <p class="leading-snug">{p.pertanyaan}</p>
                                            {#if j?.keterangan}
                                                <p class="mt-0.5 text-xs text-muted-foreground">
                                                    Ket: {j.keterangan}
                                                </p>
                                            {/if}
                                        </div>
                                        <span
                                            class="shrink-0 text-xs font-semibold pt-0.5 {p.tipe_jawaban === 'ya_tidak' && j
                                                ? j.jawaban_ya_tidak
                                                    ? 'text-green-600'
                                                    : 'text-red-600'
                                                : ''}"
                                        >
                                            {formatJawaban(p)}
                                        </span>
                                    </div>
                                {/each}
                            </div>
                        </CardContent>
                    </Card>
                {/if}
            {/each}

            {@render section("Lain-lain", lainLain, 1)}
        </div>
    {/if}
</div>

<Dialog.Root bind:open={confirmOpen}>
    <Dialog.Content class="sm:max-w-[425px]">
        <Dialog.Header>
            <Dialog.Title>Konfirmasi Keputusan</Dialog.Title>
            <Dialog.Description>
                Anda akan menyatakan <strong>{pendaftaran.nama}</strong>
                <strong
                    class={form.keputusan === "diterima"
                        ? "text-green-600"
                        : "text-red-600"}
                >
                    {form.keputusan === "diterima" ? "DITERIMA" : "DITOLAK"}
                </strong>. Lanjutkan?
            </Dialog.Description>
        </Dialog.Header>
        <Dialog.Footer>
            <Button variant="outline" onclick={() => (confirmOpen = false)}>
                Batal
            </Button>
            <Button
                id="btn-konfirmasi-keputusan"
                class={form.keputusan === "diterima"
                    ? "bg-green-600 hover:bg-green-700 text-white"
                    : "bg-red-600 hover:bg-red-700 text-white"}
                disabled={form.processing}
                onclick={submitKeputusan}
            >
                Ya, Simpan
            </Button>
        </Dialog.Footer>
    </Dialog.Content>
</Dialog.Root>
