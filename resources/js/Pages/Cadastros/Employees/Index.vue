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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h1 class="text-2xl font-bold text-sky-700">Colaboradores</h1>
                <strong class="text-sm text-gray-500">Gerencie os colaboradores da instituição</strong>
              </div>
            </div>
          </div>
          <div class="mt-4 flex md:mt-0 md:ml-4">
            <Link
              :href="route('cadastros.employees.create')"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
            >
              <PlusIcon class="w-4 h-4 mr-2" />
              Novo Colaborador
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Componente de Filtros -->
      <EmployeeFilters
        v-model:filters="filters"
        :positions="positions"
        @search="search"
        @clear="clearFilters"
      />

      <!-- Tabela de Colaboradores -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <div v-if="employees.data.length > 0">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Foto
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Nome
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cargo
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Email
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Telefone
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="employee in employees.data" :key="employee.id" 
                  class="hover:bg-gray-50 cursor-pointer transition-all duration-200 hover:scale-[1.05] hover:shadow-md"
                  @click="openEmployeeModal(employee)">
                <td class="px-6 py-4 whitespace-nowrap">
                  <img
                    v-if="employee.photo_url"
                    :src="employee.photo_url"
                    :alt="employee.name"
                    class="h-10 w-10 rounded-full object-cover"
                  />
                  <div v-else class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ employee.name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <div class="flex items-center">
                    <div v-if="employee.position" class="w-3 h-3 rounded-full mr-2 bg-blue-500"></div>
                    {{ employee.position?.name || '-' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ employee.email || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ employee.phone || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(employee.is_active)">
                    {{ employee.is_active ? 'Ativo' : 'Inativo' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Paginação -->
          <div v-if="employees.links && employees.links.length > 3" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <Pagination :links="employees.links" />
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum colaborador encontrado</h3>
          <p class="mt-1 text-sm text-gray-500">Comece criando um novo colaborador.</p>
          <div class="mt-6">
            <Link :href="route('cadastros.employees.create')"
                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
              <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
              Novo Colaborador
            </Link>
          </div>
        </div>
      </div>

    </div>

    <!-- Modal de Detalhes do Colaborador -->
    <EmployeeDetailsModal 
      v-if="selectedEmployee"
      :show="showModal"
      :employee="selectedEmployee"
      :loading="isLoadingDetails"
      @close="closeModal"
    />
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { PlusIcon } from '@heroicons/vue/20/solid'

import Pagination from '@/Shared/Pagination.vue'
import EmployeeFilters from './components/EmployeeFilters.vue'
import EmployeeDetailsModal from './components/EmployeeDetailsModal.vue'

const props = defineProps({
  employees: Object,
  positions: Array,
  filters: Object
})

const filters = reactive({
  search: props.filters?.search || '',
  position_id: props.filters?.position_id || '',
  is_active: props.filters?.is_active || ''
})

// Estado da Modal
const showModal = ref(false)
const selectedEmployee = ref(null)
const isLoadingDetails = ref(false)

const search = (searchFilters = filters) => {
  router.get(route('cadastros.employees.index'), searchFilters, {
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

const openEmployeeModal = async (employee) => {
  // Mostra modal imediatamente com dados básicos
  selectedEmployee.value = employee
  showModal.value = true
  isLoadingDetails.value = true
  
  try {
    // Busca detalhes completos da API
    const response = await fetch(route('cadastros.employees.show', employee.id), {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (!response.ok) {
      throw new Error('Erro ao carregar detalhes')
    }
    
    const data = await response.json()
    selectedEmployee.value = data.employee || employee
  } catch (error) {
    console.error('Erro ao carregar detalhes do colaborador:', error)
    // Mantém os dados básicos se houver erro
  } finally {
    isLoadingDetails.value = false
  }
}

const closeModal = () => {
  showModal.value = false
  setTimeout(() => {
    selectedEmployee.value = null
    isLoadingDetails.value = false
  }, 300) // Aguarda animação de fechamento
}

const getStatusClass = (isActive) => {
  return isActive
    ? 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800'
    : 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800'
}
</script>

