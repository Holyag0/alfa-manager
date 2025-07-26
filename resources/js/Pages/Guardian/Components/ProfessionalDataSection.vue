<template>
  <div class="bg-white rounded-xl p-6 border border-blue-200 shadow-sm">
    <h4 class="flex items-center text-lg font-semibold text-gray-800 mb-6">
      <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0H8m8 0v2a2 2 0 01-2 2H10a2 2 0 01-2-2V6"/>
        </svg>
      </div>
      Dados Profissionais
    </h4>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Profissão</label>
        <input 
          :value="modelValue.occupation"
          @input="updateField('occupation', $event.target.value)"
          type="text" 
          placeholder="Ex: Engenheiro, Professor, etc." 
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
          :class="{ 'border-red-300': errors.occupation }"
        />
        <p v-if="errors.occupation" class="mt-1 text-sm text-red-600">{{ errors.occupation }}</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Local de Trabalho</label>
        <input 
          :value="modelValue.workplace"
          @input="updateField('workplace', $event.target.value)"
          type="text" 
          placeholder="Nome da empresa/instituição" 
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
          :class="{ 'border-red-300': errors.workplace }"
        />
        <p v-if="errors.workplace" class="mt-1 text-sm text-red-600">{{ errors.workplace }}</p>
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