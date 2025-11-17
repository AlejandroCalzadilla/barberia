<script setup>
import { router, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  servicio: Object,
  categorias: Array,
})

const form = useForm({
  id_categoria: props.servicio.id_categoria ?? '',
  nombre: props.servicio.nombre ?? '',
  descripcion: props.servicio.descripcion ?? '',
  duracion_minutos: props.servicio.duracion_minutos ?? 30,
  precio: props.servicio.precio ?? 0,
  estado: props.servicio.estado ?? 'activo',
  imagen: props.servicio.imagen ?? '',
})

function submit() {
  form.put(route('servicios.update', props.servicio.id_servicio))
}
</script>

<template>
  <AppLayout :title="'Editar: ' + (props.servicio?.nombre || '')">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar servicio</h2>
        <Link :href="route('servicios.index')" class="px-3 py-2 bg-gray-200 rounded">Volver</Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-6 grid gap-4 md:grid-cols-2">
          <div>
            <label class="block text-sm font-medium mb-1">Categoría</label>
            <select v-model="form.id_categoria" class="w-full border rounded px-3 py-2">
              <option v-for="c in categorias" :key="c.id_categoria" :value="c.id_categoria">{{ c.nombre }}</option>
            </select>
            <div v-if="form.errors.id_categoria" class="text-sm text-red-600 mt-1">{{ form.errors.id_categoria }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Nombre</label>
            <input v-model="form.nombre" type="text" class="w-full border rounded px-3 py-2" />
            <div v-if="form.errors.nombre" class="text-sm text-red-600 mt-1">{{ form.errors.nombre }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Descripción</label>
            <textarea v-model="form.descripcion" class="w-full border rounded px-3 py-2"></textarea>
            <div v-if="form.errors.descripcion" class="text-sm text-red-600 mt-1">{{ form.errors.descripcion }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Duración (min)</label>
            <input v-model.number="form.duracion_minutos" type="number" min="1" class="w-full border rounded px-3 py-2" />
            <div v-if="form.errors.duracion_minutos" class="text-sm text-red-600 mt-1">{{ form.errors.duracion_minutos }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Precio</label>
            <input v-model.number="form.precio" type="number" step="0.01" min="0" class="w-full border rounded px-3 py-2" />
            <div v-if="form.errors.precio" class="text-sm text-red-600 mt-1">{{ form.errors.precio }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Estado</label>
            <select v-model="form.estado" class="w-full border rounded px-3 py-2">
              <option value="activo">Activo</option>
              <option value="inactivo">Inactivo</option>
            </select>
            <div v-if="form.errors.estado" class="text-sm text-red-600 mt-1">{{ form.errors.estado }}</div>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1">URL Imagen</label>
            <input v-model="form.imagen" type="text" class="w-full border rounded px-3 py-2" />
            <div v-if="form.errors.imagen" class="text-sm text-red-600 mt-1">{{ form.errors.imagen }}</div>
          </div>

          <div class="md:col-span-2 flex gap-2 mt-2">
            <button @click="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded">Guardar cambios</button>
            <Link :href="route('servicios.index')" class="px-4 py-2 bg-gray-200 rounded">Cancelar</Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
