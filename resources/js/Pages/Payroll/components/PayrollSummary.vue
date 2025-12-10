<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 bg-gradient-to-r from-blue-500 to-cyan-500 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
              </svg>
            </div>
          </div>
          <div>
            <h3 class="text-xl font-bold text-white">Resumo da Folha</h3>
            <p class="text-sm text-white text-opacity-90">{{ payroll.reference }}</p>
          </div>
        </div>
        
        <!-- Toggle Button -->
        <button 
          @click="isExpanded = !isExpanded"
          class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 transition-colors duration-200"
          :class="{ 'rotate-180': isExpanded }"
        >
          <svg class="w-4 h-4 text-white transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Summary Content -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 max-h-0"
      enter-to-class="opacity-100 max-h-[800px]"
      leave-active-class="transition-all duration-300 ease-in"
      leave-from-class="opacity-100 max-h-[800px]"
      leave-to-class="opacity-0 max-h-0"
    >
      <div v-show="isExpanded" class="overflow-hidden">
        <div class="px-6 py-6">
          <!-- Status e Informações Principais -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="text-center">
              <p class="text-sm text-gray-500 uppercase mb-2">Status</p>
              <span :class="getStatusBadgeClass(payroll.status)">
                {{ getStatusLabel(payroll.status) }}
              </span>
            </div>
            <div class="text-center">
              <p class="text-sm text-gray-500 uppercase mb-2">Total Bruto</p>
              <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(payroll.total_gross) }}</p>
            </div>
            <div class="text-center">
              <p class="text-sm text-gray-500 uppercase mb-2">Total Líquido</p>
              <p class="text-2xl font-bold text-green-600">{{ formatCurrency(payroll.total_net) }}</p>
            </div>
            <div class="text-center">
              <p class="text-sm text-gray-500 uppercase mb-2">Colaboradores</p>
              <p class="text-2xl font-bold text-blue-600">{{ payroll.employees_count || 0 }}</p>
              <p class="text-sm text-gray-500 mt-1">{{ payroll.paid_count || 0 }} pagos</p>
            </div>
          </div>

          <!-- Divisor -->
          <div class="border-t border-gray-200 my-6"></div>

          <!-- Detalhamento de Valores -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Adições -->
            <div class="space-y-4">
              <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Adições</h4>
              <div class="space-y-3">
                <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                  <span class="text-sm font-medium text-gray-700">Gratificações</span>
                  <span class="text-lg font-semibold text-blue-600">{{ formatCurrency(payroll.total_gratification || 0) }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                  <span class="text-sm font-medium text-gray-700">Bônus</span>
                  <span class="text-lg font-semibold text-blue-600">{{ formatCurrency(payroll.total_bonus || 0) }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                  <span class="text-sm font-medium text-gray-700">Abono</span>
                  <span class="text-lg font-semibold text-blue-600">{{ formatCurrency(payroll.total_abono || 0) }}</span>
                </div>
              </div>
            </div>

            <!-- Descontos -->
            <div class="space-y-4">
              <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Descontos</h4>
              <div class="space-y-3">
                <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                  <span class="text-sm font-medium text-gray-700">INSS</span>
                  <span class="text-lg font-semibold text-red-600">{{ formatCurrency(payroll.total_inss || 0) }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                  <span class="text-sm font-medium text-gray-700">Vale</span>
                  <span class="text-lg font-semibold text-red-600">{{ formatCurrency(payroll.total_advance || 0) }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                  <span class="text-sm font-medium text-gray-700">Outros Descontos</span>
                  <span class="text-lg font-semibold text-red-600">{{ formatCurrency(payroll.total_other_deductions || 0) }}</span>
                </div>
              </div>
            </div>

            <!-- Resumo Final -->
            <div class="space-y-4">
              <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Resumo</h4>
              <div class="space-y-3">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                  <span class="text-sm font-medium text-gray-700">Total de Adições</span>
                  <span class="text-lg font-semibold text-blue-600">
                    {{ formatCurrency((payroll.total_gratification || 0) + (payroll.total_bonus || 0) + (payroll.total_abono || 0)) }}
                  </span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                  <span class="text-sm font-medium text-gray-700">Total de Descontos</span>
                  <span class="text-lg font-semibold text-red-600">
                    {{ formatCurrency((payroll.total_inss || 0) + (payroll.total_advance || 0) + (payroll.total_other_deductions || 0)) }}
                  </span>
                </div>
              </div>
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
  payroll: Object
})

const isExpanded = ref(false)

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value || 0)
}

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Rascunho',
    processing: 'Processando',
    completed: 'Fechada'
  }
  return labels[status] || status
}

const getStatusBadgeClass = (status) => {
  const classes = {
    draft: 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800',
    processing: 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800',
    completed: 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800'
  }
  return classes[status] || classes.draft
}
</script>

<style scoped>
.rotate-180 {
  transform: rotate(180deg);
}
</style>
