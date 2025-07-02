<template>
  <ul role="list" class="divide-y divide-gray-100">
    <li v-for="enrollment in enrollments" :key="enrollment.id" class="flex justify-between gap-x-6 py-5">
      <div class="flex min-w-0 gap-x-4">
        <div class="size-12 flex-none rounded-full bg-blue-100 flex items-center justify-center text-xl font-bold text-blue-700">
          {{ enrollment.student?.name?.charAt(0) || '?' }}
        </div>
        <div class="min-w-0 flex-auto">
          <p class="text-sm font-semibold text-gray-900">{{ enrollment.student?.name }}</p>
          <p class="text-xs text-gray-500">Responsável: {{ enrollment.guardian?.name }}</p>
          <p class="text-xs text-gray-500">Turma: {{ enrollment.classroom?.name }}</p>
          <p class="text-xs text-gray-500">Data: {{ enrollment.enrollment_date }}</p>
        </div>
      </div>
      <div class="flex items-center gap-x-4">
        <span :class="statusClass(enrollment.status)">{{ statusLabel(enrollment.status) }}</span>
        <Menu as="div" class="relative">
          <MenuButton class="relative block text-gray-500 hover:text-gray-900">
            <span class="absolute -inset-2.5" />
            <span class="sr-only">Abrir opções</span>
            <EllipsisVerticalIcon class="size-5" aria-hidden="true" />
          </MenuButton>
          <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
            <MenuItems class="absolute right-0 z-10 mt-2 w-32 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-none">
              <MenuItem v-slot="{ active }">
                <a :href="route('matriculas.edit', enrollment.id)" :class="[active ? 'bg-gray-50' : '', 'block px-3 py-1 text-sm text-gray-900']">Editar</a>
              </MenuItem>
              <MenuItem v-slot="{ active }">
                <button @click.prevent="$emit('cancel', enrollment)" :class="[active ? 'bg-gray-50' : '', 'block px-3 py-1 text-sm text-red-600 w-full text-left']">Cancelar</button>
              </MenuItem>
              <MenuItem v-slot="{ active }">
                <button @click.prevent="$emit('change-classroom', enrollment)" :class="[active ? 'bg-gray-50' : '', 'block px-3 py-1 text-sm text-yellow-600 w-full text-left']">Trocar Turma</button>
              </MenuItem>
            </MenuItems>
          </transition>
        </Menu>
      </div>
    </li>
    <li v-if="!enrollments.length" class="text-center py-4 text-gray-400">Nenhuma matrícula encontrada.</li>
  </ul>
</template>

<script setup>
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { EllipsisVerticalIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
  enrollments: {
    type: Array,
    default: () => []
  }
});

function statusClass(status) {
  switch (status) {
    case 'active': return 'bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-semibold';
    case 'pending': return 'bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-semibold';
    case 'cancelled': return 'bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-semibold';
    default: return 'bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-semibold';
  }
}
function statusLabel(status) {
  switch (status) {
    case 'active': return 'Ativo';
    case 'pending': return 'Pendente';
    case 'cancelled': return 'Cancelado';
    default: return status;
  }
}
</script> 