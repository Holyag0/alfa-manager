<template>
  <div class="space-y-4">
    <div>
      <InputLabel value="Serviços Incluídos" />
      <p class="text-sm text-gray-500 mt-1">
        Selecione os serviços que fazem parte deste pacote
      </p>
    </div>

    <!-- Lista de Serviços Selecionados -->
    <div v-if="selectedServices.length > 0" class="space-y-2">
      <h4 class="text-sm font-medium text-gray-700">Serviços Selecionados ({{ selectedServices.length }})</h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
        <div
          v-for="service in selectedServices"
          :key="service.id"
          class="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-lg"
        >
          <div class="flex-1">
            <div class="text-sm font-medium text-gray-900">{{ service.name }}</div>
            <div class="text-xs text-gray-500">{{ service.category }} - {{ service.formatted_price }}</div>
          </div>
          <button
            @click="removeService(service)"
            class="ml-2 text-red-600 hover:text-red-800"
            type="button"
          >
            <XMarkIcon class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Busca de Serviços -->
    <div class="space-y-2">
      <InputLabel for="service-search" value="Adicionar Serviços" />
      <div class="relative">
        <TextInput
          id="service-search"
          v-model="searchTerm"
          type="text"
          class="w-full"
          placeholder="Buscar serviços..."
          @input="searchServices"
        />
        <div v-if="isLoading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
          <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
        </div>
      </div>
    </div>

    <!-- Resultados da Busca -->
    <div v-if="searchResults.length > 0 && searchTerm" class="space-y-2">
      <h4 class="text-sm font-medium text-gray-700">Resultados da Busca</h4>
      <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg">
        <div
          v-for="service in searchResults"
          :key="service.id"
          @click="addService(service)"
          class="flex items-center justify-between p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
        >
          <div class="flex-1">
            <div class="text-sm font-medium text-gray-900">{{ service.name }}</div>
            <div class="text-xs text-gray-500">{{ service.category }} - {{ service.formatted_price }}</div>
          </div>
          <PlusIcon class="w-4 h-4 text-green-600" />
        </div>
      </div>
    </div>

    <!-- Mensagem quando não há resultados -->
    <div v-else-if="searchTerm && !isLoading && searchResults.length === 0" class="text-sm text-gray-500">
      Nenhum serviço encontrado para "{{ searchTerm }}"
    </div>

    <!-- Resumo do Pacote -->
    <div v-if="selectedServices.length > 0" class="mt-4 p-4 bg-gray-50 rounded-lg">
      <h4 class="text-sm font-medium text-gray-700 mb-2">Resumo do Pacote</h4>
      <div class="space-y-1 text-sm">
        <div class="flex justify-between">
          <span>Total de serviços:</span>
          <span class="font-medium">{{ selectedServices.length }}</span>
        </div>
        <div class="flex justify-between">
          <span>Valor total dos serviços:</span>
          <span class="font-medium">{{ totalServicesValue }}</span>
        </div>
        <div class="flex justify-between">
          <span>Preço do pacote:</span>
          <span class="font-medium">{{ packagePrice }}</span>
        </div>
        <div v-if="discount > 0" class="flex justify-between text-green-600">
          <span>Desconto:</span>
          <span class="font-medium">{{ discountPercentage }}%</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { XMarkIcon, PlusIcon } from '@heroicons/vue/20/solid'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import { debounce } from 'lodash'

const props = defineProps({
  services: {
    type: Array,
    default: () => []
  },
  selectedServiceIds: {
    type: Array,
    default: () => []
  },
  packagePrice: {
    type: [String, Number],
    default: 0
  }
})

const emit = defineEmits(['update:selectedServiceIds', 'update:packagePrice'])

const searchTerm = ref('')
const searchResults = ref([])
const isLoading = ref(false)

// Computed properties
const selectedServices = computed(() => {
  return props.services.filter(service => 
    props.selectedServiceIds.includes(service.id)
  )
})

const totalServicesValue = computed(() => {
  const total = selectedServices.value.reduce((sum, service) => sum + parseFloat(service.price), 0)
  return `R$ ${total.toFixed(2).replace('.', ',')}`
})

const discount = computed(() => {
  const total = selectedServices.value.reduce((sum, service) => sum + parseFloat(service.price), 0)
  const packagePrice = parseFloat(props.packagePrice) || 0
  return Math.max(0, total - packagePrice)
})

const discountPercentage = computed(() => {
  const total = selectedServices.value.reduce((sum, service) => sum + parseFloat(service.price), 0)
  if (total === 0) return 0
  return Math.round((discount.value / total) * 100)
})

// Methods
const searchServices = debounce(async () => {
  if (!searchTerm.value.trim()) {
    searchResults.value = []
    return
  }

  isLoading.value = true
  try {
    // Simular busca - em produção, isso seria uma chamada API
    const results = props.services.filter(service => 
      service.name.toLowerCase().includes(searchTerm.value.toLowerCase()) &&
      !props.selectedServiceIds.includes(service.id)
    )
    searchResults.value = results.slice(0, 10) // Limitar a 10 resultados
  } catch (error) {
    console.error('Erro na busca:', error)
    searchResults.value = []
  } finally {
    isLoading.value = false
  }
}, 300)

const addService = (service) => {
  if (!props.selectedServiceIds.includes(service.id)) {
    const newSelectedIds = [...props.selectedServiceIds, service.id]
    emit('update:selectedServiceIds', newSelectedIds)
  }
  searchTerm.value = ''
  searchResults.value = []
}

const removeService = (service) => {
  const newSelectedIds = props.selectedServiceIds.filter(id => id !== service.id)
  emit('update:selectedServiceIds', newSelectedIds)
}

// Watch for changes
watch(() => props.selectedServiceIds, (newIds) => {
  // Recalcular preço sugerido baseado nos serviços selecionados
  const total = props.services
    .filter(service => newIds.includes(service.id))
    .reduce((sum, service) => sum + parseFloat(service.price), 0)
  
  // Sugerir um preço com 10% de desconto se houver mais de 1 serviço
  if (newIds.length > 1) {
    const suggestedPrice = total * 0.9
    emit('update:packagePrice', suggestedPrice.toFixed(2))
  } else {
    emit('update:packagePrice', total.toFixed(2))
  }
}, { deep: true })
</script> 