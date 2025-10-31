<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"/>
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Vinculações</h3>
            <p class="text-sm text-gray-600">Gerencie alunos vinculados e elegíveis para esta turma</p>
          </div>
        </div>
      </div>
    </div>

    <div class="p-6">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Coluna: Vinculados -->
        <div>
          <div class="flex items-center justify-between mb-3">
            <h4 class="text-sm font-medium text-gray-700">Alunos vinculados</h4>
            <div class="text-xs text-gray-500">{{ linked.length }}</div>
          </div>
          <div class="bg-gray-50 rounded-lg border border-gray-200">
            <div class="p-3 border-b border-gray-200">
              <input 
                v-model="linkedSearch" 
                type="text" 
                placeholder="Buscar aluno..." 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-white" 
              />
            </div>
            <ul class="max-h-96 overflow-auto divide-y divide-gray-100">
              <li v-for="en in filteredLinked" :key="en.id" class="px-4 py-3 flex items-center justify-between">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ en.student?.name }}</div>
                  <div class="text-xs text-gray-500">Matrícula #{{ en.id }}</div>
                </div>
                <div class="flex items-center gap-2">
                  <button 
                    @click="handleUnlink(en)" 
                    :disabled="processing[`unlink-${en.id}`]" 
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-md disabled:opacity-50"
                  >
                    <span v-if="processing[`unlink-${en.id}`]">Processando...</span>
                    <span v-else>Desvincular</span>
                  </button>
                  <button 
                    @click="handleTransfer(en)" 
                    :disabled="processing[`transfer-${en.id}`]" 
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md disabled:opacity-50"
                  >
                    <span v-if="processing[`transfer-${en.id}`]">Processando...</span>
                    <span v-else>Transferir</span>
                  </button>
                </div>
              </li>
              <li v-if="!loading && filteredLinked.length === 0" class="px-4 py-6 text-sm text-gray-500">
                Nenhum aluno vinculado encontrado.
              </li>
            </ul>
          </div>
        </div>

        <!-- Coluna: Elegíveis -->
        <div>
          <div class="flex items-center justify-between mb-3">
            <h4 class="text-sm font-medium text-gray-700">Alunos elegíveis</h4>
            <div class="text-xs text-gray-500">{{ eligible.length }}</div>
          </div>
          <div class="bg-gray-50 rounded-lg border border-gray-200">
            <div class="p-3 border-b border-gray-200">
              <input 
                v-model="eligibleSearch" 
                type="text" 
                placeholder="Buscar aluno..." 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-white" 
              />
            </div>
            <ul class="max-h-96 overflow-auto divide-y divide-gray-100">
              <li v-for="en in filteredEligible" :key="en.id" class="px-4 py-3 flex items-center justify-between">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ en.student?.name }}</div>
                  <div class="text-xs text-gray-500">Matrícula #{{ en.id }}</div>
                </div>
                <button 
                  @click="handleLink(en)" 
                  :disabled="processing[`link-${en.id}`]" 
                  class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-green-600 hover:bg-green-700 rounded-md disabled:opacity-50"
                >
                  <span v-if="processing[`link-${en.id}`]">Processando...</span>
                  <span v-else>Vincular</span>
                </button>
              </li>
              <li v-if="!loading && filteredEligible.length === 0" class="px-4 py-6 text-sm text-gray-500">
                Nenhum aluno elegível encontrado.
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmação para Desvincular -->
    <ConfirmationModal :show="showUnlinkModal" @close="showUnlinkModal = false">
      <template #title>Desvincular Aluno</template>
      <template #content>
        <p>Tem certeza que deseja desvincular <strong>{{ enrollmentToUnlink?.student?.name }}</strong> desta turma?</p>
        <p class="mt-2 text-sm text-gray-500">O aluno ficará disponível para ser vinculado a outra turma.</p>
      </template>
      <template #footer>
        <button
          @click="showUnlinkModal = false"
          class="px-4 py-2 mr-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Cancelar
        </button>
        <button
          @click="confirmUnlink"
          class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
        >
          Desvincular
        </button>
      </template>
    </ConfirmationModal>

    <!-- Modal de Confirmação para Transferir -->
    <ConfirmationModal :show="showTransferModal" @close="closeTransferModal">
      <template #title>Transferir Aluno</template>
      <template #content>
        <div>
          <p class="mb-4">Transferir <strong>{{ enrollmentToTransfer?.student?.name }}</strong> para outra turma:</p>
          <label for="transfer-classroom" class="block text-sm font-medium text-gray-700 mb-2">
            Selecione a turma de destino
          </label>
          <select
            id="transfer-classroom"
            v-model="selectedClassroomForTransfer"
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          >
            <option value="">Selecione uma turma...</option>
            <option v-for="c in classrooms" :key="c.id" :value="c.id">
              {{ c.name }} - {{ c.year }} ({{ c.available_slots }} vagas disponíveis)
            </option>
          </select>
          <p v-if="selectedClassroomForTransfer" class="mt-2 text-xs text-gray-500">
            Vagas disponíveis: {{ getSelectedClassroomInfo()?.available_slots || 0 }}
          </p>
        </div>
      </template>
      <template #footer>
        <button
          @click="closeTransferModal"
          class="px-4 py-2 mr-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Cancelar
        </button>
        <button
          @click="confirmTransfer"
          :disabled="!selectedClassroomForTransfer"
          class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Transferir
        </button>
      </template>
    </ConfirmationModal>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'

const props = defineProps({
  linked: {
    type: Array,
    default: () => []
  },
  eligible: {
    type: Array,
    default: () => []
  },
  classrooms: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  processing: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['link', 'transfer', 'unlink'])

const linkedSearch = ref('')
const eligibleSearch = ref('')
const showUnlinkModal = ref(false)
const enrollmentToUnlink = ref(null)
const showTransferModal = ref(false)
const enrollmentToTransfer = ref(null)
const selectedClassroomForTransfer = ref('')

const filteredLinked = computed(() => {
  const q = linkedSearch.value.toLowerCase().trim()
  return props.linked.filter(e => !q || e.student?.name?.toLowerCase()?.includes(q))
})

const filteredEligible = computed(() => {
  const q = eligibleSearch.value.toLowerCase().trim()
  return props.eligible.filter(e => !q || e.student?.name?.toLowerCase()?.includes(q))
})

const handleLink = (enrollment) => {
  emit('link', enrollment)
}

const handleTransfer = (enrollment) => {
  enrollmentToTransfer.value = enrollment
  selectedClassroomForTransfer.value = ''
  showTransferModal.value = true
}

const closeTransferModal = () => {
  showTransferModal.value = false
  enrollmentToTransfer.value = null
  selectedClassroomForTransfer.value = ''
}

const confirmTransfer = () => {
  if (enrollmentToTransfer.value && selectedClassroomForTransfer.value) {
    emit('transfer', enrollmentToTransfer.value, selectedClassroomForTransfer.value)
    closeTransferModal()
  }
}

const getSelectedClassroomInfo = () => {
  if (!selectedClassroomForTransfer.value) return null
  return props.classrooms.find(c => c.id == selectedClassroomForTransfer.value)
}

const handleUnlink = (enrollment) => {
  enrollmentToUnlink.value = enrollment
  showUnlinkModal.value = true
}

const confirmUnlink = () => {
  if (enrollmentToUnlink.value) {
    emit('unlink', enrollmentToUnlink.value)
    showUnlinkModal.value = false
    enrollmentToUnlink.value = null
  }
}
</script>

