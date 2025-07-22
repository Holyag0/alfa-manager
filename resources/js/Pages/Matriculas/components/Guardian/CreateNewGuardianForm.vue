<template>
  <div class="space-y-4">
    <!-- Dados pessoais -->
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
        <input
          v-model="form.name"
          @input="emitFormData"
          type="text"
          required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          placeholder="Nome completo"
        >
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">CPF *</label>
        <input
          v-model="form.cpf"
          @input="emitFormData"
          type="text"
          required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          placeholder="000.000.000-00"
        >
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
        <input
          v-model="form.email"
          @input="emitFormData"
          type="email"
          required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          placeholder="email@exemplo.com"
        >
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone *</label>
        <input
          v-model="form.phone"
          @input="emitFormData"
          type="text"
          required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          placeholder="(00) 00000-0000"
        >
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">RG</label>
        <input
          v-model="form.rg"
          @input="emitFormData"
          type="text"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          placeholder="00.000.000-0"
        >
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Data de Nascimento</label>
        <input
          v-model="form.birth_date"
          @input="emitFormData"
          type="date"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Estado Civil</label>
        <select
          v-model="form.marital_status"
          @change="emitFormData"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
          <option value="">Selecione...</option>
          <option value="solteiro">Solteiro(a)</option>
          <option value="casado">Casado(a)</option>
          <option value="divorciado">Divorciado(a)</option>
          <option value="viuvo">Viúvo(a)</option>
          <option value="uniao_estavel">União Estável</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Ocupação</label>
        <input
          v-model="form.occupation"
          @input="emitFormData"
          type="text"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          placeholder="Profissão"
        >
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
      <textarea
        v-model="form.notes"
        @input="emitFormData"
        rows="3"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        placeholder="Informações adicionais..."
      ></textarea>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue'

const emit = defineEmits(['form-data'])

// Formulário reativo
const form = reactive({
  name: '',
  cpf: '',
  email: '',
  phone: '',
  rg: '',
  birth_date: '',
  marital_status: '',
  occupation: '',
  notes: '',
})

// Função para emitir dados do formulário
const emitFormData = () => {
  // Só emite se os campos obrigatórios estão preenchidos
  if (form.name && form.cpf && form.email && form.phone) {
    emit('form-data', { ...form })
  } else {
    emit('form-data', null)
  }
}

// Watch para mudanças no formulário
watch(form, emitFormData, { deep: true })
</script> 