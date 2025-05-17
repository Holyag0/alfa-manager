<template>
    <Head title="Atribuir-Cargo" />
  <form>
    <div class="space-y-12 ">
      <div class="border-b border-gray-900/10 pb-12 ">
        <h2 class="text-base/7 font-semibold text-sky-700">Atribuir Cargos</h2>
        <p class="mt-1 text-sm/6 text-gray-700">Escolha usuário e o cargo que desempenha na alfa baby.</p>
        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 bg-white p-4 rounded-lg">
          <div class="sm:col-span-3">
            <label for="country" class="block text-sm/6 font-medium text-gray-900">Usuário Selecionado</label>
            <div class="mt-2 grid grid-cols-1">
              <select v-model="form.user_id" disabled 
              class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-1.5 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 
              focus:outline-2 focus:-outline-offset-2 focus:outline-green-600 sm:text-sm/6">
                <option :value="user.id">{{ user.name }}</option>
              </select>
              <div v-if="form.errors.user_id" class="text-red-500 text-sm mt-1">{{ form.errors.user_id }}</div>
            </div>
          </div>
          <div class="sm:col-span-3">
            <label for="country" class="block text-sm/6 font-medium text-gray-900">Escolha um cargo</label>
            <div class="mt-2 grid grid-cols-1">
              <select v-model="form.role" 
              class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-1.5 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 
              focus:outline-2 focus:-outline-offset-2 focus:outline-green-600 sm:text-sm/6">
                <option disabled value="">Selecione um cargo</option>
                <option v-for="(name, id) in roles" :key="id" :value="name">{{ name }}</option>
              </select>
              <div v-if="form.errors.role" class="text-red-500 text-sm mt-1">{{ form.errors.role }}</div>
            </div>
          </div>
        </div>
      </div>
      <div class="border-b border-gray-900/10 pb-12">
        <h2 class="text-base/7 font-semibold text-gray-900">Notificação !</h2>
          <p class="mt-1 text-sm/6 text-red-600">
            Cuidado ao atribuir cargos, se atribuidos erroniamente usuário pode ter acesso a modulos indesejados .
          </p>
        <div class="mt-10 space-y-10">
        </div>
      </div>
    </div>

    <div class="mt-6 flex items-center justify-end gap-x-6">
      <Link :href="route('user.index')" type="button" class="text-sm/6 font-semibold text-gray-900">Cancel</Link>
      <button type="button" @click="submitForm" :disabled="form.processing" 
              class="rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-teal-500 
              focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
              <span v-if="form.processing">Salvando...</span>
              <span v-else>Salvar</span>
      </button>
    </div>
  </form>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  user: Object,
  roles: Object,
})

const form = useForm({
  user_id: props.user.id || '',  // já selecionado
  role: ''
})
function submitForm() {
  form.post(route('atribuindo-cargo.store'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  })
}
</script>