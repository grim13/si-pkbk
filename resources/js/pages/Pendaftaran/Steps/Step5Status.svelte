<script lang="ts">
    import { Button } from "@/components/ui/button";
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from "@/components/ui/card";

    let { pendaftaran, jenis_berkas, onFixDocs } = $props();

    function formatDate(dateStr) {
        if (!dateStr) return "-";
        const d = new Date(dateStr);
        return (
            d.toLocaleDateString("id-ID", {
                weekday: "long",
                year: "numeric",
                month: "long",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            }) + " WIB"
        );
    }
</script>

{#if pendaftaran}
    <Card>
        {#if pendaftaran.status_pendaftaran === "verifikasi_berkas"}
            <CardHeader>
                <CardTitle class="text-center text-blue-600 text-2xl mb-2"
                    >Pendaftaran Berhasil Dikirim!</CardTitle
                >
            </CardHeader>
            <CardContent class="text-center">
                <p class="mb-4">
                    Data pendaftaran atas nama <strong
                        >{pendaftaran.nama}</strong
                    >
                    sedang dalam tahap <strong>Verifikasi Berkas</strong>.
                </p>
                <p class="text-muted-foreground">
                    Admin kami sedang memeriksa dokumen yang Anda unggah. Anda
                    akan mendapatkan pemberitahuan lebih lanjut di sini setelah
                    proses verifikasi selesai.
                </p>
            </CardContent>
        {:else if pendaftaran.status_pendaftaran === "perbaikan_berkas"}
            <CardHeader>
                <CardTitle class="text-center text-orange-600 text-2xl mb-2"
                    >Perbaikan Berkas Diperlukan</CardTitle
                >
            </CardHeader>
            <CardContent class="text-center space-y-4">
                <p>
                    Beberapa dokumen pendaftaran atas nama <strong
                        >{pendaftaran.nama}</strong
                    > memerlukan perbaikan.
                </p>
                <p class="text-muted-foreground">
                    Silakan periksa kembali berkas Anda dan unggah ulang dokumen
                    yang sesuai dengan catatan dari admin.
                </p>
                <div class="mt-6">
                    <Button
                        onclick={onFixDocs}
                        size="lg"
                        class="w-full sm:w-auto"
                    >
                        Mulai Perbaikan Berkas
                    </Button>
                </div>
            </CardContent>
        {:else if pendaftaran.status_pendaftaran === "asesmen"}
            <CardHeader>
                <CardTitle class="text-center text-indigo-600 text-2xl mb-2"
                    >Jadwal Asesmen</CardTitle
                >
            </CardHeader>
            <CardContent>
                <div class="text-center mb-6">
                    <p class="mb-4">
                        Berkas Anda telah valid. Data pendaftaran atas nama <strong
                            >{pendaftaran.nama}</strong
                        >
                        kini masuk ke tahap <strong>Asesmen</strong>.
                    </p>
                    <p class="text-muted-foreground">
                        Silakan datang ke panti dengan membawa seluruh berkas
                        asli (rangkap 2) untuk melakukan tahap wawancara
                        asesmen.
                    </p>
                </div>

                <div
                    class="bg-indigo-50 border border-indigo-100 rounded-md p-6 text-center mb-8"
                >
                    <h4 class="font-bold text-indigo-800 mb-2">
                        Jadwal & Tempat Asesmen
                    </h4>
                    <p class="text-lg font-semibold">
                        {formatDate(pendaftaran.jadwal_asesmen)}
                    </p>
                    <p class="text-indigo-600 mt-1 font-medium">
                        {pendaftaran.tempat_asesmen}
                    </p>
                    <p class="text-danger mt-1 font-medium">
                        Bawa semua berkas rangkap 2
                    </p>
                </div>

                <div class="bg-slate-50 border rounded-lg p-6 mb-8 text-left">
                    <h3 class="text-lg font-bold mb-4 border-b pb-2">
                        Detail Data Pendaftaran
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-muted-foreground block text-xs"
                                >No. Pendaftaran</span
                            >
                            <span class="font-medium"
                                >{pendaftaran.no_pendaftaran}</span
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
                        <div class="md:col-span-2">
                            <span class="text-muted-foreground block text-xs"
                                >Keterampilan yang Diminati</span
                            >
                            <span class="font-medium"
                                >{pendaftaran.keterampilan_yang_diminati}</span
                            >
                        </div>
                    </div>
                </div>

                <div class="text-left">
                    <h3 class="text-lg font-bold mb-4 border-b pb-2">
                        Dokumen Terlampir
                    </h3>
                    <div class="space-y-3 text-sm">
                        {#each jenis_berkas as jenis}
                            {@const file = pendaftaran.berkas?.find(
                                (b) =>
                                    b.jenis_berkas_pendaftaran_id === jenis.id,
                            )}
                            <div
                                class="flex justify-between items-center p-3 border rounded-md {file
                                    ? 'bg-green-50/30 border-green-100'
                                    : 'bg-gray-50'}"
                            >
                                <span class="font-medium"
                                    >{jenis.nama_berkas}</span
                                >
                                {#if file}
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded-md font-semibold"
                                            >Valid</span
                                        >
                                        <a
                                            href={`/storage/${file.file_path}`}
                                            target="_blank"
                                            rel="noreferrer"
                                            class="text-blue-600 hover:underline text-xs font-medium flex items-center gap-1"
                                        >
                                            Lihat File
                                        </a>
                                    </div>
                                {:else}
                                    <span class="text-red-500 text-xs"
                                        >Belum Diunggah</span
                                    >
                                {/if}
                            </div>
                        {/each}
                    </div>
                </div>
            </CardContent>
        {:else if pendaftaran.status_pendaftaran === "diterima"}
            <CardHeader>
                <CardTitle class="text-center text-green-600 text-2xl mb-2"
                    >Selamat, Diterima!</CardTitle
                >
            </CardHeader>
            <CardContent class="text-center">
                <p class="mb-4">
                    Peserta atas nama <strong>{pendaftaran.nama}</strong> telah
                    <strong>Lolos dan Diterima</strong> sebagai Klien Panti.
                </p>
                <p class="text-muted-foreground">
                    Silakan menunggu arahan lebih lanjut atau hubungi pihak
                    panti terkait jadwal kedatangan dan asrama.
                </p>
            </CardContent>
        {:else if pendaftaran.status_pendaftaran === "ditolak"}
            <CardHeader>
                <CardTitle class="text-center text-red-600 text-2xl mb-2"
                    >Mohon Maaf, Pendaftaran Ditolak</CardTitle
                >
            </CardHeader>
            <CardContent class="text-center">
                <p class="mb-4">
                    Pendaftaran peserta atas nama <strong
                        >{pendaftaran.nama}</strong
                    >
                    saat ini <strong>Tidak Lolos (Ditolak)</strong>.
                </p>
                <p class="text-muted-foreground">
                    Terima kasih telah mendaftar. Keputusan ini mutlak dari
                    hasil asesmen dan kuota panti.
                </p>
            </CardContent>
        {/if}
    </Card>
{/if}
