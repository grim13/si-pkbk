<script lang="ts">
    import { Button } from "@/components/ui/button";
    import { Label } from "@/components/ui/label";
    import { Input } from "@/components/ui/input";
    import {
        Card,
        CardContent,
        CardDescription,
        CardFooter,
        CardHeader,
        CardTitle,
    } from "@/components/ui/card";

    let { jenis_berkas, pendaftaran, formStep3, onNext, onBack } = $props();

    function handleFileChange(event, id) {
        formStep3.berkas[id] = event.target.files[0];
    }

    function getUploadedBerkas(jenisId) {
        if (!pendaftaran || !pendaftaran.berkas) return null;
        return pendaftaran.berkas.find(x => x.jenis_berkas_pendaftaran_id === jenisId);
    }
</script>

<Card>
    <CardHeader>
        <CardTitle>Langkah 3: Upload Dokumen Persyaratan</CardTitle>
        <CardDescription>
            Unggah seluruh berkas persyaratan yang dibutuhkan.
        </CardDescription>
    </CardHeader>
    <CardContent>
        {#if formStep3.errors.berkas}
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                {formStep3.errors.berkas}
            </div>
        {/if}
        
        <form onsubmit={(e) => { e.preventDefault(); onNext(); }} class="space-y-6">
            {#each jenis_berkas as jenis}
                {@const uploadedBerkas = getUploadedBerkas(jenis.id)}
                <div class="border p-4 rounded-md">
                    <div class="flex justify-between items-start gap-4">
                        <div>
                            <Label class="text-base font-semibold">
                                {jenis.nama_berkas}
                                {#if jenis.required}
                                    <span class="text-red-500">*</span>
                                {/if}
                            </Label>
                            {#if jenis.keterangan}
                                <p class="text-sm text-muted-foreground mt-1">{jenis.keterangan}</p>
                            {/if}
                            <p class="text-xs text-blue-600 font-medium mt-1">
                                Format diizinkan: {jenis.format_file.replace(/application\/|image\//g, '').toUpperCase()}
                            </p>
                        </div>
                        {#if jenis.template_surat}
                            <a 
                                href="/templates/{jenis.template_surat}" 
                                download 
                                class="shrink-0 text-xs flex items-center gap-1.5 text-primary hover:underline bg-primary/10 px-3 py-1.5 rounded-md font-medium transition-colors hover:bg-primary/20"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                Download Template
                            </a>
                        {/if}
                    </div>
                    <div class="mt-4 flex flex-col space-y-3">
                        <div class="flex items-center space-x-4">
                            <Input 
                                type="file" 
                                accept={jenis.format_file} 
                                disabled={uploadedBerkas?.status === 'diterima'}
                                onchange={(e) => handleFileChange(e, jenis.id)} 
                            />
                            {#if uploadedBerkas}
                                <span class="text-sm text-green-600 font-medium whitespace-nowrap flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    Sudah Diunggah
                                </span>
                            {/if}
                        </div>
                        {#if uploadedBerkas}
                            <div class="flex items-center gap-4 text-sm mt-1">
                                {#if uploadedBerkas.file_path}
                                    <a href={`/storage/${uploadedBerkas.file_path}`} target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline inline-flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>
                                        Lihat Berkas
                                    </a>
                                {/if}
                                {#if uploadedBerkas.status === 'diterima'}
                                    <span class="text-green-600 font-medium px-2 py-1 bg-green-100 rounded-md text-xs">Status: Diterima</span>
                                {:else if uploadedBerkas.status === 'perbaikan'}
                                    <span class="text-red-600 font-medium px-2 py-1 bg-red-100 rounded-md text-xs">Status: Perlu Perbaikan</span>
                                {:else}
                                    <span class="text-yellow-600 font-medium px-2 py-1 bg-yellow-100 rounded-md text-xs">Status: Menunggu Verifikasi</span>
                                {/if}
                            </div>
                            {#if uploadedBerkas.status === 'perbaikan' && uploadedBerkas.keterangan_perbaikan}
                                <div class="mt-2 p-3 bg-red-50 border border-red-200 rounded-md text-sm text-red-800">
                                    <p class="font-semibold mb-1">Catatan Perbaikan:</p>
                                    <p>{uploadedBerkas.keterangan_perbaikan}</p>
                                </div>
                            {/if}
                        {/if}
                    </div>
                </div>
            {/each}
        </form>
    </CardContent>
    <CardFooter class="flex justify-between">
        <Button variant="outline" onclick={onBack}>
            Kembali
        </Button>
        <Button onclick={onNext} disabled={formStep3.processing || Object.keys(formStep3.berkas).length === 0}>
            Upload & Lanjut
        </Button>
    </CardFooter>
</Card>
