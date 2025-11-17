<script setup>
import { router, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'

const props = defineProps({
  barberos: Object,
  filters: Object,
})

const q = ref(props.filters?.q || '')
const page = usePage()
const can = (p) => (page.props?.auth?.permissions || []).includes(p)

function search() {
  router.get(route('barberos.index'), { q: q.value }, { preserveState: true, replace: true })
}

function destroyItem(id) {
  if (confirm('¿Eliminar barbero?')) {
    router.delete(route('barberos.destroy', id))
  }
}
</script>

<template>
  <AppLayout title="Barberos">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" style="color: var(--color-neutral);">Barberos</h2>
        <Link v-if="can('barberos.create')" :href="route('barberos.create')" 
              class="px-3 py-2 text-white rounded hover:opacity-90 transition" 
              style="background-color: var(--color-primary);">
          Nuevo
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="shadow sm:rounded-lg p-4" style="background-color: var(--color-base);">
          <div class="flex gap-2 mb-4">
            <input 
              v-model="q" 
              @keyup.enter="search" 
              type="text" 
              placeholder="Buscar por nombre o email" 
              class="border rounded px-3 py-2 w-full" 
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral);"
            />
            <button 
              @click="search" 
              class="px-3 py-2 text-white rounded hover:opacity-90 transition"
              style="background-color: var(--color-secondary);">
              Buscar
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full text-sm " style="background-color: var(--color-secondary);">
              <thead>
                <tr class="text-left border-b" style="border-color: var(--color-neutral); opacity: 0.3;">
                  <th class="p-2" style="color: var(--color-neutral);">ID</th>
                  <th class="p-2" style="color: var(--color-neutral);">Usuario</th>
                  <th class="p-2" style="color: var(--color-neutral);">Especialidad</th>
                  <th class="p-2" style="color: var(--color-neutral);">Estado</th>
                  <th class="p-2" style="color: var(--color-neutral);">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="b in barberos.data" :key="b.id_barbero" class="border-b" style="border-color: var(--color-neutral); opacity: 0.2;">
                  <td class="p-2" style="color: var(--color-neutral);">{{ b.id_barbero }}</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ b.user?.name }} ({{ b.user?.email }})</td>
                  <td class="p-2" style="color: var(--color-neutral);">{{ b.especialidad }}</td>
                  <td class="p-2">
                    <span class="px-2 py-1 rounded" style="background-color: var(--color-accent); color: var(--color-base); opacity: 0.8;">
                      {{ b.estado }}
                    </span>
                  </td>
                  <td class="p-2 flex gap-2">
                    <Link 
                      v-if="can('barberos.update')" 
                      :href="route('barberos.edit', b.id_barbero)" 
                      class="hover:opacity-70 transition"
                      style="color: var(--color-primary);">
                      Editar
                    </Link>
                    <button 
                      v-if="can('barberos.delete')" 
                      @click="destroyItem(b.id_barbero)" 
                      class="hover:opacity-70 transition"
                      style="color: var(--color-error);">
                      Eliminar
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="barberos.links?.length" class="mt-4 flex flex-wrap gap-1">
            <Link 
              v-for="l in barberos.links" 
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
