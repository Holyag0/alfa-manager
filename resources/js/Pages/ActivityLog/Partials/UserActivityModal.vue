<template>
    <div
        class="fixed inset-0 z-50 overflow-y-auto"
        @click.self="emit('close')"
    >
        <div class="flex items-center justify-center min-h-screen px-4">
            <!-- Overlay -->
            <div
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                @click="emit('close')"
            ></div>

            <!-- Modal -->
            <div
                class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden"
            >
                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-green-500 text-white p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold">
                                👤 {{ user.name }}
                            </h3>
                            <p class="text-blue-100 mt-1">
                                Feed completo de atividades
                            </p>
                        </div>
                        <button
                            @click="emit('close')"
                            class="text-white hover:text-gray-200 text-3xl leading-none"
                        >
                            ×
                        </button>
                    </div>

                    <!-- Filtros -->
                    <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs text-blue-100 mb-1">Data Início</label>
                            <input
                                type="date"
                                v-model="filters.date_from"
                                @change="loadActivities"
                                class="w-full px-3 py-2 rounded-md text-gray-900 text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs text-blue-100 mb-1">Data Fim</label>
                            <input
                                type="date"
                                v-model="filters.date_to"
                                @change="loadActivities"
                                class="w-full px-3 py-2 rounded-md text-gray-900 text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs text-blue-100 mb-1">Hora Início</label>
                            <input
                                type="time"
                                v-model="filters.time_from"
                                @change="loadActivities"
                                class="w-full px-3 py-2 rounded-md text-gray-900 text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs text-blue-100 mb-1">Hora Fim</label>
                            <input
                                type="time"
                                v-model="filters.time_to"
                                @change="loadActivities"
                                class="w-full px-3 py-2 rounded-md text-gray-900 text-sm"
                            />
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-6 overflow-y-auto max-h-[calc(90vh-250px)]">
                    <div v-if="loading" class="text-center py-12">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="text-gray-500 mt-2">Carregando...</p>
                    </div>

                    <div v-else-if="activities.data.length === 0" class="text-center py-12">
                        <p class="text-xl mb-2">📭</p>
                        <p class="text-gray-500">Nenhuma atividade encontrada</p>
                    </div>

                    <!-- Feed de Atividades (baseado no feed-exemplo.md) -->
                    <div v-else class="flow-root">
                        <ul role="list" class="-mb-8">
                            <li v-for="(activity, activityIdx) in activities.data" :key="activity.id">
                                <div class="relative pb-8">
                                    <span
                                        v-if="activityIdx !== activities.data.length - 1"
                                        class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                        aria-hidden="true"
                                    ></span>
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span
                                                :class="[
                                                    getEventIconBackground(activity.event),
                                                    'flex size-8 items-center justify-center rounded-full ring-8 ring-white'
                                                ]"
                                            >
                                                <component
                                                    :is="getEventIcon(activity.event)"
                                                    class="size-5 text-white"
                                                    aria-hidden="true"
                                                />
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    <span class="font-medium text-gray-900">{{ activity.description }}</span>
                                                </p>
                                                <div class="mt-1 flex items-center gap-2 text-xs text-gray-400">
                                                    <span>🏷️ {{ activity.log_name }}</span>
                                                    <span
                                                        class="px-2 py-1 rounded-full text-xs font-medium"
                                                        :class="getEventBadgeClass(activity.event)"
                                                    >
                                                        {{ activity.event_label }}
                                                    </span>
                                                </div>
                                                <!-- Propriedades (se houver) -->
                                                <div
                                                    v-if="Object.keys(activity.properties).length > 0"
                                                    class="mt-2 text-xs text-gray-600 bg-gray-50 p-2 rounded"
                                                >
                                                    <div
                                                        v-for="(value, key) in activity.properties"
                                                        :key="key"
                                                        class="flex gap-2"
                                                    >
                                                        <span class="font-medium">{{ key }}:</span>
                                                        <span>{{ value }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                <time :datetime="activity.created_at">
                                                    {{ activity.created_at_date }}
                                                </time>
                                                <div class="text-xs text-gray-400">
                                                    {{ activity.created_at_time }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Paginação -->
                    <div v-if="activities.data.length > 0 && activities.next_page_url" class="mt-6 flex justify-center">
                        <button
                            @click="loadMoreActivities"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                        >
                            Carregar mais
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { CheckIcon, HandThumbUpIcon, UserIcon, TrashIcon, ArrowPathIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    user: Object,
});

const emit = defineEmits(['close']);

const activities = ref({ data: [] });
const loading = ref(true);
const currentPage = ref(1);
const filters = ref({
    date_from: '',
    date_to: '',
    time_from: '',
    time_to: '',
    log_name: '',
    event: '',
});

const loadActivities = async (page = 1) => {
    loading.value = true;
    currentPage.value = page;
    
    try {
        const response = await axios.get(
            route('auditoria.user.activities', props.user.id),
            { params: { ...filters.value, page } }
        );
        
        if (page === 1) {
            activities.value = response.data.activities;
        } else {
            activities.value.data = [...activities.value.data, ...response.data.activities.data];
            activities.value.next_page_url = response.data.activities.next_page_url;
        }
    } catch (error) {
        console.error('Erro ao carregar atividades:', error);
    } finally {
        loading.value = false;
    }
};

const loadMoreActivities = () => {
    if (activities.value.next_page_url) {
        loadActivities(currentPage.value + 1);
    }
};

onMounted(() => {
    loadActivities();
});

const getEventIcon = (event) => {
    if (!event) return UserIcon;
    
    const icons = {
        created: CheckIcon,
        updated: HandThumbUpIcon,
        deleted: TrashIcon,
        restored: ArrowPathIcon,
    };
    return icons[event] || UserIcon;
};

const getEventIconBackground = (event) => {
    if (!event) return 'bg-gray-400';
    
    const backgrounds = {
        created: 'bg-green-500',
        updated: 'bg-blue-500',
        deleted: 'bg-red-500',
        restored: 'bg-purple-500',
    };
    return backgrounds[event] || 'bg-gray-400';
};

const getEventBadgeClass = (event) => {
    if (!event) return 'bg-gray-100 text-gray-800';
    
    const classes = {
        created: 'bg-green-100 text-green-800',
        updated: 'bg-blue-100 text-blue-800',
        deleted: 'bg-red-100 text-red-800',
        restored: 'bg-purple-100 text-purple-800',
    };
    return classes[event] || 'bg-gray-100 text-gray-800';
};
</script>

