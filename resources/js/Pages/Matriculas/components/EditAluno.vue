<template>
  <div class="space-y-6">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Dados do Aluno</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
        <input v-model="form.name" type="text" class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Data de Nascimento</label>
        <input v-model="form.birth_date" type="date" class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
        <input v-model="form.cpf" type="text" class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">RG</label>
        <input v-model="form.rg" type="text" class="w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Foto</label>
        <!-- Slot para upload/crop de foto -->
        <div class="w-24 h-32 bg-gray-100 rounded-xl flex items-center justify-center">Foto 3x4</div>
      </div>
    </div>
    <button class="mt-6 bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 transition">Salvar Dados do Aluno</button>
  </div>
</template>
<script setup>
import { ref, watch } from 'vue';
const props = defineProps({ student: Object });
const form = ref({ ...props.student });
watch(() => props.student, (val) => { form.value = { ...val }; });
</script> 