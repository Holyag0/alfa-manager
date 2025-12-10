<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Overlay -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>

    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
      <div class="relative transform overflow-hidden rounded-lg bg-white shadow-xl transition-all w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-4">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-white flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Registrar Pagamento
            </h3>
            <button @click="$emit('close')" class="text-white hover:text-gray-200">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Body -->
        <div v-if="isPayrollClosed" class="px-6 py-4 bg-yellow-50 border-l-4 border-yellow-400">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-yellow-700">
                <strong>Folha de pagamento fechada!</strong> Esta folha não permite alterações. Reabra a folha para fazer modificações.
              </p>
            </div>
          </div>
        </div>
        <form @submit.prevent="handleSubmit" class="px-6 py-6 space-y-6" :class="{ 'opacity-50 pointer-events-none': isPayrollClosed }">
          <!-- Dados do Colaborador -->
          <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
            <h4 class="text-sm font-semibold text-blue-900 mb-3 flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              DADOS DO COLABORADOR
            </h4>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-gray-600">Nome:</span>
                <p class="font-medium text-gray-900">{{ employee.name }}</p>
              </div>
              <div>
                <span class="text-gray-600">Cargo:</span>
                <p class="font-medium text-gray-900">{{ employee.position?.name || '-' }}</p>
              </div>
              <div>
                <span class="text-gray-600">CPF:</span>
                <p class="font-medium text-gray-900">{{ employee.cpf || '-' }}</p>
              </div>
              <div>
                <span class="text-gray-600">Data Contratação:</span>
                <p class="font-medium text-gray-900">{{ formatDate(employee.hire_date) }}</p>
              </div>
              <div class="col-span-2">
                <span class="text-gray-600">Salário Base (cadastro):</span>
                <p class="font-medium text-green-600">{{ formatCurrency(employee.salary || 0) }}</p>
              </div>
            </div>
          </div>

          <!-- Proventos -->
          <div class="bg-green-50 rounded-lg p-4 border border-green-200">
            <h4 class="text-sm font-semibold text-green-900 mb-3 flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              PROVENTOS
            </h4>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">SAL. CART</label>
                <input
                  v-model.number="form.base_salary"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gratificação</label>
                <input
                  v-model.number="form.gratification"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Abonos</label>
                <input
                  v-model.number="form.bonus"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                />
              </div>
              <div class="pt-2 border-t border-green-300">
                <div class="flex justify-between items-center">
                  <span class="text-sm font-semibold text-gray-700">TOTAL BRUTO:</span>
                  <span class="text-lg font-bold text-green-600">{{ formatCurrency(calculatedGross) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Descontos -->
          <div class="bg-red-50 rounded-lg p-4 border border-red-200">
            <h4 class="text-sm font-semibold text-red-900 mb-3 flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
              </svg>
              DESCONTOS
            </h4>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">INSS</label>
                <input
                  v-model.number="form.inss_deduction"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vale</label>
                <input
                  v-model.number="form.advance_deduction"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Outros</label>
                <input
                  v-model.number="form.other_deductions"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                />
              </div>
              <div class="pt-2 border-t border-red-300">
                <div class="flex justify-between items-center">
                  <span class="text-sm font-semibold text-gray-700">TOTAL DESC:</span>
                  <span class="text-lg font-bold text-red-600">{{ formatCurrency(calculatedDeductions) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Salário Líquido -->
          <div class="bg-green-100 rounded-lg p-4 border-2 border-green-300">
            <div class="flex justify-between items-center">
              <span class="text-lg font-bold text-gray-900">SALÁRIO LÍQUIDO:</span>
              <span class="text-2xl font-bold text-green-700">{{ formatCurrency(calculatedNet) }}</span>
            </div>
          </div>

          <!-- Forma de Pagamento -->
          <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
            <h4 class="text-sm font-semibold text-purple-900 mb-3 flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
              </svg>
              FORMA DE PAGAMENTO
            </h4>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Método</label>
                <select
                  v-model="form.payment_method"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                >
                  <option value="">Selecione...</option>
                  <option value="pix">PIX</option>
                  <option value="poupanca">Poupança</option>
                  <option value="conta_corrente">Conta Corrente</option>
                  <option value="dinheiro">Dinheiro</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dados (CPF PIX, dados conta, etc.)</label>
                <input
                  v-model="form.payment_info"
                  type="text"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                  placeholder="Ex: CPF 00140488332"
                />
              </div>
            </div>
          </div>

          <!-- Observações -->
          <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Observações
            </h4>
            <textarea
              v-model="form.notes"
              rows="3"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500"
              placeholder="Observações adicionais..."
            ></textarea>
          </div>

          <!-- Marcar como pago -->
          <div class="flex items-center">
            <input
              v-model="form.mark_as_paid"
              type="checkbox"
              id="mark_as_paid"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="mark_as_paid" class="ml-2 block text-sm text-gray-900">
              Marcar como pago
            </label>
          </div>
        </form>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
          <button
            @click="$emit('close')"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          >
            Cancelar
          </button>
          <button
            @click="handleSubmit"
            :disabled="isSubmitting || isPayrollClosed"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
          >
            <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ isSubmitting ? 'Salvando...' : 'Salvar Pagamento' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  show: Boolean,
  employee: Object,
  entry: Object,
  payrollId: Number,
  payrollStatus: {
    type: String,
    default: 'draft'
  }
})

const emit = defineEmits(['close', 'saved'])

const form = ref({
  base_salary: 0,
  gratification: 0,
  bonus: 0,
  inss_deduction: 0,
  advance_deduction: 0,
  other_deductions: 0,
  payment_method: null,
  payment_info: null,
  notes: null,
  mark_as_paid: false
})

const isSubmitting = ref(false)

const isPayrollClosed = computed(() => props.payrollStatus === 'completed')

// Carregar dados do entry se existir
watch(() => props.entry, (newEntry) => {
  if (newEntry) {
    form.value = {
      base_salary: parseFloat(newEntry.base_salary) || parseFloat(props.employee.salary) || 0,
      gratification: parseFloat(newEntry.gratification) || 0,
      bonus: parseFloat(newEntry.bonus) || 0,
      inss_deduction: parseFloat(newEntry.inss_deduction) || 0,
      advance_deduction: parseFloat(newEntry.advance_deduction) || 0,
      other_deductions: parseFloat(newEntry.other_deductions) || 0,
      payment_method: newEntry.payment_method || null,
      payment_info: newEntry.payment_info || null,
      notes: newEntry.notes || null,
      mark_as_paid: !!newEntry.paid_at
    }
  } else {
    // Se não houver entry, usar salário de carteira do colaborador (SAL. CART)
    form.value = {
      base_salary: parseFloat(props.employee.salary) || 0,
      gratification: 0,
      bonus: 0,
      inss_deduction: 0,
      advance_deduction: 0,
      other_deductions: 0,
      payment_method: null,
      payment_info: null,
      notes: null,
      mark_as_paid: false
    }
  }
}, { immediate: true })

const calculatedGross = computed(() => {
  return (form.value.base_salary || 0) + (form.value.gratification || 0) + (form.value.bonus || 0)
})

const calculatedDeductions = computed(() => {
  return (form.value.inss_deduction || 0) + (form.value.advance_deduction || 0) + (form.value.other_deductions || 0)
})

const calculatedNet = computed(() => {
  return calculatedGross.value - calculatedDeductions.value
})

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value || 0)
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('pt-BR')
}

const handleSubmit = async () => {
  isSubmitting.value = true
  
  try {
    await router.post(
      route('payroll.entry.store', [props.payrollId, props.employee.id]),
      form.value,
      {
        preserveState: false,
        onFinish: () => {
          isSubmitting.value = false
          emit('saved')
        }
      }
    )
  } catch (error) {
    console.error('Erro ao salvar pagamento:', error)
    isSubmitting.value = false
  }
}
</script>

