<template>
  <nav aria-label="Progress" class="px-6">
    <ol class="flex items-center justify-between">
      <li v-for="(label, idx) in steps" :key="label" class="relative flex-1 flex items-center">
        <!-- Círculo do Step -->
        <div class="flex items-center">
          <div :class="[
            'flex items-center justify-center w-12 h-12 rounded-full border-4 transition-all duration-300 shadow-lg',
            idx + 1 === current 
              ? 'border-blue-600 bg-blue-600 text-white transform scale-110' 
              : idx + 1 < current 
                ? 'border-green-500 bg-green-500 text-white' 
                : 'border-gray-300 bg-white text-gray-400'
          ]">
            <span v-if="idx + 1 < current" class="text-lg">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
              </svg>
            </span>
            <span v-else class="font-bold text-lg">{{ idx + 1 }}</span>
          </div>
        </div>

        <!-- Label -->
        <div class="ml-4 min-w-0 flex-1">
          <p :class="[
            'text-sm font-semibold transition-colors duration-300',
            idx + 1 === current ? 'text-blue-600' : idx + 1 < current ? 'text-green-600' : 'text-gray-400'
          ]">
            {{ label }}
          </p>
          <p class="text-xs text-gray-500 mt-1">
            <span v-if="idx + 1 < current">Concluído</span>
            <span v-else-if="idx + 1 === current">Em andamento</span>
            <span v-else>Pendente</span>
          </p>
        </div>

        <!-- Linha conectora -->
        <div v-if="idx < steps.length - 1" :class="[
          'absolute top-6 left-12 w-full h-1 transition-all duration-500',
          idx + 1 < current ? 'bg-green-500' : 'bg-gray-300'
        ]" style="transform: translateX(1rem);"></div>
      </li>
    </ol>
  </nav>
</template>

<script setup>
const props = defineProps({
  current: { type: Number, required: true },
  steps: { type: Array, required: true }
});
</script>