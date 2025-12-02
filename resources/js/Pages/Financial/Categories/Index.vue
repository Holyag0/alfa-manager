<template>
  <div class="min-h-screen">
    <div class="space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="lg:flex lg:items-center lg:justify-between">
        <div class="min-w-0 flex-1">
          <h2 class="text-2xl/7 font-bold text-gray-100 sm:truncate sm:text-3xl sm:tracking-tight">
            Categorias Financeiras
          </h2>
          <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
            <div class="mt-2 flex items-center text-sm text-gray-400">
              <svg class="mr-1.5 size-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
              </svg>
              {{ categories.data.length }} categoria(s)
            </div>
          </div>
        </div>
        
        <div class="mt-5 flex lg:ml-4 lg:mt-0">
          <button @click="openCreateModal"
                  class="inline-flex items-center rounded-md bg-teal-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-teal-600">
            <svg class="-ml-0.5 mr-1.5 size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Nova Categoria
          </button>
        </div>
      </div>

      <!-- Cards de Categorias -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Receitas -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
              <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Receitas
            </h3>
            <p class="text-sm text-gray-600 mt-1">{{ receitasCount }} categoria(s)</p>
          </div>
          <ul class="divide-y divide-gray-200">
            <li v-for="category in receitas" :key="category.id" 
                class="px-6 py-4 hover:bg-gray-50">
              <div class="flex items-center justify-between">
                <div class="flex items-center flex-1">
                  <div :style="{ backgroundColor: category.color }" 
                       class="w-8 h-8 rounded-full mr-3 flex-shrink-0"></div>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ category.name }}</p>
                    <p v-if="category.description" class="text-xs text-gray-500">{{ category.description }}</p>
                    <span v-if="!category.is_active" 
                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mt-1">
                      Inativa
                    </span>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <button @click="editCategory(category)"
                          class="text-indigo-600 hover:text-indigo-900 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </button>
                  <button @click="deleteCategory(category)"
                          class="text-red-600 hover:text-red-900 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </div>
            </li>
            <li v-if="receitas.length === 0" class="px-6 py-8 text-center text-gray-500">
              Nenhuma categoria de receita cadastrada
            </li>
          </ul>
        </div>

        <!-- Despesas -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
              <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
              </svg>
              Despesas
            </h3>
            <p class="text-sm text-gray-600 mt-1">{{ despesasCount }} categoria(s)</p>
          </div>
          <ul class="divide-y divide-gray-200">
            <li v-for="category in despesas" :key="category.id" 
                class="px-6 py-4 hover:bg-gray-50">
              <div class="flex items-center justify-between">
                <div class="flex items-center flex-1">
                  <div :style="{ backgroundColor: category.color }" 
                       class="w-8 h-8 rounded-full mr-3 flex-shrink-0"></div>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ category.name }}</p>
                    <p v-if="category.description" class="text-xs text-gray-500">{{ category.description }}</p>
                    <span v-if="!category.is_active" 
                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mt-1">
                      Inativa
                    </span>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <button @click="editCategory(category)"
                          class="text-indigo-600 hover:text-indigo-900 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </button>
                  <button @click="deleteCategory(category)"
                          class="text-red-600 hover:text-red-900 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </div>
            </li>
            <li v-if="despesas.length === 0" class="px-6 py-8 text-center text-gray-500">
              Nenhuma categoria de despesa cadastrada
            </li>
          </ul>
        </div>
      </div>

      <!-- Modal Criar/Editar Categoria -->
      <CategoryModal
        :show="showModal"
        :category="selectedCategory"
        @close="closeModal"
        @saved="handleSaved"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import CategoryModal from './CategoryModal.vue'

const props = defineProps({
  categories: Object
})

const showModal = ref(false)
const selectedCategory = ref(null)

const receitas = computed(() => {
  return props.categories.data.filter(c => c.type === 'receita')
})

const despesas = computed(() => {
  return props.categories.data.filter(c => c.type === 'despesa')
})

const receitasCount = computed(() => receitas.value.length)
const despesasCount = computed(() => despesas.value.length)

const openCreateModal = () => {
  selectedCategory.value = null
  showModal.value = true
}

const editCategory = (category) => {
  selectedCategory.value = category
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedCategory.value = null
}

const handleSaved = () => {
  closeModal()
  router.reload({ only: ['categories'] })
}

const deleteCategory = (category) => {
  if (confirm(`Tem certeza que deseja excluir a categoria "${category.name}"?`)) {
    router.delete(route('financial.categories.destroy', category.id), {
      preserveScroll: true,
      onSuccess: () => {
        alert('Categoria excluída com sucesso!')
      },
      onError: () => {
        alert('Erro ao excluir categoria. Verifique se não existem transações vinculadas.')
      }
    })
  }
}
</script>

