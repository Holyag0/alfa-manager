<template>
  <div class="min-h-screen ">
    <div class="border-b border-cyan-500">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6 md:flex md:items-center md:justify-between">
          <div class="flex-1 min-w-0">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <h1 class="text-2xl font-bold text-sky-700">Turmas</h1>
                <strong class="text-sm text-gray-500">Gerencie as turmas e acompanhe a ocupação</strong>
              </div>
            </div>
          </div>
          <div class="mt-4 flex items-center gap-2 md:mt-0 md:ml-4">
            <button 
              @click="refreshData"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              Atualizar
            </button>
          </div>
        </div>
      </div>
    </div>

      <!-- Filtros (layout igual ao FiltersCard.vue) -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8 overflow-hidden mt-6">
        <!-- Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"/>
                  </svg>
                </div>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900">Filtros Avançados</h3>
                <p class="text-sm text-gray-600">Filtre as turmas por nome, status ou ocupação</p>
              </div>
            </div>
            <!-- Toggle Button -->
            <button 
              @click="toggleFilters"
              class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white shadow-sm border border-gray-200 hover:bg-gray-50 transition-colors duration-200"
              :class="{ 'rotate-180': isExpanded }"
            >
              <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Filters Content -->
        <Transition
          enter-active-class="transition-all duration-300 ease-out"
          enter-from-class="opacity-0 max-h-0"
          enter-to-class="opacity-100 max-h-96"
          leave-active-class="transition-all duration-300 ease-in"
          leave-from-class="opacity-100 max-h-96"
          leave-to-class="opacity-0 max-h-0"
        >
          <div v-show="isExpanded" class="overflow-hidden">
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Filtro por Nome da Turma -->
          <div>
            <label for="filter-name" class="block text-sm font-medium text-gray-700 mb-1">
              Nome da Turma
            </label>
            <input
              id="filter-name"
              v-model="filters.name"
              type="text"
              placeholder="Digite o nome da turma..."
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
          </div>

          <!-- Filtro por Status de Ocupação -->
          <div>
            <label for="filter-occupation" class="block text-sm font-medium text-gray-700 mb-1">
              Status de Ocupação
            </label>
            <select
              id="filter-occupation"
              v-model="filters.occupation"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="">Todas as ocupações</option>
              <option value="available">Disponível (&lt; 70%)</option>
              <option value="almost-full">Quase cheia (70-89%)</option>
              <option value="full">Lotada (≥ 90%)</option>
            </select>
          </div>

          <!-- Filtro por Status da Turma -->
          <div>
            <label for="filter-status" class="block text-sm font-medium text-gray-700 mb-1">
              Status da Turma
            </label>
            <select
              id="filter-status"
              v-model="filters.status"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="">Todas as turmas</option>
              <option value="active">Ativa</option>
              <option value="inactive">Inativa</option>
            </select>
          </div>
              </div>

              <!-- Actions -->
              <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200">
                <div class="flex items-center space-x-4">
                  <div class="text-sm text-gray-500">
                    {{ activeFiltersCount }} filtro{{ activeFiltersCount !== 1 ? 's' : '' }} ativo{{ activeFiltersCount !== 1 ? 's' : '' }}
                  </div>
                  <button 
                    v-if="hasActiveFilters"
                    @click="clearFilters"
                    type="button"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-200"
                  >
                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Limpar Filtros
                  </button>
                </div>

                <div class="flex items-center space-x-3">
                  <button 
                    @click="isExpanded = false"
                    type="button"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                  >
                    Fechar
                  </button>
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </div>

      <!-- Results Section -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
              <h3 class="text-lg font-medium text-gray-900">
                Turmas
              </h3>
              <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                {{ filteredClassrooms.length }} de {{ classrooms.length }}
              </span>
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="px-6 py-8">
          <div class="animate-pulse">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div v-for="i in 6" :key="i" class="bg-white rounded-lg shadow p-6">
                <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
                <div class="h-3 bg-gray-200 rounded w-1/2 mb-2"></div>
                <div class="h-3 bg-gray-200 rounded w-1/3"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="px-6 py-8">
          <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                  Erro ao carregar turmas
                </h3>
                <div class="mt-2 text-sm text-red-700">
                  {{ error }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Turmas Grid -->
        <div v-else class="px-6 py-8">
          <ul role="list" class="grid grid-cols-1 gap-x-6 gap-y-8 lg:grid-cols-3 xl:gap-x-8">
            <li v-for="classroom in filteredClassrooms" :key="classroom.id" class="overflow-hidden rounded-xl border-2 border-sky-700">
            <div class="flex items-center gap-x-4 border-b border-gray-900/5 bg-gray-50 p-6">
              <!-- Ícone da turma -->
              <div class="size-12 flex-none rounded-lg bg-white object-cover ring-1 ring-gray-900/10 flex items-center justify-center">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
              </div>
              <div class="text-sm/6 font-medium text-gray-900">{{ classroom.name }}</div>
              <Menu as="div" class="relative ml-auto">
                <MenuButton class="relative block text-gray-400 hover:text-gray-500">
                  <span class="absolute -inset-2.5"></span>
                  <span class="sr-only">Abrir opções</span>
                  <EllipsisHorizontalIcon class="size-5" aria-hidden="true" />
                </MenuButton>
                <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform scale-100" leave-to-class="transform opacity-0 scale-95">
                  <MenuItems class="absolute right-0 z-10 mt-0.5 w-32 origin-top-right rounded-md bg-white py-2 shadow-lg outline-1 outline-gray-900/5">
                    <MenuItem v-slot="{ active }">
                      <button @click="viewClassroomDetails(classroom)" :class="[active ? 'bg-gray-50 outline-hidden' : '', 'block px-3 py-1 text-sm/6 text-gray-900 w-full text-left']">
                        Ver<span class="sr-only">, {{ classroom.name }}</span>
                      </button>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                      <button @click="editClassroom(classroom)" :class="[active ? 'bg-gray-50 outline-hidden' : '', 'block px-3 py-1 text-sm/6 text-gray-900 w-full text-left']">
                        Editar<span class="sr-only">, {{ classroom.name }}</span>
                      </button>
                    </MenuItem>
                  </MenuItems>
                </transition>
              </Menu>
            </div>
            <dl class="-my-3 divide-y divide-gray-100 px-6 py-4 text-sm/6">
              <div class="flex justify-between gap-x-4 py-3">
                <dt class="text-gray-500">Período</dt>
                <dd class="text-gray-700">{{ classroom.year }} - {{ classroom.shift }}</dd>
              </div>
              <div class="flex justify-between gap-x-4 py-3">
                <dt class="text-gray-500">Ocupação</dt>
                <dd class="flex items-start gap-x-2">
                  <div class="font-medium text-gray-900">{{ classroom.current_students }} / {{ classroom.max_students }}</div>
                  <div v-if="classroom.occupation_percentage >= 90" class="rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-red-600/10 ring-inset">Lotada</div>
                  <div v-else-if="classroom.occupation_percentage >= 70" class="rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700 ring-1 ring-yellow-600/20 ring-inset">Quase cheia</div>
                  <div v-else class="rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-green-600/20 ring-inset">Disponível</div>
                </dd>
              </div>
              <div class="flex justify-between gap-x-4 py-3">
                <dt class="text-gray-500">Status</dt>
                <dd class="flex items-start gap-x-2">
                  <div v-if="classroom.is_active" class="rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-green-600/20 ring-inset">Ativa</div>
                  <div v-else class="rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-gray-500/10 ring-inset">Inativa</div>
                </dd>
              </div>
              <div class="flex justify-between gap-x-4 py-3">
                <dt class="text-gray-500">Vagas disponíveis</dt>
                <dd class="text-gray-700">{{ classroom.available_slots }}</dd>
              </div>
            </dl>
          </li>
          </ul>

          <!-- Empty State -->
          <div v-if="filteredClassrooms.length === 0" class="mt-8 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma turma encontrada</h3>
            <p class="mt-1 text-sm text-gray-500">Comece criando uma nova turma.</p>
          </div>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { EllipsisHorizontalIcon } from '@heroicons/vue/20/solid'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import { route } from 'ziggy-js'

// Estado reativo
const classrooms = ref([])
const loading = ref(true)
const error = ref(null)
const isExpanded = ref(false)

// Filtros
const filters = ref({
  name: '',
  occupation: '',
  status: ''
})

// Computed para turmas filtradas
const filteredClassrooms = computed(() => {
  return classrooms.value.filter(classroom => {
    // Filtro por nome
    if (filters.value.name && !classroom.name.toLowerCase().includes(filters.value.name.toLowerCase())) {
      return false
    }

    // Filtro por status de ocupação
    if (filters.value.occupation) {
      const percentage = classroom.occupation_percentage
      switch (filters.value.occupation) {
        case 'available':
          if (percentage >= 70) return false
          break
        case 'almost-full':
          if (percentage < 70 || percentage >= 90) return false
          break
        case 'full':
          if (percentage < 90) return false
          break
      }
    }

    // Filtro por status da turma
    if (filters.value.status) {
      const isActive = classroom.is_active === 1 || classroom.is_active === true
      if (filters.value.status === 'active' && !isActive) return false
      if (filters.value.status === 'inactive' && isActive) return false
    }

    return true
  })
})

// Carregar dados das turmas
const loadClassrooms = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await axios.get('/api/classrooms-detailed')
    classrooms.value = response.data
  } catch (err) {
    console.error('Erro ao carregar turmas:', err)
    error.value = 'Erro ao carregar as turmas. Tente novamente.'
  } finally {
    loading.value = false
  }
}

