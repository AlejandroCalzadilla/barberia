<script setup>
import { router, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  barbero: Object,
  usuarios: Array,
})

const form = useForm({
  id_usuario: props.barbero.id_usuario ?? '',
  especialidad: props.barbero.especialidad ?? '',
  foto_perfil: props.barbero.foto_perfil ?? '',
  calificacion_promedio: props.barbero.calificacion_promedio ?? 0,
  estado: props.barbero.estado ?? 'disponible',
})

function submit() {
  form.put(route('barberos.update', props.barbero.id_barbero))
}
</script>

<template>
  <AppLayout :title="'Editar barbero: ' + (props.barbero?.user?.name || '')">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" style="color: var(--color-neutral);">Editar barbero</h2>
        <Link 
          :href="route('barberos.index')" 
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
              <option v-for="u in usuarios" :key="u.id" :value="u.id">
                {{ u.name }} ({{ u.email }})
              </option>
            </select>
            <div v-if="form.errors.id_usuario" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.id_usuario }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Especialidad</label>
            <input 
              v-model="form.especialidad" 
              type="text" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition" 
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.especialidad" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.especialidad }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Foto perfil (URL)</label>
            <input 
              v-model="form.foto_perfil" 
              type="text" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition" 
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.foto_perfil" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.foto_perfil }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Calificación promedio</label>
            <input 
              v-model.number="form.calificacion_promedio" 
              type="number" 
              min="0" 
              max="5" 
              step="0.01" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition" 
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.calificacion_promedio" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.calificacion_promedio }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Estado</label>
            <select 
              v-model="form.estado" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}">
              <option value="disponible">Disponible</option>
              <option value="ocupado">Ocupado</option>
              <option value="ausente">Ausente</option>
            </select>
            <div v-if="form.errors.estado" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.estado }}</div>
          </div>

          <div class="flex gap-2">
            <button 
              @click="submit" 
              :disabled="form.processing" 
              class="px-4 py-2 text-white rounded hover:opacity-90 transition disabled:opacity-50"
              style="background-color: var(--color-primary);">
              Guardar cambios
            </button>
            <Link 
              :href="route('barberos.index')" 
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
