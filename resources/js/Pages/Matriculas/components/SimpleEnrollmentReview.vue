<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center">
      <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mr-4">
        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
      </div>
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Revisar Matrícula</h2>
        <p class="text-gray-600">Confirme as informações antes de finalizar</p>
      </div>
    </div>

    <!-- Resumo das Informações -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6">
      <h3 class="flex items-center text-lg font-semibold text-gray-900 mb-6">
        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
        Resumo da Matrícula
      </h3>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Aluno -->
        <div class="bg-white rounded-lg p-4 border border-green-100">
          <div class="flex items-center mb-3">
            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
              </svg>
            </div>
            <h4 class="font-semibold text-gray-900">Aluno</h4>
          </div>
          <p class="text-gray-800 font-medium">{{ student.name }}</p>
          <p class="text-sm text-gray-600">CPF: {{ student.cpf || 'Não informado' }}</p>
          <p class="text-sm text-gray-600">Nascimento: {{ formatDate(student.birth_date) }}</p>
        </div>
        
        <!-- Responsável -->
        <div class="bg-white rounded-lg p-4 border border-blue-100">
          <div class="flex items-center mb-3">
            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
            <h4 class="font-semibold text-gray-900">Responsável</h4>
          </div>
          <p class="text-gray-800 font-medium">{{ guardian.name }}</p>
          <p class="text-sm text-gray-600">CPF: {{ guardian.cpf || 'Não informado' }}</p>
          <p class="text-sm text-gray-600">Parentesco: {{ formatRelationship(guardian.relationship) }}</p>
        </div>

        <!-- Turma -->
        <div class="bg-white rounded-lg p-4 border border-purple-100">
          <div class="flex items-center mb-3">
            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
              <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
              </svg>
            </div>
            <h4 class="font-semibold text-gray-900">Turma</h4>
          </div>
          <p class="text-gray-800 font-medium">{{ classroom.name }}</p>
          <p class="text-sm text-gray-600">{{ classroom.year }} - {{ formatShift(classroom.shift) }}</p>
          <p class="text-sm text-gray-600">{{ classroom.vacancies_available }} vagas disponíveis</p>
        </div>
      </div>
    </div>

    <!-- Configurações da Matrícula -->
    <div class="bg-white border border-gray-200 rounded-xl p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Configurações da Matrícula</h3>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Status -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <div class="relative">
            <select 
              v-model="enrollmentConfig.status" 
              class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 appearance-none bg-white"
            >
              <option value="active">✅ Ativo</option>
              <option value="pending">⏳ Pendente</option>
              <option value="inactive">🔒 Inativo</option>
            </select>
          </div>
        </div>

        <!-- Processo -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Processo</label>
          <div class="relative">
            <select 
              v-model="enrollmentConfig.process" 
              class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 appearance-none bg-white"
            >
              <option value="completa">✅ Completa</option>
              <option value="reserva">🎫 Reserva</option>
              <option value="aguardando_pagamento">💳 Aguardando Pagamento</option>
              <option value="aguardando_documentos">📄 Aguardando Documentos</option>
            </select>
          </div>
        </div>

        <!-- Ano Letivo -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Ano Letivo *
            <span class="text-xs text-gray-500 font-normal ml-1">(O aluno só poderá ser vinculado a turmas do mesmo ano letivo)</span>
          </label>
          <input 
            type="number" 
            v-model="enrollmentConfig.academic_year" 
            :min="2000"
            :max="new Date().getFullYear() + 5"
            class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            required
            placeholder="Ex: 2024"
          />
        </div>

        <!-- Data da Matrícula -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Data da Matrícula</label>
          <input 
            type="date" 
            v-model="enrollmentConfig.enrollment_date" 
            class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          />
        </div>

        <!-- Observações -->
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
          <textarea 
            v-model="enrollmentConfig.notes" 
            class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none" 
            rows="3"
            placeholder="Informações adicionais sobre a matrícula..."
          ></textarea>
        </div>
      </div>
    </div>

    <!-- Botão de Finalização -->
    <div class="flex justify-center">
      <button 
        @click="handleSubmit"
        :disabled="submitting"
        :class="[
          'inline-flex items-center px-8 py-4 border border-transparent text-base font-medium rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 shadow-lg',
          submitting 
            ? 'bg-indigo-400 cursor-not-allowed' 
            : 'bg-indigo-600 hover:bg-indigo-700 hover:shadow-xl transform hover:-translate-y-0.5'
        ]"
      >
        <svg v-if="submitting" class="animate-spin w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
        </svg>
        <svg v-else class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ submitting ? 'Finalizando...' : 'Finalizar Matrícula' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';

const props = defineProps({
  student: {
    type: Object,
    required: true
  },
  guardian: {
    type: Object,
    required: true
  },
  classroom: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['submit', 'error']);

const submitting = ref(false);

const currentYear = new Date().getFullYear()

const enrollmentConfig = reactive({
  status: 'active',
  process: 'completa',
  academic_year: currentYear.toString(), // Ano letivo padrão: ano atual
  enrollment_date: new Date().toISOString().split('T')[0],
  notes: ''
});

function handleSubmit() {
  if (submitting.value) return;
  
  submitting.value = true;
  
  // Emitir evento com configurações da matrícula
  emit('submit', {
    ...enrollmentConfig,
    student_id: props.student.id,
    guardian_id: props.guardian.id,
    classroom_id: props.classroom.id
  });
}

function formatDate(dateString) {
  if (!dateString) return 'Não informado';
  return new Date(dateString).toLocaleDateString('pt-BR');
}

function formatRelationship(relationship) {
  const relationships = {
    'pai': 'Pai',
    'mae': 'Mãe',
    'avo': 'Avô',
    'avo_feminino': 'Avó',
    'tio': 'Tio',
    'tia': 'Tia',
    'padrasto': 'Padrasto',
    'madrasta': 'Madrasta',
    'tutor': 'Tutor Legal',
    'outro': 'Outro'
  };
  
  return relationships[relationship] || relationship || 'Não informado';
}

function formatShift(shift) {
  const shifts = {
    'matutino': 'Matutino',
    'vespertino': 'Vespertino',
    'noturno': 'Noturno',
    'integral': 'Integral'
  };
  
  return shifts[shift] || shift || 'Não informado';
}
</script>
