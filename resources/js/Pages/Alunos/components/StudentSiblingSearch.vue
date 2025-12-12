<template>
  <TransitionRoot :show="open" as="template" @after-leave="query = ''" appear>
    <Dialog class="relative z-10" @close="closeDialog">
      <TransitionChild 
        as="template" 
        enter="ease-out duration-300" 
        enter-from="opacity-0" 
        enter-to="opacity-100" 
        leave="ease-in duration-200" 
        leave-from="opacity-100" 
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500/25 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 w-screen overflow-y-auto p-4 sm:p-6 md:p-20">
        <TransitionChild 
          as="template" 
          enter="ease-out duration-300" 
          enter-from="opacity-0 scale-95" 
          enter-to="opacity-100 scale-100" 
          leave="ease-in duration-200" 
          leave-from="opacity-100 scale-100" 
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel class="mx-auto max-w-3xl transform divide-y divide-gray-100 overflow-hidden rounded-xl bg-white shadow-2xl outline-1 outline-black/5 transition-all">
            <Combobox v-slot="{ activeOption }" @update:modelValue="onSelect">
              <div class="grid grid-cols-1">
                <ComboboxInput 
                  class="col-start-1 row-start-1 h-12 w-full pr-4 pl-11 text-base text-gray-900 outline-hidden placeholder:text-gray-400 sm:text-sm" 
                  placeholder="Digite o nome ou CPF do aluno irmão..." 
                  :modelValue="query"
                  @update:modelValue="query = $event"
                  @input="handleSearch"
                />
                <MagnifyingGlassIcon 
                  class="pointer-events-none col-start-1 row-start-1 ml-4 size-5 self-center text-gray-400" 
                  aria-hidden="true" 
                />
                <div v-if="loading" class="col-start-1 row-start-1 ml-auto mr-4 self-center">
                  <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
              </div>

              <ComboboxOptions 
                v-if="filteredStudents.length > 0" 
                class="flex transform-gpu divide-x divide-gray-100" 
                as="div" 
                static 
                hold
              >
                <div 
                  :class="[
                    'max-h-96 min-w-0 flex-auto scroll-py-4 overflow-y-auto px-6 py-4', 
                    activeOption && 'sm:h-96'
                  ]"
                >
                  <h2 v-if="query === ''" class="mt-2 mb-4 text-xs font-semibold text-gray-500">
                    Resultados da consulta
                  </h2>
                  <h2 v-else class="mt-2 mb-4 text-xs font-semibold text-gray-500">
                    Resultados para "{{ query }}"
                  </h2>
                  <div hold class="-mx-2 text-sm text-gray-700">
                    <ComboboxOption 
                      v-for="student in filteredStudents" 
                      :key="student.id" 
                      :value="student" 
                      as="template" 
                      v-slot="{ active }"
                    >
                      <div 
                        :class="[
                          'group flex cursor-default items-center rounded-md p-2 select-none', 
                          active && 'bg-gray-100 text-gray-900 outline-hidden'
                        ]"
                      >
                        <div 
                          v-if="student.photo" 
                          class="size-10 flex-none rounded-full bg-gray-100 outline -outline-offset-1 outline-black/5 overflow-hidden"
                        >
                          <img 
                            :src="`/storage/${student.photo}`" 
                            :alt="student.name"
                            class="w-full h-full object-cover"
                          />
                        </div>
                        <div 
                          v-else
                          class="size-10 flex-none rounded-full bg-gray-100 outline -outline-offset-1 outline-black/5 flex items-center justify-center"
                        >
                          <UserIcon class="size-6 text-gray-400" />
                        </div>
                        <div class="ml-3 flex-auto min-w-0">
                          <div class="truncate font-medium">{{ student.name }}</div>
                          <div v-if="student.cpf" class="truncate text-xs text-gray-500">
                            CPF: {{ formatCPF(student.cpf) }}
                          </div>
                        </div>
                        <ChevronRightIcon 
                          v-if="active" 
                          class="ml-3 size-5 flex-none text-gray-400" 
                          aria-hidden="true" 
                        />
                      </div>
                    </ComboboxOption>
                  </div>
                </div>

                <div 
                  v-if="activeOption" 
                  class="hidden h-96 w-1/2 flex-none flex-col divide-y divide-gray-100 overflow-y-auto sm:flex"
                >
                  <div class="flex-none p-6 text-center">
                    <div 
                      v-if="activeOption.photo" 
                      class="mx-auto size-16 rounded-full bg-gray-100 outline -outline-offset-1 outline-black/5 overflow-hidden"
                    >
                      <img 
                        :src="`/storage/${activeOption.photo}`" 
                        :alt="activeOption.name"
                        class="w-full h-full object-cover"
                      />
                    </div>
                    <div 
                      v-else
                      class="mx-auto size-16 rounded-full bg-gray-100 outline -outline-offset-1 outline-black/5 flex items-center justify-center"
                    >
                      <UserIcon class="size-8 text-gray-400" />
                    </div>
                    <h2 class="mt-3 font-semibold text-gray-900">
                      {{ activeOption.name }}
                    </h2>
                    <p v-if="activeOption.cpf" class="text-sm/6 text-gray-500">
                      CPF: {{ formatCPF(activeOption.cpf) }}
                    </p>
                  </div>
                  <div class="flex flex-auto flex-col justify-between p-6">
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm text-gray-700">
                      <template v-if="activeOption.birth_date">
                        <dt class="col-end-1 font-semibold text-gray-900">Data de Nascimento</dt>
                        <dd>{{ formatDate(activeOption.birth_date) }}</dd>
                      </template>
                      <template v-if="activeOption.main_guardian_name">
                        <dt class="col-end-1 font-semibold text-gray-900">Responsável Principal</dt>
                        <dd>{{ activeOption.main_guardian_name }}</dd>
                      </template>
                      <template v-if="activeOption.classroom_name">
                        <dt class="col-end-1 font-semibold text-gray-900">Turma</dt>
                        <dd>{{ activeOption.classroom_name }}</dd>
                      </template>
                      <template v-if="activeOption.enrollment_status">
                        <dt class="col-end-1 font-semibold text-gray-900">Status</dt>
                        <dd>
                          <span :class="getEnrollmentStatusClass(activeOption.enrollment_status)">
                            {{ getEnrollmentStatusLabel(activeOption.enrollment_status) }}
                          </span>
                        </dd>
                      </template>
                    </dl>
                    <button 
                      type="button" 
                      @click="onSelect(activeOption)"
                      class="mt-6 w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                    >
                      Selecionar como Irmão
                    </button>
                  </div>
                </div>
              </ComboboxOptions>

              <div 
                v-if="filteredStudents.length === 0 && !loading" 
                class="px-6 py-14 text-center text-sm sm:px-14"
              >
                <UsersIcon class="mx-auto size-6 text-gray-400" aria-hidden="true" />
                <p class="mt-4 font-semibold text-gray-900">Nenhum aluno encontrado</p>
                <p v-if="query !== ''" class="mt-2 text-gray-500">
                  Não encontramos nenhum aluno com esse termo. Tente novamente.
                </p>
                <p v-else class="mt-2 text-gray-500">
                  Não há alunos cadastrados no sistema.
                </p>
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
import { usePage } from '@inertiajs/vue3'
import { MagnifyingGlassIcon } from '@heroicons/vue/20/solid'
import { ChevronRightIcon, UsersIcon, UserIcon } from '@heroicons/vue/24/outline'
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
import debounce from 'lodash/debounce'
import axios from 'axios'

