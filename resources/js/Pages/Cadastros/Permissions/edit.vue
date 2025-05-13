<template>
  <div class="min-h-screen p-4 bg-gray-50 sm:p-6 lg:p-8">
    <Head :title="`Editar Permissão: ${permission.name}`" />
    
    <!-- Cabeçalho com navegação de voltar -->
    <div class="mb-5">
      <Link
        :href="route('permissions.index')"
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
        <h1 class="text-2xl font-bold text-gray-900">Editar Permissão: {{ permission.name }}</h1>
      </div>
    </header>

    <!-- Formulário de edição -->
    <div class="mt-6 overflow-hidden bg-white rounded-lg shadow">
      <div class="p-6">
        <form @submit.prevent="submit">
          <div class="space-y-6">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">Nome da Permissão <span class="text-red-500">*</span></label>
              <input
                type="text"
                id="name"
                v-model="form.name"
                required
                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                placeholder="Ex: user-list, role-create, etc."
              />
              <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
              
              <div class="mt-2 text-sm text-gray-500">
                <p>O formato recomendado é "recurso-ação" (ex: user-list, role-create, etc.).</p>
                <p>Exemplos comuns de ações: list, create, edit, delete.</p>
              </div>
            </div>

            <!-- Alerta para edição de permissões existentes -->
            <div v-if="permission.roles && permission.roles.length > 0" class="p-4 rounded-md bg-yellow-50">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="w-5 h-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-yellow-800">Atenção ao modificar esta permissão</h3>
                  <div class="mt-2 text-sm text-yellow-700">
                    <p>
                      Esta permissão está sendo usada por {{ permission.roles.length }} papel(is). 
                      Modificar o nome pode afetar o funcionamento desses papéis.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex items-center justify-end mt-6 space-x-3">
            <Link
              :href="route('permissions.index')"
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
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';

const props = defineProps({
  permission: Object
});

const form = useForm({
  name: props.permission.name
});

function submit() {
  form.put(route('permissions.update', props.permission.id));
}
</script>