<template>
  <div class="min-h-screen ">
    <!-- Header Section -->
    <div class="border-b border-cyan-500">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6 md:flex md:items-center md:justify-between">
          <div class="flex-1 min-w-0">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h1 class="text-2xl font-bold text-sky-700">Matrículas</h1>
                <strong class="text-sm text-gray-500">Gerencie as matrículas dos alunos</strong>
              </div>
            </div>
          </div>
          <div class="mt-4 flex md:mt-0 md:ml-4">
            <Link 
              :href="route('matriculas.create')" 
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
              Nova Matrícula
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Filters Card -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center">
            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900">Filtros</h3>
          </div>
        </div>
        
        <form @submit.prevent="applyFilters" class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Filtro Aluno -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">
                Aluno
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                </div>
                <input 
                  v-model="filters.student" 
                  @input="onStudentInput"
                  type="text" 
                  placeholder="Nome do aluno" 
                  class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors duration-200"
                />
              </div>
            </div>

            <!-- Filtro Turma -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">
                Turma
              </label>
              <div class="relative">
                <select 
                  v-model="filters.classroom_id" 
                  class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors duration-200 appearance-none bg-white"
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
                Status
              </label>
              <div class="relative">
                <select 
                  v-model="filters.status" 
                  class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-colors duration-200 appearance-none bg-white"
                >
                  <option value="">Todos os status</option>
                  <option value="active">
                    <span class="flex items-center">
                      Ativo
                    </span>
                  </option>
                  <option value="pending">Pendente</option>
                  <option value="cancelled">Cancelado</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Ações -->
            <div class="space-y-2">
              <label class="block text-sm font-medium text-transparent">
                Ações
              </label>
              <div class="flex space-x-3">
                <button 
                  type="submit" 
                  class="flex-1 inline-flex justify-center items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                  </svg>
                  Filtrar
                </button>
                <button 
                  type="button" 
                  @click="clearFilters" 
                  class="inline-flex items-center px-3 py-2.5 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>

      <!-- Results Section -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
              <h3 class="text-lg font-medium text-gray-900">
                Resultados
              </h3>
              <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                <template v-if="enrollments.total">
                  {{ enrollments.from }}-{{ enrollments.to }} de {{ enrollments.total }} matrículas
                </template>
                <template v-else>
                  {{ enrollments.data?.length || 0 }} matrículas
                </template>
              </span>
            </div>
          </div>
        </div>
        
        <!-- Lista de Matrículas -->
        <div class="overflow-hidden">
          <div class="px-6 py-4">
            <ListEnrollments
              :enrollments="enrollments.data"
              @cancel="handleCancel"
              @change-classroom="handleChangeClassroom"
            />
          </div>
        </div>
      </div>
      <Pagination :links="enrollments.links" />
      <!-- Loading State (exemplo de como pode ser implementado) -->
      <div v-if="false" class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <div class="flex items-center justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <span class="ml-3 text-gray-600">Carregando matrículas...</span>
        </div>
      </div>

      <!-- Empty State (exemplo de como pode ser implementado) -->
      <div v-if="false" class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhuma matrícula encontrada</h3>
        <p class="mt-2 text-gray-500">Tente ajustar os filtros ou criar uma nova matrícula.</p>
        <div class="mt-6">
          <Link 
            :href="route('matriculas.create')" 
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200"
          >
            Nova Matrícula
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import ListEnrollments from './components/ListEnrollments.vue';
import Pagination from '@/Shared/Pagination.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
  enrollments: {
    type: Object,
    default: () => ({ data: [], links: [] })
  },
  classrooms: {
    type: Array,
    default: () => []
  }
});

const filters = ref({
  student: '',
  classroom_id: '',
  status: ''
});

const applyFilters = debounce(() => {
  router.get(route('matriculas.index'), {
    student: filters.value.student,
    classroom_id: filters.value.classroom_id,
    status: filters.value.status
  }, {
    preserveState: true,
    replace: true
  });
}, 400);

function onStudentInput() {
  applyFilters();
}

function clearFilters() {
  filters.value = { student: '', classroom_id: '', status: '' };
  applyFilters();
}

function handleCancel(enrollment) {
  // Implementar modal de confirmação de cancelamento
}

function handleChangeClassroom(enrollment) {
  // Implementar modal de troca de turma
}
</script>