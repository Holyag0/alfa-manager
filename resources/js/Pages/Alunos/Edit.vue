<template>
  <div class="max-w-4xl mx-auto py-10">
    
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Editar Aluno</h1>
          <p class="text-sm text-gray-600 mt-1">
            {{ student.name }} • ID: {{ student.id }} • Cadastrado em: {{ formatDate(student.created_at) }}
          </p>
        </div>
        <div class="flex space-x-3">
          <Link :href="route('students.index')" 
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
            <ArrowLeftIcon class="w-4 h-4 mr-2" />
            Voltar para Lista
          </Link>
        </div>
      </div>
    </div>

    <!-- Abas -->
    <div class="mb-6">
      <nav class="border-b border-gray-200">
        <ul class="flex -mb-px">
          <li v-for="tab in tabs" :key="tab.name" class="mr-8">
            <button
              :class="[
                currentTab === tab.name
                  ? 'border-indigo-500 text-indigo-600'
                  : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                'group inline-flex items-center border-b-2 px-1 py-4 text-sm font-medium focus:outline-none'
              ]"
              @click="currentTab = tab.name"
              type="button"
            >
              <component :is="tab.icon" :class="[currentTab === tab.name ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500', 'mr-2 -ml-0.5 size-5']" aria-hidden="true" />
              <span>{{ tab.name }}</span>
            </button>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Conteúdo das Abas -->
    <div>
      <!-- Aba Dados Pessoais -->
      <div v-if="currentTab === 'Dados Pessoais'">
        <!-- Formulário de Edição -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Dados Pessoais</h3>
            <p class="mt-1 text-sm text-gray-600">Atualize as informações pessoais do aluno.</p>
          </div>

          <form @submit.prevent="updateStudent" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Nome -->
              <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                  Nome Completo *
                </label>
                <input 
                  id="name"
                  v-model="form.name" 
                  type="text" 
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  :class="{ 'border-red-300': errors.name }"
                />
                <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
              </div>

              <!-- Data de Nascimento -->
              <div>
                <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-2">
                  Data de Nascimento
                </label>
                <input 
                  id="birth_date"
                  v-model="form.birth_date" 
                  type="date" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  :class="{ 'border-red-300': errors.birth_date }"
                />
                <p v-if="errors.birth_date" class="mt-1 text-sm text-red-600">{{ errors.birth_date }}</p>
              </div>

              <!-- CPF -->
              <div>
                <label for="cpf" class="block text-sm font-medium text-gray-700 mb-2">
                  CPF
                </label>
                <input 
                  id="cpf"
                  v-model="form.cpf" 
                  type="text" 
                  placeholder="000.000.000-00"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  :class="{ 'border-red-300': errors.cpf }"
                />
                <p v-if="errors.cpf" class="mt-1 text-sm text-red-600">{{ errors.cpf }}</p>
              </div>

              <!-- RG -->
              <div>
                <label for="rg" class="block text-sm font-medium text-gray-700 mb-2">
                  RG
                </label>
                <input 
                  id="rg"
                  v-model="form.rg" 
                  type="text" 
                  placeholder="00.000.000-0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  :class="{ 'border-red-300': errors.rg }"
                />
                <p v-if="errors.rg" class="mt-1 text-sm text-red-600">{{ errors.rg }}</p>
              </div>

              <!-- Certidão de Nascimento -->
              <div>
                <label for="birth_certificate_number" class="block text-sm font-medium text-gray-700 mb-2">
                  Certidão de Nascimento
                </label>
                <input 
                  id="birth_certificate_number"
                  v-model="form.birth_certificate_number" 
                  type="text" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  :class="{ 'border-red-300': errors.birth_certificate_number }"
                />
                <p v-if="errors.birth_certificate_number" class="mt-1 text-sm text-red-600">{{ errors.birth_certificate_number }}</p>
              </div>

              <!-- Foto -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Foto do Aluno
                </label>
                <div class="flex items-center space-x-4">
                  <div class="w-24 h-32 bg-gray-100 rounded-xl flex items-center justify-center border-2 border-dashed border-gray-300 overflow-hidden">
                    <div v-if="photoPreview || student.photo" class="w-full h-full">
                      <img 
                        :src="photoPreview || `/storage/${student.photo}`" 
                        alt="Foto do aluno" 
                        class="w-full h-full object-cover"
                      />
                    </div>
                    <div v-else class="text-center">
                      <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                      <p class="text-xs text-gray-500 mt-1">Foto 3x4</p>
                    </div>
                  </div>
                  <div class="flex-1">
                    <input 
                      ref="photoInput"
                      type="file" 
                      accept="image/*" 
                      @change="handlePhotoChange"
                      class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                    />
                    <p class="text-xs text-gray-500 mt-1">PNG, JPG até 2MB</p>
                    <p v-if="errors.photo" class="mt-1 text-sm text-red-600">{{ errors.photo }}</p>
                    
                    <!-- Botão para remover foto -->
                    <button 
                      v-if="photoPreview || student.photo"
                      type="button"
                      @click="removePhoto"
                      class="mt-2 inline-flex items-center px-3 py-1 border border-red-300 text-xs font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100"
                    >
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                      Remover Foto
                    </button>
                  </div>
                </div>
              </div>

              <!-- Observações -->
              <div class="md:col-span-2">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                  Observações
                </label>
                <textarea 
                  id="notes"
                  v-model="form.notes" 
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  :class="{ 'border-red-300': errors.notes }"
                  placeholder="Observações sobre o aluno..."
                ></textarea>
                <p v-if="errors.notes" class="mt-1 text-sm text-red-600">{{ errors.notes }}</p>
              </div>
            </div>

            <!-- Botões -->
            <div class="flex items-center justify-end pt-6 border-t border-gray-200 mt-6">
              <div class="flex space-x-3">
                <Link :href="route('students.index')" 
                      class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                  Cancelar
                </Link>
                <button 
                  type="submit"
                  :disabled="processing"
                  class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                >
                  <span v-if="processing">Salvando...</span>
                  <span v-else>Salvar Alterações</span>
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Aba Responsáveis -->
      <div v-else-if="currentTab === 'Responsáveis'">
        <StudentGuardians :student="student" :guardians="guardians" />
      </div>

      <!-- Aba Matrículas -->
      <div v-else-if="currentTab === 'Matrículas'">
        <StudentEnrollments :enrollments="student.enrollments || []" />
      </div>

      <!-- Aba Mensalidades -->
      <div v-else-if="currentTab === 'Mensalidades'">
        <StudentMonthlyFees
          :student="student"
          :installments="installments"
          :sibling-discount-hint="hasSiblingDiscountSuggestion"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { ArrowLeftIcon, UserIcon, UsersIcon, CurrencyDollarIcon, AcademicCapIcon } from '@heroicons/vue/20/solid'
