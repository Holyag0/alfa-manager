<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-cyan-50 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Configurações das Folhas</h3>
            <p class="text-sm text-gray-600">Gerencie e atualize as folhas de pagamento</p>
          </div>
        </div>
        
        <!-- Toggle Button -->
        <button 
          @click="isExpanded = !isExpanded"
          class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white shadow-sm border border-gray-200 hover:bg-gray-50 transition-colors duration-200"
          :class="{ 'rotate-180': isExpanded }"
        >
          <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Settings Content -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 max-h-0"
      enter-to-class="opacity-100 max-h-96"
      leave-active-class="transition-all duration-300 ease-in"
      leave-from-class="opacity-100 max-h-96"
      leave-to-class="opacity-0 max-h-0"
    >
      <div v-show="isExpanded" class="overflow-hidden">
        <div class="p-6 space-y-4">
          <!-- Botão Gerar Folhas -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <h4 class="text-sm font-semibold text-blue-900 mb-1">Gerar Folhas de Pagamento</h4>
                <p class="text-xs text-blue-700">Crie as 12 folhas mensais para um ano específico</p>
              </div>
              <button
                @click="$emit('generate')"
                :disabled="isGenerating"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed ml-4"
              >
                <svg v-if="isGenerating" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ isGenerating ? 'Gerando...' : 'Gerar Folhas' }}
              </button>
            </div>
          </div>

          <!-- Botão Atualizar Todas as Folhas -->
          <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <h4 class="text-sm font-semibold text-green-900 mb-1">Atualizar Todas as Folhas do Ano</h4>
                <p class="text-xs text-green-700">Remova colaboradores desativados e adicione novos colaboradores ativos em todas as folhas do ano selecionado</p>
              </div>
              <button
                @click="$emit('update-all')"
                :disabled="isUpdatingAll"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed ml-4"
              >
                <svg v-if="isUpdatingAll" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ isUpdatingAll ? 'Atualizando...' : 'Atualizar Todas' }}
              </button>
            </div>
          </div>

          <!-- Botão Deletar Todas as Folhas Draft -->
          <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <h4 class="text-sm font-semibold text-red-900 mb-1">Deletar Todas as Folhas do Ano</h4>
                <p class="text-xs text-red-700">⚠️ Deleta todas as folhas do ano selecionado que estão com status rascunho. Esta ação não pode ser desfeita!</p>
              </div>
              <button
                @click="$emit('delete-all-draft')"
                :disabled="isDeletingAll"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed ml-4"
              >
                <svg v-if="isDeletingAll" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{ isDeletingAll ? 'Deletando...' : 'Deletar Todas' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  isGenerating: {
    type: Boolean,
    default: false
  },
  isUpdatingAll: {
    type: Boolean,
    default: false
  },
  isDeletingAll: {
    type: Boolean,
    default: false
  }
})

defineEmits(['generate', 'update-all', 'delete-all-draft'])

const isExpanded = ref(false)
</script>

<style scoped>
.rotate-180 {
  transform: rotate(180deg);
}
</style>

