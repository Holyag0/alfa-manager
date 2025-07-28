<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"/>
              </svg>
            </div>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Filtros Avançados</h3>
            <p class="text-sm text-gray-600">Filtre as matrículas por aluno, turma, status ou processo</p>
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

    <!-- Filters Content -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 max-h-0"
      enter-to-class="opacity-100 max-h-96"
      leave-active-class="transition-all duration-300 ease-in"
      leave-from-class="opacity-100 max-h-96"
      leave-to-class="opacity-0 max-h-0"
    >
      <div v-show="isExpanded" class="overflow-hidden">
        <form @submit.prevent="handleApplyFilters" class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Filtro Aluno -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">
                <div class="flex items-center space-x-2">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                  <span>Aluno</span>
                </div>
              </label>
              <div class="relative">
                <input 
                  v-model="localFilters.student"
                  @input="handleStudentInput"
                  type="text" 
                  placeholder="Nome do aluno..." 
                  class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all duration-200 bg-gray-50 focus:bg-white"
                />
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Filtro Turma -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">
                <div class="flex items-center space-x-2">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                  </svg>
                  <span>Turma</span>
                </div>
              </label>
              <div class="relative">
                <select 
                  v-model="localFilters.classroom_id"
                  class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all duration-200 appearance-none bg-gray-50 focus:bg-white"
                >
                  <option value="">Todas as turmas</option>
                  <option v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                    {{ classroom.name }}
                  </option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Filtro Status -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">
                <div class="flex items-center space-x-2">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <span>Status</span>
                </div>
              </label>
              <div class="relative">
                <select 
                  v-model="localFilters.status"
                  class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all duration-200 appearance-none bg-gray-50 focus:bg-white"
                >
                  <option value="">Todos os status</option>
                  <option value="active">✅ Ativo</option>
                  <option value="pending">⏳ Pendente</option>
                  <option value="cancelled">❌ Cancelado</option>
                  <option value="inactive">🔒 Inativo</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Filtro Processo -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">
                <div class="flex items-center space-x-2">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                  </svg>
                  <span>Processo</span>
                </div>
              </label>
              <div class="relative">
                <select 
                  v-model="localFilters.process"
                  class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all duration-200 appearance-none bg-gray-50 focus:bg-white"
                >
                  <option value="">Todos os processos</option>
                  <option value="reserva">🎫 Reserva</option>
                  <option value="aguardando_pagamento">💳 Aguardando Pagamento</option>
                  <option value="aguardando_documentos">📄 Aguardando Documentos</option>
                  <option value="completa">✅ Completa</option>
                  <option value="renovacao">🔄 Renovação</option>
                  <option value="desistencia">🚫 Desistência</option>
                  <option value="transferencia">↗️ Transferência</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </div>
              </div>
            </div>

          </div>

          <!-- Actions -->
          <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
            <div class="flex items-center space-x-4">
              <div class="text-sm text-gray-500">
                {{ activeFiltersCount }} filtro{{ activeFiltersCount !== 1 ? 's' : '' }} ativo{{ activeFiltersCount !== 1 ? 's' : '' }}
              </div>
              <button 
                v-if="hasActiveFilters"
                @click="clearAllFilters"
                type="button"
                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-200"
              >
                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Limpar Filtros
              </button>
            </div>
            
            <div class="flex items-center space-x-3">
              <button 
                @click="isExpanded = false"
                type="button"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
              >
                Fechar
              </button>
              <button 
                type="submit"
                class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Aplicar Filtros
              </button>
            </div>
          </div>
        </form>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { debounce } from 'lodash'

const props = defineProps({
  filters: {
    type: Object,
    required: true
  },
  classrooms: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:filters', 'apply-filters', 'student-input'])

const isExpanded = ref(false)
const localFilters = ref({ ...props.filters })

// Computed properties
const activeFiltersCount = computed(() => {
  return Object.values(localFilters.value).filter(value => value && value.toString().trim() !== '').length
})

const hasActiveFilters = computed(() => activeFiltersCount.value > 0)

// Methods
const handleApplyFilters = () => {
  emit('update:filters', { ...localFilters.value })
  emit('apply-filters')
}

const handleStudentInput = debounce(() => {
  emit('student-input', localFilters.value.student)
}, 300)

const clearAllFilters = () => {
  localFilters.value = {
    student: '',
    classroom_id: '',
    status: '',
    process: ''
  }
  handleApplyFilters()
}

// Watch for external filter changes
watch(() => props.filters, (newFilters) => {
  localFilters.value = { ...newFilters }
}, { deep: true })
</script>

<style scoped>
.rotate-180 {
  transform: rotate(180deg);
}
</style> 