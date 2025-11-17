<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Components/Welcome.vue';
import ThemeSelector from '@/Components/ThemeSelector.vue';
import ThemeTest from '@/Components/ThemeTest.vue';
import { useTheme } from '@/composables/useTheme.js';
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

// Usar el composable global del tema
const { theme } = useTheme();
console.log('Tema actual:', theme);

const page = usePage()
const permissions = computed(() => page.props?.auth?.permissions || [])
const can = (p) => permissions.value.includes(p)

const links = computed(() => [
  { key: 'categorias', label: 'Categorías', route: 'categorias.index', perm: 'categorias.view' },
  { key: 'productos', label: 'Productos', route: 'productos.index', perm: 'productos.view' },
  { key: 'servicios', label: 'Servicios', route: 'servicios.index', perm: 'servicios.view' },
  { key: 'barberos', label: 'Barberos', route: 'barberos.index', perm: 'barberos.view' },
  { key: 'clientes', label: 'Clientes', route: 'clientes.index', perm: 'clientes.view' },
  { key: 'horarios', label: 'Horarios', route: 'horarios.index', perm: 'horarios.view' },
  { key: 'reservas', label: 'Reservas', route: 'reservas.index', perm: 'reservas.view' },
  { key: 'pagos', label: 'Pagos', route: 'pagos.index', perm: 'pagos.view' },
])
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard
            </h2>
            <ThemeSelector />
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
              <!--   <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <Welcome />
                </div> -->
                
                <!-- Componente de prueba de temas -->
              <!--   <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    <ThemeTest />
                </div> -->

                <!-- Accesos rápidos por permisos -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow sm:rounded-lg p-6">
                  <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-100">Accesos rápidos</h3>
                  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    <template v-for="item in links" :key="item.key">
                      <Link v-if="can(item.perm)"
                            :href="route(item.route)"
                            class="block p-4 rounded border border-gray-200 dark:border-gray-700 hover:border-indigo-400 dark:hover:border-indigo-400 transition-colors bg-white dark:bg-gray-900">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Gestión</div>
                        <div class="mt-1 text-base font-medium text-gray-900 dark:text-gray-100">{{ item.label }}</div>
                      </Link>
                    </template>
                  </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

