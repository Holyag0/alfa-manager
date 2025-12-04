<template>
  <TransitionRoot :show="show && !!transaction" as="template">
    <Dialog as="div" class="relative z-50" @close="closeModal">
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
            <DialogPanel v-if="transaction" class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
              <!-- Header -->
              <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r"
                   :class="transaction.type === 'receita' ? 'from-green-50 to-emerald-50' : 'from-red-50 to-rose-50'">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <div :class="[
                      'p-3 rounded-lg',
                      transaction.type === 'receita' ? 'bg-green-100' : 'bg-red-100'
                    ]">
                      <svg class="w-6 h-6" 
                           :class="transaction.type === 'receita' ? 'text-green-600' : 'text-red-600'"
                           fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="transaction.type === 'receita'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                      </svg>
                    </div>
                    <div class="ml-4">
                      <DialogTitle class="text-lg font-semibold text-gray-900">
                        {{ transaction.type_label }}
                      </DialogTitle>
                      <p class="text-sm text-gray-600">{{ transaction.transaction_number }}</p>
                    </div>
                  </div>
                  <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Body -->
              <div class="px-6 py-6 space-y-6 max-h-[60vh] overflow-y-auto">
                <!-- Loading Skeleton -->
                <div v-if="loading" class="space-y-4 animate-pulse">
                  <div class="h-12 bg-gray-200 rounded"></div>
                  <div class="h-20 bg-gray-200 rounded"></div>
                  <div class="grid grid-cols-2 gap-4">
                    <div class="h-16 bg-gray-200 rounded"></div>
                    <div class="h-16 bg-gray-200 rounded"></div>
                  </div>
                  <div class="h-24 bg-gray-200 rounded"></div>
                </div>

                <!-- Conteúdo Real -->
                <div v-else>
                <!-- Valor e Status -->
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm text-gray-500">Valor</p>
                    <p class="text-3xl font-bold" 
                       :class="transaction.type === 'receita' ? 'text-green-600' : 'text-red-600'">
                      {{ transaction.formatted_amount }}
                    </p>
                  </div>
                  <div class="flex items-center space-x-3">
                    <!-- Toggle de Status -->
                    <div v-if="transaction.status !== 'cancelled' && !transaction.source_type" 
                         class="flex flex-col items-end">
                      <label class="flex items-center cursor-pointer group">
                        <span class="text-xs font-medium text-gray-600 mr-2">Pendente</span>
                        <div class="relative">
                          <input type="checkbox" 
                                 :checked="transaction.status === 'confirmed'"
                                 @change="toggleStatus"
                                 :disabled="isTogglingStatus"
                                 class="sr-only peer">
                          <div class="w-11 h-6 bg-gray-300 rounded-full peer 
                                      peer-checked:bg-green-500 
                                      peer-focus:ring-2 peer-focus:ring-green-300
                                      peer-disabled:opacity-50 peer-disabled:cursor-not-allowed
                                      transition-colors duration-200">
                          </div>
                          <div class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full 
                                      peer-checked:translate-x-5 
                                      transition-transform duration-200">
                          </div>
                        </div>
                        <span class="text-xs font-medium text-gray-600 ml-2">Confirmado</span>
                      </label>
                      <p v-if="isTogglingStatus" class="text-xs text-gray-500 mt-1">
                        Atualizando...
                      </p>
                    </div>
                    
                    <!-- Badge de Status (para transações canceladas ou rastreadas) -->
                    <span v-else :class="getStatusClass(transaction.status)">
                      {{ transaction.status_label }}
                    </span>
                  </div>
                </div>

                <!-- Descrição -->
                <div>
                  <h3 class="text-sm font-medium text-gray-500 mb-2">Descrição</h3>
                  <p class="text-gray-900">{{ transaction.description }}</p>
                </div>

                <!-- Grid de Informações -->
                <div class="grid grid-cols-2 gap-6">
                  <!-- Categoria -->
                  <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Categoria</h3>
                    <div class="flex items-center">
                      <div :style="{ backgroundColor: transaction.category.color }" 
                           class="w-4 h-4 rounded-full mr-2"></div>
                      <span class="text-gray-900 font-medium">{{ transaction.category.name }}</span>
                    </div>
                  </div>

                  <!-- Data da Transação -->
                  <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Data da Transação</h3>
                    <p class="text-gray-900">{{ formatDate(transaction.transaction_date) }}</p>
                  </div>

                  <!-- Método de Pagamento -->
                  <div v-if="transaction.payment_method">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Método de Pagamento</h3>
                    <p class="text-gray-900">{{ transaction.payment_method_label || transaction.payment_method }}</p>
                  </div>

                  <!-- Referência -->
                  <div v-if="transaction.reference">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Referência</h3>
                    <p class="text-gray-900">{{ transaction.reference }}</p>
                  </div>
                </div>

                <!-- Informações do Pagante (Receita) -->
                <div v-if="transaction.type === 'receita' && (transaction.payer_name || transaction.payer_document)" 
                     class="pt-4 border-t border-gray-200">
                  <h3 class="text-sm font-medium text-gray-900 mb-3">Informações do Pagante</h3>
                  <div class="space-y-2 bg-gray-50 p-4 rounded-lg">
                    <div v-if="transaction.payer_name" class="flex items-center">
                      <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                      <div>
                        <p class="text-xs text-gray-500">Nome</p>
                        <p class="text-sm font-medium text-gray-900">{{ transaction.payer_name }}</p>
                      </div>
                    </div>
                    <div v-if="transaction.payer_document" class="flex items-center">
                      <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                      </svg>
                      <div>
                        <p class="text-xs text-gray-500">CPF/CNPJ</p>
                        <p class="text-sm font-medium text-gray-900">{{ transaction.payer_document }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Informações do Beneficiário (Despesa) -->
                <div v-if="transaction.type === 'despesa' && (transaction.payee_name || transaction.payee_document)" 
                     class="pt-4 border-t border-gray-200">
                  <h3 class="text-sm font-medium text-gray-900 mb-3">Informações do Fornecedor/Beneficiário</h3>
                  <div class="space-y-2 bg-gray-50 p-4 rounded-lg">
                    <div v-if="transaction.payee_name" class="flex items-center">
                      <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                      </svg>
                      <div>
                        <p class="text-xs text-gray-500">Nome</p>
                        <p class="text-sm font-medium text-gray-900">{{ transaction.payee_name }}</p>
                      </div>
                    </div>
                    <div v-if="transaction.payee_document" class="flex items-center">
                      <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                      </svg>
                      <div>
                        <p class="text-xs text-gray-500">CPF/CNPJ</p>
                        <p class="text-sm font-medium text-gray-900">{{ transaction.payee_document }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Origem da Transação (se houver) -->
                <div v-if="transaction.source_type" class="pt-4 border-t border-gray-200">
                  <div class="flex items-center p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="p-2 bg-blue-100 rounded-lg">
                      <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                    </div>
                    <div class="ml-3 flex-1">
                      <p class="text-xs font-medium text-blue-900">Transação Rastreada</p>
                      <p class="text-xs text-blue-700 mt-1">
                        {{ transaction.source_type === 'App\\Models\\MonthlyFeePayment' ? 'Pagamento de Mensalidade' : 'Pagamento de Matrícula' }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Observações -->
                <div v-if="transaction.notes" class="pt-4 border-t border-gray-200">
                  <h3 class="text-sm font-medium text-gray-500 mb-2">Observações</h3>
                  <p class="text-gray-900 whitespace-pre-wrap bg-gray-50 p-3 rounded-lg">{{ transaction.notes }}</p>
                </div>
                </div><!-- Fim Conteúdo Real -->
              </div>

              <!-- Footer com Ações -->
              <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <div class="flex space-x-3">
                  <button v-if="!transaction.source_type && transaction.status !== 'cancelled'"
                          @click="editTransaction"
                          class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar
                  </button>
                  
                  <button v-if="transaction.status !== 'cancelled'"
                          @click="cancelTransaction"
                          class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancelar Transação
                  </button>

                  <button v-if="transaction.status === 'cancelled' && !transaction.source_type"
                          @click="deleteTransaction"
                          :disabled="isDeleting"
                          class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg v-if="!isDeleting" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <svg v-else class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ isDeleting ? 'Excluindo...' : 'Excluir Transação' }}
                  </button>
                </div>
                
                <button @click="closeModal"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                  Fechar
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>

  <!-- Modal de Confirmação de Mudança de Status -->
  <ConfirmStatusChangeModal
    :show="showConfirmModal"
    :new-status="pendingNewStatus"
    :transaction-number="transaction?.transaction_number || ''"
    :amount="transaction?.formatted_amount || ''"
    :transaction-type="transaction?.type || ''"
    @confirm="confirmStatusChange"
    @cancel="cancelStatusChange"
  />
</template>

<script setup>
import { ref } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { router } from '@inertiajs/vue3'
import ConfirmStatusChangeModal from './ConfirmStatusChangeModal.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  transaction: {
    type: Object,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'edit', 'cancel', 'statusUpdated', 'deleted'])

