<template>
  <div class="">
    <!-- Header do Step -->
    <div class="flex items-center mb-8">
      <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4">
        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
      </div>
      <div>
        <h2 class="text-2xl font-bold text-gray-900">Responsável</h2>
        <p class="text-gray-600">Busque ou cadastre o responsável pelo aluno</p>
      </div>
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

    <!-- Busca de Responsável -->
    <div class="mb-8">
      <div class="bg-gray-50 rounded-xl p-6 border-2 border-dashed border-gray-300 hover:border-blue-400 transition-colors duration-200">
        <div class="flex items-center mb-4">
          <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <label class="text-sm font-semibold text-gray-700">Buscar responsável existente</label>
        </div>
        
        <div class="relative">
          <input 
            v-model="search" 
            @input="onSearch" 
            type="text" 
            placeholder="Digite o nome ou CPF do responsável" 
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 pl-10"
          />
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>
        </div>

        <!-- Resultados da Busca -->
        <div v-if="search && guardians.length" class="mt-4 bg-white rounded-lg shadow-md border border-gray-200 max-h-60 overflow-y-auto">
          <div 
            v-for="guardian in guardians" 
            :key="guardian.id"
            :class="[
              'px-4 py-3 cursor-pointer border-b border-gray-100 last:border-b-0 transition-all duration-200 flex items-center',
              selectedGuardian && selectedGuardian.id === guardian.id 
                ? 'bg-blue-50 border-l-4 border-l-blue-500' 
                : 'hover:bg-gray-50'
            ]"
            @click="selectGuardian(guardian)"
          >
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
            <div class="flex-1">
              <div class="font-semibold text-gray-900">{{ guardian.name }}</div>
              <div class="text-sm text-gray-500">CPF: {{ guardian.cpf }}</div>
            </div>
            <div v-if="selectedGuardian && selectedGuardian.id === guardian.id" class="text-blue-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- Estados da Busca -->
        <div v-if="search && !guardians.length && !loading" class="mt-4 text-center py-4">
          <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.29-1.009-5.824-2.563M15 9.34c-1.168-.91-2.544-1.34-4-1.34s-2.832.43-4 1.34"/>
          </svg>
          <p class="text-sm text-gray-500">Nenhum responsável encontrado</p>
        </div>

        <div v-if="loading" class="mt-4 flex items-center justify-center py-4">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mr-2"></div>
          <span class="text-sm text-blue-600">Buscando...</span>
        </div>

        <!-- Responsável Selecionado -->
        <div v-if="selectedGuardian" class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="flex items-start">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3 mt-1">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <div class="flex-1">
              <h4 class="font-semibold text-blue-900 mb-1">Responsável selecionado</h4>
              <p class="text-sm text-blue-800">{{ selectedGuardian.name }}</p>
              <p class="text-sm text-blue-700">CPF: {{ selectedGuardian.cpf }}</p>
            </div>
          </div>
          <div class="mt-4 flex justify-end">
            <button 
              @click="confirmGuardian" 
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

    <!-- Cadastro de Novo Responsável -->
    <div class="bg-green-50 rounded-xl p-6 border-2 border-dashed border-green-300">
      <div class="flex items-center mb-6">
        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        <label class="text-sm font-semibold text-green-800">Cadastrar novo responsável</label>
      </div>
      
      <form @submit.prevent="submitNewGuardian" class="space-y-6">
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
                v-model="newGuardian.name" 
                type="text" 
                placeholder="Nome completo do responsável" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
                required 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">CPF *</label>
              <input 
                v-model="newGuardian.cpf" 
                type="text" 
                placeholder="000.000.000-00" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
                required 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">RG</label>
              <input 
                v-model="newGuardian.rg" 
                type="text" 
                placeholder="00.000.000-0" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Data de Nascimento</label>
              <input 
                v-model="newGuardian.birth_date" 
                type="date" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Gênero</label>
              <select 
                v-model="newGuardian.gender" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
              >
                <option value="">Selecione</option>
                <option value="masculino">Masculino</option>
                <option value="feminino">Feminino</option>
                <option value="outro">Outro</option>
                <option value="prefiro_nao_informar">Prefiro não informar</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Estado Civil</label>
              <select 
                v-model="newGuardian.marital_status" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
              >
                <option value="">Selecione</option>
                <option value="solteiro">Solteiro(a)</option>
                <option value="casado">Casado(a)</option>
                <option value="divorciado">Divorciado(a)</option>
                <option value="viuvo">Viúvo(a)</option>
                <option value="uniao_estavel">União Estável</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Seção: Endereços - MOVIDA PARA CÁ -->
        <div class="bg-white rounded-xl p-6 border border-green-200 shadow-sm">
          <h4 class="flex items-center text-lg font-semibold text-gray-800 mb-6">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
            Endereços
          </h4>
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <template v-for="(address, idx) in newGuardian.addresses" :key="idx">
              <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 transition-all duration-200 hover:border-green-300 hover:bg-green-50/50">
                <!-- Header do card com tipo e ações -->
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-3">
                    <select v-model="address.address_for" class="px-6 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 bg-white text-sm font-medium">
                      <option value="casa">🏠 Casa</option>
                      <option value="trabalho">🏢 Trabalho</option>
                      <option value="outro">🏷️ Outro</option>
                    </select>
                    <button 
                      type="button" 
                      @click="principalAddressIndex = idx" 
                      :class="['px-3 py-1 rounded-full text-xs font-medium transition-colors', principalAddressIndex === idx ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600 hover:bg-green-100']"
                      title="Marcar como principal"
                    >
                      {{ principalAddressIndex === idx ? '✅ Principal' : ' Principal' }}
                    </button>
                  </div>
                  <button 
                    v-if="newGuardian.addresses.length > 1" 
                    type="button" 
                    @click="removeAddress(idx)" 
                    class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" 
                    title="Remover endereço"
                  > 🅧 Remover
                  </button>
                </div>

                <!-- CEP com busca automática -->
                <div class="mb-4">
                  <div class="flex gap-2">
                    <div class="flex-1">
                      <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                      <input 
                        v-model="address.zip_code" 
                        @blur="buscarCep(idx)" 
                        maxlength="9" 
                        placeholder="00000-000" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 bg-white text-sm" 
                      />
                    </div>
                    <div class="flex items-end">
                      <button 
                        type="button" 
                        @click="buscarCep(idx)" 
                        :disabled="address.loading" 
                        class="h-10 px-3 border border-gray-300 rounded-lg bg-white hover:border-green-400 transition disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        <svg v-if="!address.loading" class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <svg v-else class="animate-spin w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                      </button>
                    </div>
                  </div>
                  <div v-if="address.error" class="text-xs text-red-500 mt-1">{{ address.error }}</div>
                </div>

                <!-- Endereço completo -->
                <div class="space-y-3">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro</label>
                    <input v-model="address.street" placeholder="Rua, Avenida, etc." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 bg-white text-sm" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                    <input v-model="address.complement" placeholder="Apto, bloco, casa, etc." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 bg-white text-sm" />
                  </div>
                  <div class="flex gap-3">
                    <div class="flex-1">
                      <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                      <input v-model="address.neighborhood" placeholder="Bairro" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 bg-white text-sm" />
                    </div>
                    <div class="flex-1">
                      <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                      <input v-model="address.city" placeholder="Cidade" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 bg-white text-sm" />
                    </div>
                  </div>
                  <div class="flex gap-3">
                    <div class="w-24">
                      <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                      <input v-model="address.number" placeholder="Nº" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 bg-white text-sm" />
                    </div>
                    <div class="w-20">
                      <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                      <input v-model="address.state" maxlength="2" placeholder="UF" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 bg-white text-sm" />
                    </div>
                  </div>
                </div>
              </div>
            </template>
            
            <!-- Card de adicionar endereço -->
            <div @click="addAddress" class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl min-h-[280px] cursor-pointer hover:border-green-400 hover:bg-green-50 transition group">
              <svg class="w-8 h-8 text-gray-400 group-hover:text-green-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
              <span class="text-base font-medium text-gray-500 group-hover:text-green-600">Adicionar endereço</span>
              <span class="text-sm text-gray-400 group-hover:text-green-500 mt-1">Clique para incluir outro endereço</span>
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
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <template v-for="(contact, idx) in newGuardian.contacts" :key="idx">
              <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 transition-all duration-200 hover:border-green-300 hover:bg-green-50/50">
                <!-- Header do card -->
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-3">
                    <select v-model="contact.type" class="px-6 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 bg-white text-sm font-medium">
                      <option value="phone">📱 Telefone</option>
                      <option value="email">✉️ E-mail</option>
                    </select>
                    <button 
                      type="button" 
                      @click="principalContactIndex = idx" 
                      :class="['px-3 py-1 rounded-full text-xs font-medium transition-colors', principalContactIndex === idx ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600 hover:bg-green-100']"
                      title="Marcar como principal"
                    >
                      {{ principalContactIndex === idx ? '✅ Principal' : ' Principal' }}
                    </button>
                  </div>
                  <button 
                    v-if="newGuardian.contacts.length > 1" 
                    type="button" 
                    @click="removeContact(idx)" 
                    class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" 
                    title="Remover contato"
                  > 🅧 Remover
                  </button>
                </div>

                <!-- Campos do contato -->
                <div class="space-y-3">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      {{ contact.type === 'email' ? 'E-mail' : 'Telefone' }}
                    </label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg v-if="contact.type === 'phone'" class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <svg v-else class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                      </div>
                      <input 
                        v-model="contact.value" 
                        :type="contact.type === 'email' ? 'email' : 'text'" 
                        :placeholder="contact.type === 'phone' ? '(00) 00000-0000' : 'email@exemplo.com'" 
                        class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 bg-white" 
                        required 
                      />
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                    <select v-model="contact.label" class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 bg-white">
                      <option value="pessoal">📱 Whastapp/Pessoal</option>
                      <option value="fixo">📞 Fixo</option>
                      <option value="trabalho">💼 Trabalho</option>
                      <option value="emergencia">🚨 Emergência</option>
                    </select>
                  </div>
                </div>
              </div>
            </template>
            
            <!-- Card de adicionar contato -->
            <div @click="addContact" class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl min-h-[200px] cursor-pointer hover:border-green-400 hover:bg-green-50 transition group">
              <svg class="w-8 h-8 text-gray-400 group-hover:text-green-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
              <span class="text-base font-medium text-gray-500 group-hover:text-green-600">Adicionar contato</span>
              <span class="text-sm text-gray-400 group-hover:text-green-500 mt-1">Telefone ou e-mail</span>
            </div>
          </div>
        </div>

        <!-- Seção: Responsabilidade -->
        <div class="bg-white rounded-xl p-6 border border-green-200 shadow-sm">
          <h4 class="flex items-center text-lg font-semibold text-gray-800 mb-6">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
              </svg>
            </div>
            Responsabilidade
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Responsável</label>
              <select 
                v-model="newGuardian.guardian_type" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
              >
                <option value="titular">Titular</option>
                <option value="suplente">Suplente</option>
                <option value="financeiro">Financeiro</option>
                <option value="emergencia">Emergência</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Parentesco/Relacionamento</label>
              <select 
                v-model="newGuardian.relationship" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
              >
                <option value="">Selecione</option>
                <option value="pai">Pai</option>
                <option value="mae">Mãe</option>
                <option value="avo">Avô</option>
                <option value="avo_feminino">Avó</option>
                <option value="tio">Tio</option>
                <option value="tia">Tia</option>
                <option value="padrasto">Padrasto</option>
                <option value="madrasta">Madrasta</option>
                <option value="tutor">Tutor Legal</option>
                <option value="outro">Outro</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Seção: Dados Profissionais -->
        <div class="bg-white rounded-xl p-6 border border-green-200 shadow-sm">
          <h4 class="flex items-center text-lg font-semibold text-gray-800 mb-6">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0H8m8 0v2a2 2 0 01-2 2H10a2 2 0 01-2-2V6"/>
              </svg>
            </div>
            Dados Profissionais
          </h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Profissão</label>
              <input 
                v-model="newGuardian.occupation" 
                type="text" 
                placeholder="Ex: Engenheiro, Professor, etc." 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Local de Trabalho</label>
              <input 
                v-model="newGuardian.workplace" 
                type="text" 
                placeholder="Nome da empresa/instituição" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
              />
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
              v-model="newGuardian.notes" 
              rows="4"
              placeholder="Informações adicionais relevantes sobre o responsável..." 
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
import { ref, reactive, computed, watch, onMounted } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import axios from 'axios';

