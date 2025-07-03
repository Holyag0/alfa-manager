<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="flex justify-center mb-4">
          <div class="w-16 h-16  rounded-2xl flex items-center justify-center ">
            <svg class="w-8 h-8 text-sky-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
          </div>
        </div>
        <h1 class="text-3xl font-bold text-sky-700 mb-2">Nova Matrícula</h1>
        <p class="text-gray-600">Complete as informações para realizar a matrícula</p>
      </div>

      <!-- Step Indicator Modernizado -->
      <div class="mb-12">
        <StepIndicator :current="step" :steps="steps" />
      </div>

      <!-- Conteúdo dos Steps -->
      <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div v-if="step === 1" class="transition-all duration-500 ease-in-out">
          <ResponsavelStep @next="handleResponsavel" />
        </div>
        <div v-else-if="step === 2" class="transition-all duration-500 ease-in-out">
          <AlunoStep :responsavel="responsavel" @next="handleAluno" @back="step--" />
        </div>
        <div v-else-if="step === 3" class="transition-all duration-500 ease-in-out">
          <MatriculaStep :aluno="aluno" :responsavel="responsavel" @finish="handleFinish" @back="step--" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import StepIndicator from './components/StepIndicator.vue';
import ResponsavelStep from './components/ResponsavelStep.vue';
import AlunoStep from './components/AlunoStep.vue';
import MatriculaStep from './components/MatriculaStep.vue';

const step = ref(1);
const steps = [
  'Responsável',
  'Aluno', 
  'Matrícula'
];

const responsavel = ref(null);
const aluno = ref(null);

function handleResponsavel(data) {
  responsavel.value = data;
  step.value = 2;
}

function handleAluno(data) {
  aluno.value = data;
  step.value = 3;
}

function handleFinish() {
  // Finalizar matrícula
  // Redirecionar ou mostrar mensagem de sucesso
}
</script>