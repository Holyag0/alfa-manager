<template>
  <div class="min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <!-- Header -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Detalhes da Transação</h1>
            <p class="text-sm text-gray-600 mt-1">{{ transaction.transaction_number }}</p>
          </div>
          <Link :href="route('financial.transactions.index')" 
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informações Principais -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Card Principal -->
          <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r"
                 :class="transaction.type === 'receita' ? 'from-green-50 to-emerald-50' : 'from-red-50 to-rose-50'">
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div :class="[
                    'p-3 rounded-lg',
                    transaction.type === 'receita' ? 'bg-green-100' : 'bg-red-100'
                  ]">
                    <svg class="w-6 h-6" 
                         :class="transaction.type === 'receita' ? 'text-green-600' : 'text-red-600'"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path v-if="transaction.type === 'receita'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                      <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                  </div>
                  <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ transaction.type_label }}</p>
                    <p class="text-3xl font-bold" 
                       :class="transaction.type === 'receita' ? 'text-green-600' : 'text-red-600'">
                      {{ transaction.formatted_amount }}
                    </p>
                  </div>
                </div>
                <span :class="getStatusClass(transaction.status)">
                  {{ transaction.status_label }}
                </span>
              </div>
            </div>

            <div class="px-6 py-6 space-y-6">
              <!-- Descrição -->
              <div>
                <h3 class="text-sm font-medium text-gray-500 mb-2">Descrição</h3>
                <p class="text-gray-900">{{ transaction.description }}</p>
              </div>

              <!-- Categoria -->
              <div>
                <h3 class="text-sm font-medium text-gray-500 mb-2">Categoria</h3>
                <div class="flex items-center">
                  <div :style="{ backgroundColor: transaction.category.color }" 
                       class="w-4 h-4 rounded-full mr-2"></div>
                  <span class="text-gray-900 font-medium">{{ transaction.category.name }}</span>
                </div>
              </div>

              <!-- Data da Transação -->
              <div>
                <h3 class="text-sm font-medium text-gray-500 mb-2">Data da Transação</h3>
                <p class="text-gray-900">{{ formatDate(transaction.transaction_date) }}</p>
              </div>

              <!-- Método de Pagamento -->
              <div v-if="transaction.payment_method">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Método de Pagamento</h3>
                <p class="text-gray-900">{{ transaction.payment_method_label || transaction.payment_method }}</p>
              </div>

              <!-- Referência -->
              <div v-if="transaction.reference">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Referência</h3>
                <p class="text-gray-900">{{ transaction.reference }}</p>
              </div>

              <!-- Observações -->
              <div v-if="transaction.notes">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Observações</h3>
                <p class="text-gray-900 whitespace-pre-wrap">{{ transaction.notes }}</p>
              </div>

              <!-- Informações do Pagante (Receita) -->
              <div v-if="transaction.type === 'receita' && (transaction.payer_name || transaction.payer_document)" 
                   class="pt-6 border-t border-gray-200">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Informações do Pagante</h3>
                <div class="space-y-2">
                  <div v-if="transaction.payer_name" class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="text-gray-900">{{ transaction.payer_name }}</span>
                  </div>
                  <div v-if="transaction.payer_document" class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-gray-900">{{ transaction.payer_document }}</span>
                  </div>
                </div>
              </div>

              <!-- Informações do Beneficiário (Despesa) -->
              <div v-if="transaction.type === 'despesa' && (transaction.payee_name || transaction.payee_document)" 
                   class="pt-6 border-t border-gray-200">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Informações do Fornecedor/Beneficiário</h3>
                <div class="space-y-2">
                  <div v-if="transaction.payee_name" class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="text-gray-900">{{ transaction.payee_name }}</span>
                  </div>
                  <div v-if="transaction.payee_document" class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-gray-900">{{ transaction.payee_document }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Origem da Transação (se houver) -->
          <div v-if="transaction.source_type" class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
              <h3 class="text-lg font-medium text-gray-900">Origem da Transação</h3>
            </div>
            <div class="px-6 py-6">
              <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-gray-900">
                    {{ transaction.source_type === 'App\\Models\\MonthlyFeePayment' ? 'Pagamento de Mensalidade' : 'Pagamento de Matrícula' }}
                  </p>
                  <p class="text-sm text-gray-500">Registrado automaticamente pelo sistema</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar com Ações e Info -->
        <div class="space-y-6">
          <!-- Card de Ações -->
          <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Ações</h3>
            </div>
            <div class="px-6 py-4 space-y-3">
              <!-- Botão Editar (apenas transações manuais) -->
              <Link v-if="!transaction.source_type && transaction.status !== 'cancelled'"
                    :href="route('financial.transactions.edit', transaction.id)"
                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-indigo-300 rounded-md shadow-sm text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Editar Transação
              </Link>
              
              <!-- Aviso se for transação rastreada -->
              <div v-if="transaction.source_type" class="p-3 bg-blue-50 border border-blue-200 rounded-md">
                <div class="flex items-start">
                  <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <div class="flex-1">
                    <p class="text-xs font-medium text-blue-900">Transação Rastreada</p>
                    <p class="text-xs text-blue-700 mt-1">
                      Esta transação foi gerada automaticamente e não pode ser editada manualmente.
                    </p>
                  </div>
                </div>
              </div>
              
              <!-- Botão Cancelar -->
              <button v-if="transaction.status !== 'cancelled'"
                      @click="cancelTransaction"
                      class="w-full inline-flex items-center justify-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Cancelar Transação
              </button>
            </div>
          </div>

          <!-- Card de Informações do Sistema -->
          <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Informações do Sistema</h3>
            </div>
            <dl class="px-6 py-4 space-y-4">
              <div>
                <dt class="text-sm font-medium text-gray-500">Número da Transação</dt>
                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ transaction.transaction_number }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Criado em</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDateTime(transaction.created_at) }}</dd>
              </div>
              <div v-if="transaction.confirmed_by">
                <dt class="text-sm font-medium text-gray-500">Confirmado por</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ transaction.confirmed_by.name }}</dd>
              </div>
              <div v-if="transaction.confirmed_at">
                <dt class="text-sm font-medium text-gray-500">Confirmado em</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDateTime(transaction.confirmed_at) }}</dd>
              </div>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
  transaction: Object
})

const cancelTransaction = () => {
  if (confirm('Tem certeza que deseja cancelar esta transação?')) {
    const reason = prompt('Motivo do cancelamento (opcional):')
    
    router.post(route('financial.transactions.cancel', props.transaction.id), {
      reason: reason
    }, {
      onSuccess: () => {
        alert('Transação cancelada com sucesso!')
      }
    })
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('pt-BR')
}

const formatDateTime = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleString('pt-BR')
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800',
    confirmed: 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800',
    cancelled: 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800'
  }
  return classes[status] || classes.confirmed
}
</script>

