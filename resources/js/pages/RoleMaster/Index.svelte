<script module lang="ts">
  export const layout = {
    breadcrumbs: [
      {
        title: 'Role Master',
        href: '/role-master',
      },
    ],
  };
</script>

<script lang="ts">
  import { router, useForm } from '@inertiajs/svelte'
  import { untrack } from 'svelte'
  import { Button } from '@/components/ui/button'
  import { Input } from '@/components/ui/input'
  import { Label } from '@/components/ui/label'
  import * as Dialog from '@/components/ui/dialog'
  import * as Table from '@/components/ui/table'
  import { Badge } from '@/components/ui/badge'
  import { Checkbox } from '@/components/ui/checkbox'
  import AppHead from '@/components/AppHead.svelte'
  import * as DropdownMenu from '@/components/ui/dropdown-menu'
  import { MoreVertical, Edit, Trash2 } from '@lucide/svelte'

  let { roles, permissions, filters } = $props()

  // State for search and sort
  let search = $state(untrack(() => filters.search || ''))
  let sortField = $state(untrack(() => filters.sortField || 'id'))
  let sortDirection = $state(untrack(() => filters.sortDirection || 'desc'))
  let searchTimeout: ReturnType<typeof setTimeout>

  function applyFilters() {
    router.get(
      '/role-master',
      { search, sortField, sortDirection },
      { preserveState: true, preserveScroll: true, replace: true }
    )
  }

  function handleSearch(e: Event) {
    const value = (e.target as HTMLInputElement).value
    search = value
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
      applyFilters()
    }, 500) // Debounce 500ms
  }

  function handleSort(field: string) {
    if (sortField === field) {
      sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'
    } else {
      sortField = field
      sortDirection = 'asc'
    }
    applyFilters()
  }

  // State for Create/Edit Modal
  let isDialogOpen = $state(false)
  let isEditing = $state(false)
  let currentRoleId = $state<number | null>(null)

  const form = useForm({
    name: '',
    permissions: [] as string[],
  })

  function openCreateDialog() {
    isEditing = false
    currentRoleId = null
    form.reset()
    form.clearErrors()
    isDialogOpen = true
  }

  function openEditDialog(role: any) {
    isEditing = true
    currentRoleId = role.id
    form.name = role.name
    form.permissions = role.permissions ? role.permissions.map((p: any) => p.name) : []
    form.clearErrors()
    isDialogOpen = true
  }

  function submit() {
    if (isEditing && currentRoleId) {
      form.put(`/role-master/${currentRoleId}`, {
        onSuccess: () => {
          isDialogOpen = false
        }
      })
    } else {
      form.post('/role-master', {
        onSuccess: () => {
          isDialogOpen = false
        }
      })
    }
  }

  function deleteRole(role: any) {
    if (confirm(`Apakah Anda yakin ingin menghapus role ${role.name}?`)) {
      router.delete(`/role-master/${role.id}`)
    }
  }

  function togglePermission(permissionName: string, checked: boolean) {
    if (checked) {
      if (!form.permissions.includes(permissionName)) {
        form.permissions = [...form.permissions, permissionName]
      }
    } else {
      form.permissions = form.permissions.filter(p => p !== permissionName)
    }
  }
</script>

<AppHead title="Role Master" />

