<template>
  <form @submit.prevent="submit" class="p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Nome -->
      <div class="md:col-span-2">
        <InputLabel for="name" value="Nome do Serviço" />
        <TextInput
          id="name"
          v-model="form.name"
          type="text"
          class="mt-1 block w-full"
          required
          autofocus
        />
        <InputError :message="form.errors.name" class="mt-2" />
      </div>

      <!-- Categoria -->
      <div>
        <InputLabel for="category" value="Categoria" />
        <select
          id="category"
          v-model="form.category"
          class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
          required
        >
          <option value="">Selecione uma categoria</option>
          <option v-for="category in categories" :key="category.id" :value="category.name">
            {{ category.name }}
          </option>
        </select>
        <InputError :message="form.errors.category" class="mt-2" />
      </div>

      <!-- Preço -->
      <div>
        <InputLabel for="price" value="Preço" />
        <TextInput
          id="price"
          v-model="form.price"
          type="number"
          step="0.01"
          min="0"
          class="mt-1 block w-full"
          required
        />
        <InputError :message="form.errors.price" class="mt-2" />
      </div>

      <!-- Status -->
      <div>
        <InputLabel for="status" value="Status" />
        <select
          id="status"
          v-model="form.status"
          class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
          required
        >
          <option value="">Selecione o status</option>
          <option value="active">Ativo</option>
          <option value="inactive">Inativo</option>
        </select>
        <InputError :message="form.errors.status" class="mt-2" />
      </div>

      <!-- Descrição -->
      <div class="md:col-span-2">
        <InputLabel for="description" value="Descrição (Opcional)" />
        <textarea
          id="description"
          v-model="form.description"
          rows="4"
          class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
          placeholder="Descreva o serviço..."
        />
        <InputError :message="form.errors.description" class="mt-2" />
      </div>

      <!-- Vinculação com Turmas -->
      <div class="md:col-span-2">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <h3 class="text-lg font-medium text-blue-900 mb-3">Vinculação com Turmas</h3>
          
          <div class="space-y-4">
            <!-- Opção de Vinculação -->
            <div class="flex items-center">
              <input
                id="is_classroom_linked"
                v-model="form.is_classroom_linked"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label for="is_classroom_linked" class="ml-2 block text-sm text-blue-900">
                Este serviço deve ter preços diferentes por turma
              </label>
            </div>

            <!-- Explicação -->
            <div class="text-sm text-blue-700">
              <p v-if="form.is_classroom_linked">
                <strong>Vinculado a Turmas:</strong> Selecione as turmas que terão preços específicos para este serviço.
              </p>
              <p v-else>
                <strong>Serviço Global:</strong> Este serviço terá o mesmo preço para todas as turmas.
              </p>
            </div>

            <!-- Seleção de Turmas (aparece quando vinculado) -->
            <div v-if="form.is_classroom_linked" class="space-y-4">
              <div class="border-t border-blue-200 pt-4">
                <h4 class="font-medium text-blue-800 mb-3">Selecionar Turmas:</h4>
                
                <!-- Seleção Rápida -->
                <div class="flex gap-3 mb-4">
                  <button 
                    type="button" 
                    @click="selectAllClassrooms" 
                    class="text-sm px-3 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors"
                  >
                    Selecionar Todas
                  </button>
                  <button 
                    type="button" 
                    @click="deselectAllClassrooms" 
                    class="text-sm px-3 py-1 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors"
                  >
                    Desmarcar Todas
                  </button>
                </div>
                
                <!-- Lista de Turmas -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-48 overflow-y-auto border border-gray-200 rounded-md p-3 bg-white">
                  <div v-for="classroom in classrooms" :key="classroom.id" class="flex items-center">
                    <input
                      v-model="form.selected_classrooms"
                      :value="classroom.id"
                      type="checkbox"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label class="ml-2 text-sm text-gray-700 cursor-pointer">
                      {{ classroom.name }}
                    </label>
                  </div>
                </div>
                
                <!-- Contador -->
                <div class="text-sm text-blue-600 mt-2">
                  {{ form.selected_classrooms.length }} turma(s) selecionada(s)
                </div>
                
                <!-- Erro de validação -->
                <InputError :message="form.errors.selected_classrooms" class="mt-2" />
              </div>
            </div>

            <!-- Notas de Vinculação -->
            <div v-if="form.is_classroom_linked">
              <InputLabel for="classroom_linking_notes" value="Notas sobre Vinculação (Opcional)" />
              <textarea
                id="classroom_linking_notes"
                v-model="form.classroom_linking_notes"
                rows="2"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                placeholder="Ex: Preços diferenciados por série, turmas especiais..."
              />
              <InputError :message="form.errors.classroom_linking_notes" class="mt-2" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Botões -->
    <div class="flex items-center justify-end mt-6">
      <Link
        :href="cancelUrl"
        class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
      >
        Cancelar
      </Link>
      <PrimaryButton
        class="ml-4"
        :class="{ 'opacity-25': form.processing }"
        :disabled="form.processing"
      >
        {{ submitButtonText }}
      </PrimaryButton>
    </div>
  </form>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

const props = defineProps({
  service: {
    type: Object,
    default: () => ({})
  },
  categories: {
    type: Array,
    default: () => []
  },
  classrooms: {
    type: Array,
    default: () => []
  },
  submitUrl: {
    type: String,
    required: true
  },
  submitMethod: {
    type: String,
    default: 'post'
  },
  submitButtonText: {
    type: String,
    default: 'Salvar'
  },
  cancelUrl: {
    type: String,
    default: '/comercial/services'
  }
})

const form = useForm({
  name: props.service.name || '',
  category: props.service.category || '',
  price: props.service.price || '',
  status: props.service.status || '',
  description: props.service.description || '',
  is_classroom_linked: props.service.is_classroom_linked || false,
  selected_classrooms: props.service.selected_classrooms || [],
  classroom_linking_notes: props.service.classroom_linking_notes || ''
})

// Métodos para seleção de turmas
const selectAllClassrooms = () => {
  form.selected_classrooms = props.classrooms.map(classroom => classroom.id)
}

const deselectAllClassrooms = () => {
  form.selected_classrooms = []
}

const submit = () => {
  if (props.submitMethod === 'put') {
    form.put(props.submitUrl)
  } else {
    form.post(props.submitUrl)
  }
}
</script> 