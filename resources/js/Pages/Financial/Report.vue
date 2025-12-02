<template>
  <div class="min-h-screen">
    <div class="space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="lg:flex lg:items-center lg:justify-between">
        <div>
          <h2 class="text-2xl/7 font-bold text-gray-100 sm:text-3xl">Relatório Financeiro</h2>
          <p class="mt-1 text-sm text-gray-400">
            Período: {{ formatDate(report.period.start) }} até {{ formatDate(report.period.end) }}
          </p>
        </div>
        <Link :href="route('financial.transactions.index')" 
              class="mt-5 lg:mt-0 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
          <svg class="-ml-0.5 mr-1.5 size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          Voltar para Transações
        </Link>
      </div>

      <!-- Filtro de Período -->
      <div class="bg-white shadow rounded-lg p-6">
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Data Inicial</label>
            <input v-model="filters.start_date" type="date"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Data Final</label>
            <input v-model="filters.end_date" type="date"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
          </div>
          <div class="flex items-end">
            <button type="submit"
                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              Filtrar
            </button>
          </div>
        </form>
      </div>

      <!-- Cards de Resumo -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Receitas -->
        <div class="bg-white shadow rounded-lg p-6 border-l-4 border-green-500">
          <div class="flex items-center">
            <div class="p-3 rounded-lg bg-green-100">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
            </div>
            <div class="ml-4 flex-1">
              <p class="text-sm font-medium text-gray-500">Total de Receitas</p>
              <p class="text-2xl font-bold text-green-600">{{ formatCurrency(report.summary.receitas) }}</p>
            </div>
          </div>
        </div>

        <!-- Despesas -->
        <div class="bg-white shadow rounded-lg p-6 border-l-4 border-red-500">
          <div class="flex items-center">
            <div class="p-3 rounded-lg bg-red-100">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
              </svg>
            </div>
            <div class="ml-4 flex-1">
              <p class="text-sm font-medium text-gray-500">Total de Despesas</p>
              <p class="text-2xl font-bold text-red-600">{{ formatCurrency(report.summary.despesas) }}</p>
            </div>
          </div>
        </div>

        <!-- Saldo -->
        <div class="bg-white shadow rounded-lg p-6 border-l-4"
             :class="report.summary.saldo >= 0 ? 'border-blue-500' : 'border-orange-500'">
          <div class="flex items-center">
            <div class="p-3 rounded-lg"
                 :class="report.summary.saldo >= 0 ? 'bg-blue-100' : 'bg-orange-100'">
              <svg class="w-6 h-6" 
                   :class="report.summary.saldo >= 0 ? 'text-blue-600' : 'text-orange-600'"
                   fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div class="ml-4 flex-1">
              <p class="text-sm font-medium text-gray-500">Saldo</p>
              <p class="text-2xl font-bold"
                 :class="report.summary.saldo >= 0 ? 'text-blue-600' : 'text-orange-600'">
                {{ formatCurrency(report.summary.saldo) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Receitas por Categoria -->
      <div v-if="report.receitas_por_categoria.length > 0" class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
          <h3 class="text-lg font-medium text-gray-900">Receitas por Categoria</h3>
        </div>
        <ul class="divide-y divide-gray-200">
          <li v-for="item in report.receitas_por_categoria" :key="item.category"
              class="px-6 py-4 hover:bg-gray-50">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-900">{{ item.category }}</p>
                <p class="text-xs text-gray-500">{{ item.count }} transação(ões)</p>
              </div>
              <span class="text-lg font-bold text-green-600">{{ formatCurrency(item.total) }}</span>
            </div>
          </li>
        </ul>
      </div>

      <!-- Despesas por Categoria -->
      <div v-if="report.despesas_por_categoria.length > 0" class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
          <h3 class="text-lg font-medium text-gray-900">Despesas por Categoria</h3>
        </div>
        <ul class="divide-y divide-gray-200">
          <li v-for="item in report.despesas_por_categoria" :key="item.category"
              class="px-6 py-4 hover:bg-gray-50">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-900">{{ item.category }}</p>
                <p class="text-xs text-gray-500">{{ item.count }} transação(ões)</p>
              </div>
              <span class="text-lg font-bold text-red-600">{{ formatCurrency(item.total) }}</span>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
  report: Object,
  filters: Object
})

const filters = reactive({
  start_date: props.filters?.start_date || '',
  end_date: props.filters?.end_date || ''
})

const applyFilters = () => {
  router.get(route('financial.report'), filters, {
    preserveState: true
  })
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value || 0)
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('pt-BR')
}
</script>

