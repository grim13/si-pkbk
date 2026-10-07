<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import { toUrl } from '@/lib/utils';
    import { dashboard, login, register } from '@/routes';
    import {
        ArrowRight,
        BookOpen,
        CheckCircle2,
        ClipboardCheck,
        FileCheck2,
        FileText,
        GraduationCap,
        Hand,
        HeartHandshake,
        MapPin,
        Menu,
        Sparkles,
        Upload,
        UserPlus,
        Users,
        Wrench,
        X,
    } from '@lucide/svelte';

    type Berkas = {
        id: number;
        nama_berkas: string;
        keterangan: string | null;
        required: boolean;
        template_surat: string | null;
    };

    let {
        persyaratan = [],
        berkas = [],
        canRegister = true,
    }: { persyaratan: string[]; berkas: Berkas[]; canRegister?: boolean } =
        $props();

    const auth = $derived(page.props.auth);
    const appName = $derived((page.props.name as string) || 'SI-PKBK');

    let mobileOpen = $state(false);
    let scrolled = $state(false);

    const navLinks = [
        { href: '#layanan', label: 'Layanan' },
        { href: '#alur', label: 'Alur Pendaftaran' },
        { href: '#persyaratan', label: 'Persyaratan' },
        { href: '#kontak', label: 'Kontak' },
    ];

    const layanan = [
        {
            icon: Wrench,
            title: 'Bimbingan Keterampilan Praktis Produktif',
            desc: 'Pelatihan keterampilan kerja yang aplikatif agar klien mampu berkarya dan mandiri secara ekonomi.',
        },
        {
            icon: Hand,
            title: 'Bimbingan Massage / Pijat',
            desc: 'Pelatihan pijat dan massage profesional sebagai bekal usaha mandiri maupun bekerja di sektor jasa.',
        },
        {
            icon: GraduationCap,
            title: 'Bimbingan Sekolah',
            desc: 'Pendampingan pendidikan bagi klien usia sekolah agar dapat melanjutkan pendidikan formal.',
        },
        {
            icon: Users,
            title: 'Bimbingan Sosial',
            desc: 'Penguatan kemampuan bersosialisasi, berkomunikasi, dan beradaptasi di lingkungan keluarga & masyarakat.',
        },
        {
            icon: HeartHandshake,
            title: 'Bimbingan Mental Spiritual',
            desc: 'Pembinaan sikap, kepercayaan diri, dan nilai keagamaan untuk membentuk pribadi yang tangguh.',
        },
        {
            icon: ClipboardCheck,
            title: 'Asesmen & Pendampingan',
            desc: 'Asesmen menyeluruh oleh pekerja sosial untuk menentukan program rehabilitasi yang paling sesuai.',
        },
    ];

    const alur = [
        {
            icon: UserPlus,
            title: 'Buat Akun Wali',
            desc: 'Orang tua / wali mendaftar akun di sistem.',
        },
        {
            icon: FileText,
            title: 'Isi Data Klien',
            desc: 'Lengkapi formulir identitas calon klien.',
        },
        {
            icon: Upload,
            title: 'Unggah Berkas',
            desc: 'Unggah seluruh dokumen persyaratan.',
        },
        {
            icon: FileCheck2,
            title: 'Verifikasi Berkas',
            desc: 'Petugas memeriksa kelengkapan dokumen.',
        },
        {
            icon: ClipboardCheck,
            title: 'Asesmen',
            desc: 'Wawancara & asesmen oleh pekerja sosial.',
        },
        {
            icon: GraduationCap,
            title: 'Pengumuman',
            desc: 'Penetapan kelulusan oleh kepala seksi.',
        },
    ];

    const wajibCount = $derived(berkas.filter((b) => b.required).length);

    // Animasi muncul saat di-scroll
    function reveal(node: HTMLElement, delay = 0) {
        node.style.transitionDelay = `${delay}ms`;
        node.classList.add('reveal');
        const io = new IntersectionObserver(
            (entries) => {
                entries.forEach((e) => {
                    if (e.isIntersecting) {
                        node.classList.add('reveal-in');
                        io.unobserve(node);
                    }
                });
            },
            { threshold: 0.15 },
        );
        io.observe(node);
        return { destroy: () => io.disconnect() };
    }

    function splitLines(text: string) {
        return text.split('\n').filter((l) => l.trim() !== '');
    }
