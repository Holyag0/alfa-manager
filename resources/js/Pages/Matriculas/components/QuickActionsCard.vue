<template>
  <div v-if="!isInModal" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900">Ações Rápidas</h3>
      <div class="flex items-center space-x-2 text-sm text-gray-500">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        <span>Clique para alterar</span>
      </div>
    </div>

    <!-- Status Atual -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-medium text-gray-700">Status da Matrícula</h4>
        <span :class="getStatusClass(currentStatus)" class="px-3 py-1 rounded-full text-sm font-medium">
          {{ getStatusLabel(currentStatus) }}
        </span>
      </div>
      
      <div class="flex flex-wrap gap-2">
        <button 
          v-for="status in availableStatuses"
          :key="status.value"
          @click="openConfirmationModal('status', status.value, status.label)"
          :disabled="status.value === currentStatus"
          :class="[
            'inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-full transition-all duration-200',
            status.value === currentStatus 
              ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
              : `${status.bgClass} ${status.textClass} hover:${status.hoverClass} cursor-pointer`
          ]"
        >
          {{ status.label }}
        </button>
      </div>
    </div>

    <!-- Processo Atual -->
    <div class="mb-6">
      <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-medium text-gray-700">Processo da Matrícula</h4>
        <span :class="getProcessClass(currentProcess)" class="px-3 py-1 rounded-full text-sm font-medium">
          {{ getProcessLabel(currentProcess) }}
        </span>
      </div>
      
      <div class="flex flex-wrap gap-2">
        <button 
          v-for="process in availableProcesses"
          :key="process.value"
          @click="openConfirmationModal('process', process.value, process.label)"
          :disabled="process.value === currentProcess"
          :class="[
            'inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-full transition-all duration-200',
            process.value === currentProcess 
              ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
              : `${process.bgClass} ${process.textClass} hover:${process.hoverClass} cursor-pointer`
          ]"
        >
          {{ process.label }}
        </button>
      </div>
    </div>

    <!-- Ações Especiais -->
    <div>
      <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-medium text-gray-700">Ações Especiais</h4>
      </div>
      
      <div class="flex flex-wrap gap-2">
        <button 
          @click="openRenewModal"
          :disabled="!canRenew"
          :class="[
            'inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-200',
            canRenew
              ? 'bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500'
              : 'bg-gray-100 text-gray-400 cursor-not-allowed'
          ]"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          🔄 Renovar para Novo Ano
        </button>
      </div>
    </div>

    <!-- Modal de Confirmação Melhorado -->
    <div v-if="showModal" :class="['fixed inset-0 flex items-center justify-center bg-black', isInModal ? 'z-[60]' : 'z-50', 'bg-opacity-50']">
      <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-xl">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-white flex items-center">
              <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ modalTitle }}
            </h2>
            <button @click="closeModal" class="text-white hover:text-gray-200 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-4">
          <!-- Resumo da Alteração -->
          <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <p class="text-sm text-gray-600 mb-1">Alteração solicitada:</p>
                <div class="flex items-center space-x-3">
                  <div class="flex items-center space-x-2">
                    <span :class="getStatusOrProcessClass(pendingAction?.type, currentStatus, currentProcess)" 
                          class="px-3 py-1 rounded-full text-sm font-medium">
                      {{ getCurrentLabel() }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    <span :class="getStatusOrProcessClass(pendingAction?.type, pendingAction?.value, pendingAction?.value)" 
                          class="px-3 py-1 rounded-full text-sm font-medium">
                      {{ pendingAction?.label }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Explicação do que vai acontecer -->
          <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-lg p-4">
            <div class="flex items-start">
              <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <div class="flex-1">
                <h3 class="text-sm font-semibold text-blue-900 mb-2">O que o sistema fará:</h3>
                <ul class="space-y-2 text-sm text-blue-800">
                  <li v-for="(action, index) in getActionExplanation()" :key="index" class="flex items-start">
                    <span class="text-blue-600 mr-2">•</span>
                    <span v-html="action"></span>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Informações Adicionais -->
          <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-r-lg p-4">
            <div class="flex items-start">
              <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              <div class="flex-1">
                <p class="text-sm font-medium text-yellow-900 mb-1">Importante:</p>
                <p class="text-sm text-yellow-800">{{ getImportantNote() }}</p>
              </div>
            </div>
          </div>

          <!-- Informações da Matrícula -->
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-gray-500 mb-1">Aluno:</p>
              <p class="font-medium text-gray-900">{{ enrollment.student?.name }}</p>
            </div>
            <div>
              <p class="text-gray-500 mb-1">Ano Letivo:</p>
              <p class="font-medium text-gray-900">{{ enrollment.academic_year || 'Não definido' }}</p>
            </div>
            <div>
              <p class="text-gray-500 mb-1">Turma:</p>
              <p class="font-medium text-gray-900">{{ enrollment.classroom?.name || 'Sem turma' }}</p>
            </div>
            <div>
              <p class="text-gray-500 mb-1">Responsável:</p>
              <p class="font-medium text-gray-900">{{ enrollment.guardian?.name }}</p>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-end space-x-3 border-t border-gray-200">
          <button 
            @click="closeModal" 
            class="px-5 py-2.5 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 font-medium transition-colors"
          >
            Cancelar
          </button>
          <button 
            @click="confirmAction" 
            class="px-5 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 font-medium transition-colors shadow-sm"
          >
            Confirmar Alteração
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Renovação Melhorado -->
    <div v-if="showRenewModal" :class="['fixed inset-0 flex items-center justify-center bg-black', isInModal ? 'z-[60]' : 'z-50', 'bg-opacity-50']">
      <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-xl">
          <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-white flex items-center">
              <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              Renovar Matrícula para Novo Ano Letivo
            </h2>
            <button @click="closeRenewModal" class="text-white hover:text-gray-200 transition-colors" :disabled="renewing">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-5">
          <!-- Informações Atuais -->
          <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Situação Atual</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <p class="text-gray-500 mb-1">Ano Letivo Atual:</p>
                <p class="font-semibold text-gray-900">{{ enrollment.academic_year || 'Não definido' }}</p>
              </div>
              <div>
                <p class="text-gray-500 mb-1">Turma Atual:</p>
                <p class="font-semibold text-gray-900">{{ enrollment.classroom?.name || 'Sem turma' }}</p>
              </div>
              <div>
                <p class="text-gray-500 mb-1">Aluno:</p>
                <p class="font-semibold text-gray-900">{{ enrollment.student?.name }}</p>
              </div>
              <div>
                <p class="text-gray-500 mb-1">Status:</p>
                <span :class="getStatusClass(enrollment.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                  {{ getStatusLabel(enrollment.status) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Formulário -->
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Novo Ano Letivo <span class="text-red-500">*</span>
              </label>
              <select 
                v-model="renewForm.academic_year"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-2.5"
                :disabled="renewing"
              >
                <option value="">Selecione o ano</option>
                <option v-for="year in availableYears" :key="year" :value="year">
                  {{ year }}
                </option>
              </select>
              <p v-if="renewErrors.academic_year" class="mt-1 text-sm text-red-600 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ renewErrors.academic_year }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Turma Sugerida (opcional)
              </label>
              <select 
                v-model="renewForm.classroom_id"
                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-2.5"
                :disabled="renewing"
              >
                <option value="">Não sugerir turma</option>
                <option v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                  {{ classroom.name }}
                </option>
              </select>
              <p class="mt-1 text-xs text-gray-500">
                <strong>Nota:</strong> O aluno ficará elegível (sem turma) e precisará ser vinculado manualmente depois via a página de turmas
              </p>
            </div>
          </div>

          <!-- Explicação do que vai acontecer -->
          <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-lg p-4">
            <div class="flex items-start">
              <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <div class="flex-1">
                <h3 class="text-sm font-semibold text-blue-900 mb-2">O que o sistema fará:</h3>
                <ul class="space-y-2 text-sm text-blue-800">
                  <li class="flex items-start">
                    <span class="text-blue-600 mr-2">1.</span>
                    <span>Finalizará a matrícula atual (ano {{ enrollment.academic_year || 'atual' }}) com status <strong>"Completada"</strong></span>
                  </li>
                  <li class="flex items-start">
                    <span class="text-blue-600 mr-2">2.</span>
                    <span>Criará uma <strong>nova matrícula</strong> para o ano {{ renewForm.academic_year || 'selecionado' }} com status <strong>"Ativo"</strong></span>
                  </li>
                  <li class="flex items-start">
                    <span class="text-blue-600 mr-2">3.</span>
                    <span>Criará a matrícula <strong>sem turma vinculada</strong> (aluno ficará elegível)</span>
                  </li>
                  <li class="flex items-start">
                    <span class="text-blue-600 mr-2">4.</span>
                    <span v-if="renewForm.classroom_id">A turma selecionada será apenas uma <strong>sugestão</strong> - você precisará vincular manualmente depois</span>
                    <span v-else>Você poderá vincular o aluno a uma turma depois via a página de turmas</span>
                  </li>
                  <li class="flex items-start">
                    <span class="text-blue-600 mr-2">5.</span>
                    <span>As <strong>mensalidades</strong> serão geradas automaticamente apenas após vincular o aluno a uma turma</span>
                  </li>
                  <li class="flex items-start">
                    <span class="text-blue-600 mr-2">6.</span>
                    <span>Preservará o <strong>histórico separado</strong> - cada ano letivo terá sua própria matrícula</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Aviso Importante -->
          <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-r-lg p-4">
            <div class="flex items-start">
              <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              <div class="flex-1">
                <p class="text-sm font-medium text-yellow-900 mb-1">Importante:</p>
                <p class="text-sm text-yellow-800">
                  Esta ação não pode ser desfeita automaticamente. A matrícula atual será finalizada e uma nova será criada. 
                  Certifique-se de que deseja prosseguir com a renovação.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-xl flex justify-end space-x-3 border-t border-gray-200">
          <button 
            @click="closeRenewModal" 
            class="px-5 py-2.5 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 font-medium transition-colors"
            :disabled="renewing"
          >
            Cancelar
          </button>
          <button 
            @click="confirmRenew" 
            class="px-5 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 font-medium transition-colors shadow-sm flex items-center"
            :disabled="renewing || !renewForm.academic_year"
          >
            <svg v-if="renewing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span v-if="renewing">Processando...</span>
            <span v-else>Confirmar Renovação</span>
          </button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Versão para modal (sem o card wrapper) -->
  <div v-else class="space-y-6">
    <!-- Status Atual -->
    <div>
      <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-medium text-gray-700">Status da Matrícula</h4>
        <span :class="getStatusClass(currentStatus)" class="px-3 py-1 rounded-full text-sm font-medium">
          {{ getStatusLabel(currentStatus) }}
        </span>
      </div>
      
      <div class="flex flex-wrap gap-2">
        <button 
          v-for="status in availableStatuses"
          :key="status.value"
          @click="openConfirmationModal('status', status.value, status.label)"
          :disabled="status.value === currentStatus"
          :class="[
            'inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-full transition-all duration-200',
            status.value === currentStatus 
              ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
              : `${status.bgClass} ${status.textClass} hover:${status.hoverClass} cursor-pointer`
          ]"
        >
          {{ status.label }}
        </button>
      </div>
    </div>

    <!-- Processo Atual -->
    <div>
      <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-medium text-gray-700">Processo da Matrícula</h4>
        <span :class="getProcessClass(currentProcess)" class="px-3 py-1 rounded-full text-sm font-medium">
          {{ getProcessLabel(currentProcess) }}
        </span>
      </div>
      
      <div class="flex flex-wrap gap-2">
        <button 
          v-for="process in availableProcesses"
          :key="process.value"
          @click="openConfirmationModal('process', process.value, process.label)"
          :disabled="process.value === currentProcess"
          :class="[
            'inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-full transition-all duration-200',
            process.value === currentProcess 
              ? 'bg-gray-100 text-gray-400 cursor-not-allowed' 
              : `${process.bgClass} ${process.textClass} hover:${process.hoverClass} cursor-pointer`
          ]"
        >
          {{ process.label }}
        </button>
      </div>
    </div>

    <!-- Ações Especiais -->
    <div>
      <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-medium text-gray-700">Ações Especiais</h4>
      </div>
      
      <div class="flex flex-wrap gap-2">
        <button 
          @click="openRenewModal"
          :disabled="!canRenew"
          :class="[
            'inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-200',
            canRenew
              ? 'bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500'
              : 'bg-gray-100 text-gray-400 cursor-not-allowed'
          ]"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          🔄 Renovar para Novo Ano
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import ConfirmationModal from '@/Shared/ConfirmationModal.vue'

