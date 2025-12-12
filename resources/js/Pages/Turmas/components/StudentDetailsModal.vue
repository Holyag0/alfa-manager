<template>
  <Dialog :open="show" @close="closeModal" class="relative z-50">
    <div class="fixed inset-0 bg-black/25" aria-hidden="true" />

    <div class="fixed inset-0 overflow-y-auto">
      <div class="flex min-h-full items-center justify-center p-4">
        <DialogPanel class="w-full max-w-4xl transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all">
              <!-- Header -->
              <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-5">
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-4">
                    <div v-if="student?.photo" class="flex-shrink-0">
                      <img 
                        :src="`/storage/${student.photo}`" 
                        :alt="student?.name"
                        class="h-16 w-16 rounded-full object-cover ring-2 ring-white"
                      />
                    </div>
                    <div v-else class="flex-shrink-0">
                      <div class="h-16 w-16 rounded-full bg-white flex items-center justify-center text-2xl font-bold text-indigo-600 ring-2 ring-white">
                        {{ student?.name?.charAt(0)?.toUpperCase() || '?' }}
                      </div>
                    </div>
                    <div>
                      <DialogTitle class="text-xl font-bold text-white">
                        {{ student?.name || 'Aluno' }}
                      </DialogTitle>
                      <p class="text-sm text-indigo-100 mt-1">
                        Informações completas do aluno
                      </p>
                    </div>
                  </div>
                  <button
                    @click="closeModal"
                    class="rounded-md text-indigo-100 hover:text-white focus:outline-none focus:ring-2 focus:ring-white"
                  >
                    <XMarkIcon class="h-6 w-6" />
                  </button>
                </div>
              </div>

              <!-- Content -->
              <div class="px-6 py-6 max-h-[calc(100vh-200px)] overflow-y-auto">
                <div v-if="loading" class="flex items-center justify-center py-12">
                  <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                </div>

                <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
                  <div class="flex">
                    <div class="flex-shrink-0">
                      <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
                    </div>
                    <div class="ml-3">
                      <h3 class="text-sm font-medium text-red-800">Erro ao carregar dados</h3>
                      <div class="mt-2 text-sm text-red-700">{{ error }}</div>
                    </div>
                  </div>
                </div>

                <div v-else class="space-y-6">
                  <!-- Informações Pessoais -->
                  <div class="bg-gray-50 rounded-lg p-5">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                      <UserIcon class="h-5 w-5 mr-2 text-indigo-600" />
                      Informações Pessoais
                    </h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <dt class="text-sm font-medium text-gray-500">Nome Completo</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ student?.name || '-' }}</dd>
                      </div>
                      <div>
                        <dt class="text-sm font-medium text-gray-500">Data de Nascimento</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(student?.birth_date) }}</dd>
                      </div>
                      <div>
                        <dt class="text-sm font-medium text-gray-500">CPF</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ formatCPF(student?.cpf) }}</dd>
                      </div>
                      <div>
                        <dt class="text-sm font-medium text-gray-500">RG</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ student?.rg || '-' }}</dd>
                      </div>
                      <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ student?.email || '-' }}</dd>
                      </div>
                      <div>
                        <dt class="text-sm font-medium text-gray-500">Telefone</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ formatPhone(student?.phone) }}</dd>
                      </div>
                    </dl>
                  </div>

                  <!-- Status da Matrícula -->
                  <div class="bg-gray-50 rounded-lg p-5">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                      <AcademicCapIcon class="h-5 w-5 mr-2 text-indigo-600" />
                      Status da Matrícula
                    </h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <dt class="text-sm font-medium text-gray-500">Turma</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ enrollment?.classroom?.name || '-' }}</dd>
                      </div>
                      <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                          <span 
                            :class="getEnrollmentStatusClass(enrollment?.status)"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          >
                            {{ getEnrollmentStatusLabel(enrollment?.status) }}
                          </span>
                        </dd>
                      </div>
                      <div>
                        <dt class="text-sm font-medium text-gray-500">Processo</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ enrollment?.process === 'completa' ? 'Completa' : 'Incompleta' }}</dd>
                      </div>
                      <div>
                        <dt class="text-sm font-medium text-gray-500">Data de Matrícula</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(enrollment?.enrollment_date) }}</dd>
                      </div>
                      <div>
                        <dt class="text-sm font-medium text-gray-500">Ano Letivo</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ enrollment?.academic_year || '-' }}</dd>
                      </div>
                      <div>
                        <dt class="text-sm font-medium text-gray-500">Responsável</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ enrollment?.guardian?.name || '-' }}</dd>
                      </div>
                    </dl>
                  </div>

                  <!-- Informações Financeiras - Mensalidades -->
                  <div class="bg-gray-50 rounded-lg p-5">
                    <div class="flex items-center justify-between mb-4">
                      <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <CurrencyDollarIcon class="h-5 w-5 mr-2 text-indigo-600" />
                        Mensalidades
                      </h3>
                      <button
                        v-if="!hasMonthlyFees && enrollment?.status === 'active'"
                        @click="goToGenerateMonthlyFees"
                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                      >
                        <PlusIcon class="h-4 w-4 mr-1" />
                        Gerar Mensalidades
                      </button>
                    </div>

                    <!-- Feedback se não houver mensalidades -->
                    <div v-if="!hasMonthlyFees" class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                      <div class="flex">
                        <div class="flex-shrink-0">
                          <ExclamationCircleIcon class="h-5 w-5 text-yellow-400" />
                        </div>
                        <div class="ml-3">
                          <h3 class="text-sm font-medium text-yellow-800">Nenhuma mensalidade cadastrada</h3>
                          <div class="mt-2 text-sm text-yellow-700">
                            <p>Este aluno ainda não possui mensalidades cadastradas. Clique em "Gerar Mensalidades" para criar as mensalidades do aluno.</p>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Lista de Mensalidades -->
                    <div v-else class="mt-4">
                      <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                          <thead class="bg-gray-100">
                            <tr>
                              <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Mês</th>
                              <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Vencimento</th>
                              <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Valor</th>
                              <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
                            </tr>
                          </thead>
                          <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="installment in monthlyFees" :key="installment.id">
                              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ formatMonth(installment.reference_month) }}
                              </td>
                              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ formatDate(installment.due_date) }}
                              </td>
                              <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ formatCurrency(getInstallmentAmount(installment)) }}
                              </td>
                              <td class="px-4 py-3 whitespace-nowrap">
                                <span 
                                  :class="getStatusClass(installment.status)"
                                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                >
                                  {{ getStatusLabel(installment.status) }}
                                </span>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>

                      <!-- Resumo Financeiro -->
                      <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="grid grid-cols-3 gap-4">
                          <div>
                            <dt class="text-sm font-medium text-gray-500">Total Pendente</dt>
                            <dd class="mt-1 text-lg font-semibold text-yellow-600">{{ formatCurrency(totalPending) }}</dd>
                          </div>
                          <div>
                            <dt class="text-sm font-medium text-gray-500">Total Pago</dt>
                            <dd class="mt-1 text-lg font-semibold text-green-600">{{ formatCurrency(totalPaid) }}</dd>
                          </div>
                          <div>
                            <dt class="text-sm font-medium text-gray-500">Total Vencido</dt>
                            <dd class="mt-1 text-lg font-semibold text-red-600">{{ formatCurrency(totalOverdue) }}</dd>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Footer -->
              <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <button
                  @click="closeModal"
                  class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  Fechar
                </button>
                <button
                  v-if="student?.id"
                  @click="goToEditStudent"
                  class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  Editar Aluno
                </button>
              </div>
        </DialogPanel>
      </div>
    </div>
  </Dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'
