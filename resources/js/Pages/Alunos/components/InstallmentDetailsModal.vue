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
                <span class="text-gray-600">Ano Letivo:</span>
                <p class="font-medium">
                  <span class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded-md text-xs font-semibold">
                    {{ installment.monthly_fee?.academic_year || 'Não definido' }}
                  </span>
                </p>
              </div>
              <div>
                <span class="text-gray-600">Mês de Referência:</span>
                <p class="font-medium">{{ formatMonth(installment.reference_month) }}</p>
              </div>
              <div>
                <span class="text-gray-600">Parcela:</span>
                <p class="font-medium">{{ installment.installment_number }}/{{ installment.monthly_fee?.total_installments || 12 }}</p>
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
                  <div class="flex-1">
                    <p class="font-medium text-gray-900">{{ formatCurrency(payment.amount) }}</p>
                    <p class="text-xs text-gray-600">{{ getMethodLabel(payment.method) }}</p>
                  </div>
                  <div class="flex items-center space-x-2">
                  <span :class="getPaymentStatusClass(payment.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ getPaymentStatusLabel(payment.status) }}
                  </span>
                    <button
                      v-if="canRevertPayment(payment)"
                      @click="handleRevertPayment(payment)"
                      class="inline-flex items-center px-2 py-1 border border-red-300 rounded text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500"
                      title="Reverter pagamento"
                    >
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                      </svg>
                      Reverter
                    </button>
                  </div>
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
          <div class="flex flex-col space-y-3">
            <!-- Botões de Ação Principais -->
          <div class="flex justify-between items-center">
              <div class="flex space-x-2">
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
                <!-- Removido: Botão "Editar Pagamento" 
                     Para corrigir erros em pagamentos confirmados, use "Reverter Pagamento" 
                     e registre o pagamento novamente com os valores corretos -->
                <button 
                  v-if="canEditInstallment"
                  @click="handleEditDueDate"
                  :class="[
                    'inline-flex items-center px-4 py-2 border rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2',
                    hasConfirmedPayment 
                      ? 'border-yellow-300 text-yellow-700 bg-yellow-50 hover:bg-yellow-100 focus:ring-yellow-500' 
                      : 'border-blue-300 text-blue-700 bg-blue-50 hover:bg-blue-100 focus:ring-blue-500'
                  ]"
                  :title="hasConfirmedPayment ? 'Atenção: Esta mensalidade possui pagamento confirmado. Recomenda-se reverter o pagamento antes de editar.' : ''"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  Editar Vencimento
                  <svg v-if="hasConfirmedPayment" class="w-4 h-4 ml-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                </button>
                <button 
                  v-if="canDeleteInstallment"
                  @click="handleDeleteInstallment"
                  :class="[
                    'inline-flex items-center px-4 py-2 border rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2',
                    hasConfirmedPayment 
                      ? 'border-yellow-300 text-yellow-700 bg-yellow-50 hover:bg-yellow-100 focus:ring-yellow-500' 
                      : 'border-red-300 text-red-700 bg-red-50 hover:bg-red-100 focus:ring-red-500'
                  ]"
                  :title="hasConfirmedPayment ? 'Atenção: Esta mensalidade possui pagamento confirmado. Recomenda-se reverter o pagamento antes de deletar.' : ''"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                  Deletar
                  <svg v-if="hasConfirmedPayment" class="w-4 h-4 ml-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                </button>
              </div>
            <button 
              @click="$emit('close')"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
              Fechar
            </button>
            </div>
            
            <!-- Botões de Ações de Serviço -->
            <div v-if="canChangeService" class="flex space-x-2 pt-2 border-t border-gray-200">
              <button 
                @click="handleChangeService"
                class="inline-flex items-center px-3 py-1.5 border border-orange-300 rounded-md shadow-sm text-xs font-medium text-orange-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
              >
                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                Trocar Serviço (Esta Parcela)
              </button>
              <button 
                @click="handleChangeAllServices"
                class="inline-flex items-center px-3 py-1.5 border border-orange-300 rounded-md shadow-sm text-xs font-medium text-orange-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
              >
                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Trocar Serviço (Todas do Ano)
              </button>
            </div>
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

    <!-- Removido: Modal de Editar Pagamento
         Para corrigir erros, use "Reverter Pagamento" e registre novamente -->

    <!-- Modal de Trocar Serviço -->
      <ChangeServiceModal
      v-if="showChangeServiceModal"
      :show="showChangeServiceModal"
      :installment="installment"
      :change-all="changeAllServices"
      @close="closeChangeServiceModal"
      @success="handleChangeServiceSuccess"
    />
    
    <RevertPaymentModal
      v-if="showRevertPaymentModal"
      :show="showRevertPaymentModal"
      :payment="selectedPaymentForRevert"
      :installment="installment"
      @close="closeRevertPaymentModal"
      @success="handleRevertPaymentSuccess"
    />
    
    <EditDueDateModal
      v-if="showEditDueDateModal"
      :show="showEditDueDateModal"
      :installment="installment"
      @close="closeEditDueDateModal"
      @success="handleEditDueDateSuccess"
    />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import PaymentModal from './PaymentModal.vue'
