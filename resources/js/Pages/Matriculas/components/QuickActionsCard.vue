<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
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

    <!-- Modal de Confirmação -->
    <ConfirmationModal
      :show="showModal"
      @confirm="confirmAction"
      @cancel="closeModal"
    >
      <template #title>{{ modalTitle }}</template>
      <template #message>
        <div class="space-y-2">
          <p>{{ modalMessage }}</p>
          <div class="bg-blue-50 border-l-4 border-blue-400 p-3 rounded">
            <p class="text-sm text-blue-800">
              <strong>Informação:</strong> Esta ação será aplicada imediatamente.
            </p>
          </div>
        </div>
      </template>
    </ConfirmationModal>
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
  }
})

const emit = defineEmits(['updated'])

// Estados reativos
const showModal = ref(false)
const pendingAction = ref(null)
const currentStatus = ref(props.enrollment.status)
const currentProcess = ref(props.enrollment.process)

// Computed
const modalTitle = computed(() => {
  if (!pendingAction.value) return ''
  
  const typeLabel = pendingAction.value.type === 'status' ? 'Status' : 'Processo'
  return `Alterar ${typeLabel}`
})

const modalMessage = computed(() => {
  if (!pendingAction.value) return ''
  
  const typeLabel = pendingAction.value.type === 'status' ? 'status' : 'processo'
  const currentLabel = pendingAction.value.type === 'status' 
    ? getStatusLabel(currentStatus.value)
    : getProcessLabel(currentProcess.value)
  
  return `Deseja alterar o ${typeLabel} de "${currentLabel}" para "${pendingAction.value.label}"?`
})

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
</script> 