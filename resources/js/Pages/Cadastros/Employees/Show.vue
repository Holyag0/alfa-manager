<template>
  <div class="min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <!-- Header -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Detalhes do Colaborador</h1>
            <p class="text-sm text-gray-600 mt-1">Visualize as informações do colaborador</p>
          </div>
          <div class="flex space-x-2">
            <Link
              :href="route('cadastros.employees.edit', employee.id)"
              class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
            >
              Editar
            </Link>
            <Link
              :href="route('cadastros.employees.index')"
              class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Voltar
            </Link>
          </div>
        </div>
      </div>

      <!-- Conteúdo -->
      <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-start space-x-6 mb-6">
          <!-- Foto -->
          <div>
            <img
              v-if="employee.photo_url"
              :src="employee.photo_url"
              :alt="employee.name"
              class="h-32 w-32 rounded-full object-cover border-4 border-gray-200"
            />
            <div v-else class="h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center border-4 border-gray-200">
              <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
          </div>

          <!-- Informações Básicas -->
          <div class="flex-1">
            <h2 class="text-2xl font-bold text-gray-900">{{ employee.name }}</h2>
            <p class="text-lg text-gray-600 mt-1">{{ employee.position?.name || 'Sem cargo' }}</p>
            <div class="mt-4">
              <span
                :class="[
                  'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                  employee.is_active
                    ? 'bg-green-100 text-green-800'
                    : 'bg-red-100 text-red-800'
                ]"
              >
                {{ employee.is_active ? 'Ativo' : 'Inativo' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Grid de Informações -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
          <!-- Dados Pessoais -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Dados Pessoais</h3>
            <dl class="space-y-3">
              <div v-if="employee.cpf">
                <dt class="text-sm font-medium text-gray-500">CPF</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ employee.cpf }}</dd>
              </div>
              <div v-if="employee.rg">
                <dt class="text-sm font-medium text-gray-500">RG</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ employee.rg }}</dd>
              </div>
              <div v-if="employee.birth_date">
                <dt class="text-sm font-medium text-gray-500">Data de Nascimento</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(employee.birth_date) }}</dd>
              </div>
            </dl>
          </div>

          <!-- Contato -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Contato</h3>
            <dl class="space-y-3">
              <div v-if="employee.email">
                <dt class="text-sm font-medium text-gray-500">Email</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ employee.email }}</dd>
              </div>
              <div v-if="employee.phone">
                <dt class="text-sm font-medium text-gray-500">Telefone</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ employee.phone }}</dd>
              </div>
              <div v-if="employee.address">
                <dt class="text-sm font-medium text-gray-500">Endereço</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ employee.address }}</dd>
              </div>
            </dl>
          </div>

          <!-- Dados Profissionais -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Dados Profissionais</h3>
            <dl class="space-y-3">
              <div v-if="employee.hire_date">
                <dt class="text-sm font-medium text-gray-500">Data de Contratação</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(employee.hire_date) }}</dd>
              </div>
              <div v-if="employee.salary">
                <dt class="text-sm font-medium text-gray-500">Salário</dt>
                <dd class="mt-1 text-sm text-gray-900">R$ {{ formatCurrency(employee.salary) }}</dd>
              </div>
            </dl>
          </div>

          <!-- Observações -->
          <div v-if="employee.notes">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Observações</h3>
            <p class="text-sm text-gray-900">{{ employee.notes }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  employee: Object
})

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
</script>

