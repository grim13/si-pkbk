<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import BookOpen from '@lucide/svelte/icons/book-open';
    import FolderGit2 from '@lucide/svelte/icons/folder-git-2';
    import LayoutGrid from '@lucide/svelte/icons/layout-grid';
    import Users from '@lucide/svelte/icons/users';
    import Shield from '@lucide/svelte/icons/shield';
    import Contact from '@lucide/svelte/icons/contact';
    import FileText from '@lucide/svelte/icons/file-text';
    import type { Snippet } from 'svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import NavFooter from '@/components/NavFooter.svelte';
    import NavMain from '@/components/NavMain.svelte';
    import NavUser from '@/components/NavUser.svelte';
    import {
        Sidebar,
        SidebarContent,
        SidebarFooter,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import { toUrl } from '@/lib/utils';
    import { dashboard } from '@/routes';
    import type { NavItem } from '@/types';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    const user = $derived(page.props.auth.user);

    const mainNavItems: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    const accessControlItems: NavItem[] = [
        {
            title: 'User Master',
            href: '/user-master',
            icon: Users,
        },
        {
            title: 'Role Master',
            href: '/role-master',
            icon: Shield,
        },
    ];

    const masterItems: NavItem[] = [
        {
            title: 'Wali Master',
            href: '/wali-master',
            icon: Contact,
        },
    ];

    const waliItems: NavItem[] = [
        {
            title: 'Pendaftaran',
            href: '/pendaftaran',
            icon: FileText,
        },
    ];

    const adminItems: NavItem[] = [
        {
            title: 'Verifikasi Pendaftaran',
            href: '/admin/pendaftaran',
            icon: FileText, // Reusing FileText icon, or we could import ClipboardCheck
        },
    ];

    const pekerjaSosialItems: NavItem[] = [
        {
            title: 'Asesmen Klien',
            href: '/pekerja-sosial/asesmen',
            icon: FileText,
        },
    ];

    const footerNavItems: NavItem[] = [
        {
            title: 'Repository',
            href: 'https://github.com/laravel/svelte-starter-kit',
            icon: FolderGit2,
        },
        {
            title: 'Documentation',
            href: 'https://laravel.com/docs/starter-kits#svelte',
            icon: BookOpen,
        },
    ];
</script>

<Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton size="lg" asChild>
                    {#snippet children(props)}
                        <Link
                            {...props}
                            href={toUrl(dashboard())}
                            class={props.class}
                        >
                            <AppLogo />
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
        <NavMain items={mainNavItems} />
        {#if user?.permissions?.includes('access.manage')}
            <NavMain items={accessControlItems} label="Access Control" />
            <NavMain items={adminItems} label="Layanan Admin" />
        {/if}
        {#if user?.permissions?.includes('wali.manage')}
            <NavMain items={masterItems} label="Master" />
        {/if}
        {#if user?.roles?.includes('wali')}
            <NavMain items={waliItems} label="Menu Wali" />
        {/if}
        {#if user?.permissions?.includes('asesmen.do')}
            <NavMain items={pekerjaSosialItems} label="Pekerja Sosial" />
        {/if}
    </SidebarContent>

    <SidebarFooter>
        <NavFooter items={footerNavItems} />
        <NavUser />
    </SidebarFooter>
</Sidebar>
{@render children?.()}
