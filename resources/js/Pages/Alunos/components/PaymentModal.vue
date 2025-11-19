<template>
  <!-- Modal backdrop -->
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[60]" @click="closeModal">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white" @click.stop>
      <!-- Modal header -->
      <div class="flex items-center justify-between pb-4 border-b border-gray-200">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Registrar Pagamento</h3>
          <p class="text-sm text-gray-600">Registre o pagamento da Mensalidade</p>
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
      <div class="mt-6">
        <!-- Layout de 2 Colunas -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Coluna Esquerda - Informações do Pagamento -->
          <div class="space-y-6">
            <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Informações do Pagamento</h3>
            
            <!-- Formulário de Pagamento -->
            <form @submit.prevent="submitPayment" class="space-y-4">
              <!-- Valor do Pagamento -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Valor Pago <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.amount"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0,00"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-300': errors.amount }"
                  required
                />
                <p v-if="errors.amount" class="mt-1 text-sm text-red-600">{{ errors.amount }}</p>
              </div>

              <!-- Juros -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Juros (Opcional)
                </label>
                <input
                  v-model.number="form.interest_amount"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0,00"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <p class="text-xs text-gray-500 mt-1">
                  Valor calculado: {{ getFormattedCalculatedInterest() }}
                  <span v-if="calculatedInterest > 0" class="text-blue-600">(ajustável)</span>
                </p>
              </div>

              <!-- Multa -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Multa (Opcional)
                </label>
                <input
                  v-model.number="form.fine_amount"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0,00"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <p class="text-xs text-gray-500 mt-1">
                  Valor calculado: {{ getFormattedCalculatedFine() }}
                  <span v-if="calculatedFine > 0" class="text-blue-600">(ajustável)</span>
                </p>
          </div>

              <!-- Desconto Aplicado (Valor Fixo) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Desconto Aplicado (Valor Fixo)
              </label>
                <input
                  v-model="form.other_discounts"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0,00"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <p class="text-xs text-gray-500 mt-1">Valor do desconto: {{ getFormattedDiscountAmount() }}</p>
            </div>

            <!-- Método de Pagamento -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Método de Pagamento <span class="text-red-500">*</span>
              </label>
              <select 
                v-model="form.method"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-300': errors.method }"
                required
              >
                <option value="">Selecione o método</option>
                  <option value="cash">Dinheiro</option>
                <option value="pix">PIX</option>
                <option value="credit_card">Cartão de Crédito</option>
                <option value="debit_card">Cartão de Débito</option>
                <option value="bank_transfer">Transferência Bancária</option>
                <option value="check">Cheque</option>
                  <option value="other">Outro</option>
              </select>
              <p v-if="errors.method" class="mt-1 text-sm text-red-600">{{ errors.method }}</p>
            </div>

              <!-- Responsável pelo Pagamento -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Responsável pelo Pagamento <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.paid_by_guardian_id"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-300': errors.paid_by_guardian_id }"
                  required
                  :disabled="loadingGuardians"
                >
                  <option value="">
                    {{ loadingGuardians ? 'Carregando responsáveis...' : 'Selecione o responsável' }}
                  </option>
                  <option 
                    v-for="guardian in guardians" 
                    :key="guardian.id" 
                    :value="guardian.id"
                  >
                    {{ guardian.name }}{{ guardian.relationship ? ` (${guardian.relationship})` : '' }}
                  </option>
                </select>
                <p class="text-xs text-gray-500 mt-1">
                  Selecione quem está efetuando o pagamento
                </p>
                <p v-if="errors.paid_by_guardian_id" class="mt-1 text-sm text-red-600">{{ errors.paid_by_guardian_id }}</p>
            </div>

            <!-- Data do Pagamento -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Data do Pagamento <span class="text-red-500">*</span>
              </label>
              <input 
                v-model="form.payment_date"
                type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-300': errors.payment_date }"
                required
              />
              <p v-if="errors.payment_date" class="mt-1 text-sm text-red-600">{{ errors.payment_date }}</p>
            </div>

            <!-- Referência -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Referência (Opcional)
              </label>
              <input 
                v-model="form.reference"
                type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Ex: Transação PIX, comprovante..."
              />
            </div>

            <!-- Observações -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Observações (Opcional)
              </label>
              <textarea 
                v-model="form.notes"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Observações adicionais..."
                />
              </div>
            </form>
          </div>

          <!-- Coluna Direita - Resumo e Detalhes -->
          <div class="space-y-6">
            <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">Resumo do Pagamento</h3>
            
            <!-- Resumo de Valores -->
            <div class="space-y-4 p-4 bg-blue-50 rounded-lg">
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Valor Serviço:</span>
                <span class="text-lg font-semibold text-black">{{ formatCurrency(currentServicePrice) }}</span>
              </div>
              
              <!-- Feedback visual de desconto de irmão -->
              <div v-if="hasSiblingDiscount" class="flex items-center space-x-2 p-2 bg-green-50 rounded-md border border-green-200">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-xs text-green-700 font-medium">Desconto de irmão já aplicado no valor do serviço</span>
              </div>
              
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Desconto:</span>
                <span class="text-lg font-semibold text-green-600">- {{ getFormattedDiscount() }}</span>
              </div>
              <div v-if="getInterestAmount() !== 0" class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Juros:</span>
                <span class="text-lg font-semibold text-red-600">+ {{ getFormattedInterest() }}</span>
              </div>
              <div v-if="getFineAmount() !== 0" class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Multa:</span>
                <span class="text-lg font-semibold text-orange-600">+ {{ getFormattedFine() }}</span>
              </div>
              <div class="border-t pt-3 flex justify-between items-center font-semibold">
                <span class="text-gray-900">Valor Final:</span>
                <span class="text-xl text-black">{{ getFormattedPaidAmount() }}</span>
              </div>
            </div>

            <!-- Serviços Selecionados -->
            <div class="p-4 bg-gray-50 rounded-lg max-h-96 overflow-y-auto">
              <h4 class="text-sm font-medium text-gray-900 mb-3">Serviços Selecionados</h4>
              <div class="space-y-2">
                <div
                  class="border border-gray-200 rounded-lg p-3 bg-white"
                >
                  <div class="flex justify-between items-start">
                    <div class="flex-1">
                      <h5 class="font-medium text-sm text-gray-900">
                        {{ getServiceName() }}
                      </h5>
                      <p class="text-xs text-gray-600 mt-1">
                        {{ getServiceDescription() }}
                      </p>
                      <div class="flex items-center space-x-2 mt-2">
                        <span class="text-xs text-gray-500">Mês: {{ props.installment ? formatMonth(props.installment.reference_month) : '' }}</span>
                        <span
                          class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                        >
                          Pendente
                        </span>
                      </div>
                    </div>
                    <div class="text-right ml-2">
                      <span class="font-semibold text-sm text-gray-900">{{ formatCurrency(finalAmount) }}</span>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Informações Adicionais dos Serviços -->
              <div class="mt-4 pt-3 border-t border-gray-200">
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-600">Total de serviços:</span>
                    <span class="font-medium">1</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span class="font-medium text-blue-600">Pagamento em processamento</span>
                  </div>
                </div>
              </div>
            </div>
            </div>
          </div>

        <!-- Botões de Ação -->
        <div class="flex items-center justify-end mt-6 pt-6 border-t border-gray-200">
            <button 
            @click="closeModal"
            class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
              Cancelar
            </button>
            <button 
            @click="submitPayment"
              :disabled="processing"
            class="ml-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
            >
            <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ processing ? 'Processando...' : 'Registrar Pagamento' }}
            </button>
          </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, nextTick } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  installment: {
    type: Object,
    required: true
  },
  student: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'success'])

