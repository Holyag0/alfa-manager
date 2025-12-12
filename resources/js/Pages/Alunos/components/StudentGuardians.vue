<template>
  <div class="space-y-6">
    <!-- Seção de Responsáveis -->
  <div class="bg-white shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-medium text-gray-900">Responsáveis</h3>
          <p class="mt-1 text-sm text-gray-600">Gerencie os responsáveis vinculados a este aluno.</p>
        </div>
        <button 
          @click="showAddModal = true"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
        >
          <PlusIcon class="w-4 h-4 mr-2" />
          Adicionar Responsável
        </button>
      </div>
    </div>

    <div class="p-6">
      <!-- Lista de Responsáveis -->
      <div v-if="guardians && guardians.length > 0" class="space-y-4">
        <div 
          v-for="guardian in guardians" 
          :key="guardian.id" 
          class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:border-gray-300 transition-colors"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <!-- Informações Básicas -->
              <div class="flex items-center space-x-3 mb-3">
                <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                  <UserIcon class="w-6 h-6 text-indigo-600" />
                </div>
                <div>
                  <h4 class="text-lg font-semibold text-gray-900">{{ guardian.name }}</h4>
                  <p class="text-sm text-gray-600">
                    Tipo: <span class="font-medium capitalize">{{ guardian.guardian_type }}</span>
                    <span v-if="guardian.relationship"> • {{ guardian.relationship }}</span>
                  </p>
                </div>
              </div>

              <!-- Contatos -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                <div v-if="guardian.contacts && guardian.contacts.length > 0">
                  <h5 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Contatos</h5>
                  <div class="space-y-1">
                    <div 
                      v-for="contact in guardian.contacts.slice(0, 2)" 
                      :key="contact.id"
                      class="flex items-center text-sm"
                    >
                      <span v-if="contact.type === 'email'" class="w-4 h-4 mr-2">📧</span>
                      <span v-else-if="contact.type === 'phone'" class="w-4 h-4 mr-2">📱</span>
                      <span class="text-gray-700">{{ contact.value }}</span>
                      <span v-if="contact.is_primary" class="ml-2 px-1.5 py-0.5 bg-blue-100 text-blue-700 text-xs rounded">Principal</span>
                    </div>
                    <p v-if="guardian.contacts.length > 2" class="text-xs text-gray-500">
                      +{{ guardian.contacts.length - 2 }} mais contato(s)
                    </p>
                  </div>
                </div>

                <div v-if="guardian.addresses && guardian.addresses.length > 0">
                  <h5 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Endereço</h5>
                  <div class="text-sm text-gray-700">
                    <p>{{ guardian.addresses[0].street }}, {{ guardian.addresses[0].number }}</p>
                    <p>{{ guardian.addresses[0].neighborhood }} - {{ guardian.addresses[0].city }}/{{ guardian.addresses[0].state }}</p>
                    <p v-if="guardian.addresses.length > 1" class="text-xs text-gray-500 mt-1">
                      +{{ guardian.addresses.length - 1 }} mais endereço(s)
                    </p>
                  </div>
                </div>
              </div>

              <!-- Informações Adicionais -->
              <div v-if="guardian.occupation || guardian.workplace" class="text-sm text-gray-600">
                <span v-if="guardian.occupation">{{ guardian.occupation }}</span>
                <span v-if="guardian.occupation && guardian.workplace"> • </span>
                <span v-if="guardian.workplace">{{ guardian.workplace }}</span>
              </div>
            </div>

            <!-- Ações -->
            <div class="flex flex-col space-y-2 ml-4">
              <Link 
                :href="route('guardian.edit', guardian.id)"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                <PencilIcon class="w-4 h-4 mr-2" />
                Editar
              </Link>
              
              <button 
                @click="confirmRemove(guardian)"
                class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              >
                <TrashIcon class="w-4 h-4 mr-2" />
                Remover
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Estado Vazio -->
      <div v-else class="text-center py-8">
        <UsersIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum responsável vinculado</h3>
        <p class="mt-1 text-sm text-gray-500">Comece adicionando um responsável para este aluno.</p>
        <div class="mt-6">
          <button 
            @click="showAddModal = true"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            Adicionar Primeiro Responsável
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de Adicionar Responsável -->
    <AddGuardianModal
      v-if="showAddModal"
      :student="student"
      @close="showAddModal = false"
      @added="onGuardianAdded"
    />

    <!-- Modal de Confirmação de Remoção -->
    <ConfirmationModal
      :show="showRemoveModal"
      @confirm="removeGuardian"
      @cancel="showRemoveModal = false"
    >
      <template #title>Remover Responsável</template>
      <template #message>
        <div class="space-y-2">
          <p v-if="guardianToRemove">
            Tem certeza que deseja remover <strong>{{ guardianToRemove.name }}</strong> como responsável deste aluno?
          </p>
          <div class="bg-red-50 border-l-4 border-red-400 p-3 rounded">
            <p class="text-sm text-red-800">
              <strong>Atenção:</strong> Esta ação apenas remove o vínculo com este aluno. O responsável não será excluído do sistema.
            </p>
          </div>
        </div>
      </template>
    </ConfirmationModal>
    </div>

    <!-- Seção de Irmãos -->
    <div class="bg-white shadow rounded-lg">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-medium text-gray-900">Irmãos</h3>
            <p class="mt-1 text-sm text-gray-600">Gerencie os irmãos vinculados a este aluno.</p>
          </div>
          <button 
            @click="showSiblingSearch = true"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            Adicionar Irmão
          </button>
        </div>
      </div>

      <div class="p-6">
        <!-- Lista de Irmãos -->
        <div v-if="siblings && siblings.length > 0" class="space-y-4">
          <div 
            v-for="sibling in siblings" 
            :key="sibling.id" 
            class="bg-gray-50 rounded-lg p-4 border border-gray-200 hover:border-gray-300 transition-colors"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <!-- Informações Básicas -->
                <div class="flex items-center space-x-3 mb-3">
                  <div 
                    v-if="sibling.photo" 
                    class="w-12 h-12 rounded-full bg-gray-100 overflow-hidden"
                  >
                    <img 
                      :src="`/storage/${sibling.photo}`" 
                      :alt="sibling.name"
                      class="w-full h-full object-cover"
                    />
                  </div>
                  <div 
                    v-else
                    class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center"
                  >
                    <UserIcon class="w-6 h-6 text-purple-600" />
                  </div>
                  <div>
                    <h4 class="text-lg font-semibold text-gray-900">{{ sibling.name }}</h4>
                    <p class="text-sm text-gray-600">
                      <span v-if="sibling.cpf">CPF: {{ formatCPF(sibling.cpf) }}</span>
                      <span v-if="sibling.birth_date" class="ml-2">
                        • Nascimento: {{ formatDate(sibling.birth_date) }}
                      </span>
                    </p>
                    <p v-if="sibling.classroom_name" class="text-sm text-gray-500 mt-1">
                      Turma: {{ sibling.classroom_name }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Ações -->
              <div class="flex flex-col space-y-2 ml-4">
                <Link 
                  :href="route('students.edit', sibling.id)"
                  class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  <PencilIcon class="w-4 h-4 mr-2" />
                  Ver Perfil
                </Link>
                
                <button 
                  @click="confirmRemoveSibling(sibling)"
                  class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                  <TrashIcon class="w-4 h-4 mr-2" />
                  Remover
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Estado Vazio -->
        <div v-else class="text-center py-8">
          <UsersIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum irmão vinculado</h3>
          <p class="mt-1 text-sm text-gray-500">Comece adicionando um irmão para este aluno.</p>
          <div class="mt-6">
            <button 
              @click="showSiblingSearch = true"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
            >
              <PlusIcon class="w-4 h-4 mr-2" />
              Adicionar Primeiro Irmão
            </button>
          </div>
        </div>
      </div>

      <!-- Modal de Busca de Irmão -->
      <StudentSiblingSearch
        :open="showSiblingSearch"
        :exclude-student-id="student.id"
        @update:open="showSiblingSearch = $event"
        @select="onSiblingSelected"
      />

      <!-- Modal de Confirmação de Remoção de Irmão -->
      <ConfirmationModal
        :show="showRemoveSiblingModal"
        @confirm="removeSibling"
        @cancel="showRemoveSiblingModal = false"
      >
        <template #title>Remover Irmão</template>
        <template #message>
          <div class="space-y-2">
            <p v-if="siblingToRemove">
              Tem certeza que deseja remover o vínculo de irmão com <strong>{{ siblingToRemove.name }}</strong>?
            </p>
            <div class="bg-red-50 border-l-4 border-red-400 p-3 rounded">
              <p class="text-sm text-red-800">
                <strong>Atenção:</strong> Esta ação apenas remove o vínculo de irmão. O aluno não será excluído do sistema.
              </p>
            </div>
          </div>
        </template>
      </ConfirmationModal>

      <!-- Modal de Erro com Feedback Detalhado -->
      <div
        v-if="showErrorModalDialog"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
      >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showErrorModalDialog = false"></div>

          <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                  <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                  <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                    {{ errorTitle }}
                  </h3>
                  <div class="mt-4">
                    <div class="text-sm text-gray-700 whitespace-pre-line bg-gray-50 p-4 rounded-lg border border-gray-200">
                      {{ errorMessage }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                type="button"
                @click="showErrorModalDialog = false"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Entendi
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { UserIcon, UsersIcon, PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/20/solid'
import AddGuardianModal from './AddGuardianModal.vue'
import StudentSiblingSearch from './StudentSiblingSearch.vue'
import ConfirmationModal from '@/Shared/ConfirmationModal.vue'
import axios from 'axios'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  student: Object,
  guardians: Array,
  siblings: {
    type: Array,
    default: () => []
  }
})

const page = usePage()
const showAddModal = ref(false)
const showRemoveModal = ref(false)
const guardianToRemove = ref(null)
const showSiblingSearch = ref(false)
const showRemoveSiblingModal = ref(false)
const siblingToRemove = ref(null)
const showErrorModalDialog = ref(false)
const errorMessage = ref('')
const errorTitle = ref('')

const confirmRemove = (guardian) => {
  guardianToRemove.value = guardian
  showRemoveModal.value = true
}

const removeGuardian = () => {
  if (!guardianToRemove.value) return

  router.delete(route('students.guardians.detach', [props.student.id, guardianToRemove.value.id]), {
    onSuccess: () => {
      showRemoveModal.value = false
      guardianToRemove.value = null
    },
    onError: (errors) => {
      console.error('Erro ao remover responsável:', errors)
    }
  })
}

const onGuardianAdded = () => {
  showAddModal.value = false
  // A página será recarregada automaticamente pelo Inertia
}

const onSiblingSelected = async (sibling) => {
  try {
    const response = await axios.post(
      `/api/students/${props.student.id}/siblings`,
      { sibling_id: sibling.id },
      {
        headers: {
          'X-CSRF-TOKEN': page.props.csrf_token
        }
      }
    )

    // Recarregar a página para atualizar a lista de irmãos
    router.reload({
      only: ['siblings'],
      onSuccess: () => {
        showSiblingSearch.value = false
      }
    })
  } catch (error) {
    console.error('Erro ao adicionar irmão:', error)
    const errorMessage = error.response?.data?.error || 'Erro ao adicionar irmão'
    showErrorModal(errorMessage, 'Adicionar Irmão')
  }
}

const confirmRemoveSibling = (sibling) => {
  siblingToRemove.value = sibling
  showRemoveSiblingModal.value = true
}

const removeSibling = async () => {
  if (!siblingToRemove.value) return

  try {
    await axios.delete(
      `/api/students/${props.student.id}/siblings/${siblingToRemove.value.id}`,
      {
        headers: {
          'X-CSRF-TOKEN': page.props.csrf_token
        }
      }
    )

    // Recarregar a página para atualizar a lista de irmãos
    router.reload({
      only: ['siblings'],
      onSuccess: () => {
        showRemoveSiblingModal.value = false
        siblingToRemove.value = null
      }
    })
  } catch (error) {
    console.error('Erro ao remover irmão:', error)
    const errorMessage = error.response?.data?.error || 'Erro ao remover irmão'
    showRemoveSiblingModal.value = false
    showErrorModal(errorMessage, 'Remover Irmão')
  }
}

const formatCPF = (cpf) => {
  if (!cpf) return ''
  const cleaned = cpf.replace(/\D/g, '')
  if (cleaned.length !== 11) return cpf
  return cleaned.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
}

const formatDate = (date) => {
  if (!date) return ''
  const d = new Date(date)
  return d.toLocaleDateString('pt-BR')
}

const showErrorModal = (message, title = 'Erro') => {
  errorMessage.value = message
  errorTitle.value = title
  showErrorModalDialog.value = true
}
</script> 