<template>
  <div class="min-h-screen">
    <!-- Header Section -->
    <div class="border-b border-cyan-500">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6 md:flex md:items-center md:justify-between">
          <div class="flex-1 min-w-0">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h1 class="text-2xl font-bold text-sky-700">Editar Categoria</h1>
                <strong class="text-sm text-gray-500">Atualize as informações da categoria</strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <form @submit.prevent="submit" class="p-6">
          <!-- Nome -->
          <div class="mb-6">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
              Nome da Categoria *
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              :class="{ 'border-red-500': errors.name }"
              placeholder="Ex: Educação Infantil"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
          </div>

          <!-- Descrição -->
          <div class="mb-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
              Descrição (Opcional)
            </label>
            <textarea
              id="description"
              v-model="form.description"
              rows="3"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              :class="{ 'border-red-500': errors.description }"
              placeholder="Descreva brevemente esta categoria..."
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
          </div>

          <!-- Tipo -->
          <div class="mb-6">
            <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
              Tipo *
            </label>
            <select
              id="type"
              v-model="form.type"
              required
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              :class="{ 'border-red-500': errors.type }"
            >
              <option value="">Selecione um tipo</option>
              <option v-for="(label, value) in types" :key="value" :value="value">
                {{ label }}
              </option>
            </select>
            <p v-if="errors.type" class="mt-1 text-sm text-red-600">{{ errors.type }}</p>
          </div>

          <!-- Cor -->
          <div class="mb-6">
            <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
              Cor da Categoria
            </label>
            <div class="flex items-center space-x-4">
              <input
                id="color"
                v-model="form.color"
                type="color"
                class="w-16 h-10 border border-gray-300 rounded-md cursor-pointer"
              />
              <input
                v-model="form.color"
                type="text"
                class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': errors.color }"
                placeholder="#3B82F6"
                pattern="^#[0-9A-Fa-f]{6}$"
              />
            </div>
            <p v-if="errors.color" class="mt-1 text-sm text-red-600">{{ errors.color }}</p>
            <p class="mt-1 text-sm text-gray-500">A cor será usada para identificar visualmente a categoria</p>
          </div>

          <!-- Status -->
          <div class="mb-6">
            <div class="flex items-center">
              <input
                id="is_active"
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="is_active" class="ml-2 block text-sm text-gray-900">
                Categoria ativa
              </label>
            </div>
            <p class="mt-1 text-sm text-gray-500">Categorias inativas não aparecerão nas listas de seleção</p>
          </div>

          <!-- Botões -->
          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
            <Link
              :href="route('categories.index')"
              class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
              Cancelar
            </Link>
            <button
              type="submit"
              :disabled="processing"
              class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
            >
              <svg v-if="processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ processing ? 'Atualizando...' : 'Atualizar Categoria' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  category: Object,
  types: Object,
  errors: Object
})

const form = useForm({
  name: props.category.name,
  description: props.category.description || '',
  type: props.category.type,
  color: props.category.color || '#3B82F6',
  is_active: props.category.is_active
})

const processing = ref(false)

const submit = () => {
  processing.value = true
  form.put(route('categories.update', props.category.id), {
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>
