<script setup>
import { router, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'

const props = defineProps({
  servicio: Object,
})

const previewImage = ref(props.servicio.imagen || null)

const form = useForm({
  nombre: props.servicio.nombre ?? '',
  descripcion: props.servicio.descripcion ?? '',
  duracion_minutos: props.servicio.duracion_minutos ?? 30,
  precio: props.servicio.precio ?? 0,
  estado: props.servicio.estado ?? 'activo',
  imagen: null,
})

function handleImageChange(event) {
  const file = event.target.files[0]
  if (file) {
    form.imagen = file
    // Mostrar preview
    const reader = new FileReader()
    reader.onload = (e) => {
      previewImage.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

function submit() {
  form.post(route('servicios.update', props.servicio.id_servicio), {
    method: 'put',
  })
}
</script>

<template>
  <AppLayout :title="'Editar: ' + (props.servicio?.nombre || '')">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" style="color: var(--color-neutral);">Editar servicio</h2>
        <Link 
          :href="route('servicios.index')" 
          class="px-3 py-2 rounded hover:opacity-90 transition"
          style="background-color: var(--color-secondary); color: var(--color-base);">
          Volver
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="shadow sm:rounded-lg p-6 grid gap-4 md:grid-cols-2" style="background-color: var(--color-base);">
          
          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Nombre</label>
            <input 
              v-model="form.nombre" 
              type="text" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.nombre" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.nombre }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Descripción</label>
            <textarea 
              v-model="form.descripcion" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            ></textarea>
            <div v-if="form.errors.descripcion" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.descripcion }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Duración (min)</label>
            <input 
              v-model.number="form.duracion_minutos" 
              type="number" 
              min="1" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.duracion_minutos" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.duracion_minutos }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Precio</label>
            <input 
              v-model.number="form.precio" 
              type="number" 
              step="0.01" 
              min="0" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.precio" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.precio }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Estado</label>
            <select 
              v-model="form.estado" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            >
              <option value="activo">Activo</option>
              <option value="inactivo">Inactivo</option>
            </select>
            <div v-if="form.errors.estado" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.estado }}</div>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Imagen</label>
            <input 
              type="file" 
              accept="image/*" 
              @change="handleImageChange"
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral);"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.imagen" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.imagen }}</div>
            <div v-if="previewImage" class="mt-3">
              <img :src="previewImage" alt="Preview" class="w-full h-auto rounded max-w-xs" />
            </div>
          </div>

          <div class="md:col-span-2 flex gap-2 mt-2">
            <button 
              @click="submit" 
              :disabled="form.processing" 
              class="px-4 py-2 text-white rounded hover:opacity-90 transition disabled:opacity-50"
              style="background-color: var(--color-primary);">
              Guardar cambios
            </button>
            <Link 
              :href="route('servicios.index')" 
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
