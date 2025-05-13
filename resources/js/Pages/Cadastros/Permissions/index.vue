<template>
  <div class="min-h-screen p-4 bg-gray-50 sm:p-6 lg:p-8">
    <Head title="Gerenciamento de Permissões" />
    
    <!-- Breadcrumb de navegação -->
    <BreadCrumbs :items="[
      { name: 'Cadastros', url: route('menuCadastros') },
      { name: 'Permissões' }
    ]" />
    
    <!-- Cabeçalho da página -->
    <header class="bg-white rounded-lg shadow">
      <div class="flex items-center justify-between px-4 py-5 sm:px-6">
        <h1 class="text-2xl font-bold text-gray-900">Gerenciamento de Permissões</h1>
        <Link
          v-if="getPermission('permission-create')"
          :href="route('permissions.create')"
          class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          <PlusIcon class="w-5 h-5 mr-2" aria-hidden="true" />
          Nova Permissão
        </Link>
      </div>
    </header>

    <!-- Mensagem de sucesso -->
    <div v-if="$page.props.flash.success" class="p-4 mt-4 text-green-700 bg-green-100 rounded-lg">
      {{ $page.props.flash.success }}
    </div>

    <!-- Tabela de permissões -->
    <div class="mt-6 overflow-hidden bg-white rounded-lg shadow">
      <div class="p-6">
        <div v-if="permissions.data.length === 0" class="p-4 text-center text-gray-500">
          Nenhuma permissão encontrada.
        </div>
        
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">ID</th>
                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nome</th>
                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Papéis Associados</th>
                <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="permission in permissions.data" :key="permission.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ permission.id }}</td>
                <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">{{ permission.name }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  <div class="flex flex-wrap gap-1">
                    <span 
                      v-for="role in permission.roles" 
                      :key="role.id" 
                      class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full"
                    >
                      {{ role.name }}
                    </span>
                    <span v-if="permission.roles.length === 0" class="italic text-gray-400">
                      Nenhum papel
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
                  <div class="flex justify-end space-x-3">
                    <Link 
                      v-if="getPermission('permission-edit')" 
                      :href="route('permissions.edit', permission.id)" 
                      class="inline-flex items-center text-indigo-600 hover:text-indigo-900"
                    >
                      <PencilIcon class="w-4 h-4 mr-1" aria-hidden="true" />
                      Editar
                    </Link>
                    <button 
                      v-if="getPermission('permission-delete')" 
                      @click="confirmDelete(permission)" 
                      class="inline-flex items-center text-red-600 hover:text-red-900"
                    >
                      <TrashIcon class="w-4 h-4 mr-1" aria-hidden="true" />
                      Excluir
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Paginação -->
        <div class="mt-6">
          <Pagination :links="permissions.links" />
        </div>
      </div>
    </div>

    <!-- Modal de confirmação de exclusão -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-10 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" @click="closeModal"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
          <div class="sm:flex sm:items-start">
            <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
              <svg class="w-6 h-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                Confirmar exclusão
              </h3>
              <div class="mt-2">
                <p class="text-sm text-gray-500">
                  Tem certeza que deseja excluir a permissão "{{ permissionToDelete ? permissionToDelete.name : '' }}"?
                  Esta ação não poderá ser desfeita.
                </p>
                <p v-if="permissionToDelete && permissionToDelete.roles.length > 0" class="mt-2 text-sm text-red-500">
                  Atenção: Esta permissão está sendo usada por {{ permissionToDelete.roles.length }} papel(éis).
                </p>
              </div>
            </div>
          </div>
          <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
            <Link
              :href="route('permissions.destroy', permissionToDelete ? permissionToDelete.id : '')"
              method="delete"
              as="button"
              class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
              @click="closeModal"
            >
              Excluir
            </Link>
            <button 
              type="button"
              class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
              @click="closeModal"
            >
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/inertia-vue3';
import Pagination from '../../../Shared/Pagination.vue';
import BreadCrumbs from '../../../Shared/Accordion.vue';

import { 
  PlusIcon, 
  PencilIcon, 
  TrashIcon 
} from '@heroicons/vue/outline';

const props = defineProps({
  permissions: Object
});

const page = usePage();

const showDeleteModal = ref(false);
const permissionToDelete = ref(null);

function confirmDelete(permission) {
  permissionToDelete.value = permission;
  showDeleteModal.value = true;
}

function closeModal() {
  showDeleteModal.value = false;
  setTimeout(() => {
    permissionToDelete.value = null;
  }, 200);
}

function getPermission(permission) {
  const allPermissions = page.props.value.auth.user.permissions;
  return allPermissions.includes(permission);
}
</script>