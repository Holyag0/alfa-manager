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
                <h1 class="text-2xl font-bold text-sky-700">Categorias</h1>
                <strong class="text-sm text-gray-500">Gerencie as categorias de serviços e pacotes</strong>
              </div>
            </div>
          </div>
          <div class="mt-4 flex md:mt-0 md:ml-4">
            <Link
              :href="route('categories.create')"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
            >
              <PlusIcon class="w-4 h-4 mr-2" />
              Nova Categoria
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Componente de Filtros -->
      <CategoryFilters
        v-model:filters="filters"
        :types="types"
        @search="search"
        @clear="clearFilters"
      />

      <!-- Componente de Tabela -->
      <CategoryTable
        :categories="categories.data"
        :types="types"
        @delete="confirmDelete"
        @toggle-status="toggleStatus"
      />

      <!-- Paginação -->
      <div v-if="categories.links" class="mt-6">
        <Pagination :links="categories.links" />
      </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <ConfirmationModal
      :show="showDeleteModal"
      @cancel="showDeleteModal = false"
      @confirm="deleteCategory"
    >
      <template #title>
        Excluir Categoria
      </template>
      <template #message>
        Tem certeza que deseja excluir a categoria "{{ categoryToDelete?.name }}"?
        <br><br>
        <span class="text-sm text-red-600 font-medium">⚠️ Esta ação não pode ser desfeita!</span>
      </template>
    </ConfirmationModal>

    <!-- Modal de Confirmação de Toggle Status -->
    <ConfirmationModal
      :show="showToggleModal"
      @cancel="showToggleModal = false"
      @confirm="confirmToggleStatus"
    >
      <template #title>
        {{ categoryToToggle?.is_active ? 'Desativar Categoria' : 'Ativar Categoria' }}
      </template>
      <template #message>
        Deseja {{ categoryToToggle?.is_active ? 'desativar' : 'ativar' }} a categoria "{{ categoryToToggle?.name }}"?
      </template>
    </ConfirmationModal>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { PlusIcon } from '@heroicons/vue/20/solid'

import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import ConfirmationModal from '@/Shared/ConfirmationModal.vue'
import Pagination from '@/Shared/Pagination.vue'
import CategoryFilters from './components/CategoryFilters.vue'
import CategoryTable from './components/CategoryTable.vue'

const props = defineProps({
  categories: Object,
  types: Object,
  filters: Object
})

const filters = reactive({
  name: props.filters?.name || '',
  type: props.filters?.type || '',
  is_active: props.filters?.is_active || ''
})

const showDeleteModal = ref(false)
const showToggleModal = ref(false)
const categoryToDelete = ref(null)
const categoryToToggle = ref(null)

const search = () => {
  router.get(route('categories.index'), filters, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  Object.assign(filters, {
    name: '',
    type: '',
    is_active: ''
  })
  search()
}

const toggleStatus = (category) => {
  categoryToToggle.value = category
  showToggleModal.value = true
}

const confirmToggleStatus = () => {
  if (categoryToToggle.value) {
    router.get(route('categories.toggle-status', categoryToToggle.value.id))
    showToggleModal.value = false
    categoryToToggle.value = null
  }
}

const confirmDelete = (category) => {
  categoryToDelete.value = category
  showDeleteModal.value = true
}

const deleteCategory = () => {
  if (categoryToDelete.value) {
    router.delete(route('categories.destroy', categoryToDelete.value.id))
    showDeleteModal.value = false
    categoryToDelete.value = null
  }
}
</script>
