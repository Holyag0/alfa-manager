<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
        </div>
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Editar Dados da Turma</h3>
          <p class="text-sm text-gray-600">Atualize informações básicas da turma</p>
        </div>
      </div>
    </div>

    <form @submit.prevent="$emit('submit', form)" class="p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Nome -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
            Nome da Turma <span class="text-red-500">*</span>
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          />
        </div>

        <!-- Ano -->
        <div>
          <label for="year" class="block text-sm font-medium text-gray-700 mb-1">
            Ano <span class="text-red-500">*</span>
          </label>
          <input
            id="year"
            v-model="form.year"
            type="text"
            required
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          />
        </div>

        <!-- Turno -->
        <div>
          <label for="shift" class="block text-sm font-medium text-gray-700 mb-1">
            Turno <span class="text-red-500">*</span>
          </label>
          <select
            id="shift"
            v-model="form.shift"
            required
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          >
            <option value="Manhã">Manhã</option>
            <option value="Tarde">Tarde</option>
            <option value="Noite">Noite</option>
            <option value="Integral">Integral</option>
          </select>
        </div>

        <!-- Máximo de Alunos -->
        <div>
          <label for="max_students" class="block text-sm font-medium text-gray-700 mb-1">
            Máximo de Alunos <span class="text-red-500">*</span>
          </label>
          <input
            id="max_students"
            v-model.number="form.max_students"
            type="number"
            min="1"
            required
            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
          />
          <p v-if="classroom" class="mt-1 text-xs text-gray-500">Vagas disponíveis: {{ classroom.available_slots || 0 }}</p>
        </div>

        <!-- Status -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Status
          </label>
          <div class="flex items-center space-x-4">
            <label class="inline-flex items-center">
              <input
                v-model="form.is_active"
                type="checkbox"
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
              />
              <span class="ml-2 text-sm text-gray-700">Turma Ativa</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Botões -->
      <div class="mt-6 flex justify-end space-x-3 pt-6 border-t border-gray-200">
        <button
          type="button"
          @click="$emit('cancel')"
          class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Cancelar
        </button>
        <button
          type="submit"
          :disabled="saving"
          class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
        >
          <span v-if="saving">Salvando...</span>
          <span v-else>Salvar Alterações</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  classroom: {
    type: Object,
    default: null
  },
  saving: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['submit', 'cancel'])

const form = ref({
  name: '',
  year: '',
  shift: 'Manhã',
  max_students: 30,
  is_active: true
})

// Atualizar formulário quando classroom mudar
watch(() => props.classroom, (newClassroom) => {
  if (newClassroom) {
    form.value = {
      name: newClassroom.name || '',
      year: newClassroom.year || '',
      shift: newClassroom.shift || 'Manhã',
      max_students: newClassroom.max_students || 30,
      is_active: Boolean(newClassroom.is_active === true || newClassroom.is_active === 1 || newClassroom.is_active === '1')
    }
  }
}, { immediate: true })
</script>

