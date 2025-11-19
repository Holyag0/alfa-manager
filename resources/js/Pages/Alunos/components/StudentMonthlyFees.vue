<template>
  <div class="bg-white shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
      <div>
        <h3 class="text-lg font-medium text-gray-900">Mensalidades</h3>
        <p class="mt-1 text-sm text-gray-600">Mensalidades do aluno ({{ installments.length }} no total)</p>
      </div>
      
      <!-- Botão Gerar Mensalidades -->
      <button
        v-if="canGenerateMonthlyFees"
        @click="openGenerateModal"
        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
      >
        <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Gerar Mensalidades
      </button>
    </div>

    <div
      v-if="siblingDiscountHint"
      class="mx-6 mt-4 rounded-md bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700 flex items-start space-x-2"
    >
      <svg class="h-5 w-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 11-10 10A10 10 0 0112 2z" />
      </svg>
      <span>
        Este aluno possui irmãos ativos. Verifique se o desconto de irmão foi aplicado no serviço de mensalidade.
      </span>
    </div>
    <!-- Empty State - SEM turma -->
    <div v-if="!hasActiveEnrollmentWithClassroom" class="p-6 text-center">
      <svg class="mx-auto h-12 w-12 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">Aluno precisa estar vinculado a uma turma</h3>
      <p class="mt-1 text-sm text-gray-500">Para gerar mensalidades, o aluno deve ter uma matrícula ativa vinculada a uma turma.</p>
      <p class="mt-2 text-xs text-gray-400">Vá para a aba "Dados Pessoais" e vincule o aluno a uma turma.</p>
    </div>

    <!-- Empty State - COM turma mas SEM mensalidades -->
    <div v-else-if="installments.length === 0" class="p-6 text-center">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma mensalidade</h3>
      <p class="mt-1 text-sm text-gray-500">Este aluno ainda não possui mensalidades cadastradas.</p>
      <button
        @click="openGenerateModal"
        class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Gerar Mensalidades
      </button>
    </div>

    <!-- Lista de Mensalidades -->
    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mês</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimento</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr 
            v-for="row in tableRows" 
            :key="row.installment.id" 
            @click="viewDetails(row.installment)"
            class="cursor-pointer hover:bg-gray-50 hover:scale-[1.01] transition-all duration-200"
          >
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              {{ formatMonth(row.installment.reference_month) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              <div class="text-xs uppercase tracking-wide text-gray-400">
                {{ row.isPaid ? 'Pago em' : 'Vence em ' }}
              </div>
              <div class="text-sm text-gray-900">
                {{ formatDate(row.date) }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
              <div>{{ formatCurrency(row.amount)}}</div>
              <div v-if="row.isPaid && row.payment" class="text-xs text-gray-500 mt-1">
                Nº {{ row.payment.payment_number }}
                <span class="mx-1">•</span>
                {{ formatPaymentMethod(row.payment.method) }}
              </div>
              <template v-else>
                <!-- Para parcelas não pagas, mostrar desconto de irmão se houver -->
                <div v-if="row.installment.monthly_fee?.has_sibling_discount" class="text-xs text-green-600 mt-1">
                  Desc. irmão: {{ formatCurrency(row.installment.monthly_fee?.sibling_discount_amount || 0) }}
                </div>
              </template>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="getStatusClass(row.installment.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ getStatusLabel(row.installment.status) }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Resumo -->
    <div v-if="installments.length > 0" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
      <div class="flex justify-between text-sm">
        <div>
          <span class="text-gray-600">Total:</span>
          <span class="ml-2 font-semibold text-gray-900">{{ installments.length }} mensalidades</span>
        </div>
        <div class="flex space-x-6">
          <div>
            <span class="text-gray-600">Pagas:</span>
            <span class="ml-2 text-green-600 font-semibold">{{ paidCount }}</span>
          </div>
          <div>
            <span class="text-gray-600">Pendentes:</span>
            <span class="ml-2 text-yellow-600 font-semibold">{{ pendingCount }}</span>
          </div>
          <div>
            <span class="text-gray-600">Vencidas:</span>
            <span class="ml-2 text-red-600 font-semibold">{{ overdueCount }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Pagamento -->
    <PaymentModal 
      :show="showPaymentModal"
      :installment="selectedInstallment"
      :student="student"
      @close="closePaymentModal"
      @success="handlePaymentSuccess"
    />

    <!-- Modal de Detalhes -->
    <DetailsModal 
      v-if="showDetailsModal"
      :installment="selectedInstallment"
      :student="student"
      @close="closeDetailsModal"
    />

    <!-- Modal de Gerar Mensalidades -->
    <GenerateMonthlyFeesModal
      v-if="showGenerateModal"
      :student="student"
      :enrollment="activeEnrollment"
      @close="closeGenerateModal"
      @success="handleGenerateSuccess"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import PaymentModal from './PaymentModal.vue'
import DetailsModal from './InstallmentDetailsModal.vue'
import GenerateMonthlyFeesModal from './GenerateMonthlyFeesModal.vue'

const props = defineProps({
  student: {
    type: Object,
    required: true
  },
  installments: {
    type: Array,
    default: () => []
  },
  siblingDiscountHint: {
    type: Boolean,
    default: false
  }
})

const showPaymentModal = ref(false)
const showDetailsModal = ref(false)
const showGenerateModal = ref(false)
const selectedInstallment = ref(null)
const activeEnrollment = ref(null)

const paidCount = computed(() => props.installments.filter(i => i.status === 'paid').length)
const pendingCount = computed(() => props.installments.filter(i => i.status === 'pending').length)
const overdueCount = computed(() => props.installments.filter(i => i.status === 'overdue').length)

const paymentMethodLabels = {
  pix: 'PIX',
  credit_card: 'Cartão de Crédito',
  debit_card: 'Cartão de Débito',
  cash: 'Dinheiro',
  bank_transfer: 'Transferência Bancária',
  check: 'Cheque'
}

const findPrimaryPayment = (installment) => {
  if (!installment.payments || installment.payments.length === 0) {
    return null
  }

  const confirmed = installment.payments.find(payment => payment.status === 'confirmed')
  return confirmed || installment.payments[0]
}

const getServiceReferencePrice = (installment) => {
  // Sempre buscar do serviço atual (para reajustes automáticos)
  return Number(
    installment?.classroom_service?.service?.price ??
    installment?.classroom_service?.price ??
    0
  )
}

const formatPaymentMethod = (method) => {
  if (!method) return '-'
  return paymentMethodLabels[method] ?? method.toUpperCase()
}

const tableRows = computed(() => props.installments.map((installment) => {
  const payment = findPrimaryPayment(installment)
  const isPaid = installment.status === 'paid'
  
  // Se paga, usar valor do pagamento; senão, usar valor do serviço atual
  const amount = isPaid && payment 
    ? Number(payment.amount ?? 0) 
    : getServiceReferencePrice(installment) // Valor atual do serviço (reajuste automático)
  
  const date = isPaid && payment ? payment.payment_date : installment.due_date

  return {
    installment,
    payment,
    isPaid,
    amount,
    date
  }
}))

// Verifica se o aluno tem matrícula ativa com turma vinculada
const hasActiveEnrollmentWithClassroom = computed(() => {
  if (!props.student.enrollments || props.student.enrollments.length === 0) {
    return false
  }
  
  const enrollment = props.student.enrollments.find(e => 
    e.status === 'active' && e.classroom_id
  )
  
  if (enrollment) {
    activeEnrollment.value = enrollment
    return true
  }
  
  return false
})

// Pode gerar mensalidades se tem turma vinculada E não tem mensalidades
const canGenerateMonthlyFees = computed(() => {
  return hasActiveEnrollmentWithClassroom.value && props.installments.length === 0
})

const openPaymentModal = (installment) => {
  selectedInstallment.value = installment
  showPaymentModal.value = true
}

const closePaymentModal = () => {
  showPaymentModal.value = false
  selectedInstallment.value = null
}

const handlePaymentSuccess = () => {
  closePaymentModal()
  // Recarregar página completa para atualizar dados
  router.reload()
}

const viewDetails = (installment) => {
  selectedInstallment.value = installment
  showDetailsModal.value = true
}

const closeDetailsModal = () => {
  showDetailsModal.value = false
  selectedInstallment.value = null
}


const openGenerateModal = () => {
  showGenerateModal.value = true
}

const closeGenerateModal = () => {
  showGenerateModal.value = false
}

const handleGenerateSuccess = () => {
  closeGenerateModal()
  // Aguardar um momento para garantir que a requisição foi processada
  // Depois recarregar os dados de installments e student
  setTimeout(() => {
    router.reload({
      only: ['installments', 'student'],
      preserveScroll: false
    })
  }, 500)
}

const formatMonth = (monthString) => {
  if (!monthString) return ''
  const [year, month] = monthString.split('-')
  const date = new Date(year, month - 1, 1)
  return date.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleDateString('pt-BR')
}

const formatCurrency = (value) => {
  if (value === null || value === undefined) {
    return 'R$ 0,00'
  }
  const amount = Number(value)
  if (Number.isNaN(amount)) {
    return 'R$ 0,00'
  }
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(amount)
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
</script>