// Atualizar dados
const refreshData = () => {
  loadClassrooms()
}

// Toggle filtros
const toggleFilters = () => {
  isExpanded.value = !isExpanded.value
}

// Contagem de filtros ativos (para UX igual ao FiltersCard)
const activeFiltersCount = computed(() => {
  return Object.values(filters.value).filter(v => v && v.toString().trim() !== '').length
})
const hasActiveFilters = computed(() => activeFiltersCount.value > 0)

// Obter cor da barra de ocupação
const getOccupationColor = (percentage) => {
  if (percentage >= 90) return 'bg-red-500'
  if (percentage >= 70) return 'bg-yellow-500'
  return 'bg-green-500'
}

// Ver detalhes da turma
const viewClassroomDetails = (classroom) => {
  // TODO: Implementar modal ou página de detalhes
  console.log('Ver detalhes da turma:', classroom)
}

// Editar turma
const editClassroom = (classroom) => {
  router.get(route('turmas.edit', classroom.id))
}

// Limpar filtros
const clearFilters = () => {
  filters.value = {
    name: '',
    occupation: '',
    status: ''
  }
}

// Carregar dados ao montar o componente
onMounted(() => {
  loadClassrooms()
})
</script>

<style scoped>
.rotate-180 {
  transform: rotate(180deg);
}
</style>
