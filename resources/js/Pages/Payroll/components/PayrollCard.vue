<template>
  <div
    @click="$emit('click')"
    class="bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-200 cursor-pointer border border-gray-200 overflow-hidden hover:scale-105"
  >
    <!-- Header com gradiente -->
    <div class="bg-gradient-to-r from-blue-500 to-cyan-500 px-6 py-4">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-xl font-bold text-white">{{ payroll.month_name }}</h3>
          <p class="text-sm text-blue-100">{{ payroll.year }}</p>
        </div>
        <div class="text-right">
          <span :class="getStatusBadgeClass(payroll.status)">
            {{ getStatusLabel(payroll.status) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Conteúdo -->
    <div class="px-6 py-4">
      <div class="space-y-3">
        <!-- Totais -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="text-xs text-gray-500 uppercase">Total Bruto</p>
            <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(payroll.total_gross) }}</p>
          </div>
          <div>
            <p class="text-xs text-gray-500 uppercase">Total Líquido</p>
            <p class="text-lg font-semibold text-green-600">{{ formatCurrency(payroll.total_net) }}</p>
          </div>
        </div>

        <!-- Estatísticas -->
        <div class="pt-3 border-t border-gray-200">
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-600">Colaboradores</span>
            <span class="font-semibold text-gray-900">{{ payroll.employees_count || 0 }}</span>
          </div>
          <div class="flex items-center justify-between text-sm mt-2">
            <span class="text-gray-600">Pagamentos</span>
            <span class="font-semibold text-green-600">{{ payroll.paid_count || 0 }} / {{ payroll.employees_count || 0 }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
      <div class="flex items-center justify-between text-sm">
        <span class="text-gray-500">Referência</span>
        <span class="font-medium text-gray-700">{{ payroll.reference }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  payroll: Object
})

defineEmits(['click'])

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
    draft: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800',
    processing: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800',
    completed: 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800'
  }
  return classes[status] || classes.draft
}
</script>

