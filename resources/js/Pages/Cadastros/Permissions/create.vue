<template>
  <div class="min-h-screen p-4 sm:p-6 lg:p-8">
    <Head title="Nova Permissão" />
    
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
      <div class="px-4 py-5 sm:px-6">
        <h1 class="text-2xl font-bold text-sky-700">Cadastrar Nova Permissão</h1>
      </div>
    </header>

    <!-- Formulário de cadastro -->
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

            <!-- Guia rápido -->
            <div class="p-4 rounded-md bg-blue-50">
              <h4 class="mb-2 text-sm font-medium text-blue-800">Guia Rápido para Criação de Permissões</h4>
              <ul class="pl-5 space-y-1 text-sm text-blue-700 list-disc">
                <li>Use nomes descritivos e consistentes</li>
                <li>Mantenha o formato "recurso-ação" para compatibilidade</li>
                <li>Nomes em minúsculos facililtam o gerenciamento</li>
                <li>Evite usar espaços ou caracteres especiais</li>
              </ul>
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
              class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-green-500 border border-transparent rounded-md shadow-sm hover:bg-teal-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
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
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
  name: ''
});

function submit() {
  form.post(route('permissions.store'));
}
</script>