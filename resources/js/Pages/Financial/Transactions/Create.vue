<template>
  <div class="min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <!-- Header -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Nova Transação</h1>
            <p class="text-sm text-gray-600 mt-1">Registre uma nova receita ou despesa</p>
          </div>
          <Link :href="route('financial.transactions.index')" 
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
          <!-- Tipo de Transação -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Tipo de Transação <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-4">
              <button type="button"
                      @click="form.type = 'receita'"
                      :class="[
                        'relative flex items-center justify-center px-4 py-3 border-2 rounded-lg font-medium transition-all',
                        form.type === 'receita'
                          ? 'border-green-500 bg-green-50 text-green-700'
                          : 'border-gray-300 bg-white text-gray-700 hover:border-gray-400'
                      ]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Receita
              </button>
              <button type="button"
                      @click="form.type = 'despesa'"
                      :class="[
                        'relative flex items-center justify-center px-4 py-3 border-2 rounded-lg font-medium transition-all',
                        form.type === 'despesa'
                          ? 'border-red-500 bg-red-50 text-red-700'
                          : 'border-gray-300 bg-white text-gray-700 hover:border-gray-400'
                      ]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                </svg>
                Despesa
              </button>
            </div>
            <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
          </div>

          <!-- Categoria -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Categoria <span class="text-red-500">*</span>
            </label>
            <select v-model="form.category_id"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    :class="{ 'border-red-300': form.errors.category_id }">
              <option value="">Selecione uma categoria</option>
              <option v-for="category in filteredCategories" 
                      :key="category.id" 
                      :value="category.id">
                {{ category.name }}
              </option>
            </select>
            <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id }}</p>
          </div>

          <!-- Descrição -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Descrição <span class="text-red-500">*</span>
            </label>
            <textarea v-model="form.description"
                      rows="3"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      :class="{ 'border-red-300': form.errors.description }"
                      placeholder="Descreva a transação..."></textarea>
            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Valor -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Valor (R$) <span class="text-red-500">*</span>
              </label>
              <div class="relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-500 sm:text-sm">R$</span>
                </div>
                <input v-model="form.amount"
                       type="number"
                       step="0.01"
                       min="0.01"
                       class="w-full pl-12 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       :class="{ 'border-red-300': form.errors.amount }"
                       placeholder="0,00">
              </div>
              <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</p>
            </div>

            <!-- Data da Transação -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Data da Transação <span class="text-red-500">*</span>
              </label>
              <input v-model="form.transaction_date"
                     type="date"
                     class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                     :class="{ 'border-red-300': form.errors.transaction_date }">
              <p v-if="form.errors.transaction_date" class="mt-1 text-sm text-red-600">{{ form.errors.transaction_date }}</p>
            </div>
          </div>

          <!-- Método de Pagamento -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Método de Pagamento
            </label>
            <select v-model="form.payment_method"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
              <option value="">Selecione (opcional)</option>
              <option value="pix">PIX</option>
              <option value="credit_card">Cartão de Crédito</option>
              <option value="debit_card">Cartão de Débito</option>
              <option value="cash">Dinheiro</option>
              <option value="bank_transfer">Transferência Bancária</option>
              <option value="check">Cheque</option>
              <option value="other">Outro</option>
            </select>
          </div>

          <!-- Campos de Pagante (Receitas) ou Beneficiário (Despesas) -->
          <div v-if="form.type" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-sm font-medium text-gray-900">
                {{ form.type === 'receita' ? 'Informações do Pagante' : 'Informações do Fornecedor' }}
              </h3>
              <div class="flex items-center gap-2">
                <button 
                  @click="openSearchModal"
                  type="button"
                  class="text-xs text-indigo-600 hover:text-indigo-900 flex items-center px-2 py-1 border border-indigo-300 rounded hover:bg-indigo-50">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                  </svg>
                  Buscar
                </button>
                <Link :href="route('financial.suppliers.create')" 
                      target="_blank"
                      class="text-xs text-indigo-600 hover:text-indigo-900 flex items-center px-2 py-1 border border-indigo-300 rounded hover:bg-indigo-50">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                  </svg>
                  Novo {{ form.type === 'receita' ? 'Pagante' : 'Fornecedor' }}
                </Link>
              </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Nome (Receita) -->
              <div v-if="form.type === 'receita'">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Nome do Pagante
                </label>
                <input v-model="form.payer_name"
                       type="text"
                       :disabled="form.payer_supplier_id"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                       placeholder="Ex: João Silva">
                <p v-if="form.errors.payer_name" class="mt-1 text-sm text-red-600">{{ form.errors.payer_name }}</p>
              </div>

              <!-- Nome (Despesa) -->
              <div v-if="form.type === 'despesa'">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Nome do Fornecedor
                </label>
                <input v-model="form.payee_name"
                       type="text"
                       :disabled="form.payee_supplier_id"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                       placeholder="Ex: Empresa XYZ Ltda">
                <p v-if="form.errors.payee_name" class="mt-1 text-sm text-red-600">{{ form.errors.payee_name }}</p>
              </div>

              <!-- CPF/CNPJ (Receita) -->
              <div v-if="form.type === 'receita'">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  CPF/CNPJ do Pagante
                </label>
                <input v-model="form.payer_document"
                       type="text"
                       :disabled="form.payer_supplier_id"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                       placeholder="Ex: 000.000.000-00">
                <p v-if="form.errors.payer_document" class="mt-1 text-sm text-red-600">{{ form.errors.payer_document }}</p>
              </div>

              <!-- CPF/CNPJ (Despesa) -->
              <div v-if="form.type === 'despesa'">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  CPF/CNPJ do Fornecedor
                </label>
                <input v-model="form.payee_document"
                       type="text"
                       :disabled="form.payee_supplier_id"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                       placeholder="Ex: 00.000.000/0000-00">
                <p v-if="form.errors.payee_document" class="mt-1 text-sm text-red-600">{{ form.errors.payee_document }}</p>
              </div>
            </div>
            
            <!-- Informação do supplier selecionado -->
            <div v-if="(form.type === 'receita' && form.payer_supplier_id) || (form.type === 'despesa' && form.payee_supplier_id)" 
                 class="mt-4 p-3 bg-indigo-50 border border-indigo-200 rounded-md">
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <div>
                    <p class="text-sm font-medium text-indigo-900">
                      {{ form.type === 'receita' 
                        ? pagantes.find(p => p.id == form.payer_supplier_id)?.name 
                        : fornecedores.find(f => f.id == form.payee_supplier_id)?.name }}
                    </p>
                    <p v-if="form.type === 'receita' && pagantes.find(p => p.id == form.payer_supplier_id)?.document" 
                       class="text-xs text-indigo-600">
                      {{ pagantes.find(p => p.id == form.payer_supplier_id)?.document }}
                    </p>
                    <p v-if="form.type === 'despesa' && fornecedores.find(f => f.id == form.payee_supplier_id)?.document" 
                       class="text-xs text-indigo-600">
                      {{ fornecedores.find(f => f.id == form.payee_supplier_id)?.document }}
                    </p>
                  </div>
                </div>
                <button 
                  @click="clearSupplier"
                  type="button"
                  class="text-xs text-indigo-600 hover:text-indigo-900">
                  Limpar
                </button>
              </div>
            </div>
          </div>

          <!-- Referência -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Referência
            </label>
            <input v-model="form.reference"
                   type="text"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                   placeholder="Ex: Número da nota fiscal, ID da transação...">
          </div>

          <!-- Status -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Status
            </label>
            <div class="flex items-center space-x-4">
              <label class="flex items-center">
                <input v-model="form.status"
                       type="radio"
                       value="confirmed"
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                <span class="ml-2 text-sm text-gray-700">Confirmado</span>
              </label>
              <label class="flex items-center">
                <input v-model="form.status"
                       type="radio"
                       value="pending"
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                <span class="ml-2 text-sm text-gray-700">Pendente</span>
              </label>
            </div>
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
          <Link :href="route('financial.transactions.index')"
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
            {{ form.processing ? 'Salvando...' : 'Salvar Transação' }}
          </button>
        </div>
      </form>

      <!-- Modal de Busca de Fornecedor/Pagante -->
      <SupplierSearchModal
        :open="showSearchModal"
        :suppliers="form.type === 'receita' ? pagantes : fornecedores"
        :type="form.type"
        @close="showSearchModal = false"
        @select="onSupplierSelect"
        @openCreate="openCreateSupplier"
      />
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import SupplierSearchModal from './components/SupplierSearchModal.vue'

