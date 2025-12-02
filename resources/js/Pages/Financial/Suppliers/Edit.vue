<template>
  <div class="">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <!-- Header -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Editar Fornecedor/Pagante</h1>
            <p class="text-sm text-gray-600 mt-1">Atualize as informações do fornecedor/pagante</p>
          </div>
          <Link :href="route('financial.suppliers.index')" 
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar
          </Link>
        </div>
      </div>

      <!-- Formulário -->
      <form @submit.prevent="submit" class="bg-white shadow rounded-lg p-6">
        <div class="space-y-6">
          <!-- Tipos -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Tipos <span class="text-red-500">*</span>
            </label>
            <p class="text-xs text-gray-500 mb-3">Selecione um ou ambos os tipos</p>
            <div class="space-y-3">
              <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition-all"
                     :class="form.is_pagante ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:border-gray-400'">
                <input v-model="form.is_pagante"
                       type="checkbox"
                       :true-value="true"
                       :false-value="false"
                       class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                <div class="ml-3 flex-1">
                  <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-900">Pagante (Receitas)</span>
                  </div>
                  <p class="text-xs text-gray-500 mt-1">Pode fazer pagamentos de receitas</p>
                </div>
              </label>
              
              <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer transition-all"
                     :class="form.is_fornecedor ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'">
                <input v-model="form.is_fornecedor"
                       type="checkbox"
                       :true-value="true"
                       :false-value="false"
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <div class="ml-3 flex-1">
                  <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-900">Fornecedor (Despesas)</span>
                  </div>
                  <p class="text-xs text-gray-500 mt-1">Pode receber pagamentos de despesas</p>
                </div>
              </label>
            </div>
            <p v-if="form.errors.is_pagante || form.errors.is_fornecedor" class="mt-1 text-sm text-red-600">
              {{ form.errors.is_pagante || form.errors.is_fornecedor }}
            </p>
          </div>

          <!-- Nome -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Nome <span class="text-red-500">*</span>
            </label>
            <input v-model="form.name"
                   type="text"
                   required
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                   :class="{ 'border-red-300': form.errors.name }"
                   placeholder="Nome completo ou razão social">
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Documento -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                CPF/CNPJ
              </label>
              <input v-model="form.document"
                     type="text"
                     class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                     :class="{ 'border-red-300': form.errors.document }"
                     placeholder="000.000.000-00 ou 00.000.000/0000-00">
              <p v-if="form.errors.document" class="mt-1 text-sm text-red-600">{{ form.errors.document }}</p>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Email
              </label>
              <input v-model="form.email"
                     type="email"
                     class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                     :class="{ 'border-red-300': form.errors.email }"
                     placeholder="email@exemplo.com">
              <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Telefone -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Telefone
              </label>
              <input v-model="form.phone"
                     type="text"
                     class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                     placeholder="(00) 00000-0000">
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Status
              </label>
              <select v-model="form.active"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option :value="true">Ativo</option>
                <option :value="false">Inativo</option>
              </select>
            </div>
          </div>

          <!-- Endereço -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Endereço
            </label>
            <input v-model="form.address"
                   type="text"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                   placeholder="Rua, número, bairro, cidade - UF">
          </div>

          <!-- Observações -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Observações
            </label>
            <textarea v-model="form.notes"
                      rows="3"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      placeholder="Observações adicionais..."></textarea>
          </div>
        </div>

        <!-- Botões de Ação -->
        <div class="mt-6 flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
          <Link :href="route('financial.suppliers.index')"
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            Cancelar
          </Link>
          <button type="submit"
                  :disabled="form.processing"
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50">
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  supplier: Object
})

const form = useForm({
  is_pagante: props.supplier.is_pagante,
  is_fornecedor: props.supplier.is_fornecedor,
  name: props.supplier.name,
  document: props.supplier.document || '',
  email: props.supplier.email || '',
  phone: props.supplier.phone || '',
  address: props.supplier.address || '',
  notes: props.supplier.notes || '',
  active: props.supplier.active
})

const submit = () => {
  // Garantir que os valores booleanos sejam enviados corretamente
  form.transform((data) => ({
    ...data,
    is_pagante: Boolean(data.is_pagante),
    is_fornecedor: Boolean(data.is_fornecedor),
    active: Boolean(data.active)
  })).put(route('financial.suppliers.update', props.supplier.id), {
    preserveScroll: true,
    onSuccess: () => {
      // Sucesso - redirecionamento é feito pelo backend
    },
    onError: (errors) => {
      console.log('Erros de validação:', errors)
    }
  })
}
</script>

