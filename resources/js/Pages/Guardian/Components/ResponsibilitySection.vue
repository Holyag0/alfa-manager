<template>
  <div class="bg-white rounded-xl p-6 border border-blue-200 shadow-sm">
    <h4 class="flex items-center text-lg font-semibold text-gray-800 mb-6">
      <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
        </svg>
      </div>
      Responsabilidade
    </h4>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Responsável</label>
        <select 
          :value="modelValue.guardian_type"
          @change="updateField('guardian_type', $event.target.value)"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
          :class="{ 'border-red-300': errors.guardian_type }"
        >
          <option value="titular">Titular</option>
          <option value="suplente">Suplente</option>
          <option value="financeiro">Financeiro</option>
          <option value="emergencia">Emergência</option>
        </select>
        <p v-if="errors.guardian_type" class="mt-1 text-sm text-red-600">{{ errors.guardian_type }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Parentesco/Relacionamento</label>
        <select 
          :value="modelValue.relationship"
          @change="updateField('relationship', $event.target.value)"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
          :class="{ 'border-red-300': errors.relationship }"
        >
          <option value="">Selecione</option>
          <option value="pai">Pai</option>
          <option value="mae">Mãe</option>
          <option value="avo">Avô</option>
          <option value="avo_feminino">Avó</option>
          <option value="tio">Tio</option>
          <option value="tia">Tia</option>
          <option value="padrasto">Padrasto</option>
          <option value="madrasta">Madrasta</option>
          <option value="tutor">Tutor Legal</option>
          <option value="outro">Outro</option>
        </select>
        <p v-if="errors.relationship" class="mt-1 text-sm text-red-600">{{ errors.relationship }}</p>
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