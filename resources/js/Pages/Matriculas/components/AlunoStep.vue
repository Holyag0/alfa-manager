<template>
  <div class="p-8">
    <!-- Header do Step -->
    <div class="flex items-center justify-between mb-8">
      <div class="flex items-center">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
          </svg>
        </div>
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Aluno</h2>
          <p class="text-gray-600">Busque ou cadastre o aluno para matrícula</p>
        </div>
      </div>
      <button 
        @click="$emit('back')" 
        class="inline-flex items-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Voltar
      </button>
    </div>

    <!-- Mensagem de Erro -->
    <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-xl p-4 mb-8">
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

    <!-- Informações do Responsável -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-8">
      <div class="flex items-center">
        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
        <div>
          <p class="text-sm font-medium text-blue-900">Responsável selecionado:</p>
          <p class="text-blue-800 font-semibold">{{ responsavel.name }} (CPF: {{ responsavel.cpf }})</p>
        </div>
      </div>
    </div>

    <!-- Busca de Aluno -->
    <div class="mb-8">
      <div class="bg-gray-50 rounded-xl p-6 border-2 border-dashed border-gray-300 hover:border-blue-400 transition-colors duration-200">
        <div class="flex items-center mb-4">
          <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <label class="text-sm font-semibold text-gray-700">Buscar aluno existente</label>
        </div>
        
        <div class="relative">
          <input 
            v-model="search" 
            @input="onSearch" 
            type="text" 
            placeholder="Digite o nome ou CPF do aluno" 
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 pl-10"
          />
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>
        </div>

        <!-- Resultados da Busca -->
        <div v-if="search && students.length" class="mt-4 bg-white rounded-lg shadow-md border border-gray-200 max-h-60 overflow-y-auto">
          <div 
            v-for="student in students" 
            :key="student.id"
            :class="[
              'px-4 py-3 cursor-pointer border-b border-gray-100 last:border-b-0 transition-all duration-200 flex items-center',
              selectedStudent && selectedStudent.id === student.id 
                ? 'bg-blue-50 border-l-4 border-l-blue-500' 
                : 'hover:bg-gray-50'
            ]"
            @click="selectStudent(student)"
          >
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
              </svg>
            </div>
            <div class="flex-1">
              <div class="font-semibold text-gray-900">{{ student.name }}</div>
              <div class="text-sm text-gray-500">CPF: {{ student.cpf }} | Nascimento: {{ formatDate(student.birth_date) }}</div>
            </div>
            <div v-if="selectedStudent && selectedStudent.id === student.id" class="text-blue-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- Estados da Busca -->
        <div v-if="search && !students.length && !loading" class="mt-4 text-center py-4">
          <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
          </svg>
          <p class="text-sm text-gray-500">Nenhum aluno encontrado</p>
        </div>

        <div v-if="loading" class="mt-4 flex items-center justify-center py-4">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mr-2"></div>
          <span class="text-sm text-blue-600">Buscando...</span>
        </div>

        <!-- Aluno Selecionado -->
        <div v-if="selectedStudent" class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-start">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-1">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <div class="flex-1">
              <h4 class="font-semibold text-blue-900 mb-1">Aluno selecionado</h4>
              <p class="text-sm text-blue-800 font-medium">{{ selectedStudent.name }}</p>
              <p class="text-sm text-blue-700">CPF: {{ selectedStudent.cpf }}</p>
              <p class="text-sm text-blue-700">Nascimento: {{ formatDate(selectedStudent.birth_date) }}</p>
            </div>
          </div>
          <div class="mt-4 flex justify-end">
            <button 
              @click="confirmStudent" 
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
            >
              Continuar
              <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <div class="relative mb-8">
      <div class="absolute inset-0 flex items-center">
        <div class="w-full border-t border-gray-300"></div>
      </div>
      <div class="relative flex justify-center text-sm">
        <span class="px-4 bg-white text-gray-500 font-medium">ou</span>
      </div>
    </div>

    <!-- Cadastro de Novo Aluno -->
    <div class="bg-green-50 rounded-xl p-6 border-2 border-dashed border-green-300">
      <div class="flex items-center mb-6">
        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        <label class="text-sm font-semibold text-green-800">Cadastrar novo aluno</label>
      </div>
      
      <form @submit.prevent="submitNewStudent" class="space-y-6">
        <!-- Seção: Dados Pessoais -->
        <div class="bg-white rounded-xl p-6 border border-green-200 shadow-sm">
          <h4 class="flex items-center text-lg font-semibold text-gray-800 mb-6">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
            Dados Pessoais
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Nome completo *</label>
                              <input 
                  v-model="newStudent.name" 
                  type="text" 
                  placeholder="Nome completo do aluno" 
                  :class="[
                    'w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 transition-colors duration-200',
                    form.errors.name ? 'border-red-300 focus:border-red-500' : 'border-gray-300 focus:border-green-500'
                  ]"
                  required 
                />
                <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                  {{ form.errors.name }}
                </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Data de Nascimento *</label>
                              <input 
                  v-model="newStudent.birth_date" 
                  type="date" 
                  :class="[
                    'w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 transition-colors duration-200',
                    form.errors.birth_date ? 'border-red-300 focus:border-red-500' : 'border-gray-300 focus:border-green-500'
                  ]"
                  required 
                />
                <div v-if="form.errors.birth_date" class="mt-1 text-sm text-red-600">
                  {{ form.errors.birth_date }}
                </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">CPF *</label>
                              <input 
                  v-model="newStudent.cpf" 
                  type="text" 
                  placeholder="000.000.000-00" 
                  :class="[
                    'w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-green-500 transition-colors duration-200',
                    form.errors.cpf ? 'border-red-300 focus:border-red-500' : 'border-gray-300 focus:border-green-500'
                  ]"
                  required 
                />
                <div v-if="form.errors.cpf" class="mt-1 text-sm text-red-600">
                  {{ form.errors.cpf }}
                </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">RG</label>
              <input 
                v-model="newStudent.rg" 
                type="text" 
                placeholder="00.000.000-0" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Certidão de Nascimento</label>
              <input 
                v-model="newStudent.birth_certificate_number" 
                type="text" 
                placeholder="000000000000000000000000000000000" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
              />
            </div>
            <!-- Campo de upload de foto moderno -->
            <div class="lg:col-span-1 flex flex-col items-center justify-center">
              <label
                class="w-32 h-32 flex flex-col items-center justify-center border-2 border-dashed border-green-400 rounded-full cursor-pointer transition hover:border-green-600 relative group bg-white"
                :class="{ 'ring-2 ring-green-400': isDragging }"
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="onDropPhoto"
              >
                <template v-if="photoPreview">
                  <img :src="photoPreview" alt="Pré-visualização da foto" class="w-32 h-32 object-cover rounded-full border border-gray-200" />
                  <button type="button" @click.stop.prevent="removePhoto" class="absolute top-1 right-1 bg-white bg-opacity-80 rounded-full p-1 shadow hover:bg-red-100 transition" title="Remover foto">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                  </button>
                </template>
                <template v-else>
                  <div class="flex flex-col items-center justify-center">
                    <svg class="w-10 h-10 text-green-400 mb-2 group-hover:text-green-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 002.828 2.828l6.586-6.586a2 2 0 00-2.828-2.828z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7V3a1 1 0 00-1-1h-4a1 1 0 00-1 1v4" />
                    </svg>
                    <span class="text-xs text-green-700 text-center">Arraste uma foto aqui<br>ou</span>
                    <span class="mt-1 inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold cursor-pointer group-hover:bg-green-200 transition">Selecionar foto</span>
                  </div>
                  <input type="file" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" @change="onPhotoChange" />
                </template>
              </label>
            </div>
          </div>
        </div>

        <!-- Seção: Contato -->
        <div class="bg-white rounded-xl p-6 border border-green-200 shadow-sm">
          <h4 class="flex items-center text-lg font-semibold text-gray-800 mb-6">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
            </div>
            Informações de Contato
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">E-mail</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </div>
                <input 
                  v-model="newStudent.email" 
                  type="email" 
                  placeholder="email@exemplo.com" 
                  class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                  </svg>
                </div>
                <input 
                  v-model="newStudent.phone" 
                  type="text" 
                  placeholder="(00) 00000-0000" 
                  class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Seção: Informações Acadêmicas -->
        <div class="bg-white rounded-xl p-6 border border-green-200 shadow-sm">
          <h4 class="flex items-center text-lg font-semibold text-gray-800 mb-6">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
              </svg>
            </div>
            Informações Acadêmicas
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Série/Ano</label>
              <select 
                v-model="newStudent.grade" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
              >
                <option value="">Selecione a série</option>
                <option value="1_fundamental">1º Ano - Ensino Fundamental</option>
                <option value="2_fundamental">2º Ano - Ensino Fundamental</option>
                <option value="3_fundamental">3º Ano - Ensino Fundamental</option>
                <option value="4_fundamental">4º Ano - Ensino Fundamental</option>
                <option value="5_fundamental">5º Ano - Ensino Fundamental</option>
                <option value="6_fundamental">6º Ano - Ensino Fundamental</option>
                <option value="7_fundamental">7º Ano - Ensino Fundamental</option>
                <option value="8_fundamental">8º Ano - Ensino Fundamental</option>
                <option value="9_fundamental">9º Ano - Ensino Fundamental</option>
                <option value="1_medio">1º Ano - Ensino Médio</option>
                <option value="2_medio">2º Ano - Ensino Médio</option>
                <option value="3_medio">3º Ano - Ensino Médio</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Turno</label>
              <select 
                v-model="newStudent.shift" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
              >
                <option value="">Selecione o turno</option>
                <option value="matutino">🌅 Matutino</option>
                <option value="vespertino">🌇 Vespertino</option>
                <option value="noturno">🌙 Noturno</option>
                <option value="integral">🌞 Integral</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Seção: Informações Médicas -->
        <div class="bg-white rounded-xl p-6 border border-green-200 shadow-sm">
          <h4 class="flex items-center text-lg font-semibold text-gray-800 mb-6">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
              </svg>
            </div>
            Informações Médicas (Opcional)
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Alergias</label>
              <textarea 
                v-model="newStudent.allergies" 
                rows="3"
                placeholder="Descreva alergias conhecidas (medicamentos, alimentos, etc.)" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 resize-none" 
              ></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Medicamentos em uso</label>
              <textarea 
                v-model="newStudent.medications" 
                rows="3"
                placeholder="Medicamentos de uso contínuo" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 resize-none" 
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Seção: Observações -->
        <div class="bg-white rounded-xl p-6 border border-green-200 shadow-sm">
          <h4 class="flex items-center text-lg font-semibold text-gray-800 mb-6">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </div>
            Observações Adicionais
          </h4>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
            <textarea 
              v-model="newStudent.notes" 
              rows="4"
              placeholder="Informações adicionais relevantes sobre o aluno (necessidades especiais, comportamento, etc.)" 
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 resize-none" 
            ></textarea>
          </div>
        </div>

        <!-- Botão de Submit -->
        <div class="flex justify-end pt-6 border-t border-green-200">
          <button 
            type="submit" 
            :disabled="submitting"
            :class="[
              'inline-flex items-center px-8 py-4 border border-transparent text-base font-medium rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-lg',
              submitting 
                ? 'bg-green-400 cursor-not-allowed' 
                : 'bg-green-600 hover:bg-green-700 hover:shadow-xl transform hover:-translate-y-0.5'
            ]"
          >
            <svg v-if="submitting" class="animate-spin w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            <svg v-else class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            {{ submitting ? 'Cadastrando...' : 'Cadastrar e Continuar' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';

