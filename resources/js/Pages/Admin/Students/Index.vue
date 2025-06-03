<template>

    <div class="min-h-screen ">
      <!-- Header -->
      <div class="shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between items-center py-6">
            <div>
              <h1 class="text-3xl font-bold text-sky-800">Alunos</h1>
              <p class="mt-1 text-sm text-gray-600">
                Gerencie todos os alunos da escola alfa baby
              </p>
            </div>
            <Link
              :href="route('admin.students.create')"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
              Novo Aluno
            </Link>
          </div>
        </div>
      </div>

      <!-- Filtros e Busca -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Busca -->
            <div class="md:col-span-2">
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                  </svg>
                </div>
                <input
                  v-model="form.search"
                  @input="debouncedSearch"
                  type="text"
                  placeholder="Buscar por nome, CPF ou email..."
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                >
              </div>
            </div>

            <!-- Filtro Status -->
            <div>
              <select
                v-model="form.status"
                @change="applyFilters"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Todos os Status</option>
                <option value="active">Ativos</option>
                <option value="inactive">Inativos</option>
              </select>
            </div>

            <!-- Filtro Turma -->
            <div>
              <select
                v-model="form.classroom"
                @change="applyFilters"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Todas as Turmas</option>
                <option v-for="classroom in classrooms" :key="classroom.id" :value="classroom.id">
                  {{ classroom.name }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total de Alunos</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.total || 0 }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Ativos</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.active || 0 }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Inativos</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.inactive || 0 }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Sem Turma</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.without_classroom || 0 }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabela de Alunos -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Aluno
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Contato
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Turma
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ações
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <!-- Dados -->
                <tr v-for="student in students.data" :key="student.id" class="hover:bg-gray-50">
                  <!-- Aluno -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-12 w-12">
                        <img
                          v-if="student.photo_url"
                          :src="student.photo_url"
                          :alt="student.name"
                          class="h-12 w-12 rounded-full object-cover"
                        >
                        <div
                          v-else
                          class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center"
                        >
                          <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                          </svg>
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ student.name }}</div>
                        <div class="text-sm text-gray-500">{{ student.cpf }}</div>
                        <div class="text-sm text-gray-500">{{ student.birth_date_formatted }}</div>
                      </div>
                    </div>
                  </td>

                  <!-- Contato -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ student.email }}</div>
                    <div class="text-sm text-gray-500">{{ student.phone }}</div>
                    <div v-if="student.guardian" class="text-sm text-gray-500">
                      Resp: {{ student.guardian.name }}
                    </div>
                  </td>

                  <!-- Turma -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div v-if="student.current_classroom" class="text-sm">
                      <div class="font-medium text-gray-900">{{ student.current_classroom.name }}</div>
                      <div class="text-gray-500">{{ student.current_classroom.teacher?.name }}</div>
                    </div>
                    <span v-else class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                      Sem turma
                    </span>
                  </td>

                  <!-- Status -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        'px-2 py-1 text-xs font-medium rounded-full',
                        student.status === 'active'
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800'
                      ]"
                    >
                      {{ student.status === 'active' ? 'Ativo' : 'Inativo' }}
                    </span>
                  </td>

                  <!-- Ações -->
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                      <Link
                        :href="route('admin.students.show', student.id)"
                        class="text-blue-600 hover:text-blue-900 transition-colors"
                        title="Ver detalhes"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                      </Link>

                      <Link
                        :href="route('admin.students.edit', student.id)"
                        class="text-yellow-600 hover:text-yellow-900 transition-colors"
                        title="Editar"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                      </Link>

                      <button
                        @click="toggleStatus(student)"
                        :class="[
                          'transition-colors',
                          student.status === 'active'
                            ? 'text-red-600 hover:text-red-900'
                            : 'text-green-600 hover:text-green-900'
                        ]"
                        :title="student.status === 'active' ? 'Inativar' : 'Ativar'"
                      >
                        <svg v-if="student.status === 'active'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                      </button>

                      <button
                        v-if="student.current_classroom"
                        @click="transferStudent(student)"
                        class="text-purple-600 hover:text-purple-900 transition-colors"
                        title="Transferir"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Sem dados -->
                <tr v-if="students.data && students.data.length === 0">
                  <td colspan="5" class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum aluno encontrado</h3>
                    <p class="mt-1 text-sm text-gray-500">
                      {{ form.search ? 'Tente ajustar os filtros de busca.' : 'Comece cadastrando o primeiro aluno.' }}
                    </p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginação Inertia -->
          <div v-if="students.total > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="flex-1 flex justify-between sm:hidden">
                <Link
                  v-if="students.prev_page_url"
                  :href="students.prev_page_url"
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                >
                  Anterior
                </Link>
                <Link
                  v-if="students.next_page_url"
                  :href="students.next_page_url"
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                >
                  Próximo
                </Link>
              </div>
              <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-gray-700">
                    Mostrando
                    <span class="font-medium">{{ students.from }}</span>
                    até
                    <span class="font-medium">{{ students.to }}</span>
                    de
                    <span class="font-medium">{{ students.total }}</span>
                    resultados
                  </p>
                </div>
                <div v-if="students.links">
                  <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <Link
                      v-for="link in students.links"
                      :key="link.label"
                      :href="link.url"
                      v-html="link.label"
                      :class="[
                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                        link.active
                          ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                          : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                        !link.url && 'opacity-50 cursor-not-allowed'
                      ]"
                    />
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'

// Props do Inertia
const props = defineProps({
  students: Object,
  stats: Object,
  classrooms: Array,
  filters: Object
})

// Formulário de filtros
const form = reactive({
  search: props.filters.search || '',
  status: props.filters.status || '',
  classroom: props.filters.classroom || ''
})

// Debounce para busca
let searchTimeout = null
const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

// Aplicar filtros
const applyFilters = () => {
  router.get(route('admin.students.index'), form, {
    preserveState: true,
    preserveScroll: true
  })
}

// Toggle status do aluno
const toggleStatus = (student) => {
  router.patch(route('admin.students.toggle-status', student.id), {}, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      // Status será atualizado automaticamente pelo Inertia
    },
    onError: () => {
      alert('Erro ao alterar status do aluno')
    }
  })
}

// Transferir aluno
const transferStudent = (student) => {
  // Implementar modal de transferência ou redirecionar para página
  console.log('Transferir aluno:', student.name)
}
</script>