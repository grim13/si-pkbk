<script lang="ts">
    import { Button } from "@/components/ui/button";
    import { Label } from "@/components/ui/label";
    import { Input } from "@/components/ui/input";
    import InputError from "@/components/InputError.svelte";
    import { Textarea } from "@/components/ui/textarea";
    import {
        Card,
        CardContent,
        CardDescription,
        CardFooter,
        CardHeader,
        CardTitle,
    } from "@/components/ui/card";

    let { formStep2, onNext, onBack } = $props();
</script>

<Card>
    <CardHeader>
        <CardTitle>Langkah 2: Data Pendaftaran Peserta</CardTitle>
        <CardDescription>
            Isi data diri peserta/klien yang akan didaftarkan.
        </CardDescription>
    </CardHeader>
    <CardContent>
        <form onsubmit={(e) => { e.preventDefault(); onNext(); }} class="space-y-4">
            <div class="grid gap-2">
                <Label for="nik">NIK</Label>
                <Input id="nik" bind:value={formStep2.nik} required />
                <InputError message={formStep2.errors.nik} />
            </div>
            <div class="grid gap-2">
                <Label for="nama">Nama Lengkap</Label>
                <Input id="nama" bind:value={formStep2.nama} required />
                <InputError message={formStep2.errors.nama} />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="grid gap-2">
                    <Label for="tempat_lahir">Tempat Lahir</Label>
                    <Input id="tempat_lahir" bind:value={formStep2.tempat_lahir} required />
                    <InputError message={formStep2.errors.tempat_lahir} />
                </div>
                <div class="grid gap-2">
                    <Label for="tanggal_lahir">Tanggal Lahir</Label>
                    <Input type="date" id="tanggal_lahir" bind:value={formStep2.tanggal_lahir} required />
                    <InputError message={formStep2.errors.tanggal_lahir} />
                </div>
            </div>
            <div class="grid gap-2">
                <Label for="alamat_asal">Alamat Asal</Label>
                <Textarea id="alamat_asal" bind:value={formStep2.alamat_asal} required />
                <InputError message={formStep2.errors.alamat_asal} />
            </div>
            <div class="grid gap-2">
                <Label for="pendidikan">Pendidikan Terakhir</Label>
                <Input id="pendidikan" bind:value={formStep2.pendidikan} required />
                <InputError message={formStep2.errors.pendidikan} />
            </div>
            <div class="grid gap-2">
                <Label for="keterampilan">Keterampilan yang Diminati</Label>
                <Input id="keterampilan" bind:value={formStep2.keterampilan_yang_diminati} required />
                <InputError message={formStep2.errors.keterampilan_yang_diminati} />
            </div>
        </form>
    </CardContent>
    <CardFooter class="flex justify-between">
        <Button variant="outline" onclick={onBack}>
            Kembali
        </Button>
        <Button onclick={onNext} disabled={formStep2.processing}>
            Selanjutnya
        </Button>
    </CardFooter>
</Card>
