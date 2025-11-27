<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[60]" @click="closeModal">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-3xl shadow-lg rounded-md bg-white" @click.stop>
      <!-- Modal header -->
      <div class="flex items-center justify-between pb-4 border-b border-gray-200">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Editar Pagamento</h3>
          <p class="text-sm text-gray-600">Edite as informações do pagamento registrado</p>
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
        <form @submit.prevent="submitEdit" class="space-y-4">
          <!-- Aviso se pagamento confirmado -->
          <div v-if="payment?.status === 'confirmed'" class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
            <div class="flex items-start">
              <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              <div class="flex-1">
                <p class="text-sm font-medium text-yellow-800">
                  Pagamento Confirmado
                </p>
                <p class="text-xs text-yellow-700 mt-1">
                  Apenas campos não financeiros podem ser editados. Para alterar valores, estorne o pagamento primeiro.
                </p>
              </div>
            </div>
          </div>

          <!-- Campos editáveis baseados no status -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Referência -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Referência
              </label>
              <input
                v-model="form.reference"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-300': errors.reference }"
                placeholder="Ex: Transação PIX, comprovante..."
              />
              <p v-if="errors.reference" class="mt-1 text-sm text-red-600">{{ errors.reference }}</p>
            </div>

            <!-- Transaction ID -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                ID da Transação
              </label>
              <input
                v-model="form.transaction_id"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-300': errors.transaction_id }"
                placeholder="ID da transação..."
              />
              <p v-if="errors.transaction_id" class="mt-1 text-sm text-red-600">{{ errors.transaction_id }}</p>
            </div>

            <!-- Campos financeiros (apenas se não confirmado) -->
            <template v-if="payment?.status !== 'confirmed'">
              <!-- Valor Pago -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Valor Pago <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.amount"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-300': errors.amount }"
                />
                <p v-if="errors.amount" class="mt-1 text-sm text-red-600">{{ errors.amount }}</p>
              </div>

              <!-- Método de Pagamento -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Método de Pagamento <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.method"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-300': errors.method }"
                >
                  <option value="">Selecione o método</option>
                  <option value="cash">Dinheiro</option>
                  <option value="pix">PIX</option>
                  <option value="credit_card">Cartão de Crédito</option>
                  <option value="debit_card">Cartão de Débito</option>
                  <option value="bank_transfer">Transferência Bancária</option>
                  <option value="check">Cheque</option>
                </select>
                <p v-if="errors.method" class="mt-1 text-sm text-red-600">{{ errors.method }}</p>
              </div>

              <!-- Data do Pagamento -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Data do Pagamento <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.payment_date"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-300': errors.payment_date }"
                />
                <p v-if="errors.payment_date" class="mt-1 text-sm text-red-600">{{ errors.payment_date }}</p>
              </div>

              <!-- Responsável pelo Pagamento -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Responsável pelo Pagamento <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.paid_by_guardian_id"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  :class="{ 'border-red-300': errors.paid_by_guardian_id }"
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
                <p v-if="errors.paid_by_guardian_id" class="mt-1 text-sm text-red-600">{{ errors.paid_by_guardian_id }}</p>
              </div>
            </template>
          </div>

          <!-- Observações -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Observações
            </label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              :class="{ 'border-red-300': errors.notes }"
              placeholder="Observações adicionais..."
            ></textarea>
            <p v-if="errors.notes" class="mt-1 text-sm text-red-600">{{ errors.notes }}</p>
          </div>

          <!-- Botões de Ação -->
          <div class="flex items-center justify-end pt-6 border-t border-gray-200 mt-6">
            <button
              type="button"
              @click="closeModal"
              class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="processing"
              class="ml-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
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
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  payment: {
    type: Object,
    required: true
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

const form = reactive({
  amount: 0,
  method: '',
  payment_date: '',
  paid_by_guardian_id: '',
  reference: '',
  transaction_id: '',
  notes: ''
})

const loadGuardians = async () => {
  loadingGuardians.value = true
  try {
    const response = await axios.get(`/api/students/${props.student.id}/guardians`)
    guardians.value = response.data
  } catch (error) {
    console.error('Erro ao carregar responsáveis:', error)
  } finally {
    loadingGuardians.value = false
  }
}

const initializeForm = () => {
  if (props.payment) {
    form.amount = props.payment.amount || 0
    form.method = props.payment.method || ''
    form.payment_date = props.payment.payment_date ? props.payment.payment_date.split('T')[0] : ''
    form.paid_by_guardian_id = props.payment.paid_by_guardian_id || ''
    form.reference = props.payment.reference || ''
    form.transaction_id = props.payment.transaction_id || ''
    form.notes = props.payment.notes || ''
  }
}

const closeModal = () => {
  emit('close')
}

const submitEdit = async () => {
  processing.value = true
  errors.value = {}

  try {
    // Preparar dados para envio (remover campos vazios se não confirmado)
    const updateData = {}
    
    if (props.payment.status === 'confirmed') {
      // Apenas campos não financeiros
      if (form.reference !== null) updateData.reference = form.reference
      if (form.transaction_id !== null) updateData.transaction_id = form.transaction_id
      if (form.notes !== null) updateData.notes = form.notes
    } else {
      // Todos os campos
      updateData.amount = parseFloat(form.amount) || 0
      updateData.method = form.method
      updateData.payment_date = form.payment_date
      updateData.paid_by_guardian_id = parseInt(form.paid_by_guardian_id) || null
      updateData.reference = form.reference || null
      updateData.transaction_id = form.transaction_id || null
      updateData.notes = form.notes || null
    }

    await axios.put(`/api/monthly-payments/${props.payment.id}`, updateData)

    emit('success')
    alert('Pagamento atualizado com sucesso!')
    closeModal()
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      alert('Erro ao atualizar pagamento: ' + (error.response?.data?.message || error.message))
    }
  } finally {
    processing.value = false
  }
}

watch(() => props.show, (newValue) => {
  if (newValue) {
    initializeForm()
    loadGuardians()
  }
})

onMounted(() => {
  if (props.show) {
    initializeForm()
    loadGuardians()
  }
})
</script>

