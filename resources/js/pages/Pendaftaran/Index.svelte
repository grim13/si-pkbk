<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: "Pendaftaran",
                href: "/pendaftaran",
            },
        ],
    };
</script>

<script lang="ts">
    import AppHead from "@/components/AppHead.svelte";
    import { useForm, router } from "@inertiajs/svelte";
    import { untrack } from "svelte";
    
    import Step1Persyaratan from "./Steps/Step1Persyaratan.svelte";
    import Step2DataKlien from "./Steps/Step2DataKlien.svelte";
    import Step3UploadDokumen from "./Steps/Step3UploadDokumen.svelte";
    import Step4Review from "./Steps/Step4Review.svelte";
    import Step5Status from "./Steps/Step5Status.svelte";

    let { persyaratan, jenis_berkas, pendaftaran, errors } = $props();

    // Determine initial step based on pendaftaran status
    let initialStep = untrack(() => {
        if (pendaftaran) {
            if (pendaftaran.status_pendaftaran !== 'draft') {
                return 5; // Submitted status screen
            } else {
                return 2; // Start from 2 if draft exists
            }
        }
        return 1;
    });

    let step = $state(initialStep);

    let formStep2 = useForm(untrack(() => ({
        nik: pendaftaran?.nik || '',
        nama: pendaftaran?.nama || '',
        tempat_lahir: pendaftaran?.tempat_lahir || '',
        tanggal_lahir: pendaftaran?.tanggal_lahir || '',
        alamat_asal: pendaftaran?.alamat_asal || '',
        pendidikan: pendaftaran?.pendidikan || '',
        keterampilan_yang_diminati: pendaftaran?.keterampilan_yang_diminati || '',
    })));

    function submitStep2() {
        formStep2.post('/pendaftaran/step2', {
            preserveScroll: true,
            onSuccess: () => { step = 3; }
        });
    }

    let formStep3 = useForm({
        berkas: {}
    });

    function submitStep3() {
        formStep3.post('/pendaftaran/step3', {
            preserveScroll: true,
            onSuccess: () => { step = 4; }
        });
    }

    let finalizing = $state(false);
    function submitStep4() {
        finalizing = true;
        router.post('/pendaftaran/finalize', {}, {
            preserveScroll: true,
            onSuccess: () => { step = 5; },
            onFinish: () => { finalizing = false; }
        });
    }
</script>

<AppHead title="Pendaftaran" />

<div class="container mx-auto py-8 px-4 max-w-4xl">
    <!-- Wizard Progress -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold {step >= 1 ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'}">1</div>
                <span class="text-sm mt-2 font-medium">Persyaratan</span>
            </div>
            <div class="flex-1 h-1 mx-4 {step >= 2 ? 'bg-primary' : 'bg-muted'}"></div>
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold {step >= 2 ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'}">2</div>
                <span class="text-sm mt-2 font-medium">Data Klien</span>
            </div>
            <div class="flex-1 h-1 mx-4 {step >= 3 ? 'bg-primary' : 'bg-muted'}"></div>
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold {step >= 3 ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'}">3</div>
                <span class="text-sm mt-2 font-medium">Dokumen</span>
            </div>
            <div class="flex-1 h-1 mx-4 {step >= 4 ? 'bg-primary' : 'bg-muted'}"></div>
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold {step >= 4 ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'}">4</div>
                <span class="text-sm mt-2 font-medium">Selesai</span>
            </div>
        </div>
    </div>

    {#if step === 1}
        <Step1Persyaratan 
            {persyaratan} 
            onNext={() => step = 2} 
        />
    {/if}

    {#if step === 2}
        <Step2DataKlien 
            {formStep2} 
            onNext={submitStep2} 
            onBack={() => step = 1} 
        />
    {/if}

    {#if step === 3}
        <Step3UploadDokumen 
            {jenis_berkas} 
            {pendaftaran} 
            {formStep3} 
            onNext={submitStep3} 
            onBack={() => step = 2} 
        />
    {/if}

    {#if step === 4}
        <Step4Review 
            {pendaftaran} 
            {errors} 
            {finalizing} 
            onNext={submitStep4} 
            onBack={() => step = 3} 
        />
    {/if}

    {#if step === 5}
        <Step5Status 
            {pendaftaran} 
            {jenis_berkas}
            onFixDocs={() => step = 3} 
        />
    {/if}
</div>
