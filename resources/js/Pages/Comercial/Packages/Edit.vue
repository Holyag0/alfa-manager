<template>
  <div class="min-h-screen">
    <!-- Header Section -->
    <div class="border-b border-cyan-500">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6 md:flex md:items-center md:justify-between">
          <div class="flex-1 min-w-0">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h1 class="text-2xl font-bold text-sky-700">Editar Pacote</h1>
                <strong class="text-sm text-gray-500">Atualize as informações do pacote</strong>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <PackageForm
          :package="package"
          :categories="categories"
          :services="services"
          :submit-url="route('packages.update', package.id)"
          submit-method="put"
          submit-button-text="Atualizar Pacote"
          :cancel-url="route('packages.index')"
        />
      </div>
    </div>
  </div>
</template>

<script setup>

import PackageForm from './components/PackageForm.vue'

defineProps({
  package: Object,
  categories: Array,
  services: Array
})
</script> 