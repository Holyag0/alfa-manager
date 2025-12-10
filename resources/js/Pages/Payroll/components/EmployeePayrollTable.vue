<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
      <h2 class="text-lg font-semibold text-gray-900">Colaboradores</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Nome
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Cargo
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              SAL. CART
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Gratif
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Bônus
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Bruto
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              INSS
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Vale
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Líquido
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr
            v-for="item in employeesData"
            :key="item.employee.id"
            @click="canEdit ? openPayment(item) : null"
            :class="[
              'transition-colors duration-200',
              canEdit ? 'hover:bg-gray-50 cursor-pointer' : 'cursor-not-allowed opacity-60'
            ]"
          >
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ item.employee.name }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-500">{{ item.employee.position?.name || '-' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
              {{ formatCurrency(item.entry?.base_salary || 0) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
              {{ formatCurrency(item.entry?.gratification || 0) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
              {{ formatCurrency(item.entry?.bonus || 0) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
              {{ formatCurrency(item.entry?.gross_salary || 0) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-red-600">
              {{ formatCurrency(item.entry?.inss_deduction || 0) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-red-600">
              {{ formatCurrency(item.entry?.advance_deduction || 0) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-green-600">
              {{ formatCurrency(item.entry?.net_salary || 0) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-center">
              <span :class="getStatusBadgeClass(item)">
                {{ item.is_paid ? 'Pago' : item.has_entry ? 'Pendente' : 'Não Registrado' }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  employeesData: Array,
  payrollId: Number,
  payrollStatus: {
    type: String,
    default: 'draft'
  }
})

const canEdit = computed(() => props.payrollStatus !== 'completed')

const emit = defineEmits(['open-payment'])

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value || 0)
}

const getStatusBadgeClass = (item) => {
  if (item.is_paid) {
    return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800'
  } else if (item.has_entry) {
    return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800'
  } else {
    return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800'
  }
}

const openPayment = (item) => {
  emit('open-payment', item)
}
</script>

