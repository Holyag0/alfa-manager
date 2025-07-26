<template>
  <div>
    <Head title="Gerenciar Usuários" />
    
    <div class="mb-6">
      <h2 class="font-semibold text-xl text-sky-800 leading-tight">
        Gerenciar autorização
      </h2>
    </div>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-medium text-gray-900">
                Lista de Usuários
              </h3>
              <div class="text-sm text-gray-500">
                Total: {{ users.total }} usuários
              </div>
            </div>

            <!-- Tabela -->
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Usuário
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Cadastro
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Ações
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                          <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                            <span class="text-sm font-medium text-gray-700">
                              {{ user.name.charAt(0).toUpperCase() }}
                            </span>
                          </div>
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">
                            {{ user.name }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ user.email }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        user.is_auth 
                          ? 'bg-green-100 text-green-800' 
                          : 'bg-red-100 text-red-800'
                      ]">
                        <svg :class="[
                          'w-1.5 h-1.5 mr-1.5',
                          user.is_auth ? 'text-green-400' : 'text-red-400'
                        ]" fill="currentColor" viewBox="0 0 8 8">
                          <circle cx="4" cy="4" r="3" />
                        </svg>
                        {{ user.is_auth ? 'Autorizado' : 'Não Autorizado' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatDate(user.created_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <button
                        @click="openConfirmModal(user)"
                        :class="[
                          'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors',
                          user.is_auth
                            ? 'text-red-700 bg-red-100 hover:bg-red-200 focus:ring-red-500'
                            : 'text-green-700 bg-green-100 hover:bg-green-200 focus:ring-green-500'
                        ]"
                        :disabled="processing"
                      >
                        <component 
                          :is="user.is_auth ? XMarkIcon : CheckIcon" 
                          class="w-4 h-4 mr-1" 
                        />
                        {{ user.is_auth ? 'Desautorizar' : 'Autorizar' }}
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Paginação -->
            <div v-if="users.links.length > 3" class="mt-6">
              <Pagination :links="users.links" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmação -->
    <ConfirmationModal
      :show="showModal"
      @confirm="handleConfirm"
      @cancel="closeModal"
    >
      <template #title>{{ modalData.title }}</template>
      <template #message>
        <div class="space-y-2">
          <p>{{ modalData.message }}</p>
          <div v-if="processing" class="bg-gray-50 border-l-4 border-gray-400 p-3 rounded">
            <p class="text-sm text-gray-600">
              <strong>Processando...</strong> Por favor, aguarde.
            </p>
          </div>
        </div>
      </template>
    </ConfirmationModal>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import Pagination from '@/Shared/Pagination.vue'
import ConfirmationModal from '@/Shared/ConfirmationModal.vue'

defineProps({
  users: Object
})

const processing = ref(false)
const showModal = ref(false)
const selectedUser = ref(null)

const modalData = reactive({
  title: '',
  message: '',
  type: 'warning',
  confirmText: 'Confirmar'
})

const openConfirmModal = (user) => {
  selectedUser.value = user
  
  if (user.is_auth) {
    // Desautorizar
    modalData.title = 'Desautorizar Usuário'
    modalData.message = `Tem certeza que deseja remover a autorização de acesso do usuário "${user.name}"? Ele não conseguirá mais acessar o sistema.`
    modalData.type = 'danger'
    modalData.confirmText = 'Desautorizar'
  } else {
    // Autorizar
    modalData.title = 'Autorizar Usuário'
    modalData.message = `Tem certeza que deseja autorizar o usuário "${user.name}" a acessar o sistema?`
    modalData.type = 'success'
    modalData.confirmText = 'Autorizar'
  }
  
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedUser.value = null
}

const handleConfirm = () => {
  if (!selectedUser.value || processing.value) return
  
  processing.value = true
  
  router.patch(route('admin.users.toggle-auth', selectedUser.value.id), {}, {
    onFinish: () => {
      processing.value = false
      closeModal()
    }
  })
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}
</script>