const props = defineProps({
  enrollment: {
    type: Object,
    required: true
  },
  classrooms: {
    type: Array,
    default: () => []
  },
  isInModal: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['updated', 'close-modal'])

// Estados reativos
const showModal = ref(false)
const pendingAction = ref(null)
const currentStatus = ref(props.enrollment.status)
const currentProcess = ref(props.enrollment.process)

// Estados para renovação
const showRenewModal = ref(false)
const renewForm = ref({
  academic_year: '',
  classroom_id: ''
})
const renewErrors = ref({})
const renewing = ref(false)

// Anos disponíveis (ano atual + próximos 5 anos)
const currentYear = new Date().getFullYear()
const availableYears = computed(() => {
  const years = []
  for (let i = 0; i <= 5; i++) {
    years.push((currentYear + i).toString())
  }
  return years
})

// Verificar se pode renovar
const canRenew = computed(() => {
  return ['active', 'completed'].includes(props.enrollment.status)
})

// Computed
const modalTitle = computed(() => {
  if (!pendingAction.value) return ''
  
  const typeLabel = pendingAction.value.type === 'status' ? 'Status' : 'Processo'
  return `Confirmar Alteração de ${typeLabel}`
})

const getCurrentLabel = () => {
  if (!pendingAction.value) return ''
  return pendingAction.value.type === 'status' 
    ? getStatusLabel(currentStatus.value)
    : getProcessLabel(currentProcess.value)
}

const getStatusOrProcessClass = (type, value1, value2) => {
  if (type === 'status') {
    return getStatusClass(value1 || value2)
  } else {
    return getProcessClass(value1 || value2)
  }
}

// Explicação detalhada do que vai acontecer
const getActionExplanation = () => {
  if (!pendingAction.value) return []
  
  const { type, value } = pendingAction.value
  const explanations = {
    status: {
      active: [
        'A matrícula será marcada como <strong class="font-semibold">Ativa</strong>',
        'O aluno poderá frequentar as aulas normalmente',
        'As mensalidades continuarão sendo geradas (se houver turma vinculada)',
        'A matrícula aparecerá nas listagens de alunos ativos'
      ],
      pending: [
        'A matrícula será marcada como <strong class="font-semibold">Pendente</strong>',
        'O aluno ainda não está totalmente matriculado',
        'Aguardando conclusão de algum processo ou documentação',
        'As mensalidades podem não ser geradas até a matrícula ser completada'
      ],
      cancelled: [
        'A matrícula será marcada como <strong class="font-semibold">Cancelada</strong>',
        'O aluno não poderá mais frequentar as aulas',
        'As mensalidades futuras serão canceladas automaticamente',
        'Esta ação pode ser revertida alterando o status novamente'
      ],
      inactive: [
        'A matrícula será marcada como <strong class="font-semibold">Inativa</strong>',
        'O aluno não está mais ativo no sistema',
        'A matrícula será arquivada mas não será deletada',
        'Pode ser reativada alterando o status para "Ativo"'
      ]
    },
    process: {
      reserva: [
        'O processo será alterado para <strong class="font-semibold">Reserva</strong>',
        'A vaga do aluno está reservada temporariamente',
        'Aguardando confirmação ou pagamento para completar a matrícula',
        'A matrícula ainda não está totalmente efetivada'
      ],
      aguardando_pagamento: [
        'O processo será alterado para <strong class="font-semibold">Aguardando Pagamento</strong>',
        'A matrícula está aguardando o pagamento das taxas iniciais',
        'O aluno não poderá frequentar as aulas até o pagamento ser confirmado',
        'Após o pagamento, altere para "Completa"'
      ],
      aguardando_documentos: [
        'O processo será alterado para <strong class="font-semibold">Aguardando Documentos</strong>',
        'A matrícula está aguardando a entrega de documentos necessários',
        'O aluno não poderá frequentar as aulas até os documentos serem entregues',
        'Após a entrega, altere para "Completa"'
      ],
      completa: [
        'O processo será alterado para <strong class="font-semibold">Completa</strong>',
        'A matrícula está totalmente finalizada e ativa',
        'O aluno pode frequentar as aulas normalmente',
        'As mensalidades serão geradas automaticamente (se houver turma)'
      ],
      renovacao: [
        'O processo será alterado para <strong class="font-semibold">Renovação</strong>',
        'Indica que esta matrícula foi renovada de um ano anterior',
        'A matrícula está completa e ativa',
        'O histórico do ano anterior é preservado separadamente'
      ],
      desistencia: [
        'O processo será alterado para <strong class="font-semibold">Desistência</strong>',
        'O aluno desistiu da matrícula',
        'A matrícula será finalizada e não poderá ser reativada facilmente',
        'As mensalidades futuras serão canceladas'
      ],
      transferencia: [
        'O processo será alterado para <strong class="font-semibold">Transferência</strong>',
        'Indica que o aluno foi transferido de outra instituição',
        'A matrícula está completa e ativa',
        'O histórico da transferência será registrado'
      ]
    }
  }
  
  return explanations[type]?.[value] || ['A alteração será aplicada imediatamente']
}

const getImportantNote = () => {
  if (!pendingAction.value) return ''
  
  const { type, value } = pendingAction.value
  
  const notes = {
    status: {
      cancelled: 'Esta ação pode afetar o histórico financeiro e as mensalidades futuras. Certifique-se de que esta é a ação desejada.',
      inactive: 'Matrículas inativas não aparecerão nas listagens principais, mas podem ser reativadas a qualquer momento.',
      active: 'Ao ativar a matrícula, certifique-se de que todos os processos necessários foram concluídos.',
      pending: 'Matrículas pendentes podem não gerar mensalidades até serem completadas.'
    },
    process: {
      completa: 'Certifique-se de que todos os documentos e pagamentos foram concluídos antes de marcar como completa.',
      desistencia: 'Esta ação indica que o aluno desistiu. Considere cancelar a matrícula se necessário.',
      aguardando_pagamento: 'Lembre-se de alterar para "Completa" após o pagamento ser confirmado.',
      aguardando_documentos: 'Lembre-se de alterar para "Completa" após os documentos serem entregues.'
    }
  }
  
  return notes[type]?.[value] || 'Esta alteração será aplicada imediatamente e pode afetar o funcionamento da matrícula.'
}

// Opções disponíveis
const availableStatuses = [
  { value: 'active', label: '✅ Ativo', bgClass: 'bg-green-100', textClass: 'text-green-800', hoverClass: 'bg-green-200' },
  { value: 'pending', label: '⏳ Pendente', bgClass: 'bg-yellow-100', textClass: 'text-yellow-800', hoverClass: 'bg-yellow-200' },
  { value: 'cancelled', label: '❌ Cancelado', bgClass: 'bg-red-100', textClass: 'text-red-800', hoverClass: 'bg-red-200' },
  { value: 'inactive', label: '🔒 Inativo', bgClass: 'bg-gray-100', textClass: 'text-gray-800', hoverClass: 'bg-gray-200' }
]

const availableProcesses = [
  { value: 'reserva', label: '🎫 Reserva', bgClass: 'bg-purple-100', textClass: 'text-purple-800', hoverClass: 'bg-purple-200' },
  { value: 'aguardando_pagamento', label: '💳 Aguard. Pagamento', bgClass: 'bg-yellow-100', textClass: 'text-yellow-800', hoverClass: 'bg-yellow-200' },
  { value: 'aguardando_documentos', label: '📄 Aguard. Documentos', bgClass: 'bg-orange-100', textClass: 'text-orange-800', hoverClass: 'bg-orange-200' },
  { value: 'completa', label: '✅ Completa', bgClass: 'bg-green-100', textClass: 'text-green-800', hoverClass: 'bg-green-200' },
  { value: 'renovacao', label: '🔄 Renovação', bgClass: 'bg-blue-100', textClass: 'text-blue-800', hoverClass: 'bg-blue-200' },
  { value: 'desistencia', label: '🚫 Desistência', bgClass: 'bg-red-100', textClass: 'text-red-800', hoverClass: 'bg-red-200' },
  { value: 'transferencia', label: '↗️ Transferência', bgClass: 'bg-indigo-100', textClass: 'text-indigo-800', hoverClass: 'bg-indigo-200' }
]

// Métodos
const openConfirmationModal = (type, value, label) => {
  pendingAction.value = { type, value, label }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  emit('close-modal')
  pendingAction.value = null
}

const confirmAction = () => {
  if (!pendingAction.value) return
  
  const { type, value } = pendingAction.value
  const updateData = {}
  updateData[type] = value
  
  router.patch(route('matriculas.update', props.enrollment.id), updateData, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      // Atualizar valores locais
      if (type === 'status') {
        currentStatus.value = value
      } else {
        currentProcess.value = value
      }
      
      emit('updated', { type, value })
      closeModal()
    },
    onError: (errors) => {
      console.error('Erro ao atualizar:', errors)
      closeModal()
    }
  })
}

