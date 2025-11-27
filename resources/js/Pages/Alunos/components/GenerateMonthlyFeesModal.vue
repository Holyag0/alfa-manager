<template>
  <div class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
      <!-- Background overlay -->
      <div 
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
        aria-hidden="true"
        @click="close"
      ></div>

      <!-- Center modal -->
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <!-- Modal Container - Layout Horizontal -->
      <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-4xl sm:w-full">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-xl font-semibold text-white" id="modal-title">
                  Mensalidades
                </h3>
                <p class="text-sm text-indigo-100 mt-0.5">
                  {{ student.name }} • {{ enrollment?.classroom?.name || 'N/A' }}
                </p>
              </div>
            </div>
            <button
              @click="close"
              class="text-indigo-200 hover:text-white focus:outline-none"
            >
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          </div>

        <!-- Tabs -->
        <div class="border-b border-gray-200 bg-gray-50">
          <nav class="flex -mb-px" aria-label="Tabs">
            <button
              @click="activeTab = 'generate'"
              :class="[
                'flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition-colors',
                activeTab === 'generate'
                  ? 'border-indigo-500 text-indigo-600 bg-white'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Gerar Mensalidades
            </button>
            <button
              @click="activeTab = 'add'"
              :class="[
                'flex-1 py-4 px-6 text-center border-b-2 font-medium text-sm transition-colors',
                activeTab === 'add'
                  ? 'border-indigo-500 text-indigo-600 bg-white'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
              Adicionar Mensalidade
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="px-6 py-6">
          <!-- Aba: Gerar Mensalidades -->
          <div v-show="activeTab === 'generate'">
            <form @submit.prevent="handleGenerateSubmit" class="space-y-5">
              <!-- Aviso se já existem mensalidades no intervalo -->
              <div v-if="hasExistingInRange" class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                <div class="flex items-start">
                  <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                  </svg>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-yellow-900">
                      Mensalidades já existem no período selecionado
                    </p>
                    <p class="text-xs text-yellow-800 mt-1">
                      Existem {{ existingInRangeCount }} mensalidade(s) no período de {{ months[generateForm.start_month - 1].label }} a {{ months[generateForm.end_month - 1].label }} de {{ generateForm.academic_year }}.
                    </p>
                    <p class="text-xs text-yellow-800 mt-2">
                      <strong>Atenção:</strong> As mensalidades existentes neste período serão substituídas pelas novas mensalidades.
                    </p>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Coluna Esquerda -->
                <div class="space-y-5">
                  <!-- Ano Letivo -->
                  <div>
                    <label for="generate_academic_year" class="block text-sm font-medium text-gray-700 mb-1">
                      Ano Letivo *
                    </label>
                    <select
                      id="generate_academic_year"
                      v-model="generateForm.academic_year"
                      @change="checkExistingMonthlyFees"
                      required
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                      <option v-for="year in availableYears" :key="year" :value="year">
                        {{ year }} {{ getEnrollmentLabel(year) }}
                      </option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                      {{ availableYears.length }} ano(s) letivo(s) disponível(is)
                    </p>
                    
                    <!-- Aviso se ano letivo anterior ou matrícula completada -->
                    <div v-if="generateValidationWarning" class="mt-2 p-3 bg-red-50 border border-red-200 rounded-md">
                      <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1">
                          <p class="text-sm font-medium text-red-900">
                            {{ generateValidationWarning.title }}
                          </p>
                          <p class="text-xs text-red-800 mt-1">
                            {{ generateValidationWarning.message }}
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>

            <!-- Período das Mensalidades -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Período das Mensalidades
              </label>
              
              <!-- Botão de Ano Completo -->
              <button
                type="button"
                @click="setFullYear"
                class="w-full mb-3 px-4 py-2 border border-indigo-300 rounded-md text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              >
                <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Ano Completo (Janeiro a Dezembro)
              </button>

                    <div class="grid grid-cols-2 gap-3">
                <!-- Mês de Início -->
                <div>
                        <label for="generate_start_month" class="block text-xs font-medium text-gray-600 mb-1">
                    Mês de Início
                  </label>
                  <select
                          id="generate_start_month"
                          v-model="generateForm.start_month"
                    @change="validateMonthRange"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  >
                    <option v-for="month in months" :key="month.value" :value="month.value">
                      {{ month.label }}
                    </option>
                  </select>
                </div>

                <!-- Mês de Término -->
                <div>
                        <label for="generate_end_month" class="block text-xs font-medium text-gray-600 mb-1">
                    Mês de Término
                  </label>
                  <select
                          id="generate_end_month"
                          v-model="generateForm.end_month"
                    @change="validateMonthRange"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  >
                    <option v-for="month in months" :key="month.value" :value="month.value">
                      {{ month.label }}
                    </option>
                  </select>
                </div>
              </div>

                    <!-- Informação de Quantidade -->
              <div class="mt-3 p-3 bg-blue-50 rounded-md border border-blue-200">
                <div class="flex items-center">
                  <svg class="h-5 w-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                  </svg>
                  <div class="text-sm">
                    <span class="font-medium text-blue-900">{{ totalInstallments }} mensalidade{{ totalInstallments !== 1 ? 's' : '' }}</span>
                    <span class="text-blue-700"> serão geradas</span>
                  </div>
                </div>
                <p class="mt-1 text-xs text-blue-600 ml-7">
                  {{ getPeriodDescription() }}
                </p>
              </div>
                  </div>
            </div>

                <!-- Coluna Direita -->
                <div class="space-y-5">
            <!-- Dia de Vencimento -->
            <div>
                    <label for="generate_due_day" class="block text-sm font-medium text-gray-700 mb-1">
                Dia de Vencimento
              </label>
              <select
                      id="generate_due_day"
                      v-model="generateForm.due_day"
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
                <option v-for="day in 31" :key="day" :value="day">
                  Dia {{ day }}
                </option>
              </select>
              <p class="mt-1 text-xs text-gray-500">
                      Vencimento todo dia {{ generateForm.due_day }} de cada mês
              </p>
            </div>

            <!-- Desconto de Pontualidade -->
                  <div class="space-y-3">
            <div class="flex items-start">
              <div class="flex items-center h-5">
                <input
                          id="generate_has_punctuality_discount"
                          v-model="generateForm.has_punctuality_discount"
                  type="checkbox"
                  class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                />
              </div>
              <div class="ml-3 text-sm">
                        <label for="generate_has_punctuality_discount" class="font-medium text-gray-700">
                  Aplicar desconto de pontualidade
                </label>
                        <p class="text-gray-500 text-xs">Desconto ao pagar em dia</p>
              </div>
            </div>

                    <!-- Valor do Desconto -->
                    <div v-if="generateForm.has_punctuality_discount">
                      <label for="generate_punctuality_discount_amount" class="block text-xs font-medium text-gray-700 mb-1">
                Valor do Desconto (R$)
              </label>
                      <div class="relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-500 sm:text-sm">R$</span>
                </div>
                <input
                  type="number"
                          id="generate_punctuality_discount_amount"
                          v-model="generateForm.punctuality_discount_amount"
                  step="0.01"
                  min="0"
                          class="block w-full pl-10 rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  placeholder="0,00"
                />
                      </div>
              </div>
            </div>

            <!-- Observações -->
            <div>
                    <label for="generate_notes" class="block text-sm font-medium text-gray-700 mb-1">
                Observações (opcional)
              </label>
              <textarea
                      id="generate_notes"
                      v-model="generateForm.notes"
                rows="3"
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      placeholder="Adicione observações sobre este contrato..."
              ></textarea>
                  </div>
                </div>
            </div>

            <!-- Mensagem de erro -->
            <div v-if="error" :class="[
              'rounded-md p-4 border',
              isConfirmedPaymentsError 
                ? 'bg-orange-50 border-orange-300' 
                : 'bg-red-50 border-red-200'
            ]">
              <div class="flex items-start">
                <svg v-if="isConfirmedPaymentsError" class="h-5 w-5 text-orange-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <svg v-else class="h-5 w-5 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="ml-3 flex-1">
                  <p v-if="isConfirmedPaymentsError" class="text-sm font-semibold text-orange-900 mb-2">
                    ⚠️ Pagamentos Confirmados Encontrados
                  </p>
                  <p :class="[
                    'text-sm',
                    isConfirmedPaymentsError ? 'text-orange-800' : 'text-red-800 font-medium'
                  ]">{{ error }}</p>
                  <p v-if="isConfirmedPaymentsError" class="text-xs text-orange-700 mt-3 font-medium">
                    💡 <strong>Ação necessária:</strong> Acesse a página de mensalidades do aluno e reverta os pagamentos confirmados antes de tentar substituir as mensalidades novamente.
                  </p>
                </div>
              </div>
            </div>

              <!-- Botões -->
              <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <button
                  type="button"
                  @click="close"
                  :disabled="submitting"
                  class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  :disabled="submitting || !!generateValidationWarning"
                  class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ submitting ? 'Gerando...' : 'Gerar Mensalidades' }}
                </button>
              </div>
            </form>
          </div>

          <!-- Aba: Adicionar Mensalidade -->
          <div v-show="activeTab === 'add'">
            <form @submit.prevent="handleAddSubmit" class="space-y-5">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Coluna Esquerda -->
                <div class="space-y-5">
                  <!-- Ano Letivo -->
                  <div>
                    <label for="add_academic_year" class="block text-sm font-medium text-gray-700 mb-1">
                      Ano Letivo *
                    </label>
                    <select
                      id="add_academic_year"
                      v-model="addForm.academic_year"
                      @change="checkExistingForAdd"
                      required
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                      <option v-for="year in availableYears" :key="year" :value="year">
                        {{ year }} {{ getEnrollmentLabel(year) }}
                      </option>
                    </select>
                    
                    <!-- Aviso se ano letivo anterior ou matrícula completada -->
                    <div v-if="addValidationWarning" class="mt-2 p-3 bg-red-50 border border-red-200 rounded-md">
                      <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="flex-1">
                          <p class="text-sm font-medium text-red-900">
                            {{ addValidationWarning.title }}
                          </p>
                          <p class="text-xs text-red-800 mt-1">
                            {{ addValidationWarning.message }}
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Mês -->
                  <div>
                    <label for="add_month" class="block text-sm font-medium text-gray-700 mb-1">
                      Mês de Referência *
                    </label>
                    <select
                      id="add_month"
                      v-model="addForm.month"
                      required
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                      <option v-for="month in months" :key="month.value" :value="month.value">
                        {{ month.label }}
                      </option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                      Mensalidade referente a este mês
                    </p>
                  </div>

                  <!-- Dia de Vencimento -->
                  <div>
                    <label for="add_due_day" class="block text-sm font-medium text-gray-700 mb-1">
                      Dia de Vencimento *
                    </label>
                    <select
                      id="add_due_day"
                      v-model="addForm.due_day"
                      required
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                      <option v-for="day in 31" :key="day" :value="day">
                        Dia {{ day }}
                      </option>
                    </select>
                  </div>
                </div>

                <!-- Coluna Direita -->
                <div class="space-y-5">
                  <!-- Aviso se já existe mensalidade para o mês -->
                  <div v-if="hasExistingForMonth" class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <div class="flex items-start">
                      <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                      </svg>
                      <div class="flex-1">
                        <p class="text-sm font-medium text-yellow-900">
                          Já existe mensalidade para este mês
                        </p>
                        <p class="text-xs text-yellow-800 mt-1">
                          Uma mensalidade para {{ months[addForm.month - 1]?.label }} de {{ addForm.academic_year }} já foi criada.
                  </p>
                </div>
                    </div>
                  </div>

                  <!-- Observações -->
                  <div>
                    <label for="add_notes" class="block text-sm font-medium text-gray-700 mb-1">
                      Observações (opcional)
                    </label>
                    <textarea
                      id="add_notes"
                      v-model="addForm.notes"
                      rows="4"
                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      placeholder="Adicione observações sobre esta mensalidade..."
                    ></textarea>
                  </div>
                </div>
              </div>

              <!-- Mensagem de erro -->
              <div v-if="error" class="rounded-md bg-red-50 p-4 border border-red-200">
                <div class="flex">
                  <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                  </svg>
                  <p class="ml-3 text-sm font-medium text-red-800">{{ error }}</p>
              </div>
            </div>

            <!-- Botões -->
              <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
              <button
                type="button"
                @click="close"
                :disabled="submitting"
                  class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
              >
                Cancelar
              </button>
                <button
                  type="submit"
                  :disabled="submitting || hasExistingForMonth || !!addValidationWarning"
                  class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ submitting ? 'Adicionando...' : 'Adicionar Mensalidade' }}
                </button>
            </div>
          </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Confirmação -->
  <div v-if="showConfirmModal" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showConfirmModal = false"></div>
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
        <div class="sm:flex sm:items-start">
          <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
              Confirmar Geração de Mensalidades
            </h3>
            <div class="mt-4">
              <div class="text-sm text-gray-600 space-y-2">
                <p>
                  <strong>Aluno:</strong> {{ student.name }}
                </p>
                <p>
                  <strong>Ano Letivo:</strong> {{ generateForm.academic_year }}
                </p>
                <p>
                  <strong>Período:</strong> {{ months[generateForm.start_month - 1].label }} a {{ months[generateForm.end_month - 1].label }}
                </p>
                <p>
                  <strong>Quantidade:</strong> {{ totalInstallments }} mensalidade{{ totalInstallments !== 1 ? 's' : '' }}
                </p>
                <p>
                  <strong>Dia de Vencimento:</strong> Todo dia {{ generateForm.due_day }}
                </p>
                <div v-if="hasExistingInRange" class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                  <p class="text-sm font-medium text-yellow-900">
                    ⚠️ Atenção
                  </p>
                  <p class="text-xs text-yellow-800 mt-1">
                    Existem {{ existingInRangeCount }} mensalidade(s) no período selecionado que serão substituídas pelas novas mensalidades.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
          <button
            type="button"
            @click="confirmGenerate"
            :disabled="submitting"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ submitting ? 'Gerando...' : 'Confirmar e Gerar' }}
          </button>
          <button
            type="button"
            @click="showConfirmModal = false"
            :disabled="submitting"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm disabled:opacity-50"
          >
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  student: {
    type: Object,
    required: true
  },
  enrollment: {
    type: Object,
    required: true
  },
  installments: {
    type: Array,
    default: () => []
  },
  enrollments: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'success'])

