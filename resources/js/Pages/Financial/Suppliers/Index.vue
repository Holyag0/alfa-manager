<template>
  <div class="">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="lg:flex lg:items-center lg:justify-between mb-6">
        <div class="min-w-0 flex-1">
          <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">
            Fornecedores e Pagantes
          </h2>
          <p class="mt-1 text-sm text-gray-500">
            Gerencie fornecedores (para despesas) e pagantes (para receitas)
          </p>
        </div>
        
        <div class="mt-5 flex lg:ml-4 lg:mt-0">
          <Link :href="route('financial.suppliers.create')"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
            <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Novo Fornecedor/Pagante
          </Link>
        </div>
      </div>

      <!-- Filtros -->
      <div class="bg-white shadow rounded-lg p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
            <select v-model="filterType"
                    @change="applyTypeFilter"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
              <option value="">Todos</option>
              <option value="pagante">Pagante</option>
              <option value="fornecedor">Fornecedor</option>
              <option value="both">Pagante e Fornecedor</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select v-model="filters.active"
                    @change="applyFilters"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
              <option value="">Todos</option>
              <option value="true">Ativos</option>
              <option value="false">Inativos</option>
            </select>
          </div>
          
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
            <div class="flex gap-2">
              <input v-model="filters.search"
                     @keyup.enter="applyFilters"
                     type="text"
                     placeholder="Nome, documento ou email..."
                     class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
              <button @click="applyFilters"
                      class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                Buscar
              </button>
              <button @click="clearFilters"
                      class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                Limpar
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabela -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tipo
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Nome
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Documento
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
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="supplier in suppliers.data" :key="supplier.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    supplier.is_pagante && supplier.is_fornecedor
                      ? 'bg-purple-100 text-purple-800'
                      : supplier.is_pagante
                        ? 'bg-green-100 text-green-800'
                        : 'bg-blue-100 text-blue-800'
                  ]">
                    {{ supplier.is_pagante && supplier.is_fornecedor ? 'Pagante e Fornecedor' : supplier.is_pagante ? 'Pagante' : 'Fornecedor' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ supplier.name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-500">{{ supplier.document || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-500">{{ supplier.email || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-500">{{ supplier.phone || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    supplier.active 
                      ? 'bg-green-100 text-green-800' 
                      : 'bg-gray-100 text-gray-800'
                  ]">
                    {{ supplier.active ? 'Ativo' : 'Inativo' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link :href="route('financial.suppliers.show', supplier.id)"
                        class="text-indigo-600 hover:text-indigo-900 mr-4">
                    Ver
                  </Link>
                  <Link :href="route('financial.suppliers.edit', supplier.id)"
                        class="text-indigo-600 hover:text-indigo-900 mr-4">
                    Editar
                  </Link>
                  <button @click="deleteSupplier(supplier)"
                          class="text-red-600 hover:text-red-900">
                    Excluir
                  </button>
                </td>
              </tr>
              <tr v-if="suppliers.data.length === 0">
                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                  Nenhum fornecedor/pagante encontrado
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginação -->
        <div v-if="suppliers.links && suppliers.links.length > 3" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
              <Link v-if="suppliers.prev_page_url" 
                    :href="suppliers.prev_page_url"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Anterior
              </Link>
              <Link v-if="suppliers.next_page_url"
                    :href="suppliers.next_page_url"
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Próximo
              </Link>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Mostrando <span class="font-medium">{{ suppliers.from }}</span> até <span class="font-medium">{{ suppliers.to }}</span>
                  de <span class="font-medium">{{ suppliers.total }}</span> resultados
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <Link v-for="link in suppliers.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="[
                          'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                          link.active 
                            ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' 
                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                          link.url === null ? 'cursor-not-allowed opacity-50' : ''
                        ]"
                        v-html="link.label">
                  </Link>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
  suppliers: Object,
  filters: Object
})

const filters = ref({
  active: props.filters?.active || '',
  search: props.filters?.search || ''
})

const filterType = ref('')

const applyTypeFilter = () => {
  if (filterType.value === 'pagante') {
    filters.value.is_pagante = 'true'
    filters.value.is_fornecedor = ''
  } else if (filterType.value === 'fornecedor') {
    filters.value.is_pagante = ''
    filters.value.is_fornecedor = 'true'
  } else if (filterType.value === 'both') {
    filters.value.is_pagante = 'true'
    filters.value.is_fornecedor = 'true'
  } else {
    filters.value.is_pagante = ''
    filters.value.is_fornecedor = ''
  }
  applyFilters()
}

const applyFilters = () => {
  router.get(route('financial.suppliers.index'), filters.value, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  filterType.value = ''
  filters.value = {
    active: '',
    search: ''
  }
  applyFilters()
}

const deleteSupplier = (supplier) => {
  if (confirm(`Tem certeza que deseja excluir "${supplier.name}"?`)) {
    router.delete(route('financial.suppliers.destroy', supplier.id), {
      preserveScroll: true,
      onSuccess: () => {
        alert('Fornecedor/Pagante excluído com sucesso!')
      },
      onError: () => {
        alert('Erro ao excluir. Verifique se não existem transações vinculadas.')
      }
    })
  }
}
</script>