// Funções de estilo e label
const getStatusClass = (status) => {
  const statusObj = availableStatuses.find(s => s.value === status)
  return statusObj ? `${statusObj.bgClass} ${statusObj.textClass}` : 'bg-gray-100 text-gray-800'
}

const getStatusLabel = (status) => {
  const statusObj = availableStatuses.find(s => s.value === status)
  return statusObj ? statusObj.label : status
}

const getProcessClass = (process) => {
  const processObj = availableProcesses.find(p => p.value === process)
  return processObj ? `${processObj.bgClass} ${processObj.textClass}` : 'bg-gray-100 text-gray-800'
}

const getProcessLabel = (process) => {
  const processObj = availableProcesses.find(p => p.value === process)
  return processObj ? processObj.label : process
}

// Métodos de renovação
const openRenewModal = () => {
  renewForm.value = {
    academic_year: (currentYear + 1).toString(), // Próximo ano como padrão
    classroom_id: ''
  }
  renewErrors.value = {}
  showRenewModal.value = true
}

const closeRenewModal = () => {
  emit('close-modal')
  showRenewModal.value = false
  renewForm.value = {
    academic_year: '',
    classroom_id: ''
  }
  renewErrors.value = {}
}

const confirmRenew = () => {
  // Validação
  renewErrors.value = {}
  
  if (!renewForm.value.academic_year) {
    renewErrors.value.academic_year = 'O ano letivo é obrigatório'
    return
  }

  const newYear = parseInt(renewForm.value.academic_year)
  const currentYearEnrollment = parseInt(props.enrollment.academic_year || currentYear)
  
  if (newYear <= currentYearEnrollment) {
    renewErrors.value.academic_year = 'O novo ano deve ser maior que o ano atual'
    return
  }

  renewing.value = true

  const formData = {
    academic_year: newYear,
    classroom_id: renewForm.value.classroom_id || null
  }

  router.post(route('matriculas.renovar', props.enrollment.id), formData, {
    preserveState: false,
    preserveScroll: false,
    onSuccess: () => {
      closeRenewModal()
      // Redirecionamento será feito pelo backend
    },
    onError: (errors) => {
      renewErrors.value = errors
      renewing.value = false
    }
  })
}
</script> 