const processing = ref(false)
const errors = ref({})
const guardians = ref([])
const loadingGuardians = ref(false)

// Calcular valores dinamicamente do serviço atual
const currentServicePrice = computed(() => {
  return Number(
    props.installment?.classroom_service?.service?.price ??
    props.installment?.classroom_service?.price ??
    0
  )
})

// Verificar se tem desconto de irmão aplicado (apenas para feedback visual)
const hasSiblingDiscount = computed(() => {
  return Number(
    props.installment?.monthly_fee?.sibling_discount_amount ??
    0
  ) > 0
})

// Calcular juros baseado na data de pagamento vs vencimento
const calculatedInterest = computed(() => {
  if (!props.installment?.due_date) return 0
  
  const dueDate = new Date(props.installment.due_date)
  const paymentDate = new Date(form.payment_date)
  
  // Se pagamento antes ou na data de vencimento, sem juros
  if (paymentDate <= dueDate) return 0
  
  // Calcular dias de atraso
  const daysLate = Math.ceil((paymentDate - dueDate) / (1000 * 60 * 60 * 24))
  
  // Juros: 1% ao mês (proporcional aos dias)
  const monthlyInterestRate = 0.01 // 1%
  const baseAmount = currentServicePrice.value
  const interest = (baseAmount * monthlyInterestRate * daysLate) / 30
  
  return Math.round(interest * 100) / 100
})

