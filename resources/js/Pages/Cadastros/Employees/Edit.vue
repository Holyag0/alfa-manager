<template>
  <div class="min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <!-- Header -->
      <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Editar Colaborador</h1>
            <p class="text-sm text-gray-600 mt-1">Atualize as informações do colaborador</p>
          </div>
          <Link
            :href="route('cadastros.employees.index')"
            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
          >
            Voltar
          </Link>
        </div>
      </div>

      <!-- Formulário -->
      <form @submit.prevent="submit" class="bg-white shadow rounded-lg p-6">
        <div class="space-y-6">
          <!-- Foto -->
          <PhotoUpload v-model="form.photo" :current-photo="employee.photo_url" />

          <!-- Dados Pessoais -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Dados Pessoais</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Nome Completo <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  :class="{ 'border-red-300': form.errors.name }"
                />
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">CPF</label>
                <input
                  v-model="form.cpf"
                  type="text"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  :class="{ 'border-red-300': form.errors.cpf }"
                />
                <p v-if="form.errors.cpf" class="mt-1 text-sm text-red-600">{{ form.errors.cpf }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">RG</label>
                <input
                  v-model="form.rg"
                  type="text"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Data de Nascimento</label>
                <input
                  v-model="form.birth_date"
                  type="date"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
              </div>
            </div>
          </div>

          <!-- Contato -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Contato</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input
                  v-model="form.email"
                  type="email"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  :class="{ 'border-red-300': form.errors.email }"
                />
                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                <input
                  v-model="form.phone"
                  type="text"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Endereço</label>
                <textarea
                  v-model="form.address"
                  rows="2"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Profissional -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Dados Profissionais</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cargo</label>
                <select
                  v-model="form.position_id"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="">Selecione um cargo</option>
                  <option v-for="position in positions" :key="position.id" :value="position.id">
                    {{ position.name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Data de Contratação</label>
                <input
                  v-model="form.hire_date"
                  type="date"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Salário</label>
                <input
                  v-model="form.salary"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <div class="flex items-center">
                  <input
                    v-model="form.is_active"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  />
                  <label class="ml-2 block text-sm text-gray-900">Ativo</label>
                </div>
              </div>
            </div>
          </div>

          <!-- Observações -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            ></textarea>
          </div>

          <!-- Botões -->
          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
            <Link
              :href="route('cadastros.employees.index')"
              class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400"
            >
              Cancelar
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 disabled:opacity-50"
            >
              {{ form.processing ? 'Atualizando...' : 'Atualizar' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import PhotoUpload from './components/PhotoUpload.vue'

const props = defineProps({
  employee: Object,
  positions: Array,
  errors: Object
})

const form = useForm({
  name: props.employee?.name || '',
  email: props.employee?.email || '',
  phone: props.employee?.phone || '',
  cpf: props.employee?.cpf || '',
  rg: props.employee?.rg || '',
  birth_date: props.employee?.birth_date || '',
  photo: null,
  position_id: props.employee?.position_id || '',
  hire_date: props.employee?.hire_date || '',
  salary: props.employee?.salary || '',
  address: props.employee?.address || '',
  notes: props.employee?.notes || '',
  is_active: props.employee?.is_active ?? true
})

const submit = () => {
  // Usamos POST com spoofing de método para evitar perda de campos ao enviar FormData com PUT
  form
    .transform((data) => ({
      ...data,
      _method: 'put'
    }))
    .post(route('cadastros.employees.update', props.employee.id), {
      forceFormData: true
    })
}
</script>

