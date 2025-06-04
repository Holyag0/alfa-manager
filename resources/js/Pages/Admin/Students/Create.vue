<template>
  <div class="min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-sky-800">📚 Nova Matrícula</h1>
        <p class="text-gray-600 mt-2">Cadastre um novo aluno e efetue a matrícula</p>
      </div>

      <!-- Progress Steps -->
      <div class="rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
          <div v-for="(step, index) in steps" :key="index" 
               class="flex items-center"
               :class="{ 'opacity-50': index > currentStep }">
            <div class="flex items-center">
              <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-medium"
                   :class="getStepClass(index)">
                {{ index + 1 }}
              </div>
              <span class="ml-3 text-sm font-medium" :class="index <= currentStep ? 'text-gray-900' : 'text-gray-500'">
                {{ step.title }}
              </span>
            </div>
            <div v-if="index < steps.length - 1" class="flex-1 mx-4">
              <div class="h-0.5 bg-gray-200" :class="{ 'bg-blue-500': index < currentStep }"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Form Content -->
      <form @submit.prevent="handleSubmit" class="bg-white rounded-lg shadow-sm">
        
        <!-- Step 1: Dados Pessoais -->
        <div v-show="currentStep === 0" class="p-6">
          <h2 class="text-xl font-semibold mb-6 flex items-center">
            👤 Dados Pessoais do Aluno
          </h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nome completo -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Nome Completo *
              </label>
              <input v-model="form.name" 
                     type="text" 
                     required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                     placeholder="Digite o nome completo do aluno">
            </div>

            <!-- Data de nascimento -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Data de Nascimento *
              </label>
              <input v-model="form.birth_date" 
                     type="date" 
                     required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- CPF -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                CPF
              </label>
              <input v-model="form.cpf" 
                     type="text" 
                     @input="formatCPF"
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                     placeholder="000.000.000-00">
            </div>

            <!-- RG -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                RG
              </label>
              <input v-model="form.rg" 
                     type="text" 
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                     placeholder="00.000.000-0">
            </div>

            <!-- Sexo -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Sexo *
              </label>
              <select v-model="form.gender" 
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecione</option>
                <option value="male">Masculino</option>
                <option value="female">Feminino</option>
              </select>
            </div>

            <!-- Upload de foto -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Foto do Aluno
              </label>
              <div class="flex items-center space-x-4">
                <div class="w-20 h-20 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden">
                  <img v-if="form.photo_url" :src="form.photo_url" class="w-full h-full object-cover">
                  <span v-else class="text-2xl">📷</span>
                </div>
                <input type="file" 
                       @change="uploadPhoto" 
                       accept="image/*"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
              </div>
            </div>
          </div>
        </div>

        <!-- Step 2: Endereço -->
        <div v-show="currentStep === 1" class="p-6">
          <h2 class="text-xl font-semibold mb-6 flex items-center">
            📍 Endereço do Aluno
          </h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- CEP -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                CEP
              </label>
              <input v-model="form.address.cep" 
                     type="text" 
                     @blur="searchCEP"
                     @input="formatCEP"
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                     placeholder="00000-000">
            </div>

            <!-- Rua -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Rua/Logradouro
              </label>
              <input v-model="form.address.street" 
                     type="text" 
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                     placeholder="Nome da rua">
            </div>

            <!-- Número -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Número
              </label>
              <input v-model="form.address.number" 
                     type="text" 
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                     placeholder="123">
            </div>

            <!-- Complemento -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Complemento
              </label>
              <input v-model="form.address.complement" 
                     type="text" 
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                     placeholder="Apto, bloco, etc">
            </div>

            <!-- Bairro -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Bairro
              </label>
              <input v-model="form.address.neighborhood" 
                     type="text" 
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                     placeholder="Nome do bairro">
            </div>

            <!-- Cidade -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Cidade
              </label>
              <input v-model="form.address.city" 
                     type="text" 
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                     placeholder="Nome da cidade">
            </div>

            <!-- Estado -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Estado
              </label>
              <select v-model="form.address.state" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecione</option>
                <option value="CE">Ceará</option>
                <option value="SP">São Paulo</option>
                <!-- Adicionar outros estados -->
              </select>
            </div>
          </div>
        </div>

        <!-- Step 3: Responsáveis -->
        <div v-show="currentStep === 2" class="p-6">
          <h2 class="text-xl font-semibold mb-6 flex items-center">
            👨‍👩‍👧‍👦 Responsáveis pelo Aluno
          </h2>

          <!-- Buscar responsável existente -->
          <div class="bg-blue-50 p-4 rounded-lg mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              🔍 Buscar Responsável Existente
            </label>
            <input v-model="guardianSearch" 
                   @input="searchGuardians"
                   type="text" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="Digite nome ou CPF do responsável">
            
            <!-- Resultados da busca -->
            <div v-if="foundGuardians.length" class="mt-3 space-y-2">
              <div v-for="guardian in foundGuardians" :key="guardian.id" 
                   class="flex items-center justify-between bg-white p-3 rounded border cursor-pointer hover:bg-gray-50"
                   @click="addExistingGuardian(guardian)">
                <div>
                  <div class="font-medium">{{ guardian.name }}</div>
                  <div class="text-sm text-gray-500">{{ guardian.cpf }} - {{ guardian.phone }}</div>
                </div>
                <button type="button" class="text-blue-600 hover:text-blue-800">
                  ➕ Adicionar
                </button>
              </div>
            </div>
          </div>

          <!-- Responsáveis adicionados -->
          <div v-if="form.guardians.length" class="space-y-4 mb-6">
            <h3 class="font-medium">Responsáveis Adicionados:</h3>
            <div v-for="(guardian, index) in form.guardians" :key="index" 
                 class="border rounded-lg p-4 bg-gray-50">
              <div class="flex items-center justify-between">
                <div>
                  <div class="font-medium">{{ guardian.name }}</div>
                  <div class="text-sm text-gray-500">{{ guardian.relationship }} - {{ guardian.phone }}</div>
                </div>
                <button type="button" 
                        @click="removeGuardian(index)"
                        class="text-red-600 hover:text-red-800">
                  🗑️ Remover
                </button>
              </div>
            </div>
          </div>

          <!-- Adicionar novo responsável -->
          <div class="border rounded-lg p-4">
            <h3 class="font-medium mb-4">➕ Adicionar Novo Responsável</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <input v-model="newGuardian.name" 
                     type="text" 
                     placeholder="Nome completo"
                     class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              
              <input v-model="newGuardian.cpf" 
                     type="text" 
                     placeholder="CPF"
                     @input="formatCPFGuardian"
                     class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              
              <input v-model="newGuardian.phone" 
                     type="text" 
                     placeholder="Telefone"
                     @input="formatPhone"
                     class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              
              <select v-model="newGuardian.relationship" 
                      class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Parentesco</option>
                <option value="father">Pai</option>
                <option value="mother">Mãe</option>
                <option value="grandfather">Avô</option>
                <option value="grandmother">Avó</option>
                <option value="uncle">Tio(a)</option>
                <option value="guardian">Responsável Legal</option>
              </select>
              
              <div class="md:col-span-2">
                <button type="button" 
                        @click="addNewGuardian"
                        :disabled="!canAddGuardian"
                        class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                  ➕ Adicionar Responsável
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 4: Matrícula -->
        <div v-show="currentStep === 3" class="p-6">
          <h2 class="text-xl font-semibold mb-6 flex items-center">
            🎓 Dados da Matrícula
          </h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Ano letivo -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Ano Letivo *
              </label>
              <select v-model="form.enrollment.school_year" 
                      required
                      @change="loadAvailableClassrooms"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecione</option>
                <option value="2024">2024</option>
                <option value="2025">2025</option>
              </select>
            </div>

            <!-- Série/Ano -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Série/Ano *
              </label>
              <select v-model="form.enrollment.grade_level" 
                      required
                      @change="loadAvailableClassrooms"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecione</option>
                <option value="1">1º Ano</option>
                <option value="2">2º Ano</option>
                <option value="3">3º Ano</option>
                <option value="4">4º Ano</option>
                <option value="5">5º Ano</option>
                <option value="6">6º Ano</option>
                <option value="7">7º Ano</option>
                <option value="8">8º Ano</option>
                <option value="9">9º Ano</option>
              </select>
            </div>

            <!-- Turma -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Turma *
              </label>
              <select v-model="form.enrollment.classroom_id" 
                      required
                      @change="calculateEnrollmentFee"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Selecione uma turma</option>
                <option v-for="classroom in availableClassrooms" 
                        :key="classroom.id"
                        :value="classroom.id"
                        :disabled="classroom.available_spots <= 0">
                  {{ classroom.name }} - {{ classroom.period }} 
                  ({{ classroom.available_spots }} vagas disponíveis)
                  <span v-if="classroom.available_spots <= 0"> - LOTADA</span>
                </option>
              </select>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Status da Matrícula *
              </label>
              <select v-model="form.enrollment.status" 
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="pending">Pendente</option>
                <option value="active">Ativa</option>
              </select>
            </div>

            <!-- Data da matrícula -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Data da Matrícula *
              </label>
              <input v-model="form.enrollment.enrollment_date" 
                     type="date" 
                     required
                     class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
          </div>

          <!-- Resumo da matrícula -->
          <div v-if="selectedClassroom" class="mt-6 bg-blue-50 p-4 rounded-lg">
            <h3 class="font-medium mb-2">📋 Resumo da Matrícula</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div><strong>Aluno:</strong> {{ form.name }}</div>
              <div><strong>Turma:</strong> {{ selectedClassroom.name }}</div>
              <div><strong>Período:</strong> {{ selectedClassroom.period }}</div>
              <div><strong>Professor:</strong> {{ selectedClassroom.teacher?.name || 'Não definido' }}</div>
              <div><strong>Valor da Matrícula:</strong> R$ {{ enrollmentFee.toFixed(2) }}</div>
              <div><strong>Status:</strong> {{ getStatusLabel(form.enrollment.status) }}</div>
            </div>
          </div>
        </div>

        <!-- Step 5: Checkout (EM STANDBY) -->
        <div v-show="currentStep === 4" class="p-6">
          <h2 class="text-xl font-semibold mb-6 flex items-center">
            💳 Pagamento da Matrícula
            <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">EM DESENVOLVIMENTO</span>
          </h2>
          
          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <div class="text-6xl mb-4">🚧</div>
            <h3 class="text-lg font-medium text-yellow-800 mb-2">
              Módulo de Pagamento em Desenvolvimento
            </h3>
            <p class="text-yellow-700 mb-4">
              Esta funcionalidade estará disponível em breve. Por enquanto, você pode finalizar a matrícula sem pagamento.
            </p>
            <div class="bg-white p-4 rounded border text-left">
              <h4 class="font-medium mb-2">💰 Valor da Matrícula:</h4>
              <div class="text-2xl font-bold text-green-600">R$ {{ enrollmentFee.toFixed(2) }}</div>
              <p class="text-sm text-gray-600 mt-1">
                O pagamento poderá ser processado posteriormente através do sistema financeiro.
              </p>
            </div>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex items-center justify-between p-6 border-t bg-gray-50">
          <button type="button" 
                  @click="previousStep"
                  v-show="currentStep > 0"
                  class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600">
            ← Anterior
          </button>
          
          <div class="flex space-x-4">
            <button type="button" 
                    @click="nextStep"
                    v-show="currentStep < steps.length - 1"
                    :disabled="!canProceedToNext"
                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
              Próximo →
            </button>
            
            <button type="submit" 
                    v-show="currentStep === steps.length - 1"
                    :disabled="loading"
                    class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
              <span v-if="loading">⏳ Finalizando...</span>
              <span v-else">✅ Finalizar Matrícula</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

