<template>
  <div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
      <h3 class="text-lg font-medium text-gray-900">Matrículas do Aluno</h3>
      <p class="mt-1 text-sm text-gray-600">
        {{ enrollments.length }} matrícula{{ enrollments.length !== 1 ? 's' : '' }} cadastrada{{ enrollments.length !== 1 ? 's' : '' }}
      </p>
    </div>

    <!-- Lista de Matrículas -->
    <ul v-if="enrollments.length > 0" role="list" class="divide-y divide-gray-100">
      <li 
        v-for="enrollment in sortedEnrollments" 
        :key="enrollment.id" 
        @click="goToEnrollment(enrollment.id)"
        class="relative flex justify-between gap-x-6 px-4 py-5 hover:bg-gray-50 sm:px-6 lg:px-8 cursor-pointer transition-all duration-200"
      >
        <div class="flex min-w-0 gap-x-4 transition-transform duration-200 hover:scale-110">
          <!-- Ícone/Status da Matrícula -->
          <div class="flex-none">
            <div :class="getStatusIconClass(enrollment.status)" class="size-12 flex-none rounded-full flex items-center justify-center">
              <component :is="getStatusIcon(enrollment.status)" class="size-6" />
            </div>
          </div>
          
          <div class="min-w-0 flex-auto">
            <!-- Ano Letivo e Status -->
            <div class="flex items-center gap-2 mb-1">
              <p class="text-sm font-semibold text-gray-900">
                Ano Letivo {{ enrollment.academic_year }}
              </p>
              <span :class="getStatusBadgeClass(enrollment.status)" class="px-2 py-0.5 text-xs font-medium rounded-full">
                {{ getStatusLabel(enrollment.status) }}
              </span>
            </div>
            
            <!-- Informações da Matrícula -->
            <div class="mt-2 space-y-1">
              <!-- Turma -->
              <p class="flex items-center text-xs text-gray-500">
                <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span class="font-medium text-gray-700">Turma:</span>
                <span class="ml-1">{{ enrollment.classroom?.name || 'Elegível (sem turma)' }}</span>
              </p>
              
              <!-- Responsável -->
              <p class="flex items-center text-xs text-gray-500">
                <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="font-medium text-gray-700">Responsável:</span>
                <span class="ml-1">{{ enrollment.guardian?.name || 'Não informado' }}</span>
              </p>
              
              <!-- Processo -->
              <p class="flex items-center text-xs text-gray-500">
                <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="font-medium text-gray-700">Processo:</span>
                <span class="ml-1 capitalize">{{ enrollment.process || 'Não informado' }}</span>
              </p>
              
              <!-- Data de Matrícula -->
              <p class="flex items-center text-xs text-gray-500">
                <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="font-medium text-gray-700">Data:</span>
                <span class="ml-1">{{ formatDate(enrollment.enrollment_date) }}</span>
              </p>
            </div>
          </div>
        </div>
        
        <div class="flex shrink-0 items-center">
          <svg class="size-5 flex-none text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </div>
      </li>
    </ul>

    <!-- Empty State -->
    <div v-else class="p-12 text-center">
      <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
      </div>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">Nenhuma matrícula cadastrada</h3>
      <p class="text-sm text-gray-500">Este aluno ainda não possui matrículas cadastradas.</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { 
  CheckCircleIcon, 
  ClockIcon, 
  XCircleIcon, 
  PauseIcon,
  ChevronRightIcon 
} from '@heroicons/vue/20/solid'

const props = defineProps({
  enrollments: {
    type: Array,
    default: () => []
  }
})

// Ordenar matrículas por ano letivo (mais recente primeiro)
const sortedEnrollments = computed(() => {
  return [...props.enrollments].sort((a, b) => {
    const yearA = parseInt(a.academic_year || 0)
    const yearB = parseInt(b.academic_year || 0)
    return yearB - yearA
  })
})

const goToEnrollment = (enrollmentId) => {
  router.visit(route('matriculas.edit', enrollmentId))
}

const getStatusLabel = (status) => {
  const labels = {
    active: 'Ativa',
    pending: 'Pendente',
    cancelled: 'Cancelada',
    completed: 'Completada',
    suspended: 'Suspensa'
  }
  return labels[status] || status
}

const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    pending: 'bg-yellow-100 text-yellow-800',
    cancelled: 'bg-red-100 text-red-800',
    completed: 'bg-blue-100 text-blue-800',
    suspended: 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusIcon = (status) => {
  const icons = {
    active: CheckCircleIcon,
    pending: ClockIcon,
    cancelled: XCircleIcon,
    completed: CheckCircleIcon,
    suspended: PauseIcon
  }
  return icons[status] || ClockIcon
}

const getStatusIconClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-600',
    pending: 'bg-yellow-100 text-yellow-600',
    cancelled: 'bg-red-100 text-red-600',
    completed: 'bg-blue-100 text-blue-600',
    suspended: 'bg-gray-100 text-gray-600'
  }
  return classes[status] || 'bg-gray-100 text-gray-600'
}

const formatDate = (dateString) => {
  if (!dateString) return 'Não informado'
  return new Date(dateString).toLocaleDateString('pt-BR')
}
</script>

