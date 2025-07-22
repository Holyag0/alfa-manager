<template>
  <div class="space-y-6">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Informações Adicionais</h2>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Alergias</label>
      <textarea v-model="form.allergies" class="w-full border rounded px-3 py-2" rows="2"></textarea>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Medicamentos em uso</label>
      <textarea v-model="form.medications" class="w-full border rounded px-3 py-2" rows="2"></textarea>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Histórico Escolar</label>
      <textarea v-model="form.school_history" class="w-full border rounded px-3 py-2" rows="2"></textarea>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
      <textarea v-model="form.notes" class="w-full border rounded px-3 py-2" rows="2"></textarea>
    </div>
    <button class="mt-6 bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 transition">Salvar Informações</button>
  </div>
</template>
<script setup>
import { ref, watch } from 'vue';
const props = defineProps({ student: Object });
const form = ref({
  allergies: props.student?.allergies || '',
  medications: props.student?.medications || '',
  school_history: props.student?.school_history || '',
  notes: props.student?.notes || ''
});
watch(() => props.student, (val) => {
  form.value = {
    allergies: val?.allergies || '',
    medications: val?.medications || '',
    school_history: val?.school_history || '',
    notes: val?.notes || ''
  };
});
</script> 