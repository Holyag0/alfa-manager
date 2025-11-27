<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[70]" @click="handleCancel">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-md shadow-lg rounded-md bg-white" @click.stop>
      <!-- Modal header -->
      <div class="flex items-center justify-between pb-4 border-b border-gray-200">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">
            Editar Data de Vencimento
          </h3>
          <p class="text-sm text-gray-600 mt-1">
            Altere a data de vencimento desta mensalidade
          </p>
        </div>
        <button
          @click="handleCancel"
          class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Modal content -->
      <div class="mt-6 space-y-4">
        <!-- Informações atuais -->
        <div class="bg-gray-50 rounded-lg p-4">
          <h4 class="text-sm font-medium text-gray-900 mb-3">Mensalidade</h4>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-600">Mês de Referência:</span>
              <span class="font-medium">{{ formatMonth(installment?.reference_month) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Parcela:</span>
              <span class="font-medium">{{ installment?.installment_number }}/{{ installment?.monthly_fee?.total_installments || 12 }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Vencimento Atual:</span>
              <span class="font-medium">{{ formatDate(installment?.due_date) }}</span>
            </div>
          </div>
        </div>

        <!-- Aviso se tiver pagamento -->
        <div v-if="hasPayment" class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1">
              <p class="text-sm font-medium text-yellow-900">
                Atenção
              </p>
              <p class="text-xs text-yellow-800 mt-1">
                Esta mensalidade possui pagamento(s). Apenas mensalidades sem pagamentos confirmados podem ter a data de vencimento alterada.
              </p>
            </div>
          </div>
        </div>

        <!-- Campo de data -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Nova Data de Vencimento <span class="text-red-500">*</span>
          </label>
          <input
            v-model="dueDate"
            type="date"
            required
            :disabled="hasPayment"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
          />
          <p v-if="errors.due_date" class="mt-1 text-sm text-red-600">{{ errors.due_date }}</p>
        </div>

        <!-- Botões de Ação -->
        <div class="flex items-center justify-end pt-6 border-t border-gray-200">
          <button
            type="button"
            @click="handleCancel"
            class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            Cancelar
          </button>
          <button
            type="button"
            @click="handleConfirm"
            :disabled="processing || hasPayment"
            class="ml-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
          >
            <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ processing ? 'Salvando...' : 'Salvar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  installment: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'success'])

const processing = ref(false)
const errors = ref({})
const dueDate = ref('')

const hasPayment = computed(() => {
  if (!props.installment?.payments) return false
  return props.installment.payments.some(p => p.status === 'confirmed')
})

watch(() => props.show, (newValue) => {
  if (newValue && props.installment?.due_date) {
    // Formatar data para input type="date" (YYYY-MM-DD)
    // Usar método que não sofre com timezone
    const date = new Date(props.installment.due_date)
    // Ajustar para timezone local para evitar problemas de conversão
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    dueDate.value = `${year}-${month}-${day}`
  }
})

const handleConfirm = async () => {
  if (!dueDate.value) {
    errors.value.due_date = 'Data de vencimento é obrigatória'
    return
  }

  processing.value = true
  errors.value = {}

  try {
    await axios.put(`/api/installments/${props.installment.id}`, {
      due_date: dueDate.value
    })

    emit('success')
    alert('Data de vencimento atualizada com sucesso!')
    handleCancel()
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      alert('Erro ao atualizar data de vencimento: ' + (error.response?.data?.message || error.message))
    }
  } finally {
    processing.value = false
  }
}

const handleCancel = () => {
  dueDate.value = ''
  errors.value = {}
  emit('close')
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('pt-BR')
}

const formatMonth = (monthString) => {
  if (!monthString) return ''
  const [year, month] = monthString.split('-')
  const monthNames = [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
  ]
  return `${monthNames[parseInt(month) - 1]} de ${year}`
}
</script>

