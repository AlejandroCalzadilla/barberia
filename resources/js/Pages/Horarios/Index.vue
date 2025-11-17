<script setup>
import { router, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'

const props = defineProps({
  horarios: Object,
  barberos: Array,
  filters: Object,
})

const barbero = ref(props.filters?.barbero || '')
const page = usePage()
const can = (p) => (page.props?.auth?.permissions || []).includes(p)

function search() {
  router.get(route('horarios.index'), { barbero: barbero.value }, { preserveState: true, replace: true })
}

function destroyItem(id) {
  if (confirm('¿Eliminar horario?')) {
    router.delete(route('horarios.destroy', id))
  }
}
</script>

<template>
  <AppLayout title="Horarios">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Horarios</h2>
        <Link v-if="can('horarios.create')" :href="route('horarios.create')" class="px-3 py-2 bg-indigo-600 text-white rounded">Nuevo</Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-4">
          <div class="flex gap-2 mb-4">
            <select v-model="barbero" class="border rounded px-3 py-2">
              <option value="">Todos los barberos</option>
              <option v-for="b in barberos" :key="b.id_barbero" :value="b.id_barbero">{{ b.user?.name }}</option>
            </select>
            <button @click="search" class="px-3 py-2 bg-gray-700 text-white rounded">Filtrar</button>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left border-b">
                  <th class="p-2">ID</th>
                  <th class="p-2">Barbero</th>
                  <th class="p-2">Día</th>
                  <th class="p-2">Inicio</th>
                  <th class="p-2">Fin</th>
                  <th class="p-2">Estado</th>
                  <th class="p-2">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="h in horarios.data" :key="h.id_horario" class="border-b">
                  <td class="p-2">{{ h.id_horario }}</td>
                  <td class="p-2">{{ h.barbero?.user?.name }}</td>
                  <td class="p-2">{{ h.dia_semana }}</td>
                  <td class="p-2">{{ h.hora_inicio }}</td>
                  <td class="p-2">{{ h.hora_fin }}</td>
                  <td class="p-2"><span class="px-2 py-1 rounded bg-gray-100">{{ h.estado }}</span></td>
                  <td class="p-2 flex gap-2">
                    <Link v-if="can('horarios.update')" :href="route('horarios.edit', h.id_horario)" class="text-indigo-600">Editar</Link>
                    <button v-if="can('horarios.delete')" @click="destroyItem(h.id_horario)" class="text-red-600">Eliminar</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="horarios.links?.length" class="mt-4 flex flex-wrap gap-1">
            <Link v-for="l in horarios.links" :key="l.url + l.label" :href="l.url || '#'" preserve-state replace
                  :class="['px-3 py-1 rounded border', l.active ? 'bg-indigo-600 text-white' : 'bg-white']" v-html="l.label" />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
