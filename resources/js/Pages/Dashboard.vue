<template>
  <div class="max-w-7xl mx-auto">
    <main class="pb-8">
      <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:max-w-7xl lg:px-8">
        <h1 class="sr-only">Dashboard</h1>
        <!-- Main 3 column grid -->
        <div class="grid grid-cols-1 items-start gap-4 lg:grid-cols-3 lg:gap-8">
          <!-- Left column -->
          <div class="grid grid-cols-1 gap-4 lg:col-span-2">
            <!-- Welcome panel -->
            <section aria-labelledby="profile-overview-title">
              <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <h2 class="sr-only" id="profile-overview-title">Profile Overview</h2>
                <div class="bg-white p-6">
                  <div class="sm:flex sm:items-center sm:justify-between">
                    <div class="sm:flex sm:space-x-5">
                      <div class="shrink-0">
                        <img 
                          v-if="user.imageUrl"
                          class="mx-auto size-20 rounded-full" 
                          :src="user.imageUrl" 
                          alt="" 
                        />
                        <div 
                          v-else
                          class="mx-auto size-20 rounded-full bg-indigo-100 flex items-center justify-center"
                        >
                          <UserIcon class="size-10 text-indigo-600" />
                        </div>
                      </div>
                      <div class="mt-4 text-center sm:mt-0 sm:pt-1 sm:text-left">
                        <p class="text-sm font-medium text-gray-600">Bem-vindo de volta,</p>
                        <p class="text-xl font-bold text-gray-900 sm:text-2xl">{{ user.name }}</p>
                        <p class="text-sm font-medium text-gray-600">{{ user.role || 'Usuário' }}</p>
                      </div>
                    </div>
                    <div class="mt-5 flex justify-center sm:mt-0">
                      <Link
                        :href="route('profile.show')"
                        class="flex items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 ring-inset hover:bg-gray-50"
                      >
                        Ver Perfil
                      </Link>
                    </div>
                  </div>
                </div>
                <div class="grid grid-cols-1 divide-y divide-gray-200 border-t border-gray-200 bg-gray-50 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                  <div v-for="stat in stats" :key="stat.label" class="px-6 py-5 text-center text-sm font-medium">
                    <span class="text-gray-900">{{ stat.value }}</span>
                    {{ ' ' }}
                    <span class="text-gray-600">{{ stat.label }}</span>
                  </div>
                </div>
              </div>
            </section>

            <!-- Actions panel -->
            <section aria-labelledby="quick-links-title">
              <div class="divide-y divide-gray-200 overflow-hidden rounded-lg bg-gray-200 shadow-sm sm:grid sm:grid-cols-2 sm:gap-px sm:divide-y-0">
                <h2 class="sr-only" id="quick-links-title">Ações rápidas</h2>
                <div 
                  v-for="(action, actionIdx) in actions" 
                  :key="action.name" 
                  :class="[
                    actionIdx === 0 ? 'rounded-tl-lg rounded-tr-lg sm:rounded-tr-none' : '', 
                    actionIdx === 1 ? 'sm:rounded-tr-lg' : '', 
                    actionIdx === actions.length - 2 ? 'sm:rounded-bl-lg' : '', 
                    actionIdx === actions.length - 1 ? 'rounded-br-lg rounded-bl-lg sm:rounded-bl-none' : '', 
                    'group relative bg-white p-6 focus-within:ring-2 focus-within:ring-cyan-500 focus-within:ring-inset'
                  ]"
                >
                  <div>
                    <span :class="[action.iconBackground, action.iconForeground, 'inline-flex rounded-lg p-3 ring-4 ring-white']">
                      <component :is="action.icon" class="size-6" aria-hidden="true" />
                    </span>
                  </div>
                  <div class="mt-8">
                    <h3 class="text-lg font-medium">
                      <a 
                        v-if="action.action === 'search'"
                        @click.prevent="openSearch = !openSearch"
                        href="#" 
                        class="focus:outline-hidden cursor-pointer"
                      >
                        <!-- Extend touch target to entire panel -->
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        {{ action.name }}
                      </a>
                      <Link 
                        v-else
                        :href="action.href" 
                        class="focus:outline-hidden"
                      >
                        <!-- Extend touch target to entire panel -->
                        <span class="absolute inset-0" aria-hidden="true"></span>
                        {{ action.name }}
                      </Link>
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">{{ action.description }}</p>
                  </div>
                  <span class="pointer-events-none absolute top-6 right-6 text-gray-300 group-hover:text-gray-400" aria-hidden="true">
                    <svg class="size-6" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M20 4h1a1 1 0 00-1-1v1zm-1 12a1 1 0 102 0h-2zM8 3a1 1 0 000 2V3zM3.293 19.293a1 1 0 101.414 1.414l-1.414-1.414zM19 4v12h2V4h-2zm1-1H8v2h12V3zm-.707.293l-16 16 1.414 1.414 16-16-1.414-1.414z" />
                    </svg>
                  </span>
                </div>
              </div>
            </section>
          </div>

          <!-- Right column -->
          <div class="grid grid-cols-1 gap-4">
            <!-- Mensalidades Pagas -->
            <section aria-labelledby="paid-installments-title">
              <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="p-6">
                  <h2 class="text-base font-medium text-gray-900" id="paid-installments-title">Mensalidades Pagas</h2>
                  <div class="mt-6 flow-root">
                    <ul role="list" class="-my-5 divide-y divide-gray-200">
                      <li v-for="installment in paidInstallments" :key="installment.id" class="py-5">
                        <div class="relative focus-within:ring-2 focus-within:ring-cyan-500">
                          <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                              <h3 class="text-sm font-semibold text-gray-800">
                                {{ installment.service_name }}
                              </h3>
                              <div class="mt-1 space-y-1">
                                <p class="text-xs text-gray-600">
                                  <span class="font-medium">Valor:</span> {{ formatCurrency(installment.amount) }}
                                </p>
                                <p class="text-xs text-gray-600">
                                  <span class="font-medium">Status:</span> 
                                  <span :class="getStatusClass(installment.status)">
                                    {{ getStatusLabel(installment.status) }}
                                  </span>
                                </p>
                                <p class="text-xs text-gray-600">
                                  <span class="font-medium">Pagamento:</span> {{ formatDate(installment.payment_date) }}
                                </p>
                                <p class="text-xs text-gray-600">
                                  <span class="font-medium">Vencimento:</span> {{ formatDate(installment.due_date) }}
                                </p>
                              </div>
                            </div>
                            <div class="ml-4 shrink-0">
                              <Link 
                                v-if="installment.student_id"
                                :href="route('students.edit', installment.student_id)"
                                class="inline-flex items-center rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 ring-inset hover:bg-gray-50"
                              >
                                Ver
                              </Link>
                            </div>
                          </div>
                        </div>
                      </li>
                      <li v-if="paidInstallments.length === 0" class="py-5 text-center text-sm text-gray-500">
                        Nenhuma mensalidade paga encontrada
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </section>

            <!-- Transações -->
            <section aria-labelledby="transactions-title">
              <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="p-6">
                  <h2 class="text-base font-medium text-gray-900" id="transactions-title">Transações</h2>
                  <div class="mt-6 flow-root">
                    <ul role="list" class="-my-5 divide-y divide-gray-200">
                      <li v-for="transaction in transactions" :key="transaction.id" class="py-4">
                        <div class="flex items-center justify-between">
                          <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-900">
                              <span :class="transaction.type === 'receita' ? 'text-green-600' : 'text-red-600'">
                                {{ transaction.type === 'receita' ? 'Receita' : 'Despesa' }}
                              </span>
                            </p>
                            <div class="mt-1 space-y-1">
                              <p class="text-xs text-gray-600">
                                <span class="font-medium">Valor:</span> {{ formatCurrency(transaction.amount) }}
                              </p>
                              <p class="text-xs text-gray-600">
                                <span class="font-medium">Status:</span> 
                                <span :class="getTransactionStatusClass(transaction.status)">
                                  {{ getTransactionStatusLabel(transaction.status) }}
                                </span>
                              </p>
                              <p v-if="transaction.description" class="text-xs text-gray-500 truncate">
                                {{ transaction.description }}
                              </p>
                            </div>
                          </div>
                        </div>
                      </li>
                      <li v-if="transactions.length === 0" class="py-5 text-center text-sm text-gray-500">
                        Nenhuma transação encontrada
                      </li>
                    </ul>
                  </div>
                  <div class="mt-6">
                    <Link 
                      :href="route('financial.transactions.index')"
                      class="flex w-full items-center justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 ring-inset hover:bg-gray-50"
                    >
                      Ver todas
                    </Link>
                  </div>
                </div>
              </div>
            </section>
          </div>
        </div>
      </div>
    </main>

    <!-- Componente de Busca de Alunos -->
    <StudentSearch v-model:open="openSearch" />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { MagnifyingGlassIcon, UserIcon } from '@heroicons/vue/24/outline'
