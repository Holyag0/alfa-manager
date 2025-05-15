<template>
  <div class="min-h-screen p-4 sm:p-6 lg:p-8">
    <Head :title="`Permissão: ${permission.name}`" />
    
    <!-- Cabeçalho com navegação de voltar -->
    <div class="mb-5">
      <Link
        :href="route('permissions.index')"
        class="inline-flex items-center text-sm font-medium text-sky-700 hover:text-indigo-900"
      >
        <svg class="w-5 h-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Voltar para a lista
      </Link>
    </div>
    
    <!-- Cabeçalho da página -->
    <header class="">
      <div class="flex items-center justify-between px-4 py-5 sm:px-6">
        <h1 class="text-2xl font-bold text-sky-700">Detalhes da Permissão</h1>
        <div class="flex space-x-3">
          <Link
            :href="route('permissions.edit', permission.id)"
            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-green-500 border border-transparent rounded-md shadow-sm hover:bg-teal-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Editar
          </Link>
        </div>
      </div>
    </header>

    <!-- Detalhes da permissão -->
    <div class="mt-6 overflow-hidden bg-white rounded-lg shadow">
      <!-- Informações básicas -->
      <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900">{{ permission.name }}</h3>
        <p class="max-w-2xl mt-1 text-sm text-gray-500">ID: {{ permission.id }}</p>
      </div>
      
      <!-- Papéis que usam esta permissão -->
      <div class="px-4 py-5 sm:p-6">
        <h4 class="mb-3 text-base font-medium text-gray-900">Papéis que usam esta permissão</h4>
        
        <div v-if="permission.roles && permission.roles.length > 0" class="space-y-4">
          <ul class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
            <li v-for="role in permission.roles" :key="role.id" class="flex">
              <Link 
                :href="route('roles.edit', role.id)"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-sky-700 bg-indigo-100 rounded-md hover:bg-indigo-200"
              >
                <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                {{ role.name }}
              </Link>
            </li>
          </ul>
        </div>
        
        <div v-else class="italic text-gray-500">
          Esta permissão não está sendo usada por nenhum papel.
        </div>
      </div>

      <!-- Detalhes técnicos -->
      <div class="px-4 py-5 border-t border-gray-200 bg-gray-50 sm:p-6">
        <h4 class="mb-3 text-base font-medium text-gray-900">Detalhes Técnicos</h4>
        
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
          <div>
            <dt class="text-sm font-medium text-gray-500">Guard</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ permission.guard_name }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Criado em</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ formatDateTime(permission.created_at) }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Atualizado em</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ formatDateTime(permission.updated_at) }}</dd>
          </div>
        </dl>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  permission: Object
});

// Formata a data e hora para exibição
function formatDateTime(dateTimeString) {
  if (!dateTimeString) return 'N/A';
  
  const date = new Date(dateTimeString);
  return date.toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}
</script>