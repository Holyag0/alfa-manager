<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center">
      <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4">
        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
        </svg>
      </div>
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Selecionar Aluno</h2>
        <p class="text-gray-600">Busque um aluno existente para matricular</p>
      </div>
    </div>

    <!-- Busca -->
    <div class="bg-gray-50 rounded-xl p-6 border-2 border-dashed border-gray-300">
      <div class="flex items-center mb-4">
        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <label class="text-sm font-semibold text-gray-700">Buscar aluno</label>
      </div>
      
      <div class="relative">
        <input 
          v-model="search" 
          @input="onSearch" 
          type="text" 
          placeholder="Digite o nome ou CPF do aluno" 
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 pl-10"
        />
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
      </div>

      <!-- Resultados da Busca -->
      <div v-if="search && students.length" class="mt-4 bg-white rounded-lg shadow-md border border-gray-200 max-h-60 overflow-y-auto">
        <div 
          v-for="student in students" 
          :key="student.id"
          :class="[
            'px-4 py-3 cursor-pointer border-b border-gray-100 last:border-b-0 transition-all duration-200 flex items-center',
            selectedStudent && selectedStudent.id === student.id 
              ? 'bg-green-50 border-l-4 border-l-green-500' 
              : 'hover:bg-gray-50'
          ]"
          @click="selectStudent(student)"
        >
          <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
            </svg>
          </div>
          <div class="flex-1">
            <div class="font-semibold text-gray-900">{{ student.name }}</div>
            <div class="text-sm text-gray-500">
              CPF: {{ student.cpf || 'Não informado' }} | 
              Nascimento: {{ formatDate(student.birth_date) }}
            </div>
          </div>
          <div v-if="selectedStudent && selectedStudent.id === student.id" class="text-green-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Estados da Busca -->
      <div v-if="search && !students.length && !loading" class="mt-4 text-center py-4">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
        </svg>
        <p class="text-sm text-gray-500">Nenhum aluno encontrado</p>
        <p class="text-xs text-gray-400 mt-1">Tente buscar por nome ou CPF</p>
      </div>

      <div v-if="loading" class="mt-4 flex items-center justify-center py-4">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-600 mr-2"></div>
        <span class="text-sm text-green-600">Buscando...</span>
      </div>

      <!-- Aluno Selecionado -->
      <div v-if="selectedStudent" class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-start">
          <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3 mt-1">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <div class="flex-1">
            <h4 class="font-semibold text-green-900 mb-1">Aluno selecionado</h4>
            <p class="text-sm text-green-800 font-medium">{{ selectedStudent.name }}</p>
            <p class="text-sm text-green-700">CPF: {{ selectedStudent.cpf || 'Não informado' }}</p>
            <p class="text-sm text-green-700">Nascimento: {{ formatDate(selectedStudent.birth_date) }}</p>
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
            <li>• Digite o nome completo ou parcial do aluno</li>
            <li>• Use o CPF (com ou sem formatação)</li>
            <li>• Clique no aluno desejado para selecioná-lo</li>
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
  }
});

const emit = defineEmits(['selected', 'error']);

const students = ref([]);
const search = ref('');
const loading = ref(false);
const selectedStudent = ref(props.selected);

const fetchStudents = debounce(async () => {
  if (!search.value || search.value.length < 2) {
    students.value = [];
    return;
  }
  
  loading.value = true;
  try {
    const response = await axios.get('/api/students', {
      params: { q: search.value }
    });
    students.value = response.data || [];
  } catch (error) {
    console.error('Erro na busca:', error);
    emit('error', 'Erro ao buscar alunos. Tente novamente.');
  } finally {
    loading.value = false;
  }
}, 400);

function onSearch() {
  fetchStudents();
}

function selectStudent(student) {
  selectedStudent.value = student;
  emit('selected', student);
}

function formatDate(dateString) {
  if (!dateString) return 'Não informado';
  return new Date(dateString).toLocaleDateString('pt-BR');
}
</script>
