<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[70]" @click="handleCancel">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-md shadow-lg rounded-md bg-white" @click.stop>
      <!-- Modal header -->
      <div class="flex items-center justify-between pb-4 border-b border-gray-200">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">
            Deletar Mensalidades em Lote
          </h3>
          <p class="text-sm text-gray-600 mt-1">
            Esta ação irá deletar {{ installmentIds.length }} mensalidade(s)
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
        <!-- Aviso importante -->
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-red-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1">
              <p class="text-sm font-medium text-red-900">
                Atenção: Esta ação não pode ser desfeita
              </p>
              <p class="text-xs text-red-800 mt-1">
                As mensalidades selecionadas serão deletadas permanentemente. Mensalidades com pagamentos confirmados serão processadas, mas recomenda-se reverter os pagamentos primeiro para manter a integridade dos dados.
              </p>
            </div>
          </div>
        </div>

        <!-- Informação -->
        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
              <p class="text-sm font-medium text-blue-900">
                Mensalidades Selecionadas
              </p>
              <p class="text-xs text-blue-800 mt-1">
                {{ installmentIds.length }} mensalidade(s) serão deletadas.
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
            :disabled="processing"
            class="ml-4 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
          >
            <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ processing ? 'Deletando...' : 'Confirmar Deleção' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  installmentIds: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['close', 'success'])

const processing = ref(false)

const handleConfirm = async () => {
  processing.value = true

  try {
    const response = await axios.post('/api/installments/delete-batch', {
      installment_ids: props.installmentIds
    })

    emit('success')
    alert(response.data.message || 'Mensalidades deletadas com sucesso!')
    handleCancel()
  } catch (error) {
    alert('Erro ao deletar mensalidades: ' + (error.response?.data?.message || error.message))
  } finally {
    processing.value = false
  }
}

const handleCancel = () => {
  emit('close')
}
</script>