const activeTab = ref('generate')
const submitting = ref(false)
const error = ref(null)
const hasExistingMonthlyFees = ref(false)
const existingInstallmentsCount = ref(0)
const hasExistingInRange = ref(false)
const existingInRangeCount = ref(0)
const hasExistingForMonth = ref(false)
const showConfirmModal = ref(false)

const months = [
  { value: 1, label: 'Janeiro', short: 'Jan' },
  { value: 2, label: 'Fevereiro', short: 'Fev' },
  { value: 3, label: 'Março', short: 'Mar' },
  { value: 4, label: 'Abril', short: 'Abr' },
  { value: 5, label: 'Maio', short: 'Mai' },
  { value: 6, label: 'Junho', short: 'Jun' },
  { value: 7, label: 'Julho', short: 'Jul' },
  { value: 8, label: 'Agosto', short: 'Ago' },
  { value: 9, label: 'Setembro', short: 'Set' },
  { value: 10, label: 'Outubro', short: 'Out' },
  { value: 11, label: 'Novembro', short: 'Nov' },
  { value: 12, label: 'Dezembro', short: 'Dez' }
]

// Gerar lista de anos disponíveis
const availableYears = computed(() => {
  if (!props.enrollments || props.enrollments.length === 0) {
    const currentYear = new Date().getFullYear()
    return [currentYear.toString()]
  }
  
  const years = new Set()
  props.enrollments.forEach(enrollment => {
    if (enrollment.academic_year && enrollment.status === 'active') {
      years.add(enrollment.academic_year.toString())
    }
  })
  
  if (years.size === 0 && props.enrollment?.academic_year) {
    years.add(props.enrollment.academic_year.toString())
  }
  
  return Array.from(years).sort((a, b) => parseInt(b) - parseInt(a))
})

