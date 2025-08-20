<template>
  <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Nome
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tipo
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Cor
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Criado em
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Ações
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="category in categories" :key="category.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ category.name }}</div>
                <div v-if="category.description" class="text-sm text-gray-500">{{ category.description }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {{ types[category.type] }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div
                    class="w-6 h-6 rounded-full border border-gray-300"
                    :style="{ backgroundColor: category.formatted_color }"
                  ></div>
                  <span class="ml-2 text-sm text-gray-900">{{ category.formatted_color }}</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    category.is_active
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-800'
                  ]"
                >
                  {{ category.is_active ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(category.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <Menu as="div" class="relative">
                  <MenuButton class="relative block text-gray-500 hover:text-gray-900 transition-colors duration-200">
                    <span class="absolute -inset-2.5" />
                    <span class="sr-only">Abrir opções</span>
                    <EllipsisVerticalIcon class="size-5" aria-hidden="true" />
                  </MenuButton>
                  <transition 
                    enter-active-class="transition ease-out duration-100" 
                    enter-from-class="transform opacity-0 scale-95" 
                    enter-to-class="transform opacity-100 scale-100" 
                    leave-active-class="transition ease-in duration-75" 
                    leave-from-class="transform opacity-100 scale-100" 
                    leave-to-class="transform opacity-0 scale-95"
                  >
                    <MenuItems class="absolute right-0 mt-2 w-40 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-none z-10">
                      <MenuItem v-slot="{ active }">
                        <Link
                          :href="route('categories.show', category.id)"
                          :class="[active ? 'bg-gray-50' : '', 'block px-3 py-1 text-sm text-gray-900']"
                        >
                          <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Ver
                          </div>
                        </Link>
                      </MenuItem>
                      <MenuItem v-slot="{ active }">
                        <Link
                          :href="route('categories.edit', category.id)"
                          :class="[active ? 'bg-gray-50' : '', 'block px-3 py-1 text-sm text-gray-900']"
                        >
                          <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Editar
                          </div>
                        </Link>
                      </MenuItem>
                      <MenuItem v-slot="{ active }">
                        <button
                          @click="toggleStatus(category)"
                          :class="[
                            active ? 'bg-gray-50' : '', 
                            'block px-3 py-1 text-sm w-full text-left',
                            category.is_active ? 'text-red-600' : 'text-green-600'
                          ]"
                        >
                          <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/>
                            </svg>
                            {{ category.is_active ? 'Desativar' : 'Ativar' }}
                          </div>
                        </button>
                      </MenuItem>
                      <MenuItem v-slot="{ active }">
                        <button
                          @click="deleteCategory(category)"
                          :class="[active ? 'bg-gray-50' : '', 'block px-3 py-1 text-sm text-red-600 w-full text-left']"
                        >
                          <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Excluir
                          </div>
                        </button>
                      </MenuItem>
                    </MenuItems>
                  </transition>
                </Menu>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { EllipsisVerticalIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  categories: {
    type: Array,
    required: true
  },
  types: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['delete', 'toggle-status'])

const toggleStatus = (category) => {
  emit('toggle-status', category)
}

const deleteCategory = (category) => {
  emit('delete', category)
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}
</script>
