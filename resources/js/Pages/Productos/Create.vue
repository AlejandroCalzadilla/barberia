<script setup>
import { router, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  categorias: Array,
})

const form = useForm({
  id_categoria: '',
  codigo: '',
  nombre: '',
  descripcion: '',
  precio_compra: 0,
  precio_venta: 0,
  stock_actual: 0,
  stock_minimo: 0,
  unidad_medida: '',
  estado: 'activo',
  imagenurl: '',
})

function submit() {
  form.post(route('productos.store'))
}
</script>

<template>
  <AppLayout title="Nuevo producto">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo producto</h2>
        <Link :href="route('productos.index')" class="px-3 py-2 bg-gray-200 rounded">Volver</Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg p-6 grid gap-4 md:grid-cols-2">
          <div>
            <label class="block text-sm font-medium mb-1">Categoría</label>
            <select v-model="form.id_categoria" class="w-full border rounded px-3 py-2">
              <option value="" disabled>Seleccione...</option>
              <option v-for="c in categorias" :key="c.id_categoria" :value="c.id_categoria">{{ c.nombre }}</option>
            </select>
            <div v-if="form.errors.id_categoria" class="text-sm text-red-600 mt-1">{{ form.errors.id_categoria }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Código</label>
            <input v-model="form.codigo" type="text" class="w-full border rounded px-3 py-2" />
            <div v-if="form.errors.codigo" class="text-sm text-red-600 mt-1">{{ form.errors.codigo }}</div>
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
            <label class="block text-sm font-medium mb-1">Precio compra</label>
            <input v-model.number="form.precio_compra" type="number" step="0.01" min="0" class="w-full border rounded px-3 py-2" />
            <div v-if="form.errors.precio_compra" class="text-sm text-red-600 mt-1">{{ form.errors.precio_compra }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Precio venta</label>
            <input v-model.number="form.precio_venta" type="number" step="0.01" min="0" class="w-full border rounded px-3 py-2" />
            <div v-if="form.errors.precio_venta" class="text-sm text-red-600 mt-1">{{ form.errors.precio_venta }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Stock actual</label>
            <input v-model.number="form.stock_actual" type="number" min="0" class="w-full border rounded px-3 py-2" />
            <div v-if="form.errors.stock_actual" class="text-sm text-red-600 mt-1">{{ form.errors.stock_actual }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Stock mínimo</label>
            <input v-model.number="form.stock_minimo" type="number" min="0" class="w-full border rounded px-3 py-2" />
            <div v-if="form.errors.stock_minimo" class="text-sm text-red-600 mt-1">{{ form.errors.stock_minimo }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Unidad de medida</label>
            <input v-model="form.unidad_medida" type="text" class="w-full border rounded px-3 py-2" />
            <div v-if="form.errors.unidad_medida" class="text-sm text-red-600 mt-1">{{ form.errors.unidad_medida }}</div>
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
            <input v-model="form.imagenurl" type="text" class="w-full border rounded px-3 py-2" />
            <div v-if="form.errors.imagenurl" class="text-sm text-red-600 mt-1">{{ form.errors.imagenurl }}</div>
          </div>

          <div class="md:col-span-2 flex gap-2 mt-2">
            <button @click="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded">Guardar</button>
            <Link :href="route('productos.index')" class="px-4 py-2 bg-gray-200 rounded">Cancelar</Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