const getDefaultAcademicYear = () => {
  if (availableYears.value.length > 0) {
    return availableYears.value[0]
  }
  if (props.enrollment?.academic_year) {
    return props.enrollment.academic_year.toString()
  }
  return new Date().getFullYear().toString()
  }

// Formulário para Gerar Mensalidades
const generateForm = reactive({
  enrollment_id: props.enrollment.id,
  academic_year: getDefaultAcademicYear(),
  start_month: 1,
  end_month: 12,
  due_day: 10,
  has_punctuality_discount: true,
  punctuality_discount_amount: 0,
  replace_existing: false, // Apenas para substituir quando já existem mensalidades
  notes: ''
})

// Formulário para Adicionar Mensalidade
const addForm = reactive({
  enrollment_id: props.enrollment.id,
  academic_year: getDefaultAcademicYear(),
  month: new Date().getMonth() + 1,
  due_day: 10,
  notes: ''
})

// Verificar se já existem mensalidades para o ano letivo selecionado (gerar)
// Ignora mensalidades com soft delete (deleted_at)
const checkExistingMonthlyFees = () => {
  if (!props.installments || props.installments.length === 0) {
    hasExistingMonthlyFees.value = false
    existingInstallmentsCount.value = 0
    hasExistingInRange.value = false
    existingInRangeCount.value = 0
    return
  }
  
  const year = generateForm.academic_year
  const existing = props.installments.filter(inst => {
    // Ignorar mensalidades com soft delete
    if (inst.deleted_at) {
      return false
    }
    
    const instYear = inst.monthly_fee?.academic_year
    return instYear === year || instYear === parseInt(year)
  })
  
  hasExistingMonthlyFees.value = existing.length > 0
  existingInstallmentsCount.value = existing.length
  
  // Verificar mensalidades no intervalo selecionado
  checkExistingInRange()
}

