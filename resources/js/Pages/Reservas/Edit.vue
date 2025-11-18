<script setup>
import { router, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  reserva: Object,
  clientes: Array,
  barberos: Array,
  servicios: Array,
})

const form = useForm({
  id_cliente: props.reserva.id_cliente ?? '',
  id_barbero: props.reserva.id_barbero ?? '',
  id_servicio: props.reserva.id_servicio ?? '',
  fecha_reserva: props.reserva.fecha_reserva ?? '',
  hora_inicio: props.reserva.hora_inicio ?? '09:00',
  hora_fin: props.reserva.hora_fin ?? '10:00',
  notas: props.reserva.notas ?? '',
  precio_servicio: props.reserva.precio_servicio ?? 0,
  monto_anticipo: props.reserva.monto_anticipo ?? 0,
  porcentaje_anticipo: props.reserva.porcentaje_anticipo ?? 0,
  estado: props.reserva.estado ?? 'pendiente_pago',
})

function onServicioChange() {
  const s = props.servicios.find(x => x.id_servicio === form.id_servicio)
  if (s && s.precio != null) form.precio_servicio = s.precio
}

function submit() {
  form.put(route('reservas.update', props.reserva.id_reserva))
}
</script>

<template>
  <AppLayout :title="'Editar reserva #' + (props.reserva?.id_reserva || '')">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" style="color: var(--color-neutral);">Editar reserva</h2>
        <Link 
          :href="route('reservas.index')" 
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
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Cliente</label>
            <select 
              v-model="form.id_cliente" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            >
              <option v-for="c in clientes" :key="c.id_cliente" :value="c.id_cliente">{{ c.user?.name }}</option>
            </select>
            <div v-if="form.errors.id_cliente" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.id_cliente }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Barbero</label>
            <select 
              v-model="form.id_barbero" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            >
              <option v-for="b in barberos" :key="b.id_barbero" :value="b.id_barbero">{{ b.user?.name }}</option>
            </select>
            <div v-if="form.errors.id_barbero" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.id_barbero }}</div>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Servicio</label>
            <select 
              v-model="form.id_servicio" 
              @change="onServicioChange" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            >
              <option v-for="s in servicios" :key="s.id_servicio" :value="s.id_servicio">{{ s.nombre }}</option>
            </select>
            <div v-if="form.errors.id_servicio" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.id_servicio }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Fecha</label>
            <input 
              v-model="form.fecha_reserva" 
              type="date" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.fecha_reserva" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.fecha_reserva }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Hora inicio</label>
            <input 
              v-model="form.hora_inicio" 
              type="time" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.hora_inicio" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.hora_inicio }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Hora fin</label>
            <input 
              v-model="form.hora_fin" 
              type="time" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.hora_fin" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.hora_fin }}</div>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Notas</label>
            <textarea 
              v-model="form.notas" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            ></textarea>
            <div v-if="form.errors.notas" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.notas }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Precio servicio</label>
            <input 
              v-model.number="form.precio_servicio" 
              type="number" 
              step="0.01" 
              min="0" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.precio_servicio" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.precio_servicio }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Monto anticipo</label>
            <input 
              v-model.number="form.monto_anticipo" 
              type="number" 
              step="0.01" 
              min="0" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.monto_anticipo" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.monto_anticipo }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">% Anticipo</label>
            <input 
              v-model.number="form.porcentaje_anticipo" 
              type="number" 
              step="0.01" 
              min="0" 
              max="100" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            />
            <div v-if="form.errors.porcentaje_anticipo" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.porcentaje_anticipo }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1" style="color: var(--color-neutral);">Estado</label>
            <select 
              v-model="form.estado" 
              class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 transition"
              style="background-color: var(--color-base); border-color: var(--color-neutral); color: var(--color-neutral); opacity: 0.5;"
              :style="{'--tw-ring-color': 'var(--color-primary)'}"
            >
              <option value="pendiente_pago">Pendiente de pago</option>
              <option value="confirmada">Confirmada</option>
              <option value="en_proceso">En proceso</option>
              <option value="completada">Completada</option>
              <option value="cancelada">Cancelada</option>
              <option value="no_asistio">No asistió</option>
            </select>
            <div v-if="form.errors.estado" class="text-sm mt-1" style="color: var(--color-error);">{{ form.errors.estado }}</div>
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
              :href="route('reservas.index')" 
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
