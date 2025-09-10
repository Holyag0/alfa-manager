<template>
  <div class="space-y-6">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Financeiro da Matrícula</h2>
    
    <!-- Resumo Financeiro -->
    <div v-if="financialSummary" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-cyan-50">
        <h3 class="text-lg font-semibold text-gray-900">Resumo Financeiro</h3>
        <p class="text-sm text-gray-600">Informações financeiras da matrícula</p>
      </div>
      
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Total em Aberto -->
          <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-red-600">Total em Aberto</p>
                <p class="text-2xl font-bold text-red-900">{{ financialSummary.summary.total_due_formatted }}</p>
              </div>
            </div>
          </div>

          <!-- Total Pago -->
          <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-green-600">Total Pago</p>
                <p class="text-2xl font-bold text-green-900">{{ financialSummary.summary.total_payments_formatted }}</p>
              </div>
            </div>
          </div>

          <!-- Faturas Pendentes -->
          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-yellow-600">Faturas Pendentes</p>
                <p class="text-2xl font-bold text-yellow-900">{{ financialSummary.summary.pending_invoices }}</p>
              </div>
            </div>
          </div>

          <!-- Status -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-blue-600">Status</p>
                <p class="text-lg font-bold text-blue-900">{{ financialSummary.summary.payment_status_label }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Faturas Recentes -->
    <div v-if="financialSummary && financialSummary.recent_invoices.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">Faturas Recentes</h3>
          <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
            Ver todas
          </button>
        </div>
      </div>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimento</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="invoice in financialSummary.recent_invoices" :key="invoice.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ invoice.invoice_number }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ invoice.description }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                {{ invoice.formatted_amount }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(invoice.due_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(invoice.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ invoice.status_label }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagamentos Recentes -->
    <div v-if="financialSummary && financialSummary.recent_payments.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">Pagamentos Recentes</h3>
          <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
            Ver todos
          </button>
        </div>
      </div>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="payment in financialSummary.recent_payments" :key="payment.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ payment.payment_number }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ payment.description }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                {{ payment.formatted_amount }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ payment.method_label }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(payment.payment_date) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Seleção de Serviços -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-indigo-50">
        <h3 class="text-lg font-semibold text-gray-900">Adicionar Serviços</h3>
        <p class="text-sm text-gray-600">Escolha serviços adicionais para esta matrícula</p>
      </div>
      
      <div class="p-6">
        <!-- Filtros -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
            <input
              v-model="serviceFilters.search"
              type="text"
              placeholder="Nome do serviço..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Categoria</label>
            <select
              v-model="serviceFilters.category"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Todas as categorias</option>
              <option v-for="category in availableCategories" :key="category" :value="category">
                {{ category }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="serviceFilters.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Todos os status</option>
              <option value="active">Ativo</option>
              <option value="inactive">Inativo</option>
            </select>
          </div>
        </div>

        <!-- Lista de Serviços -->
        <div class="space-y-3 max-h-64 overflow-y-auto">
          <div
            v-for="service in filteredServices"
            :key="service.id"
            class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
          >
            <div class="flex-1">
              <div class="flex items-center space-x-3">
                <input
                  :id="'service-' + service.id"
                  v-model="selectedServices"
                  :value="service.id"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <div>
                  <label :for="'service-' + service.id" class="text-sm font-medium text-gray-900 cursor-pointer">
                    {{ service.name }}
                  </label>
                  <p class="text-sm text-gray-500">{{ service.description || 'Sem descrição' }}</p>
                </div>
              </div>
            </div>
            <div class="text-right">
              <span class="text-lg font-semibold text-green-600">{{ service.formatted_price }}</span>
              <div class="flex items-center space-x-2 mt-1">
                <span
                  :class="[
                    'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                    service.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ service.status === 'active' ? 'Ativo' : 'Inativo' }}
                </span>
                <span
                  :class="[
                    'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                    service.is_classroom_linked ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ service.is_classroom_linked ? 'Vinculado' : 'Global' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Botões de Ação -->
        <div class="mt-6 flex justify-end space-x-3">
          <button
            @click="clearServiceSelection"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Limpar Seleção
          </button>
          <button
            @click="addSelectedServices"
            :disabled="selectedServices.length === 0 || addingServices"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ addingServices ? 'Adicionando...' : `Adicionar ${selectedServices.length} Serviço(s)` }}
          </button>
        </div>
      </div>
    </div>

    <!-- Estado vazio -->
    <div v-if="!financialSummary" class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
      <svg class="mx-auto h-12 w-12 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
      </svg>
      <h3 class="mt-4 text-lg font-medium text-yellow-800">Nenhuma informação financeira</h3>
      <p class="mt-2 text-yellow-700">Esta matrícula ainda não possui registros financeiros.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'

