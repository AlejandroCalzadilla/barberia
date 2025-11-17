<script setup>
import { router, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'

const props = defineProps({
  reservas: Object,
  clientes: Array,
  barberos: Array,
  servicios: Array,
  filters: Object,
})

const cliente = ref(props.filters?.cliente || '')
const barbero = ref(props.filters?.barbero || '')
const estado = ref(props.filters?.estado || '')
const fecha = ref(props.filters?.fecha || '')
const page = usePage()
const can = (p) => (page.props?.auth?.permissions || []).includes(p)

function search() {
  router.get(route('reservas.index'), { cliente: cliente.value, barbero: barbero.value, estado: estado.value, fecha: fecha.value }, { preserveState: true, replace: true })
}

function destroyItem(id) {
  if (confirm('¿Eliminar reserva?')) {
    router.delete(route('reservas.destroy', id))
  }
}
</script>

<template>
  <AppLayout title="Reservas">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reservas</h2>
        <Link v-if="can('reservas.create')" :href="route('reservas.create')" class="px-3 py-2 bg-indigo-600 text-white rounded">Nueva</Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-4">
          <div class="grid md:grid-cols-4 gap-2 mb-4">
            <select v-model="cliente" class="border rounded px-3 py-2">
              <option value="">Todos los clientes</option>
              <option v-for="c in clientes" :key="c.id_cliente" :value="c.id_cliente">{{ c.user?.name }}</option>
            </select>
            <select v-model="barbero" class="border rounded px-3 py-2">
              <option value="">Todos los barberos</option>
              <option v-for="b in barberos" :key="b.id_barbero" :value="b.id_barbero">{{ b.user?.name }}</option>
            </select>
            <select v-model="estado" class="border rounded px-3 py-2">
              <option value="">Todos los estados</option>
              <option value="pendiente_pago">Pendiente de pago</option>
              <option value="confirmada">Confirmada</option>
              <option value="en_proceso">En proceso</option>
              <option value="completada">Completada</option>
              <option value="cancelada">Cancelada</option>
              <option value="no_asistio">No asistió</option>
            </select>
            <div class="flex gap-2">
              <input v-model="fecha" type="date" class="border rounded px-3 py-2 w-full" />
              <button @click="search" class="px-3 py-2 bg-gray-700 text-white rounded">Filtrar</button>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left border-b">
                  <th class="p-2">ID</th>
                  <th class="p-2">Cliente</th>
                  <th class="p-2">Barbero</th>
                  <th class="p-2">Servicio</th>
                  <th class="p-2">Fecha</th>
                  <th class="p-2">Horario</th>
                  <th class="p-2">Estado</th>
                  <th class="p-2">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in reservas.data" :key="r.id_reserva" class="border-b">
                  <td class="p-2">{{ r.id_reserva }}</td>
                  <td class="p-2">{{ r.cliente?.user?.name }}</td>
                  <td class="p-2">{{ r.barbero?.user?.name }}</td>
                  <td class="p-2">{{ r.servicio?.nombre }}</td>
                  <td class="p-2">{{ r.fecha_reserva }}</td>
                  <td class="p-2">{{ r.hora_inicio }} - {{ r.hora_fin }}</td>
                  <td class="p-2"><span class="px-2 py-1 rounded bg-gray-100">{{ r.estado }}</span></td>
                  <td class="p-2 flex gap-2">
                    <Link v-if="can('reservas.update')" :href="route('reservas.edit', r.id_reserva)" class="text-indigo-600">Editar</Link>
                    <button v-if="can('reservas.delete')" @click="destroyItem(r.id_reserva)" class="text-red-600">Eliminar</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="reservas.links?.length" class="mt-4 flex flex-wrap gap-1">
            <Link v-for="l in reservas.links" :key="l.url + l.label" :href="l.url || '#'" preserve-state replace
                  :class="['px-3 py-1 rounded border', l.active ? 'bg-indigo-600 text-white' : 'bg-white']" v-html="l.label" />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
