<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg max-w-6xl w-full p-6 mx-4 max-h-[95vh] overflow-y-auto">
      <h3 class="text-lg font-semibold mb-4">Adicionar Responsável</h3>
      
      <!-- Toggle entre buscar e criar -->
      <div class="mb-6 flex bg-gray-100 rounded-lg p-1">
        <button
          @click="addMode = 'search'"
          :class="[
            addMode === 'search' 
              ? 'bg-white text-blue-600 shadow-sm' 
              : 'text-gray-500',
            'flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors'
          ]"
        >
          Buscar Existente
        </button>
        <button
          @click="addMode = 'create'"
          :class="[
            addMode === 'create' 
              ? 'bg-white text-blue-600 shadow-sm' 
              : 'text-gray-500',
            'flex-1 py-2 px-4 text-sm font-medium rounded-md transition-colors'
          ]"
        >
          Criar Novo
        </button>
      </div>

      <!-- Modo buscar existente -->
      <div v-if="addMode === 'search'">
        <div class="bg-gray-50 rounded-xl p-6 border-2 border-dashed border-gray-300 hover:border-blue-400 transition-colors duration-200">
          <div class="flex items-center mb-4">
            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <label class="text-sm font-semibold text-gray-700">Buscar responsável existente</label>
          </div>
          
          <div class="relative">
            <input 
              v-model="searchTerm" 
              @input="searchGuardians" 
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
          <div v-if="searchTerm && searchResults.length" class="mt-4 bg-white rounded-lg shadow-md border border-gray-200 max-h-60 overflow-y-auto">
            <div 
              v-for="guardian in searchResults" 
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
          <div v-if="searchTerm && !searchResults.length && !loading" class="mt-4 text-center py-4">
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
          </div>
        </div>
      </div>

      <!-- Modo criar novo -->
      <div v-else class="space-y-6">
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
                v-model="newGuardianForm.name" 
                type="text" 
                placeholder="Nome completo do responsável" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
                required 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">CPF *</label>
              <input 
                v-model="newGuardianForm.cpf" 
                type="text" 
                placeholder="000.000.000-00" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
                required 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">RG</label>
              <input 
                v-model="newGuardianForm.rg" 
                type="text" 
                placeholder="00.000.000-0" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Data de Nascimento</label>
              <input 
                v-model="newGuardianForm.birth_date" 
                type="date" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Gênero</label>
              <select 
                v-model="newGuardianForm.gender" 
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
                v-model="newGuardianForm.marital_status" 
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

        <!-- Seção: Endereços -->
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
            <template v-for="(address, idx) in newGuardianForm.addresses" :key="idx">
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
                    v-if="newGuardianForm.addresses.length > 1" 
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
            <template v-for="(contact, idx) in newGuardianForm.contacts" :key="idx">
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
                    v-if="newGuardianForm.contacts.length > 1" 
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
                      <option value="pessoal">📱 WhatsApp/Pessoal</option>
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
                v-model="newGuardianForm.guardian_type" 
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
                v-model="newGuardianForm.relationship" 
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
                v-model="newGuardianForm.occupation" 
                type="text" 
                placeholder="Ex: Engenheiro, Professor, etc." 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Local de Trabalho</label>
              <input 
                v-model="newGuardianForm.workplace" 
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
              v-model="newGuardianForm.notes" 
              rows="4"
              placeholder="Informações adicionais relevantes sobre o responsável..." 
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 resize-none" 
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Botões do modal -->
      <div class="flex justify-end space-x-2 mt-6 pt-6 border-t border-gray-200">
        <button 
          @click="$emit('close')"
          class="px-6 py-3 text-gray-600 hover:text-gray-800 font-medium"
          :disabled="loading"
        >
          Cancelar
        </button>
        <button 
          @click="handleAdd"
          :disabled="!canAdd || loading"
          class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 font-medium"
        >
          {{ loading ? 'Adicionando...' : (addMode === 'create' ? 'Criar e Vincular' : 'Adicionar') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import axios from 'axios'
import { debounce } from 'lodash'

const props = defineProps({
  student: Object,
  loading: Boolean
})

const emit = defineEmits(['close', 'add'])

// Estados
const addMode = ref('search')
const selectedGuardian = ref(null)
const searchTerm = ref('')
const searchResults = ref([])
const loading = ref(false)

const principalContactIndex = ref(0)
const principalAddressIndex = ref(0)

const newGuardianForm = reactive({
  name: '',
  cpf: '',
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
    { type: 'phone', value: '', label: 'pessoal', is_primary: true }
  ],
  addresses: [
    { 
      zip_code: '', 
      street: '', 
      number: '', 
      complement: '', 
      neighborhood: '', 
      city: '', 
      state: '', 
      address_for: 'casa', 
      is_primary: true, 
      loading: false, 
      error: '' 
    }
  ]
})

// Buscar responsáveis não vinculados (com debounce) - mantém axios para autocomplete
const searchGuardians = debounce(async () => {
  if (searchTerm.value.length < 2) {
    searchResults.value = []
    return
  }
  
  loading.value = true
  try {
    const response = await axios.get(route('students.guardians.search-not-linked', props.student.id), {
      params: { q: searchTerm.value }
    })
    searchResults.value = response.data
  } catch (error) {
    console.error('Erro na busca:', error)
    searchResults.value = []
  } finally {
    loading.value = false
  }
}, 300)

// Selecionar responsável da lista
const selectGuardian = (guardian) => {
  selectedGuardian.value = guardian
  searchResults.value = []
  searchTerm.value = guardian.name
}

// Computed para validar se pode adicionar
const canAdd = computed(() => {
  if (addMode.value === 'search') {
    return selectedGuardian.value !== null
  } else {
    return newGuardianForm.name && newGuardianForm.cpf && 
           newGuardianForm.contacts.some(c => c.value) &&
           newGuardianForm.addresses.some(a => a.zip_code || a.street)
  }
})

// Adicionar responsável
const handleAdd = () => {
  if (addMode.value === 'search') {
    emit('add', {
      mode: 'search',
      guardian_id: selectedGuardian.value.id
    })
  } else {
    // Preparar dados para envio
    const addresses = newGuardianForm.addresses.map((addr, idx) => ({
      zip_code: addr.zip_code,
      street: addr.street,
      number: addr.number,
      complement: addr.complement,
      neighborhood: addr.neighborhood,
      city: addr.city,
      state: addr.state,
      address_for: addr.address_for,
      is_primary: principalAddressIndex.value === idx,
    }))
    
    const contacts = newGuardianForm.contacts.map((contact, idx) => ({
      type: contact.type,
      value: contact.value,
      label: contact.label,
      is_primary: principalContactIndex.value === idx,
      contact_for: contact.label,
    }))

    emit('add', {
      mode: 'create',
      guardianData: {
        ...newGuardianForm,
        addresses,
        contacts,
      }
    })
  }
}

// Funções para contatos
const addContact = () => {
  newGuardianForm.contacts.push({ type: 'phone', value: '', label: 'pessoal', is_primary: false })
}

const removeContact = (idx) => {
  if (newGuardianForm.contacts.length === 1) return
  newGuardianForm.contacts.splice(idx, 1)
  if (principalContactIndex.value === idx) {
    principalContactIndex.value = 0
  } else if (principalContactIndex.value > idx) {
    principalContactIndex.value--
  }
}

// Funções para endereços
const addAddress = () => {
  newGuardianForm.addresses.push({ 
    zip_code: '', 
    street: '', 
    number: '', 
    complement: '', 
    neighborhood: '', 
    city: '', 
    state: '', 
    address_for: 'casa', 
    is_primary: false, 
    loading: false, 
    error: '' 
  })
}

const removeAddress = (idx) => {
  if (newGuardianForm.addresses.length === 1) return
  newGuardianForm.addresses.splice(idx, 1)
  if (principalAddressIndex.value === idx) {
    principalAddressIndex.value = 0
  } else if (principalAddressIndex.value > idx) {
    principalAddressIndex.value--
  }
}

// Buscar CEP
const buscarCep = async (idx) => {
  const addr = newGuardianForm.addresses[idx]
  if (!addr.zip_code || addr.zip_code.length < 8) return
  
  addr.loading = true
  addr.error = ''
  
  try {
    const res = await fetch(`https://viacep.com.br/ws/${addr.zip_code.replace(/\D/g, '')}/json/`)
    const data = await res.json()
    
    if (data.erro) throw new Error('CEP não encontrado')
    
    addr.street = data.logradouro || ''
    addr.neighborhood = data.bairro || ''
    addr.city = data.localidade || ''
    addr.state = data.uf || ''
  } catch (e) {
    addr.error = 'CEP não encontrado'
    addr.street = addr.neighborhood = addr.city = addr.state = ''
  } finally {
    addr.loading = false
  }
}

// Watchers para validação
watch(() => newGuardianForm.contacts.length, (len) => {
  if (principalContactIndex.value >= len) {
    principalContactIndex.value = 0
  }
})

watch(() => newGuardianForm.addresses.length, (len) => {
  if (principalAddressIndex.value >= len) {
    principalAddressIndex.value = 0
  }
})
</script> 