const props = defineProps({
  responsavel: { type: Object, required: true }
});

const emit = defineEmits(['next', 'back']);
const students = ref([]);
const search = ref('');
const loading = ref(false);
const selectedStudent = ref(null);
const submitting = ref(false);
const errorMessage = ref('');

const page = usePage();

const fetchStudents = debounce(async () => {
  if (!search.value) {
    students.value = [];
    selectedStudent.value = null;
    return;
  }
  loading.value = true;
  const response = await fetch(`/api/students?q=${encodeURIComponent(search.value)}`);
  students.value = await response.json();
  loading.value = false;
}, 400);

function onSearch() {
  fetchStudents();
}

function selectStudent(student) {
  selectedStudent.value = student;
}

function confirmStudent() {
  if (selectedStudent.value) {
    emit('next', selectedStudent.value);
  }
}

const newStudent = ref({ 
  name: '', 
  birth_date: '',
  cpf: '', 
  rg: '',
  birth_certificate_number: '',
  email: '', 
  phone: '',
  grade: '',
  shift: '',
  allergies: '',
  medications: '',
  notes: '' 
});

const photoFile = ref(null);
const photoPreview = ref(null);
const isDragging = ref(false);

function onDropPhoto(e) {
  isDragging.value = false;
  const file = e.dataTransfer.files[0];
  handlePhotoFile(file);
}

