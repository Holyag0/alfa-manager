<template>
  <!-- Modal backdrop -->
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeModal">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white" @click.stop>
      <!-- Modal header -->
      <div class="flex items-center justify-between pb-4 border-b border-gray-200">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Registrar Pagamento</h3>
          <p class="text-sm text-gray-600">Registre o pagamento dos serviços selecionados</p>
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
            <form @submit.prevent="processPayment" class="space-y-4">
              <!-- Valor do Pagamento -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Valor Pago <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="paymentForm.amount"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0,00"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  required
                />
              </div>

              <!-- Juros -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Juros (Opcional)
                </label>
                <input
                  v-model="paymentForm.interest_amount"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0,00"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <p class="text-xs text-gray-500 mt-1">Valor dos juros aplicados</p>
              </div>

              <!-- Desconto Aplicado (Valor Fixo) -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Desconto Aplicado (Valor Fixo)
                </label>
                <input
                  v-model="paymentForm.discount_amount"
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
                  v-model="paymentForm.method"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  required
                >
                  <option value="">Selecione o método</option>
                  <option value="cash">Dinheiro</option>
                  <option value="pix">PIX</option>
                  <option value="credit_card">Cartão de Crédito</option>
                  <option value="debit_card">Cartão de Débito</option>
                  <option value="bank_transfer">Transferência Bancária</option>
                  <option value="check">Cheque</option>
                </select>
              </div>

              <!-- Data do Pagamento -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Data do Pagamento <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="paymentForm.payment_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  required
                />
              </div>

              <!-- Referência -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Referência (Opcional)
                </label>
                <input
                  v-model="paymentForm.reference"
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
                  v-model="paymentForm.notes"
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
                <span class="text-lg font-semibold text-green-600">{{ getTotalAmount() }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Juros:</span>
                <span class="text-lg font-semibold text-orange-600">{{ getFormattedInterestAmount() }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700">Desconto:</span>
                <span class="text-lg font-semibold text-red-600">{{ getFormattedDiscount() }}</span>
              </div>
              <div class="border-t pt-3 flex justify-between items-center font-semibold">
                <span class="text-gray-900">Valor Final:</span>
                <span class="text-xl text-green-600">{{ getFormattedPaidAmount() }}</span>
              </div>
            </div>

            <!-- Serviços Selecionados -->
            <div class="p-4 bg-gray-50 rounded-lg max-h-96 overflow-y-auto">
              <h4 class="text-sm font-medium text-gray-900 mb-3">Serviços Selecionados</h4>
              <div class="space-y-2">
                <div
                  v-for="invoice in selectedInvoices"
                  :key="invoice.id"
                  class="border border-gray-200 rounded-lg p-3 bg-white"
                >
                  <div class="flex justify-between items-start">
                    <div class="flex-1">
                      <h5 class="font-medium text-sm text-gray-900">{{ invoice.name }}</h5>
                      <p class="text-xs text-gray-600 mt-1">{{ invoice.description }}</p>
                      <div class="flex items-center space-x-2 mt-2">
                        <span class="text-xs text-gray-500">Serviço: {{ invoice.invoice_number }}</span>
                        <span
                          :class="[
                            'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                            invoice.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                          ]"
                        >
                          {{ invoice.status_label }}
                        </span>
                      </div>
                    </div>
                    <div class="text-right ml-2">
                      <span class="font-semibold text-sm text-gray-900">{{ invoice.formatted_amount }}</span>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Informações Adicionais dos Serviços -->
              <div class="mt-4 pt-3 border-t border-gray-200">
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-600">Total de serviços:</span>
                    <span class="font-medium">{{ selectedInvoices.length }}</span>
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
            @click="processPayment"
            :disabled="processingPayment"
            class="ml-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
          >
            <svg v-if="processingPayment" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ processingPayment ? 'Processando...' : 'Registrar Pagamento' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  enrollment: {
    type: Object,
    required: true
  },
  selectedInvoices: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'payment-processed'])

