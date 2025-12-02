<template>
  <div class="">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <!-- Header -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ supplier.name }}</h1>
            <p class="text-sm text-gray-600 mt-1">
              <span :class="[
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mr-2',
                supplier.is_pagante && supplier.is_fornecedor
                  ? 'bg-purple-100 text-purple-800'
                  : supplier.is_pagante
                    ? 'bg-green-100 text-green-800'
                    : 'bg-blue-100 text-blue-800'
              ]">
                {{ supplier.is_pagante && supplier.is_fornecedor ? 'Pagante e Fornecedor' : supplier.is_pagante ? 'Pagante' : 'Fornecedor' }}
              </span>
              <span :class="[
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                supplier.active 
                  ? 'bg-green-100 text-green-800' 
                  : 'bg-gray-100 text-gray-800'
              ]">
                {{ supplier.active ? 'Ativo' : 'Inativo' }}
              </span>
            </p>
          </div>
          <div class="flex space-x-3">
            <Link :href="route('financial.suppliers.edit', supplier.id)"
                  class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Editar
            </Link>
            <Link :href="route('financial.suppliers.index')" 
                  class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              Voltar
            </Link>
          </div>
        </div>
      </div>

      <!-- Informações -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Informações</h2>
        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
          <div>
            <dt class="text-sm font-medium text-gray-500">Nome</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ supplier.name }}</dd>
          </div>
          
          <div v-if="supplier.document">
            <dt class="text-sm font-medium text-gray-500">CPF/CNPJ</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ supplier.document }}</dd>
          </div>
          
          <div v-if="supplier.email">
            <dt class="text-sm font-medium text-gray-500">Email</dt>
            <dd class="mt-1 text-sm text-gray-900">
              <a :href="`mailto:${supplier.email}`" class="text-indigo-600 hover:text-indigo-900">
                {{ supplier.email }}
              </a>
            </dd>
          </div>
          
          <div v-if="supplier.phone">
            <dt class="text-sm font-medium text-gray-500">Telefone</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ supplier.phone }}</dd>
          </div>
          
          <div v-if="supplier.address" class="sm:col-span-2">
            <dt class="text-sm font-medium text-gray-500">Endereço</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ supplier.address }}</dd>
          </div>
          
          <div v-if="supplier.notes" class="sm:col-span-2">
            <dt class="text-sm font-medium text-gray-500">Observações</dt>
            <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ supplier.notes }}</dd>
          </div>
        </dl>
      </div>

      <!-- Estatísticas -->
      <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Estatísticas</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-sm font-medium text-gray-500">Transações como Pagante</div>
            <div class="mt-2 text-2xl font-semibold text-gray-900">
              {{ supplier.transactions_as_payer_count || 0 }}
            </div>
          </div>
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-sm font-medium text-gray-500">Transações como Fornecedor</div>
            <div class="mt-2 text-2xl font-semibold text-gray-900">
              {{ supplier.transactions_as_payee_count || 0 }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  supplier: Object
})
</script>