// Verificar mensalidades existentes no intervalo selecionado
const checkExistingInRange = () => {
  if (!props.installments || props.installments.length === 0 || !generateForm.start_month || !generateForm.end_month) {
    hasExistingInRange.value = false
    existingInRangeCount.value = 0
    return
  }
  
  const year = generateForm.academic_year
  const startMonth = generateForm.start_month
  const endMonth = generateForm.end_month
  
  const existingInRange = props.installments.filter(inst => {
    // Ignorar mensalidades com soft delete
    if (inst.deleted_at) {
      return false
    }
    
    const instYear = inst.monthly_fee?.academic_year
    if (instYear !== year && instYear !== parseInt(year)) {
      return false
    }
    
    // Extrair mês do reference_month (formato: YYYY-MM)
    const refMonth = inst.reference_month
    if (!refMonth) return false
    
    const monthStr = refMonth.split('-')[1]
    const month = parseInt(monthStr)
    
    return month >= startMonth && month <= endMonth
  })
  
  hasExistingInRange.value = existingInRange.length > 0
  existingInRangeCount.value = existingInRange.length
}

// Verificar se já existe mensalidade para o mês selecionado (adicionar)
const checkExistingForAdd = () => {
  checkExistingForMonth()
}

const checkExistingForMonth = () => {
  if (!props.installments || props.installments.length === 0 || !addForm.month) {
    hasExistingForMonth.value = false
    return
  }
  
  const year = addForm.academic_year
  const referenceMonth = `${year}-${String(addForm.month).padStart(2, '0')}`
  
  const existing = props.installments.find(inst => {
    // Ignorar mensalidades com soft delete
    if (inst.deleted_at) {
      return false
    }
    
    const instYear = inst.monthly_fee?.academic_year
    const yearMatch = instYear === year || instYear === parseInt(year)
    const monthMatch = inst.reference_month === referenceMonth
    return yearMatch && monthMatch
  })
  
  hasExistingForMonth.value = !!existing
}