const props = defineProps({ 
  enrollment: Object 
})

const financialSummary = ref(null)
const loading = ref(false)

// Variáveis para seleção de serviços
const services = ref([])
const selectedServices = ref([])
const addingServices = ref(false)
const serviceFilters = ref({
  search: '',
  category: '',
  status: ''
})

// Computed properties para formatação
const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const getStatusClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'paid': 'bg-green-100 text-green-800',
    'overdue': 'bg-red-100 text-red-800',
    'cancelled': 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

// Carregar dados financeiros
const loadFinancialData = async () => {
  if (!props.enrollment?.id) return
  
  loading.value = true
  try {
    const response = await fetch(`/api/enrollments/${props.enrollment.id}/financial-summary`)
    if (response.ok) {
      const data = await response.json()
      financialSummary.value = data
      
      // Adicionar formatação aos valores
      if (financialSummary.value?.summary) {
        financialSummary.value.summary.total_due_formatted = `R$ ${financialSummary.value.summary.total_due.toFixed(2).replace('.', ',')}`
        financialSummary.value.summary.total_payments_formatted = `R$ ${financialSummary.value.summary.total_payments.toFixed(2).replace('.', ',')}`
        financialSummary.value.summary.payment_status_label = getPaymentStatusLabel(financialSummary.value.summary.payment_status)
      }
    }
  } catch (error) {
    console.error('Erro ao carregar dados financeiros:', error)
  } finally {
    loading.value = false
  }
}

const getPaymentStatusLabel = (status) => {
  const labels = {
    'paid': 'Pago',
    'pending': 'Pendente',
    'overdue': 'Vencido',
    'partial': 'Parcial'
  }
  return labels[status] || status
}

// Computed properties para serviços
const availableCategories = computed(() => {
  const categories = [...new Set(services.value.map(service => service.category))]
  return categories.sort()
})

const filteredServices = computed(() => {
  return services.value.filter(service => {
    const matchesSearch = !serviceFilters.value.search || 
      service.name.toLowerCase().includes(serviceFilters.value.search.toLowerCase()) ||
      (service.description && service.description.toLowerCase().includes(serviceFilters.value.search.toLowerCase()))
    
    const matchesCategory = !serviceFilters.value.category || 
      service.category === serviceFilters.value.category
    
    const matchesStatus = !serviceFilters.value.status || 
      service.status === serviceFilters.value.status
    
    return matchesSearch && matchesCategory && matchesStatus
  })
})

// Carregar serviços disponíveis
const loadServices = async () => {
  try {
    const response = await fetch('/comercial/services/api')
    if (response.ok) {
      const data = await response.json()
      services.value = data.data || data
    }
  } catch (error) {
    console.error('Erro ao carregar serviços:', error)
  }
}

// Limpar seleção de serviços
const clearServiceSelection = () => {
  selectedServices.value = []
}

// Adicionar serviços selecionados
const addSelectedServices = async () => {
  if (selectedServices.value.length === 0) return
  
  addingServices.value = true
  try {
    // Aqui você implementaria a lógica para adicionar os serviços à matrícula
    // Por exemplo, criar faturas para cada serviço selecionado
    
    console.log('Serviços selecionados:', selectedServices.value)
    
    // Recarregar dados financeiros após adicionar serviços
    await loadFinancialData()
    
    // Limpar seleção
    clearServiceSelection()
    
    // Mostrar mensagem de sucesso
    alert(`${selectedServices.value.length} serviço(s) adicionado(s) com sucesso!`)
    
  } catch (error) {
    console.error('Erro ao adicionar serviços:', error)
    alert('Erro ao adicionar serviços. Tente novamente.')
  } finally {
    addingServices.value = false
  }
}

onMounted(() => {
  loadFinancialData()
  loadServices()
})
</script> 