<div class="p-6 w-full">
  <div class="mb-4 flex items-center justify-between">
    <div class="w-full max-w-sm">
      <Input
        type="search"
        placeholder="Cari nama role..."
        value={search}
        oninput={handleSearch}
      />
    </div>
    <Button onclick={openCreateDialog}>Tambah Role</Button>
  </div>

  <div class="rounded-md border bg-card">
    <Table.Root>
      <Table.Header>
        <Table.Row>
          <Table.Head class="cursor-pointer select-none" onclick={() => handleSort('id')}>
            ID {sortField === 'id' ? (sortDirection === 'asc' ? '↑' : '↓') : ''}
          </Table.Head>
          <Table.Head class="cursor-pointer select-none" onclick={() => handleSort('name')}>
            Nama {sortField === 'name' ? (sortDirection === 'asc' ? '↑' : '↓') : ''}
          </Table.Head>
          <Table.Head>Permissions</Table.Head>
          <Table.Head class="text-right">Aksi</Table.Head>
        </Table.Row>
      </Table.Header>
      <Table.Body>
        {#each roles.data as role (role.id)}
          <Table.Row>
            <Table.Cell class="font-medium">{role.id}</Table.Cell>
            <Table.Cell>{role.name}</Table.Cell>
            <Table.Cell>
              <div class="flex flex-wrap gap-1 max-w-[400px]">
                {#if role.permissions && role.permissions.length > 0}
                  {#each role.permissions as permission}
                    <Badge variant="secondary">{permission.name}</Badge>
                  {/each}
                {:else}
                  <span class="text-muted-foreground text-sm">-</span>
                {/if}
              </div>
            </Table.Cell>
            <Table.Cell class="text-right">
              <DropdownMenu.Root>
                <DropdownMenu.Trigger>
                  <Button variant="ghost" size="icon">
                    <MoreVertical class="h-4 w-4" />
                  </Button>
                </DropdownMenu.Trigger>
                <DropdownMenu.Content align="end">
                  <DropdownMenu.Item onclick={() => openEditDialog(role)}>
                    <Edit class="mr-2 h-4 w-4" />
                    Edit
                  </DropdownMenu.Item>
                  <DropdownMenu.Item class="text-destructive focus:bg-destructive/10 focus:text-destructive" onclick={() => deleteRole(role)}>
                    <Trash2 class="mr-2 h-4 w-4" />
                    Hapus
                  </DropdownMenu.Item>
                </DropdownMenu.Content>
              </DropdownMenu.Root>
            </Table.Cell>
          </Table.Row>
        {:else}
          <Table.Row>
            <Table.Cell colspan={4} class="h-24 text-center">
              Tidak ada data.
            </Table.Cell>
          </Table.Row>
        {/each}
      </Table.Body>
    </Table.Root>
  </div>

  <div class="mt-4 flex items-center justify-between">
    <div class="text-sm text-muted-foreground">
      Menampilkan {roles.from || 0} hingga {roles.to || 0} dari total {roles.total} data.
    </div>
    <div class="flex items-center gap-1">
      {#each roles.links as link}
        <!-- eslint-disable-next-line svelte/valid-compile -->
        <Button
          variant={link.active ? 'default' : 'outline'}
          size="sm"
          disabled={!link.url}
          class={!link.url ? 'opacity-50 cursor-not-allowed' : ''}
          onclick={() => {
            if (link.url) router.get(link.url, { search, sortField, sortDirection }, { preserveState: true })
          }}
        >
          {@html link.label}
        </Button>
      {/each}
    </div>
  </div>
</div>

<Dialog.Root bind:open={isDialogOpen}>
  <Dialog.Content class="sm:max-w-[425px]">
    <Dialog.Header>
      <Dialog.Title>{isEditing ? 'Edit Role' : 'Tambah Role'}</Dialog.Title>
      <Dialog.Description>
        {isEditing ? 'Ubah data role di bawah ini beserta permission-nya.' : 'Masukkan data role baru beserta permission-nya.'}
      </Dialog.Description>
    </Dialog.Header>
    <form onsubmit={(e) => { e.preventDefault(); submit(); }}>
      <div class="grid gap-4 py-4">
        <div class="grid gap-2">
          <Label for="name">Nama Role</Label>
          <Input id="name" bind:value={form.name} />
          {#if form.errors.name}
            <span class="text-sm text-destructive">{form.errors.name}</span>
          {/if}
        </div>
        <div class="grid gap-2">
          <Label>Permissions</Label>
          <div class="grid grid-cols-2 gap-2 mt-2 border rounded-md p-3 max-h-60 overflow-y-auto">
            {#each permissions as permission}
              <div class="flex items-center space-x-2">
                <Checkbox
                  id={`permission-${permission.id}`}
                  checked={form.permissions.includes(permission.name)}
                  onCheckedChange={(v: boolean) => togglePermission(permission.name, v)}
                />
                <Label
                  for={`permission-${permission.id}`}
                  class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                >
                  {permission.name}
                </Label>
              </div>
            {/each}
          </div>
          {#if form.errors.permissions}
            <span class="text-sm text-destructive">{form.errors.permissions}</span>
          {/if}
        </div>
      </div>
      <Dialog.Footer>
        <Button type="submit" disabled={form.processing}>
          {form.processing ? 'Menyimpan...' : 'Simpan'}
        </Button>
      </Dialog.Footer>
    </form>
  </Dialog.Content>
</Dialog.Root>