// Observar mudanças no mês (aba adicionar)
watch(() => addForm.month, () => {
  checkExistingForMonth()
})

watch(() => addForm.academic_year, () => {
  checkExistingForMonth()
})

// Obter matrícula para um ano letivo específico
const getEnrollmentForYear = (year) => {
  if (!props.enrollments || props.enrollments.length === 0) {
    return props.enrollment
  }
  
  const enrollment = props.enrollments.find(e => 
    e.academic_year === year || e.academic_year === parseInt(year)
  )
  
  return enrollment || props.enrollment
}

const getEnrollmentLabel = (year) => {
  const enrollment = getEnrollmentForYear(year)
  if (enrollment?.classroom?.name) {
    return `(${enrollment.classroom.name})`
  }
  return enrollment ? '(Sem turma)' : ''
}

const validateAcademicYear = (year) => {
  if (!year) {
    return { valid: false, message: 'Ano letivo é obrigatório', reason: 'missing' }
  }
  
  const enrollment = getEnrollmentForYear(year)
  
  if (!enrollment) {
    return { valid: false, message: 'Nenhuma matrícula encontrada para este ano letivo', reason: 'not_found' }
  }
  
  // Verificar se o ano letivo é anterior ao atual
  const currentYear = new Date().getFullYear()
  const academicYear = parseInt(year)
  if (academicYear < currentYear) {
    return { 
      valid: false, 
      message: `Não é possível gerar mensalidades para anos letivos anteriores (${year}). Apenas o ano atual (${currentYear}) ou futuros podem receber mensalidades.`,
      reason: 'past_year'
    }
  }
  
  // Verificar se a matrícula está completada
  if (enrollment.status === 'completed') {
    return { 
      valid: false, 
      message: `Não é possível gerar mensalidades para este ano letivo. A matrícula de ${year} foi completada.`,
      reason: 'completed'
    }
  }
  
  if (enrollment.status !== 'active') {
    return { valid: false, message: 'A matrícula para este ano letivo não está ativa', reason: 'inactive' }
  }
  
  if (!enrollment.classroom_id) {
    return { valid: false, message: 'O aluno não está vinculado a uma turma para este ano letivo', reason: 'no_classroom' }
  }
  
  return { valid: true, enrollment }
}

