<script setup>
import { router, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  cliente: Object,
  usuarios: Array,
})

const form = useForm({
  id_usuario: props.cliente.id_usuario ?? '',
  fecha_nacimiento: props.cliente.fecha_nacimiento ?? '',
  ci: props.cliente.ci ?? '',
})

function submit() {
  form.put(route('clientes.update', props.cliente.id_cliente))
}
</script>

<template>
  <AppLayout :title="'Editar cliente: ' + (props.cliente?.user?.name || '')">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar cliente</h2>
        <Link :href="route('clientes.index')" class="px-3 py-2 bg-gray-200 rounded">Volver</Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Usuario</label>
            <select v-model="form.id_usuario" class="w-full border rounded px-3 py-2">
              <option v-for="u in usuarios" :key="u.id" :value="u.id">
                {{ u.name }} ({{ u.email }})
              </option>
            </select>
            <div v-if="form.errors.id_usuario" class="text-sm text-red-600 mt-1">{{ form.errors.id_usuario }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Fecha de nacimiento</label>
            <input v-model="form.fecha_nacimiento" type="date" class="w-full border rounded px-3 py-2" />
            <div v-if="form.errors.fecha_nacimiento" class="text-sm text-red-600 mt-1">{{ form.errors.fecha_nacimiento }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">CI</label>
            <input v-model="form.ci" type="text" class="w-full border rounded px-3 py-2" />
            <div v-if="form.errors.ci" class="text-sm text-red-600 mt-1">{{ form.errors.ci }}</div>
          </div>

          <div class="flex gap-2">
            <button @click="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded">Guardar cambios</button>
            <Link :href="route('clientes.index')" class="px-4 py-2 bg-gray-200 rounded">Cancelar</Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
