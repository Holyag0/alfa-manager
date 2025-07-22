<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
      <h2 class="text-lg font-bold mb-2">
        <slot name="title">Confirmação</slot>
      </h2>
      <div class="mb-4">
        <slot name="message">Tem certeza que deseja continuar?</slot>
              </div>
      <div class="flex justify-end space-x-2">
        <button @click="$emit('cancel')" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Cancelar</button>
        <button @click="$emit('confirm')" class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700">Confirmar</button>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  show: { type: Boolean, default: false }
});
</script>