// Computed para aviso de validação (gerar)
const generateValidationWarning = computed(() => {
  if (!generateForm.academic_year) return null
  
  const validation = validateAcademicYear(generateForm.academic_year)
  if (!validation.valid) {
    if (validation.reason === 'past_year') {
      return {
        title: 'Ano Letivo Anterior',
        message: `Não é possível gerar mensalidades para anos letivos anteriores (${generateForm.academic_year}). Apenas o ano atual ou futuros podem receber mensalidades.`
      }
    }
    if (validation.reason === 'completed') {
      return {
        title: 'Matrícula Completada',
        message: `A matrícula de ${generateForm.academic_year} foi completada. Não é possível gerar novas mensalidades para este ano letivo.`
      }
    }
  }
  return null
})

// Computed para aviso de validação (adicionar)
const addValidationWarning = computed(() => {
  if (!addForm.academic_year) return null
  
  const validation = validateAcademicYear(addForm.academic_year)
  if (!validation.valid) {
    if (validation.reason === 'past_year') {
      return {
        title: 'Ano Letivo Anterior',
        message: `Não é possível adicionar mensalidades para anos letivos anteriores (${addForm.academic_year}). Apenas o ano atual ou futuros podem receber mensalidades.`
      }
    }
    if (validation.reason === 'completed') {
      return {
        title: 'Matrícula Completada',
        message: `A matrícula de ${addForm.academic_year} foi completada. Não é possível adicionar novas mensalidades para este ano letivo.`
      }
    }
  }
  return null
})

