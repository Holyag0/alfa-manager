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
            <p class="text-sm text-gray-600">Selecione o ano para visualizar as folhas</p>
          </div>
        </div>
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
      enter-to-class="opacity-100 max-h-40"
      leave-active-class="transition-all duration-300 ease-in"
      leave-from-class="opacity-100 max-h-40"
      leave-to-class="opacity-0 max-h-0"
    >
      <div v-show="isExpanded" class="overflow-hidden">
        <form @submit.prevent class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">
                <div class="flex items-center space-x-2">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"/>
                  </svg>
                  <span>Ano</span>
                </div>
              </label>
              <div class="relative">
                <select
                  v-model="localYear"
                  @change="handleYearChange"
                  class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all duration-200 appearance-none bg-gray-50 focus:bg-white"
                >
                  <option v-for="year in availableYears" :key="year" :value="year">
                    {{ year }}
                  </option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  selectedYear: Number,
  availableYears: Array
})

const emit = defineEmits(['update:selectedYear', 'year-changed'])

const isExpanded = ref(false)
const localYear = ref(props.selectedYear)

const handleYearChange = (event) => {
  const year = parseInt(event.target.value)
  localYear.value = year
  emit('update:selectedYear', year)
  emit('year-changed', year)
}

watch(() => props.selectedYear, (newVal) => {
  localYear.value = newVal
})
</script>

<style scoped>
.rotate-180 {
  transform: rotate(180deg);
}
</style>

