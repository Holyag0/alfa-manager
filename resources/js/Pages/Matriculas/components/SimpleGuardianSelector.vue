<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center">
      <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
      </div>
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Selecionar Responsável</h2>
        <p class="text-gray-600">Escolha o responsável pelo aluno</p>
      </div>
    </div>

    <!-- Busca -->
    <div class="bg-gray-50 rounded-xl p-6 border-2 border-dashed border-gray-300">
      <div class="flex items-center mb-4">
        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <label class="text-sm font-semibold text-gray-700">Buscar responsável</label>
      </div>
      
      <div class="relative">
        <input 
          v-model="search" 
          @input="onSearch" 
          type="text" 
          placeholder="Digite o nome ou CPF do responsável" 
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 pl-10"
        />
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
      </div>

      <!-- Resultados da Busca -->
      <div v-if="search && guardians.length" class="mt-4 bg-white rounded-lg shadow-md border border-gray-200 max-h-60 overflow-y-auto">
        <div 
          v-for="guardian in guardians" 
          :key="guardian.id"
          :class="[
            'px-4 py-3 cursor-pointer border-b border-gray-100 last:border-b-0 transition-all duration-200 flex items-center',
            selectedGuardian && selectedGuardian.id === guardian.id 
              ? 'bg-blue-50 border-l-4 border-l-blue-500' 
              : 'hover:bg-gray-50'
          ]"
          @click="selectGuardian(guardian)"
        >
          <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
          <div class="flex-1">
            <div class="font-semibold text-gray-900">{{ guardian.name }}</div>
            <div class="text-sm text-gray-500">
              CPF: {{ guardian.cpf || 'Não informado' }} | 
              Parentesco: {{ formatRelationship(guardian.relationship) }}
            </div>
          </div>
          <div v-if="selectedGuardian && selectedGuardian.id === guardian.id" class="text-blue-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Estados da Busca -->
      <div v-if="search && !guardians.length && !loading" class="mt-4 text-center py-4">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.29-1.009-5.824-2.563M15 9.34c-1.168-.91-2.544-1.34-4-1.34s-2.832.43-4 1.34"/>
        </svg>
        <p class="text-sm text-gray-500">Nenhum responsável encontrado</p>
        <p class="text-xs text-gray-400 mt-1">Tente buscar por nome ou CPF</p>
      </div>

      <div v-if="loading" class="mt-4 flex items-center justify-center py-4">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mr-2"></div>
        <span class="text-sm text-blue-600">Buscando...</span>
      </div>

      <!-- Responsável Selecionado -->
      <div v-if="selectedGuardian" class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
          <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-1">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <div class="flex-1">
            <h4 class="font-semibold text-blue-900 mb-1">Responsável selecionado</h4>
            <p class="text-sm text-blue-800 font-medium">{{ selectedGuardian.name }}</p>
            <p class="text-sm text-blue-700">CPF: {{ selectedGuardian.cpf || 'Não informado' }}</p>
            <p class="text-sm text-blue-700">Parentesco: {{ formatRelationship(selectedGuardian.relationship) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Instruções -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
      <div class="flex items-start">
        <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-0.5">
          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <h4 class="text-sm font-medium text-blue-900 mb-1">Como buscar</h4>
          <ul class="text-sm text-blue-800 space-y-1">
            <li>• Digite o nome completo ou parcial do responsável</li>
            <li>• Use o CPF (com ou sem formatação)</li>
            <li>• Clique no responsável desejado para selecioná-lo</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { debounce } from 'lodash';
import axios from 'axios';

const props = defineProps({
  selected: {
    type: Object,
    default: null
  },
  studentId: {
    type: Number,
    default: null
  }
});

const emit = defineEmits(['selected', 'error']);

const guardians = ref([]);
const search = ref('');
const loading = ref(false);
const selectedGuardian = ref(props.selected);

const fetchGuardians = debounce(async () => {
  if (!search.value || search.value.length < 2) {
    guardians.value = [];
    return;
  }
  
  loading.value = true;
  try {
    let response;
    // Se studentId existe, buscar responsáveis não vinculados a este aluno
    if (props.studentId) {
      response = await axios.get(`/api/students/${props.studentId}/guardians/search-not-linked`, {
        params: { q: search.value }
      });
    } else {
      response = await axios.get('/api/guardians', {
        params: { q: search.value }
      });
    }
    
    guardians.value = response.data || [];
  } catch (error) {
    console.error('Erro na busca:', error);
    emit('error', 'Erro ao buscar responsáveis. Tente novamente.');
  } finally {
    loading.value = false;
  }
}, 400);

function onSearch() {
  fetchGuardians();
}

function selectGuardian(guardian) {
  selectedGuardian.value = guardian;
  emit('selected', guardian);
}

function formatRelationship(relationship) {
  const relationships = {
    'pai': 'Pai',
    'mae': 'Mãe',
    'avo': 'Avô',
    'avo_feminino': 'Avó',
    'tio': 'Tio',
    'tia': 'Tia',
    'padrasto': 'Padrasto',
    'madrasta': 'Madrasta',
    'tutor': 'Tutor Legal',
    'outro': 'Outro'
  };
  
  return relationships[relationship] || relationship || 'Não informado';
}
</script>
