<template>
  <div class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div 
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
        aria-hidden="true"
        @click="close"
      ></div>

      <!-- Center modal -->
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
        <div>
          <!-- Ícone -->
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100">
            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>

          <!-- Título -->
          <div class="mt-3 text-center sm:mt-5">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
              Gerar Mensalidades
            </h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500">
                Configure as mensalidades para <span class="font-semibold">{{ student.name }}</span>
              </p>
              <p class="text-xs text-gray-400 mt-1">
                Turma: {{ enrollment?.classroom?.name || 'N/A' }}
              </p>
            </div>
          </div>

          <!-- Formulário -->
          <form @submit.prevent="handleSubmit" class="mt-6 space-y-4">
            <!-- Ano Letivo -->
            <div>
              <label for="academic_year" class="block text-sm font-medium text-gray-700">
                Ano Letivo *
              </label>
              <select
                id="academic_year"
                v-model="form.academic_year"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
                <option v-for="year in availableYears" :key="year" :value="year">
                  {{ year }}
                </option>
              </select>
              <p class="mt-1 text-xs text-gray-500">
                Selecione o ano letivo para o qual as mensalidades serão geradas
              </p>
            </div>

            <!-- Período das Mensalidades -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Período das Mensalidades
              </label>
              
              <!-- Botão de Ano Completo -->
              <button
                type="button"
                @click="setFullYear"
                class="w-full mb-3 px-4 py-2 border border-indigo-300 rounded-md text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              >
                <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Ano Completo (Janeiro a Dezembro)
              </button>

              <div class="grid grid-cols-2 gap-4">
                <!-- Mês de Início -->
                <div>
                  <label for="start_month" class="block text-xs font-medium text-gray-600 mb-1">
                    Mês de Início
                  </label>
                  <select
                    id="start_month"
                    v-model="form.start_month"
                    @change="validateMonthRange"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  >
                    <option v-for="month in months" :key="month.value" :value="month.value">
                      {{ month.label }}
                    </option>
                  </select>
                </div>

                <!-- Mês de Término -->
                <div>
                  <label for="end_month" class="block text-xs font-medium text-gray-600 mb-1">
                    Mês de Término
                  </label>
                  <select
                    id="end_month"
                    v-model="form.end_month"
                    @change="validateMonthRange"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  >
                    <option v-for="month in months" :key="month.value" :value="month.value">
                      {{ month.label }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Informação de Quantidade de Parcelas -->
              <div class="mt-3 p-3 bg-blue-50 rounded-md border border-blue-200">
                <div class="flex items-center">
                  <svg class="h-5 w-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                  </svg>
                  <div class="text-sm">
                    <span class="font-medium text-blue-900">{{ totalInstallments }} mensalidade{{ totalInstallments !== 1 ? 's' : '' }}</span>
                    <span class="text-blue-700"> serão geradas</span>
                  </div>
                </div>
                <p class="mt-1 text-xs text-blue-600 ml-7">
                  {{ getPeriodDescription() }}
                </p>
              </div>

              <!-- Alerta se período inválido -->
              <div v-if="form.end_month < form.start_month" class="mt-2 p-2 bg-red-50 rounded-md border border-red-200">
                <div class="flex items-center">
                  <svg class="h-4 w-4 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                  </svg>
                  <span class="text-xs text-red-700">O mês de término deve ser posterior ao mês de início</span>
                </div>
              </div>
            </div>

            <!-- Dia de Vencimento -->
            <div>
              <label for="due_day" class="block text-sm font-medium text-gray-700">
                Dia de Vencimento
              </label>
              <select
                id="due_day"
                v-model="form.due_day"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
                <option v-for="day in 31" :key="day" :value="day">
                  Dia {{ day }}
                </option>
              </select>
              <p class="mt-1 text-xs text-gray-500">
                As mensalidades vencerão todo dia {{ form.due_day }} de cada mês
              </p>
            </div>

            <!-- Desconto de Pontualidade -->
            <div class="flex items-start">
              <div class="flex items-center h-5">
                <input
                  id="has_punctuality_discount"
                  v-model="form.has_punctuality_discount"
                  type="checkbox"
                  class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                />
              </div>
              <div class="ml-3 text-sm">
                <label for="has_punctuality_discount" class="font-medium text-gray-700">
                  Aplicar desconto de pontualidade
                </label>
                <p class="text-gray-500">Se habilitado, o aluno receberá desconto ao pagar em dia</p>
              </div>
            </div>

            <!-- Valor do Desconto de Pontualidade -->
            <div v-if="form.has_punctuality_discount">
              <label for="punctuality_discount_amount" class="block text-sm font-medium text-gray-700">
                Valor do Desconto (R$)
              </label>
              <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-500 sm:text-sm">R$</span>
                </div>
                <input
                  type="number"
                  id="punctuality_discount_amount"
                  v-model="form.punctuality_discount_amount"
                  step="0.01"
                  min="0"
                  class="block w-full pl-10 pr-12 rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  placeholder="0,00"
                />
              </div>
            </div>

            <!-- Observações -->
            <div>
              <label for="notes" class="block text-sm font-medium text-gray-700">
                Observações (opcional)
              </label>
              <textarea
                id="notes"
                v-model="form.notes"
                rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="Adicione observações sobre este contrato de mensalidades..."
              ></textarea>
            </div>

            <!-- Mensagem de erro -->
            <div v-if="error" class="rounded-md bg-red-50 p-4">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                  </svg>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-red-800">{{ error }}</p>
                </div>
              </div>
            </div>

            <!-- Resumo -->
            <div class="rounded-md bg-indigo-50 p-4">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                  </svg>
                </div>
                <div class="ml-3 flex-1">
                  <p class="text-sm text-indigo-700 font-medium">
                    Resumo da Geração
                  </p>
                  <p class="mt-2 text-sm text-indigo-700">
                    Serão geradas <span class="font-bold">{{ totalInstallments }} mensalidade{{ totalInstallments !== 1 ? 's' : '' }}</span> 
                    ({{ months[form.start_month - 1].label }} a {{ months[form.end_month - 1].label }})
                    com vencimento todo dia <span class="font-bold">{{ form.due_day }}</span> de cada mês{{ form.has_punctuality_discount ? ` com desconto de R$ ${formatCurrency(form.punctuality_discount_amount)} para pagamentos pontuais` : '' }}.
                  </p>
                </div>
              </div>
            </div>

            <!-- Botões -->
            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
              <button
                type="submit"
                :disabled="submitting"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg v-if="submitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ submitting ? 'Gerando...' : 'Gerar Mensalidades' }}
              </button>
              <button
                type="button"
                @click="close"
                :disabled="submitting"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Cancelar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  student: {
    type: Object,
    required: true
  },
  enrollment: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'success'])