</script>

<svelte:window onscroll={() => (scrolled = window.scrollY > 16)} />

<AppHead title="Pendaftaran Klien Rehabilitasi Sosial">
    <meta
        name="description"
        content="Informasi layanan rehabilitasi sosial penyandang disabilitas sensorik, persyaratan, dan alur pendaftaran klien secara online."
    />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    />
</AppHead>

<div class="landing min-h-screen bg-[#f7f5ef] text-slate-800 dark:bg-[#07110f] dark:text-slate-200">
    <!-- ================= NAVBAR ================= -->
    <header
        class="fixed inset-x-0 top-0 z-50 transition-all duration-300 {scrolled
            ? 'border-b border-slate-200/60 bg-white/80 shadow-sm backdrop-blur-xl dark:border-white/10 dark:bg-[#07110f]/80'
            : 'bg-transparent'}"
    >
        <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6">
            <a href="#top" class="flex items-center gap-2.5">
                <span
                    class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-teal-600 to-emerald-500 text-white shadow-lg shadow-teal-600/30"
                >
                    <AppLogoIcon class="size-5 fill-current" />
                </span>
                <span class="text-lg font-extrabold tracking-tight">{appName}</span>
            </a>

            <ul class="hidden items-center gap-8 text-sm font-medium md:flex">
                {#each navLinks as l}
                    <li>
                        <a
                            href={l.href}
                            class="text-slate-600 transition-colors hover:text-teal-700 dark:text-slate-300 dark:hover:text-teal-300"
                            >{l.label}</a
                        >
                    </li>
                {/each}
            </ul>

            <div class="hidden items-center gap-3 md:flex">
                {#if auth.user}
                    <Link
                        href={toUrl(dashboard())}
                        class="btn-primary"
                        id="nav-dashboard">Dashboard <ArrowRight class="size-4" /></Link
                    >
                {:else}
                    <Link
                        href={toUrl(login())}
                        id="nav-login"
                        class="rounded-full px-4 py-2 text-sm font-semibold text-slate-700 transition hover:text-teal-700 dark:text-slate-200"
                        >Masuk</Link
                    >
                    {#if canRegister}
                        <Link href={toUrl(register())} class="btn-primary" id="nav-register"
                            >Daftar Sekarang</Link
                        >
                    {/if}
                {/if}
            </div>

            <button
                class="rounded-lg p-2 md:hidden"
                aria-label="Buka menu"
                id="mobile-menu-toggle"
                onclick={() => (mobileOpen = !mobileOpen)}
            >
                {#if mobileOpen}<X class="size-6" />{:else}<Menu class="size-6" />{/if}
            </button>
        </nav>

        {#if mobileOpen}
            <div
                class="border-t border-slate-200 bg-white/95 px-6 py-4 backdrop-blur-xl md:hidden dark:border-white/10 dark:bg-[#07110f]/95"
            >
                <ul class="space-y-3 text-sm font-medium">
                    {#each navLinks as l}
                        <li>
                            <a href={l.href} class="block py-1" onclick={() => (mobileOpen = false)}
                                >{l.label}</a
                            >
                        </li>
                    {/each}
                </ul>
                <div class="mt-4 flex gap-3">
                    {#if auth.user}
                        <Link href={toUrl(dashboard())} class="btn-primary flex-1 justify-center"
                            >Dashboard</Link
                        >
                    {:else}
                        <Link href={toUrl(login())} class="btn-ghost flex-1 justify-center">Masuk</Link>
                        {#if canRegister}
                            <Link href={toUrl(register())} class="btn-primary flex-1 justify-center"
                                >Daftar</Link
                            >
                        {/if}
                    {/if}
                </div>
            </div>
        {/if}
    </header>

    <main id="top">
        <!-- ================= HERO ================= -->
        <section class="relative overflow-hidden pt-28 pb-20 lg:pt-36 lg:pb-28">
            <!-- dekorasi -->
            <div class="pointer-events-none absolute inset-0 -z-0">
                <div
                    class="blob absolute -top-32 -left-24 size-[28rem] rounded-full bg-teal-300/40 blur-3xl dark:bg-teal-700/30"
                ></div>
                <div
                    class="blob absolute top-40 -right-24 size-[24rem] rounded-full bg-amber-200/50 blur-3xl [animation-delay:-6s] dark:bg-amber-600/20"
                ></div>
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(15,118,110,0.12)_1px,transparent_0)] [background-size:28px_28px] [mask-image:linear-gradient(to_bottom,black,transparent_85%)]"
                ></div>
            </div>

            <div class="relative mx-auto grid max-w-7xl items-center gap-14 px-6 lg:grid-cols-2">
                <div use:reveal>
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-teal-600/20 bg-white/70 px-3.5 py-1.5 text-xs font-semibold text-teal-800 shadow-sm backdrop-blur dark:border-teal-400/20 dark:bg-white/5 dark:text-teal-300"
                    >
                        <span class="relative flex size-2">
                            <span
                                class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-500 opacity-75"
                            ></span>
                            <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                        </span>
                        Pendaftaran klien dibuka secara online
                    </span>

                    <h1
                        class="mt-6 text-4xl leading-[1.1] font-extrabold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl dark:text-white"
                    >
                        Bersama Menuju
                        <span
                            class="bg-gradient-to-r from-teal-600 via-emerald-500 to-amber-500 bg-clip-text text-transparent"
                            >Kemandirian</span
                        >
                        Penyandang Disabilitas Sensorik
                    </h1>

                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate-600 dark:text-slate-400">
                        Layanan rehabilitasi sosial Panti Sosial Rehabilitasi Penyandang Disabilitas
                        Sensorik (PSR-PDS) — bimbingan keterampilan, sosial, dan mental spiritual untuk
                        membekali klien hidup mandiri dan berdaya.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-4">
                        {#if auth.user}
                            <Link href={toUrl(dashboard())} class="btn-primary btn-lg" id="hero-dashboard">
                                Buka Dashboard <ArrowRight class="size-5" />
                            </Link>
                        {:else if canRegister}
                            <Link href={toUrl(register())} class="btn-primary btn-lg" id="hero-register">
                                Daftar Sekarang <ArrowRight class="size-5" />
                            </Link>
                        {/if}
                        <a href="#persyaratan" class="btn-ghost btn-lg" id="hero-persyaratan">
                            Lihat Persyaratan
                        </a>
                    </div>

                    <dl class="mt-12 grid max-w-lg grid-cols-3 gap-6">
                        <div>
                            <dt class="text-xs font-medium text-slate-500">Usia Klien</dt>
                            <dd class="mt-1 text-2xl font-extrabold text-slate-900 dark:text-white">
                                9–35 <span class="text-sm font-semibold text-slate-500">th</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">Berkas Wajib</dt>
                            <dd class="mt-1 text-2xl font-extrabold text-slate-900 dark:text-white">
                                {wajibCount || '-'}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500">Tahap Seleksi</dt>
                            <dd class="mt-1 text-2xl font-extrabold text-slate-900 dark:text-white">
                                {alur.length}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="relative" use:reveal={150}>
                    <div
                        class="absolute -inset-4 rounded-[2rem] bg-gradient-to-br from-teal-500/30 via-emerald-400/20 to-amber-300/30 blur-2xl"
                    ></div>
                    <div
                        class="relative overflow-hidden rounded-[2rem] border border-white/60 shadow-2xl shadow-teal-900/20 dark:border-white/10"
                    >
                        <img
                            src="/images/landing-hero.jpg"
                            alt="Klien penyandang disabilitas sensorik mengikuti bimbingan keterampilan, braille, pijat, dan bahasa isyarat"
                            class="aspect-[4/3] w-full object-cover"
                            loading="eager"
                        />
                    </div>

                    <div
                        class="float absolute -bottom-6 -left-4 flex items-center gap-3 rounded-2xl border border-white/70 bg-white/90 p-4 shadow-xl backdrop-blur sm:-left-8 dark:border-white/10 dark:bg-slate-900/90"
                    >
                        <span class="flex size-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                            <CheckCircle2 class="size-6" />
                        </span>
                        <div>
                            <p class="text-sm font-bold">Daftar dari Rumah</p>
                            <p class="text-xs text-slate-500">Unggah berkas & pantau status online</p>
                        </div>
                    </div>
                    <div
                        class="float absolute -top-5 -right-3 hidden items-center gap-2 rounded-2xl border border-white/70 bg-white/90 px-4 py-3 shadow-xl backdrop-blur [animation-delay:-3s] sm:flex dark:border-white/10 dark:bg-slate-900/90"
                    >
                        <Sparkles class="size-5 text-amber-500" />
                        <p class="text-sm font-semibold">Program Rehabilitasi Terpadu</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= LAYANAN ================= -->
        <section id="layanan" class="scroll-mt-20 py-20 lg:py-28">
            <div class="mx-auto max-w-7xl px-6">
                <div class="mx-auto max-w-2xl text-center" use:reveal>
                    <p class="section-eyebrow">Informasi Layanan</p>
                    <h2 class="section-title">Program Rehabilitasi Sosial</h2>
                    <p class="mt-4 text-slate-600 dark:text-slate-400">
                        Klien mendapatkan rangkaian bimbingan yang disesuaikan dengan hasil asesmen,
                        minat, dan potensi masing-masing.
                    </p>
                </div>

                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {#each layanan as item, i}
                        {@const Icon = item.icon}
                        <article
                            use:reveal={i * 80}
                            class="group relative overflow-hidden rounded-3xl border border-slate-200/70 bg-white p-7 shadow-sm transition-all duration-300 hover:-translate-y-1.5 hover:border-teal-500/40 hover:shadow-xl hover:shadow-teal-900/10 dark:border-white/10 dark:bg-white/[0.03]"
                        >
                            <div
                                class="absolute -top-16 -right-16 size-40 rounded-full bg-gradient-to-br from-teal-400/0 to-emerald-400/0 transition-all duration-500 group-hover:from-teal-400/20 group-hover:to-emerald-300/10"
                            ></div>
                            <span
                                class="relative flex size-12 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-600 to-emerald-500 text-white shadow-lg shadow-teal-600/25 transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3"
                            >
                                <Icon class="size-6" />
                            </span>
                            <h3 class="relative mt-5 text-lg font-bold text-slate-900 dark:text-white">
                                {item.title}
                            </h3>
                            <p class="relative mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                                {item.desc}
                            </p>
                        </article>
                    {/each}
                </div>
            </div>
        </section>

        <!-- ================= ALUR ================= -->
        <section
            id="alur"
            class="relative scroll-mt-20 overflow-hidden bg-gradient-to-br from-teal-900 via-teal-800 to-emerald-900 py-20 text-white lg:py-28"
        >
            <div
                class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.08)_1px,transparent_0)] [background-size:24px_24px]"
            ></div>
            <div class="relative mx-auto max-w-7xl px-6">
                <div class="mx-auto max-w-2xl text-center" use:reveal>
                    <p class="text-sm font-bold tracking-widest text-amber-300 uppercase">Alur Pendaftaran</p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">
                        6 Langkah Mudah Menjadi Klien
                    </h2>
                    <p class="mt-4 text-teal-100/80">
                        Seluruh proses dapat dipantau langsung melalui akun wali.
                    </p>
                </div>

                <ol class="relative mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-6 lg:gap-4">
                    <div
                        class="absolute top-7 right-[8%] left-[8%] hidden h-0.5 bg-gradient-to-r from-amber-300/0 via-amber-300/60 to-amber-300/0 lg:block"
                    ></div>
                    {#each alur as step, i}
                        {@const Icon = step.icon}
                        <li class="relative flex flex-col items-center text-center" use:reveal={i * 90}>
                            <span
                                class="relative flex size-14 items-center justify-center rounded-2xl border border-white/20 bg-white/10 shadow-lg backdrop-blur transition-transform duration-300 hover:scale-110"
                            >
                                <Icon class="size-6 text-amber-300" />
                                <span
                                    class="absolute -top-2 -right-2 flex size-6 items-center justify-center rounded-full bg-amber-400 text-xs font-extrabold text-teal-950"
                                    >{i + 1}</span
                                >
                            </span>
                            <h3 class="mt-4 font-bold">{step.title}</h3>
                            <p class="mt-1 text-sm text-teal-100/75">{step.desc}</p>
                        </li>
                    {/each}
                </ol>
            </div>
        </section>

        <!-- ================= PERSYARATAN ================= -->
        <section id="persyaratan" class="scroll-mt-20 py-20 lg:py-28">
            <div class="mx-auto max-w-7xl px-6">
                <div class="mx-auto max-w-2xl text-center" use:reveal>
                    <p class="section-eyebrow">Persyaratan Pendaftaran</p>
                    <h2 class="section-title">Siapkan Sebelum Mendaftar</h2>
                    <p class="mt-4 text-slate-600 dark:text-slate-400">
                        Pastikan calon klien memenuhi ketentuan berikut dan dokumen telah disiapkan
                        dalam bentuk file (PDF / gambar).
                    </p>
                </div>

                <div class="mt-14 grid gap-8 lg:grid-cols-5">
                    <!-- Persyaratan administrasi -->
                    <div
                        use:reveal
                        class="rounded-3xl border border-slate-200/70 bg-white p-7 shadow-sm lg:col-span-3 dark:border-white/10 dark:bg-white/[0.03]"
                    >
                        <div class="flex items-center gap-3">
                            <span class="flex size-10 items-center justify-center rounded-xl bg-teal-100 text-teal-700 dark:bg-teal-500/15 dark:text-teal-300">
                                <BookOpen class="size-5" />
                            </span>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                Persyaratan Administrasi
                            </h3>
                        </div>

                        {#if persyaratan.length}
                            <ol class="mt-6 space-y-3">
                                {#each persyaratan as item, i}
                                    {@const lines = splitLines(item)}
                                    <li
                                        class="flex gap-4 rounded-2xl p-3 transition-colors hover:bg-teal-50/70 dark:hover:bg-white/5"
                                    >
                                        <span
                                            class="flex size-7 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-teal-600 to-emerald-500 text-xs font-bold text-white"
                                            >{i + 1}</span
                                        >
                                        <div class="pt-0.5 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                                            <p>{lines[0]}</p>
                                            {#if lines.length > 1}
                                                <ul class="mt-2 space-y-1.5 text-slate-600 dark:text-slate-400">
                                                    {#each lines.slice(1) as sub}
                                                        <li class="pl-1">{sub}</li>
                                                    {/each}
                                                </ul>
                                            {/if}
                                        </div>
                                    </li>
                                {/each}
                            </ol>
                        {:else}
                            <p class="mt-6 text-sm text-slate-500">Informasi persyaratan belum tersedia.</p>
                        {/if}
                    </div>

                    <!-- Berkas -->
                    <div
                        use:reveal={120}
                        class="rounded-3xl border border-slate-200/70 bg-white p-7 shadow-sm lg:col-span-2 dark:border-white/10 dark:bg-white/[0.03]"
                    >
                        <div class="flex items-center gap-3">
                            <span class="flex size-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                                <FileText class="size-5" />
                            </span>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                    Berkas yang Diunggah
                                </h3>
                                <p class="text-xs text-slate-500">
                                    {wajibCount} wajib · {berkas.length - wajibCount} opsional
                                </p>
                            </div>
                        </div>

                        {#if berkas.length}
                            <ul class="mt-6 divide-y divide-slate-100 dark:divide-white/5">
                                {#each berkas as b}
                                    <li class="flex items-start justify-between gap-3 py-3">
                                        <div class="flex items-start gap-3">
                                            <CheckCircle2
                                                class="mt-0.5 size-4 shrink-0 {b.required
                                                    ? 'text-emerald-600'
                                                    : 'text-slate-400'}"
                                            />
                                            <div>
                                                <p class="text-sm font-medium text-slate-800 dark:text-slate-200">
                                                    {b.nama_berkas}
                                                </p>
                                                {#if b.template_surat}
                                                    <p class="text-xs text-teal-700 dark:text-teal-400">
                                                        Template tersedia saat pengisian formulir
                                                    </p>
                                                {/if}
                                            </div>
                                        </div>
                                        <span
                                            class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold tracking-wide uppercase {b.required
                                                ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300'
                                                : 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-400'}"
                                        >
                                            {b.required ? 'Wajib' : 'Opsional'}
                                        </span>
                                    </li>
                                {/each}
                            </ul>
                        {:else}
                            <p class="mt-6 text-sm text-slate-500">Daftar berkas belum tersedia.</p>
                        {/if}
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= CTA ================= -->
        <section class="px-6 pb-20 lg:pb-28">
            <div
                use:reveal
                class="relative mx-auto max-w-7xl overflow-hidden rounded-[2rem] bg-gradient-to-r from-amber-400 via-amber-300 to-emerald-300 p-10 text-center shadow-2xl shadow-amber-900/10 sm:p-14"
            >
                <div class="blob pointer-events-none absolute -top-20 -left-10 size-72 rounded-full bg-white/40 blur-3xl"></div>
                <h2 class="relative text-3xl font-extrabold tracking-tight text-teal-950 sm:text-4xl">
                    Siap Mendaftarkan Calon Klien?
                </h2>
                <p class="relative mx-auto mt-4 max-w-xl text-teal-900/80">
                    Buat akun wali, lengkapi data, dan unggah berkas. Status pendaftaran dapat dipantau
                    kapan saja.
                </p>
                <div class="relative mt-8 flex flex-wrap justify-center gap-4">
                    {#if auth.user}
                        <Link href={toUrl(dashboard())} class="btn-dark btn-lg" id="cta-dashboard">
                            Buka Dashboard <ArrowRight class="size-5" />
                        </Link>
                    {:else}
                        {#if canRegister}
                            <Link href={toUrl(register())} class="btn-dark btn-lg" id="cta-register">
                                Buat Akun Wali <ArrowRight class="size-5" />
                            </Link>
                        {/if}
                        <Link
                            href={toUrl(login())}
                            id="cta-login"
                            class="btn-lg inline-flex items-center rounded-full border border-teal-950/20 bg-white/50 font-semibold text-teal-950 backdrop-blur transition hover:bg-white/80"
                            >Sudah punya akun? Masuk</Link
                        >
                    {/if}
                </div>
            </div>
        </section>
    </main>

    <!-- ================= FOOTER ================= -->
    <footer id="kontak" class="border-t border-slate-200/70 bg-white dark:border-white/10 dark:bg-black/20">
        <div class="mx-auto grid max-w-7xl gap-10 px-6 py-14 md:grid-cols-3">
            <div>
                <div class="flex items-center gap-2.5">
                    <span
                        class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-teal-600 to-emerald-500 text-white"
                    >
                        <AppLogoIcon class="size-5 fill-current" />
                    </span>
                    <span class="text-lg font-extrabold">{appName}</span>
                </div>
                <p class="mt-4 max-w-xs text-sm text-slate-600 dark:text-slate-400">
                    Sistem informasi pendaftaran klien rehabilitasi sosial penyandang disabilitas
                    sensorik.
                </p>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tautan</h3>
                <ul class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                    {#each navLinks.slice(0, 3) as l}
                        <li><a href={l.href} class="hover:text-teal-700 dark:hover:text-teal-300">{l.label}</a></li>
                    {/each}
                </ul>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Lokasi Layanan</h3>
                <p class="mt-4 flex gap-2 text-sm text-slate-600 dark:text-slate-400">
                    <MapPin class="mt-0.5 size-4 shrink-0 text-teal-600" />
                    Panti Sosial Rehabilitasi Penyandang Disabilitas Sensorik (PSR-PDS)
                </p>
            </div>
        </div>
        <div class="border-t border-slate-200/70 py-6 text-center text-xs text-slate-500 dark:border-white/10">
            © {new Date().getFullYear()} {appName}. Seluruh hak cipta dilindungi.
        </div>
    </footer>
</div>

<style>
    .landing {
        font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
        scroll-behavior: smooth;
    }
    :global(html:has(.landing)) {
        scroll-behavior: smooth;
    }

    .landing :global(.btn-primary) {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border-radius: 9999px;
        padding: 0.55rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 700;
        color: white;
        background-image: linear-gradient(135deg, #0f766e, #10b981);
        box-shadow: 0 10px 25px -10px rgb(15 118 110 / 0.6);
        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }
    .landing :global(.btn-primary:hover) {
        transform: translateY(-2px);
        box-shadow: 0 16px 30px -12px rgb(15 118 110 / 0.7);
    }
    .landing :global(.btn-ghost) {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border-radius: 9999px;
        padding: 0.55rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 700;
        border: 1px solid rgb(15 118 110 / 0.25);
        background: rgb(255 255 255 / 0.6);
        color: #115e59;
        backdrop-filter: blur(8px);
        transition: background 0.2s ease;
    }
    .landing :global(.btn-ghost:hover) {
        background: rgb(255 255 255 / 0.95);
    }
    :global(.dark) .landing :global(.btn-ghost) {
        background: rgb(255 255 255 / 0.05);
        color: #5eead4;
        border-color: rgb(94 234 212 / 0.25);
    }
    .landing :global(.btn-dark) {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        border-radius: 9999px;
        font-weight: 700;
        color: white;
        background: #042f2e;
        transition: transform 0.2s ease;
    }
    .landing :global(.btn-dark:hover) {
        transform: translateY(-2px);
    }
    .landing :global(.btn-lg) {
        padding: 0.85rem 1.75rem;
        font-size: 1rem;
    }

    .section-eyebrow {
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #0f766e;
    }
    .section-title {
        margin-top: 0.75rem;
        font-size: clamp(1.875rem, 3vw, 2.5rem);
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.15;
    }

    :global(.reveal) {
        opacity: 0;
        transform: translateY(24px);
        transition:
            opacity 0.7s ease,
            transform 0.7s cubic-bezier(0.22, 1, 0.36, 1);
    }
    :global(.reveal-in) {
        opacity: 1;
        transform: none;
    }

    .blob {
        animation: blob 14s ease-in-out infinite;
    }
    .float {
        animation: float 6s ease-in-out infinite;
    }
    @keyframes blob {
        0%,
        100% {
            transform: translate(0, 0) scale(1);
        }
        50% {
            transform: translate(30px, 20px) scale(1.08);
        }
    }
    @keyframes float {
        0%,
        100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-10px);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .blob,
        .float {
            animation: none;
        }
        :global(.reveal) {
            opacity: 1;
            transform: none;
            transition: none;
        }
    }
</style>
