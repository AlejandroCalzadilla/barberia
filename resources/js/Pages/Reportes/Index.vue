<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import FullReportsChart from '@/Components/FullReportsChart.vue'
import { ref, onMounted } from 'vue'

const props = defineProps({
  pagosData: Array,
  reservasData: Array,
  ingresosMensuales: Object,
  rankingBarberos: Array,
  serviciosMasPopulares: Array,
  clientesFrecuentes: Array,
  distribucionEstados: Object,
  distribucionMetodosPago: Array,
})

console.log('📋 Reportes/Index - Props recibidos:', {
  pagosData: props.pagosData,
  reservasData: props.reservasData,
  ingresosMensuales: props.ingresosMensuales
})

const fechaInicio = ref(new Date(new Date().setMonth(new Date().getMonth() - 1)).toISOString().split('T')[0])
const fechaFin = ref(new Date().toISOString().split('T')[0])
const filtroAplicado = ref(false)

function aplicarFiltros() {
  filtroAplicado.value = !filtroAplicado.value
}
</script>

<template>
  <AppLayout title="Reportes y Estadísticas">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl leading-tight" :style="{ color: 'var(--color-neutral)' }">
          Reportes y Estadísticas
        </h2>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Filtros -->
        <div class="shadow sm:rounded-lg p-6 mb-6" :style="{ backgroundColor: 'var(--color-base)' }">
          <h3 class="text-lg font-semibold mb-4" :style="{ color: 'var(--color-neutral)' }">
            Filtros
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                Fecha Inicio
              </label>
              <input 
                v-model="fechaInicio" 
                type="date" 
                class="w-full rounded px-3 py-2 transition"
                :style="{ 
                  borderColor: 'var(--color-neutral)',
                  borderWidth: '1px',
                  backgroundColor: 'var(--color-base)',
                  color: 'var(--color-neutral)'
                }"
              />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1" :style="{ color: 'var(--color-neutral)' }">
                Fecha Fin
              </label>
              <input 
                v-model="fechaFin" 
                type="date"
                class="w-full rounded px-3 py-2 transition"
                :style="{ 
                  borderColor: 'var(--color-neutral)',
                  borderWidth: '1px',
                  backgroundColor: 'var(--color-base)',
                  color: 'var(--color-neutral)'
                }"
              />
            </div>
            <div class="flex items-end">
              <button 
                @click="aplicarFiltros"
                class="w-full px-4 py-2 rounded transition font-medium"
                :style="{ 
                  backgroundColor: 'var(--color-primary)',
                  color: 'var(--color-base)'
                }"
              >
                Aplicar Filtros
              </button>
            </div>
          </div>
        </div>

        <!-- Gráficos y Reportes -->
        <FullReportsChart 
          :pagos-data="pagosData" 
          :reservas-data="reservasData"
          :ingresos-mensuales="ingresosMensuales"
          :ranking-barberos="rankingBarberos"
          :servicios-mas-populares="serviciosMasPopulares"
          :clientes-frecuentes="clientesFrecuentes"
          :distribucion-estados="distribucionEstados"
          :distribucion-metodos-pago="distribucionMetodosPago"
        />
      </div>
    </div>
  </AppLayout>
</template>
