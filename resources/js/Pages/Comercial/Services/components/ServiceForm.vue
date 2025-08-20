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
  description: props.service.description || ''
})

const submit = () => {
  if (props.submitMethod === 'put') {
    form.put(props.submitUrl)
  } else {
    form.post(props.submitUrl)
  }
}
</script> 