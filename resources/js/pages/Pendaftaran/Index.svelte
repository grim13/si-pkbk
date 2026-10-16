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
    import { Button } from "@/components/ui/button";
    import Plus from "@lucide/svelte/icons/plus";

    let {
        persyaratan,
        jenis_berkas,
        pendaftaran,
        pendaftaran_list = [],
        is_creating_new = false,
        errors,
    } = $props();

    function computeStep(current: any, creatingNew: boolean) {
        if (creatingNew || !current) {
            return 1;
        }
        if (current.status_pendaftaran !== "draft") {
            return 5;
        }
        return 2;
    }

    let step = $state(untrack(() => computeStep(pendaftaran, is_creating_new)));
    let trackedKey = $state(
        untrack(() => (is_creating_new ? "new" : String(pendaftaran?.id ?? "none"))),
    );

    let formStep2 = useForm(
        untrack(() => ({
            pendaftaran_id: pendaftaran?.id ?? null,
            nik: pendaftaran?.nik || "",
            nama: pendaftaran?.nama || "",
            tempat_lahir: pendaftaran?.tempat_lahir || "",
            tanggal_lahir: pendaftaran?.tanggal_lahir || "",
            alamat_asal: pendaftaran?.alamat_asal || "",
            pendidikan: pendaftaran?.pendidikan || "",
            keterampilan_yang_diminati:
                pendaftaran?.keterampilan_yang_diminati || "",
        })),
    );

    let formStep3 = useForm(
        untrack(() => ({
            pendaftaran_id: pendaftaran?.id ?? null,
            berkas: {} as Record<number, File>,
        })),
    );

    $effect(() => {
        const nextKey = is_creating_new
            ? "new"
            : String(pendaftaran?.id ?? "none");
        formStep2.pendaftaran_id = pendaftaran?.id ?? null;
        formStep3.pendaftaran_id = pendaftaran?.id ?? null;

        if (nextKey !== trackedKey) {
            const wasCreatingNew = trackedKey === "new" || trackedKey === "none";
            trackedKey = nextKey;

            formStep2.nik = pendaftaran?.nik || "";
            formStep2.nama = pendaftaran?.nama || "";
            formStep2.tempat_lahir = pendaftaran?.tempat_lahir || "";
            formStep2.tanggal_lahir = pendaftaran?.tanggal_lahir || "";
            formStep2.alamat_asal = pendaftaran?.alamat_asal || "";
            formStep2.pendidikan = pendaftaran?.pendidikan || "";
            formStep2.keterampilan_yang_diminati =
                pendaftaran?.keterampilan_yang_diminati || "";
            formStep2.clearErrors();

            formStep3.berkas = {};
            formStep3.clearErrors();

            if (!(wasCreatingNew && pendaftaran?.status_pendaftaran === "draft" && step === 3)) {
                step = computeStep(pendaftaran, is_creating_new);
            }
        }
    });

    function selectPendaftaran(id: number) {
        router.get("/pendaftaran", { pendaftaran_id: id });
    }

    function startNewPendaftaran() {
        router.get("/pendaftaran", { baru: 1 });
    }

    function formatStatusBadge(status: string) {
        if (!status) return "-";
        return status.replaceAll("_", " ").toUpperCase();
    }

    function submitStep2() {
        formStep2.pendaftaran_id = pendaftaran?.id ?? null;
        formStep2.post("/pendaftaran/step2", {
            preserveScroll: true,
            onSuccess: () => {
                step = 3;
            },
        });
    }

    function submitStep3() {
        formStep3.pendaftaran_id = pendaftaran?.id ?? null;
        formStep3.post("/pendaftaran/step3", {
            preserveScroll: true,
            onSuccess: () => {
                step = 4;
            },
        });
    }

    let finalizing = $state(false);
    function submitStep4() {
        finalizing = true;
        router.post(
            "/pendaftaran/finalize",
            { pendaftaran_id: pendaftaran?.id ?? null },
            {
                preserveScroll: true,
                onSuccess: () => {
                    step = 5;
                },
                onFinish: () => {
                    finalizing = false;
                },
            },
        );
    }
</script>

<AppHead title="Pendaftaran" />

<div class="container mx-auto py-8 px-4 max-w-4xl">
    {#if pendaftaran_list && pendaftaran_list.length > 0}
        <div class="mb-6 rounded-xl border bg-card p-4 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <div>
                    <h2 class="text-sm font-semibold">Daftar Klien Terdaftar</h2>
                    <p class="text-xs text-muted-foreground">
                        Pilih klien untuk melihat status pendaftaran atau daftarkan klien baru.
                    </p>
                </div>
                <Button
                    type="button"
                    size="sm"
                    variant={is_creating_new ? "default" : "outline"}
                    onclick={startNewPendaftaran}
                >
                    <Plus class="w-4 h-4 mr-1.5" />
                    Daftarkan Klien Baru
                </Button>
            </div>
            <div class="flex flex-wrap gap-2">
                {#each pendaftaran_list as item (item.id)}
                    {@const isActive = !is_creating_new && pendaftaran?.id === item.id}
                    <button
                        type="button"
                        onclick={() => selectPendaftaran(item.id)}
                        class="flex items-center gap-2 rounded-lg border px-3 py-2 text-left text-xs transition-colors {isActive
                            ? 'border-primary bg-primary/10 text-primary font-semibold'
                            : 'bg-background hover:bg-muted text-foreground'}"
                    >
                        <span class="font-mono font-bold">#{item.no_pendaftaran}</span>
                        <span>{item.nama}</span>
                        <span class="rounded-full bg-muted px-2 py-0.5 text-[10px] font-medium text-muted-foreground">
                            {formatStatusBadge(item.status_pendaftaran)}
                        </span>
                    </button>
                {/each}
            </div>
        </div>
    {/if}

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
