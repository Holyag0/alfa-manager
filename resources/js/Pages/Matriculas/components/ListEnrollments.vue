<template>
  <ul role="list" class="divide-y divide-gray-100">
    <li v-for="enrollment in enrollments" :key="enrollment.id" class="flex justify-between gap-x-6 py-5">
      <div class="flex min-w-0 gap-x-4">
        <div>
          <template v-if="enrollment.student?.photo">
            <img :src="`/storage/${enrollment.student.photo}`" alt="Foto do aluno" class="size-12 rounded-full object-cover border border-gray-200 cursor-pointer transition hover:ring-2 hover:ring-blue-400" @click="openPhotoModal(enrollment.student, enrollment)" />
          </template>
          <template v-else>
            <div class="size-12 flex-none rounded-full bg-blue-100 flex items-center justify-center text-xl font-bold text-blue-700 cursor-pointer transition hover:ring-2 hover:ring-blue-400" @click="openPhotoModal(enrollment.student, enrollment)">
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
        <div class="flex flex-col items-end gap-y-1">
          <span :class="statusClass(enrollment.status)">{{ statusLabel(enrollment.status) }}</span>
          <span :class="processClass(enrollment.process)" class="text-xs px-2 py-1 rounded-full">
            {{ processLabel(enrollment.process) }}
          </span>
        </div>
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

  <!-- Modal de informações da matrícula -->
  <transition name="fade">
    <div v-if="showPhotoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 overflow-y-auto p-4">
      <div class="bg-white rounded-xl shadow-lg p-6 relative flex flex-col min-w-[340px] max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <button @click="closePhotoModal" class="absolute top-2 right-2 text-gray-500 hover:text-red-500 bg-white bg-opacity-80 rounded-full p-1 shadow z-10">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <template v-if="loadingStudent">
          <div class="flex flex-col items-center justify-center h-80">
            <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-green-500 mb-4"></div>
            <div class="text-green-700 font-semibold">Carregando informações...</div>
          </div>
        </template>
        <template v-else-if="selectedStudent">
          <!-- Foto e Informações Básicas -->
          <div class="flex flex-col items-center mb-6">
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
          </div>

          <!-- Informações Financeiras -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
              <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
              </svg>
              Informações Financeiras
            </h3>

            <template v-if="loadingFinancial">
              <div class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mr-3"></div>
                <span class="text-gray-600">Carregando informações financeiras...</span>
              </div>
            </template>

            <template v-else-if="financialSummary">
              <!-- Serviços Contratados -->
              <div v-if="servicesList && servicesList.length > 0" class="space-y-3">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Serviços Contratados</h4>
                <div class="space-y-2 max-h-60 overflow-y-auto">
                  <div v-for="service in servicesList" :key="service.id" class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <div class="flex justify-between items-start">
                      <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ service.name || service.description }}</p>
                        <p v-if="service.description && service.description !== service.name" class="text-xs text-gray-500 mt-1">{{ service.description }}</p>
                        <p class="text-xs text-gray-500 mt-1 font-semibold">{{ formatCurrency(service.amount) }}</p>
                      </div>
                      <span :class="[
                        'px-2 py-1 rounded-full text-xs font-medium',
                        service.status === 'paid' ? 'bg-green-100 text-green-800' : 
                        service.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                        'bg-gray-100 text-gray-800'
                      ]">
                        {{ getStatusLabel(service.status) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Mensagem quando não há serviços -->
              <div v-else class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                  <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <div>
                    <p class="text-sm font-medium text-blue-800 mb-1">Nenhum serviço contratado</p>
                    <p class="text-sm text-blue-700">
                      Esta matrícula ainda não possui serviços contratados. Para adicionar serviços e visualizar informações financeiras completas, acesse a aba <strong>Financeiro</strong> na edição da matrícula.
                    </p>
                    <a :href="route('matriculas.edit', selectedEnrollment?.id)" class="inline-flex items-center mt-2 text-sm font-medium text-blue-600 hover:text-blue-800">
                      Ver Financeiro
                      <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                      </svg>
                    </a>
                  </div>
                </div>
              </div>
            </template>

            <template v-else>
              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-sm text-yellow-800">
                  Não foi possível carregar as informações financeiras. Tente novamente ou acesse a aba Financeiro na edição da matrícula.
                </p>
              </div>
            </template>
          </div>
        </template>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { EllipsisVerticalIcon } from '@heroicons/vue/20/solid';
import { ref, computed } from 'vue';

const props = defineProps({
  enrollments: {
    type: Array,
    default: () => []
  }
});

const showPhotoModal = ref(false);
const selectedStudent = ref(null);
const selectedEnrollment = ref(null);
const loadingStudent = ref(false);
const loadingFinancial = ref(false);
const financialSummary = ref(null);

// Computed para obter lista de serviços
const servicesList = computed(() => {
  if (!financialSummary.value?.services) return [];
  return financialSummary.value.services.map(service => ({
    id: service.id,
    name: service.name || service.full_description || service.description, // Priorizar nome do serviço
    description: service.description, // Descrição adicional (se houver)
    full_description: service.full_description, // Descrição completa original
    amount: service.amount,
    formatted_amount: service.formatted_amount,
    status: service.status,
    status_label: service.status_label || getStatusLabel(service.status)
  }));
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

async function openPhotoModal(student, enrollment = null) {
  selectedStudent.value = null;
  selectedEnrollment.value = enrollment;
  loadingStudent.value = true;
  loadingFinancial.value = true;
  financialSummary.value = null;
  showPhotoModal.value = true;
  
  try {
    // Buscar dados completos do aluno
    const studentResponse = await fetch(`/api/students/${student.id}`);
    
    if (!studentResponse.ok) {
      throw new Error(`HTTP error! status: ${studentResponse.status}`);
    }
    
    const studentData = await studentResponse.json();
    selectedStudent.value = studentData;

    // Buscar serviços contratados se tiver enrollment
    if (enrollment?.id) {
      try {
        // Buscar apenas serviços contratados (invoices do tipo 'service')
        const servicesResponse = await fetch(`/api/enrollments/${enrollment.id}/services`);
        if (servicesResponse.ok) {
          const servicesData = await servicesResponse.json();
          financialSummary.value = { services: servicesData };
        }
      } catch (error) {
        console.error('Erro ao carregar serviços contratados:', error);
      }
    }
  } catch (error) {
    console.error('Erro ao carregar dados do aluno:', error);
    // Em caso de erro, usa os dados básicos que já temos
    selectedStudent.value = student;
  } finally {
    loadingStudent.value = false;
    loadingFinancial.value = false;
  }
}
function closePhotoModal() {
  showPhotoModal.value = false;
  selectedStudent.value = null;
  selectedEnrollment.value = null;
  loadingStudent.value = false;
  loadingFinancial.value = false;
  financialSummary.value = null;
}

// Função para estilizar o processo
function processClass(process) {
  switch (process) {
    case 'reserva': return 'bg-purple-100 text-purple-800';
    case 'aguardando_pagamento': return 'bg-yellow-100 text-yellow-800';
    case 'aguardando_documentos': return 'bg-orange-100 text-orange-800';
    case 'completa': return 'bg-green-100 text-green-800';
    case 'renovacao': return 'bg-blue-100 text-blue-800';
    case 'desistencia': return 'bg-red-100 text-red-800';
    case 'transferencia': return 'bg-indigo-100 text-indigo-800';
    default: return 'bg-gray-100 text-gray-800';
  }
}

// Função para label do processo
function processLabel(process) {
  switch (process) {
    case 'reserva': return '🎫 Reserva';
    case 'aguardando_pagamento': return '💳 Aguard. Pagamento';
    case 'aguardando_documentos': return '📄 Aguard. Documentos';
    case 'completa': return '✅ Completa';
    case 'renovacao': return '🔄 Renovação';
    case 'desistencia': return '🚫 Desistência';
    case 'transferencia': return '↗️ Transferência';
    default: return process || 'N/A';
  }
}
function formatDate(dateString) {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('pt-BR');
}

function formatCurrency(value) {
  if (!value) return 'R$ 0,00';
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value);
}

function getStatusLabel(status) {
  const labels = {
    'paid': 'Pago',
    'pending': 'Pendente',
    'overdue': 'Vencido',
    'cancelled': 'Cancelado',
    'refunded': 'Estornado'
  };
  return labels[status] || status;
}
</script> 