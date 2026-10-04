<script lang="ts">
    import { Button } from "@/components/ui/button";
    import {
        Card,
        CardContent,
        CardDescription,
        CardFooter,
        CardHeader,
        CardTitle,
    } from "@/components/ui/card";

    let { pendaftaran, errors, finalizing, onNext, onBack } = $props();
</script>

<Card>
    <CardHeader>
        <CardTitle>Langkah 4: Review Pendaftaran</CardTitle>
        <CardDescription>
            Periksa kembali kelengkapan pendaftaran Anda sebelum dikirim untuk diverifikasi.
        </CardDescription>
    </CardHeader>
    <CardContent>
        {#if errors && errors.berkas}
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                {errors.berkas}
            </div>
        {/if}

        <div class="space-y-4">
            <div>
                <h3 class="font-semibold text-lg border-b pb-2">Data Diri Peserta</h3>
                <div class="mt-4 grid grid-cols-2 gap-y-2 text-sm">
                    <span class="text-muted-foreground">NIK:</span>
                    <span class="font-medium">{pendaftaran?.nik}</span>
                    <span class="text-muted-foreground">Nama Lengkap:</span>
                    <span class="font-medium">{pendaftaran?.nama}</span>
                    <span class="text-muted-foreground">Tempat, Tanggal Lahir:</span>
                    <span class="font-medium">{pendaftaran?.tempat_lahir}, {pendaftaran?.tanggal_lahir}</span>
                    <span class="text-muted-foreground">Alamat Asal:</span>
                    <span class="font-medium">{pendaftaran?.alamat_asal}</span>
                    <span class="text-muted-foreground">Pendidikan:</span>
                    <span class="font-medium">{pendaftaran?.pendidikan}</span>
                    <span class="text-muted-foreground">Keterampilan:</span>
                    <span class="font-medium">{pendaftaran?.keterampilan_yang_diminati}</span>
                </div>
            </div>
        </div>
    </CardContent>
    <CardFooter class="flex justify-between">
        <Button variant="outline" onclick={onBack}>
            Kembali Ke Upload
        </Button>
        <Button onclick={onNext} disabled={finalizing}>
            Finalisasi Pendaftaran
        </Button>
    </CardFooter>
</Card>
