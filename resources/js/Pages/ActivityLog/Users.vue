<template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-900">
                            📋 Auditoria do Sistema
                        </h2>
                        <p class="text-gray-600 mt-2">
                            Visualize as atividades realizadas por cada usuário
                        </p>
                    </div>
                </div>

                <!-- Lista de Usuários -->
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="space-y-4">
                            <div
                                v-for="user in users"
                                :key="user.id"
                                @click="openUserModal(user)"
                                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors"
                            >
                                <div class="flex items-center gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-green-500 flex items-center justify-center text-white font-bold text-lg">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ user.name }}
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            {{ user.email }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="text-right">
                                        <div class="text-sm text-gray-500">Atividades</div>
                                        <div class="text-2xl font-bold text-blue-600">
                                            {{ user.activities_count }}
                                        </div>
                                    </div>
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div
                                v-if="users.length === 0"
                                class="text-center py-12 text-gray-500"
                            >
                                <p class="text-xl mb-2">📭</p>
                                <p>Nenhum usuário encontrado</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Atividades do Usuário -->
        <UserActivityModal
            v-if="showModal"
            :user="selectedUser"
            @close="showModal = false"
        />
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import UserActivityModal from './Partials/UserActivityModal.vue';

const props = defineProps({
    users: Array,
});

const selectedUser = ref(null);
const showModal = ref(false);

const openUserModal = (user) => {
    selectedUser.value = user;
    showModal.value = true;
};
</script>

