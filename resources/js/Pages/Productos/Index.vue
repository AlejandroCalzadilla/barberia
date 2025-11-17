<script setup>
import { router, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'

const props = defineProps({
  productos: Object,
  categorias: Array,
  filters: Object,
})

const q = ref(props.filters?.q || '')
const categoria = ref(props.filters?.categoria || '')
const page = usePage()
const can = (p) => (page.props?.auth?.permissions || []).includes(p)

function search() {
  router.get(route('productos.index'), { q: q.value, categoria: categoria.value }, { preserveState: true, replace: true })
}

function destroyItem(id) {
  if (confirm('¿Eliminar producto?')) {
    router.delete(route('productos.destroy', id))
  }
}
</script>

<template>
  <AppLayout title="Productos">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Productos</h2>
        <Link v-if="can('productos.create')" :href="route('productos.create')" class="px-3 py-2 bg-indigo-600 text-white rounded">Nuevo</Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-4">
          <div class="flex gap-2 mb-4">
            <input v-model="q" @keyup.enter="search" type="text" placeholder="Buscar por nombre/código" class="border rounded px-3 py-2 w-full" />
            <select v-model="categoria" class="border rounded px-3 py-2">
              <option value="">Todas</option>
              <option v-for="c in categorias" :key="c.id_categoria" :value="c.id_categoria">{{ c.nombre }}</option>
            </select>
            <button @click="search" class="px-3 py-2 bg-gray-700 text-white rounded">Buscar</button>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left border-b">
                  <th class="p-2">ID</th>
                  <th class="p-2">Código</th>
                  <th class="p-2">Nombre</th>
                  <th class="p-2">Categoría</th>
                  <th class="p-2">Precio</th>
                  <th class="p-2">Estado</th>
                  <th class="p-2">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in productos.data" :key="p.id_producto" class="border-b">
                  <td class="p-2">{{ p.id_producto }}</td>
                  <td class="p-2">{{ p.codigo }}</td>
                  <td class="p-2">{{ p.nombre }}</td>
                  <td class="p-2">{{ p.categoria?.nombre }}</td>
                  <td class="p-2">{{ p.precio_venta }}</td>
                  <td class="p-2"><span class="px-2 py-1 rounded bg-gray-100">{{ p.estado }}</span></td>
                  <td class="p-2 flex gap-2">
                    <Link v-if="can('productos.update')" :href="route('productos.edit', p.id_producto)" class="text-indigo-600">Editar</Link>
                    <button v-if="can('productos.delete')" @click="destroyItem(p.id_producto)" class="text-red-600">Eliminar</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="productos.links?.length" class="mt-4 flex flex-wrap gap-1">
            <Link v-for="l in productos.links" :key="l.url + l.label" :href="l.url || '#'" preserve-state replace
                  :class="['px-3 py-1 rounded border', l.active ? 'bg-indigo-600 text-white' : 'bg-white']" v-html="l.label" />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
