<template>
  <div class="bg-white shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-medium text-gray-900">Dados da Matrícula</h3>
      <p class="mt-1 text-sm text-gray-600">Gerencie o status, turma e informações da matrícula.</p>
    </div>

    <form @submit.prevent="updateMatricula" class="p-6 space-y-6">
      <!-- Status da Matrícula -->
      <div>
        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
          Status da Matrícula
        </label>
        <select 
          id="status"
          v-model="form.status" 
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          :class="{ 'border-red-300': errors.status }"
        >
          <option value="pending">🟡 Pendente</option>
          <option value="active">🟢 Ativa</option>
          <option value="suspended">🟠 Suspensa</option>
          <option value="cancelled">🔴 Cancelada</option>
          <option value="completed">✅ Concluída</option>
        </select>
        <p v-if="errors.status" class="mt-1 text-sm text-red-600">{{ errors.status }}</p>
      </div>

      <!-- Turma -->
      <div>
        <label for="classroom_id" class="block text-sm font-medium text-gray-700 mb-2">
          Turma
        </label>
        <select 
          id="classroom_id"
          v-model="form.classroom_id" 
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          :class="{ 'border-red-300': errors.classroom_id }"
        >
          <option value="">Selecione uma turma...</option>
          <option v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
            {{ classroom.name }} - {{ classroom.grade }}
          </option>
        </select>
        <p v-if="errors.classroom_id" class="mt-1 text-sm text-red-600">{{ errors.classroom_id }}</p>
      </div>

      <!-- Data da Matrícula -->
      <div>
        <label for="enrollment_date" class="block text-sm font-medium text-gray-700 mb-2">
          Data da Matrícula
        </label>
        <input 
          id="enrollment_date"
          v-model="form.enrollment_date" 
          type="date"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          :class="{ 'border-red-300': errors.enrollment_date }"
        />
        <p v-if="errors.enrollment_date" class="mt-1 text-sm text-red-600">{{ errors.enrollment_date }}</p>
      </div>

      <!-- Observações -->
      <div>
        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
          Observações
        </label>
        <textarea 
          id="notes"
          v-model="form.notes" 
          rows="4"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          :class="{ 'border-red-300': errors.notes }"
          placeholder="Observações sobre a matrícula..."
        ></textarea>
        <p v-if="errors.notes" class="mt-1 text-sm text-red-600">{{ errors.notes }}</p>
      </div>

      <!-- Botões de Ação -->
      <div class="flex items-center justify-between pt-4 border-t border-gray-200">
        <div class="flex space-x-3">
          <!-- Ações Rápidas -->
          <button 
            type="button"
            @click="changeStatus('suspended')"
            v-if="form.status === 'active'"
            class="inline-flex items-center px-3 py-2 border border-orange-300 text-sm leading-4 font-medium rounded-md text-orange-700 bg-orange-50 hover:bg-orange-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
          >
            🟠 Suspender
          </button>
          
          <button 
            type="button"
            @click="changeStatus('active')"
            v-if="form.status === 'suspended'"
            class="inline-flex items-center px-3 py-2 border border-green-300 text-sm leading-4 font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
          >
            🟢 Reativar
          </button>

          <button 
            type="button"
            @click="changeStatus('cancelled')"
            v-if="['active', 'suspended', 'pending'].includes(form.status)"
            class="inline-flex items-center px-3 py-2 border border-red-300 text-sm leading-4 font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
          >
            🔴 Cancelar
          </button>
        </div>

        <!-- Salvar Alterações -->
        <button 
          type="submit"
          :disabled="processing"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
        >
          <span v-if="processing">Salvando...</span>
          <span v-else>Salvar Alterações</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  enrollment: Object,
  classrooms: Array
})

const processing = ref(false)
const errors = ref({})

const form = reactive({
  status: props.enrollment.status,
  classroom_id: props.enrollment.classroom_id,
  enrollment_date: props.enrollment.enrollment_date,
  notes: props.enrollment.notes || ''
})

const updateMatricula = () => {
  processing.value = true
  errors.value = {}

  router.patch(route('matriculas.update', props.enrollment.id), form, {
    preserveScroll: true,
    onSuccess: () => {
      // Success message será mostrada via flash
    },
    onError: (responseErrors) => {
      errors.value = responseErrors
    },
    onFinish: () => {
      processing.value = false
    }
  })
}

const changeStatus = (newStatus) => {
  form.status = newStatus
  updateMatricula()
}
</script> 