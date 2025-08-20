<template>
<Disclosure>
    <header class="relative isolate z-10 bg-gradient-to-b from-sky-500 rounded-sm  to-emerald-500 ">
        <nav class="mx-auto flex max-w-7xl items-center justify-between p-6 lg:px-8" aria-label="Global">
            <div class="flex lg:flex-1">
                <a href="#" class="-m-1.5 p-1.5">
                    <span class="sr-only">Your Company</span>
                    <img class="h-12 w-auto" src="./Imagens/20200615180924320.png" alt="" />
                </a>
            </div>
            <div class="flex lg:hidden">
                <DisclosureButton
                    class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-100 hover:bg-green-400 hover:text-black focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                    <span class="absolute -inset-0.5" />
                    <span class="sr-only">Open main menu</span>
                    <Bars3Icon v-if="!open" class="block size-6" aria-hidden="true" />
                    <XMarkIcon v-else class="block size-6" aria-hidden="true" />
                </DisclosureButton>

            </div>
            <PopoverGroup class="hidden lg:flex lg:gap-x-12">
                <Popover>
                    <PopoverButton class="flex items-center gap-x-1 text-sm/6 font-semibold text-gray-100
                    transition ease-in-out delay-150 hover:text-sky-800 hover:translate-y-1 hover:scale-110 ">
                        Cadastro
                        <ChevronDownIcon class="size-5 flex-none text-white " aria-hidden="true" />
                    </PopoverButton>

                    <transition enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 -translate-y-1" enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-1">
                        <PopoverPanel class="absolute inset-x-0 top-0 -z-10 bg-sky-800 pt-14 shadow-lg rounded-md">
                            <div class="mx-auto grid max-w-7xl grid-cols-4 gap-x-4 px-6 py-10 lg:px-8 xl:gap-x-8">
                                <div v-for="item in products" :key="item.name"
                                    class="group relative rounded-lg p-6 text-sm/6 hover:bg-white/5">
                                    <div
                                        class="flex size-11 items-center justify-center rounded-lg bg-gray-50 group-hover:bg-white/5">
                                        <component :is="item.icon"
                                            class="size-6 text-gray-600 group-hover:text-green-500"
                                            aria-hidden="true" />
                                    </div>
                                    <Link :href="item.href" class="mt-6 block font-semibold text-white">
                                    {{ item.name }}
                                    <span class="absolute inset-0" />
                                    </Link>
                                    <p class="mt-1 text-gray-100">{{ item.description }}</p>
                                </div>
                            </div>
                            <div class="bg-white/5 ">
                                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                                    <div class="grid grid-cols-3 divide-x divide-gray-900/5 ">
                                        <a v-for="item in callsToAction" :key="item.name" :href="item.href"
                                            class="flex items-center justify-center gap-x-2.5 p-3 text-sm/6 font-semibold text-gray-100 hover:bg-white/5">
                                            <component :is="item.icon" class="size-5 flex-none text-gray-400"
                                                aria-hidden="true" />
                                            {{ item.name }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </PopoverPanel>
                    </transition>
                </Popover>

                <a href="#" class="text-sm/6 font-semibold text-gray-100">Features</a>
                <Link :href="route('matriculas.index')" class="text-sm/6 font-semibold text-gray-100">Matrículas</Link>
                
                <!-- Módulo Comercial -->
                <Popover>
                    <PopoverButton class="flex items-center gap-x-1 text-sm/6 font-semibold text-gray-100
                    transition ease-in-out delay-150 hover:text-sky-800 hover:translate-y-1 hover:scale-110 ">
                        Comercial
                        <ChevronDownIcon class="size-5 flex-none text-white " aria-hidden="true" />
                    </PopoverButton>

                    <transition enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 -translate-y-1" enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-1">
                        <PopoverPanel class="absolute inset-x-0 top-0 -z-10 bg-sky-800 pt-14 shadow-lg rounded-md">
                            <div class="mx-auto grid max-w-7xl grid-cols-4 gap-x-4 px-6 py-10 lg:px-8 xl:gap-x-8">
                                <div v-for="item in comercialItems" :key="item.name"
                                    class="group relative rounded-lg p-6 text-sm/6 hover:bg-white/5">
                                    <div
                                        class="flex size-11 items-center justify-center rounded-lg bg-gray-50 group-hover:bg-white/5">
                                        <component :is="item.icon"
                                            class="size-6 text-gray-600 group-hover:text-green-500"
                                            aria-hidden="true" />
                                    </div>
                                    <Link :href="item.href" class="mt-6 block font-semibold text-white">
                                    {{ item.name }}
                                    <span class="absolute inset-0" />
                                    </Link>
                                    <p class="mt-1 text-gray-100">{{ item.description }}</p>
                                </div>
                            </div>
                            <div class="bg-white/5 ">
                                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                                    <div class="grid grid-cols-3 divide-x divide-gray-900/5 ">
                                        <a v-for="item in comercialActions" :key="item.name" :href="item.href"
                                            class="flex items-center justify-center gap-x-2.5 p-3 text-sm/6 font-semibold text-gray-100 hover:bg-white/5">
                                            <component :is="item.icon" class="size-5 flex-none text-gray-400"
                                                aria-hidden="true" />
                                            {{ item.name }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </PopoverPanel>
                    </transition>
                </Popover>
                
                <a href="#" class="text-sm/6 font-semibold text-gray-100">Company</a>
            </PopoverGroup>
            <div class="lg:flex lg:flex-1 lg:justify-end">
                <Dropdown align="right" width="48">
                    <template #trigger :layout="layout">
                        <button v-if="$page.props.jetstream.managesProfilePhotos"
                            class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                            <img class="size-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url"
                                :alt="$page.props.auth.user.name">
                        </button>

                        <span v-else class="inline-flex rounded-md">
                            <button type="button"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                {{ $page.props.auth?.user?.name ?? ''}}

                                <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                        </span>
                    </template>

                    <template #content 
                    >
                        <!-- Account Management -->
                        <div class="block px-4 py-2 text-xs text-gray-500">
                          Opções do Perfil
                        </div>

                        <DropdownLink :href="route('profile.show')">
                            Profile
                        </DropdownLink>

                        <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">
                            API Tokens
                        </DropdownLink>

                        <div class="border-t border-gray-200" />

                        <!-- Authentication -->
                        <form @submit.prevent="logout">
                            <DropdownLink as="button">
                                Log Out
                            </DropdownLink>
                        </form>
                    </template>
                </Dropdown>
            </div>
        </nav>
    </header>
    <DisclosurePanel class="border-b border-gray-700 md:hidden">
        <div class="space-y-1 px-2 py-3 sm:px-3">
            <!-- Menu Cadastro Mobile -->
            <DisclosureButton class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">
                Cadastro
            </DisclosureButton>
            <div class="ml-4 space-y-1">
                <Link :href="route('user.index')" class="block rounded-md px-3 py-2 text-sm font-medium text-gray-400 hover:bg-gray-700 hover:text-white">
                    Usuários
                </Link>
                <Link :href="route('roles.index')" class="block rounded-md px-3 py-2 text-sm font-medium text-gray-400 hover:bg-gray-700 hover:text-white">
                    Cargos
                </Link>
                <Link :href="route('permissions.index')" class="block rounded-md px-3 py-2 text-sm font-medium text-gray-400 hover:bg-gray-700 hover:text-white">
                    Permissões
                </Link>
            </div>
            
            <!-- Menu Comercial Mobile -->
            <DisclosureButton class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">
                Comercial
            </DisclosureButton>
            <div class="ml-4 space-y-1">
                <Link :href="route('services.index')" class="block rounded-md px-3 py-2 text-sm font-medium text-gray-400 hover:bg-gray-700 hover:text-white">
                    Serviços
                </Link>
                <Link :href="route('packages.index')" class="block rounded-md px-3 py-2 text-sm font-medium text-gray-400 hover:bg-gray-700 hover:text-white">
                    Pacotes
                </Link>
                <Link :href="route('services.create')" class="block rounded-md px-3 py-2 text-sm font-medium text-gray-400 hover:bg-gray-700 hover:text-white">
                    Novo Serviço
                </Link>
                <Link :href="route('packages.create')" class="block rounded-md px-3 py-2 text-sm font-medium text-gray-400 hover:bg-gray-700 hover:text-white">
                    Novo Pacote
                </Link>
            </div>
            
            <!-- Outros Links Mobile -->
            <Link :href="route('matriculas.index')" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-gray-700 hover:text-white">
                Matrículas
            </Link>
        </div>
        <div class="border-t border-gray-700 pb-3 pt-4">
            <div class="flex items-center px-5">
                <div class="shrink-0">
                    <img class="size-10 rounded-full" alt="" />
                </div>
                <div class="ml-3">
                    <div class="text-base/5 font-medium text-white">{{ user.name }}</div>
                    <div class="text-sm font-medium text-gray-400">{{ user.email }}</div>
                </div>
                <button type="button"
                    class="relative ml-auto shrink-0 rounded-full bg-gray-800 p-1 text-gray-400 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                    <span class="absolute -inset-1.5" />
                    <span class="sr-only">View notifications</span>
                    <BellIcon class="size-6" aria-hidden="true" />
                </button>
            </div>
            <div class="mt-3 space-y-1 px-2">
                <Link :href="route('profile.show')" class="block rounded-md px-3 py-2 text-base font-medium text-gray-400 hover:bg-gray-700 hover:text-white">
                    Profile
                </Link>
                <button @click="logout" class="block w-full text-left rounded-md px-3 py-2 text-base font-medium text-gray-400 hover:bg-gray-700 hover:text-white">
                    Log Out
                </button>
            </div>
        </div>
    </DisclosurePanel>


</Disclosure>
</template>

<script setup>
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { onMounted, ref } from 'vue'
import {
    Dialog, DialogPanel, Disclosure,
    DisclosureButton, DisclosurePanel,
    Popover, PopoverButton, PopoverGroup, PopoverPanel,
    Menu, MenuButton, MenuItem, MenuItems
} from '@headlessui/vue'
import {
    Bars3Icon,
    ChartPieIcon,
    CursorArrowRaysIcon,
    FingerPrintIcon,
    SquaresPlusIcon,
    XMarkIcon,
    CogIcon,
    CubeIcon,
    CurrencyDollarIcon,
    ShoppingCartIcon,
    TagIcon,
    ChartBarIcon,
    BellIcon,
} from '@heroicons/vue/24/outline'
import { ChevronDownIcon, PhoneIcon, PlayCircleIcon, RectangleGroupIcon } from '@heroicons/vue/20/solid'
import { Link,router  } from '@inertiajs/vue3';


const user = {
    name: 'Tom Cook',
    email: 'tom@example.com',
    imageUrl:
        'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
}
const userNavigation = [
    { name: 'Your Profile', href: '#' },
    { name: 'Settings', href: '#' },
    { name: 'Sign out', href: '/logout' },
]
const products = [
    {
        name: 'Alunos',
        description: 'Matricule novos alunos, veja lista de alunos matriculados e todas infomações dos alunos alfa baby.',
        href: '#',
        icon: ChartPieIcon,
    },
    {
        name: 'Usuários',
        description: 'Crie contas para quem vai usar o sistema e administre usuários da escola alfa baby. ',
        href: route('user.index'),
        icon: CursorArrowRaysIcon,
    },
    { name: 'Cargos', 
        description: 'Aqui você pode criar cargos para usuários da alfa baby', 
        href: route('roles.index'), 
        icon: FingerPrintIcon 
    },
    {
        name: 'Permissões',
        description: 'Permita,crie e edite o que cada usuário pode fazer no sistema',
        href: route('permissions.index'),
        icon: SquaresPlusIcon,
    },
    
]
const callsToAction = [
    { name: 'Watch demo', href: '#', icon: PlayCircleIcon },
    { name: 'Contact sales', href: '#', icon: PhoneIcon },
    { name: 'View all products', href: '#', icon: RectangleGroupIcon },
]

const comercialItems = [
    {
        name: 'Serviços',
        description: 'Gerencie os serviços oferecidos pela escola, defina preços e categorias.',
        href: route('services.index'),
        icon: CogIcon,
    },
    {
        name: 'Pacotes',
        description: 'Crie pacotes combinando múltiplos serviços com preços especiais.',
        href: route('packages.index'),
        icon: CubeIcon,
    },
    {
        name: 'Categorias',
        description: 'Organize serviços e pacotes por categorias para melhor gestão.',
        href: route('categories.index'),
        icon: TagIcon,
    },
    {
        name: 'Relatórios',
        description: 'Acompanhe vendas, faturamento e performance dos produtos.',
        href: '#',
        icon: ChartBarIcon,
    },
]

const comercialActions = [
    { name: 'Novo Serviço', href: route('services.create'), icon: CogIcon },
    { name: 'Novo Pacote', href: route('packages.create'), icon: CubeIcon },
    { name: 'Nova Categoria', href: route('categories.create'), icon: TagIcon },
    { name: 'Ver Todos', href: route('services.index'), icon: ShoppingCartIcon },
]

const mobileMenuOpen = ref(false)
const logout = () => {
    router.post(route('logout'));
};
defineProps({
    layout:false,
    
})
</script>