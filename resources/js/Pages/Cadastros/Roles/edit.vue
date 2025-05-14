<template>
  <div class="min-h-screen p-4 bg-gray-50 sm:p-6 lg:p-8">
    <Head title="Novo Papel" />
    
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
      <div class="px-4 py-5 sm:px-6">
        <h1 class="text-2xl font-bold text-gray-900">Cadastrar Novo Papel</h1>
      </div>
    </header>

    <!-- Formulário de cadastro -->
    <div class="mt-6 overflow-hidden bg-white rounded-lg shadow">
      <div class="p-6">
        <form @submit.prevent="submit">
          <div class="space-y-6">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">Nome do Papel <span class="text-red-500">*</span></label>
              <input
                type="text"
                id="name"
                v-model="form.name"
                required
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              />
              <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
            </div>

            <!-- Permissões -->
            <div>
              <label class="block text-sm font-medium text-gray-700">Permissões <span class="text-red-500">*</span></label>
              <div v-if="form.errors.permissions" class="mt-1 text-sm text-red-600">{{ form.errors.permissions }}</div>
              
              <div class="grid grid-cols-1 mt-4 gap-y-6 gap-x-4 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="(groupedPermissions, group) in groupedPermissionsList" :key="group" class="p-4 border border-gray-200 rounded-md">
                  <h3 class="mb-3 text-sm font-semibold text-gray-700">{{ formatGroupName(group) }}</h3>
                  <div class="space-y-2">
                    <div v-for="permission in groupedPermissions" :key="permission.id" class="relative flex items-start">
                      <div class="flex items-center h-5">
                        <input
                          :id="`permission-${permission.id}`"
                          type="checkbox"
                          :value="permission.id"
                          v-model="form.permissions"
                          class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                        />
                      </div>
                      <div class="ml-3 text-sm">
                        <label :for="`permission-${permission.id}`" class="font-medium text-gray-700">
                          {{ formatPermissionName(permission.name) }}
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex items-center justify-end mt-6 space-x-3">
            <Link
              :href="route('roles.index')"
              class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Cancelar
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
            >
              {{ form.processing ? 'Salvando...' : 'Salvar' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  permissions: Array
});

const form = useForm({
  name: '',
  permissions: []
});

// Agrupa as permissões por categoria (baseada no prefixo antes do hífen)
const groupedPermissionsList = computed(() => {
  const grouped = {};
  
  props.permissions.forEach(permission => {
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

// Submete o formulário
function submit() {
  form.post(route('roles.store'));
}
</script>