// Calcular multa baseado na data de pagamento vs vencimento
const calculatedFine = computed(() => {
  if (!props.installment?.due_date) return 0
  
  const dueDate = new Date(props.installment.due_date)
  const paymentDate = new Date(form.payment_date)
  
  // Se pagamento antes ou na data de vencimento, sem multa
  if (paymentDate <= dueDate) return 0
  
  // Multa: 2% sobre o valor base (cobrada uma única vez)
  const fineRate = 0.02 // 2%
  const baseAmount = currentServicePrice.value
  const fine = baseAmount * fineRate
  
  return Math.round(fine * 100) / 100
})

// Funções auxiliares para obter valores de juros e multa
const getInterestAmount = () => {
  // Usar valor do form se preenchido, senão usar o calculado
  if (form.interest_amount !== null && form.interest_amount !== undefined && form.interest_amount !== '') {
    return parseFloat(form.interest_amount) || 0
  }
  return calculatedInterest.value
}

const getFineAmount = () => {
  // Usar valor do form se preenchido, senão usar o calculado
  if (form.fine_amount !== null && form.fine_amount !== undefined && form.fine_amount !== '') {
    return parseFloat(form.fine_amount) || 0
  }
  return calculatedFine.value
}

const finalAmount = computed(() => {
  // O valor base já vem com desconto de irmão aplicado (do serviço)
  const baseAmount = currentServicePrice.value
  const discount = parseFloat(form.other_discounts) || 0
  // Usar as funções auxiliares que já fazem a lógica correta
  const interest = getInterestAmount()
  const fine = getFineAmount()
  
  return Math.max(0, baseAmount - discount + interest + fine)
})

const form = reactive({
  paid_by_guardian_id: '',
  method: '',
  payment_date: new Date().toISOString().split('T')[0],
  amount: 0, // Será atualizado no watch
  interest_amount: 0, // Juros (calculado automaticamente, mas ajustável)
  fine_amount: 0, // Multa (calculada automaticamente, mas ajustável)
  other_discounts: 0, // Descontos adicionais (campo esperado pelo backend)
  reference: '',
  notes: '',
  auto_confirm: true
})

// Função para atualizar o valor final
const updateFinalAmount = () => {
  // Usar o computed finalAmount que já tem toda a lógica correta
  const calculatedAmount = finalAmount.value
  
  // Só atualizar se o valor atual estiver muito próximo do valor calculado anterior
  // ou se for a primeira vez (valor 0)
  const currentAmount = parseFloat(form.amount) || 0
  if (currentAmount === 0 || Math.abs(currentAmount - calculatedAmount) < 0.01) {
    form.amount = calculatedAmount
  }
}

// Atualizar valores calculados de juros e multa quando a data de pagamento mudar
watch(() => form.payment_date, () => {
  form.interest_amount = calculatedInterest.value
  form.fine_amount = calculatedFine.value
  updateFinalAmount()
})

// Recalcular valor pago quando desconto, juros ou multa mudarem
watch(() => [form.other_discounts, form.interest_amount, form.fine_amount, currentServicePrice], () => {
  updateFinalAmount()
}, { immediate: true })

const loadGuardians = async () => {
  loadingGuardians.value = true
  try {
    const response = await axios.get(`/api/students/${props.student.id}/guardians`)
    guardians.value = response.data
  } catch (error) {
    // Silenciar erro - será tratado pela UI se necessário
  } finally {
    loadingGuardians.value = false
  }
}

const closeModal = () => {
  emit('close')
}

