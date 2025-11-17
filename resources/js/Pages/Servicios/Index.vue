<script setup>
import { router, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'

const props = defineProps({
  servicios: Object,
  categorias: Array,
  filters: Object,
})

const q = ref(props.filters?.q || '')
const categoria = ref(props.filters?.categoria || '')
const page = usePage()
const can = (p) => (page.props?.auth?.permissions || []).includes(p)

function search() {
  router.get(route('servicios.index'), { q: q.value, categoria: categoria.value }, { preserveState: true, replace: true })
}

function destroyItem(id) {
  if (confirm('¿Eliminar servicio?')) {
    router.delete(route('servicios.destroy', id))
  }
}
</script>

<template>
  <AppLayout title="Servicios">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Servicios</h2>
        <Link v-if="can('servicios.create')" :href="route('servicios.create')" class="px-3 py-2 bg-indigo-600 text-white rounded">Nuevo</Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-4">
          <div class="flex gap-2 mb-4">
            <input v-model="q" @keyup.enter="search" type="text" placeholder="Buscar por nombre" class="border rounded px-3 py-2 w-full" />
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
                  <th class="p-2">Nombre</th>
                  <th class="p-2">Categoría</th>
                  <th class="p-2">Duración</th>
                  <th class="p-2">Precio</th>
                  <th class="p-2">Estado</th>
                  <th class="p-2">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="s in servicios.data" :key="s.id_servicio" class="border-b">
                  <td class="p-2">{{ s.id_servicio }}</td>
                  <td class="p-2">{{ s.nombre }}</td>
                  <td class="p-2">{{ s.categoria?.nombre }}</td>
                  <td class="p-2">{{ s.duracion_minutos }} min</td>
                  <td class="p-2">{{ s.precio }}</td>
                  <td class="p-2"><span class="px-2 py-1 rounded bg-gray-100">{{ s.estado }}</span></td>
                  <td class="p-2 flex gap-2">
                    <Link v-if="can('servicios.update')" :href="route('servicios.edit', s.id_servicio)" class="text-indigo-600">Editar</Link>
                    <button v-if="can('servicios.delete')" @click="destroyItem(s.id_servicio)" class="text-red-600">Eliminar</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="servicios.links?.length" class="mt-4 flex flex-wrap gap-1">
            <Link v-for="l in servicios.links" :key="l.url + l.label" :href="l.url || '#'" preserve-state replace
                  :class="['px-3 py-1 rounded border', l.active ? 'bg-indigo-600 text-white' : 'bg-white']" v-html="l.label" />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
