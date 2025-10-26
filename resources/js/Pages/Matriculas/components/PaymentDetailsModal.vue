<template>
  <!-- Modal backdrop -->
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeModal">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white" @click.stop>
      <!-- Modal header -->
      <div class="flex items-center justify-between pb-4 border-b border-gray-200">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Detalhes do Pagamento</h3>
          <p class="text-sm text-gray-600">Informações completas do pagamento</p>
        </div>
        <button
          @click="closeModal"
          class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Modal content -->
      <div class="mt-6" v-if="payment">
        <!-- Layout de 2 Colunas -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Coluna Esquerda - Informações Básicas -->
          <div class="space-y-6">
            <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Informações Básicas</h3>
            
            <div class="space-y-4">
              <!-- Número do Pagamento -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Número do Pagamento</label>
                <p class="text-lg font-semibold text-gray-900">{{ payment.payment_number }}</p>
              </div>

              <!-- Descrição -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                <p class="text-gray-900">{{ payment.description }}</p>
              </div>

              <!-- Método de Pagamento -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Método de Pagamento</label>
            <p class="text-gray-900">{{ payment.method_label }}</p> 
              </div>

              <!-- Responsável pelo Pagamento -->
              <div v-if="payment.paid_by_guardian">
                <label class="block text-sm font-medium text-gray-700 mb-1">Responsável pelo Pagamento</label>
                <p class="text-gray-900">{{ payment.paid_by_guardian.name }} - {{ payment.paid_by_guardian.relationship }}</p>
              </div>

              <!-- Data do Pagamento -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data do Pagamento</label>
                <p class="text-gray-900">{{ formatDate(payment.payment_date) }}</p>
              </div>

              <!-- Status -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <span :class="getStatusClass(payment.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ payment.status_label }}
                </span>
              </div>
            </div>
          </div>

          <!-- Coluna Direita - Valores e Detalhes -->
          <div class="space-y-6">
            <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Valores e Detalhes</h3>
            
            <!-- Resumo de Valores -->
            <div class="space-y-4 p-4 bg-blue-50 rounded-lg">
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Valor Serviço:</span>
                <span class="text-lg font-semibold text-gray-900">{{ getFormattedOriginalAmount() }}</span>
              </div>
              <div v-if="payment.interest_amount > 0" class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Juros:</span>
                <span class="text-lg font-semibold text-orange-600">{{ getFormattedInterestAmount() }}</span>
              </div>
              <div v-if="payment.discount_amount > 0" class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Desconto:</span>
                <span class="text-lg font-semibold text-red-600">{{ getFormattedDiscountAmount() }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Valor Pago:</span>
                <span class="text-lg font-semibold text-green-600">{{ payment.formatted_amount }}</span>
              </div>
            </div>

            <!-- Informações Adicionais -->
            <div class="space-y-4">
              <div v-if="payment.reference">
                <label class="block text-sm font-medium text-gray-700 mb-1">Referência</label>
                <p class="text-gray-900">{{ payment.reference }}</p>
              </div>

              <div v-if="payment.notes">
                <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                <p class="text-gray-900 whitespace-pre-wrap">{{ payment.notes }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data de Criação</label>
                <p class="text-gray-900">{{ formatDate(payment.created_at) }}</p>
              </div>

              <div v-if="payment.updated_at !== payment.created_at">
                <label class="block text-sm font-medium text-gray-700 mb-1">Última Atualização</label>
                <p class="text-gray-900">{{ formatDate(payment.updated_at) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal footer -->
      <div class="mt-6 pt-4 border-t border-gray-200 flex justify-between">
        <!-- Botões de Ação -->
        <div class="flex space-x-3">
          <!-- Botão Editar (apenas se o pagamento pode ser editado) -->
          <button
            v-if="canEdit"
            @click="requestEdit"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Editar Pagamento
          </button>
          
          <!-- Botão Estornar (apenas se o pagamento pode ser estornado) -->
          <button
            v-if="canRefund"
            @click="requestRefund"
            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            Estornar Pagamento
          </button>
          
          <!-- Botão Deletar Pagamento (apenas se o pagamento está estornado e tem fatura) -->
          <button
            v-if="canDeleteService"
            @click="requestDeleteService"
            class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Deletar Pagamento
          </button>
        </div>
        
        <!-- Botão Fechar -->
        <div class="ml-auto">
          <button
            @click="closeModal"
            class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            Fechar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  payment: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'request-edit', 'request-refund', 'request-delete-service'])

const closeModal = () => {
  emit('close')
}

// Computed para verificar se pode editar
const canEdit = computed(() => {
  return props.payment && props.payment.status === 'confirmed'
})

// Computed para verificar se pode estornar
const canRefund = computed(() => {
  return props.payment && props.payment.status === 'confirmed'
})

// Computed para verificar se pode deletar serviço
const canDeleteService = computed(() => {
  return props.payment && 
         props.payment.status === 'refunded' && 
         props.payment.invoice_id
})

// Função para solicitar edição
const requestEdit = () => {
  emit('request-edit', props.payment)
}

// Função para solicitar estorno
const requestRefund = () => {
  emit('request-refund', props.payment)
}

// Função para solicitar deleção do serviço
const requestDeleteService = () => {
  emit('request-delete-service', props.payment)
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'confirmed': 'bg-green-100 text-green-800',
    'cancelled': 'bg-red-100 text-red-800',
    'refunded': 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getFormattedOriginalAmount = () => {
  if (!props.payment) return 'R$ 0,00'
  const amount = props.payment.original_amount || props.payment.amount
  return `R$ ${Number(amount).toFixed(2).replace('.', ',')}`
}

const getFormattedInterestAmount = () => {
  if (!props.payment || !props.payment.interest_amount) return 'R$ 0,00'
  return `R$ ${Number(props.payment.interest_amount).toFixed(2).replace('.', ',')}`
}

const getFormattedDiscountAmount = () => {
  if (!props.payment || !props.payment.discount_amount) return 'R$ 0,00'
  return `R$ ${Number(props.payment.discount_amount).toFixed(2).replace('.', ',')}`
}
</script>
