<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Asesmen Klien",
                href: "/pekerja-sosial/asesmen",
            },
            {
                title: "Form Asesmen",
            },
        ],
    };
</script>

<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import { Button } from "@/components/ui/button";
    import { Input } from "@/components/ui/input";
    import { Label } from "@/components/ui/label";
    import { Textarea } from "@/components/ui/textarea";
    import { Checkbox } from "@/components/ui/checkbox";
    import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
    import { useForm, router } from "@inertiajs/svelte";
    import { untrack } from "svelte";
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import Plus from '@lucide/svelte/icons/plus';

    let { pendaftaran, asesmen, seksiMaster } = $props();

    let currentStep = $state(1);
    const totalSteps = 4;

    const initialJawaban: Record<number, any> = {};
    untrack(() => {
        seksiMaster.forEach((seksi: any) => {
            seksi.pertanyaan.forEach((p: any) => {
                const existing = asesmen.jawaban?.find((j: any) => j.asesmen_pertanyaan_id === p.id);
                initialJawaban[p.id] = {
                    jawaban_ya_tidak: existing ? existing.jawaban_ya_tidak : null,
                    jawaban_text: existing ? existing.jawaban_text : "",
                    jawaban_number: existing ? existing.jawaban_number : null,
                    keterangan: existing ? existing.keterangan : "",
                };
            });
        });
    });

    let form = useForm(
        untrack(() => ({
            status: asesmen.status || "draft",
            tanggal_asesmen: asesmen.tanggal_asesmen || new Date().toISOString().split("T")[0],

            // -------------------- IDENTITAS KLIEN & KELUARGA --------------------
            pendaftaran: {
                nik: pendaftaran.nik || "",
                nama: pendaftaran.nama || "",
                tempat_lahir: pendaftaran.tempat_lahir || "",
                tanggal_lahir: pendaftaran.tanggal_lahir || "",
                jenis_kelamin: pendaftaran.jenis_kelamin || "",
                agama: pendaftaran.agama || "",
                alamat_asal: pendaftaran.alamat_asal || "",
                pendidikan: pendaftaran.pendidikan || "",
                status_perkawinan: pendaftaran.status_perkawinan || "",
                tinggi_badan: pendaftaran.tinggi_badan || "",
                berat_badan: pendaftaran.berat_badan || "",
                bentuk_keadaan_mata: pendaftaran.bentuk_keadaan_mata || "",
                pendengaran: pendaftaran.pendengaran || "",
                golongan_darah: pendaftaran.golongan_darah || "",
                nomor_induk_klien: pendaftaran.nomor_induk_klien || "",
                disabilitas_netra_sejak: pendaftaran.disabilitas_netra_sejak || "",
                penyebab_disabilitas: pendaftaran.penyebab_disabilitas || "",
                tempat_cacat: pendaftaran.tempat_cacat || "",
                pengaruh_cacat_tingkah_laku: pendaftaran.pengaruh_cacat_tingkah_laku || "",
                dirawat_di: pendaftaran.dirawat_di || "",
                gradasi_kecacatan: pendaftaran.gradasi_kecacatan || "",
            },

            wali: {
                nik: pendaftaran.wali?.nik || "",
                nama: pendaftaran.wali?.user?.name || "",
                tempat_lahir: pendaftaran.wali?.tempat_lahir || "",
                tanggal_lahir: pendaftaran.wali?.tanggal_lahir || "",
                alamat: pendaftaran.wali?.alamat || "",
                nomor_hp: pendaftaran.wali?.nomor_hp || "",
                agama: pendaftaran.wali?.agama || "",
                pendidikan_terakhir: pendaftaran.wali?.pendidikan_terakhir || "",
                status_dalam_keluarga: pendaftaran.wali?.status_dalam_keluarga || "",
                pekerjaan: pendaftaran.wali?.pekerjaan || "",
                penghasilan_perbulan: pendaftaran.wali?.penghasilan_perbulan || "",
                tanggungan_keluarga: pendaftaran.wali?.tanggungan_keluarga || "",
                rumah_tempat_tinggal: pendaftaran.wali?.rumah_tempat_tinggal || "",
                keadaan_lingkungan: pendaftaran.wali?.keadaan_lingkungan || "",
            },

            anggota_keluarga: pendaftaran.wali?.anggota_keluarga?.length ? 
                pendaftaran.wali.anggota_keluarga.map((ak: any) => ({
                    nama: ak.nama,
                    status: ak.status,
                    tanggal_lahir: ak.tanggal_lahir || "",
                    pendidikan: ak.pendidikan || "",
                    pekerjaan: ak.pekerjaan || ""
                })) : 
                [
                    { nama: "", status: "", tanggal_lahir: "", pendidikan: "", pekerjaan: "" }
                ],

            // -------------------- PENDIDIKAN & KETERAMPILAN --------------------
            pendidikan_tk: asesmen.pendidikan_tk || "",
            pendidikan_sd: asesmen.pendidikan_sd || "",
            pendidikan_smp: asesmen.pendidikan_smp || "",
            pendidikan_sma: asesmen.pendidikan_sma || "",
            pendidikan_pt: asesmen.pendidikan_pt || "",
            kursus_nama: asesmen.kursus_nama || "",
            kursus_diberikan_oleh: asesmen.kursus_diberikan_oleh || "",
            kursus_tempat: asesmen.kursus_tempat || "",
            kursus_lama: asesmen.kursus_lama || "",

            // -------------------- KONDISI MENTAL/EMOSIONAL --------------------
            sikap_klien: asesmen.sikap_klien || "",
            sikap_keluarga: asesmen.sikap_keluarga || "",
            sikap_masyarakat: asesmen.sikap_masyarakat || "",
            keluhan_hambatan: asesmen.keluhan_hambatan || "",

            // -------------------- MASALAH KELAHIRAN --------------------
            bayi_kuning: asesmen.bayi_kuning ?? null,
            bayi_kuning_penanganan: asesmen.bayi_kuning_penanganan || "",
            bayi_kuning_uv_hari: asesmen.bayi_kuning_uv_hari || "",
            bayi_hisap_asi: asesmen.bayi_hisap_asi ?? null,
            lama_asi_bulan: asesmen.lama_asi_bulan || "",
            lama_asi_lainnya: asesmen.lama_asi_lainnya || "",
            masalah_kejang_stuip: asesmen.masalah_kejang_stuip ?? null,
            masalah_kejang_stuip_ket: asesmen.masalah_kejang_stuip_ket || "",
            masalah_diare: asesmen.masalah_diare ?? null,
            masalah_diare_ket: asesmen.masalah_diare_ket || "",
            masalah_lainnya: asesmen.masalah_lainnya ?? null,
            masalah_lainnya_ket: asesmen.masalah_lainnya_ket || "",

            // -------------------- LAIN-LAIN CHECKBOX --------------------
            tujuan_bimbingan_keterampilan: asesmen.tujuan_bimbingan_keterampilan || false,
            tujuan_bimbingan_sekolah: asesmen.tujuan_bimbingan_sekolah || false,
            keterampilan_praktis_produktif: asesmen.keterampilan_praktis_produktif || false,
            keterampilan_massage_pijat: asesmen.keterampilan_massage_pijat || false,
            info_dokter_rs: asesmen.info_dokter_rs || false,
            info_petugas_sosial_kec: asesmen.info_petugas_sosial_kec || false,
            info_dinas_sosial: asesmen.info_dinas_sosial || false,
            info_psm: asesmen.info_psm || false,
            info_teman: asesmen.info_teman || false,

            // -------------------- KESIMPULAN --------------------
            kesimpulan_latar_belakang: asesmen.kesimpulan_latar_belakang || "",
            kesimpulan_pengaruh_kecacatan: asesmen.kesimpulan_pengaruh_kecacatan || "",
            kesimpulan_keinginan_harapan: asesmen.kesimpulan_keinginan_harapan || "",
            kesimpulan_bentuk_bantuan: asesmen.kesimpulan_bentuk_bantuan || "",

            // -------------------- SIFAT BALITA --------------------
            hal_hal_menyolok: asesmen.hal_hal_menyolok || [],
            catatan_tambahan_balita: asesmen.catatan_tambahan_balita || "",
            pendapat_sekolah: asesmen.pendapat_sekolah || "",

            // -------------------- DYNAMIC QUESTIONS --------------------
            jawaban: initialJawaban,
        }))
    );

    function addAnggotaKeluarga() {
        form.anggota_keluarga = [...form.anggota_keluarga, { nama: "", status: "", tanggal_lahir: "", pendidikan: "", pekerjaan: "" }];
    }

    function removeAnggotaKeluarga(index: number) {
        form.anggota_keluarga = form.anggota_keluarga.filter((_: any, i: number) => i !== index);
    }

    const sifatMenyolokOptions = [
        "Kidal", "Mengisap jempol/jari", "Menggigit-gigit kuku",
        "Pendengaran kurang baik", "Penglihatan kurang baik", "Sangat banyak bergerak/aktif",
        "Berani", "Kurang percaya diri", "Agresif",
        "Berkacamata", "Perengek/cengeng", "Tidak patuh", "Masih senang bermain-main",
        "Pelamun", "Pemalas", "Jorok", "Sakitan", "Kurang semangat", "Semangat", "Sabar",
        "Sangat lambat", "Penakut", "Cepat lelah", "Lalai", "Sulit diatur", "Gangguan berbicara",
        "Gagap", "Keras kepala", "Ganguan dalam motoric", "Kurang konsentrasi", "Mudah mengalah",
        "Suka jahil", "Pemalu", "Suka teriak", "Mudah tersinggung", "Ramah/mudah tersenyum",
        "Pendiam", "Cemas", "Suka tegang", "Pelit", "Kurang tanggung jawab", "Sopan"
    ];

    function toggleMenyolok(option: string) {
        if (form.hal_hal_menyolok.includes(option)) {
            form.hal_hal_menyolok = form.hal_hal_menyolok.filter((o: string) => o !== option);
        } else {
            form.hal_hal_menyolok = [...form.hal_hal_menyolok, option];
        }
    }

    function submit(status: "draft" | "selesai") {
        form.status = status;
        form.post(`/pekerja-sosial/asesmen/${pendaftaran.id}`);
    }

    const steps = [
        "1. Identitas Klien & Keluarga",
        "2. Pendidikan & Latar Belakang",
        "3. Kuesioner Asesmen",
        "4. Kondisi Mental & Kesimpulan"
    ];