function onPhotoChange(e) {
  const file = e.target.files[0];
  handlePhotoFile(file);
}

function handlePhotoFile(file) {
  if (file && file.type.startsWith('image/')) {
    if (file.size > 2 * 1024 * 1024) {
      alert('A imagem deve ter no máximo 2MB.');
      return;
    }
    photoFile.value = file;
    const reader = new FileReader();
    reader.onload = (ev) => {
      photoPreview.value = ev.target.result;
    };
    reader.readAsDataURL(file);
  } else if (file) {
    alert('Por favor, selecione uma imagem válida.');
  }
}

function removePhoto() {
  photoFile.value = null;
  photoPreview.value = null;
}

const form = useForm({
  name: '',
  gender: '',
  birth_date: '',
  cpf: '', 
  rg: '',
  birth_certificate_number: '',
  email: '', 
  phone: '',
  grade: '',
  shift: '',
  allergies: '',
  medications: '',
  notes: '',
  photo: null
});

function submitNewStudent() {
  submitting.value = true;
  errorMessage.value = '';
  
  // Preencher o form com os dados do newStudent
  Object.entries(newStudent.value).forEach(([key, value]) => {
    form[key] = value ?? '';
  });
  
  if (photoFile.value) {
    form.photo = photoFile.value;
  }
  
  form.post(route('students.store'), {
    preserveState: true,
    replace: true,
    forceFormData: true,
    onSuccess: (page) => {
      if (page.props.student) {
        emit('next', page.props.student);
      }
    },
    onError: (errors) => {
      errorMessage.value = 'Erro ao cadastrar o aluno. Verifique os dados e tente novamente.';
      // Scroll to top to show the error message
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    onFinish: () => {
      submitting.value = false;
    }
  });
}

function formatDate(dateString) {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('pt-BR');
}
watch(
  () => page.props.flash.success,
  (success) => {
    if (success) {
      emit('next', page.props.flash.student || selectedStudent.value || { ...newStudent.value });
    }
  }
);
// Limpar mensagem de erro quando o usuário começar a digitar
watch([() => newStudent.value.name, () => newStudent.value.cpf], () => {
  if (errorMessage.value) {
    errorMessage.value = '';
  }
});
</script>