// Estado do formulário
const processingPayment = ref(false)
const paymentForm = ref({
  amount: '',
  interest_amount: 0,
  discount_amount: 0,
  method: '',
  payment_date: new Date().toISOString().split('T')[0], // Data atual
  reference: '',
  notes: ''
})

// Computed properties
const getTotalAmountRaw = () => {
  return props.selectedInvoices.reduce((total, invoice) => {
    return total + parseFloat(invoice.amount || 0)
  }, 0)
}

const getTotalAmount = () => {
  const total = getTotalAmountRaw()
  return `R$ ${total.toFixed(2).replace('.', ',')}`
}

const getDiscountAmount = () => {
  return parseFloat(paymentForm.value.discount_amount) || 0
}

const getInterestAmount = () => {
  return parseFloat(paymentForm.value.interest_amount) || 0
}

const getPaidAmount = () => {
  return parseFloat(paymentForm.value.amount) || 0
}

const getFormattedPaidAmount = () => {
  const amount = getPaidAmount()
  return `R$ ${amount.toFixed(2).replace('.', ',')}`
}

const getFormattedInterestAmount = () => {
  const interest = getInterestAmount()
  return `R$ ${interest.toFixed(2).replace('.', ',')}`
}

const getFormattedDiscount = () => {
  const discount = getDiscountAmount()
  return `R$ ${discount.toFixed(2).replace('.', ',')}`
}

const getFormattedDiscountAmount = () => {
  const discount = getDiscountAmount()
  return `R$ ${discount.toFixed(2).replace('.', ',')}`
}

const getFinalAmount = () => {
  const paid = getPaidAmount()
  return `R$ ${paid.toFixed(2).replace('.', ',')}`
}

const hasDiscount = computed(() => {
  return paymentForm.value.discount_amount > 0
})

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

// Métodos
const closeModal = () => {
  emit('close')
}

const processPayment = async () => {
  if (!paymentForm.value.amount || !paymentForm.value.method) {
    alert('Por favor, preencha todos os campos obrigatórios.')
    return
  }

  processingPayment.value = true

  try {
    const response = await fetch(`/api/enrollments/${props.enrollment.id}/register-payment`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        invoice_ids: props.selectedInvoices.map(invoice => invoice.id),
        amount: parseFloat(paymentForm.value.amount),
        interest_amount: parseFloat(paymentForm.value.interest_amount) || 0,
        discount_amount: parseFloat(paymentForm.value.discount_amount) || 0,
        method: paymentForm.value.method,
        payment_date: paymentForm.value.payment_date,
        reference: paymentForm.value.reference,
        notes: paymentForm.value.notes
      })
    })

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.error || 'Erro ao processar pagamento')
    }

    const result = await response.json()
    
    alert('Pagamento registrado com sucesso!')
    
    emit('payment-processed', result)
    closeModal()
    
  } catch (error) {
    console.error('Erro ao processar pagamento:', error)
    alert(`Erro ao processar pagamento: ${error.message}`)
  } finally {
    processingPayment.value = false
  }
}

const resetForm = () => {
  const totalAmount = getTotalAmountRaw()
  paymentForm.value = {
    amount: totalAmount,
    interest_amount: 0,
    discount_amount: 0,
    method: '',
    payment_date: new Date().toISOString().split('T')[0],
    reference: '',
    notes: ''
  }
}

// Resetar formulário quando o modal abrir
watch(() => props.show, (newValue) => {
  if (newValue) {
    resetForm()
  }
})

// Recalcular valor pago quando juros ou desconto mudarem
watch(() => [paymentForm.value.interest_amount, paymentForm.value.discount_amount], () => {
  const totalAmount = getTotalAmountRaw()
  const interest = parseFloat(paymentForm.value.interest_amount) || 0
  const discount = parseFloat(paymentForm.value.discount_amount) || 0
  
  // Valor pago = valor original + juros - desconto
  const newAmount = totalAmount + interest - discount
  paymentForm.value.amount = newAmount
}, { deep: true })
</script>