import { 
  XMarkIcon, 
  UserIcon, 
  AcademicCapIcon, 
  CurrencyDollarIcon,
  ExclamationTriangleIcon,
  ExclamationCircleIcon,
  PlusIcon
} from '@heroicons/vue/24/outline'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import { route } from 'ziggy-js'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  enrollment: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close'])

const student = ref(null)
const enrollmentData = ref(null)
const monthlyFees = ref([])
const loading = ref(false)
const error = ref(null)

const enrollment = computed(() => enrollmentData.value || props.enrollment)

const hasMonthlyFees = computed(() => monthlyFees.value.length > 0)

const totalPending = computed(() => {
  return monthlyFees.value
    .filter(i => i.status === 'pending' && !isOverdue(i))
    .reduce((sum, i) => sum + getInstallmentAmount(i), 0)
})

const totalPaid = computed(() => {
  return monthlyFees.value
    .filter(i => i.status === 'paid')
    .reduce((sum, i) => sum + getInstallmentAmount(i), 0)
})

const totalOverdue = computed(() => {
  return monthlyFees.value
    .filter(i => i.status === 'overdue' || (i.status === 'pending' && isOverdue(i)))
    .reduce((sum, i) => sum + getInstallmentAmount(i), 0)
})

watch(() => props.show, (newVal) => {
  if (newVal && props.enrollment) {
    loadData()
  }
})