import StudentGuardians from './components/StudentGuardians.vue'
import StudentMonthlyFees from './components/StudentMonthlyFees.vue'
import StudentEnrollments from './components/StudentEnrollments.vue'

const props = defineProps({
  student: Object,
  guardians: Array,
  installments: {
    type: Array,
    default: () => []
  },
  hasSiblingDiscountSuggestion: {
    type: Boolean,
    default: false
  }
})

const processing = ref(false)
const errors = ref({})
const photoPreview = ref(null)
const photoInput = ref(null)

const tabs = [
  { name: 'Dados Pessoais', icon: UserIcon },
  { name: 'Responsáveis', icon: UsersIcon },
  { name: 'Matrículas', icon: AcademicCapIcon },
  { name: 'Mensalidades', icon: CurrencyDollarIcon }
]

const currentTab = ref(tabs[0].name)

const form = reactive({
  name: props.student.name || '',
  birth_date: props.student.birth_date || '',
  cpf: props.student.cpf || '',
  rg: props.student.rg || '',
  birth_certificate_number: props.student.birth_certificate_number || '',
  notes: props.student.notes || '',
  photo: null
})

const handlePhotoChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.photo = file
    
    // Criar preview da imagem
    const reader = new FileReader()
    reader.onload = (e) => {
      photoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const removePhoto = () => {
  form.photo = 'DELETE' // Sinal para o backend remover a foto
  photoPreview.value = null
  
  // Limpar o input file
  if (photoInput.value) {
    photoInput.value.value = ''
  }
}

const updateStudent = () => {
  processing.value = true
  errors.value = {}

  router.post(route('students.update', props.student.id), {
    ...form,
    _method: 'PATCH'
  }, {
    forceFormData: true, // Força uso de FormData para upload
    preserveScroll: true,
    onSuccess: () => {
      // Success message será mostrada via flash
      photoPreview.value = null // Limpa preview após sucesso
    },
    onError: (responseErrors) => {
      errors.value = responseErrors
    },
    onFinish: () => {
      processing.value = false
    }
  })
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('pt-BR')
}
</script> 