const submitting = ref(false)
const error = ref(null)

const months = [
  { value: 1, label: 'Janeiro', short: 'Jan' },
  { value: 2, label: 'Fevereiro', short: 'Fev' },
  { value: 3, label: 'Março', short: 'Mar' },
  { value: 4, label: 'Abril', short: 'Abr' },
  { value: 5, label: 'Maio', short: 'Mai' },
  { value: 6, label: 'Junho', short: 'Jun' },
  { value: 7, label: 'Julho', short: 'Jul' },
  { value: 8, label: 'Agosto', short: 'Ago' },
  { value: 9, label: 'Setembro', short: 'Set' },
  { value: 10, label: 'Outubro', short: 'Out' },
  { value: 11, label: 'Novembro', short: 'Nov' },
  { value: 12, label: 'Dezembro', short: 'Dez' }
]

// Gerar lista de anos disponíveis (ano atual e próximos 5 anos)
const currentYear = new Date().getFullYear()
const availableYears = computed(() => {
  const years = []
  for (let i = 0; i <= 5; i++) {
    years.push((currentYear + i).toString())
  }
  return years
})

// Valor padrão do ano letivo: usar o ano atual se não houver academic_year na enrollment
// Se a enrollment tiver academic_year como 2024, mas estamos em 2025, usar 2025 como padrão
const getDefaultAcademicYear = () => {
  const enrollmentYear = props.enrollment.academic_year
  // Se não houver academic_year na enrollment, usar ano atual
  if (!enrollmentYear) {
    return currentYear.toString()
  }
  // Se o academic_year da enrollment for menor que o ano atual, usar ano atual
  if (parseInt(enrollmentYear) < currentYear) {
    return currentYear.toString()
  }
  // Caso contrário, usar o academic_year da enrollment
  return enrollmentYear
}

