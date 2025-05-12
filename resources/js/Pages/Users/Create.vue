<template>
  <button @click="open = true" type="button"
    class="inline-flex items-center rounded-md bg-green-500 px-3 py-2 text-sm font-semibold text-white shadow-sm 
    hover:bg-teal-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600
    hover:translate-y-1 hover:scale-110  transition ease-in-out delay-150">
    <PlusIcon class="-ml-0.5 mr-1.5 size-5" aria-hidden="true" />
    Cadastro
  </button>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-10" @close="open = false">
      <div class="fixed inset-0" />
  
      <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
          <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
            <TransitionChild as="template" enter="transform transition ease-in-out duration-500 sm:duration-700"
              enter-from="translate-x-full" enter-to="translate-x-0"
              leave="transform transition ease-in-out duration-500 sm:duration-700" leave-from="translate-x-0"
              leave-to="translate-x-full">
              <DialogPanel class="pointer-events-auto w-screen max-w-md">
                <form @submit.prevent='submitUsuario'
                  class="flex h-full flex-col divide-y divide-gray-200 bg-white shadow-xl">
                  <div class="h-0 flex-1 overflow-y-auto">
                    <div class="bg-gradient-to-b from-emerald-500  rounded-sm  to-sky-500 px-4 py-6 sm:px-6">
                      <div class="flex items-center justify-between">
                        <DialogTitle class="text-base font-semibold text-white">Novo Usuário</DialogTitle>
                        <div class="ml-3 flex h-7 items-center">
                          <button type="button"
                            class="relative rounded-md text-indigo-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-white"
                            @click="open = false">
                            <span class="absolute -inset-2.5" />
                            <span class="sr-only">Close panel</span>
                            <XMarkIcon class="size-6" aria-hidden="true" />
                          </button>
                        </div>
                      </div>
                      <div class="mt-1">
                        <p class="text-sm text-gray-300">
                          Preencha os campos abaixo para cadastrar um novo usuário do sistema.
                        </p>
                      </div>
                    </div>
                    <div class="flex flex-1 flex-col justify-between">
                      <div class="divide-y divide-gray-200 px-4 sm:px-6">
                        <div class="space-y-6 pb-5 pt-6">
                          <div>
                            <InputLabel for="name" value="Nome" />
                            <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" required
                              autofocus autocomplete="name" />
                            <InputError class="mt-2" :message="form.errors.name" />
                          </div>
  
                          <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required
                              autocomplete="username" />
                            <InputError class="mt-2" :message="form.errors.email" />
                          </div>
  
                          <div>
                            <InputLabel for="password" value="Senha" />
                            <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full"
                              required autocomplete="new-password" />
                            <InputError class="mt-2" :message="form.errors.password" />
                          </div>
  
                          <div>
                            <InputLabel for="password_confirmation" value="Confirmar Senha" />
                            <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password"
                              class="mt-1 block w-full" required autocomplete="new-password" />
                            <InputError class="mt-2" :message="form.errors.password_confirmation" />
                          </div>
  
                          <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature">
                            <InputLabel for="terms">
                              <div class="flex items-center">
                                <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />
                                <div class="ml-2 text-sm text-gray-600">
                                  Eu concordo com os
                                  <a target="_blank" :href="route('terms.show')"
                                    class="underline hover:text-gray-900">
                                    Termos de Serviço</a>
                                  e a
                                  <a target="_blank" :href="route('policy.show')"
                                    class="underline hover:text-gray-900">
                                    Política de Privacidade 
                                  </a>
                                  da Escola Alfa Baby
                                </div>
                              </div>
                            </InputLabel>
                            <InputError class="mt-2" :message="form.errors.terms" />
                          </div>
  
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="flex shrink-0 justify-end px-4 py-4">
                    <button type="button"
                      class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                      @click="open = false">Cancel</button>
                    <button type="submit"
                      class="ml-4 inline-flex justify-center rounded-md bg-green-500  px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-teal-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
                  </div>
                </form>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
  </template>
  
  <script setup>
  import { useForm } from '@inertiajs/vue3'
  import { ref } from 'vue'
  import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
  import { XMarkIcon } from '@heroicons/vue/24/outline'
  import { PlusIcon } from '@heroicons/vue/20/solid'
  import InputLabel from '@/Components/InputLabel.vue';
  import TextInput from '@/Components/TextInput.vue';
  import InputError from '@/Components/InputError.vue';
  import Checkbox from '@/Components/Checkbox.vue';
  const open = ref(false)
  
  const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
    errors: Object,
  });
  
  const submitUsuario = () => {
    // if (!confirmPassword()) return
  
    form.post(route('user.store'))
  }
  
  const confirmPassword = () => {
    if (form.password !== form.password_confirmation) {
      alert('As senhas não conferem')
      return false
    }
    return true
  }
  </script>