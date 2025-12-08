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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h1 class="text-2xl font-bold text-sky-700">Cargos</h1>
                <strong class="text-sm text-gray-500">Gerencie os cargos dos colaboradores</strong>
              </div>
            </div>
          </div>
          <div class="mt-4 flex md:mt-0 md:ml-4">
            <Link
              :href="route('cadastros.positions.create')"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
            >
              <PlusIcon class="w-4 h-4 mr-2" />
              Novo Cargo
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Componente de Filtros -->
      <PositionFilters
        v-model:filters="filters"
        @search="search"
        @clear="clearFilters"
      />

      <!-- Componente de Tabela -->
      <PositionTable
        :positions="positions.data"
        @delete="confirmDelete"
        @toggle-status="toggleStatus"
      />

      <!-- Paginação -->
      <div v-if="positions.links && positions.links.length > 3" class="mt-6">
        <Pagination :links="positions.links" />
      </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <ConfirmationModal
      :show="showDeleteModal"
      @cancel="showDeleteModal = false"
      @confirm="deletePosition"
    >
      <template #title>
        Excluir Cargo
      </template>
      <template #message>
        Tem certeza que deseja excluir o cargo "{{ positionToDelete?.name }}"?
        <br><br>
        <span class="text-sm text-red-600 font-medium">⚠️ Esta ação não pode ser desfeita!</span>
        <br>
        <span class="text-sm text-orange-600 font-medium" v-if="positionToDelete?.employees_count > 0">
          ⚠️ Este cargo possui {{ positionToDelete?.employees_count }} colaborador(es) vinculado(s).
        </span>
      </template>
    </ConfirmationModal>

    <!-- Modal de Confirmação de Toggle Status -->
    <ConfirmationModal
      :show="showToggleModal"
      @cancel="showToggleModal = false"
      @confirm="confirmToggleStatus"
    >
      <template #title>
        {{ positionToToggle?.is_active ? 'Desativar Cargo' : 'Ativar Cargo' }}
      </template>
      <template #message>
        Deseja {{ positionToToggle?.is_active ? 'desativar' : 'ativar' }} o cargo "{{ positionToToggle?.name }}"?
      </template>
    </ConfirmationModal>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { PlusIcon } from '@heroicons/vue/20/solid'

import ConfirmationModal from '@/Shared/ConfirmationModal.vue'
import Pagination from '@/Shared/Pagination.vue'
import PositionFilters from './components/PositionFilters.vue'
import PositionTable from './components/PositionTable.vue'

const props = defineProps({
  positions: Object,
  filters: Object
})

const filters = reactive({
  name: props.filters?.name || '',
  is_active: props.filters?.is_active || ''
})

const showDeleteModal = ref(false)
const showToggleModal = ref(false)
const positionToDelete = ref(null)
const positionToToggle = ref(null)

const search = (searchFilters = filters) => {
  router.get(route('cadastros.positions.index'), searchFilters, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  Object.keys(filters).forEach(key => {
    filters[key] = ''
  })
  search()
}

const toggleStatus = (position) => {
  positionToToggle.value = position
  showToggleModal.value = true
}

const confirmToggleStatus = () => {
  if (positionToToggle.value) {
    router.get(route('cadastros.positions.toggle-status', positionToToggle.value.id))
    showToggleModal.value = false
    positionToToggle.value = null
  }
}

const confirmDelete = (position) => {
  positionToDelete.value = position
  showDeleteModal.value = true
}

const deletePosition = () => {
  if (positionToDelete.value) {
    router.delete(route('cadastros.positions.destroy', positionToDelete.value.id), {
      onError: (errors) => {
        alert(errors.error || 'Erro ao excluir cargo. Verifique se não existem colaboradores vinculados.')
      }
    })
    showDeleteModal.value = false
    positionToDelete.value = null
  }
}
</script>

