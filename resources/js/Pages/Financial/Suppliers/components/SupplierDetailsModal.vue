<template>
  <div v-if="show && supplier" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Overlay -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>

    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
      <div class="relative transform overflow-hidden rounded-lg bg-white shadow-xl transition-all w-full max-w-2xl">
        <!-- Header -->
        <div class="bg-white px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-medium text-gray-900">{{ supplier.name }}</h3>
              <div class="mt-2 flex gap-2">
                <span :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
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
              </div>
            </div>
            <button @click="$emit('close')" class="text-gray-400 hover:text-gray-500">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Body -->
        <div class="px-6 py-4">
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

          <!-- Estatísticas -->
          <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="text-sm font-medium text-gray-900 mb-4">Estatísticas</h4>
            <div class="grid grid-cols-2 gap-4">
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

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
          <button
            @click="$emit('delete', supplier)"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Excluir
          </button>
          <Link
            :href="route('financial.suppliers.edit', supplier.id)"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Editar
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  show: {
    type: Boolean,
    default: false
  },
  supplier: {
    type: Object,
    default: null
  }
})

defineEmits(['close', 'delete'])
</script>

