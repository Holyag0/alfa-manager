<template>
  <div class="min-h-screen">
    <!-- Header Section -->
    <div class="border-b border-cyan-500">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6 md:flex md:items-center md:justify-between">
          <div class="flex-1 min-w-0">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h1 class="text-2xl font-bold text-sky-700">{{ payroll.reference }}</h1>
                <strong class="text-sm text-gray-500">Folha de Pagamento - {{ payroll.month_name }} / {{ payroll.year }}</strong>
              </div>
            </div>
          </div>
          <div class="mt-4 flex md:mt-0 md:ml-4 space-x-2">
            <button
              v-if="payroll.status !== 'completed'"
              @click="updatePayroll"
              :disabled="isUpdating"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="isUpdating" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              {{ isUpdating ? 'Atualizando...' : 'Atualizar Folha' }}
            </button>
            <button
              v-if="payroll.status === 'completed'"
              @click="reopenPayroll"
              :disabled="isReopening"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="isReopening" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              {{ isReopening ? 'Reabrindo...' : 'Reabrir Folha' }}
            </button>
            <button
              v-else
              @click="closePayroll"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700"
            >
              Fechar Folha
            </button>
            <Link
              :href="route('payroll.index', { year: payroll.year })"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
            >
              Voltar
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Resumo da Folha -->
      <PayrollSummary :payroll="payroll" />

      <!-- Tabela de Colaboradores -->
      <EmployeePayrollTable
        :employees-data="employeesData"
        :payroll-id="payroll.id"
        :payroll-status="payroll.status"
        @open-payment="openPaymentModal"
      />

      <!-- Modal de Pagamento -->
      <PaymentEntryModal
        v-if="selectedEmployee"
        :show="showPaymentModal"
        :employee="selectedEmployee.employee"
        :entry="selectedEmployee.entry"
        :payroll-id="payroll.id"
        :payroll-status="payroll.status"
        @close="closePaymentModal"
        @saved="handlePaymentSaved"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import PayrollSummary from './components/PayrollSummary.vue'
import EmployeePayrollTable from './components/EmployeePayrollTable.vue'
import PaymentEntryModal from './components/PaymentEntryModal.vue'

const props = defineProps({
  payroll: Object,
  employeesData: Array
})

const showPaymentModal = ref(false)
const selectedEmployee = ref(null)
const isUpdating = ref(false)
const isReopening = ref(false)

const openPaymentModal = async (employeeData) => {
  if (props.payroll.status === 'completed') {
    alert('Folha de pagamento fechada não permite alterações. Reabra a folha para fazer alterações.')
    return
  }
  selectedEmployee.value = employeeData
  showPaymentModal.value = true
}

const closePaymentModal = () => {
  showPaymentModal.value = false
  setTimeout(() => {
    selectedEmployee.value = null
  }, 300)
}

const handlePaymentSaved = () => {
  router.reload({ only: ['payroll', 'employeesData'] })
  closePaymentModal()
}

const closePayroll = () => {
  if (confirm('Tem certeza que deseja fechar esta folha de pagamento?')) {
    router.post(route('payroll.close', props.payroll.id), {}, {
      preserveState: false
    })
  }
}

const reopenPayroll = () => {
  if (confirm('Tem certeza que deseja reabrir esta folha de pagamento? Após reabrir, você poderá fazer alterações novamente.')) {
    isReopening.value = true
    router.post(route('payroll.reopen', props.payroll.id), {}, {
      preserveState: false,
      onFinish: () => {
        isReopening.value = false
      }
    })
  }
}

const updatePayroll = () => {
  if (!confirm('Tem certeza que deseja atualizar esta folha? Isso irá remover colaboradores desativados e adicionar novos colaboradores ativos.')) {
    return
  }
  
  isUpdating.value = true
  router.put(route('payroll.update', props.payroll.id), {}, {
    preserveState: false,
    onFinish: () => {
      isUpdating.value = false
    }
  })
}
</script>

