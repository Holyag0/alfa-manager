<template>
  <form @submit.prevent="submit" class="space-y-4 bg-white p-6 rounded shadow">
    <div>
      <label class="block text-sm font-medium mb-1">Aluno</label>
      <select v-model="form.student_id" class="form-select w-full">
        <option value="">Selecione um aluno</option>
        <option v-for="student in students" :key="student.id" :value="student.id">{{ student.name }}</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Responsável</label>
      <select v-model="form.guardian_id" class="form-select w-full">
        <option value="">Selecione um responsável</option>
        <option v-for="guardian in guardians" :key="guardian.id" :value="guardian.id">{{ guardian.name }}</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Turma</label>
      <select v-model="form.classroom_id" class="form-select w-full">
        <option value="">Selecione uma turma</option>
        <option v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">{{ classroom.name }}</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Status</label>
      <select v-model="form.status" class="form-select w-full">
        <option value="active">Ativo</option>
        <option value="pending">Pendente</option>
        <option value="cancelled">Cancelado</option>
        <option value="inactive">Inativo</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Data da Matrícula</label>
      <input type="date" v-model="form.enrollment_date" class="form-input w-full" />
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Observações</label>
      <textarea v-model="form.notes" class="form-textarea w-full" rows="2"></textarea>
    </div>
    <div class="flex justify-end">
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
        {{ isEdit ? 'Salvar Alterações' : 'Salvar Matrícula' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, toRefs } from 'vue';

const props = defineProps({
  students: { type: Array, default: () => [] },
  guardians: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
  initialEnrollment: { type: Object, default: null },
  isEdit: { type: Boolean, default: false },
});

const emit = defineEmits(['submitted']);

const form = useForm({
  student_id: '',
  guardian_id: '',
  classroom_id: '',
  status: 'active',
  enrollment_date: '',
  notes: '',
});

// Preencher o formulário se estiver em modo edição
if (props.isEdit && props.initialEnrollment) {
  form.student_id = props.initialEnrollment.student_id || '';
  form.guardian_id = props.initialEnrollment.guardian_id || '';
  form.classroom_id = props.initialEnrollment.classroom_id || '';
  form.status = props.initialEnrollment.status || 'active';
  form.enrollment_date = props.initialEnrollment.enrollment_date || '';
  form.notes = props.initialEnrollment.notes || '';
}

function submit() {
  if (props.isEdit && props.initialEnrollment) {
    form.put(route('matriculas.update', props.initialEnrollment.id), {
      onSuccess: () => emit('submitted'),
    });
  } else {
    form.post(route('matriculas.store'), {
      onSuccess: () => emit('submitted'),
    });
  }
}
</script> 