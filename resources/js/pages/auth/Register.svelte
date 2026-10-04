<script module lang="ts">
    export const layout = {
        title: "Registrasi Akun Wali",
        description: "Masukkan data diri Anda sebagai Wali.",
    };
</script>

<script lang="ts">
    import { Form } from "@inertiajs/svelte";
    import AppHead from "@/components/AppHead.svelte";
    import InputError from "@/components/InputError.svelte";
    import PasswordInput from "@/components/PasswordInput.svelte";
    import TextLink from "@/components/TextLink.svelte";
    import { Button } from "@/components/ui/button";
    import { Input } from "@/components/ui/input";
    import { Label } from "@/components/ui/label";
    import { Spinner } from "@/components/ui/spinner";
    import { login } from "@/routes";
    import { store } from "@/routes/register";

    let { passwordRules }: { passwordRules: string } = $props();
</script>

<AppHead title="Registrasi Wali" />

<Form
    {...store.form()}
    resetOnSuccess={["password", "password_confirmation"]}
    class="flex flex-col gap-6"
>
    {#snippet children({ errors, processing })}
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="name">Nama Lengkap</Label>
                <Input
                    id="name"
                    type="text"
                    required
                    autocomplete="name"
                    name="name"
                    placeholder="Nama Lengkap"
                />
                <InputError message={errors.name} />
            </div>

            <div class="grid gap-2">
                <Label for="email">Alamat Email</Label>
                <Input
                    id="email"
                    type="email"
                    required
                    autocomplete="email"
                    name="email"
                    placeholder="email@contoh.com"
                />
                <InputError message={errors.email} />
            </div>

            <div class="grid gap-2">
                <Label for="nik">NIK</Label>
                <Input
                    id="nik"
                    type="text"
                    required
                    name="nik"
                    placeholder="Nomor Induk Kependudukan"
                />
                <InputError message={errors.nik} />
            </div>

            <div class="grid gap-2">
                <Label for="alamat">Alamat</Label>
                <Input
                    id="alamat"
                    type="text"
                    required
                    name="alamat"
                    placeholder="Alamat Lengkap"
                />
                <InputError message={errors.alamat} />
            </div>

            <div class="grid gap-2">
                <Label for="tempat_lahir">Tempat Lahir</Label>
                <Input
                    id="tempat_lahir"
                    type="text"
                    required
                    name="tempat_lahir"
                    placeholder="Tempat Lahir"
                />
                <InputError message={errors.tempat_lahir} />
            </div>

            <div class="grid gap-2">
                <Label for="tanggal_lahir">Tanggal Lahir</Label>
                <Input
                    id="tanggal_lahir"
                    type="date"
                    required
                    name="tanggal_lahir"
                />
                <InputError message={errors.tanggal_lahir} />
            </div>

            <div class="grid gap-2">
                <Label for="nomor_hp">Nomor HP</Label>
                <Input
                    id="nomor_hp"
                    type="tel"
                    required
                    name="nomor_hp"
                    placeholder="Nomor HP"
                />
                <InputError message={errors.nomor_hp} />
            </div>

            <div class="grid gap-2">
                <Label for="password">Kata Sandi</Label>
                <PasswordInput
                    id="password"
                    required
                    autocomplete="new-password"
                    name="password"
                    placeholder="Kata Sandi"
                    passwordrules={passwordRules}
                />
                <InputError message={errors.password} />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">Konfirmasi Kata Sandi</Label>
                <PasswordInput
                    id="password_confirmation"
                    required
                    autocomplete="new-password"
                    name="password_confirmation"
                    placeholder="Konfirmasi Kata Sandi"
                    passwordrules={passwordRules}
                />
                <InputError message={errors.password_confirmation} />
            </div>

            <Button
                type="submit"
                class="mt-2 w-full"
                disabled={processing}
                data-test="register-user-button"
            >
                {#if processing}<Spinner />{/if}
                Buat Akun
            </Button>
        </div>

        <div class="text-center text-sm text-muted-foreground">
            Sudah punya akun?
            <TextLink href={login()} class="underline underline-offset-4">
                Masuk
            </TextLink>
        </div>
    {/snippet}
</Form>
