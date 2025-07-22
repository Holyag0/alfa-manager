<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 mx-4">
      <h3 class="text-lg font-semibold mb-4">Confirmar Remoção</h3>
      <p class="text-gray-600 mb-6">
        Tem certeza que deseja remover <strong>{{ guardian?.name }}</strong> desta matrícula?
      </p>
      <div class="flex justify-end space-x-2">
        <button 
          @click="$emit('close')"
          class="px-4 py-2 text-gray-600 hover:text-gray-800"
          :disabled="loading"
        >
          Cancelar
        </button>
        <button 
          @click="$emit('confirm')"
          class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
          :disabled="loading"
        >
          {{ loading ? 'Removendo...' : 'Remover' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  guardian: Object,
  loading: Boolean
})

defineEmits(['close', 'confirm'])
</script> 