<template>
  <TransitionRoot :show="open" as="template" @after-leave="query = ''" appear>
    <Dialog class="relative z-50" @close="close">
      <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 bg-gray-500/25 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 w-screen overflow-y-auto p-4 sm:p-6 md:p-20">
        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="ease-in duration-200" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
          <DialogPanel class="mx-auto max-w-xl transform rounded-xl bg-white p-2 shadow-2xl outline-1 outline-black/5 transition-all">
            <div class="px-4 py-2 border-b border-gray-200">
              <h3 class="text-lg font-semibold text-gray-900">
                Buscar {{ type === 'receita' ? 'Pagante' : 'Fornecedor' }}
              </h3>
            </div>
            
            <Combobox @update:modelValue="onSelect">
              <ComboboxInput 
                class="w-full rounded-md bg-gray-100 px-4 py-2.5 text-gray-900 outline-hidden placeholder:text-gray-500 sm:text-sm mt-2" 
                placeholder="Digite o nome ou documento..." 
                @change="query = $event.target.value" 
                @blur="query = ''" 
                autofocus
              />

              <ComboboxOptions v-if="filteredSuppliers.length > 0" static class="-mb-2 max-h-72 scroll-py-2 overflow-y-auto py-2 text-sm text-gray-800">
                <ComboboxOption v-for="supplier in filteredSuppliers" :key="supplier.id" :value="supplier" as="template" v-slot="{ active }">
                  <li :class="['cursor-default rounded-md px-4 py-2 select-none', active && 'bg-indigo-600 text-white outline-hidden']">
                    <div class="flex items-center justify-between">
                      <div>
                        <div class="font-medium">{{ supplier.name }}</div>
                        <div v-if="supplier.document" :class="['text-xs mt-1', active ? 'text-indigo-100' : 'text-gray-500']">
                          {{ supplier.document }}
                        </div>
                      </div>
                      <span :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        active 
                          ? 'bg-indigo-100 text-indigo-800' 
                          : supplier.is_pagante && supplier.is_fornecedor
                            ? 'bg-purple-100 text-purple-800'
                            : supplier.is_pagante 
                              ? 'bg-green-100 text-green-800' 
                              : 'bg-blue-100 text-blue-800'
                      ]">
                        {{ supplier.is_pagante && supplier.is_fornecedor ? 'Pagante e Fornecedor' : supplier.is_pagante ? 'Pagante' : 'Fornecedor' }}
                      </span>
                    </div>
                  </li>
                </ComboboxOption>
              </ComboboxOptions>

              <div v-if="query !== '' && filteredSuppliers.length === 0" class="px-4 py-14 text-center sm:px-14">
                <UsersIcon class="mx-auto size-6 text-gray-400" aria-hidden="true" />
                <p class="mt-4 text-sm text-gray-900">Nenhum {{ type === 'receita' ? 'pagante' : 'fornecedor' }} encontrado.</p>
                <button 
                  @click="openCreate"
                  class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                  </svg>
                  Criar Novo {{ type === 'receita' ? 'Pagante' : 'Fornecedor' }}
                </button>
              </div>
            </Combobox>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { UsersIcon } from '@heroicons/vue/24/outline'
import {
  Combobox,
  ComboboxInput,
  ComboboxOptions,
  ComboboxOption,
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  open: Boolean,
  suppliers: Array,
  type: String, // 'receita' ou 'despesa'
})

const emit = defineEmits(['close', 'select', 'openCreate'])

const query = ref('')

const filteredSuppliers = computed(() => {
  if (query.value === '') {
    return props.suppliers.slice(0, 10) // Mostra os primeiros 10 quando não há busca
  }
  
  const searchTerm = query.value.toLowerCase()
  return props.suppliers.filter((supplier) => {
    return (
      supplier.name.toLowerCase().includes(searchTerm) ||
      (supplier.document && supplier.document.toLowerCase().includes(searchTerm)) ||
      (supplier.email && supplier.email.toLowerCase().includes(searchTerm))
    )
  })
})

function onSelect(supplier) {
  if (supplier) {
    emit('select', supplier)
    close()
  }
}

function close() {
  emit('close')
}

function openCreate() {
  emit('openCreate')
  close()
}

watch(() => props.open, (newValue) => {
  if (newValue) {
    query.value = ''
  }
})
</script>

