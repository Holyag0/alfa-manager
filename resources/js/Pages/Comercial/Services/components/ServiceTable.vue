<template>
  <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Nome
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Categoria
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Preço
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Ações
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="service in services" :key="service.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">
                {{ service.name }}
              </div>
              <div v-if="service.description" class="text-sm text-gray-500">
                {{ service.description }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ service.category }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
              {{ service.formatted_price }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  service.status === 'active'
                    ? 'bg-green-100 text-green-800'
                    : 'bg-red-100 text-red-800'
                ]"
              >
                {{ service.status === 'active' ? 'Ativo' : 'Inativo' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <div class="flex space-x-2">
                <Link
                  :href="route('services.show', service.id)"
                  class="text-indigo-600 hover:text-indigo-900 flex items-center"
                >
                  <EyeIcon class="w-4 h-4 mr-1" />
                  Ver
                </Link>
                <Link
                  :href="route('services.edit', service.id)"
                  class="text-blue-600 hover:text-blue-900 flex items-center"
                >
                  <PencilIcon class="w-4 h-4 mr-1" />
                  Editar
                </Link>
                <button
                  @click="$emit('delete', service)"
                  class="text-red-600 hover:text-red-900 flex items-center"
                >
                  <TrashIcon class="w-4 h-4 mr-1" />
                  Excluir
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Estado vazio -->
    <div v-if="services.length === 0" class="text-center py-12">
      <div class="flex flex-col items-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhum serviço encontrado</h3>
        <p class="mt-2 text-gray-500">Tente ajustar os filtros ou criar um novo serviço.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { EyeIcon, PencilIcon, TrashIcon } from '@heroicons/vue/20/solid'

defineProps({
  services: {
    type: Array,
    default: () => []
  }
})

defineEmits(['delete'])
</script> 