import {
  ClockIcon,
  CheckBadgeIcon,
  UsersIcon,
  BanknotesIcon,
  ReceiptRefundIcon,
  AcademicCapIcon,
} from '@heroicons/vue/24/outline'
import StudentSearch from '@/Pages/Alunos/components/StudentSearch.vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  paidInstallments: {
    type: Array,
    default: () => []
  },
  transactions: {
    type: Array,
    default: () => []
  }
})

const page = usePage()
const openSearch = ref(false)

const user = computed(() => {
  const authUser = page.props.auth?.user
  return {
    name: authUser?.name || 'Usuário',
    email: authUser?.email || '',
    role: 'Administrador',
    imageUrl: authUser?.profile_photo_url || null,
  }
})

const stats = [
  { label: 'Alunos cadastrados', value: '0' },
  { label: 'Matrículas ativas', value: '0' },
  { label: 'Turmas', value: '0' },
]

const actions = [
  {
    icon: UsersIcon,
    name: 'Buscar Aluno',
    href: '#',
    description: 'Encontre e gerencie informações dos alunos',
    iconForeground: 'text-sky-700',
    iconBackground: 'bg-sky-50',
    action: 'search',
  },
  {
    icon: CheckBadgeIcon,
    name: 'Matrículas',
    href: route('matriculas.index'),
    description: 'Gerencie as matrículas dos alunos',
    iconForeground: 'text-purple-700',
    iconBackground: 'bg-purple-50',
  },
  {
    icon: ClockIcon,
    name: 'Turmas',
    href: route('turmas.index'),
    description: 'Visualize e gerencie as turmas',
    iconForeground: 'text-teal-700',
    iconBackground: 'bg-teal-50',
  },
  {
    icon: BanknotesIcon,
    name: 'Folha de Pagamento',
    href: route('payroll.index'),
    description: 'Gerencie a folha de pagamento',
    iconForeground: 'text-yellow-700',
    iconBackground: 'bg-yellow-50',
  },
  {
    icon: ReceiptRefundIcon,
    name: 'Financeiro',
    href: route('financial.transactions.index'),
    description: 'Acompanhe transações financeiras',
    iconForeground: 'text-rose-700',
    iconBackground: 'bg-rose-50',
  },
  {
    icon: AcademicCapIcon,
    name: 'Cadastros',
    href: route('user.index'),
    description: 'Gerencie usuários e permissões',
    iconForeground: 'text-indigo-700',
    iconBackground: 'bg-indigo-50',
  },
]

// Funções auxiliares
function formatCurrency(value) {
  if (!value) return 'R$ 0,00'
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

function formatDate(date) {
  if (!date) return '-'
  const d = new Date(date)
  return d.toLocaleDateString('pt-BR')
}

function getStatusLabel(status) {
  const labels = {
    'paid': 'Pago',
    'pending': 'Pendente',
    'overdue': 'Vencido',
    'cancelled': 'Cancelado',
    'waived': 'Isento'
  }
  return labels[status] || status
}

function getStatusClass(status) {
  const classes = {
    'paid': 'text-green-600',
    'pending': 'text-yellow-600',
    'overdue': 'text-red-600',
    'cancelled': 'text-gray-600',
    'waived': 'text-blue-600'
  }
  return classes[status] || 'text-gray-600'
}

function getTransactionStatusLabel(status) {
  const labels = {
    'confirmed': 'Confirmado',
    'pending': 'Pendente',
    'cancelled': 'Cancelado'
  }
  return labels[status] || status
}

function getTransactionStatusClass(status) {
  const classes = {
    'confirmed': 'text-green-600',
    'pending': 'text-yellow-600',
    'cancelled': 'text-red-600'
  }
  return classes[status] || 'text-gray-600'
}
</script>