const props = defineProps({
  student: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(['next']);

const guardians = ref([]);
const search = ref('');
const loading = ref(false);
const selectedGuardian = ref(null);
const submitting = ref(false);
const errorMessage = ref('');

const page = usePage();

const fetchGuardians = debounce(async () => {
  if (!search.value) {
    guardians.value = [];
    selectedGuardian.value = null;
    return;
  }
  loading.value = true;
  try {
    let response;
    // Se props.student existe, buscar responsáveis não vinculados a este aluno
    if (props.student) {
      response = await axios.get(`/api/students/${props.student.id}/guardians/search-not-linked`, {
        params: { q: search.value }
      });
    } else {
      response = await axios.get('/api/guardians', {
        params: { q: search.value }
      });
    }
    
    guardians.value = response.data;
  } catch (error) {
    console.error('Erro na busca:', error);
    guardians.value = [];
  } finally {
    loading.value = false;
  }
}, 400);

function onSearch() {
  fetchGuardians();
}

function selectGuardian(guardian) {
  selectedGuardian.value = guardian;
}

function confirmGuardian() {
  if (selectedGuardian.value) {
    emit('next', selectedGuardian.value);
  }
}

const newGuardian = reactive({
  name: '',
  cpf: '',
  email: '',
  rg: '',
  birth_date: '',
  gender: '',
  marital_status: '',
  guardian_type: 'titular',
  relationship: '',
  occupation: '',
  workplace: '',
  notes: '',
  status: 'active',
  contacts: [
    { type: 'phone', value: '', label: 'pessoal' }
  ],
  addresses: [
    { zip_code: '', street: '', number: '', complement: '', neighborhood: '', city: '', state: '', address_for: 'casa', is_primary: true, loading: false, error: '' }
  ]
});

const principalContactIndex = ref(0);
const principalAddressIndex = ref(0);

function addContact() {
  newGuardian.contacts.push({ type: 'phone', value: '', label: 'pessoal' });
}

function removeContact(idx) {
  if (newGuardian.contacts.length === 1) return;
  newGuardian.contacts.splice(idx, 1);
  if (principalContactIndex.value === idx) {
    principalContactIndex.value = 0;
  } else if (principalContactIndex.value > idx) {
    principalContactIndex.value--;
  }
}

watch(
  () => newGuardian.contacts.length,
  (len) => {
    if (principalContactIndex.value >= len) {
      principalContactIndex.value = 0;
    }
  }
);

function submitNewGuardian() {
  if (submitting.value) return;
  submitting.value = true;
  errorMessage.value = '';

  // Validar dados obrigatórios
  if (!newGuardian.name || !newGuardian.cpf) {
    errorMessage.value = 'Nome e CPF são obrigatórios.';
    submitting.value = false;
    return;
  }

  // Preparar dados para envio
  const addresses = (newGuardian.addresses || []).map(addr => ({
    zip_code: addr.zip_code,
    street: addr.street,
    number: addr.number,
    complement: addr.complement,
    neighborhood: addr.neighborhood,
    city: addr.city,
    state: addr.state,
    address_for: addr.address_for,
    is_primary: addr.is_primary,
  }));
  const contacts = (newGuardian.contacts || []).map(contact => ({
    type: contact.type,
    value: contact.value,
    label: contact.label,
  }));
  
  form.name = newGuardian.name;
  form.cpf = newGuardian.cpf;
  form.rg = newGuardian.rg;
  form.birth_date = newGuardian.birth_date;
  form.gender = newGuardian.gender;
  form.marital_status = newGuardian.marital_status;
  form.guardian_type = newGuardian.guardian_type;
  form.relationship = newGuardian.relationship;
  form.occupation = newGuardian.occupation;
  form.workplace = newGuardian.workplace;
  form.notes = newGuardian.notes;
  form.status = newGuardian.status;
  form.addresses = addresses;
  form.contacts = contacts;
  
  form.post(route('guardian.store'), {
    preserveState: true,
    replace: true,
    onSuccess: (page) => {
    },
    onError: (errors) => {
      errorMessage.value = 'Erro ao cadastrar o responsável. Verifique os dados e tente novamente.';
      // Scroll to top to show the error message
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    onFinish: () => {
      submitting.value = false;
    }
  });
}

function addAddress() {
  if (!newGuardian.addresses) newGuardian.addresses = [];
  newGuardian.addresses.push({ zip_code: '', street: '', number: '', complement: '', neighborhood: '', city: '', state: '', address_for: 'casa', is_primary: false, loading: false, error: '' });
}

function removeAddress(idx) {
  if (!newGuardian.addresses || newGuardian.addresses.length === 1) return;
  newGuardian.addresses.splice(idx, 1);
  if (principalAddressIndex.value === idx) principalAddressIndex.value = 0;
  else if (principalAddressIndex.value > idx) principalAddressIndex.value--;
}

async function buscarCep(idx) {
  if (!newGuardian.addresses || !newGuardian.addresses[idx]) return;
  const addr = newGuardian.addresses[idx];
  if (!addr.zip_code || addr.zip_code.length < 8) return;
  addr.loading = true;
  addr.error = '';
  try {
    const res = await axios.get(`https://viacep.com.br/ws/${addr.zip_code.replace(/\D/g, '')}/json/`);
    const data = res.data;
    if (data.erro) throw new Error('CEP não encontrado');
    addr.street = data.logradouro || '';
    addr.neighborhood = data.bairro || '';
    addr.city = data.localidade || '';
    addr.state = data.uf || '';
  } catch (e) {
    addr.error = 'CEP não encontrado';
    addr.street = addr.neighborhood = addr.city = addr.state = '';
  } finally {
    addr.loading = false;
  }
}

watch(() => newGuardian.addresses && newGuardian.addresses.length, len => {
  if (principalAddressIndex.value >= len) principalAddressIndex.value = 0;
});

async function fetchGuardianByCpf(cpf) {
  const response = await axios.get(`/api/guardians?q=${encodeURIComponent(cpf)}`);
  const results = response.data;
  return results && results.length ? results[0] : null;
}

watch(
  () => page.props.flash.success,
  (success) => {
    if (success) {
      emit('next', page.props.flash.guardian || selectedGuardian.value || { ...newGuardian });
    }
  }
);

// Limpar mensagem de erro quando o usuário começar a digitar
watch([() => newGuardian.name, () => newGuardian.cpf], () => {
  if (errorMessage.value) {
    errorMessage.value = '';
  }
});
</script>