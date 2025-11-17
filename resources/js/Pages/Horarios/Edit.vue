<script setup>
import { router, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  horario: Object,
  barberos: Array,
})

const form = useForm({
  id_barbero: props.horario.id_barbero ?? '',
  dia_semana: props.horario.dia_semana ?? 'lunes',
  hora_inicio: props.horario.hora_inicio ?? '09:00',
  hora_fin: props.horario.hora_fin ?? '18:00',
  estado: props.horario.estado ?? 'activo',
})

function submit() {
  form.put(route('horarios.update', props.horario.id_horario))
}
</script>

<template>
  <AppLayout :title="'Editar horario: ' + (props.horario?.barbero?.user?.name || '')">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar horario</h2>
        <Link :href="route('horarios.index')" class="px-3 py-2 bg-gray-200 rounded">Volver</Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Barbero</label>
            <select v-model="form.id_barbero" class="w-full border rounded px-3 py-2">
              <option v-for="b in barberos" :key="b.id_barbero" :value="b.id_barbero">{{ b.user?.name }}</option>
            </select>
            <div v-if="form.errors.id_barbero" class="text-sm text-red-600 mt-1">{{ form.errors.id_barbero }}</div>
          </div>

          <div class="grid md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Día</label>
              <select v-model="form.dia_semana" class="w-full border rounded px-3 py-2">
                <option value="lunes">Lunes</option>
                <option value="martes">Martes</option>
                <option value="miercoles">Miércoles</option>
                <option value="jueves">Jueves</option>
                <option value="viernes">Viernes</option>
                <option value="sabado">Sábado</option>
                <option value="domingo">Domingo</option>
              </select>
              <div v-if="form.errors.dia_semana" class="text-sm text-red-600 mt-1">{{ form.errors.dia_semana }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Hora inicio</label>
              <input v-model="form.hora_inicio" type="time" class="w-full border rounded px-3 py-2" />
              <div v-if="form.errors.hora_inicio" class="text-sm text-red-600 mt-1">{{ form.errors.hora_inicio }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Hora fin</label>
              <input v-model="form.hora_fin" type="time" class="w-full border rounded px-3 py-2" />
              <div v-if="form.errors.hora_fin" class="text-sm text-red-600 mt-1">{{ form.errors.hora_fin }}</div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Estado</label>
            <select v-model="form.estado" class="w-full border rounded px-3 py-2">
              <option value="activo">Activo</option>
              <option value="inactivo">Inactivo</option>
            </select>
            <div v-if="form.errors.estado" class="text-sm text-red-600 mt-1">{{ form.errors.estado }}</div>
          </div>

          <div class="flex gap-2">
            <button @click="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded">Guardar cambios</button>
            <Link :href="route('horarios.index')" class="px-4 py-2 bg-gray-200 rounded">Cancelar</Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