// Computed para verificar se o erro é relacionado a pagamentos confirmados
const isConfirmedPaymentsError = computed(() => {
  if (!error.value) return false
  const errorMsg = error.value.toLowerCase()
  return errorMsg.includes('pagamentos confirmados') || 
         errorMsg.includes('pagamento confirmado') ||
         errorMsg.includes('reverta os pagamentos') ||
         errorMsg.includes('reverter os pagamentos')
})

onMounted(() => {
  checkExistingMonthlyFees()
  checkExistingForMonth()
})

watch(() => generateForm.academic_year, () => {
  checkExistingMonthlyFees()
  const validation = validateAcademicYear(generateForm.academic_year)
  if (!validation.valid) {
    error.value = validation.message
  } else {
    error.value = null
  }
})

watch([() => generateForm.start_month, () => generateForm.end_month], () => {
  checkExistingInRange()
})

// Calcula quantidade de mensalidades
const totalInstallments = computed(() => {
  if (generateForm.end_month < generateForm.start_month) return 0
  return generateForm.end_month - generateForm.start_month + 1
})

const setFullYear = () => {
  generateForm.start_month = 1
  generateForm.end_month = 12
}

const validateMonthRange = () => {
  if (generateForm.end_month < generateForm.start_month) {
    error.value = 'O mês de término deve ser posterior ao mês de início'
  } else {
    error.value = null
  }
}

const getPeriodDescription = () => {
  if (generateForm.end_month < generateForm.start_month) {
    return 'Período inválido'
  }
  
  const startMonth = months[generateForm.start_month - 1]
  const endMonth = months[generateForm.end_month - 1]
  
  if (generateForm.start_month === 1 && generateForm.end_month === 12) {
    return 'Ano letivo completo'
  }
  
  return `De ${startMonth.label} a ${endMonth.label} de ${generateForm.academic_year}`
}

// Handler para Gerar Mensalidades
const handleGenerateSubmit = () => {
  if (generateForm.end_month < generateForm.start_month) {
    error.value = 'O mês de término deve ser posterior ao mês de início'
    return
  }

  const validation = validateAcademicYear(generateForm.academic_year)
  if (!validation.valid) {
    error.value = validation.message
    return
  }
  
  const enrollmentForYear = validation.enrollment
  if (!enrollmentForYear?.classroom_id) {
    error.value = 'O aluno deve estar vinculado a uma turma para este ano letivo para gerar mensalidades'
    return
  }
  
  // Mostrar modal de confirmação
  showConfirmModal.value = true
}

// Confirmar e gerar mensalidades
const confirmGenerate = () => {
  showConfirmModal.value = false
  generateForm.enrollment_id = getEnrollmentForYear(generateForm.academic_year).id

  submitting.value = true
  error.value = null

  // Preparar payload: substitui mensalidades no intervalo selecionado
  const payload = {
    enrollment_id: generateForm.enrollment_id,
    academic_year: generateForm.academic_year,
    start_month: generateForm.start_month,
    end_month: generateForm.end_month,
    due_day: generateForm.due_day,
    total_installments: totalInstallments.value,
    has_punctuality_discount: generateForm.has_punctuality_discount,
    punctuality_discount_amount: generateForm.punctuality_discount_amount,
    notes: generateForm.notes,
    replace_existing: hasExistingInRange.value // Substituir apenas se houver mensalidades no intervalo
  }

  router.post('/api/monthly-fees', payload, {
    preserveScroll: true,
    only: [],
    onSuccess: (page) => {
      const successMessage = page?.props?.success || page?.props?.flash?.success
      
      if (successMessage) {
        const startMonth = months[generateForm.start_month - 1].label
        const endMonth = months[generateForm.end_month - 1].label
        alert(`✅ Mensalidades geradas com sucesso!\n\n${totalInstallments.value} mensalidade${totalInstallments.value !== 1 ? 's' : ''} ${totalInstallments.value !== 1 ? 'foram criadas' : 'foi criada'} para ${props.student.name}.\n\nPeríodo: ${startMonth} a ${endMonth} de ${generateForm.academic_year}`)
      }
      
      submitting.value = false
      emit('success')
    },
    onError: (errors) => {
      console.error('Erro ao gerar mensalidades:', errors)
      
      let errorMessage = ''
      
      if (typeof errors === 'string') {
        errorMessage = errors
      } else if (errors?.message) {
        errorMessage = errors.message
      } else if (errors?.error) {
        errorMessage = errors.error
      } else if (errors?.errors && typeof errors.errors === 'object') {
        const firstError = Object.values(errors.errors)[0]
        errorMessage = Array.isArray(firstError) ? firstError[0] : firstError
      } else if (Array.isArray(errors) && errors.length > 0) {
        errorMessage = errors[0]
      } else {
        errorMessage = 'Erro ao gerar mensalidades. Verifique se você está autenticado e tente novamente.'
      }
      
      // Verificar se o erro é relacionado a pagamentos confirmados
      const hasConfirmedPaymentsError = errorMessage.includes('pagamentos confirmados') || 
                                       errorMessage.includes('pagamento confirmado') ||
                                       errorMessage.includes('reverta os pagamentos')
      
      if (hasConfirmedPaymentsError) {
        // Exibir alerta destacado para erros de pagamentos confirmados
        alert('⚠️ ATENÇÃO: Pagamentos Confirmados Encontrados\n\n' + 
              errorMessage + '\n\n' +
              'Por favor, acesse a página de mensalidades do aluno e reverta os pagamentos confirmados antes de tentar substituir as mensalidades.')
      }
      
      error.value = errorMessage
      submitting.value = false
    },
    onFinish: () => {
      submitting.value = false
    }
  })
}

