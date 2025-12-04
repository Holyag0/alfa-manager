<template>
  <div class="min-h-screen">
    <div class="space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="lg:flex lg:items-center lg:justify-between">
        <div class="min-w-0 flex-1">
          <h2 class="text-2xl/7 font-bold text-sky-700 sm:truncate sm:text-3xl sm:tracking-tight">
            Transações Financeiras
          </h2>
          <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
            <div class="mt-2 flex items-center text-sm text-gray-400">
              <svg class="mr-1.5 size-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ transactions.total }} transação(ões)
            </div>
          </div>
        </div>
        
        <div class="mt-5 flex lg:ml-4 lg:mt-0 space-x-3">
          <Link :href="route('financial.report')" 
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            <svg class="-ml-0.5 mr-1.5 size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Relatórios
          </Link>
          
          <Link :href="route('financial.transactions.create')" 
                class="inline-flex items-center rounded-md bg-teal-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-teal-600">
            <svg class="-ml-0.5 mr-1.5 size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Nova Transação
          </Link>
        </div>
      </div>

      <!-- Filtros -->
      <div class="bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Tipo -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
            <select v-model="filters.type" @change="applyFilters"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
              <option value="">Todos</option>
              <option value="receita">Receitas</option>
              <option value="despesa">Despesas</option>
            </select>
          </div>

          <!-- Categoria -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Categoria</label>
            <select v-model="filters.category_id" @change="applyFilters"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
              <option value="">Todas</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>

          <!-- Status -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select v-model="filters.status" @change="applyFilters"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
              <option value="">Todos</option>
              <option value="pending">Pendente</option>
              <option value="confirmed">Confirmado</option>
              <option value="cancelled">Cancelado</option>
            </select>
          </div>

          <!-- Busca -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
            <input v-model="filters.search" @input="debounceSearch"
                   type="text"
                   placeholder="Número ou descrição..."
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
          </div>
        </div>

        <!-- Filtro de Data -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Data Inicial</label>
            <input v-model="filters.start_date" @change="applyFilters"
                   type="date"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Data Final</label>
            <input v-model="filters.end_date" @change="applyFilters"
                   type="date"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
          </div>
        </div>

        <!-- Botão Limpar Filtros -->
        <div class="mt-4 flex justify-end">
          <button @click="clearFilters"
                  class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            Limpar Filtros
          </button>
        </div>
      </div>

      <!-- Tabela de Transações -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <div v-if="transactions.data.length > 0">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tipo
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Categoria
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Valor
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Data
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="transaction in transactions.data" :key="transaction.id" 
                  class="hover:bg-gray-50 cursor-pointer transition-all duration-200 hover:scale-[1.02] hover:shadow-md"
                  @click="openTransactionModal(transaction)">
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    transaction.type === 'receita' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                  ]">
                    {{ transaction.type_label }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  <div class="flex items-center">
                    <div :style="{ backgroundColor: transaction.category.color }" 
                         class="w-3 h-3 rounded-full mr-2"></div>
                    {{ transaction.category.name }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold"
                    :class="transaction.type === 'receita' ? 'text-green-600' : 'text-red-600'">
                  {{ transaction.formatted_amount }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(transaction.transaction_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(transaction.status)">
                    {{ transaction.status_label }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Paginação -->
          <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="flex-1 flex justify-between sm:hidden">
                <Link v-if="transactions.prev_page_url" 
                      :href="transactions.prev_page_url"
                      class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                  Anterior
                </Link>
                <span v-else class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100 cursor-not-allowed">
                  Anterior
                </span>
                <Link v-if="transactions.next_page_url" 
                      :href="transactions.next_page_url"
                      class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                  Próxima
                </Link>
                <span v-else class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100 cursor-not-allowed">
                  Próxima
                </span>
              </div>
              <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-gray-700">
                    Mostrando
                    <span class="font-medium">{{ transactions.from }}</span>
                    a
                    <span class="font-medium">{{ transactions.to }}</span>
                    de
                    <span class="font-medium">{{ transactions.total }}</span>
                    resultados
                  </p>
                </div>
                <div>
                  <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <template v-for="link in transactions.links" :key="link.label">
                      <Link v-if="link.url"
                            :href="link.url"
                            :class="[
                              'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                              link.active 
                                ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                            ]"
                            v-html="link.label">
                      </Link>
                      <span v-else
                            :class="[
                              'relative inline-flex items-center px-4 py-2 border text-sm font-medium cursor-not-allowed',
                              link.active 
                                ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                : 'bg-gray-100 border-gray-300 text-gray-400'
                            ]"
                            v-html="link.label">
                      </span>
                    </template>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma transação encontrada</h3>
          <p class="mt-1 text-sm text-gray-500">Comece criando uma nova transação.</p>
          <div class="mt-6">
            <Link :href="route('financial.transactions.create')"
                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700">
              <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
              Nova Transação
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Detalhes da Transação -->
    <TransactionDetailsModal 
      v-if="selectedTransaction"
      :show="showModal"
      :transaction="selectedTransaction"
      :loading="isLoadingDetails"
      @close="closeModal"
      @status-updated="handleStatusUpdated"
      @deleted="handleTransactionDeleted"
    />
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import TransactionDetailsModal from './components/TransactionDetailsModal.vue'

const props = defineProps({
  transactions: Object,
  categories: Array,
  filters: Object
})

const filters = reactive({
  type: props.filters?.type || '',
  category_id: props.filters?.category_id || '',
  status: props.filters?.status || '',
  start_date: props.filters?.start_date || '',
  end_date: props.filters?.end_date || '',
  search: props.filters?.search || ''
})

// Estado da Modal
const showModal = ref(false)
const selectedTransaction = ref(null)
const isLoadingDetails = ref(false)

let searchTimeout = null

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const applyFilters = () => {
  router.get(route('financial.transactions.index'), filters, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  filters.type = ''
  filters.category_id = ''
  filters.status = ''
  filters.start_date = ''
  filters.end_date = ''
  filters.search = ''
  applyFilters()
}

const openTransactionModal = async (transaction) => {
  // Mostra modal imediatamente com dados básicos
  selectedTransaction.value = transaction
  showModal.value = true
  isLoadingDetails.value = true
  
  try {
    // Busca detalhes completos da API
    const response = await fetch(route('financial.transactions.details', transaction.id), {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    if (!response.ok) {
      throw new Error('Erro ao carregar detalhes')
    }
    
    const detailedTransaction = await response.json()
    selectedTransaction.value = detailedTransaction
  } catch (error) {
    console.error('Erro ao carregar detalhes da transação:', error)
    alert('Erro ao carregar detalhes completos da transação')
  } finally {
    isLoadingDetails.value = false
  }
}

const closeModal = () => {
  showModal.value = false
  setTimeout(() => {
    selectedTransaction.value = null
    isLoadingDetails.value = false
  }, 300) // Aguarda animação de fechamento
}

const handleStatusUpdated = (updatedTransaction) => {
  // Atualizar a transação na lista
  const index = props.transactions.data.findIndex(t => t.id === updatedTransaction.id)
  if (index !== -1) {
    // Atualizar campos essenciais na lista
    props.transactions.data[index].status = updatedTransaction.status
    props.transactions.data[index].status_label = updatedTransaction.status_label
  }
}

const handleTransactionDeleted = (transactionId) => {
  // Remover a transação da lista
  const index = props.transactions.data.findIndex(t => t.id === transactionId)
  if (index !== -1) {
    props.transactions.data.splice(index, 1)
    // Atualizar total
    props.transactions.total = Math.max(0, props.transactions.total - 1)
  }
  
  // Recarregar a lista para garantir sincronização
  router.reload({ only: ['transactions'] })
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('pt-BR')
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800',
    confirmed: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800',
    cancelled: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800'
  }
  return classes[status] || classes.confirmed
}
</script>

