<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center">
      <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
      </div>
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Selecionar Turma</h2>
        <p class="text-gray-600">Escolha a turma para matricular o aluno</p>
      </div>
    </div>

    <!-- Filtros -->
    <div class="bg-gray-50 rounded-xl p-4">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-700">Filtrar turmas</h3>
        <button 
          @click="clearFilters"
          class="text-xs text-blue-600 hover:text-blue-800"
        >
          Limpar filtros
        </button>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Ano</label>
          <select 
            v-model="filters.year" 
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
          >
            <option value="">Todos os anos</option>
            <option v-for="year in availableYears" :key="year" :value="year">
              {{ year }}
            </option>
          </select>
        </div>
        
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Turno</label>
          <select 
            v-model="filters.shift" 
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
          >
            <option value="">Todos os turnos</option>
            <option value="matutino">Matutino</option>
            <option value="vespertino">Vespertino</option>
            <option value="noturno">Noturno</option>
            <option value="integral">Integral</option>
          </select>
        </div>
        
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
          <select 
            v-model="filters.hasVacancies" 
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
          >
            <option value="">Todas</option>
            <option value="true">Com vagas</option>
            <option value="false">Sem vagas</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Lista de Turmas -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div 
        v-for="classroom in filteredClassrooms" 
        :key="classroom.id"
        :class="[
          'border-2 rounded-xl p-4 cursor-pointer transition-all duration-200',
          selectedClassroom && selectedClassroom.id === classroom.id
            ? 'border-purple-500 bg-purple-50'
            : classroom.vacancies_available > 0
              ? 'border-gray-200 hover:border-purple-300 hover:bg-purple-25'
              : 'border-red-200 bg-red-25 opacity-60 cursor-not-allowed'
        ]"
        @click="selectClassroom(classroom)"
      >
        <!-- Header da turma -->
        <div class="flex items-start justify-between mb-3">
          <div class="flex-1">
            <h3 class="font-semibold text-gray-900">{{ classroom.name }}</h3>
            <p class="text-sm text-gray-600">{{ classroom.year }} - {{ formatShift(classroom.shift) }}</p>
          </div>
          
          <!-- Status de vagas -->
          <div class="flex items-center">
            <div :class="[
              'px-2 py-1 rounded-full text-xs font-medium',
              classroom.vacancies_available > 0
                ? 'bg-green-100 text-green-800'
                : 'bg-red-100 text-red-800'
            ]">
              {{ classroom.vacancies_available }} vagas
            </div>
          </div>
        </div>

        <!-- Informações da turma -->
        <div class="space-y-2">
          <div class="flex items-center text-sm text-gray-600">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            {{ classroom.total_students }} alunos matriculados
          </div>
          
          <div class="flex items-center text-sm text-gray-600">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Capacidade: {{ classroom.vacancies }} vagas
          </div>
        </div>

        <!-- Indicador de seleção -->
        <div v-if="selectedClassroom && selectedClassroom.id === classroom.id" class="mt-3 flex items-center text-purple-600">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          <span class="text-sm font-medium">Selecionada</span>
        </div>
      </div>
    </div>

    <!-- Mensagem quando não há turmas -->
    <div v-if="filteredClassrooms.length === 0" class="text-center py-8">
      <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
      </svg>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma turma encontrada</h3>
      <p class="text-gray-600">Tente ajustar os filtros para encontrar turmas disponíveis.</p>
    </div>

    <!-- Turma Selecionada -->
    <div v-if="selectedClassroom" class="bg-purple-50 border border-purple-200 rounded-lg p-4">
      <div class="flex items-start">
        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3 mt-1">
          <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <div class="flex-1">
          <h4 class="font-semibold text-purple-900 mb-1">Turma selecionada</h4>
          <p class="text-sm text-purple-800 font-medium">{{ selectedClassroom.name }}</p>
          <p class="text-sm text-purple-700">{{ selectedClassroom.year }} - {{ formatShift(selectedClassroom.shift) }}</p>
          <p class="text-sm text-purple-700">{{ selectedClassroom.vacancies_available }} vagas disponíveis</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
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

const classrooms = ref([]);
const selectedClassroom = ref(props.selected);

const filters = ref({
  year: '',
  shift: '',
  hasVacancies: ''
});

const availableYears = computed(() => {
  const years = [...new Set(classrooms.value.map(c => c.year))];
  return years.sort();
});

const filteredClassrooms = computed(() => {
  let filtered = [...classrooms.value];

  if (filters.value.year) {
    filtered = filtered.filter(c => c.year === filters.value.year);
  }

  if (filters.value.shift) {
    filtered = filtered.filter(c => c.shift === filters.value.shift);
  }

  if (filters.value.hasVacancies === 'true') {
    filtered = filtered.filter(c => c.vacancies_available > 0);
  } else if (filters.value.hasVacancies === 'false') {
    filtered = filtered.filter(c => c.vacancies_available === 0);
  }

  return filtered;
});

onMounted(async () => {
  await loadClassrooms();
});

async function loadClassrooms() {
  try {
    const response = await axios.get('/api/classrooms');
    const rawClassrooms = response.data || [];
    
    // Calcular vagas disponíveis para cada turma
    classrooms.value = await Promise.all(rawClassrooms.map(async (classroom) => {
      try {
        const enrollmentResponse = await axios.get(`/api/classrooms/${classroom.id}/enrollments`);
        const enrolledCount = enrollmentResponse.data?.count || 0;
        
        return {
          ...classroom,
          total_students: enrolledCount,
          vacancies_available: Math.max(0, classroom.vacancies - enrolledCount)
        };
      } catch (error) {
        // Se não conseguir buscar matrículas, assumir que todas as vagas estão disponíveis
        return {
          ...classroom,
          total_students: 0,
          vacancies_available: classroom.vacancies
        };
      }
    }));
  } catch (error) {
    console.error('Erro ao carregar turmas:', error);
    emit('error', 'Erro ao carregar turmas. Tente novamente.');
  }
}

function selectClassroom(classroom) {
  if (classroom.vacancies_available > 0) {
    selectedClassroom.value = classroom;
    emit('selected', classroom);
  }
}

function applyFilters() {
  // Os filtros são aplicados automaticamente via computed property
}

function clearFilters() {
  filters.value = {
    year: '',
    shift: '',
    hasVacancies: ''
  };
}

function formatShift(shift) {
  const shifts = {
    'matutino': 'Matutino',
    'vespertino': 'Vespertino',
    'noturno': 'Noturno',
    'integral': 'Integral'
  };
  
  return shifts[shift] || shift || 'Não informado';
}
</script>