// Handler para Adicionar Mensalidade
const handleAddSubmit = () => {
  if (hasExistingForMonth.value) {
    error.value = 'Já existe uma mensalidade para este mês. Escolha outro mês ou ano letivo.'
    return
  }

  const validation = validateAcademicYear(addForm.academic_year)
  if (!validation.valid) {
    error.value = validation.message
    return
  }
  
  const enrollmentForYear = validation.enrollment
  if (!enrollmentForYear?.classroom_id) {
    error.value = 'O aluno deve estar vinculado a uma turma para este ano letivo'
    return
  }
  
  addForm.enrollment_id = enrollmentForYear.id

  submitting.value = true
  error.value = null

  // Preparar payload para adicionar uma mensalidade individual
  // Usa o mesmo endpoint mas com start_month e end_month iguais
  const payload = {
    enrollment_id: addForm.enrollment_id,
    academic_year: addForm.academic_year,
    start_month: addForm.month,
    end_month: addForm.month,
    due_day: addForm.due_day,
    total_installments: 1,
    has_punctuality_discount: false,
    punctuality_discount_amount: 0,
    add_to_existing: true, // Sempre adiciona ao existente
    notes: addForm.notes
  }

  router.post('/api/monthly-fees', payload, {
    preserveScroll: true,
    only: [],
    onSuccess: (page) => {
      const successMessage = page?.props?.success || page?.props?.flash?.success
      
      if (successMessage) {
        const monthLabel = months[addForm.month - 1].label
        alert(`✅ Mensalidade adicionada com sucesso!\n\nMensalidade de ${monthLabel} de ${addForm.academic_year} foi criada para ${props.student.name}.`)
      }
      
      submitting.value = false
      emit('success')
    },
    onError: (errors) => {
      console.error('Erro ao adicionar mensalidade:', errors)
      
      if (typeof errors === 'string') {
        error.value = errors
      } else if (errors?.message) {
        error.value = errors.message
      } else if (errors?.error) {
        error.value = errors.error
      } else if (errors?.errors && typeof errors.errors === 'object') {
        const firstError = Object.values(errors.errors)[0]
        error.value = Array.isArray(firstError) ? firstError[0] : firstError
      } else if (Array.isArray(errors) && errors.length > 0) {
        error.value = errors[0]
      } else {
        error.value = 'Erro ao adicionar mensalidade. Verifique se você está autenticado e tente novamente.'
      }
      
      submitting.value = false
    },
    onFinish: () => {
      submitting.value = false
    }
  })
}

const close = () => {
  if (!submitting.value) {
    emit('close')
  }
}
</script>
