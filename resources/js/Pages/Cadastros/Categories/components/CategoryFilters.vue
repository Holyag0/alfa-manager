<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-cyan-50 border-b border-gray-200">
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
            <p class="text-sm text-gray-600">Filtre as categorias por nome, tipo ou status</p>
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
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- Filtro Nome -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">
                <div class="flex items-center space-x-2">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                  </svg>
                  <span>Nome</span>
                </div>
              </label>
              <div class="relative">
                <input 
                  v-model="localFilters.name"
                  @input="handleNameInput"
                  type="text" 
                  placeholder="Nome da categoria..." 
                  class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all duration-200 bg-gray-50 focus:bg-white"
                />
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Filtro Tipo -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">
                <div class="flex items-center space-x-2">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                  </svg>
                  <span>Tipo</span>
                </div>
              </label>
              <div class="relative">
                <select 
                  v-model="localFilters.type"
                  class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all duration-200 appearance-none bg-gray-50 focus:bg-white"
                >
                  <option value="">Todos os tipos</option>
                  <option v-for="(label, value) in types" :key="value" :value="value">
                    {{ label }}
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
                  v-model="localFilters.is_active"
                  class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all duration-200 appearance-none bg-gray-50 focus:bg-white"
                >
                  <option value="">Todos os status</option>
                  <option :value="true">✅ Ativo</option>
                  <option :value="false">🔒 Inativo</option>
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
                class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-cyan-600 rounded-lg hover:from-blue-700 hover:to-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
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
  types: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:filters', 'search', 'clear'])

const isExpanded = ref(false)
const localFilters = ref({ ...props.filters })

// Computed properties
const activeFiltersCount = computed(() => {
  return Object.values(localFilters.value).filter(value => {
    if (value === null || value === undefined) return false
    if (typeof value === 'boolean') return true
    return value.toString().trim() !== ''
  }).length
})

const hasActiveFilters = computed(() => activeFiltersCount.value > 0)

// Methods
const handleApplyFilters = () => {
  emit('update:filters', { ...localFilters.value })
  emit('search', localFilters.value)
}

const handleNameInput = debounce(() => {
  emit('search', localFilters.value)
}, 300)

const clearAllFilters = () => {
  localFilters.value = {
    name: '',
    type: '',
    is_active: ''
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
