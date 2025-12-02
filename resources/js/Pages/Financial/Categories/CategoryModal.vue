<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="close">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-md shadow-lg rounded-md bg-white" @click.stop>
      <div class="flex items-center justify-between pb-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ isEdit ? 'Editar Categoria' : 'Nova Categoria' }}
        </h3>
        <button @click="close" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <form @submit.prevent="submit" class="mt-6 space-y-4">
        <!-- Nome -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Nome <span class="text-red-500">*</span>
          </label>
          <input v-model="form.name"
                 type="text"
                 required
                 class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                 :class="{ 'border-red-300': form.errors.name }">
          <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
        </div>

        <!-- Tipo -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Tipo <span class="text-red-500">*</span>
          </label>
          <div class="grid grid-cols-2 gap-3">
            <button type="button"
                    @click="form.type = 'receita'"
                    :class="[
                      'px-4 py-2 border-2 rounded-lg font-medium transition-all',
                      form.type === 'receita'
                        ? 'border-green-500 bg-green-50 text-green-700'
                        : 'border-gray-300 bg-white text-gray-700 hover:border-gray-400'
                    ]">
              <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Receita
            </button>
            <button type="button"
                    @click="form.type = 'despesa'"
                    :class="[
                      'px-4 py-2 border-2 rounded-lg font-medium transition-all',
                      form.type === 'despesa'
                        ? 'border-red-500 bg-red-50 text-red-700'
                        : 'border-gray-300 bg-white text-gray-700 hover:border-gray-400'
                    ]">
              <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
              </svg>
              Despesa
            </button>
          </div>
          <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
        </div>

        <!-- Cor -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Cor <span class="text-red-500">*</span>
          </label>
          <div class="flex items-center space-x-3">
            <input v-model="form.color"
                   type="color"
                   class="h-10 w-20 rounded border border-gray-300 cursor-pointer">
            <input v-model="form.color"
                   type="text"
                   pattern="^#[0-9A-Fa-f]{6}$"
                   placeholder="#10B981"
                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
          </div>
          <p v-if="form.errors.color" class="mt-1 text-sm text-red-600">{{ form.errors.color }}</p>
        </div>

        <!-- Descrição -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Descrição
          </label>
          <textarea v-model="form.description"
                    rows="3"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Descrição opcional da categoria"></textarea>
        </div>

        <!-- Ativa -->
        <div class="flex items-center">
          <input v-model="form.is_active"
                 type="checkbox"
                 id="is_active"
                 class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
          <label for="is_active" class="ml-2 block text-sm text-gray-900">
            Categoria ativa
          </label>
        </div>

        <!-- Botões -->
        <div class="flex justify-end space-x-3 pt-4 border-t">
          <button type="button"
                  @click="close"
                  class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
            Cancelar
          </button>
          <button type="submit"
                  :disabled="form.processing"
                  class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50">
            {{ form.processing ? 'Salvando...' : 'Salvar' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  show: Boolean,
  category: Object
})

const emit = defineEmits(['close', 'saved'])

const isEdit = ref(false)

const form = useForm({
  name: '',
  type: 'despesa',
  color: '#6B7280',
  description: '',
  is_active: true
})

watch(() => props.show, (newValue) => {
  if (newValue) {
    if (props.category) {
      isEdit.value = true
      form.name = props.category.name
      form.type = props.category.type
      form.color = props.category.color
      form.description = props.category.description || ''
      form.is_active = props.category.is_active
      form.clearErrors()
    } else {
      isEdit.value = false
      form.name = ''
      form.type = 'despesa'
      form.color = '#6B7280'
      form.description = ''
      form.is_active = true
      form.clearErrors()
    }
  }
})

const close = () => {
  emit('close')
}

const submit = () => {
  const method = isEdit.value ? 'put' : 'post'
  const url = isEdit.value 
    ? route('financial.categories.update', props.category.id)
    : route('financial.categories.store')

  form[method](url, {
    preserveScroll: true,
    onSuccess: () => {
      emit('saved')
      alert(isEdit.value ? 'Categoria atualizada com sucesso!' : 'Categoria criada com sucesso!')
    }
  })
}
</script>

