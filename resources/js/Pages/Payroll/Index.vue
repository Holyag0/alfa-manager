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
                <h1 class="text-2xl font-bold text-sky-700">Folha de Pagamento</h1>
                <strong class="text-sm text-gray-500">Gerencie as folhas de pagamento dos colaboradores</strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Filtros -->
      <PayrollFilters
        v-model:selected-year="selectedYear"
        :available-years="availableYears"
        @year-changed="handleYearChange"
      />

      <!-- Configurações das Folhas -->
      <PayrollSettings
        :is-generating="isGenerating || isGeneratingCustom"
        :is-updating-all="isUpdatingAll"
        :is-deleting-all="isDeletingAll"
        @generate="openGenerateModal"
        @update-all="updateAllPayrolls"
        @delete-all-draft="deleteAllDraftPayrolls"
      />

      <!-- Grid de Folhas -->
      <div v-if="hasPayrolls" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <PayrollCard
          v-for="payroll in payrolls"
          :key="payroll.id"
          :payroll="payroll"
          @click="openPayroll(payroll.id)"
        />
      </div>

      <!-- Empty State -->
      <div v-if="hasPayrolls && payrolls.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma folha encontrada</h3>
        <p class="mt-1 text-sm text-gray-500">Não há folhas de pagamento para o ano selecionado.</p>
      </div>
    </div>

    <!-- Modal Gerar Folhas por Ano -->
    <div v-if="showGenerateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 px-4">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Gerar Folhas por Ano</h3>
        <p class="text-sm text-gray-600 mb-4">Informe o ano para gerar as 12 folhas automaticamente.</p>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ano</label>
            <input
              v-model.number="modalYear"
              type="number"
              min="2020"
              max="2100"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
          <div class="flex items-center justify-end space-x-3">
            <button
              @click="closeGenerateModal"
              class="px-4 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
            >
              Cancelar
            </button>
            <button
              @click="generatePayrollsForYear"
              :disabled="isGeneratingCustom"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
            >
              <svg v-if="isGeneratingCustom" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              {{ isGeneratingCustom ? 'Gerando...' : 'Gerar Folhas' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import PayrollFilters from './components/PayrollFilters.vue'
import PayrollCard from './components/PayrollCard.vue'
import PayrollSettings from './components/PayrollSettings.vue'

const props = defineProps({
  payrolls: Array,
  selectedYear: Number,
  availableYears: Array,
  hasPayrolls: Boolean
})

const selectedYear = ref(props.selectedYear)
const isGenerating = ref(false)
const isUpdatingAll = ref(false)
const isDeletingAll = ref(false)
const showGenerateModal = ref(false)
const isGeneratingCustom = ref(false)
const modalYear = ref(props.selectedYear || new Date().getFullYear())

const handleYearChange = (year) => {
  router.get(route('payroll.index'), { year }, {
    preserveState: true,
    preserveScroll: true
  })
}

const generatePayrolls = () => {
  isGenerating.value = true
  router.post(route('payroll.generate'), { year: selectedYear.value }, {
    preserveState: false,
    onFinish: () => {
      isGenerating.value = false
    }
  })
}

const openGenerateModal = () => {
  modalYear.value = selectedYear.value || new Date().getFullYear()
  showGenerateModal.value = true
}

const closeGenerateModal = () => {
  showGenerateModal.value = false
}

const generatePayrollsForYear = () => {
  if (!modalYear.value || modalYear.value < 2020 || modalYear.value > 2100) {
    alert('Informe um ano válido entre 2020 e 2100.')
    return
  }
  isGeneratingCustom.value = true
  router.post(route('payroll.generate'), { year: modalYear.value }, {
    preserveState: false,
    onFinish: () => {
      isGeneratingCustom.value = false
      showGenerateModal.value = false
      selectedYear.value = modalYear.value
    }
  })
}

const openPayroll = (id) => {
  router.visit(route('payroll.show', id))
}

const updateAllPayrolls = () => {
  if (!selectedYear.value) {
    alert('Por favor, selecione um ano no filtro antes de atualizar as folhas.')
    return
  }

  if (!confirm(`Tem certeza que deseja atualizar todas as folhas do ano ${selectedYear.value}? Isso irá remover colaboradores desativados e adicionar novos colaboradores ativos em todas as folhas deste ano.`)) {
    return
  }
  
  isUpdatingAll.value = true
  router.post(route('payroll.update-all'), { year: selectedYear.value }, {
    preserveState: false,
    onFinish: () => {
      isUpdatingAll.value = false
    }
  })
}

const deleteAllDraftPayrolls = () => {
  if (!selectedYear.value) {
    alert('Por favor, selecione um ano no filtro antes de deletar as folhas.')
    return
  }

  if (!confirm(`⚠️ ATENÇÃO: Esta ação irá deletar TODAS as folhas do ano ${selectedYear.value} que estão com status RASCUNHO.\n\nEsta ação NÃO pode ser desfeita!\n\nTem certeza que deseja continuar?`)) {
    return
  }
  
  isDeletingAll.value = true
  router.delete(route('payroll.delete-all-draft'), {
    data: { year: selectedYear.value },
    preserveState: false,
    onFinish: () => {
      isDeletingAll.value = false
    }
  })
}
</script>

