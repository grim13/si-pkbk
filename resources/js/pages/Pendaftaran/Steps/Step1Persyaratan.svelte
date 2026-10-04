<script lang="ts">
    import { Button } from "@/components/ui/button";
    import { Checkbox } from "@/components/ui/checkbox";
    import { Label } from "@/components/ui/label";
    import {
        Card,
        CardContent,
        CardDescription,
        CardFooter,
        CardHeader,
        CardTitle,
    } from "@/components/ui/card";

    let { persyaratan, onNext } = $props();
    let agreed = $state(false);
</script>

<Card>
    <CardHeader>
        <CardTitle>Langkah 1: Persyaratan Administrasi</CardTitle>
        <CardDescription>
            Harap baca dan setujui persyaratan berikut sebelum melanjutkan proses pendaftaran.
        </CardDescription>
    </CardHeader>
    <CardContent>
        <div class="bg-white text-black p-8 sm:p-12 rounded-lg mb-6 border shadow-sm max-w-3xl mx-auto font-serif">
            <h2 class="text-center font-bold text-lg sm:text-xl mb-8 uppercase tracking-wide">
                Persyaratan Administrasi Calon Klien
            </h2>
            
            <p class="text-justify indent-8 mb-6 leading-relaxed">
                Panti Sosial Rehabilitasi Penyandang Disabilitas Sensorik (PSR-PDS) merupakan Unit Pelaksana Teknis Dinas yang bertugas memberikan pelayanan rehabilitasi sosial yang meliputi pembinaan fisik, mental, dan pelatihan keterampilan bagi tuna netra agar mampu berperan aktif dalam kehidupan bermasyarakat.
            </p>

            <p class="mb-4">
                Adapun persyaratan administrasi yang harus disiapkan adalah sebagai berikut :
            </p>

            <ol class="list-decimal list-outside ml-8 space-y-2">
                {#each persyaratan as item, index}
                    {#if index !== persyaratan.length}
                        <li class="pl-2 whitespace-pre-line leading-relaxed">{item.deskripsi}</li>
                    {/if}
                {/each}
            </ol>

            <p class="mt-4 leading-relaxed">
                (Semua persyaratan di rangkap 2 dan dibawa pada saat wawancara asesmen)
            </p>
        </div>

        <div class="flex items-center space-x-2 mt-4 justify-center">
            <Checkbox id="terms" bind:checked={agreed} />
            <Label for="terms" class="text-base font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer">
                Saya telah membaca, memahami, dan menyetujui seluruh persyaratan di atas.
            </Label>
        </div>
    </CardContent>
    <CardFooter class="flex justify-end">
        <Button onclick={onNext} disabled={!agreed}>
            Lanjut
        </Button>
    </CardFooter>
</Card>