const loadData = async () => {
  if (!props.enrollment?.student?.id) return

  loading.value = true
  error.value = null

  try {
    const [studentRes, installmentsRes] = await Promise.all([
      axios.get(`/api/students/${props.enrollment.student.id}`),
      axios.get('/api/installments', {
        params: {
          student_id: props.enrollment.student.id,
          per_page: 100
        }
      })
    ])

    student.value = studentRes.data
    
    // Usar diretamente o enrollment do prop que já tem classroom carregado
    enrollmentData.value = props.enrollment
    
    // A API pode retornar paginado ou array direto
    const installmentsData = installmentsRes.data
    let allInstallments = []
    if (installmentsData?.data) {
      allInstallments = installmentsData.data
    } else if (Array.isArray(installmentsData)) {
      allInstallments = installmentsData
    }

    // Filtrar apenas mensalidades do último ano letivo
    if (allInstallments.length > 0) {
      // Extrair todos os anos letivos únicos
      const academicYears = new Set()
      allInstallments.forEach(installment => {
        if (installment.monthly_fee?.academic_year) {
          academicYears.add(installment.monthly_fee.academic_year)
        }
      })

      // Encontrar o último ano letivo (mais recente)
      const sortedYears = Array.from(academicYears).sort((a, b) => b.localeCompare(a))
      const lastAcademicYear = sortedYears[0]

      // Filtrar mensalidades do último ano letivo
      monthlyFees.value = allInstallments.filter(installment => 
        installment.monthly_fee?.academic_year === lastAcademicYear
      )
    } else {
      monthlyFees.value = []
    }
  } catch (err) {
    console.error('Erro ao carregar dados:', err)
    error.value = 'Erro ao carregar informações do aluno.'
  } finally {
    loading.value = false
  }
}

const closeModal = () => {
  emit('close')
}

const goToEditStudent = () => {
  if (student.value?.id) {
    router.get(route('students.edit', student.value.id))
  }
}

const goToGenerateMonthlyFees = () => {
  if (student.value?.id) {
    router.get(route('students.edit', student.value.id))
    // Scroll para a aba de mensalidades
    setTimeout(() => {
      const mensalidadesTab = document.querySelector('[data-tab="Mensalidades"]')
      if (mensalidadesTab) {
        mensalidadesTab.click()
      }
    }, 500)
  }
  closeModal()
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return '-'
  return date.toLocaleDateString('pt-BR')
}

const formatCPF = (cpf) => {
  if (!cpf) return '-'
  return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
}

const formatPhone = (phone) => {
  if (!phone) return '-'
  return phone.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3')
}

const formatMonth = (monthString) => {
  if (!monthString) return '-'
  const [year, month] = monthString.split('-')
  const date = new Date(year, month - 1, 1)
  return date.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
}

const formatCurrency = (value) => {
  if (value === null || value === undefined) return 'R$ 0,00'
  const amount = Number(value)
  if (Number.isNaN(amount)) return 'R$ 0,00'
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(amount)
}

const getInstallmentAmount = (installment) => {
  if (installment.status === 'paid' && installment.payments?.length > 0) {
    const payment = installment.payments.find(p => p.status === 'confirmed') || installment.payments[0]
    return Number(payment?.amount ?? 0)
  }
  // Tentar diferentes caminhos para o valor
  return Number(
    installment?.classroom_service?.service?.price ??
    installment?.classroomService?.service?.price ??
    installment?.classroom_service?.price ??
    installment?.classroomService?.price ??
    installment?.amount ??
    0
  )
}

const isOverdue = (installment) => {
  if (['paid', 'cancelled', 'waived'].includes(installment.status)) return false
  if (installment.status === 'overdue') return true
  if (installment.status === 'pending' && installment.due_date) {
    const dueDate = new Date(installment.due_date)
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    dueDate.setHours(0, 0, 0, 0)
    return dueDate < today
  }
  return false
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

const getEnrollmentStatusLabel = (status) => {
  const labels = {
    active: 'Ativa',
    inactive: 'Inativa',
    cancelled: 'Cancelada',
    suspended: 'Suspensa'
  }
  return labels[status] || status || '-'
}

const getEnrollmentStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    cancelled: 'bg-red-100 text-red-800',
    suspended: 'bg-yellow-100 text-yellow-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>
