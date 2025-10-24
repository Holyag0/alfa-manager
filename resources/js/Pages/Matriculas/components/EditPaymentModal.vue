<template>
  <!-- Modal backdrop -->
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeModal">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white" @click.stop>
      <!-- Modal header -->
      <div class="flex items-center justify-between pb-4 border-b border-gray-200">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Editar Pagamento</h3>
          <p class="text-sm text-gray-600">Modifique os dados do pagamento</p>
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
        <form @submit.prevent="updatePayment">
          <div class="space-y-6">
            <!-- Informações do Pagamento -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Valor Pago -->
              <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                  Valor Pago <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">R$</span>
                  </div>
                  <input
                    id="amount"
                    v-model="editForm.amount"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    class="block w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="0,00"
                  />
                </div>
              </div>

              <!-- Método de Pagamento -->
              <div>
                <label for="method" class="block text-sm font-medium text-gray-700 mb-1">
                  Método de Pagamento <span class="text-red-500">*</span>
                </label>
                <select
                  id="method"
                  v-model="editForm.method"
                  required
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                >
                  <option value="">Selecione o método</option>
                  <option value="cash">Dinheiro</option>
                  <option value="credit_card">Cartão de Crédito</option>
                  <option value="debit_card">Cartão de Débito</option>
                  <option value="pix">PIX</option>
                  <option value="bank_transfer">Transferência Bancária</option>
                  <option value="check">Cheque</option>
                  <option value="other">Outro</option>
                </select>
              </div>

              <!-- Data do Pagamento -->
              <div>
                <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-1">
                  Data do Pagamento <span class="text-red-500">*</span>
                </label>
                <input
                  id="payment_date"
                  v-model="editForm.payment_date"
                  type="date"
                  required
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                />
              </div>

              <!-- Referência -->
              <div>
                <label for="reference" class="block text-sm font-medium text-gray-700 mb-1">
                  Referência
                </label>
                <input
                  id="reference"
                  v-model="editForm.reference"
                  type="text"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  placeholder="Ex: Comprovante, NSU, etc."
                />
              </div>
            </div>

            <!-- Valores Adicionais -->
            <div class="border-t border-gray-200 pt-6">
              <h4 class="text-md font-medium text-gray-900 mb-4">Valores Adicionais</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Juros -->
                <div>
                  <label for="interest_amount" class="block text-sm font-medium text-gray-700 mb-1">
                    Juros
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <span class="text-gray-500 sm:text-sm">R$</span>
                    </div>
                    <input
                      id="interest_amount"
                      v-model="editForm.interest_amount"
                      type="number"
                      step="0.01"
                      min="0"
                      class="block w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      placeholder="0,00"
                    />
                  </div>
                </div>

                <!-- Desconto -->
                <div>
                  <label for="discount_amount" class="block text-sm font-medium text-gray-700 mb-1">
                    Desconto
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <span class="text-gray-500 sm:text-sm">R$</span>
                    </div>
                    <input
                      id="discount_amount"
                      v-model="editForm.discount_amount"
                      type="number"
                      step="0.01"
                      min="0"
                      class="block w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                      placeholder="0,00"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Observações -->
            <div>
              <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                Observações
              </label>
              <textarea
                id="notes"
                v-model="editForm.notes"
                rows="3"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                placeholder="Observações adicionais sobre o pagamento..."
              ></textarea>
            </div>

            <!-- Resumo dos Valores -->
            <div class="bg-blue-50 rounded-lg p-4">
              <h4 class="text-sm font-medium text-gray-900 mb-3">Resumo dos Valores</h4>
              <div class="space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Valor Pago:</span>
                  <span class="font-medium">{{ getFormattedAmount(editForm.amount) }}</span>
                </div>
                <div v-if="editForm.interest_amount > 0" class="flex justify-between text-sm">
                  <span class="text-gray-600">Juros:</span>
                  <span class="font-medium text-orange-600">{{ getFormattedAmount(editForm.interest_amount) }}</span>
                </div>
                <div v-if="editForm.discount_amount > 0" class="flex justify-between text-sm">
                  <span class="text-gray-600">Desconto:</span>
                  <span class="font-medium text-red-600">-{{ getFormattedAmount(editForm.discount_amount) }}</span>
                </div>
                <div class="border-t border-gray-200 pt-2 flex justify-between text-sm font-semibold">
                  <span class="text-gray-900">Total:</span>
                  <span class="text-blue-600">{{ getFormattedTotal() }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal footer -->
          <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
            <button
              type="button"
              @click="closeModal"
              class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
              Cancelar
            </button>
            <button
              type="button"
              @click="confirmUpdate"
              :disabled="processing"
              class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
            >
              <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ processing ? 'Salvando...' : 'Salvar Alterações' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Flash Messages -->
  <div v-if="flashMessage.message" class="fixed top-4 right-4 z-50">
    <div :class="[
      'px-4 py-3 rounded-md shadow-lg max-w-sm',
      flashMessage.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    ]">
      <div class="flex items-center">
        <svg v-if="flashMessage.type === 'success'" class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <svg v-else class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span class="text-sm font-medium">{{ flashMessage.message }}</span>
      </div>
    </div>
  </div>

  <!-- Modal de Confirmação -->
  <ConfirmationModal :show="showConfirmation" @close="showConfirmation = false">
    <template #title>
      Confirmar Alterações
    </template>
    <template #content>
      Tem certeza que deseja salvar as alterações no pagamento? Esta ação não pode ser desfeita.
    </template>
    <template #footer>
      <button
        type="button"
        @click="showConfirmation = false"
        class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
      >
        Cancelar
      </button>
      <button
        type="button"
        @click="updatePayment"
        class="ml-3 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
      >
        Confirmar
      </button>
    </template>
  </ConfirmationModal>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'
