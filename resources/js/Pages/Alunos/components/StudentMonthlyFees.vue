<template>
  <div class="bg-white shadow rounded-lg overflow-hidden">
    <!-- Header Principal -->
    <div class="px-6 py-5 bg-gradient-to-r from-indigo-50 to-white border-b border-gray-200">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex-1">
          <div class="flex items-center space-x-3">
            <div class="p-2 bg-indigo-100 rounded-lg">
              <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <h3 class="text-xl font-semibold text-gray-900">Mensalidades</h3>
          <p class="mt-1 text-sm text-gray-600">
            <span class="text-indigo-600 font-medium">Ano Letivo {{ selectedAcademicYear }}</span>
            <span class="text-gray-500"> • {{ filteredInstallments.length }} mensalidade{{ filteredInstallments.length !== 1 ? 's' : '' }}</span>
            <span v-if="availableAcademicYears.length > 1" class="text-gray-500">
              ({{ availableAcademicYears.length }} anos disponíveis)
            </span>
          </p>
            </div>
          </div>
        </div>
        
        <!-- Botão Gerar Mensalidades -->
        <button
          v-if="canGenerateMonthlyFees"
          @click="openGenerateModal"
          class="inline-flex items-center px-4 py-2.5 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200"
        >
          <svg class="mr-2 -ml-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
          </svg>
          Gerar Mensalidades
        </button>
      </div>
    </div>

    <!-- Filtros e Navegação por Ano Letivo -->
    <div v-if="availableAcademicYears.length > 0" class="px-6 py-4 bg-gray-50 border-b border-gray-200">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex-1">
          <label class="text-sm font-semibold text-gray-700 mb-3 block">Ano Letivo:</label>
          
          <!-- Indicadores de Anos Disponíveis (Pills) -->
          <div class="flex flex-wrap gap-2">
            <button
              v-for="year in availableAcademicYears"
              :key="year"
              @click="selectedAcademicYear = year"
              :class="[
                'px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200',
                selectedAcademicYear === year
                  ? 'bg-indigo-600 text-white shadow-md transform scale-105'
                  : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300 shadow-sm hover:border-indigo-300'
              ]"
            >
              {{ year }}
              <span :class="selectedAcademicYear === year ? 'opacity-90' : 'opacity-60'" class="ml-1.5">
                ({{ getYearCount(year) }})
              </span>
            </button>
          </div>
        </div>
        
        <!-- Informação do Ano Selecionado -->
        <div class="flex items-center space-x-2">
          <span class="px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-lg text-sm font-medium shadow-sm">
            {{ filteredInstallments.length }} mensalidade{{ filteredInstallments.length !== 1 ? 's' : '' }}
          </span>
        </div>
      </div>
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
    <div v-else-if="filteredInstallments.length === 0" class="p-12 text-center">
      <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">
        Nenhuma mensalidade para {{ selectedAcademicYear }}
      </h3>
      <p class="text-sm text-gray-500 max-w-md mx-auto">
        Este aluno não possui mensalidades cadastradas para o ano letivo {{ selectedAcademicYear }}.
        <span v-if="availableAcademicYears.length > 1" class="block mt-2 text-indigo-600">
          Tente selecionar outro ano letivo usando o filtro acima.
        </span>
      </p>
    </div>

    <!-- Lista de Mensalidades -->
    <div v-else class="overflow-x-auto">
      <!-- Agrupar por Ano Letivo -->
      <div v-for="(group, academicYear) in groupedByAcademicYear" :key="academicYear" class="mb-6 last:mb-0">
        <!-- Header do Grupo (Ano Letivo) -->
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4 shadow-sm">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center space-x-3">
              <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
              </div>
              <div>
                <h4 class="text-lg font-bold text-white">
                  Ano Letivo {{ academicYear }}
                </h4>
                <p class="text-sm text-indigo-100 mt-0.5">
                  {{ group.length }} mensalidade{{ group.length !== 1 ? 's' : '' }}
                </p>
              </div>
            </div>
            <div class="flex items-center space-x-4">
              <div class="text-right">
                <div class="text-xs text-indigo-200 uppercase tracking-wide mb-1">Resumo</div>
                <div class="text-sm font-semibold text-white">
                  {{ getYearSummary(group) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="border border-gray-200 border-t-0 rounded-b-lg overflow-hidden shadow-sm">
          <!-- Barra de ações em lote -->
          <div v-if="selectedInstallments.length > 0" class="bg-blue-50 border-b border-blue-200 px-6 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <span class="text-sm font-medium text-blue-900">
                {{ selectedInstallments.length }} mensalidade(s) selecionada(s)
              </span>
            </div>
            <div class="flex items-center space-x-2">
              <button
                @click.stop="openBatchEditDueDateModal"
                class="inline-flex items-center px-3 py-1.5 border border-blue-300 rounded-md text-xs font-medium text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Editar Vencimento
              </button>
              <button
                @click.stop="openBatchDeleteModal"
                class="inline-flex items-center px-3 py-1.5 border border-red-300 rounded-md text-xs font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500"
              >
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Deletar
              </button>
              <button
                @click.stop="clearSelection"
                class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500"
              >
                Limpar Seleção
              </button>
            </div>
          </div>
          
          <table class="min-w-full divide-y divide-gray-200 bg-white">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3.5 text-left">
                  <input
                    type="checkbox"
                    :checked="isAllSelected(group)"
                    @change="toggleSelectAll(group, $event)"
                    @click.stop
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  />
                </th>
                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Mês</th>
                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Vencimento</th>
                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Valor</th>
                <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr 
                v-for="row in group" 
                :key="row.installment.id" 
                @click="viewDetails(row.installment)"
                :class="[
                  'cursor-pointer hover:bg-indigo-50 transition-colors duration-150 group',
                  isSelected(row.installment.id) ? 'bg-blue-50' : ''
                ]"
              >
                <td class="px-6 py-4 whitespace-nowrap" @click.stop>
                  <input
                    type="checkbox"
                    :checked="isSelected(row.installment.id)"
                    @change="toggleSelect(row.installment.id, $event)"
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-semibold text-gray-900 group-hover:text-indigo-700">
                    {{ formatMonth(row.installment.reference_month) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-xs uppercase tracking-wide text-gray-400 mb-1">
                    {{ row.isPaid ? 'Pago em' : (isOverdue(row.installment) ? 'Vencido em' : 'Vence em') }}
                  </div>
                  <div class="text-sm font-medium" :class="isOverdue(row.installment) && !row.isPaid ? 'text-red-600 font-semibold' : 'text-gray-900'">
                    {{ formatDate(row.date) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-bold text-gray-900">{{ formatCurrency(row.amount) }}</div>
                  <div v-if="row.isPaid && row.payment" class="text-xs text-gray-500 mt-1.5 flex items-center space-x-1">
                    <span>Nº {{ row.payment.payment_number }}</span>
                    <span>•</span>
                    <span>{{ formatPaymentMethod(row.payment.method) }}</span>
                  </div>
                  <template v-else>
                    <!-- Para parcelas não pagas, mostrar desconto de irmão se houver -->
                    <div v-if="row.installment.monthly_fee?.has_sibling_discount" class="text-xs text-green-600 mt-1.5 font-medium">
                      <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                      Desc. irmão: {{ formatCurrency(row.installment.monthly_fee?.sibling_discount_amount || 0) }}
                    </div>
                  </template>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(row.displayStatus || row.installment.status)" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ getStatusLabel(row.displayStatus || row.installment.status) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Resumo Geral -->
    <div v-if="filteredInstallments.length > 0" class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-t border-gray-200">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-2">
          <span class="text-sm font-medium text-gray-700">Total:</span>
          <span class="text-lg font-bold text-gray-900">
            {{ filteredInstallments.length }} mensalidade{{ filteredInstallments.length !== 1 ? 's' : '' }}
          </span>
          <span class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded-md text-xs font-medium">
            Ano Letivo {{ selectedAcademicYear }}
          </span>
        </div>
        <div class="flex flex-wrap items-center gap-4">
          <div class="flex items-center space-x-2">
            <div class="w-3 h-3 rounded-full bg-green-500"></div>
            <span class="text-sm text-gray-600">Pagas:</span>
            <span class="text-sm font-bold text-green-600">{{ paidCount }}</span>
          </div>
          <div class="flex items-center space-x-2">
            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
            <span class="text-sm text-gray-600">Pendentes:</span>
            <span class="text-sm font-bold text-yellow-600">{{ pendingCount }}</span>
          </div>
          <div class="flex items-center space-x-2">
            <div class="w-3 h-3 rounded-full bg-red-500"></div>
            <span class="text-sm text-gray-600">Vencidas:</span>
            <span class="text-sm font-bold text-red-600">{{ overdueCount }}</span>
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

    <!-- Modal de Editar Vencimento em Lote -->
    <BatchEditDueDateModal
      v-if="showBatchEditDueDateModal"
      :show="showBatchEditDueDateModal"
      :installment-ids="selectedInstallments"
      @close="closeBatchEditDueDateModal"
      @success="handleBatchEditDueDateSuccess"
    />

    <!-- Modal de Deletar em Lote -->
    <BatchDeleteModal
      v-if="showBatchDeleteModal"
      :show="showBatchDeleteModal"
      :installment-ids="selectedInstallments"
      @close="closeBatchDeleteModal"
      @success="handleBatchDeleteSuccess"
    />

    <!-- Modal de Gerar Mensalidades -->
    <GenerateMonthlyFeesModal
      v-if="showGenerateModal"
      :student="student"
      :enrollment="activeEnrollment"
      :installments="installments"
      :enrollments="student.enrollments || []"
      @close="closeGenerateModal"
      @success="handleGenerateSuccess"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import PaymentModal from './PaymentModal.vue'
import DetailsModal from './InstallmentDetailsModal.vue'
import GenerateMonthlyFeesModal from './GenerateMonthlyFeesModal.vue'
import BatchEditDueDateModal from './BatchEditDueDateModal.vue'
import BatchDeleteModal from './BatchDeleteModal.vue'

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
const showBatchEditDueDateModal = ref(false)
const showBatchDeleteModal = ref(false)
const selectedInstallment = ref(null)
const selectedInstallments = ref([])
const activeEnrollment = ref(null)

// Extrair anos letivos únicos das mensalidades
const availableAcademicYears = computed(() => {
  const years = new Set()
  props.installments.forEach(installment => {
    if (installment.monthly_fee?.academic_year) {
      years.add(installment.monthly_fee.academic_year)
    }
  })
  return Array.from(years).sort((a, b) => b.localeCompare(a)) // Mais recente primeiro
})

// Sempre selecionar o último ano letivo (mais recente) por padrão
const selectedAcademicYear = ref('')

// Inicializar com o último ano quando os anos estiverem disponíveis
watch(availableAcademicYears, (years) => {
  if (years.length > 0 && !selectedAcademicYear.value) {
    selectedAcademicYear.value = years[0] // Primeiro = mais recente
  }
}, { immediate: true })

// Filtrar mensalidades por ano letivo selecionado
const filteredInstallments = computed(() => {
  if (!selectedAcademicYear.value) {
    return props.installments
  }
  return props.installments.filter(installment => 
    installment.monthly_fee?.academic_year === selectedAcademicYear.value
  )
})

// Função para verificar se uma mensalidade está vencida
const isOverdue = (installment) => {
  // Se já está paga, cancelada ou dispensada, não está vencida
  if (['paid', 'cancelled', 'waived'].includes(installment.status)) {
    return false
  }
  
  // Se o status já é overdue, está vencida
  if (installment.status === 'overdue') {
    return true
  }
  
  // Se está pendente, verificar se a data de vencimento passou
  if (installment.status === 'pending' && installment.due_date) {
    // Função para parsear data sem problemas de timezone
    const parseDateLocal = (dateString) => {
      if (!dateString) return null
      const match = dateString.match(/^(\d{4})-(\d{2})-(\d{2})/)
      if (match) {
        const year = parseInt(match[1], 10)
        const month = parseInt(match[2], 10) - 1 // month é 0-indexed
        const day = parseInt(match[3], 10)
        return new Date(year, month, day)
      }
      return new Date(dateString)
    }
    
    const dueDate = parseDateLocal(installment.due_date)
    const today = new Date()
    // Comparar apenas datas (sem hora)
    today.setHours(0, 0, 0, 0)
    if (dueDate) {
      dueDate.setHours(0, 0, 0, 0)
      return dueDate < today
    }
  }
  
  return false
}

// Função para obter o status visual (considerando vencimento)
const getDisplayStatus = (installment) => {
  if (isOverdue(installment)) {
    return 'overdue'
  }
  return installment.status
}

// Agrupar mensalidades por ano letivo
const groupedByAcademicYear = computed(() => {
  const groups = {}
  const installmentsToUse = selectedAcademicYear.value ? filteredInstallments.value : props.installments
  
  installmentsToUse.forEach(installment => {
    const academicYear = installment.monthly_fee?.academic_year || 'Sem ano'
    if (!groups[academicYear]) {
      groups[academicYear] = []
    }
    
    const payment = findPrimaryPayment(installment)
    const isPaid = installment.status === 'paid'
    const amount = isPaid && payment 
      ? Number(payment.amount ?? 0) 
      : getServiceReferencePrice(installment)
    const date = isPaid && payment ? payment.payment_date : installment.due_date
    
    groups[academicYear].push({
      installment,
      payment,
      isPaid,
      amount,
      date,
      displayStatus: getDisplayStatus(installment) // Status visual considerando vencimento
    })
  })
  
  // Ordenar grupos por ano (mais recente primeiro)
  const sortedGroups = {}
  Object.keys(groups).sort((a, b) => {
    if (a === 'Sem ano') return 1
    if (b === 'Sem ano') return -1
    return b.localeCompare(a)
  }).forEach(year => {
    sortedGroups[year] = groups[year].sort((a, b) => {
      // Ordenar por reference_month para garantir ordem cronológica correta
      const monthA = a.installment.reference_month || ''
      const monthB = b.installment.reference_month || ''
      return monthA.localeCompare(monthB)
    })
  })
  
  return sortedGroups
})

// Resumo do ano letivo
const getYearSummary = (group) => {
  const paid = group.filter(r => r.isPaid).length
  const pending = group.filter(r => {
    const displayStatus = r.displayStatus || r.installment.status
    return displayStatus === 'pending'
  }).length
  const overdue = group.filter(r => {
    const displayStatus = r.displayStatus || r.installment.status
    return displayStatus === 'overdue'
  }).length
  
  const parts = []
  if (paid > 0) parts.push(`${paid} paga${paid !== 1 ? 's' : ''}`)
  if (pending > 0) parts.push(`${pending} pendente${pending !== 1 ? 's' : ''}`)
  if (overdue > 0) parts.push(`${overdue} vencida${overdue !== 1 ? 's' : ''}`)
  
  return parts.join(' • ') || 'Nenhuma mensalidade'
}

// Contar mensalidades por ano
const getYearCount = (year) => {
  return props.installments.filter(i => 
    i.monthly_fee?.academic_year === year
  ).length
}


const paidCount = computed(() => {
  const installmentsToUse = selectedAcademicYear.value ? filteredInstallments.value : props.installments
  return installmentsToUse.filter(i => i.status === 'paid').length
})
const pendingCount = computed(() => {
  const installmentsToUse = selectedAcademicYear.value ? filteredInstallments.value : props.installments
  return installmentsToUse.filter(i => {
    // Contar como pendente apenas se não estiver vencida
    return i.status === 'pending' && !isOverdue(i)
  }).length
})
const overdueCount = computed(() => {
  const installmentsToUse = selectedAcademicYear.value ? filteredInstallments.value : props.installments
  return installmentsToUse.filter(i => {
    // Contar como vencida se status é overdue OU se está pendente e vencida
    return i.status === 'overdue' || (i.status === 'pending' && isOverdue(i))
  }).length
})

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

// tableRows não é mais necessário - usando groupedByAcademicYear diretamente

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

// Pode gerar mensalidades se tem turma vinculada
// Não precisa verificar se já tem mensalidades - pode substituir se necessário
const canGenerateMonthlyFees = computed(() => {
  return hasActiveEnrollmentWithClassroom.value
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

// Funções de seleção para edição em lote
const isSelected = (installmentId) => {
  return selectedInstallments.value.includes(installmentId)
}

const toggleSelect = (installmentId, event) => {
  event.stopPropagation()
  const index = selectedInstallments.value.indexOf(installmentId)
  if (index > -1) {
    selectedInstallments.value.splice(index, 1)
  } else {
    selectedInstallments.value.push(installmentId)
  }
}

const isAllSelected = (group) => {
  if (group.length === 0) return false
  return group.every(row => selectedInstallments.value.includes(row.installment.id))
}

const toggleSelectAll = (group, event) => {
  event.stopPropagation()
  const allSelected = isAllSelected(group)
  if (allSelected) {
    // Desmarcar todas do grupo
    group.forEach(row => {
      const index = selectedInstallments.value.indexOf(row.installment.id)
      if (index > -1) {
        selectedInstallments.value.splice(index, 1)
      }
    })
  } else {
    // Marcar todas do grupo
    group.forEach(row => {
      if (!selectedInstallments.value.includes(row.installment.id)) {
        selectedInstallments.value.push(row.installment.id)
      }
    })
  }
}

const clearSelection = () => {
  selectedInstallments.value = []
}

const openBatchEditDueDateModal = () => {
  if (selectedInstallments.value.length === 0) return
  showBatchEditDueDateModal.value = true
}

const closeBatchEditDueDateModal = () => {
  showBatchEditDueDateModal.value = false
}

const handleBatchEditDueDateSuccess = () => {
  closeBatchEditDueDateModal()
  clearSelection()
  router.reload()
}

const openBatchDeleteModal = () => {
  if (selectedInstallments.value.length === 0) return
  showBatchDeleteModal.value = true
}

const closeBatchDeleteModal = () => {
  showBatchDeleteModal.value = false
}

const handleBatchDeleteSuccess = () => {
  closeBatchDeleteModal()
  clearSelection()
  router.reload()
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


