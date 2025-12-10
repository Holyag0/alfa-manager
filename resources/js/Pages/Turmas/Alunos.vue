<template>
  <div class="min-h-screen">
    <div class="border-b border-cyan-500">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6 md:flex md:items-center md:justify-between">
          <div class="flex-1 min-w-0">
            <div class="flex items-center">
              <button
                @click="goBack"
                class="mr-4 p-2 text-gray-400 hover:text-gray-600 transition-colors"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
              </button>
              <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h1 class="text-2xl font-bold text-sky-700">Alunos da Turma</h1>
                <strong class="text-sm text-gray-500" v-if="classroom">{{ classroom.name }}</strong>
              </div>
            </div>
          </div>
          <div class="mt-4 flex items-center gap-2 md:mt-0 md:ml-4">
            <button 
              @click="refreshData"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              Atualizar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="animate-pulse">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
          <div v-for="i in 8" :key="i" class="col-span-1 flex flex-col divide-y divide-gray-200 rounded-lg bg-white text-center shadow-sm">
            <div class="flex flex-1 flex-col p-8">
              <div class="mx-auto size-32 shrink-0 rounded-full bg-gray-300"></div>
              <div class="mt-6 h-4 bg-gray-200 rounded w-3/4 mx-auto"></div>
              <div class="mt-2 h-3 bg-gray-200 rounded w-1/2 mx-auto"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">
              Erro ao carregar alunos
            </h3>
            <div class="mt-2 text-sm text-red-700">
              {{ error }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Alunos Grid -->
    <div v-else class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        <li v-for="enrollment in enrollments" :key="enrollment.id" class="col-span-1 flex flex-col divide-y divide-gray-200 rounded-lg bg-white text-center shadow-sm">
          <div class="flex flex-1 flex-col p-8">
            <img 
              v-if="enrollment.student?.photo" 
              class="mx-auto size-32 shrink-0 rounded-full bg-gray-300 outline -outline-offset-1 outline-black/5 object-cover" 
              :src="`/storage/${enrollment.student.photo}`" 
              :alt="enrollment.student?.name || 'Aluno'" 
            />
            <div 
              v-else
              class="mx-auto size-32 shrink-0 rounded-full bg-gray-300 outline -outline-offset-1 outline-black/5 flex items-center justify-center text-4xl font-bold text-gray-600"
            >
              {{ enrollment.student?.name?.charAt(0)?.toUpperCase() || '?' }}
            </div>
            <h3 class="mt-6 text-sm font-medium text-gray-900">{{ enrollment.student?.name || 'Sem nome' }}</h3>
            <dl class="mt-1 flex grow flex-col justify-between">
              <dt class="sr-only">Responsável</dt>
              <dd class="text-sm text-gray-500">{{ enrollment.guardian?.name || 'Sem responsável' }}</dd>
              <dt class="sr-only">Status</dt>
              <dd class="mt-3">
                <span 
                  class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 inset-ring inset-ring-green-600/20"
                >
                  {{ enrollment.status === 'active' ? 'Ativo' : 'Inativo' }}
                </span>
              </dd>
            </dl>
          </div>
          <div>
            <div class="-mt-px flex divide-x divide-gray-200">
              <div class="flex w-0 flex-1">
                <button
                  @click="openStudentModal(enrollment)"
                  class="relative -mr-px inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-bl-lg border border-transparent py-4 text-sm font-semibold text-gray-900 hover:bg-gray-50 transition-colors"
                >
                  <EyeIcon class="size-5 text-gray-400" aria-hidden="true" />
                  Ver
                </button>
              </div>
              <div class="-ml-px flex w-0 flex-1">
                <button
                  @click="editStudent(enrollment)"
                  class="relative inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-br-lg border border-transparent py-4 text-sm font-semibold text-gray-900 hover:bg-gray-50 transition-colors"
                >
                  <PencilIcon class="size-5 text-gray-400" aria-hidden="true" />
                  Editar
                </button>
              </div>
            </div>
          </div>
        </li>
      </ul>

      <!-- Empty State -->
      <div v-if="enrollments.length === 0" class="mt-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum aluno encontrado</h3>
        <p class="mt-1 text-sm text-gray-500">Esta turma ainda não possui alunos vinculados.</p>
      </div>
    </div>

    <!-- Modal de Detalhes do Aluno -->
    <StudentDetailsModal
      :show="showStudentModal"
      :enrollment="selectedEnrollment"
      @close="closeStudentModal"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import { route } from 'ziggy-js'
import { EyeIcon, PencilIcon } from '@heroicons/vue/20/solid'
import StudentDetailsModal from './components/StudentDetailsModal.vue'

const props = defineProps({
  classroomId: { type: Number, required: true }
})

const enrollments = ref([])
const classroom = ref(null)
const loading = ref(true)
const error = ref(null)
const showStudentModal = ref(false)
const selectedEnrollment = ref(null)

const goBack = () => {
  router.get(route('turmas.index'))
}

const loadData = async () => {
  loading.value = true
  error.value = null
  
  try {
    const [enrollmentsRes, classroomRes] = await Promise.all([
      axios.get(`/api/classrooms/${props.classroomId}/linked-enrollments`),
      axios.get(`/api/classrooms/${props.classroomId}`)
    ])
    
    enrollments.value = enrollmentsRes.data || []
    classroom.value = classroomRes.data
  } catch (err) {
    console.error('Erro ao carregar alunos:', err)
    error.value = 'Erro ao carregar os alunos da turma. Tente novamente.'
  } finally {
    loading.value = false
  }
}

const refreshData = () => {
  loadData()
}

const openStudentModal = (enrollment) => {
  selectedEnrollment.value = enrollment
  showStudentModal.value = true
}

const closeStudentModal = () => {
  showStudentModal.value = false
  selectedEnrollment.value = null
}

const editStudent = (enrollment) => {
  if (enrollment.student?.id) {
    router.get(route('students.edit', enrollment.student.id))
  }
}

onMounted(() => {
  loadData()
})
</script>
