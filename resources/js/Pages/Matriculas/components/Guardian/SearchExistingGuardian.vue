<template>
  <div>
    <!-- Campo de busca com autocomplete -->
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Buscar Responsável</label>
      <input
        v-model="searchTerm"
        @input="searchGuardians"
        type="text"
        placeholder="Digite o nome do responsável..."
        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      >
      
      <!-- Lista de resultados -->
      <div v-if="searchResults.length > 0" class="mt-2 border border-gray-200 rounded-lg max-h-40 overflow-y-auto">
        <button
          v-for="result in searchResults"
          :key="result.id"
          @click="selectGuardian(result)"
          class="w-full text-left px-3 py-2 hover:bg-gray-50 border-b border-gray-100 last:border-b-0"
        >
          <div class="font-medium">{{ result.name }}</div>
          <div class="text-sm text-gray-600">{{ result.email }}</div>
        </button>
      </div>
    </div>

    <!-- Responsável selecionado -->
    <div v-if="selectedGuardian" class="mb-4 p-3 bg-blue-50 rounded-lg">
      <div class="font-medium text-blue-900">{{ selectedGuardian.name }}</div>
      <div class="text-sm text-blue-700">{{ selectedGuardian.email }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { debounce } from 'lodash'

const props = defineProps({
  enrollment: Object
})

const emit = defineEmits(['guardian-selected'])

// Estados
const searchTerm = ref('')
const searchResults = ref([])
const selectedGuardian = ref(null)

// Buscar responsáveis não vinculados (com debounce)
const searchGuardians = debounce(async () => {
  if (searchTerm.value.length < 2) {
    searchResults.value = []
    return
  }
  
  try {
    const response = await axios.get(route('matriculas.guardians.search-not-linked', props.enrollment.id), {
      params: { q: searchTerm.value }
    })
    searchResults.value = response.data
  } catch (error) {
    console.error('Erro na busca:', error)
    searchResults.value = []
  }
}, 300)

// Selecionar responsável da lista
const selectGuardian = (guardian) => {
  selectedGuardian.value = guardian
  searchResults.value = []
  searchTerm.value = guardian.name
  emit('guardian-selected', guardian)
}
</script> 