<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[70]" @click="handleCancel">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-3xl shadow-lg rounded-md bg-white" @click.stop>
      <!-- Modal header -->
      <div class="flex items-center justify-between pb-4 border-b border-gray-200">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">
            Confirmar Troca de Serviço em Lote
          </h3>
          <p class="text-sm text-gray-600 mt-1">
            Revise as informações antes de confirmar a operação
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
      <div class="mt-6 space-y-6">
        <!-- Resumo da operação -->
        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
              <p class="text-sm font-medium text-blue-900 mb-2">
                Resumo da Operação
              </p>
              <div class="text-sm text-blue-800 space-y-1">
                <p><strong>Total de mensalidades:</strong> {{ previewData.total_installments }}</p>
                <p class="text-green-700"><strong>Serão alteradas:</strong> {{ previewData.will_update }} mensalidade(s)</p>
                <p v-if="previewData.will_skip > 0" class="text-orange-700">
                  <strong>Serão ignoradas:</strong> {{ previewData.will_skip }} mensalidade(s) (possuem pagamentos confirmados)
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Novo serviço -->
        <div class="bg-gray-50 rounded-lg p-4">
          <h4 class="text-sm font-medium text-gray-900 mb-3">Novo Serviço</h4>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-600">Serviço:</span>
              <span class="font-medium">{{ previewData.new_service?.name || 'N/A' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Valor:</span>
              <span class="font-medium">{{ formatCurrency(previewData.new_service?.price || 0) }}</span>
            </div>
          </div>
        </div>

        <!-- Parcelas que serão alteradas -->
        <div v-if="previewData.can_update && previewData.can_update.length > 0">
          <h4 class="text-sm font-medium text-gray-900 mb-2">
            Mensalidades que serão alteradas ({{ previewData.can_update.length }})
          </h4>
          <div class="bg-green-50 border border-green-200 rounded-md p-3 max-h-48 overflow-y-auto">
            <div class="space-y-1">
              <div
                v-for="item in previewData.can_update"
                :key="item.id"
                class="text-sm text-green-800 flex items-center"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Parcela {{ item.installment_number }} - {{ formatMonth(item.reference_month) }} ({{ item.status }})
              </div>
            </div>
          </div>
        </div>

        <!-- Parcelas que serão ignoradas -->
        <div v-if="previewData.cannot_update && previewData.cannot_update.length > 0">
          <h4 class="text-sm font-medium text-gray-900 mb-2">
            Mensalidades que serão ignoradas ({{ previewData.cannot_update.length }})
          </h4>
          <div class="bg-orange-50 border border-orange-200 rounded-md p-3 max-h-48 overflow-y-auto">
            <div class="space-y-1">
              <div
                v-for="item in previewData.cannot_update"
                :key="item.id"
                class="text-sm text-orange-800 flex items-start"
              >
                <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                  <div>Parcela {{ item.installment_number }} - {{ formatMonth(item.reference_month) }} ({{ item.status }})</div>
                  <div class="text-xs text-orange-700 mt-0.5">{{ item.reason }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Aviso importante -->
        <div v-if="previewData.will_skip > 0" class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1">
              <p class="text-sm font-medium text-yellow-900">
                Atenção
              </p>
              <p class="text-xs text-yellow-800 mt-1">
                As mensalidades com pagamentos confirmados não serão alteradas. Para alterá-las, é necessário estornar os pagamentos primeiro.
              </p>
            </div>
          </div>
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
            :disabled="processing || previewData.will_update === 0"
            class="ml-4 inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
          >
            <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ processing ? 'Processando...' : 'Confirmar Troca' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  previewData: {
    type: Object,
    default: () => ({
      total_installments: 0,
      will_update: 0,
      will_skip: 0,
      can_update: [],
      cannot_update: [],
      new_service: {}
    })
  },
  processing: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['confirm', 'cancel'])

const handleConfirm = () => {
  emit('confirm')
}

const handleCancel = () => {
  emit('cancel')
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

