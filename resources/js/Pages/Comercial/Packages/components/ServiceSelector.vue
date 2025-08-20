<template>
  <div class="space-y-4">
    <!-- Serviços Selecionados -->
    <div>
      <h4 class="text-sm font-medium text-gray-700 mb-3">Serviços Incluídos ({{ selectedServiceIds.length }})</h4>
      
      <!-- Lista de serviços selecionados -->
      <div v-if="selectedServiceIds.length > 0" class="space-y-2 mb-4">
        <div
          v-for="serviceId in selectedServiceIds"
          :key="serviceId"
          class="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-lg"
        >
          <div class="flex-1">
            <div class="flex items-center space-x-3">
              <div
                class="w-3 h-3 rounded-full"
                :style="{ backgroundColor: getServiceById(serviceId)?.category_color || '#3B82F6' }"
              ></div>
              <div>
                <h5 class="text-sm font-medium text-gray-900">{{ getServiceById(serviceId)?.name }}</h5>
                <p class="text-xs text-gray-500">{{ getServiceById(serviceId)?.category }} - {{ getServiceById(serviceId)?.formatted_price }}</p>
              </div>
            </div>
          </div>
          <button
            @click="removeService(serviceId)"
            class="text-red-600 hover:text-red-800 p-1"
            title="Remover serviço"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Botão para abrir modal -->
      <button
        type="button"
        @click="openModal"
        class="w-full flex items-center justify-center px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-gray-400 hover:text-gray-700 transition-colors"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        {{ selectedServiceIds.length > 0 ? 'Adicionar Mais Serviços' : 'Adicionar Serviços' }}
      </button>
    </div>

    <!-- Resumo do Pacote -->
    <div v-if="selectedServiceIds.length > 0" class="p-4 bg-gray-50 rounded-lg">
      <h4 class="text-sm font-medium text-gray-700 mb-3">Resumo do Pacote</h4>
      <dl class="grid grid-cols-1 gap-2 text-sm">
        <div class="flex justify-between">
          <dt class="text-gray-500">Total de serviços:</dt>
          <dd class="font-medium">{{ selectedServiceIds.length }}</dd>
        </div>
        <div class="flex justify-between">
          <dt class="text-gray-500">Valor total dos serviços:</dt>
          <dd class="font-medium">{{ totalServicesValue }}</dd>
        </div>
        <div class="flex justify-between">
          <dt class="text-gray-500">Preço do pacote:</dt>
          <dd class="font-medium">{{ packagePriceFormatted }}</dd>
        </div>
        <div v-if="hasDiscount" class="flex justify-between text-green-600">
          <dt>Desconto:</dt>
          <dd class="font-medium">{{ discountPercentage }}%</dd>
        </div>
      </dl>
    </div>

    <!-- Modal de Seleção de Serviços -->
    <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

        <!-- Modal -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-medium text-gray-900">Selecionar Serviços</h3>
              <button
                @click="closeModal"
                class="text-gray-400 hover:text-gray-600"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Filtros -->
            <div class="mb-6">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                  <input
                    v-model="searchTerm"
                    type="text"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Nome do serviço..."
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                  <select
                    v-model="selectedCategory"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="">Todas as categorias</option>
                    <option v-for="category in availableCategories" :key="category" :value="category">
                      {{ category }}
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                  <select
                    v-model="selectedStatus"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="">Todos</option>
                    <option value="active">Ativo</option>
                    <option value="inactive">Inativo</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Lista de Serviços -->
            <div class="max-h-96 overflow-y-auto">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div
                  v-for="service in filteredServices"
                  :key="service.id"
                  class="p-3 border rounded-lg hover:bg-gray-50 transition-colors"
                  :class="{
                    'border-blue-300 bg-blue-50': isServiceSelected(service.id),
                    'border-gray-200': !isServiceSelected(service.id)
                  }"
                >
                  <div class="flex items-start space-x-3">
                    <input
                      type="checkbox"
                      :checked="isServiceSelected(service.id)"
                      @change="toggleService(service.id)"
                      class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center space-x-2 mb-1">
                        <div
                          class="w-3 h-3 rounded-full"
                          :style="{ backgroundColor: service.category_color || '#3B82F6' }"
                        ></div>
                        <h4 class="text-sm font-medium text-gray-900 truncate">{{ service.name }}</h4>
                      </div>
                      <p class="text-xs text-gray-500 mb-2">{{ service.category }}</p>
                      <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-900">{{ service.formatted_price }}</span>
                        <span
                          :class="[
                            'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                            service.status === 'active'
                              ? 'bg-green-100 text-green-800'
                              : 'bg-red-100 text-red-800'
                          ]"
                        >
                          {{ service.status === 'active' ? 'Ativo' : 'Inativo' }}
                        </span>
                      </div>
                      <p v-if="service.description" class="text-xs text-gray-500 mt-1 line-clamp-2">
                        {{ service.description }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Mensagem quando não há serviços -->
              <div v-if="filteredServices.length === 0" class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum serviço encontrado</h3>
                <p class="mt-1 text-sm text-gray-500">Tente ajustar os filtros de busca.</p>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              type="button"
              @click="closeModal"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Confirmar Seleção ({{ selectedServiceIds.length }})
            </button>
            <button
              type="button"
              @click="closeModal"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

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

// Estado do modal
const isModalOpen = ref(false)
const searchTerm = ref('')
const selectedCategory = ref('')
const selectedStatus = ref('')

// Computed properties
const availableCategories = computed(() => {
  const categories = [...new Set(props.services.map(s => s.category))]
  return categories.sort()
})

const filteredServices = computed(() => {
  return props.services.filter(service => {
    const matchesSearch = !searchTerm.value || 
      service.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      service.description?.toLowerCase().includes(searchTerm.value.toLowerCase())
    
    const matchesCategory = !selectedCategory.value || 
      service.category === selectedCategory.value
    
    const matchesStatus = !selectedStatus.value || 
      service.status === selectedStatus.value
    
    return matchesSearch && matchesCategory && matchesStatus
  })
})

const totalServicesValue = computed(() => {
  const total = props.selectedServiceIds.reduce((sum, serviceId) => {
    const service = props.services.find(s => s.id === serviceId)
    return sum + (parseFloat(service?.price) || 0)
  }, 0)
  return `R$ ${total.toFixed(2).replace('.', ',')}`
})

const packagePriceFormatted = computed(() => {
  const price = parseFloat(props.packagePrice) || 0
  return `R$ ${price.toFixed(2).replace('.', ',')}`
})

const hasDiscount = computed(() => {
  const total = props.selectedServiceIds.reduce((sum, serviceId) => {
    const service = props.services.find(s => s.id === serviceId)
    return sum + (parseFloat(service?.price) || 0)
  }, 0)
  const packagePrice = parseFloat(props.packagePrice) || 0
  return total > packagePrice
})

const discountPercentage = computed(() => {
  const total = props.selectedServiceIds.reduce((sum, serviceId) => {
    const service = props.services.find(s => s.id === serviceId)
    return sum + (parseFloat(service?.price) || 0)
  }, 0)
  const packagePrice = parseFloat(props.packagePrice) || 0
  if (total > 0) {
    return (((total - packagePrice) / total) * 100).toFixed(1)
  }
  return 0
})

// Methods
const openModal = () => {
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  // Limpar filtros ao fechar
  searchTerm.value = ''
  selectedCategory.value = ''
  selectedStatus.value = ''
}

const getServiceById = (serviceId) => {
  return props.services.find(s => s.id === serviceId)
}

const isServiceSelected = (serviceId) => {
  return props.selectedServiceIds.includes(serviceId)
}

const toggleService = (serviceId) => {
  const newSelectedIds = [...props.selectedServiceIds]
  const index = newSelectedIds.indexOf(serviceId)
  
  if (index > -1) {
    newSelectedIds.splice(index, 1)
  } else {
    newSelectedIds.push(serviceId)
  }
  
  emit('update:selectedServiceIds', newSelectedIds)
  
  // Sugerir preço com desconto se múltiplos serviços
  if (newSelectedIds.length > 1) {
    const total = newSelectedIds.reduce((sum, id) => {
      const service = props.services.find(s => s.id === id)
      return sum + (parseFloat(service?.price) || 0)
    }, 0)
    const suggestedPrice = total * 0.9 // 10% de desconto
    emit('update:packagePrice', suggestedPrice.toFixed(2))
  }
}

const removeService = (serviceId) => {
  const newSelectedIds = props.selectedServiceIds.filter(id => id !== serviceId)
  emit('update:selectedServiceIds', newSelectedIds)
}

// Watch para sugerir preço quando serviços são selecionados
watch(() => props.selectedServiceIds, (newIds) => {
  if (newIds.length > 1) {
    const total = newIds.reduce((sum, id) => {
      const service = props.services.find(s => s.id === id)
      return sum + (parseFloat(service?.price) || 0)
    }, 0)
    const suggestedPrice = total * 0.9 // 10% de desconto
    emit('update:packagePrice', suggestedPrice.toFixed(2))
  }
}, { deep: true })
</script> 