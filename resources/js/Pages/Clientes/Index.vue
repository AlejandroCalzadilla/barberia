<script setup>
import { router, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'

const props = defineProps({
  clientes: Object,
  filters: Object,
})

const q = ref(props.filters?.q || '')
const page = usePage()
const can = (p) => (page.props?.auth?.permissions || []).includes(p)

function search() {
  router.get(route('clientes.index'), { q: q.value }, { preserveState: true, replace: true })
}

function destroyItem(id) {
  if (confirm('¿Eliminar cliente?')) {
    router.delete(route('clientes.destroy', id))
  }
}
</script>

<template>
  <AppLayout title="Clientes">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Clientes</h2>
        <Link v-if="can('clientes.create')" :href="route('clientes.create')" class="px-3 py-2 bg-indigo-600 text-white rounded">Nuevo</Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-4">
          <div class="flex gap-2 mb-4">
            <input v-model="q" @keyup.enter="search" type="text" placeholder="Buscar por nombre o email" class="border rounded px-3 py-2 w-full" />
            <button @click="search" class="px-3 py-2 bg-gray-700 text-white rounded">Buscar</button>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left border-b">
                  <th class="p-2">ID</th>
                  <th class="p-2">Usuario</th>
                  <th class="p-2">CI</th>
                  <th class="p-2">Fecha nacimiento</th>
                  <th class="p-2">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="c in clientes.data" :key="c.id_cliente" class="border-b">
                  <td class="p-2">{{ c.id_cliente }}</td>
                  <td class="p-2">{{ c.user?.name }} ({{ c.user?.email }})</td>
                  <td class="p-2">{{ c.ci }}</td>
                  <td class="p-2">{{ c.fecha_nacimiento }}</td>
                  <td class="p-2 flex gap-2">
                    <Link v-if="can('clientes.update')" :href="route('clientes.edit', c.id_cliente)" class="text-indigo-600">Editar</Link>
                    <button v-if="can('clientes.delete')" @click="destroyItem(c.id_cliente)" class="text-red-600">Eliminar</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="clientes.links?.length" class="mt-4 flex flex-wrap gap-1">
            <Link v-for="l in clientes.links" :key="l.url + l.label" :href="l.url || '#'" preserve-state replace
                  :class="['px-3 py-1 rounded border', l.active ? 'bg-indigo-600 text-white' : 'bg-white']" v-html="l.label" />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
