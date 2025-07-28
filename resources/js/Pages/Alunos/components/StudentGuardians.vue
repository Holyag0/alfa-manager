<template>
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
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { UserIcon, UsersIcon, PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/20/solid'
import AddGuardianModal from './AddGuardianModal.vue'
import ConfirmationModal from '@/Shared/ConfirmationModal.vue'

const props = defineProps({
  student: Object,
  guardians: Array
})

const showAddModal = ref(false)
const showRemoveModal = ref(false)
const guardianToRemove = ref(null)

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
</script> 