import FlashMessenger from '@/Shared/FlashMessenger.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  payment: {
    type: Object,
    default: null
  },
  enrollment: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'payment-updated'])

const processing = ref(false)
const showConfirmation = ref(false)
const flashMessage = ref({ type: '', message: '' })

// Formulário de edição
const editForm = ref({
  amount: '',
  interest_amount: 0,
  discount_amount: 0,
  method: '',
  payment_date: '',
  reference: '',
  notes: ''
})

// Computed para calcular o total
const getFormattedTotal = () => {
  const amount = parseFloat(editForm.value.amount) || 0
  const interest = parseFloat(editForm.value.interest_amount) || 0
  const discount = parseFloat(editForm.value.discount_amount) || 0
  const total = amount + interest - discount
  return `R$ ${total.toFixed(2).replace('.', ',')}`
}

// Função para formatar valores
const getFormattedAmount = (value) => {
  const numValue = parseFloat(value) || 0
  return `R$ ${numValue.toFixed(2).replace('.', ',')}`
}

// Função para fechar modal
const closeModal = () => {
  emit('close')
}

// Função para validar formulário
const validateForm = () => {
  if (!editForm.value.amount || !editForm.value.method) {
    showFlashMessage('error', 'Por favor, preencha todos os campos obrigatórios.')
    return false
  }
  return true
}

// Função para mostrar mensagem flash
const showFlashMessage = (type, message) => {
  flashMessage.value = { type, message }
  setTimeout(() => {
    flashMessage.value = { type: '', message: '' }
  }, 5000)
}

// Função para confirmar atualização
const confirmUpdate = () => {
  if (!validateForm()) return
  
  showConfirmation.value = true
}

// Função para atualizar pagamento
const updatePayment = async () => {
  processing.value = true
  showConfirmation.value = false

  try {
    const response = await fetch(`/api/enrollments/${props.enrollment.id}/payments/${props.payment.id}/update`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        amount: parseFloat(editForm.value.amount),
        interest_amount: parseFloat(editForm.value.interest_amount) || 0,
        discount_amount: parseFloat(editForm.value.discount_amount) || 0,
        method: editForm.value.method,
        payment_date: editForm.value.payment_date,
        reference: editForm.value.reference,
        notes: editForm.value.notes
      })
    })

    if (!response.ok) {
      let errorMessage = 'Erro ao atualizar pagamento'
      try {
        const errorData = await response.json()
        errorMessage = errorData.error || errorData.message || errorMessage
      } catch (e) {
        // Se não conseguir fazer parse do JSON, usar mensagem padrão
        console.error('Erro ao fazer parse da resposta:', e)
      }
      throw new Error(errorMessage)
    }

    const result = await response.json()
    
    showFlashMessage('success', 'Pagamento atualizado com sucesso!')
    
    emit('payment-updated', result)
    closeModal()
    
  } catch (error) {
    console.error('Erro ao atualizar pagamento:', error)
    showFlashMessage('error', `Erro ao atualizar pagamento: ${error.message}`)
  } finally {
    processing.value = false
  }
}

// Função para inicializar formulário
const initializeForm = () => {
  if (props.payment) {
    editForm.value = {
      amount: props.payment.amount || props.payment.original_amount || '',
      interest_amount: props.payment.interest_amount || 0,
      discount_amount: props.payment.discount_amount || 0,
      method: props.payment.method || '',
      payment_date: props.payment.payment_date ? props.payment.payment_date.split('T')[0] : '',
      reference: props.payment.reference || '',
      notes: props.payment.notes || ''
    }
  }
}

// Watcher para inicializar formulário quando o modal abrir
watch(() => props.show, (newValue) => {
  if (newValue && props.payment) {
    initializeForm()
  }
})

// Watcher para inicializar quando o pagamento mudar
watch(() => props.payment, (newPayment) => {
  if (newPayment && props.show) {
    initializeForm()
  }
})
</script>