const submitPayment = async () => {
  processing.value = true
  errors.value = {}

  // Validação do responsável pelo pagamento
  if (!form.paid_by_guardian_id || form.paid_by_guardian_id === '') {
    errors.value.paid_by_guardian_id = 'Selecione o responsável pelo pagamento'
    processing.value = false
    return
  }

  // Validação do método de pagamento
  if (!form.method || form.method === '') {
    errors.value.method = 'Selecione o método de pagamento'
    processing.value = false
    return
  }

  try {
    // Preparar dados conforme esperado pelo backend
    const paidByGuardianId = parseInt(form.paid_by_guardian_id)
    if (isNaN(paidByGuardianId) || paidByGuardianId <= 0) {
      errors.value.paid_by_guardian_id = 'Responsável pelo pagamento inválido'
      processing.value = false
      return
    }

    const paymentData = {
      paid_by_guardian_id: paidByGuardianId,
      method: form.method,
      payment_date: form.payment_date,
      amount: parseFloat(form.amount) || 0, // Valor final já inclui juros/multa ajustados
      other_discounts: parseFloat(form.other_discounts) || 0,
      reference: form.reference || null,
      notes: form.notes || null,
      auto_confirm: form.auto_confirm !== false, // Padrão true
      // Enviar juros e multa ajustados se diferentes do calculado automaticamente
      // Verificar diferença considerando valores numéricos (tolerância de 0.01)
      interest_override: Math.abs(parseFloat(form.interest_amount || 0) - calculatedInterest.value) > 0.01 
        ? parseFloat(form.interest_amount || 0) 
        : null,
      fine_override: Math.abs(parseFloat(form.fine_amount || 0) - calculatedFine.value) > 0.01 
        ? parseFloat(form.fine_amount || 0) 
        : null
    }
    
    await axios.post(`/api/installments/${props.installment.id}/pay`, paymentData)
    
    emit('success')
    alert('Pagamento registrado com sucesso!')
    closeModal()
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      alert('Erro ao registrar pagamento: ' + (error.response?.data?.message || error.message))
    }
  } finally {
    processing.value = false
  }
}

// Métodos de formatação
const getFormattedDiscount = () => {
  const discount = parseFloat(form.other_discounts) || 0
  return formatCurrency(discount)
}

const getFormattedInterest = () => {
  const interest = getInterestAmount()
  return formatCurrency(Math.abs(interest))
}

const getFormattedFine = () => {
  const fine = getFineAmount()
  return formatCurrency(Math.abs(fine))
}

const getFormattedCalculatedInterest = () => {
  return formatCurrency(calculatedInterest.value)
}

const getFormattedCalculatedFine = () => {
  return formatCurrency(calculatedFine.value)
}

const getFormattedPaidAmount = () => {
  // Usar o computed finalAmount para garantir consistência
  const amount = finalAmount.value
  return formatCurrency(amount)
}

const getFormattedDiscountAmount = () => {
  const discount = parseFloat(form.other_discounts) || 0
  return formatCurrency(discount)
}

// Métodos para obter informações do serviço
const getServiceName = () => {
  if (!props.installment) return 'Mensalidade'
  const service = props.installment?.classroom_service?.service
  if (service?.name) {
    return service.name
  }
  return `Mensalidade - ${formatMonth(props.installment.reference_month)}`
}

const getServiceDescription = () => {
  if (!props.installment) return 'Serviço adicional adicionado à matrícula'
  const service = props.installment?.classroom_service?.service
  if (service?.description) {
    return service.description
  }
  return `Serviço de Mensalidade Escolar`
}

const formatMonth = (monthString) => {
  if (!monthString) return ''
  const [year, month] = monthString.split('-')
  const date = new Date(year, month - 1, 1)
  return date.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })
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

// Resetar formulário quando o modal abrir
watch(() => props.show, (newValue) => {
  if (newValue) {
    // Resetar campos básicos primeiro
    form.payment_date = new Date().toISOString().split('T')[0]
    form.other_discounts = 0
    form.reference = ''
    form.notes = ''
    form.method = ''
    form.paid_by_guardian_id = ''
    form.amount = 0 // Resetar valor inicial
    errors.value = {}
    
    // Usar nextTick para garantir que os computed estejam atualizados após resetar payment_date
    nextTick(() => {
      // Calcular juros e multa iniciais baseado na data atual
      form.interest_amount = calculatedInterest.value
      form.fine_amount = calculatedFine.value
      
      // Atualizar valor final após definir juros e multa
      updateFinalAmount()
    })
    
    loadGuardians()
  }
})

onMounted(() => {
  if (props.show) {
  loadGuardians()
  }
})
</script>



