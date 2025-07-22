<template>
  <ul class="space-y-3">
    <li v-for="guardian in guardians" :key="guardian.id" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
      <div class="flex justify-between items-start">
        <div class="flex-1">
          <h3 class="font-medium text-gray-900">{{ guardian.name }}</h3>
          <p class="text-sm text-gray-600">{{ guardian.email }} • {{ guardian.phone }}</p>
          <div class="mt-2 flex items-center space-x-2">
            <span class="text-xs text-gray-500">Tipo de responsável:</span>
            <select 
              :value="guardian.guardian_type" 
              @change="$emit('update-guardian-type', guardian.id, $event.target.value)"
              class="text-xs border-gray-300 rounded px-2 py-1"
              :disabled="loading"
            >
              <option value="">Selecione...</option>
              <option value="titular">Titular</option>
              <option value="suplente">Suplente</option>
              <option value="financeiro">Financeiro</option>
              <option value="emergencia">Emergência</option>
            </select>
          </div>
        </div>
        <button 
          @click="$emit('remove', guardian)"
          class="text-red-600 hover:text-red-800 text-sm"
          :disabled="loading"
        >
          Remover
        </button>
      </div>
    </li>
  </ul>
</template>

<script setup>
defineProps({
  guardians: Array,
  loading: Boolean
})

defineEmits(['update-guardian-type', 'remove'])
</script> 