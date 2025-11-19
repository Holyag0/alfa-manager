<template>
  <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Overlay -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>

    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
      <div class="relative transform overflow-hidden rounded-lg bg-white shadow-xl transition-all w-full max-w-2xl">
        <!-- Header -->
        <div class="bg-white px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Detalhes da Mensalidade</h3>
            <button @click="$emit('close')" class="text-gray-400 hover:text-gray-500">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Body -->
        <div class="px-6 py-4">
          <!-- Informações Básicas -->
          <div class="mb-6">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Informações da Mensalidade</h4>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-gray-600">Mês de Referência:</span>
                <p class="font-medium">{{ formatMonth(installment.reference_month) }}</p>
              </div>
              <div>
                <span class="text-gray-600">Parcela:</span>
                <p class="font-medium">{{ installment.installment_number }}/12</p>
              </div>
              <div>
                <span class="text-gray-600">Data de Vencimento:</span>
                <p class="font-medium">{{ formatDate(installment.due_date) }}</p>
              </div>
              <div>
                <span class="text-gray-600">Status:</span>
                <p>
                  <span :class="getStatusClass(installment.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ getStatusLabel(installment.status) }}
                  </span>
                </p>
              </div>
            </div>
          </div>

          <!-- Valores -->
          <div class="mb-6">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Valores</h4>
            <div class="bg-gray-50 rounded-lg p-4 space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-600">Valor da Mensalidade:</span>
                <span class="font-medium">{{ formatCurrency(getBaseAmount()) }}</span>
              </div>
              <div v-if="getSiblingDiscount() > 0" class="flex justify-between text-green-600">
                <span>Desconto de Irmão:</span>
                <span>- {{ formatCurrency(getSiblingDiscount()) }}</span>
              </div>
              <div v-if="getPunctualityDiscount() > 0" class="flex justify-between text-green-600">
                <span>Desconto de Pontualidade:</span>
                <span>- {{ formatCurrency(getPunctualityDiscount()) }}</span>
              </div>
              <div v-if="getOtherDiscounts() > 0" class="flex justify-between text-green-600">
                <span>Outros Descontos:</span>
                <span>- {{ formatCurrency(getOtherDiscounts()) }}</span>
              </div>
              <div v-if="getInterestAmount() !== 0" class="flex justify-between text-red-600">
                <span>Juros:</span>
                <span>+ {{ formatCurrency(Math.abs(getInterestAmount())) }}</span>
              </div>
              <div v-if="getFineAmount() !== 0" class="flex justify-between text-orange-600">
                <span>Multa:</span>
                <span>+ {{ formatCurrency(getFineAmount()) }}</span>
              </div>
              <div class="flex justify-between pt-2 border-t border-gray-300 font-semibold text-base">
                <span class="text-gray-900">Valor Final:</span>
                <span class="text-black">{{ formatCurrency(getFinalAmount()) }}</span>
              </div>
            </div>
          </div>

          <!-- Pagamentos -->
          <div v-if="installment.payments && installment.payments.length > 0" class="mb-6">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Pagamentos Registrados</h4>
            <div class="space-y-3">
              <div v-for="payment in installment.payments" :key="payment.id" class="bg-green-50 rounded-lg p-4 text-sm">
                <div class="flex justify-between items-start mb-2">
                  <div>
                    <p class="font-medium text-gray-900">{{ formatCurrency(payment.amount) }}</p>
                    <p class="text-xs text-gray-600">{{ getMethodLabel(payment.method) }}</p>
                  </div>
                  <span :class="getPaymentStatusClass(payment.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ getPaymentStatusLabel(payment.status) }}
                  </span>
                </div>
                <div class="text-xs text-gray-600 space-y-1">
                  <p>Data: {{ formatDate(payment.payment_date) }}</p>
                  <p v-if="payment.reference">Referência: {{ payment.reference }}</p>
                  <p v-if="payment.notes">Obs: {{ payment.notes }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Observações -->
          <div v-if="installment.notes" class="mb-6">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Observações</h4>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-lg p-4">{{ installment.notes }}</p>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
          <div class="flex justify-between items-center">
            <button 
              v-if="canRegisterPayment"
              @click="handleRegisterPayment"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
              </svg>
              Registrar Pagamento
            </button>
            <div v-else></div>
            <button 
              @click="$emit('close')"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Fechar
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal de Pagamento -->
    <PaymentModal 
      :show="showPaymentModal"
      :installment="installment"
      :student="student"
      @close="closePaymentModal"
      @success="handlePaymentSuccess"
    />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import PaymentModal from './PaymentModal.vue'

const props = defineProps({
  installment: {
    type: Object,
    required: true
  },
  student: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close'])

const showPaymentModal = ref(false)

const canRegisterPayment = computed(() => {
  return props.installment.status !== 'paid' && 
         props.installment.status !== 'cancelled' &&
         props.installment.status !== 'waived'
})

const handleRegisterPayment = () => {
  showPaymentModal.value = true
}

const closePaymentModal = () => {
  showPaymentModal.value = false
}

const handlePaymentSuccess = () => {
  closePaymentModal()
  // Recarregar página para atualizar dados
  router.reload()
}

const formatMonth = (monthString) => {
  if (!monthString) return ''
  const [year, month] = monthString.split('-')
  const date = new Date(year, month - 1, 1)
  return date.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('pt-BR')
}

const formatCurrency = (value) => {
  if (value === null || value === undefined || isNaN(value)) {
    return 'R$ 0,00'
  }
  const numValue = Number(value)
  if (isNaN(numValue)) {
    return 'R$ 0,00'
  }
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(numValue)
}

const getStatusLabel = (status) => {
  const labels = {
    pending: 'Pendente',
    paid: 'Pago',
    overdue: 'Vencido',
    cancelled: 'Cancelado',
    waived: 'Dispensado'
  }
  return labels[status] || status
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-green-100 text-green-800',
    overdue: 'bg-red-100 text-red-800',
    cancelled: 'bg-gray-100 text-gray-800',
    waived: 'bg-blue-100 text-blue-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusLabel = (status) => {
  const labels = {
    pending: 'Pendente',
    confirmed: 'Confirmado',
    cancelled: 'Cancelado',
    refunded: 'Estornado'
  }
  return labels[status] || status
}

const getPaymentStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-green-100 text-green-800',
    cancelled: 'bg-gray-100 text-gray-800',
    refunded: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getMethodLabel = (method) => {
  const labels = {
    pix: 'PIX',
    credit_card: 'Cartão de Crédito',
    debit_card: 'Cartão de Débito',
    cash: 'Dinheiro',
    bank_transfer: 'Transferência Bancária',
    check: 'Cheque'
  }
  return labels[method] || method
}

// Funções para obter valores com fallback
const getBaseAmount = () => {
  // Tentar obter do accessor do backend
  if (props.installment.base_amount !== undefined && !isNaN(props.installment.base_amount)) {
    return Number(props.installment.base_amount)
  }
  
  // Tentar obter do serviço atual
  if (props.installment?.classroom_service?.service?.price) {
    return Number(props.installment.classroom_service.service.price)
  }
  
  // Fallback: valor do monthlyFee
  if (props.installment?.monthly_fee?.base_amount) {
    return Number(props.installment.monthly_fee.base_amount)
  }
  
  return 0
}

const getSiblingDiscount = () => {
  // Tentar do accessor
  if (props.installment.sibling_discount !== undefined && !isNaN(props.installment.sibling_discount)) {
    return Number(props.installment.sibling_discount)
  }
  
  // Tentar do monthlyFee
  if (props.installment?.monthly_fee?.sibling_discount_amount) {
    return Number(props.installment.monthly_fee.sibling_discount_amount)
  }
  
  return 0
}

const getPunctualityDiscount = () => {
  // Buscar do pagamento confirmado
  const confirmedPayment = getConfirmedPayment()
  if (confirmedPayment?.punctuality_discount !== undefined && !isNaN(confirmedPayment.punctuality_discount)) {
    return Number(confirmedPayment.punctuality_discount)
  }
  
  return 0
}

const getOtherDiscounts = () => {
  // Buscar do pagamento confirmado
  const confirmedPayment = getConfirmedPayment()
  if (confirmedPayment?.other_discounts !== undefined && !isNaN(confirmedPayment.other_discounts)) {
    return Number(confirmedPayment.other_discounts)
  }
  
  return 0
}

const getInterestAmount = () => {
  // Se já tem pagamento confirmado, usar o valor do pagamento
  const confirmedPayment = getConfirmedPayment()
  if (confirmedPayment) {
    if (confirmedPayment.interest_applied !== undefined && confirmedPayment.interest_applied !== null && !isNaN(confirmedPayment.interest_applied)) {
      return Number(confirmedPayment.interest_applied)
    }
  }
  
  // Se não tem pagamento, calcular baseado na data atual vs vencimento
  if (!props.installment?.due_date) return 0
  
  const dueDate = new Date(props.installment.due_date)
  const today = new Date()
  
  // Se pagamento antes ou na data de vencimento, sem juros
  if (today <= dueDate) return 0
  
  // Calcular dias de atraso
  const daysLate = Math.ceil((today - dueDate) / (1000 * 60 * 60 * 24))
  
  // Juros: 1% ao mês (proporcional aos dias)
  const monthlyInterestRate = 0.01 // 1%
  const baseAmount = getBaseAmount()
  const interest = (baseAmount * monthlyInterestRate * daysLate) / 30
  
  return Math.round(interest * 100) / 100
}

const getFineAmount = () => {
  // Se já tem pagamento confirmado, usar o valor do pagamento
  const confirmedPayment = getConfirmedPayment()
  if (confirmedPayment?.fine_applied !== undefined && !isNaN(confirmedPayment.fine_applied)) {
    return Number(confirmedPayment.fine_applied)
  }
  
  // Se não tem pagamento, calcular baseado na data atual vs vencimento
  if (!props.installment?.due_date) return 0
  
  const dueDate = new Date(props.installment.due_date)
  const today = new Date()
  
  // Se pagamento antes ou na data de vencimento, sem multa
  if (today <= dueDate) return 0
  
  // Multa: 2% sobre o valor base (cobrada uma única vez)
  const fineRate = 0.02 // 2%
  const baseAmount = getBaseAmount()
  const fine = baseAmount * fineRate
  
  return Math.round(fine * 100) / 100
}

const getFinalAmount = () => {
  // Se está paga, usar valor do pagamento confirmado
  if (props.installment.status === 'paid') {
    const confirmedPayment = getConfirmedPayment()
    if (confirmedPayment?.amount !== undefined && !isNaN(confirmedPayment.amount)) {
      return Number(confirmedPayment.amount)
    }
  }
  
  // Tentar do accessor
  if (props.installment.final_amount !== undefined && !isNaN(props.installment.final_amount)) {
    return Number(props.installment.final_amount)
  }
  
  // Calcular: base - descontos + juros + multa
  const base = getBaseAmount()
  const siblingDiscount = getSiblingDiscount()
  const punctualityDiscount = getPunctualityDiscount()
  const otherDiscounts = getOtherDiscounts()
  const interest = getInterestAmount()
  const fine = getFineAmount()
  
  return Math.max(0, base - siblingDiscount - punctualityDiscount - otherDiscounts + interest + fine)
}

const getConfirmedPayment = () => {
  if (!props.installment.payments || props.installment.payments.length === 0) {
    return null
  }
  
  return props.installment.payments.find(p => p.status === 'confirmed') || props.installment.payments[0]
}
</script>




