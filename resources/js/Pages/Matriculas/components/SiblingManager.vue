<template>
  <div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-medium text-gray-900">
        Gestão de Irmãos
      </h3>
      <button
        @click="openModal"
        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Gerenciar Irmãos
      </button>
    </div>

    <!-- Status do Grupo de Irmãos -->
    <div v-if="siblingGroup" class="mb-6 p-4 bg-blue-50 rounded-lg">
      <div class="flex items-center">
        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <div>
          <h4 class="text-sm font-medium text-blue-900">
            Grupo: {{ siblingGroup.name }}
          </h4>
          <p class="text-sm text-blue-700">
            {{ siblingGroup.siblings_count }} irmão(s) no grupo
            <span v-if="siblingGroup.active_siblings_count > 0" class="ml-2 text-green-600 font-medium">
              ({{ siblingGroup.active_siblings_count }} matriculado(s))
            </span>
          </p>
        </div>
      </div>
    </div>

    <!-- Lista de Irmãos -->
    <div v-if="siblings.length > 0" class="space-y-3">
      <h4 class="text-sm font-medium text-gray-900">Irmãos Vinculados:</h4>
      <div v-for="sibling in siblings" :key="sibling.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
          </svg>
          <div>
            <p class="text-sm font-medium text-gray-900">{{ sibling.name }}</p>
            <p class="text-xs text-gray-500">{{ sibling.relationship }}</p>
          </div>
        </div>
        <button
          @click="removeSibling(sibling.id)"
          class="text-red-600 hover:text-red-800 text-sm font-medium"
        >
          Remover
        </button>
      </div>
    </div>

    <!-- Mensagem quando não há irmãos -->
    <div v-else class="text-center py-8 text-gray-500">
      <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
      </svg>
      <p class="text-sm">Nenhum irmão vinculado</p>
      <p class="text-xs text-gray-400 mt-1">Clique em "Gerenciar Irmãos" para adicionar</p>
    </div>

    <!-- Modal de Gestão -->
    <div v-if="isModalOpen" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">
              Gerenciar Irmãos
            </h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <!-- Formulário para criar/editar grupo -->
          <div v-if="!siblingGroup" class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Nome do Grupo de Irmãos
            </label>
            <input
              v-model="newGroupName"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
              placeholder="Ex: Família Silva"
            />
          </div>

          <!-- Lista de responsáveis disponíveis -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Selecionar Irmãos
            </label>
            <div class="max-h-48 overflow-y-auto border border-gray-300 rounded-md">
              <div v-for="guardian in availableGuardians" :key="guardian.id" class="flex items-center p-2 hover:bg-gray-50">
                <input
                  :id="`guardian-${guardian.id}`"
                  v-model="selectedGuardians"
                  :value="guardian.id"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label :for="`guardian-${guardian.id}`" class="ml-2 text-sm text-gray-900">
                  {{ guardian.name }} ({{ guardian.relationship }})
                </label>
              </div>
            </div>
          </div>

          <!-- Botões de ação -->
          <div class="flex justify-end space-x-3">
            <button
              @click="closeModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
            >
              Cancelar
            </button>
            <button
              @click="saveSiblings"
              :disabled="selectedGuardians.length === 0"
              class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ siblingGroup ? 'Atualizar' : 'Criar Grupo' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  guardian: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['siblings-updated'])

const page = usePage()
const isModalOpen = ref(false)
const newGroupName = ref('')
const selectedGuardians = ref([])
const availableGuardians = ref([])
const siblings = ref([])
const siblingGroup = ref(null)

// Computed
const hasActiveSiblings = computed(() => {
  return siblings.value.some(sibling => sibling.students?.some(student => 
    student.enrollments?.some(enrollment => enrollment.status === 'active')
  ))
})

// Methods
const openModal = async () => {
  await loadAvailableGuardians()
  selectedGuardians.value = siblings.value.map(s => s.id)
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  newGroupName.value = ''
  selectedGuardians.value = []
}

const loadAvailableGuardians = async () => {
  try {
    const response = await fetch('/api/guardians')
    const data = await response.json()
    availableGuardians.value = data.filter(g => g.id !== props.guardian.id)
  } catch (error) {
    console.error('Erro ao carregar responsáveis:', error)
  }
}

const loadSiblings = async () => {
  try {
    if (props.guardian.sibling_group_id) {
      const response = await fetch(`/api/sibling-groups/${props.guardian.sibling_group_id}`)
      const data = await response.json()
      siblingGroup.value = data
      siblings.value = data.guardians?.filter(g => g.id !== props.guardian.id) || []
    }
  } catch (error) {
    console.error('Erro ao carregar irmãos:', error)
  }
}

const saveSiblings = async () => {
  try {
    const payload = {
      guardian_ids: selectedGuardians.value,
      group_name: newGroupName.value
    }

    const response = await fetch(`/api/guardians/${props.guardian.id}/siblings`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': page.props.csrf_token
      },
      body: JSON.stringify(payload)
    })

    if (response.ok) {
      await loadSiblings()
      emit('siblings-updated')
      closeModal()
    }
  } catch (error) {
    console.error('Erro ao salvar irmãos:', error)
  }
}

const removeSibling = async (siblingId) => {
  try {
    const response = await fetch(`/api/guardians/${props.guardian.id}/siblings/${siblingId}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': page.props.csrf_token
      }
    })

    if (response.ok) {
      await loadSiblings()
      emit('siblings-updated')
    }
  } catch (error) {
    console.error('Erro ao remover irmão:', error)
  }
}

// Lifecycle
onMounted(() => {
  loadSiblings()
})
</script>
