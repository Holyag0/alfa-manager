<template>
  <div class="overflow-x-auto bg-white rounded shadow">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Aluno</th>
          <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Responsável</th>
          <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Turma</th>
          <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
          <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
          <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Ações</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr v-for="enrollment in enrollments" :key="enrollment.id">
          <td class="px-4 py-2">{{ enrollment.student?.name }}</td>
          <td class="px-4 py-2">{{ enrollment.guardian?.name }}</td>
          <td class="px-4 py-2">{{ enrollment.classroom?.name }}</td>
          <td class="px-4 py-2">
            <span :class="statusClass(enrollment.status)">{{ statusLabel(enrollment.status) }}</span>
          </td>
          <td class="px-4 py-2">{{ enrollment.enrollment_date }}</td>
          <td class="px-4 py-2 text-center space-x-2">
            <Link :href="route('matriculas.edit', enrollment.id)" class="text-blue-600 hover:underline">Editar</Link>
            <button @click="openCancelModal(enrollment)" class="text-red-600 hover:underline">Cancelar</button>
            <button @click="openChangeClassroomModal(enrollment)" class="text-yellow-600 hover:underline">Trocar Turma</button>
          </td>
        </tr>
        <tr v-if="!enrollments.length">
          <td colspan="6" class="text-center py-4 text-gray-400">Nenhuma matrícula encontrada.</td>
        </tr>
      </tbody>
    </table>
    <!-- Modal de confirmação para cancelar -->
    <ConfirmationModal :show="showCancelModal" @cancel="showCancelModal = false" @confirm="confirmCancel">
      <template #title>Cancelar Matrícula</template>
      <template #message>
        <div class="space-y-2">
          <p>{{ cancelModalMessage }}</p>
          <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
            <p class="text-sm text-yellow-800">
              <strong>Atenção:</strong> Esta ação não poderá ser desfeita.
            </p>
          </div>
        </div>
      </template>
    </ConfirmationModal>
    <!-- Modal de confirmação para trocar turma -->
    <ConfirmationModal :show="showChangeClassroomModal" @cancel="showChangeClassroomModal = false" @confirm="confirmChangeClassroom">
      <template #title>Trocar Turma</template>
      <template #message>
        <div class="space-y-3">
          <p v-if="selectedEnrollment">{{ changeClassroomModalMessage }}</p>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Selecione a nova turma:</label>
            <select v-model="selectedNewClassroomId" class="form-select w-full border-gray-300 rounded-md shadow-sm">
              <option value="">Selecione uma turma</option>
              <option v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                {{ classroom.name }}
              </option>
            </select>
          </div>
          <div v-if="selectedNewClassroomId && selectedNewClassroomId == selectedEnrollment?.classroom_id" class="text-red-500 text-sm">
            ⚠️ Selecione uma turma diferente da atual.
          </div>
          <div v-if="selectedNewClassroomId && selectedNewClassroomId != selectedEnrollment?.classroom_id" class="bg-blue-50 border-l-4 border-blue-400 p-3 rounded">
            <p class="text-sm text-blue-800">
              <strong>Confirmação:</strong> {{ finalChangeMessage }}
            </p>
          </div>
        </div>
      </template>
    </ConfirmationModal>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import ConfirmationModal from '@/Shared/ConfirmationModal.vue';

const props = defineProps({
  enrollments: {
    type: Array,
    default: () => []
  },
  classrooms: {
    type: Array,
    default: () => []
  }
});

const showCancelModal = ref(false);
const showChangeClassroomModal = ref(false);
const selectedEnrollment = ref(null);
const selectedNewClassroomId = ref('');

// Computed properties para as mensagens dos modais
const cancelModalMessage = computed(() => {
  if (!selectedEnrollment.value) return '';
  
  const currentStatus = statusLabel(selectedEnrollment.value.status);
  return `Tem certeza que deseja mudar o status de "${currentStatus}" para "Cancelado"?`;
});

const changeClassroomModalMessage = computed(() => {
  if (!selectedEnrollment.value) return '';
  
  const currentClassroom = selectedEnrollment.value.classroom?.name || 'Não definida';
  return `Turma atual: ${currentClassroom}`;
});

const finalChangeMessage = computed(() => {
  if (!selectedEnrollment.value || !selectedNewClassroomId.value) return '';
  
  const currentClassroom = selectedEnrollment.value.classroom?.name || 'Não definida';
  const newClassroom = classrooms.find(c => c.id == selectedNewClassroomId.value)?.name || 'Não encontrada';
  
  return `Trocar de "${currentClassroom}" para "${newClassroom}".`;
});

function openCancelModal(enrollment) {
  selectedEnrollment.value = enrollment;
  showCancelModal.value = true;
}

function openChangeClassroomModal(enrollment) {
  selectedEnrollment.value = enrollment;
  selectedNewClassroomId.value = '';
  showChangeClassroomModal.value = true;
}

function confirmCancel() {
  if (selectedEnrollment.value) {
    router.post(route('matriculas.cancelar', selectedEnrollment.value.id), {}, {
      onFinish: () => {
        showCancelModal.value = false;
        selectedEnrollment.value = null;
      }
    });
  }
}

function confirmChangeClassroom() {
  if (
    selectedEnrollment.value &&
    selectedNewClassroomId.value &&
    selectedNewClassroomId.value != selectedEnrollment.value.classroom_id
  ) {
    router.post(route('matriculas.trocar-turma', selectedEnrollment.value.id), {
      classroom_id: selectedNewClassroomId.value
    }, {
      onFinish: () => {
        showChangeClassroomModal.value = false;
        selectedEnrollment.value = null;
        selectedNewClassroomId.value = '';
      }
    });
  }
}

function statusClass(status) {
  switch (status) {
    case 'active': return 'text-green-600 font-semibold';
    case 'pending': return 'text-yellow-600 font-semibold';
    case 'cancelled': return 'text-red-600 font-semibold';
    default: return '';
  }
}

function statusLabel(status) {
  switch (status) {
    case 'active': return 'Ativo';
    case 'pending': return 'Pendente';
    case 'cancelled': return 'Cancelado';
    case 'inactive': return 'Inativo';
    case 'suspended': return 'Suspenso';
    default: return status;
  }
}
</script> 