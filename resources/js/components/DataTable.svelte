<script lang="ts">
    import * as Table from "@/components/ui/table";
    import { Button } from "@/components/ui/button";
    import * as Select from "@/components/ui/select";
    import { router } from "@inertiajs/svelte";
    import { Input } from "@/components/ui/input";
    import type { Snippet } from "svelte";

    type Props = {
        data: any;
        filters?: any;
        searchPlaceholder?: string;
        onFilterChange?: (filters: { search: string; perPage: string }) => void;
        header: Snippet;
        row: Snippet<[any]>;
        emptyMessage?: string;
        columnsCount?: number;
        children?: Snippet;
    };

    let {
        data,
        filters = {},
        searchPlaceholder = "Cari...",
        onFilterChange,
        header,
        row,
        emptyMessage = "Tidak ada data.",
        columnsCount = 1,
        children
    }: Props = $props();

    let search = $state(filters?.search || "");
    let perPage = $state(filters?.perPage?.toString() || "10");

    let searchTimeout: ReturnType<typeof setTimeout>;

    function handleSearch(e: Event) {
        const value = (e.target as HTMLInputElement).value;
        search = value;
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            onFilterChange?.({ search, perPage });
        }, 500);
    }

    function handlePerPageChange(val: string) {
        perPage = val;
        onFilterChange?.({ search, perPage });
    }

    function navigateToPage(url: string | null) {
        if (url) {
            router.get(url, {}, { preserveState: true, preserveScroll: true });
        }
    }
</script>

<div class="mb-4 flex items-center justify-between">
    <div class="w-full max-w-sm">
        <Input
            type="search"
            placeholder={searchPlaceholder}
            value={search}
            oninput={handleSearch}
        />
    </div>
    {#if children}
        {@render children()}
    {/if}
</div>

<div class="rounded-md border bg-card">
    <Table.Root>
        <Table.Header>
            <Table.Row>
                {@render header()}
            </Table.Row>
        </Table.Header>
        <Table.Body>
            {#each data.data as item (item.id || Math.random())}
                <Table.Row>
                    {@render row(item)}
                </Table.Row>
            {:else}
                <Table.Row>
                    <Table.Cell colspan={columnsCount} class="h-24 text-center">
                        {emptyMessage}
                    </Table.Cell>
                </Table.Row>
            {/each}
        </Table.Body>
    </Table.Root>
</div>

<div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
    <div class="flex items-center gap-2 text-sm text-muted-foreground">
        <span>Tampilkan</span>
        <Select.Root
            type="single"
            value={perPage}
            onValueChange={handlePerPageChange}
        >
            <Select.Trigger class="w-[70px] h-8">
                {perPage}
            </Select.Trigger>
            <Select.Content>
                <Select.Item value="10" label="10">10</Select.Item>
                <Select.Item value="25" label="25">25</Select.Item>
                <Select.Item value="50" label="50">50</Select.Item>
                <Select.Item value="100" label="100">100</Select.Item>
            </Select.Content>
        </Select.Root>
        <span>
            data dari total {data.total}.
        </span>
    </div>
    
    <div class="flex items-center gap-1">
        {#each data.links as link}
            <!-- eslint-disable-next-line svelte/valid-compile -->
            <Button
                variant={link.active ? "default" : "outline"}
                size="sm"
                disabled={!link.url}
                class={!link.url ? "opacity-50 cursor-not-allowed" : ""}
                onclick={() => navigateToPage(link.url)}
            >
                {@html link.label}
            </Button>
        {/each}
    </div>
</div>