const props = defineProps({
  open: {
    type: Boolean,
    default: false
  },
  excludeStudentId: {
    type: Number,
    default: null
  }
})

const emit = defineEmits(['update:open', 'select'])

const page = usePage()
const query = ref('')
const students = ref([])
const loading = ref(false)

const filteredStudents = computed(() => {
  return students.value.filter(student => {
    // Excluir o próprio aluno da lista
    if (props.excludeStudentId && student.id === props.excludeStudentId) {
      return false
    }
    return true
  })
})

const fetchStudents = debounce(async (searchTerm = '') => {
  loading.value = true
  try {
    const url = searchTerm && searchTerm.trim() !== '' 
      ? `/api/students?q=${encodeURIComponent(searchTerm)}`
      : '/api/students'
    const response = await axios.get(url)
    students.value = response.data || []
  } catch (error) {
    console.error('Erro ao buscar alunos:', error)
    students.value = []
  } finally {
    loading.value = false
  }
}, 400)

const handleSearch = (event) => {
  const searchTerm = event.target.value
  query.value = searchTerm
  fetchStudents(searchTerm)
}

watch(query, (newValue) => {
  fetchStudents(newValue)
})

watch(() => props.open, (newValue) => {
  if (newValue) {
    fetchStudents('')
  } else {
    query.value = ''
    students.value = []
  }
})

function closeDialog() {
  emit('update:open', false)
}

function onSelect(student) {
  if (student && student.id) {
    emit('select', student)
    emit('update:open', false)
  }
}

function formatCPF(cpf) {
  if (!cpf) return ''
  const cleaned = cpf.replace(/\D/g, '')
  if (cleaned.length !== 11) return cpf
  return cleaned.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
}

function formatDate(date) {
  if (!date) return ''
  const d = new Date(date)
  return d.toLocaleDateString('pt-BR')
}

function getEnrollmentStatusLabel(status) {
  const labels = {
    'active': 'Ativa',
    'pending': 'Pendente',
    'cancelled': 'Cancelada',
    'completed': 'Concluída',
    'transferred': 'Transferida'
  }
  return labels[status] || status
}

function getEnrollmentStatusClass(status) {
  const classes = {
    'active': 'text-green-600',
    'pending': 'text-yellow-600',
    'cancelled': 'text-red-600',
    'completed': 'text-blue-600',
    'transferred': 'text-gray-600'
  }
  return classes[status] || 'text-gray-600'
}
</script>

