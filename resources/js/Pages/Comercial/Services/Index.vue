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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h1 class="text-2xl font-bold text-sky-700">Serviços</h1>
                <strong class="text-sm text-gray-500">Gerencie os serviços oferecidos pela escola</strong>
              </div>
            </div>
          </div>
          <div class="mt-4 flex md:mt-0 md:ml-4">
            <Link
              :href="route('services.create')"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
            >
              <PlusIcon class="w-4 h-4 mr-2" />
              Novo Serviço
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Cabeçalho -->
       

        <!-- Componente de Filtros -->
        <ServiceFilters
          v-model:filters="filters"
          :categories="categories"
          @search="search"
          @clear="clearFilters"
        />

        <!-- Componente de Tabela -->
        <ServiceTable
          :services="services.data"
          @delete="confirmDelete"
        />

        <!-- Paginação -->
        <div v-if="services.links" class="mt-6">
          <Pagination :links="services.links" />
        </div>
      </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <ConfirmationModal
      :show="showDeleteModal"
      @close="showDeleteModal = false"
    >
      <template #title>
        Confirmar Exclusão
      </template>
      <template #message>
        Tem certeza que deseja excluir o serviço "{{ serviceToDelete?.name }}"?
        Esta ação não pode ser desfeita.
      </template>
      <template #footer>
        <SecondaryButton @click="showDeleteModal = false">
          Cancelar
        </SecondaryButton>
        <DangerButton @click="deleteService" class="ml-3">
          Excluir
        </DangerButton>
      </template>
    </ConfirmationModal>
  
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { PlusIcon } from '@heroicons/vue/20/solid'

import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import ConfirmationModal from '@/Shared/ConfirmationModal.vue'
import Pagination from '@/Shared/Pagination.vue'
import ServiceFilters from './components/ServiceFilters.vue'
import ServiceTable from './components/ServiceTable.vue'

const props = defineProps({
  services: Object,
  categories: Array,
  filters: Object
})

const filters = reactive({
  name: props.filters?.name || '',
  category: props.filters?.category || '',
  status: props.filters?.status || ''
})

const showDeleteModal = ref(false)
const serviceToDelete = ref(null)

const search = (searchFilters = filters) => {
  router.get(route('services.index'), searchFilters, {
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

const confirmDelete = (service) => {
  serviceToDelete.value = service
  showDeleteModal.value = true
}

const deleteService = () => {
  router.delete(route('services.destroy', serviceToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false
      serviceToDelete.value = null
    }
  })
}
</script> 