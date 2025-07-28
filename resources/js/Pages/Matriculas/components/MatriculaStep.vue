<template>
  <div class="p-8">
    <!-- Header do Step -->
    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center">
        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mr-4">
          <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </div>
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Finalizar Matrícula</h2>
          <p class="text-gray-600">Complete as informações da matrícula</p>
        </div>
      </div>
      <button 
        @click="$emit('back')" 
        class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Voltar
      </button>
    </div>

    <!-- Resumo das Informações -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6 mb-8">
      <h3 class="flex items-center text-lg font-semibold text-gray-900 mb-4">
        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
        Resumo da Matrícula
      </h3>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg p-4 border border-blue-100">
          <div class="flex items-center mb-2">
            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
            <h4 class="font-semibold text-gray-900">Responsável</h4>
          </div>
          <p class="text-gray-800 font-medium">{{ responsavel.name }}</p>
          <p class="text-sm text-gray-600">CPF: {{ responsavel.cpf }}</p>
        </div>
        
        <div class="bg-white rounded-lg p-4 border border-green-100">
          <div class="flex items-center mb-2">
            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
              </svg>
            </div>
            <h4 class="font-semibold text-gray-900">Aluno</h4>
          </div>
          <p class="text-gray-800 font-medium">{{ aluno.name }}</p>
          <p class="text-sm text-gray-600">CPF: {{ aluno.cpf }}</p>
        </div>
      </div>
    </div>

    <!-- Formulário de Matrícula -->
    <div class="bg-white border border-gray-200 rounded-xl p-6">
      <form @submit.prevent="submitMatricula" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Turma -->
          <div>
            <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
              <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
              </svg>
              Turma *
            </label>
            <div class="relative">
              <select 
                v-model="form.classroom_id" 
                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200 appearance-none bg-white" 
                required
              >
                <option value="">Selecione uma turma</option>
                <option v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                  {{ classroom.name }}
                </option>
              </select>
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </div>
            </div>
          </div>

          <!-- Status -->
          <div>
            <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
              <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Status
            </label>
            <div class="relative">
              <select 
                v-model="form.status" 
                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200 appearance-none bg-white"
              >
                <option value="active">✅ Ativo</option>
                <option value="pending">⏳ Pendente</option>
                <option value="cancelled">❌ Cancelado</option>
                <option value="inactive">🔒 Inativo</option>
              </select>
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </div>
            </div>
          </div>

          <!-- Processo -->
          <div>
            <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
              <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
              Processo
            </label>
            <div class="relative">
              <select 
                v-model="form.process" 
                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200 appearance-none bg-white"
              >
                                 <option value="reserva">🎫 Reserva</option>
                 <option value="aguardando_pagamento">💳 Aguardando Pagamento</option>
                 <option value="aguardando_documentos">📄 Aguardando Documentos</option>
                 <option value="completa">✅ Completa</option>
                 <option value="renovacao">🔄 Renovação</option>
                 <option value="desistencia">🚫 Desistência</option>
                 <option value="transferencia">↗️ Transferência</option>
              </select>
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </div>
            </div>
          </div>

          <!-- Data da Matrícula -->
          <div>
            <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
              <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              Data da Matrícula
            </label>
            <input 
              type="date" 
              v-model="form.enrollment_date" 
              class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200" 
            />
          </div>
        </div>

        <!-- Observações -->
        <div>
          <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Observações
          </label>
          <textarea 
            v-model="form.notes" 
            class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200" 
            rows="3"
            placeholder="Informações adicionais sobre a matrícula..."
          ></textarea>
        </div>

        <!-- Botões de Ação -->
        <div class="flex justify-end pt-6 border-t border-gray-200">
          <button 
            type="submit" 
            class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Finalizar Matrícula
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  aluno: { type: Object, required: true },
  responsavel: { type: Object, required: true }
});

const emit = defineEmits(['finish', 'back']);

const classrooms = ref([]);

const form = useForm({
  student_id: props.aluno.id,
  guardian_id: props.responsavel.id,
  classroom_id: '',
  status: 'active',
  process: 'completa',
  enrollment_date: new Date().toISOString().split('T')[0],
  notes: ''
});

onMounted(async () => {
  // Buscar turmas disponíveis
  const response = await fetch('/api/classrooms');
  classrooms.value = await response.json();
});

function submitMatricula() {
  form.post(route('matriculas.wizard.complete'), {
    preserveState: true,
    replace: true,
    onSuccess: () => {
      // Pode emitir evento ou redirecionar
      window.location = route('matriculas.index');
    }
  });
}
</script>