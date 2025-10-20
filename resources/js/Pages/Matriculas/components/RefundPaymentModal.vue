<template>
  <!-- Modal backdrop -->
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeModal">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white" @click.stop>
      <!-- Modal header -->
      <div class="flex items-center justify-between pb-4 border-b border-gray-200">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Estornar Pagamento</h3>
          <p class="text-sm text-gray-600">Confirme os detalhes do estorno</p>
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
        <!-- Informações do Pagamento -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
          <h4 class="text-sm font-medium text-gray-900 mb-3">Informações do Pagamento</h4>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <span class="text-gray-600">Número:</span>
              <span class="font-medium ml-2">{{ payment.payment_number }}</span>
            </div>
            <div>
              <span class="text-gray-600">Valor:</span>
              <span class="font-medium ml-2">{{ payment.formatted_amount }}</span>
            </div>
            <div>
              <span class="text-gray-600">Método:</span>
              <span class="font-medium ml-2">{{ payment.method_label }}</span>
            </div>
            <div>
              <span class="text-gray-600">Data:</span>
              <span class="font-medium ml-2">{{ formatDate(payment.payment_date) }}</span>
            </div>
          </div>
        </div>

        <!-- Formulário de Estorno -->
        <form @submit.prevent="processRefund" class="space-y-4">
          <!-- Motivo do Estorno -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Motivo do Estorno <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="refundForm.reason"
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
              placeholder="Descreva o motivo do estorno..."
              required
            ></textarea>
            <p class="text-xs text-gray-500 mt-1">Este motivo será registrado no histórico do pagamento</p>
          </div>

          <!-- Confirmação -->
          <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Atenção!</h3>
                <div class="mt-2 text-sm text-red-700">
                  <ul class="list-disc list-inside space-y-1">
                    <li>Esta ação não pode ser desfeita</li>
                    <li>O pagamento será marcado como "Reembolsado"</li>
                    <li>O serviço relacionado voltará para "Estornado"</li>
                    <li>Os totais financeiros serão recalculados</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Checkbox de Confirmação -->
          <div class="flex items-center">
            <input
              v-model="refundForm.confirmed"
              type="checkbox"
              id="confirmRefund"
              class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded"
              required
            />
            <label for="confirmRefund" class="ml-2 block text-sm text-gray-900">
              Eu confirmo que desejo estornar este pagamento
            </label>
          </div>
        </form>
      </div>

      <!-- Modal footer -->
      <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end space-x-3">
        <button
          @click="closeModal"
          class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          Cancelar
        </button>
        <button
          @click="processRefund"
          :disabled="!refundForm.confirmed || !refundForm.reason.trim() || processing"
          class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25 disabled:cursor-not-allowed"
        >
          <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
          {{ processing ? 'Estornando...' : 'Confirmar Estorno' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  payment: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'refund-success'])

const processing = ref(false)
const refundForm = ref({
  reason: '',
  confirmed: false
})

// Reset form when modal opens/closes
watch(() => props.show, (newValue) => {
  if (newValue) {
    resetForm()
  }
})

const resetForm = () => {
  refundForm.value = {
    reason: '',
    confirmed: false
  }
}

const closeModal = () => {
  emit('close')
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const processRefund = async () => {
  if (!refundForm.value.confirmed || !refundForm.value.reason.trim()) {
    return
  }

  processing.value = true

  try {
    const response = await fetch(`/api/enrollments/${props.payment.enrollment_id}/payments/${props.payment.id}/refund`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        reason: refundForm.value.reason.trim()
      })
    })
    
    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.error || 'Erro ao estornar pagamento')
    }
    
    const result = await response.json()
    console.log('Pagamento estornado:', result)
    
    // Emitir evento de sucesso
    emit('refund-success', result)
    
    // Fechar modal
    closeModal()
    
    alert('Pagamento estornado com sucesso!')
    
  } catch (error) {
    console.error('Erro ao estornar pagamento:', error)
    alert(`Erro ao estornar pagamento: ${error.message}`)
  } finally {
    processing.value = false
  }
}
</script>