const isTogglingStatus = ref(false)
const isDeleting = ref(false)
const showConfirmModal = ref(false)
const pendingNewStatus = ref(null)

const closeModal = () => {
  emit('close')
}

const toggleStatus = () => {
  if (isTogglingStatus.value) return
  
  const newStatus = props.transaction.status === 'confirmed' ? 'pending' : 'confirmed'
  
  // Armazenar o novo status e mostrar modal de confirmação
  pendingNewStatus.value = newStatus
  showConfirmModal.value = true
}

const confirmStatusChange = async () => {
  showConfirmModal.value = false
  isTogglingStatus.value = true
  
  try {
    const response = await fetch(
      route('financial.transactions.updateStatus', props.transaction.id),
      {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ status: pendingNewStatus.value })
      }
    )
    
    if (!response.ok) {
      const error = await response.json()
      throw new Error(error.error || 'Erro ao atualizar status')
    }
    
    const data = await response.json()
    
    // Atualizar o objeto transaction com os novos dados
    Object.assign(props.transaction, data.transaction)
    
    // Emitir evento para atualizar a lista também
    emit('statusUpdated', data.transaction)
  } catch (error) {
    console.error('Erro ao atualizar status:', error)
    alert(error.message || 'Erro ao atualizar status da transação')
  } finally {
    isTogglingStatus.value = false
    pendingNewStatus.value = null
  }
}

