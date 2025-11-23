<script setup>
import { router, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  clientes: Object,
  filters: Object,
})

const page = usePage()
const can = (p) => (page.props?.auth?.permissions || []).includes(p)

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
        <h2 class="font-semibold text-xl leading-tight" style="color: var(--color-neutral);">Clientes</h2>
        <Link v-if="can('clientes.create')" :href="route('clientes.create')" 
              class="px-3 py-2 text-white rounded hover:opacity-90 transition" 
              style="background-color: var(--color-primary);">
          Nuevo
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="shadow sm:rounded-lg p-4" style="background-color: var(--color-base); border: 2px solid var(--color-neutral)">
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm" style="background-color: var(--color-base);">
              <thead>
                <tr class="text-left border-b" style="border-color: var(--color-neutral);">
                  <th class="p-2" style="color: var(--color-neutral);">ID</th>
                  <th class="p-2" style="color: var(--color-neutral);">Usuario</th>
                  <th class="p-2" style="color: var(--color-neutral);">CI</th>
                  <th class="p-2" style="color: var(--color-neutral);">Fecha nacimiento</th>
                  <th class="p-2" style="color: var(--color-neutral);">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="c in clientes.data" :key="c.id_cliente" class="border-b" style="border-color: var(--color-neutral);">
                  <td class="p-2" style="color: var(--color-neutral);">{{ c.id_cliente }}</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ c.user?.name }} ({{ c.user?.email }})</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ c.ci }}</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ c.fecha_nacimiento }}</td>
                  <td class="p-2 flex gap-2">
                    <Link 
                      v-if="can('clientes.update')" 
                      :href="route('clientes.edit', c.id_cliente)" 
                      class="hover:opacity-70 transition"
                      style="color: var(--color-primary);">
                      Editar
                    </Link>
                    <button 
                      v-if="can('clientes.delete')" 
                      @click="destroyItem(c.id_cliente)" 
                      class="hover:opacity-70 transition"
                      style="color: var(--color-error);">
                      Eliminar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="clientes.links?.length" class="mt-4 flex flex-wrap gap-1">
            <Link 
              v-for="l in clientes.links" 
              :key="l.url + l.label" 
              :href="l.url || '#'" 
              preserve-state 
              replace
              class="px-3 py-1 rounded border transition hover:opacity-80"
              :style="l.active 
                ? 'background-color: var(--color-primary); color: var(--color-base); border-color: var(--color-primary);' 
                : 'background-color: var(--color-base); color: var(--color-neutral); border-color: var(--color-neutral);'"
              v-html="l.label" 
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
