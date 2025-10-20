<template>
  <!-- Modal backdrop -->
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeModal">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white" @click.stop>
      <!-- Modal header -->
      <div class="flex items-center justify-between pb-4 border-b border-gray-200">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Adicionar Serviços</h3>
          <p class="text-sm text-gray-600">Escolha serviços adicionais para esta matrícula</p>
        </div>
        <button
          @click="closeModal"
          class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Modal content -->
      <div class="mt-6">
        <!-- Filtros -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
            <input
              v-model="serviceFilters.search"
              type="text"
              placeholder="Nome do serviço..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Categoria</label>
            <select
              v-model="serviceFilters.category"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Todas as categorias</option>
              <option v-for="category in availableCategories" :key="category" :value="category">
                {{ category }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="serviceFilters.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Todos os status</option>
              <option value="active">Ativo</option>
              <option value="inactive">Inativo</option>
            </select>
          </div>
        </div>

        <!-- Lista de Serviços -->
        <div class="space-y-3 max-h-96 overflow-y-auto border border-gray-200 rounded-lg p-4">
          <div
            v-for="service in filteredServices"
            :key="service.id"
            class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
          >
            <div class="flex-1">
              <div class="flex items-center space-x-3">
                <input
                  :id="'service-' + service.id"
                  v-model="selectedServices"
                  :value="service.id"
                  type="checkbox"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <div>
                  <label :for="'service-' + service.id" class="text-sm font-medium text-gray-900 cursor-pointer">
                    {{ service.name }}
                  </label>
                  <p class="text-sm text-gray-500">{{ service.description || 'Sem descrição' }}</p>
                </div>
              </div>
            </div>
            <div class="text-right">
              <span class="text-lg font-semibold text-green-600">{{ service.formatted_price }}</span>
              <div class="flex items-center space-x-2 mt-1">
                <span
                  :class="[
                    'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                    service.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ service.status === 'active' ? 'Ativo' : 'Inativo' }}
                </span>
                <span
                  :class="[
                    'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                    service.is_classroom_linked ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ service.is_classroom_linked ? 'Vinculado' : 'Global' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Estado vazio quando não há serviços -->
          <div v-if="filteredServices.length === 0" class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum serviço encontrado</h3>
            <p class="mt-1 text-sm text-gray-500">Tente ajustar os filtros de busca.</p>
          </div>
        </div>

        <!-- Botões de Ação -->
        <div class="mt-6 flex justify-end space-x-3">
          <button
            @click="clearServiceSelection"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Limpar Seleção
          </button>
          <button
            @click="addSelectedServices"
            :disabled="selectedServices.length === 0 || addingServices"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ addingServices ? 'Adicionando...' : `Adicionar ${selectedServices.length} Serviço(s)` }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  enrollment: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'services-added'])

// Variáveis para seleção de serviços
const services = ref([])
const selectedServices = ref([])
const addingServices = ref(false)
const serviceFilters = ref({
  search: '',
  category: '',
  status: ''
})

// Computed properties para serviços
const availableCategories = computed(() => {
  const categories = [...new Set(services.value.map(service => service.category))]
  return categories.sort()
})

const filteredServices = computed(() => {
  return services.value.filter(service => {
    const matchesSearch = !serviceFilters.value.search || 
      service.name.toLowerCase().includes(serviceFilters.value.search.toLowerCase()) ||
      (service.description && service.description.toLowerCase().includes(serviceFilters.value.search.toLowerCase()))
    
    const matchesCategory = !serviceFilters.value.category || 
      service.category === serviceFilters.value.category
    
    const matchesStatus = !serviceFilters.value.status || 
      service.status === serviceFilters.value.status
    
    return matchesSearch && matchesCategory && matchesStatus
  })
})

// Métodos
const closeModal = () => {
  emit('close')
}

const clearServiceSelection = () => {
  selectedServices.value = []
}

const addSelectedServices = async () => {
  if (selectedServices.value.length === 0) return
  
  addingServices.value = true
  try {
    // Buscar dados dos serviços selecionados
    const selectedServicesData = services.value.filter(service => 
      selectedServices.value.includes(service.id)
    )
    
    console.log('Serviços selecionados:', selectedServicesData)
    
    // Emitir evento para o componente pai com os dados completos dos serviços
    emit('services-added', selectedServicesData)
    
    // Limpar seleção
    clearServiceSelection()
    
    // Fechar modal
    closeModal()
    
    // Mostrar mensagem de sucesso
    alert(`${selectedServicesData.length} serviço(s) adicionado(s) ao carrinho!`)
    
  } catch (error) {
    console.error('Erro ao adicionar serviços:', error)
    alert(`Erro ao adicionar serviços: ${error.message}`)
  } finally {
    addingServices.value = false
  }
}

// Carregar serviços disponíveis
const loadServices = async () => {
  try {
    const response = await fetch('/comercial/services/api')
    if (response.ok) {
      const data = await response.json()
      services.value = data.data || data
    }
  } catch (error) {
    console.error('Erro ao carregar serviços:', error)
  }
}

// Resetar filtros e seleção quando o modal abrir
watch(() => props.show, (newValue) => {
  if (newValue) {
    // Resetar filtros
    serviceFilters.value = {
      search: '',
      category: '',
      status: ''
    }
    // Limpar seleção
    selectedServices.value = []
  }
})

onMounted(() => {
  loadServices()
})
</script>
