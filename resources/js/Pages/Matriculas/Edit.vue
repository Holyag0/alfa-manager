<template>
  <div class="max-w-4xl mx-auto py-10">    
    <!-- Header com informações básicas -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Editar Matrícula #{{ enrollment.id }}</h1>
          <p class="text-sm text-gray-600 mt-1">
            Aluno: <span class="font-medium">{{ enrollment.student.name }}</span> • 
            Aberta por: <span class="font-medium">{{ enrollment.guardian.name }}</span>
          </p>
        </div>
        <div class="flex space-x-3">
          <Link :href="route('students.edit', enrollment.student.id)" 
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <UserIcon class="w-4 h-4 mr-2" />
            Editar Aluno
          </Link>
          <Link :href="route('guardian.index')" 
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <UsersIcon class="w-4 h-4 mr-2" />
            Gerenciar Responsáveis
          </Link>
        </div>
      </div>
    </div>

    <!-- Abas da Matrícula -->
    <div class="mb-6">
      <nav class="border-b border-gray-200">
        <ul class="flex -mb-px">
          <li v-for="tab in tabs" :key="tab.name" class="mr-8">
            <button
              :class="[
                currentTab === tab.name
                  ? 'border-indigo-500 text-indigo-600'
                  : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                'group inline-flex items-center border-b-2 px-1 py-4 text-sm font-medium focus:outline-none'
              ]"
              @click="currentTab = tab.name"
              type="button"
            >
              <component :is="tab.icon" :class="[currentTab === tab.name ? 'text-indigo-500' : 'text-gray-400 group-hover:text-gray-500', 'mr-2 -ml-0.5 size-5']" aria-hidden="true" />
              <span>{{ tab.name }}</span>
            </button>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Conteúdo das Abas -->
    <div class="grid grid-cols-1 gap-8" :class="currentTab === 'Matrícula' ? 'lg:grid-cols-5' : 'lg:grid-cols-1'">
      <!-- Conteúdo Principal -->
      <div :class="currentTab === 'Matrícula' ? 'lg:col-span-3' : 'lg:col-span-1'">
        <EditMatricula v-if="currentTab === 'Matrícula'" :enrollment="enrollment" :classrooms="classrooms" />
        <EditFinanceiro v-else-if="currentTab === 'Financeiro'" :enrollment="enrollment" />
      </div>
      
      <!-- Sidebar com Ações Rápidas - APENAS na aba Matrícula -->
      <div v-if="currentTab === 'Matrícula'" class="lg:col-span-2">
        <QuickActionsCard :enrollment="enrollment" :classrooms="classrooms" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { UserIcon, UsersIcon, CreditCardIcon, AcademicCapIcon } from '@heroicons/vue/20/solid';
import FlashMessenger from '@/Shared/FlashMessenger.vue';
import EditMatricula from './components/EditMatricula.vue';
import EditFinanceiro from './components/EditFinanceiro.vue';
import QuickActionsCard from './components/QuickActionsCard.vue';

const props = defineProps({
  enrollment: Object,
  classrooms: Array
});

const tabs = [
  { name: 'Matrícula', icon: AcademicCapIcon },
  { name: 'Financeiro', icon: CreditCardIcon }
];

const currentTab = ref(tabs[0].name);
</script> 