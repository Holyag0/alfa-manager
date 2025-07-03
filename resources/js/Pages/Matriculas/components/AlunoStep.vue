<template>
  <div class="p-8">
    <!-- Header do Step -->
    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
          </svg>
        </div>
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Aluno</h2>
          <p class="text-gray-600">Busque ou cadastre o aluno para matrícula</p>
        </div>
      </div>
      <button 
        @click="$emit('back')" 
        class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Voltar
      </button>
    </div>

    <!-- Informações do Responsável -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-8">
      <div class="flex items-center">
        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
        <div>
          <p class="text-sm font-medium text-blue-900">Responsável selecionado:</p>
          <p class="text-blue-800">{{ responsavel.name }} (CPF: {{ responsavel.cpf }})</p>
        </div>
      </div>
    </div>

    <!-- Busca de Aluno -->
    <div class="mb-8">
      <div class="bg-gray-50 rounded-xl p-6 border-2 border-dashed border-gray-300 hover:border-blue-400 transition-colors duration-200">
        <div class="flex items-center mb-4">
          <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <label class="text-sm font-semibold text-gray-700">Buscar aluno existente</label>
        </div>
        
        <div class="relative">
          <input 
            v-model="search" 
            @input="onSearch" 
            type="text" 
            placeholder="Digite o nome ou CPF do aluno" 
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 pl-10"
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
                ? 'bg-blue-50 border-l-4 border-l-blue-500' 
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
              <div class="text-sm text-gray-500">CPF: {{ student.cpf }}</div>
            </div>
            <div v-if="selectedStudent && selectedStudent.id === student.id" class="text-blue-600">
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
        </div>

        <div v-if="loading" class="mt-4 flex items-center justify-center py-4">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mr-2"></div>
          <span class="text-sm text-blue-600">Buscando...</span>
        </div>

        <!-- Aluno Selecionado -->
        <div v-if="selectedStudent" class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-start">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-1">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <div class="flex-1">
              <h4 class="font-semibold text-blue-900 mb-1">Aluno selecionado</h4>
              <p class="text-sm text-blue-800">{{ selectedStudent.name }}</p>
              <p class="text-sm text-blue-700">CPF: {{ selectedStudent.cpf }}</p>
            </div>
          </div>
          <div class="mt-4 flex justify-end">
            <button 
              @click="confirmStudent" 
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
            >
              Continuar
              <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <div class="relative mb-8">
      <div class="absolute inset-0 flex items-center">
        <div class="w-full border-t border-gray-300"></div>
      </div>
      <div class="relative flex justify-center text-sm">
        <span class="px-4 bg-white text-gray-500 font-medium">ou</span>
      </div>
    </div>

    <!-- Cadastro de Novo Aluno -->
    <div class="bg-green-50 rounded-xl p-6 border-2 border-dashed border-green-300">
      <div class="flex items-center mb-4">
        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        <label class="text-sm font-semibold text-green-800">Cadastrar novo aluno</label>
      </div>
      
      <form @submit.prevent="submitNewStudent" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome completo *</label>
            <input 
              v-model="newStudent.name" 
              type="text" 
              placeholder="Nome completo do aluno" 
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
              required 
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">CPF *</label>
            <input 
              v-model="newStudent.cpf" 
              type="text" 
              placeholder="000.000.000-00" 
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
              required 
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input 
              v-model="newStudent.email" 
              type="email" 
              placeholder="email@exemplo.com" 
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
            <input 
              v-model="newStudent.phone" 
              type="text" 
              placeholder="(00) 00000-0000" 
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
            />
          </div>
        </div>
        
        <div class="flex justify-end pt-4">
          <button 
            type="submit" 
            class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Cadastrar e Continuar
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';

const props = defineProps({
  responsavel: { type: Object, required: true }
});

const emit = defineEmits(['next', 'back']);
const students = ref([]);
const search = ref('');
const loading = ref(false);
const selectedStudent = ref(null);

const fetchStudents = debounce(async () => {
  if (!search.value) {
    students.value = [];
    selectedStudent.value = null;
    return;
  }
  loading.value = true;
  const response = await fetch(`/api/students?q=${encodeURIComponent(search.value)}`);
  students.value = await response.json();
  loading.value = false;
}, 400);

function onSearch() {
  fetchStudents();
}

function selectStudent(student) {
  selectedStudent.value = student;
}

function confirmStudent() {
  if (selectedStudent.value) {
    emit('next', selectedStudent.value);
  }
}

const newStudent = ref({ name: '', cpf: '', email: '', phone: '' });

function submitNewStudent() {
  router.post(route('students.store'), newStudent.value, {
    preserveState: true,
    replace: true,
    onSuccess: (page) => {
      if (page.props.student) {
        emit('next', page.props.student);
      }
    },
    onError: (errors) => {
      emit('next', { ...newStudent.value, id: Date.now() });
    }
  });
}
</script>