// Removido: EditPaymentModal - use Reverter Pagamento para corrigir erros
import ChangeServiceModal from './ChangeServiceModal.vue'
import RevertPaymentModal from './RevertPaymentModal.vue'
import EditDueDateModal from './EditDueDateModal.vue'

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
// Removido: showEditPaymentModal - funcionalidade de editar pagamento removida
const showChangeServiceModal = ref(false)
const showRevertPaymentModal = ref(false)
const showEditDueDateModal = ref(false)
const showDeleteInstallmentModal = ref(false)
// Removido: selectedPayment - não é mais necessário após remover edição de pagamento
const selectedPaymentForRevert = ref(null)
const changeAllServices = ref(false)

const canRegisterPayment = computed(() => {
  return props.installment.status !== 'paid' && 
         props.installment.status !== 'cancelled' &&
         props.installment.status !== 'waived'
})

// Removido: canEditPayment - funcionalidade de editar pagamento removida
// Para corrigir erros, use "Reverter Pagamento" e registre o pagamento novamente

const canChangeService = computed(() => {
  // Pode trocar serviço se não tiver pagamento confirmado
  const confirmedPayment = getConfirmedPayment()
  return !confirmedPayment || confirmedPayment.status !== 'confirmed'
})

// Verificar se há pagamento confirmado (para aviso, não bloqueia)
const hasConfirmedPayment = computed(() => {
  const confirmedPayment = getConfirmedPayment()
  return confirmedPayment !== null && confirmedPayment.status === 'confirmed'
})

// Sempre permite editar/deletar para flexibilidade em correção de erros
// O backend registra aviso no log se houver pagamento confirmado
const canEditInstallment = computed(() => {
  return true // Sempre permite editar
})

const canDeleteInstallment = computed(() => {
  return true // Sempre permite deletar
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

// Removido: handleEditPayment, closeEditPaymentModal, handleEditPaymentSuccess
// Funcionalidade de editar pagamento removida - use Reverter Pagamento para corrigir erros

const handleChangeService = () => {
  changeAllServices.value = false
  showChangeServiceModal.value = true
}

const handleChangeAllServices = () => {
  changeAllServices.value = true
  showChangeServiceModal.value = true
}

const closeChangeServiceModal = () => {
  showChangeServiceModal.value = false
  changeAllServices.value = false
}

const handleChangeServiceSuccess = () => {
  closeChangeServiceModal()
  router.reload()
}

const canRevertPayment = (payment) => {
  // Pode reverter se o pagamento estiver confirmado ou pendente
  // Não pode reverter se já foi cancelado ou estornado
  return payment.status === 'confirmed' || payment.status === 'pending'
}

const handleRevertPayment = (payment) => {
  selectedPaymentForRevert.value = payment
  showRevertPaymentModal.value = true
}

const closeRevertPaymentModal = () => {
  showRevertPaymentModal.value = false
  selectedPaymentForRevert.value = null
}

const handleRevertPaymentSuccess = () => {
  closeRevertPaymentModal()
  router.reload()
}

const handleEditDueDate = () => {
  if (hasConfirmedPayment.value) {
    const proceed = confirm('⚠️ ATENÇÃO: Esta mensalidade possui pagamento confirmado.\n\n' +
                           'Recomenda-se reverter o pagamento antes de editar para manter a integridade dos dados.\n\n' +
                           'Deseja continuar mesmo assim?')
    if (!proceed) {
      return
    }
  }
  showEditDueDateModal.value = true
}

const closeEditDueDateModal = () => {
  showEditDueDateModal.value = false
}

const handleEditDueDateSuccess = () => {
  closeEditDueDateModal()
  router.reload()
}

const handleDeleteInstallment = () => {
  let confirmMessage = 'Tem certeza que deseja deletar esta mensalidade? Esta ação não pode ser desfeita.'
  
  if (hasConfirmedPayment.value) {
    confirmMessage = '⚠️ ATENÇÃO: Esta mensalidade possui pagamento confirmado.\n\n' +
                     'Recomenda-se reverter o pagamento antes de deletar para manter a integridade dos dados.\n\n' +
                     'Deseja continuar mesmo assim?'
  }
  
  if (confirm(confirmMessage)) {
    deleteInstallment()
  }
}

const deleteInstallment = async () => {
  try {
    await axios.delete(`/api/installments/${props.installment.id}`)
    alert('Mensalidade deletada com sucesso!')
    emit('close')
    router.reload()
  } catch (error) {
    alert('Erro ao deletar mensalidade: ' + (error.response?.data?.message || error.message))
  }
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




