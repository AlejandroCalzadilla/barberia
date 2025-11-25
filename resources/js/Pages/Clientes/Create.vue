<script setup>
import { router, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  usuarios: Array,
})

const form = useForm({
  id_usuario: '',
  fecha_nacimiento: '',
  ci: '',
})

function submit() {
  form.post(route('clientes.store'))
}
</script>

<template>
  <AppLayout title="Nuevo cliente">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" style="color: var(--color-neutral);">Nuevo cliente</h2>
        <Link 
          :href="route('clientes.index')" 
          class="px-3 py-2 rounded hover:opacity-90 transition"
          style="background-color: var(--color-secondary); color: var(--color-base);">
          Volver
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="shadow sm:rounded-lg p-6 space-y-4" style="background-color: var(--color-base);">
          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Usuario</label>
            <select 
              v-model="form.id_usuario" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}">
              <option value="" disabled>Seleccione...</option>
              <option v-for="u in usuarios" :key="u.id" :value="u.id">
                {{ u.name }} ({{ u.email }})
              </option>
            </select>
            <div v-if="form.errors.id_usuario" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.id_usuario }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Fecha de nacimiento</label>
            <input 
              v-model="form.fecha_nacimiento" 
              type="date" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}" />
            <div v-if="form.errors.fecha_nacimiento" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.fecha_nacimiento }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">CI</label>
            <input 
              v-model="form.ci" 
              type="text" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}" />
            <div v-if="form.errors.ci" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.ci }}</div>
          </div>

          <div class="flex gap-2">
            <button 
              @click="submit" 
              :disabled="form.processing" 
              class="px-4 py-2 text-white rounded hover:opacity-90 transition disabled:opacity-50"
              style="background-color: var(--color-primary);">
              Guardar
            </button>
            <Link 
              :href="route('clientes.index')" 
              class="px-4 py-2 rounded hover:opacity-90 transition"
              style="background-color: var(--color-secondary); color: var(--color-base);">
              Cancelar
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
