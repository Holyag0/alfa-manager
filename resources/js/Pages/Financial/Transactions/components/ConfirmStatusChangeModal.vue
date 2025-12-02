<template>
  <TransitionRoot :show="show" as="template">
    <Dialog as="div" class="relative z-50" @close="cancel">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
              <!-- Ícone e Título -->
              <div class="px-6 pt-6 pb-4">
                <div class="flex items-start">
                  <!-- Ícone -->
                  <div :class="[
                    'flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:h-10 sm:w-10',
                    newStatus === 'confirmed' ? 'bg-green-100' : 'bg-yellow-100'
                  ]">
                    <!-- Ícone Check (Confirmando) -->
                    <svg v-if="newStatus === 'confirmed'" class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <!-- Ícone Alerta (Voltando para Pendente) -->
                    <svg v-else class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                  </div>
                  
                  <!-- Título e Descrição -->
                  <div class="ml-4 mt-0 flex-1">
                    <DialogTitle class="text-lg font-semibold leading-6 text-gray-900">
                      {{ newStatus === 'confirmed' ? 'Confirmar Transação?' : 'Voltar para Pendente?' }}
                    </DialogTitle>
                    
                    <div class="mt-3 space-y-3">
                      <!-- Mensagem principal -->
                      <p class="text-sm text-gray-600">
                        {{ getMessage() }}
                      </p>
                      
                      <!-- Informações da transação -->
                      <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                        <div class="flex items-center justify-between text-sm">
                          <span class="font-medium text-gray-700">Transação:</span>
                          <span class="text-gray-900">{{ transactionNumber }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm mt-2">
                          <span class="font-medium text-gray-700">Valor:</span>
                          <span :class="transactionType === 'receita' ? 'text-green-600' : 'text-red-600'" 
                                class="font-semibold">
                            {{ amount }}
                          </span>
                        </div>
                      </div>
                      
                      <!-- Aviso sobre confirmação -->
                      <div v-if="newStatus === 'confirmed'" class="flex items-start space-x-2 text-sm">
                        <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1">
                          <p class="font-medium text-gray-700">Ao confirmar:</p>
                          <ul class="mt-1 space-y-1 text-gray-600 list-disc list-inside">
                            <li>Data e hora da confirmação serão registradas</li>
                            <li>Seu usuário será registrado como confirmador</li>
                            <li>A transação entrará nos relatórios financeiros</li>
                          </ul>
                        </div>
                      </div>
                      
                      <!-- Aviso sobre voltar para pendente -->
                      <div v-else class="flex items-start space-x-2 text-sm">
                        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div class="flex-1">
                          <p class="font-medium text-gray-700">Ao voltar para pendente:</p>
                          <ul class="mt-1 space-y-1 text-gray-600 list-disc list-inside">
                            <li>A transação ficará aguardando confirmação</li>
                            <li>Poderá ser confirmada novamente depois</li>
                            <li>Os dados de confirmação anteriores serão mantidos</li>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Botões de Ação -->
              <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3">
                <button
                  type="button"
                  @click="confirm"
                  :class="[
                    'inline-flex justify-center rounded-md px-4 py-2 text-sm font-semibold text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2',
                    newStatus === 'confirmed' 
                      ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' 
                      : 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500'
                  ]"
                >
                  {{ newStatus === 'confirmed' ? 'Sim, Confirmar' : 'Sim, Voltar para Pendente' }}
                </button>
                <button
                  type="button"
                  @click="cancel"
                  class="inline-flex justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                >
                  Cancelar
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  newStatus: {
    type: String,
    required: true
  },
  transactionNumber: {
    type: String,
    required: true
  },
  amount: {
    type: String,
    required: true
  },
  transactionType: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['confirm', 'cancel'])

const getMessage = () => {
  if (props.newStatus === 'confirmed') {
    return 'Você está confirmando esta transação. Após a confirmação, a transação será contabilizada nos relatórios financeiros.'
  } else {
    return 'Você está alterando o status desta transação de Confirmada para Pendente. Tem certeza que deseja fazer isso?'
  }
}

const confirm = () => {
  emit('confirm')
}

const cancel = () => {
  emit('cancel')
}
</script>