export default {
  name: 'StudentCreate',
  props: {
    classrooms: Array,
    guardians: Array
  },
  setup(props) {
    const currentStep = ref(0)
    const loading = ref(false)
    const guardianSearch = ref('')
    const foundGuardians = ref([])
    const availableClassrooms = ref(props.classrooms || [])
    const enrollmentFee = ref(0)

    const steps = [
      { title: 'Dados Pessoais', icon: '👤' },
      { title: 'Endereço', icon: '📍' },
      { title: 'Responsáveis', icon: '👨‍👩‍👧‍👦' },
      { title: 'Matrícula', icon: '🎓' },
      { title: 'Pagamento', icon: '💳' }
    ]

    const form = useForm({
      name: '',
      birth_date: '',
      cpf: '',
      rg: '',
      gender: '',
      photo_url: '',
      address: {
        cep: '',
        street: '',
        number: '',
        complement: '',
        neighborhood: '',
        city: '',
        state: ''
      },
      guardians: [],
      enrollment: {
        school_year: '2025',
        grade_level: '',
        classroom_id: '',
        status: 'pending',
        enrollment_date: new Date().toISOString().split('T')[0]
      }
    })

    const newGuardian = ref({
      name: '',
      cpf: '',
      phone: '',
      relationship: ''
    })

    const selectedClassroom = computed(() => {
      return availableClassrooms.value.find(c => c.id == form.enrollment.classroom_id)
    })

    const canAddGuardian = computed(() => {
      return newGuardian.value.name && 
             newGuardian.value.cpf && 
             newGuardian.value.phone && 
             newGuardian.value.relationship
    })

    const canProceedToNext = computed(() => {
      switch(currentStep.value) {
        case 0: // Dados pessoais
          return form.name && form.birth_date && form.gender
        case 1: // Endereço
          return true // Endereço é opcional
        case 2: // Responsáveis
          return form.guardians.length > 0
        case 3: // Matrícula
          return form.enrollment.school_year && 
                 form.enrollment.grade_level && 
                 form.enrollment.classroom_id
        default:
          return true
      }
    })

    // Methods
    const getStepClass = (index) => {
      if (index < currentStep.value) return 'bg-green-500 text-white'
      if (index === currentStep.value) return 'bg-blue-500 text-white'
      return 'bg-gray-200 text-gray-600'
    }

    const nextStep = () => {
      if (currentStep.value < steps.length - 1) {
        currentStep.value++
      }
    }

    const previousStep = () => {
      if (currentStep.value > 0) {
        currentStep.value--
      }
    }

    const formatCPF = (event) => {
      let value = event.target.value.replace(/\D/g, '')
      value = value.replace(/(\d{3})(\d)/, '$1.$2')
      value = value.replace(/(\d{3})(\d)/, '$1.$2')
      value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2')
      form.cpf = value
    }

    const formatCEP = (event) => {
      let value = event.target.value.replace(/\D/g, '')
      value = value.replace(/(\d{5})(\d)/, '$1-$2')
      form.address.cep = value
    }

    const formatCPFGuardian = (event) => {
      let value = event.target.value.replace(/\D/g, '')
      value = value.replace(/(\d{3})(\d)/, '$1.$2')
      value = value.replace(/(\d{3})(\d)/, '$1.$2')
      value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2')
      newGuardian.value.cpf = value
    }

    const formatPhone = (event) => {
      let value = event.target.value.replace(/\D/g, '')
      value = value.replace(/(\d{2})(\d)/, '($1) $2')
      value = value.replace(/(\d{5})(\d)/, '$1-$2')
      newGuardian.value.phone = value
    }

    const uploadPhoto = async (event) => {
      const file = event.target.files[0]
      if (!file) return

      const formData = new FormData()
      formData.append('photo', file)

      try {
        const response = await fetch('/upload/student-photo', {
          method: 'POST',
          body: formData,
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        })
        
        const data = await response.json()
        form.photo_url = data.url
      } catch (error) {
        console.error('Erro no upload:', error)
      }
    }

    const searchCEP = async () => {
      const cep = form.address.cep.replace(/\D/g, '')
      if (cep.length === 8) {
        try {
          const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`)
          const data = await response.json()
          
          if (!data.erro) {
            form.address.street = data.logradouro
            form.address.neighborhood = data.bairro
            form.address.city = data.localidade
            form.address.state = data.uf
          }
        } catch (error) {
          console.error('Erro ao buscar CEP:', error)
        }
      }
    }

    const searchGuardians = async () => {
      if (guardianSearch.value.length >= 3) {
        // Simular busca de responsáveis
        foundGuardians.value = props.guardians.filter(g => 
          g.name.toLowerCase().includes(guardianSearch.value.toLowerCase())
        //   g.cpf.includes(guardianSearch.value)
        )
      } else {
        foundGuardians.value = []
      }
    }

    const addExistingGuardian = (guardian) => {
      // Verificar se já não foi adicionado
      const exists = form.guardians.find(g => g.id === guardian.id)
      if (!exists) {
        form.guardians.push({
          id: guardian.id,
          name: guardian.name,
          cpf: guardian.cpf,
          phone: guardian.phone,
          relationship: guardian.relationship || 'guardian'
        })
        guardianSearch.value = ''
        foundGuardians.value = []
      }
    }

    const addNewGuardian = () => {
      if (canAddGuardian.value) {
        form.guardians.push({
          ...newGuardian.value,
          isNew: true
        })
        
        // Resetar formulário
        newGuardian.value = {
          name: '',
          cpf: '',
          phone: '',
          relationship: ''
        }
      }
    }

    const removeGuardian = (index) => {
      form.guardians.splice(index, 1)
    }

    const loadAvailableClassrooms = async () => {
      if (form.enrollment.school_year && form.enrollment.grade_level) {
        try {
          const response = await fetch(`/api/classrooms/available?year=${form.enrollment.school_year}&grade=${form.enrollment.grade_level}`)
          const data = await response.json()
          availableClassrooms.value = data
        } catch (error) {
          console.error('Erro ao carregar turmas:', error)
        }
      }
    }

    const calculateEnrollmentFee = () => {
      if (selectedClassroom.value) {
        // Simular cálculo de taxa de matrícula baseado na turma
        enrollmentFee.value = selectedClassroom.value.enrollment_fee || 150.00
      }
    }

    const getStatusLabel = (status) => {
      const labels = {
        'pending': 'Pendente',
        'active': 'Ativa',
        'inactive': 'Inativa'
      }
      return labels[status] || status
    }

    const handleSubmit = async () => {
      loading.value = true
      
      try {
        // Enviar dados do formulário
        await form.post('/admin/students', {
          onSuccess: () => {
            router.visit('/admin/students')
          },
          onError: (errors) => {
            console.error('Erro ao cadastrar:', errors)
          }
        })
      } catch (error) {
        console.error('Erro:', error)
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      // Carregar turmas disponíveis se já tem ano e série
      if (form.enrollment.school_year && form.enrollment.grade_level) {
        loadAvailableClassrooms()
      }
    })

    return {
      currentStep,
      loading,
      guardianSearch,
      foundGuardians,
      availableClassrooms,
      enrollmentFee,
      steps,
      form,
      newGuardian,
      selectedClassroom,
      canAddGuardian,
      canProceedToNext,
      getStepClass,
      nextStep,
      previousStep,
      formatCPF,
      formatCEP,
      formatCPFGuardian,
      formatPhone,
      uploadPhoto,
      searchCEP,
      searchGuardians,
      addExistingGuardian,
      addNewGuardian,
      removeGuardian,
      loadAvailableClassrooms,
      calculateEnrollmentFee,
      getStatusLabel,
      handleSubmit
    }
  }
}
</script>

<style scoped>
/* Adicionar estilos customizados se necessário */
.step-transition {
  transition: all 0.3s ease-in-out;
}

.photo-preview {
  transition: opacity 0.3s ease;
}

.guardian-card {
  transition: transform 0.2s ease;
}

.guardian-card:hover {
  transform: translateY(-2px);
}

/* Loading animation */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Custom scrollbar para lista de responsáveis */
.guardians-list::-webkit-scrollbar {
  width: 4px;
}

.guardians-list::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.guardians-list::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 2px;
}

.guardians-list::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>