const props = defineProps({
  categories: Array,
  pagantes: Array,
  fornecedores: Array
})

const form = useForm({
  type: 'despesa',
  category_id: '',
  description: '',
  amount: '',
  transaction_date: new Date().toISOString().split('T')[0],
  payment_method: '',
  reference: '',
  status: 'confirmed',
  notes: '',
  payer_name: '',
  payer_document: '',
  payee_name: '',
  payee_document: '',
  payer_supplier_id: '',
  payee_supplier_id: ''
})

const clearSupplier = () => {
  if (form.type === 'receita') {
    form.payer_supplier_id = ''
    form.payer_name = ''
    form.payer_document = ''
  } else {
    form.payee_supplier_id = ''
    form.payee_name = ''
    form.payee_document = ''
  }
}

const showSearchModal = ref(false)

// Watcher para limpar campos quando muda o tipo de transação
watch(() => form.type, (newType) => {
  // Limpar campos de supplier quando muda o tipo
  form.payer_supplier_id = ''
  form.payee_supplier_id = ''
  form.payer_name = ''
  form.payer_document = ''
  form.payee_name = ''
  form.payee_document = ''
})

const filteredCategories = computed(() => {
  if (!form.type) return props.categories
  return props.categories.filter(cat => cat.type === form.type)
})

const openSearchModal = () => {
  showSearchModal.value = true
}

const onSupplierSelect = (supplier) => {
  if (form.type === 'receita') {
    // Verificar se o supplier pode ser pagante
    if (!supplier.is_pagante) {
      alert('Este fornecedor não está marcado como pagante. Selecione outro ou marque este como pagante primeiro.')
      return
    }
    form.payer_supplier_id = supplier.id
    form.payer_name = supplier.name
    form.payer_document = supplier.document || ''
  } else {
    // Verificar se o supplier pode ser fornecedor
    if (!supplier.is_fornecedor) {
      alert('Este pagante não está marcado como fornecedor. Selecione outro ou marque este como fornecedor primeiro.')
      return
    }
    form.payee_supplier_id = supplier.id
    form.payee_name = supplier.name
    form.payee_document = supplier.document || ''
  }
}

const openCreateSupplier = () => {
  window.open(route('financial.suppliers.create'), '_blank')
}

const submit = () => {
  form.post(route('financial.transactions.store'))
}
</script>