</script>

<AppHead title="Form Asesmen Klien" />

<div class="p-6 w-full max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Form Asesmen Klien</h1>
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium">Status saat ini: {asesmen.status?.toUpperCase() || 'DRAFT'}</span>
        </div>
    </div>

    <!-- Stepper Navigation -->
    <div class="flex gap-2 p-2 bg-muted rounded-xl">
        {#each steps as step, index}
            <button
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-colors
                {currentStep === index + 1 ? 'bg-background shadow-sm text-foreground' : 'text-muted-foreground hover:bg-muted/80'}"
                onclick={() => currentStep = index + 1}
            >
                {step}
            </button>
        {/each}
    </div>

    <form onsubmit={(e) => { e.preventDefault(); submit('draft'); }} class="space-y-6">
        
        <!-- STEP 1: IDENTITAS -->
        {#if currentStep === 1}
            <div class="space-y-6">
                <Card>
                    <CardHeader><CardTitle>Identitas Klien</CardTitle></CardHeader>
                    <CardContent class="grid grid-cols-2 gap-4">
                        <div class="space-y-2"><Label>Nama Lengkap</Label><Input bind:value={form.pendaftaran.nama} /></div>
                        <div class="space-y-2"><Label>Nomor Induk Klien</Label><Input bind:value={form.pendaftaran.nomor_induk_klien} /></div>
                        <div class="space-y-2"><Label>NIK (KTP)</Label><Input bind:value={form.pendaftaran.nik} /></div>
                        <div class="space-y-2"><Label>Jenis Kelamin</Label><Input bind:value={form.pendaftaran.jenis_kelamin} /></div>
                        <div class="space-y-2"><Label>Tempat Lahir</Label><Input bind:value={form.pendaftaran.tempat_lahir} /></div>
                        <div class="space-y-2"><Label>Tanggal Lahir</Label><Input type="date" bind:value={form.pendaftaran.tanggal_lahir} /></div>
                        <div class="space-y-2"><Label>Agama</Label><Input bind:value={form.pendaftaran.agama} /></div>
                        <div class="space-y-2"><Label>Pendidikan Terakhir</Label><Input bind:value={form.pendaftaran.pendidikan} /></div>
                        <div class="space-y-2"><Label>Status Perkawinan</Label><Input bind:value={form.pendaftaran.status_perkawinan} /></div>
                        <div class="space-y-2"><Label>Tinggi Badan (cm)</Label><Input bind:value={form.pendaftaran.tinggi_badan} /></div>
                        <div class="space-y-2"><Label>Berat Badan (kg)</Label><Input bind:value={form.pendaftaran.berat_badan} /></div>
                        <div class="space-y-2"><Label>Bentuk / Keadaan Mata</Label><Input bind:value={form.pendaftaran.bentuk_keadaan_mata} /></div>
                        <div class="space-y-2"><Label>Pendengaran</Label><Input bind:value={form.pendaftaran.pendengaran} /></div>
                        <div class="space-y-2"><Label>Golongan Darah</Label><Input bind:value={form.pendaftaran.golongan_darah} /></div>
                        <div class="col-span-2 space-y-2"><Label>Alamat Lengkap</Label><Textarea bind:value={form.pendaftaran.alamat_asal} /></div>
                        
                        <div class="col-span-2 mt-4"><h3 class="font-semibold border-b pb-2">Informasi Disabilitas</h3></div>
                        <div class="space-y-2"><Label>Disabilitas Netra Sejak</Label><Input bind:value={form.pendaftaran.disabilitas_netra_sejak} /></div>
                        <div class="space-y-2"><Label>Penyebab Disabilitas</Label><Input bind:value={form.pendaftaran.penyebab_disabilitas} /></div>
                        <div class="space-y-2"><Label>Tempat Cacat (Bagian Tubuh)</Label><Input bind:value={form.pendaftaran.tempat_cacat} /></div>
                        <div class="space-y-2"><Label>Pengaruh Cacat terhadap Tingkah Laku</Label><Input bind:value={form.pendaftaran.pengaruh_cacat_tingkah_laku} /></div>
                        <div class="space-y-2"><Label>Gradasi Kecacatan</Label><Input bind:value={form.pendaftaran.gradasi_kecacatan} /></div>
                        <div class="space-y-2"><Label>Pernah Dirawat Di</Label><Input bind:value={form.pendaftaran.dirawat_di} /></div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader><CardTitle>Identitas Wali / Penanggung Jawab</CardTitle></CardHeader>
                    <CardContent class="grid grid-cols-2 gap-4">
                        <div class="space-y-2"><Label>Nama Lengkap</Label><Input bind:value={form.wali.nama} /></div>
                        <div class="space-y-2"><Label>Nomor HP</Label><Input bind:value={form.wali.nomor_hp} /></div>
                        <div class="space-y-2"><Label>Tempat Lahir</Label><Input bind:value={form.wali.tempat_lahir} /></div>
                        <div class="space-y-2"><Label>Tanggal Lahir</Label><Input type="date" bind:value={form.wali.tanggal_lahir} /></div>
                        <div class="space-y-2"><Label>Agama</Label><Input bind:value={form.wali.agama} /></div>
                        <div class="space-y-2"><Label>Pendidikan Terakhir</Label><Input bind:value={form.wali.pendidikan_terakhir} /></div>
                        <div class="space-y-2"><Label>Pekerjaan</Label><Input bind:value={form.wali.pekerjaan} /></div>
                        <div class="space-y-2"><Label>Penghasilan Per Bulan</Label><Input bind:value={form.wali.penghasilan_perbulan} /></div>
                        <div class="space-y-2"><Label>Status Dalam Keluarga</Label><Input bind:value={form.wali.status_dalam_keluarga} /></div>
                        <div class="space-y-2"><Label>Tanggungan Keluarga (orang)</Label><Input type="number" bind:value={form.wali.tanggungan_keluarga} /></div>
                        <div class="space-y-2"><Label>Rumah Tempat Tinggal</Label><Input bind:value={form.wali.rumah_tempat_tinggal} placeholder="Milik Sendiri/Sewa/Numpang" /></div>
                        <div class="space-y-2"><Label>Keadaan Lingkungan</Label><Input bind:value={form.wali.keadaan_lingkungan} /></div>
                        <div class="col-span-2 space-y-2"><Label>Alamat Lengkap</Label><Textarea bind:value={form.wali.alamat} /></div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Susunan Anggota Keluarga</CardTitle>
                        <CardDescription>Tambahkan semua anggota keluarga yang tinggal dalam satu rumah tangga.</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        {#each form.anggota_keluarga as ak, i}
                            <div class="flex gap-2 items-end">
                                <div class="space-y-2 flex-1"><Label>Nama</Label><Input bind:value={ak.nama} /></div>
                                <div class="space-y-2 flex-1"><Label>Status</Label><Input bind:value={ak.status} placeholder="Anak/Istri/Suami" /></div>
                                <div class="space-y-2 flex-1"><Label>Tanggal Lahir</Label><Input type="date" bind:value={ak.tanggal_lahir} /></div>
                                <div class="space-y-2 flex-1"><Label>Pendidikan</Label><Input bind:value={ak.pendidikan} /></div>
                                <div class="space-y-2 flex-1"><Label>Pekerjaan</Label><Input bind:value={ak.pekerjaan} /></div>
                                <Button type="button" variant="destructive" size="icon" onclick={() => removeAnggotaKeluarga(i)}>
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                            </div>
                        {/each}
                        <Button type="button" variant="outline" class="w-full mt-2" onclick={addAnggotaKeluarga}>
                            <Plus class="w-4 h-4 mr-2" /> Tambah Anggota Keluarga
                        </Button>
                    </CardContent>
                </Card>
            </div>
        {/if}

        <!-- STEP 2: PENDIDIKAN -->
        {#if currentStep === 2}
            <Card>
                <CardHeader><CardTitle>Latar Belakang Pendidikan</CardTitle></CardHeader>
                <CardContent class="space-y-4">
                    <h3 class="font-semibold">Formal</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2"><Label>Taman Kanak-Kanak</Label><Input bind:value={form.pendidikan_tk} /></div>
                        <div class="space-y-2"><Label>Sekolah Dasar</Label><Input bind:value={form.pendidikan_sd} /></div>
                        <div class="space-y-2"><Label>Sekolah Menengah Pertama</Label><Input bind:value={form.pendidikan_smp} /></div>
                        <div class="space-y-2"><Label>Sekolah Menengah Atas</Label><Input bind:value={form.pendidikan_sma} /></div>
                        <div class="space-y-2"><Label>Perguruan Tinggi</Label><Input bind:value={form.pendidikan_pt} /></div>
                    </div>

                    <h3 class="font-semibold pt-4">Non-Formal (Kursus / Keterampilan)</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2"><Label>Pernah mengikuti kursus/keterampilan</Label><Input bind:value={form.kursus_nama} /></div>
                        <div class="space-y-2"><Label>Diberikan oleh</Label><Input bind:value={form.kursus_diberikan_oleh} /></div>
                        <div class="space-y-2"><Label>Tempat</Label><Input bind:value={form.kursus_tempat} /></div>
                        <div class="space-y-2"><Label>Lama Mengikuti</Label><Input bind:value={form.kursus_lama} /></div>
                    </div>
                </CardContent>
            </Card>
        {/if}

        <!-- STEP 3: ASESMEN KUESIONER -->
        {#if currentStep === 3}
            <div class="space-y-6">
                {#each seksiMaster as seksi}
                    {#if seksi.kode !== 'masalah_setelah_kelahiran'}
                        <Card>
                            <CardHeader><CardTitle>{seksi.nama}</CardTitle></CardHeader>
                            <CardContent>
                                <table class="w-full text-sm border-collapse">
                                    <thead>
                                        <tr class="border-b text-left">
                                            <th class="py-2 w-10">No</th>
                                            <th class="py-2">Pertanyaan</th>
                                            <th class="py-2 w-32 text-center">Ya / Tidak</th>
                                            <th class="py-2 w-48">Keterangan / Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {#each seksi.pertanyaan as tanya}
                                            <tr class="border-b last:border-0 hover:bg-muted/50">
                                                <td class="py-3 align-top">{tanya.nomor}</td>
                                                <td class="py-3 pr-4 align-top">{tanya.pertanyaan}</td>
                                                <td class="py-3 align-top text-center space-x-2">
                                                    {#if tanya.tipe_jawaban === 'ya_tidak'}
                                                        <label class="inline-flex items-center">
                                                            <input type="radio" class="mr-1" bind:group={form.jawaban[tanya.id].jawaban_ya_tidak} value={true} /> Ya
                                                        </label>
                                                        <label class="inline-flex items-center">
                                                            <input type="radio" class="mr-1" bind:group={form.jawaban[tanya.id].jawaban_ya_tidak} value={false} /> Tidak
                                                        </label>
                                                    {/if}
                                                </td>
                                                <td class="py-2 align-top">
                                                    {#if tanya.tipe_jawaban === 'number'}
                                                        <div class="flex items-center gap-2">
                                                            <Input type="number" bind:value={form.jawaban[tanya.id].jawaban_number} class="w-24 h-8" />
                                                            <span class="text-muted-foreground text-xs">{tanya.satuan || ''}</span>
                                                        </div>
                                                    {:else if tanya.tipe_jawaban === 'text'}
                                                        <Input type="text" class="h-8 text-xs" bind:value={form.jawaban[tanya.id].jawaban_text} />
                                                    {:else}
                                                        <Input type="text" class="h-8 text-xs" placeholder="Keterangan..." bind:value={form.jawaban[tanya.id].keterangan} />
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/each}
                                    </tbody>
                                </table>

                                {#if seksi.kode === 'perkembangan_balita'}
                                    <div class="mt-6 border-t pt-4">
                                        <h4 class="font-semibold mb-2">Hal-hal menyolok (Beri tanda pada ciri-ciri yang tampak)</h4>
                                        <div class="grid grid-cols-3 gap-2">
                                            {#each sifatMenyolokOptions as option}
                                                <label class="flex items-center space-x-2 text-sm cursor-pointer">
                                                    <Checkbox
                                                        checked={form.hal_hal_menyolok.includes(option)}
                                                        onCheckedChange={() => toggleMenyolok(option)}
                                                    />
                                                    <span>{option}</span>
                                                </label>
                                            {/each}
                                        </div>
                                        <div class="mt-4 space-y-2">
                                            <Label>Catatan tambahan</Label>
                                            <Textarea bind:value={form.catatan_tambahan_balita} />
                                        </div>
                                        <div class="mt-4 space-y-2">
                                            <Label>Apakah orang tua berpendapat bahwa anak tersebut sudah mampu untuk sekolah?</Label>
                                            <div class="flex gap-4">
                                                <label class="flex items-center gap-2"><input type="radio" bind:group={form.pendapat_sekolah} value="Ya" /> Ya</label>
                                                <label class="flex items-center gap-2"><input type="radio" bind:group={form.pendapat_sekolah} value="Ragu-ragu" /> Ragu-ragu</label>
                                                <label class="flex items-center gap-2"><input type="radio" bind:group={form.pendapat_sekolah} value="Tidak" /> Tidak</label>
                                            </div>
                                        </div>
                                    </div>
                                {/if}
                            </CardContent>
                        </Card>
                    {:else}
                        <Card>
                            <CardHeader><CardTitle>Masalah Setelah Kelahiran</CardTitle></CardHeader>
                            <CardContent class="space-y-4 text-sm">
                                <div class="grid grid-cols-2 gap-x-8 gap-y-4">
                                    <div class="space-y-2">
                                        <Label>Bayi menderita "kuning"?</Label>
                                        <div class="flex gap-4">
                                            <label class="flex items-center gap-2"><input type="radio" bind:group={form.bayi_kuning} value={true} /> Ya</label>
                                            <label class="flex items-center gap-2"><input type="radio" bind:group={form.bayi_kuning} value={false} /> Tidak</label>
                                        </div>
                                        {#if form.bayi_kuning === true}
                                            <div class="pl-4 mt-2 space-y-2 border-l-2">
                                                <Label>Bila Ya, apakah bayi:</Label>
                                                <div class="flex flex-col gap-2">
                                                    <label class="flex items-center gap-2"><input type="radio" bind:group={form.bayi_kuning_penanganan} value="Dijemur saja" /> Dijemur saja</label>
                                                    <label class="flex items-center gap-2"><input type="radio" bind:group={form.bayi_kuning_penanganan} value="Disinar UV" /> Disinar Ultraviolet selama...
                                                        <Input type="number" bind:value={form.bayi_kuning_uv_hari} class="w-16 h-7" /> hari
                                                    </label>
                                                    <label class="flex items-center gap-2"><input type="radio" bind:group={form.bayi_kuning_penanganan} value="Transfusi darah" /> Harus dilakukan transfusi darah</label>
                                                </div>
                                            </div>
                                        {/if}
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <Label>Bayi dapat menghisap ASI dengan kuat?</Label>
                                        <div class="flex gap-4">
                                            <label class="flex items-center gap-2"><input type="radio" bind:group={form.bayi_hisap_asi} value={true} /> Ya</label>
                                            <label class="flex items-center gap-2"><input type="radio" bind:group={form.bayi_hisap_asi} value={false} /> Tidak</label>
                                        </div>
                                        <div class="pl-4 mt-2 space-y-2 border-l-2">
                                            <Label>Lamanya pemberian ASI</Label>
                                            <div class="flex flex-col gap-2">
                                                <div class="flex items-center gap-2"><Input type="number" bind:value={form.lama_asi_bulan} class="w-16 h-7" /> Bulan</div>
                                                <div class="flex items-center gap-2">Lainnya: <Input bind:value={form.lama_asi_lainnya} class="h-7 w-48" /></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-span-2 mt-4">
                                        <h4 class="font-semibold mb-2">Masalah yang diderita bayi pada usia 1 bulan pertama</h4>
                                        <div class="grid grid-cols-3 gap-6">
                                            <div class="space-y-2">
                                                <Label>1. Kejang Stuip</Label>
                                                <div class="flex gap-4">
                                                    <label class="flex items-center gap-2"><input type="radio" bind:group={form.masalah_kejang_stuip} value={false} /> Tidak Ada</label>
                                                    <label class="flex items-center gap-2"><input type="radio" bind:group={form.masalah_kejang_stuip} value={true} /> Ada</label>
                                                </div>
                                                {#if form.masalah_kejang_stuip === true}
                                                    <Input placeholder="Jelaskan..." bind:value={form.masalah_kejang_stuip_ket} class="h-8 text-xs" />
                                                {/if}
                                            </div>
                                            <div class="space-y-2">
                                                <Label>2. Diare</Label>
                                                <div class="flex gap-4">
                                                    <label class="flex items-center gap-2"><input type="radio" bind:group={form.masalah_diare} value={false} /> Tidak Ada</label>
                                                    <label class="flex items-center gap-2"><input type="radio" bind:group={form.masalah_diare} value={true} /> Ada</label>
                                                </div>
                                                {#if form.masalah_diare === true}
                                                    <Input placeholder="Jelaskan..." bind:value={form.masalah_diare_ket} class="h-8 text-xs" />
                                                {/if}
                                            </div>
                                            <div class="space-y-2">
                                                <Label>3. Lainnya</Label>
                                                <div class="flex gap-4">
                                                    <label class="flex items-center gap-2"><input type="radio" bind:group={form.masalah_lainnya} value={false} /> Tidak Ada</label>
                                                    <label class="flex items-center gap-2"><input type="radio" bind:group={form.masalah_lainnya} value={true} /> Ada</label>
                                                </div>
                                                {#if form.masalah_lainnya === true}
                                                    <Input placeholder="Jelaskan..." bind:value={form.masalah_lainnya_ket} class="h-8 text-xs" />
                                                {/if}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    {/if}
                {/each}
            </div>
        {/if}

        <!-- STEP 4: MENTAL & KESIMPULAN -->
        {#if currentStep === 4}
            <div class="space-y-6">
                <Card>
                    <CardHeader><CardTitle>Sikap Emosional / Mental</CardTitle></CardHeader>
                    <CardContent class="grid grid-cols-2 gap-4">
                        <div class="space-y-2"><Label>Sikap Klien</Label><Textarea bind:value={form.sikap_klien} /></div>
                        <div class="space-y-2"><Label>Sikap Keluarga</Label><Textarea bind:value={form.sikap_keluarga} /></div>
                        <div class="space-y-2"><Label>Sikap Masyarakat</Label><Textarea bind:value={form.sikap_masyarakat} /></div>
                        <div class="space-y-2"><Label>Keluhan / Hambatan</Label><Textarea bind:value={form.keluhan_hambatan} /></div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader><CardTitle>Lain-Lain & Kesimpulan</CardTitle></CardHeader>
                    <CardContent class="space-y-6">
                        <div>
                            <h3 class="font-semibold mb-2">Maksud dan Tujuan Bimbingan</h3>
                            <div class="flex gap-6">
                                <label class="flex items-center gap-2">
                                    <Checkbox checked={form.tujuan_bimbingan_keterampilan} onCheckedChange={(v) => form.tujuan_bimbingan_keterampilan = !!v} />
                                    Bimbingan Keterampilan
                                </label>
                                <label class="flex items-center gap-2">
                                    <Checkbox checked={form.tujuan_bimbingan_sekolah} onCheckedChange={(v) => form.tujuan_bimbingan_sekolah = !!v} />
                                    Bimbingan Sekolah
                                </label>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-semibold mb-2">Sumber Informasi PSBN</h3>
                            <div class="flex flex-wrap gap-4">
                                <label class="flex items-center gap-2"><Checkbox checked={form.info_dokter_rs} onCheckedChange={(v) => form.info_dokter_rs = !!v} /> Dokter / Rumah Sakit</label>
                                <label class="flex items-center gap-2"><Checkbox checked={form.info_petugas_sosial_kec} onCheckedChange={(v) => form.info_petugas_sosial_kec = !!v} /> Petugas Sosial Kecamatan</label>
                                <label class="flex items-center gap-2"><Checkbox checked={form.info_dinas_sosial} onCheckedChange={(v) => form.info_dinas_sosial = !!v} /> Dinas Sosial</label>
                                <label class="flex items-center gap-2"><Checkbox checked={form.info_psm} onCheckedChange={(v) => form.info_psm = !!v} /> PSM</label>
                                <label class="flex items-center gap-2"><Checkbox checked={form.info_teman} onCheckedChange={(v) => form.info_teman = !!v} /> Teman</label>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-semibold mb-2">Kesimpulan</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2"><Label>Latar Belakang</Label><Textarea bind:value={form.kesimpulan_latar_belakang} /></div>
                                <div class="space-y-2"><Label>Pengaruh Kecacatan</Label><Textarea bind:value={form.kesimpulan_pengaruh_kecacatan} /></div>
                                <div class="space-y-2"><Label>Keinginan & Harapan</Label><Textarea bind:value={form.kesimpulan_keinginan_harapan} /></div>
                                <div class="space-y-2"><Label>Bentuk Bantuan yang disarankan</Label><Textarea bind:value={form.kesimpulan_bentuk_bantuan} /></div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        {/if}

        <div class="flex justify-between gap-4 pb-10 mt-6 border-t pt-6">
            <div>
                {#if currentStep > 1}
                    <Button type="button" variant="outline" onclick={() => currentStep--}>
                        &larr; Sebelumnya
                    </Button>
                {/if}
            </div>
            
            <div class="flex gap-2">
                <Button type="button" variant="outline" onclick={() => submit('draft')} disabled={form.processing}>
                    Simpan Draft
                </Button>
                
                {#if currentStep < totalSteps}
                    <Button type="button" onclick={() => currentStep++}>
                        Selanjutnya &rarr;
                    </Button>
                {:else}
                    <Button type="button" onclick={() => submit('selesai')} disabled={form.processing}>
                        Selesaikan Asesmen
                    </Button>
                {/if}
            </div>
        </div>
    </form>
</div>