const form = reactive({
  enrollment_id: props.enrollment.id,
  academic_year: getDefaultAcademicYear(),
  start_month: 1,
  end_month: 12,
  due_day: 10,
  has_punctuality_discount: true,
  punctuality_discount_amount: 0,
  notes: ''
})

// Calcula automaticamente a quantidade de mensalidades
const totalInstallments = computed(() => {
  if (form.end_month < form.start_month) return 0
  return form.end_month - form.start_month + 1
})

// Define ano completo rapidamente
const setFullYear = () => {
  form.start_month = 1
  form.end_month = 12
}

// Valida o range de meses
const validateMonthRange = () => {
  if (form.end_month < form.start_month) {
    error.value = 'O mês de término deve ser posterior ao mês de início'
  } else {
    error.value = null
  }
}

// Descrição do período
const getPeriodDescription = () => {
  if (form.end_month < form.start_month) {
    return 'Período inválido'
  }
  
  const startMonth = months[form.start_month - 1]
  const endMonth = months[form.end_month - 1]
  
  if (form.start_month === 1 && form.end_month === 12) {
    return 'Ano letivo completo'
  }
  
  return `De ${startMonth.label} a ${endMonth.label} de ${form.academic_year}`
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value || 0)
}

const handleSubmit = () => {
  // Validar antes de enviar
  if (form.end_month < form.start_month) {
    error.value = 'O mês de término deve ser posterior ao mês de início'
    return
  }

  submitting.value = true
  error.value = null

  // Preparar dados com total_installments calculado
  const payload = {
    ...form,
    total_installments: totalInstallments.value
  }

  // Usar Inertia router para manter cookies e sessão
  // O controller detectará que é uma requisição Inertia e retornará redirect apropriado
  router.post('/api/monthly-fees', payload, {
    preserveScroll: true,
    only: [], // Não recarregar props automaticamente
    onSuccess: (page) => {
      // Mostrar mensagem de sucesso
      const startMonth = months[form.start_month - 1].label
      const endMonth = months[form.end_month - 1].label
      alert(`✅ Mensalidades geradas com sucesso!\n\n${totalInstallments.value} mensalidade${totalInstallments.value !== 1 ? 's' : ''} ${totalInstallments.value !== 1 ? 'foram criadas' : 'foi criada'} para ${props.student.name}.\n\nPeríodo: ${startMonth} a ${endMonth} de ${form.academic_year}`)
      
      submitting.value = false
      emit('success')
    },
    onError: (errors) => {
      console.error('Erro ao gerar mensalidades:', errors)
      
      // Tratar diferentes formatos de erro
      if (typeof errors === 'string') {
        error.value = errors
      } else if (errors?.message) {
        error.value = errors.message
      } else if (errors?.error) {
        error.value = errors.error
      } else if (Array.isArray(errors) && errors.length > 0) {
        error.value = errors[0]
      } else {
        error.value = 'Erro ao gerar mensalidades. Tente novamente.'
      }
      
      submitting.value = false
    },
    onFinish: () => {
      submitting.value = false
    }
  })
}

const close = () => {
  if (!submitting.value) {
    emit('close')
  }
}
</script>

