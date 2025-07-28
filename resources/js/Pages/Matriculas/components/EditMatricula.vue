<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <!-- Cabeçalho -->
    <div class="px-6 py-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Editar Matrícula</h3>
          <p class="text-sm text-gray-600">Altere as informações da matrícula</p>
        </div>
        
      
      </div>
    </div>

    <!-- Formulário -->
    <form @submit.prevent="updateMatricula" class="p-6 space-y-6">
      <!-- Turma e Data -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Turma</label>
          <select 
            v-model="form.classroom_id"
            :class="[
              'block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
              errors.classroom_id ? 'border-red-300' : ''
            ]"
          >
            <option value="">Selecione uma turma</option>
            <option 
              v-for="classroom in classrooms" 
              :key="classroom.id" 
              :value="classroom.id"
            >
              {{ classroom.name }}
            </option>
          </select>
          <p v-if="errors.classroom_id" class="mt-1 text-sm text-red-600">{{ errors.classroom_id }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Data da Matrícula</label>
          <input 
            type="date"
            v-model="form.enrollment_date"
            :class="[
              'block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
              errors.enrollment_date ? 'border-red-300' : ''
            ]"
          />
          <p v-if="errors.enrollment_date" class="mt-1 text-sm text-red-600">{{ errors.enrollment_date }}</p>
        </div>
      </div>

      <!-- Observações -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
        <textarea
          v-model="form.notes"
          rows="4"
          :class="[
            'block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
            errors.notes ? 'border-red-300' : ''
          ]"
          placeholder="Adicione observações sobre esta matrícula..."
        ></textarea>
        <p v-if="errors.notes" class="mt-1 text-sm text-red-600">{{ errors.notes }}</p>
      </div>

      <!-- Botões de Ação -->
      <div class="flex items-center justify-between pt-4 border-t border-gray-200">
        <div class="flex space-x-3">
          <!-- Ações Rápidas -->
          <button 
            type="button"
            @click="openQuickActionModal('suspended')"
            v-if="form.status === 'active'"
            class="inline-flex items-center px-3 py-2 border border-orange-300 text-sm leading-4 font-medium rounded-md text-orange-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
          >
            🟠 Suspender
          </button>
          
          <button 
            type="button"
            @click="openQuickActionModal('active')"
            v-if="form.status === 'suspended'"
            class="inline-flex items-center px-3 py-2 border border-green-300 text-sm leading-4 font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
          >
            🟢 Reativar
          </button>

          <button 
            type="button"
            @click="openQuickActionModal('cancelled')"
            v-if="['active', 'suspended', 'pending'].includes(form.status)"
            class="inline-flex items-center px-3 py-2 border border-red-300 text-sm leading-4 font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
          >
            🔴 Cancelar
          </button>
        </div>

        <!-- Salvar Alterações -->
        <button 
          type="submit"
          :disabled="processing"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
        >
          <span v-if="processing">Salvando...</span>
          <span v-else>Salvar Alterações</span>
        </button>
      </div>
    </form>

    <!-- Modal de confirmação para ações rápidas -->
    <ConfirmationModal :show="showQuickActionModal" @cancel="closeQuickActionModal" @confirm="confirmQuickAction">
      <template #title>{{ quickActionModalTitle }}</template>
      <template #message>
        <div class="space-y-2">
          <p>{{ quickActionModalMessage }}</p>
          <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
            <p class="text-sm text-yellow-800">
              <strong>Atenção:</strong> Esta ação atualizará a matrícula imediatamente.
            </p>
          </div>
        </div>
      </template>
    </ConfirmationModal>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import ConfirmationModal from '@/Shared/ConfirmationModal.vue'

const props = defineProps({
  enrollment: Object,
  classrooms: Array
})

const processing = ref(false)
const errors = ref({})

// Estados para o modal de ação rápida
const showQuickActionModal = ref(false)
const pendingQuickAction = ref(null)

const form = reactive({
  status: props.enrollment.status,
  process: props.enrollment.process || 'completa',
  classroom_id: props.enrollment.classroom_id,
  enrollment_date: props.enrollment.enrollment_date,
  notes: props.enrollment.notes || ''
})

// Computed properties para o modal de ação rápida
const quickActionModalTitle = computed(() => {
  if (!pendingQuickAction.value) return ''
  
  switch (pendingQuickAction.value) {
    case 'suspended': return 'Suspender Matrícula'
    case 'active': return 'Reativar Matrícula' 
    case 'cancelled': return 'Cancelar Matrícula'
    default: return 'Alterar Status'
  }
})

const quickActionModalMessage = computed(() => {
  if (!pendingQuickAction.value) return ''
  
  const currentStatus = getStatusLabel(form.status)
  const newStatus = getStatusLabel(pendingQuickAction.value)
  
  return `Tem certeza que deseja mudar o status de "${currentStatus}" para "${newStatus}"?`
})

const updateMatricula = () => {
  processing.value = true
  errors.value = {}

  // Remove status e process do form data, pois eles são gerenciados pelo QuickActionsCard
  const formData = {
    classroom_id: form.classroom_id,
    enrollment_date: form.enrollment_date,
    notes: form.notes
  }

  router.patch(route('matriculas.update', props.enrollment.id), formData, {
    preserveScroll: true,
    onSuccess: () => {
      // Success message será mostrada via flash
    },
    onError: (responseErrors) => {
      errors.value = responseErrors
    },
    onFinish: () => {
      processing.value = false
    }
  })
}

const openQuickActionModal = (newStatus) => {
  pendingQuickAction.value = newStatus
  showQuickActionModal.value = true
}

const closeQuickActionModal = () => {
  showQuickActionModal.value = false
  pendingQuickAction.value = null
}

const confirmQuickAction = () => {
  if (pendingQuickAction.value) {
    changeStatus(pendingQuickAction.value)
  }
  closeQuickActionModal()
}

const changeStatus = (newStatus) => {
  form.status = newStatus
  
  // Enviar apenas a mudança de status
  router.patch(route('matriculas.update', props.enrollment.id), { status: newStatus }, {
    preserveScroll: true,
    onSuccess: () => {
      // Success message será mostrada via flash
    },
    onError: (responseErrors) => {
      errors.value = responseErrors
    }
  })
}

const handleQuickUpdate = (updateInfo) => {
  // Atualizar os valores do formulário local
  if (updateInfo.type === 'status') {
    form.status = updateInfo.value
  } else if (updateInfo.type === 'process') {
    form.process = updateInfo.value
  }
}

const getStatusLabel = (status) => {
  switch (status) {
    case 'active': return 'Ativo'
    case 'pending': return 'Pendente'
    case 'cancelled': return 'Cancelado'
    case 'suspended': return 'Suspenso'
    case 'inactive': return 'Inativo'
    default: return status
  }
}
</script> 