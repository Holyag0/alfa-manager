<template>
  <ul role="list" class="divide-y divide-gray-100">
    <li v-for="enrollment in enrollments" :key="enrollment.id" class="flex justify-between gap-x-6 py-5">
      <div class="flex min-w-0 gap-x-4">
        <div>
          <template v-if="enrollment.student?.photo">
            <img :src="`/storage/${enrollment.student.photo}`" alt="Foto do aluno" class="size-12 rounded-full object-cover border border-gray-200 cursor-pointer transition hover:ring-2 hover:ring-blue-400" @click="openPhotoModal(enrollment.student)" />
          </template>
          <template v-else>
            <div class="size-12 flex-none rounded-full bg-blue-100 flex items-center justify-center text-xl font-bold text-blue-700 cursor-pointer transition hover:ring-2 hover:ring-blue-400" @click="openPhotoModal(enrollment.student)">
              {{ enrollment.student?.name?.charAt(0) || '?' }}
            </div>
          </template>
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
        <Menu as="div" class="relative ">
          <MenuButton class="relative block text-gray-500 hover:text-gray-900">
            <span class="absolute -inset-2.5" />
            <span class="sr-only">Abrir opções</span>
            <EllipsisVerticalIcon class="size-5" aria-hidden="true" />
          </MenuButton>
          <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
            <MenuItems class="absolute right-0 mt-2 w-32 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-none">
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

  <!-- Modal de foto ampliada -->
  <transition name="fade">
    <div v-if="showPhotoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60">
      <div class="bg-white rounded-xl shadow-lg p-6 relative flex flex-col items-center min-w-[340px] min-h-[420px]">
        <button @click="closePhotoModal" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 bg-white bg-opacity-80 rounded-full p-1 shadow">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <template v-if="loadingStudent">
          <div class="flex flex-col items-center justify-center h-80">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-green-500 mb-4"></div>
            <div class="text-green-700 font-semibold">Carregando informações...</div>
          </div>
        </template>
        <template v-else-if="selectedStudent">
          <template v-if="selectedStudent?.photo">
            <img :src="`/storage/${selectedStudent.photo}`" alt="Foto ampliada do aluno" class="w-32 h-44 object-cover rounded-xl border border-gray-300 mx-auto" style="aspect-ratio:3/4;" />
          </template>
          <template v-else>
            <div class="w-32 h-44 flex items-center justify-center rounded-xl bg-blue-100 text-6xl font-bold text-blue-700 border border-gray-300 mx-auto" style="aspect-ratio:3/4;">
              {{ selectedStudent?.name?.charAt(0) || '?' }}
            </div>
          </template>
          <div class="mt-4 text-center space-y-1">
            <div class="text-lg font-semibold text-gray-900">{{ selectedStudent?.name }}</div>
            <div class="text-sm text-gray-500">CPF: {{ selectedStudent?.cpf }}</div>
            <div class="text-sm text-gray-500">Nascimento: {{ formatDate(selectedStudent?.birth_date) }}</div>
            <div v-if="selectedStudent?.email" class="text-sm text-gray-500">Email: {{ selectedStudent.email }}</div>
            <div v-if="selectedStudent?.phone" class="text-sm text-gray-500">Telefone: {{ selectedStudent.phone }}</div>
            <div v-if="selectedStudent?.birth_certificate_number" class="text-sm text-gray-500">Certidão: {{ selectedStudent.birth_certificate_number }}</div>
            <div v-if="selectedStudent?.notes" class="text-sm text-gray-500">Notas: {{ selectedStudent.notes }}</div>
          </div>
        </template>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { EllipsisVerticalIcon } from '@heroicons/vue/20/solid';
import { ref } from 'vue';

const props = defineProps({
  enrollments: {
    type: Array,
    default: () => []
  }
});

const showPhotoModal = ref(false);
const selectedStudent = ref(null);
const loadingStudent = ref(false);

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

async function openPhotoModal(student) {
  selectedStudent.value = null;
  loadingStudent.value = true;
  showPhotoModal.value = true;
  
  try {
    const response = await fetch(`/api/students/${student.id}`);
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const data = await response.json();
    selectedStudent.value = data;
  } catch (error) {
    console.error('Erro ao carregar dados do aluno:', error);
    // Em caso de erro, usa os dados básicos que já temos
    selectedStudent.value = student;
  } finally {
    loadingStudent.value = false;
  }
}
function closePhotoModal() {
  showPhotoModal.value = false;
  selectedStudent.value = null;
  loadingStudent.value = false;
}
function formatDate(dateString) {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('pt-BR');
}
</script> 