<template>
  <div class="bg-white rounded-xl p-6 border border-blue-200 shadow-sm">
    <h4 class="flex items-center text-lg font-semibold text-gray-800 mb-6">
      <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
      </div>
      Observações Adicionais
    </h4>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
      <textarea 
        :value="modelValue.notes"
        @input="updateField('notes', $event.target.value)"
        rows="4"
        placeholder="Informações adicionais relevantes sobre o responsável..." 
        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 resize-none"
        :class="{ 'border-red-300': errors.notes }"
      ></textarea>
      <p v-if="errors.notes" class="mt-1 text-sm text-red-600">{{ errors.notes }}</p>
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