<template>
  <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="flex justify-center mb-4">
          <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
          </div>
        </div>
        <h1 class="text-3xl font-bold text-blue-700 mb-2">Nova Matrícula</h1>
        <p class="text-gray-600">Fluxo simplificado para realizar matrículas</p>
      </div>

      <!-- Step Indicator -->
      <div class="mb-12">
        <StepIndicator :current="currentStep" :steps="steps" />
      </div>

      <!-- Conteúdo dos Steps -->
      <div class="bg-white rounded-2xl shadow-xl p-8">
        <div v-if="currentStep === 1" class="transition-all duration-500 ease-in-out">
          <StudentSelector 
            :selected="formData.student" 
            @selected="handleStudent" 
            @error="handleError"
          />
        </div>
        
        <div v-else-if="currentStep === 2" class="transition-all duration-500 ease-in-out">
          <GuardianSelector 
            :selected="formData.guardian"
            :student-id="formData.student?.id"
            @selected="handleGuardian" 
            @error="handleError"
          />
        </div>
        
        <div v-else-if="currentStep === 3" class="transition-all duration-500 ease-in-out">
          <ClassroomSelector 
            :selected="formData.classroom"
            :student-id="formData.student?.id"
            @selected="handleClassroom" 
            @error="handleError"
          />
        </div>
        
        <div v-else-if="currentStep === 4" class="transition-all duration-500 ease-in-out">
          <EnrollmentReview 
            :student="formData.student"
            :guardian="formData.guardian"
            :classroom="formData.classroom"
            @submit="handleSubmit"
            @error="handleError"
          />
        </div>
      </div>

      <!-- Botões de Navegação -->
      <div class="flex justify-between mt-8">
        <button 
          @click="prevStep" 
          :disabled="currentStep === 1"
          :class="[
            'inline-flex items-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-lg transition-colors duration-200',
            currentStep === 1 
              ? 'text-gray-400 bg-gray-100 cursor-not-allowed' 
              : 'text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500'
          ]"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          Anterior
        </button>

        <button 
          @click="nextStep" 
          :disabled="!canProceed"
          :class="[
            'inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg transition-colors duration-200',
            canProceed 
              ? 'text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500' 
              : 'text-gray-400 bg-gray-100 cursor-not-allowed'
          ]"
        >
          {{ currentStep === 4 ? 'Finalizar' : 'Próximo' }}
          <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
          </svg>
        </button>
      </div>

      <!-- Mensagem de Erro Global -->
      <div v-if="errorMessage" class="mt-6 bg-red-50 border border-red-200 rounded-xl p-4">
        <div class="flex items-center">
          <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.502 0L4.732 18.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-medium text-red-900">{{ errorMessage }}</p>
          </div>
          <button 
            @click="errorMessage = ''" 
            class="p-1 ml-3 text-red-400 hover:text-red-600 focus:outline-none"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';
import StepIndicator from './components/StepIndicator.vue';
import StudentSelector from './components/SimpleStudentSelector.vue';
import GuardianSelector from './components/SimpleGuardianSelector.vue';
import ClassroomSelector from './components/SimpleClassroomSelector.vue';
import EnrollmentReview from './components/SimpleEnrollmentReview.vue';

const currentStep = ref(1);
const errorMessage = ref('');
const submitting = ref(false);

const steps = [
  'Aluno',
  'Responsável', 
  'Turma',
  'Revisão'
];

const formData = reactive({
  student: null,
  guardian: null,
  classroom: null
});

const enrollmentForm = useForm({
  student_id: null,
  guardian_id: null,
  classroom_id: null,
  status: 'active',
  process: 'completa',
  enrollment_date: new Date().toISOString().split('T')[0],
  notes: ''
});

const canProceed = computed(() => {
  switch (currentStep.value) {
    case 1: return formData.student !== null;
    case 2: return formData.guardian !== null;
    case 3: return formData.classroom !== null;
    case 4: return true;
    default: return false;
  }
});

function handleStudent(student) {
  formData.student = student;
  enrollmentForm.student_id = student.id;
  errorMessage.value = '';
}

function handleGuardian(guardian) {
  formData.guardian = guardian;
  enrollmentForm.guardian_id = guardian.id;
  errorMessage.value = '';
}

function handleClassroom(classroom) {
  formData.classroom = classroom;
  enrollmentForm.classroom_id = classroom.id;
  errorMessage.value = '';
}

function handleError(error) {
  errorMessage.value = error;
}

function nextStep() {
  if (canProceed.value && currentStep.value < 4) {
    currentStep.value++;
  }
}

function prevStep() {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
}

function handleSubmit() {
  if (submitting.value) return;
  
  submitting.value = true;
  errorMessage.value = '';

  enrollmentForm.post(route('matriculas.wizard.complete'), {
    preserveState: true,
    replace: true,
    onSuccess: () => {
      // Redirecionar para lista de matrículas
      window.location = route('matriculas.index');
    },
    onError: (errors) => {
      if (errors && Object.keys(errors).length > 0) {
        const errorMessages = [];
        Object.keys(errors).forEach(field => {
          if (Array.isArray(errors[field])) {
            errorMessages.push(...errors[field]);
          } else {
            errorMessages.push(errors[field]);
          }
        });
        errorMessage.value = errorMessages.join(' | ');
      } else {
        errorMessage.value = 'Erro ao finalizar matrícula. Tente novamente.';
      }
    },
    onFinish: () => {
      submitting.value = false;
    }
  });
}
</script>
