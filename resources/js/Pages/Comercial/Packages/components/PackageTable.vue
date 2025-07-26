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
          <tr v-for="packageItem in packages" :key="packageItem.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">
                {{ packageItem.name }}
              </div>
              <div v-if="packageItem.description" class="text-sm text-gray-500">
                {{ packageItem.description }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                {{ packageItem.category }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900 font-semibold">
                {{ packageItem.formatted_price }}
              </div>
              <div v-if="packageItem.has_discount" class="text-xs text-green-600">
                {{ packageItem.discount_percentage }}% desconto
              </div>
            </td>

            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  packageItem.status === 'active'
                    ? 'bg-green-100 text-green-800'
                    : 'bg-red-100 text-red-800'
                ]"
              >
                {{ packageItem.status === 'active' ? 'Ativo' : 'Inativo' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <div class="flex space-x-2">
                <Link
                  :href="route('packages.show', packageItem.id)"
                  class="text-indigo-600 hover:text-indigo-900 flex items-center"
                >
                  <EyeIcon class="w-4 h-4 mr-1" />
                  Ver
                </Link>
                <Link
                  :href="route('packages.edit', packageItem.id)"
                  class="text-blue-600 hover:text-blue-900 flex items-center"
                >
                  <PencilIcon class="w-4 h-4 mr-1" />
                  Editar
                </Link>
                <button
                  @click="$emit('delete', packageItem)"
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
    <div v-if="packages.length === 0" class="text-center py-12">
      <div class="flex flex-col items-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">Nenhum pacote encontrado</h3>
        <p class="mt-2 text-gray-500">Tente ajustar os filtros ou criar um novo pacote.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { EyeIcon, PencilIcon, TrashIcon } from '@heroicons/vue/20/solid'

defineProps({
  packages: {
    type: Array,
    default: () => []
  }
})

defineEmits(['delete'])
</script> 