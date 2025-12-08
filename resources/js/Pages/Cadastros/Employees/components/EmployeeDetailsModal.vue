<template>
  <TransitionRoot :show="show && !!employee" as="template">
    <Dialog as="div" class="relative z-50" @close="closeModal">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel v-if="employee" class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
              <!-- Header -->
              <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-cyan-50">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-blue-100">
                      <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                    </div>
                    <div class="ml-4">
                      <DialogTitle class="text-lg font-semibold text-gray-900">
                        {{ employee.name }}
                      </DialogTitle>
                      <p class="text-sm text-gray-600">{{ employee.position?.name || 'Sem cargo' }}</p>
                    </div>
                  </div>
                  <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Body -->
              <div class="px-6 py-6 space-y-6 max-h-[60vh] overflow-y-auto">
                <!-- Loading Skeleton -->
                <div v-if="loading" class="space-y-4 animate-pulse">
                  <div class="h-12 bg-gray-200 rounded"></div>
                  <div class="h-20 bg-gray-200 rounded"></div>
                  <div class="grid grid-cols-2 gap-4">
                    <div class="h-16 bg-gray-200 rounded"></div>
                    <div class="h-16 bg-gray-200 rounded"></div>
                  </div>
                  <div class="h-24 bg-gray-200 rounded"></div>
                </div>

                <!-- Conteúdo Real -->
                <div v-else>
                  <!-- Foto e Status -->
                  <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                      <img
                        v-if="employee.photo_url"
                        :src="employee.photo_url"
                        :alt="employee.name"
                        class="h-20 w-20 rounded-full object-cover border-4 border-gray-200"
                      />
                      <div v-else class="h-20 w-20 rounded-full bg-gray-200 flex items-center justify-center border-4 border-gray-200">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                      </div>
                      <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <span :class="getStatusClass(employee.is_active)">
                          {{ employee.is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Dados Pessoais -->
                  <div class="pt-4 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Dados Pessoais</h3>
                    <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                      <div v-if="employee.cpf" class="flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div>
                          <p class="text-xs text-gray-500">CPF</p>
                          <p class="text-sm font-medium text-gray-900">{{ employee.cpf }}</p>
                        </div>
                      </div>
                      <div v-if="employee.rg" class="flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div>
                          <p class="text-xs text-gray-500">RG</p>
                          <p class="text-sm font-medium text-gray-900">{{ employee.rg }}</p>
                        </div>
                      </div>
                      <div v-if="employee.birth_date" class="flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <div>
                          <p class="text-xs text-gray-500">Data de Nascimento</p>
                          <p class="text-sm font-medium text-gray-900">{{ formatDate(employee.birth_date) }}</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Contato -->
                  <div class="pt-4 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Contato</h3>
                    <div class="space-y-2 bg-gray-50 p-4 rounded-lg">
                      <div v-if="employee.email" class="flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <div>
                          <p class="text-xs text-gray-500">Email</p>
                          <p class="text-sm font-medium text-gray-900">{{ employee.email }}</p>
                        </div>
                      </div>
                      <div v-if="employee.phone" class="flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <div>
                          <p class="text-xs text-gray-500">Telefone</p>
                          <p class="text-sm font-medium text-gray-900">{{ employee.phone }}</p>
                        </div>
                      </div>
                      <div v-if="employee.address" class="flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <div>
                          <p class="text-xs text-gray-500">Endereço</p>
                          <p class="text-sm font-medium text-gray-900">{{ employee.address }}</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Dados Profissionais -->
                  <div class="pt-4 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Dados Profissionais</h3>
                    <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                      <div v-if="employee.position" class="flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <div>
                          <p class="text-xs text-gray-500">Cargo</p>
                          <p class="text-sm font-medium text-gray-900">{{ employee.position.name }}</p>
                        </div>
                      </div>
                      <div v-if="employee.hire_date" class="flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <div>
                          <p class="text-xs text-gray-500">Data de Contratação</p>
                          <p class="text-sm font-medium text-gray-900">{{ formatDate(employee.hire_date) }}</p>
                        </div>
                      </div>
                      <div v-if="employee.salary" class="flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                          <p class="text-xs text-gray-500">Salário</p>
                          <p class="text-sm font-medium text-gray-900">R$ {{ formatCurrency(employee.salary) }}</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Observações -->
                  <div v-if="employee.notes" class="pt-4 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Observações</h3>
                    <p class="text-gray-900 whitespace-pre-wrap bg-gray-50 p-3 rounded-lg">{{ employee.notes }}</p>
                  </div>
                </div><!-- Fim Conteúdo Real -->
              </div>

              <!-- Footer com Ações -->
              <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                <div class="flex space-x-3">
                  <button
                    @click="editEmployee"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Editar
                  </button>
                </div>
                
                <button @click="closeModal"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                  Fechar
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  employee: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

const closeModal = () => {
  emit('close')
}

const editEmployee = () => {
  if (props.employee) {
    router.visit(route('cadastros.employees.edit', props.employee.id))
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('pt-BR')
}

const formatCurrency = (value) => {
  if (!value) return '0,00'
  return parseFloat(value).toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

const getStatusClass = (isActive) => {
  return isActive
    ? 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800'
    : 'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800'
}
</script>



