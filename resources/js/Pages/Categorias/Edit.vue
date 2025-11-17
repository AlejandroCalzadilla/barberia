<script setup>
import { router, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  categoria: Object,
})

const form = useForm({
  nombre: props.categoria.nombre ?? '',
  descripcion: props.categoria.descripcion ?? '',
  estado: props.categoria.estado ?? 'activa',
})

function submit() {
  form.put(route('categorias.update', props.categoria.id_categoria))
}
</script>

<template>
  <AppLayout :title="'Editar: ' + (props.categoria?.nombre || '')">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar categoría</h2>
        <Link :href="route('categorias.index')" class="px-3 py-2 bg-gray-200 rounded">Volver</Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
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
            <label class="block text-sm font-medium mb-1">Estado</label>
            <select v-model="form.estado" class="w-full border rounded px-3 py-2">
              <option value="activa">Activa</option>
              <option value="inactiva">Inactiva</option>
            </select>
            <div v-if="form.errors.estado" class="text-sm text-red-600 mt-1">{{ form.errors.estado }}</div>
          </div>

          <div class="flex gap-2">
            <button @click="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded">
              Guardar cambios
            </button>
            <Link :href="route('categorias.index')" class="px-4 py-2 bg-gray-200 rounded">Cancelar</Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
