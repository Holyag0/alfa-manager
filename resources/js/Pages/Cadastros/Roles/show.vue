<template>
  <div class="min-h-screen p-4 bg-gray-50 sm:p-6 lg:p-8">
    <Head :title="`Papel: ${role.name}`" />
    
    <!-- Cabeçalho com navegação de voltar -->
    <div class="mb-5">
      <Link
        :href="route('roles.index')"
        class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-900"
      >
        <svg class="w-5 h-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Voltar para a lista
      </Link>
    </div>
    
    <!-- Cabeçalho da página -->
    <header class="bg-white rounded-lg shadow">
      <div class="flex items-center justify-between px-4 py-5 sm:px-6">
        <h1 class="text-2xl font-bold text-gray-900">Detalhes do Papel</h1>
        <div class="flex space-x-3">
          <Link
            :href="route('roles.edit', role.id)"
            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Editar
          </Link>
        </div>
      </div>
    </header>

    <!-- Detalhes do papel -->
    <div class="mt-6 overflow-hidden bg-white rounded-lg shadow">
      <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900">{{ role.name }}</h3>
        <p class="max-w-2xl mt-1 text-sm text-gray-500">ID: {{ role.id }}</p>
      </div>
      
      <div class="px-4 py-5 sm:p-6">
        <div class="mb-6">
          <h4 class="mb-3 text-base font-medium text-gray-900">Permissões atribuídas</h4>
          
          <div class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="(groupedPermissions, group) in groupedPermissionsList" :key="group" class="p-3 border border-gray-200 rounded-md">
              <h5 class="mb-2 text-sm font-semibold text-gray-700">{{ formatGroupName(group) }}</h5>
              <ul class="space-y-1">
                <li v-for="permission in groupedPermissions" :key="permission.id" class="text-sm text-gray-600">
                  {{ formatPermissionName(permission.name) }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/inertia-vue3';

const props = defineProps({
  role: Object
});

// Agrupa as permissões por categoria (baseada no prefixo antes do hífen)
const groupedPermissionsList = computed(() => {
  const grouped = {};
  
  if (!props.role.permissions) return grouped;
  
  props.role.permissions.forEach(permission => {
    if (!permission || !permission.name) return;
    
    // Extrai o nome do grupo (texto antes do hífen)
    const parts = permission.name.split('-');
    const groupName = parts.length > 0 ? parts[0] : 'outros';
    
    // Inicializa o grupo se não existir
    if (!grouped[groupName]) {
      grouped[groupName] = [];
    }
    
    // Adiciona a permissão ao grupo
    grouped[groupName].push(permission);
  });
  
  return grouped;
});

// Formata o nome do grupo para exibição
function formatGroupName(groupName) {
  if (!groupName) return '';
  return groupName.charAt(0).toUpperCase() + groupName.slice(1);
}

// Formata o nome da permissão para exibição
function formatPermissionName(permissionName) {
  if (!permissionName) return '';
  
  // Obtém o texto após o hífen
  const parts = permissionName.split('-');
  if (parts.length < 2) return permissionName;
  
  const actionName = parts[1];
  
  if (actionName === 'list') return 'Listar';
  if (actionName === 'create') return 'Criar';
  if (actionName === 'edit') return 'Editar';
  if (actionName === 'delete') return 'Excluir';
  
  return actionName.charAt(0).toUpperCase() + actionName.slice(1);
}
</script>