<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[70]" @click="handleCancel">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white" @click.stop>
      <!-- Modal header -->
      <div class="flex items-center justify-between pb-4 border-b border-gray-200">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">
            Reverter Pagamento
          </h3>
          <p class="text-sm text-gray-600 mt-1">
            Esta ação irá remover o pagamento e restaurar a mensalidade ao estado anterior
          </p>
        </div>
        <button
          @click="handleCancel"
          class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Modal content -->
      <div class="mt-6 space-y-6">
        <!-- Aviso importante -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1">
              <p class="text-sm font-medium text-yellow-900">
                Atenção: Esta ação não pode ser desfeita
              </p>
              <p class="text-xs text-yellow-800 mt-1">
                O pagamento será marcado como revertido e a mensalidade retornará ao estado anterior (pendente ou vencida, conforme a data de vencimento).
              </p>
            </div>
          </div>
        </div>

        <!-- Informações do pagamento -->
        <div class="bg-gray-50 rounded-lg p-4">
          <h4 class="text-sm font-medium text-gray-900 mb-3">Informações do Pagamento</h4>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-600">Valor:</span>
              <span class="font-medium">{{ formatCurrency(payment?.amount || 0) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Método:</span>
              <span class="font-medium">{{ getMethodLabel(payment?.method) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Data do Pagamento:</span>
              <span class="font-medium">{{ formatDate(payment?.payment_date) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Status:</span>
              <span :class="getPaymentStatusClass(payment?.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ getPaymentStatusLabel(payment?.status) }}
              </span>
            </div>
            <div v-if="payment?.reference" class="flex justify-between">
              <span class="text-gray-600">Referência:</span>
              <span class="font-medium">{{ payment.reference }}</span>
            </div>
          </div>
        </div>

        <!-- Informações da mensalidade -->
        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
              <p class="text-sm font-medium text-blue-900 mb-1">
                Estado da Mensalidade Após Reversão
              </p>
              <p class="text-xs text-blue-800">
                A mensalidade será restaurada ao estado <strong>{{ getNewInstallmentStatus() }}</strong>.
                <span v-if="hasOtherPayments" class="block mt-1">
                  <strong>Nota:</strong> Existem outros pagamentos confirmados para esta mensalidade, então o status pode permanecer como "pago".
                </span>
              </p>
            </div>
          </div>
        </div>

        <!-- Motivo da reversão -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Motivo da Reversão (Opcional)
          </label>
          <textarea
            v-model="reason"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Descreva o motivo da reversão do pagamento..."
          ></textarea>
        </div>

        <!-- Botões de Ação -->
        <div class="flex items-center justify-end pt-6 border-t border-gray-200">
          <button
            type="button"
            @click="handleCancel"
            class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            Cancelar
          </button>
          <button
            type="button"
            @click="handleConfirm"
            :disabled="processing"
            class="ml-4 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
          >
            <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ processing ? 'Revertendo...' : 'Confirmar Reversão' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  payment: {
    type: Object,
    default: null
  },
  installment: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'success'])

const processing = ref(false)
const reason = ref('')

const hasOtherPayments = computed(() => {
  if (!props.installment?.payments) return false
  return props.installment.payments.some(p => 
    p.id !== props.payment?.id && p.status === 'confirmed'
  )
})

const getNewInstallmentStatus = () => {
  if (!props.installment) return 'pendente'
  
  const today = new Date()
  const dueDate = new Date(props.installment.due_date)
  today.setHours(0, 0, 0, 0)
  dueDate.setHours(0, 0, 0, 0)
  
  if (hasOtherPayments.value) {
    return 'pago (existem outros pagamentos confirmados)'
  }
  
  return today > dueDate ? 'vencida' : 'pendente'
}

const handleConfirm = async () => {
  if (!props.payment) return
  
  processing.value = true
  
  try {
    await axios.post(`/api/monthly-payments/${props.payment.id}/revert`, {
      reason: reason.value || null
    })
    
    emit('success')
    alert('Pagamento revertido com sucesso! A mensalidade foi restaurada ao estado anterior.')
    handleCancel()
  } catch (error) {
    alert('Erro ao reverter pagamento: ' + (error.response?.data?.message || error.message))
  } finally {
    processing.value = false
  }
}

const handleCancel = () => {
  reason.value = ''
  emit('close')
}

const formatCurrency = (value) => {
  if (value === null || value === undefined || isNaN(value)) {
    return 'R$ 0,00'
  }
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('pt-BR')
}

const getMethodLabel = (method) => {
  const methods = {
    'pix': 'PIX',
    'credit_card': 'Cartão de Crédito',
    'debit_card': 'Cartão de Débito',
    'cash': 'Dinheiro',
    'bank_transfer': 'Transferência Bancária',
    'check': 'Cheque',
  }
  return methods[method] || 'Outro'
}

const getPaymentStatusLabel = (status) => {
  const statuses = {
    'pending': 'Pendente',
    'confirmed': 'Confirmado',
    'cancelled': 'Cancelado',
    'refunded': 'Estornado',
  }
  return statuses[status] || 'Desconhecido'
}

const getPaymentStatusClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'confirmed': 'bg-green-100 text-green-800',
    'cancelled': 'bg-gray-100 text-gray-800',
    'refunded': 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>

