<template>
  <div class="bg-white rounded-xl p-6 border border-blue-200 shadow-sm">
    <h4 class="flex items-center text-lg font-semibold text-gray-800 mb-6">
      <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
      </div>
      Dados Pessoais
    </h4>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-2">Nome completo *</label>
        <input 
          :value="modelValue.name"
          @input="updateField('name', $event.target.value)"
          type="text" 
          placeholder="Nome completo do responsável" 
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
          :class="{ 'border-red-300': errors.name }"
          required 
        />
        <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">CPF *</label>
        <input 
          :value="modelValue.cpf"
          @input="updateField('cpf', $event.target.value)"
          type="text" 
          placeholder="000.000.000-00" 
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
          :class="{ 'border-red-300': errors.cpf }"
          required 
        />
        <p v-if="errors.cpf" class="mt-1 text-sm text-red-600">{{ errors.cpf }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">RG</label>
        <input 
          :value="modelValue.rg"
          @input="updateField('rg', $event.target.value)"
          type="text" 
          placeholder="00.000.000-0" 
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
          :class="{ 'border-red-300': errors.rg }"
        />
        <p v-if="errors.rg" class="mt-1 text-sm text-red-600">{{ errors.rg }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Data de Nascimento</label>
        <input 
          :value="modelValue.birth_date"
          @input="updateField('birth_date', $event.target.value)"
          type="date" 
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
          :class="{ 'border-red-300': errors.birth_date }"
        />
        <p v-if="errors.birth_date" class="mt-1 text-sm text-red-600">{{ errors.birth_date }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Gênero</label>
        <select 
          :value="modelValue.gender"
          @change="updateField('gender', $event.target.value)"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
          :class="{ 'border-red-300': errors.gender }"
        >
          <option value="">Selecione</option>
          <option value="masculino">Masculino</option>
          <option value="feminino">Feminino</option>
          <option value="outro">Outro</option>
          <option value="prefiro_nao_informar">Prefiro não informar</option>
        </select>
        <p v-if="errors.gender" class="mt-1 text-sm text-red-600">{{ errors.gender }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Estado Civil</label>
        <select 
          :value="modelValue.marital_status"
          @change="updateField('marital_status', $event.target.value)"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
          :class="{ 'border-red-300': errors.marital_status }"
        >
          <option value="">Selecione</option>
          <option value="solteiro">Solteiro(a)</option>
          <option value="casado">Casado(a)</option>
          <option value="divorciado">Divorciado(a)</option>
          <option value="viuvo">Viúvo(a)</option>
          <option value="uniao_estavel">União Estável</option>
        </select>
        <p v-if="errors.marital_status" class="mt-1 text-sm text-red-600">{{ errors.marital_status }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:modelValue'])

const updateField = (field, value) => {
  emit('update:modelValue', {
    ...props.modelValue,
    [field]: value
  })
}
</script> 