const cancelStatusChange = () => {
  showConfirmModal.value = false
  pendingNewStatus.value = null
}

const editTransaction = () => {
  router.visit(route('financial.transactions.edit', props.transaction.id))
}

const cancelTransaction = () => {
  if (confirm('Tem certeza que deseja cancelar esta transação?')) {
    const reason = prompt('Motivo do cancelamento (opcional):')
    router.post(route('financial.transactions.cancel', props.transaction.id), {
      reason: reason
    }, {
      preserveScroll: true,
      onSuccess: () => {
        closeModal()
        alert('Transação cancelada com sucesso!')
      }
    })
  }
}

const deleteTransaction = () => {
  if (!confirm('Tem certeza que deseja excluir permanentemente esta transação cancelada?\n\nEsta ação não pode ser desfeita.')) {
    return
  }

  isDeleting.value = true

  router.delete(route('financial.transactions.destroy', props.transaction.id), {
    preserveScroll: true,
    onSuccess: () => {
      emit('deleted', props.transaction.id)
      closeModal()
      alert('Transação excluída com sucesso!')
    },
    onError: (errors) => {
      const errorMessage = errors.error || 'Erro ao excluir transação. Verifique se ela não possui transações vinculadas.'
      alert(errorMessage)
    },
    onFinish: () => {
      isDeleting.value = false
    }
  })
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('pt-BR')
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800',
    confirmed: 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800',
    cancelled: 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800'
  }
  return classes[status] || classes.confirmed
}
</script>

