<template>
  <div class="min-h-screen">
    <div class="border-b border-cyan-500">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6 md:flex md:items-center md:justify-between">
          <div class="flex-1 min-w-0">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h1 class="text-2xl font-bold text-sky-700">Editar Turma</h1>
                <strong class="text-sm text-gray-500">Gerencie vínculos de alunos</strong>
              </div>
            </div>
          </div>
          <div class="mt-4 flex md:mt-0 md:ml-4">
            <button @click="goBack" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
              Voltar
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Abas -->
      <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'details'"
            :class="[
              activeTab === 'details'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            Dados da Turma
          </button>
          <button
            @click="activeTab = 'linking'"
            :class="[
              activeTab === 'linking'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            Vinculações
          </button>
        </nav>
      </div>

      <!-- Mensagens de feedback -->
      <div v-if="successMessage" class="mb-4 bg-green-50 border border-green-200 rounded-md p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-green-800">{{ successMessage }}</p>
          </div>
        </div>
      </div>

      <div v-if="error" class="mb-4 bg-red-50 border border-red-200 rounded-md p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-red-800">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Aba: Dados da Turma -->
      <ClassroomDetailsForm
        v-if="activeTab === 'details'"
        :classroom="classroom"
        :saving="saving"
        @submit="updateClassroom"
        @cancel="resetForm"
      />

      <!-- Aba: Vinculações -->
      <ClassroomLinkingManager
        v-if="activeTab === 'linking'"
        :linked="linked"
        :eligible="eligible"
        :classrooms="classrooms"
        :loading="loading"
        :processing="processing"
        @link="onLink"
        @transfer="onTransfer"
        @unlink="onUnlink"
      />
    </div>
  </div>
  
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import { route } from 'ziggy-js'
import ClassroomDetailsForm from './components/ClassroomDetailsForm.vue'
import ClassroomLinkingManager from './components/ClassroomLinkingManager.vue'

const props = defineProps({
  classroomId: { type: Number, required: true }
})

const activeTab = ref('details')
const loading = ref(true)
const error = ref(null)
const saving = ref(false)
const classroom = ref(null)
const linked = ref([])
const eligible = ref([])
const classrooms = ref([])
const processing = ref({})
const successMessage = ref('')

const goBack = () => {
  router.get(route('turmas.index'))
}

const loadClassroom = async () => {
  try {
    const response = await axios.get(`/api/classrooms/${props.classroomId}`)
    classroom.value = response.data
  } catch (e) {
    console.error('Erro ao carregar turma:', e)
    error.value = 'Falha ao carregar dados da turma.'
  }
}

const loadData = async () => {
  loading.value = true
  error.value = null
  try {
    const [classroomRes, linkedRes, eligibleRes, classesRes] = await Promise.all([
      axios.get(`/api/classrooms/${props.classroomId}`),
      axios.get(`/api/classrooms/${props.classroomId}/linked-enrollments`),
      axios.get(`/api/classrooms/${props.classroomId}/eligible-enrollments`),
      axios.get('/api/classrooms-detailed')
    ])
    classroom.value = classroomRes.data
    linked.value = linkedRes.data || []
    eligible.value = eligibleRes.data || []
    classrooms.value = (classesRes.data || []).filter(c => c.id !== props.classroomId)
  } catch (e) {
    console.error('Erro ao carregar dados:', e)
    error.value = 'Falha ao carregar informações.'
  } finally {
    loading.value = false
  }
}

const updateClassroom = async (formData) => {
  saving.value = true
  successMessage.value = ''
  error.value = null

  try {
    await axios.put(`/api/classrooms/${props.classroomId}`, formData)
    successMessage.value = 'Turma atualizada com sucesso!'
    await loadClassroom()
    setTimeout(() => successMessage.value = '', 3000)
  } catch (e) {
    const msg = e.response?.data?.message || e.response?.data?.error || 'Erro ao atualizar turma'
    error.value = msg
    setTimeout(() => error.value = null, 5000)
  } finally {
    saving.value = false
  }
}

const resetForm = async () => {
  // Componente filho gerencia o reset interno
  if (classroom.value) {
    await loadClassroom()
  }
}

const onLink = async (enrollment) => {
  if (processing.value[`link-${enrollment.id}`]) return
  
  processing.value[`link-${enrollment.id}`] = true
  successMessage.value = ''
  error.value = null
  
  try {
    await axios.post(`/api/classrooms/${props.classroomId}/link-enrollment`, { enrollment_id: enrollment.id })
    successMessage.value = `Aluno ${enrollment.student?.name} vinculado com sucesso!`
    await loadData()
    setTimeout(() => successMessage.value = '', 3000)
  } catch (e) {
    const msg = e.response?.data?.message || e.response?.data?.error || 'Erro ao vincular aluno'
    error.value = msg
    console.error('Erro ao vincular:', e)
    setTimeout(() => error.value = null, 5000)
  } finally {
    processing.value[`link-${enrollment.id}`] = false
  }
}

const onTransfer = async (enrollment, targetClassroomId) => {
  if (!targetClassroomId || processing.value[`transfer-${enrollment.id}`]) return
  
  processing.value[`transfer-${enrollment.id}`] = true
  successMessage.value = ''
  error.value = null
  
  try {
    await axios.post(`/api/classrooms/${targetClassroomId}/transfer-enrollment`, { enrollment_id: enrollment.id })
    const targetClassroom = classrooms.value.find(c => c.id == targetClassroomId)
    successMessage.value = `Aluno ${enrollment.student?.name} transferido para ${targetClassroom?.name || 'outra turma'}!`
    await loadData()
    setTimeout(() => successMessage.value = '', 3000)
  } catch (e) {
    const msg = e.response?.data?.message || e.response?.data?.error || 'Erro ao transferir aluno'
    error.value = msg
    console.error('Erro ao transferir:', e)
    setTimeout(() => error.value = null, 5000)
  } finally {
    processing.value[`transfer-${enrollment.id}`] = false
  }
}

const onUnlink = async (enrollment) => {
  if (processing.value[`unlink-${enrollment.id}`]) return
  
  processing.value[`unlink-${enrollment.id}`] = true
  successMessage.value = ''
  error.value = null
  
  try {
    await axios.post(`/api/classrooms/${props.classroomId}/unlink-enrollment`, { enrollment_id: enrollment.id })
    successMessage.value = `Aluno ${enrollment.student?.name} desvinculado com sucesso!`
    await loadData()
    setTimeout(() => successMessage.value = '', 3000)
  } catch (e) {
    const msg = e.response?.data?.message || e.response?.data?.error || 'Erro ao desvincular aluno'
    error.value = msg
    console.error('Erro ao desvincular:', e)
    setTimeout(() => error.value = null, 5000)
  } finally {
    processing.value[`unlink-${enrollment.id}`] = false
  }
}

onMounted(() => {
  loadData()
})
</script>


