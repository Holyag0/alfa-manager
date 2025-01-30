<template>
    <header class="relative isolate z-10 bg-gradient-to-b from-sky-500 rounded-sm  to-emerald-500 ">
        <nav class="mx-auto flex max-w-7xl items-center justify-between p-6 lg:px-8" aria-label="Global">
            <div class="flex lg:flex-1">
                <a href="#" class="-m-1.5 p-1.5">
                    <span class="sr-only">Your Company</span>
                    <img class="h-8 w-auto" src="https://tailwindui.com/plus/img/logos/mark.svg?color=indigo&shade=600"
                        alt="" />
                </a>
            </div>
            <div class="flex lg:hidden">
                <button type="button"
                    class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-700"
                    @click="mobileMenuOpen = true">
                    <span class="sr-only">Open main menu</span>
                    <Bars3Icon class="size-6" aria-hidden="true" />
                </button>
            </div>
            <PopoverGroup class="hidden lg:flex lg:gap-x-12">
                <Popover>
                    <PopoverButton 
                    class="flex items-center gap-x-1 text-sm/6 font-semibold text-gray-100
                    transition ease-in-out delay-150 hover:text-green-500 hover:translate-y-1 hover:scale-110 ">
                        Cadastro
                        <ChevronDownIcon class="size-5 flex-none text-white " aria-hidden="true" />
                    </PopoverButton>

                    <transition enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 -translate-y-1" enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-1">
                        <PopoverPanel
                            class="absolute inset-x-0 top-0 -z-10 bg-sky-800 pt-14 shadow-lg rounded-md">
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
                <a href="#" class="text-sm/6 font-semibold text-gray-100">Marketplace</a>
                <a href="#" class="text-sm/6 font-semibold text-gray-100">Company</a>
            </PopoverGroup>
            <div class="lg:flex lg:flex-1 lg:justify-end">
                <Menu as="div" class="relative ml-3">
                    <div>
                        <MenuButton
                            class="relative flex max-w-xs items-center rounded-full bg-gray-800 text-sm">
                            <span class="absolute -inset-1.5" />
                            <span class="sr-only">Open user menu</span>
                            <img class="size-8 rounded-full" :src="user.imageUrl" alt="" />
                        </MenuButton>
                    </div>
                    <transition enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95">
                        <MenuItems
                            class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg focus:outline-none">
                            <MenuItem v-for="item in userNavigation" :key="item.name" v-slot="{ active }">
                            <Link :href="item.href"
                                :class="[active ? 'bg-gray-100 outline-none' : '', 'block px-4 py-2 text-sm text-gray-700']">{{
                                item.name }}</Link>
                            </MenuItem>
                        </MenuItems>
                    </transition>
                </Menu>
            </div>
        </nav>
        <Dialog class="lg:hidden" @close="mobileMenuOpen = false" :open="mobileMenuOpen">
            <div class="fixed inset-0 z-10" />
            <DialogPanel
                class="fixed inset-y-0 right-0 z-10 w-full overflow-y-auto bg-white px-6 py-6 sm:max-w-sm ">
                <div class="flex items-center justify-between">
                    <a href="#" class="-m-1.5 p-1.5">
                        <span class="sr-only">Your Company</span>
                        <img class="h-8 w-auto"
                            src="https://tailwindui.com/plus/img/logos/mark.svg?color=indigo&shade=600" alt="" />
                    </a>
                    <button type="button" class="-m-2.5 rounded-md p-2.5 text-gray-700" @click="mobileMenuOpen = false">
                        <span class="sr-only">Close menu</span>
                        <XMarkIcon class="size-6" aria-hidden="true" />
                    </button>
                </div>
                <div class="mt-6 flow-root">
                    <div class="-my-6 divide-y divide-gray-500/10">
                        <div class="space-y-2 py-6">
                            <Disclosure as="div" class="-mx-3" v-slot="{ open }">
                                <DisclosureButton
                                    class="flex w-full items-center  justify-between rounded-lg py-2 pl-3 pr-3.5 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">
                                    Product
                                    <ChevronDownIcon :class="[open ? 'rotate-180' : '', 'size-5 flex-none']"
                                        aria-hidden="true" />
                                </DisclosureButton>
                                <DisclosurePanel class="mt-2 space-y-2">
                                    <DisclosureButton v-for="item in [...products, ...callsToAction]" :key="item.name"
                                        as="a" :href="item.href"
                                        class="block rounded-lg py-2 pl-6 pr-3 text-sm/7 font-semibold text-gray-900 hover:bg-gray-50">
                                        {{ item.name }}</DisclosureButton>
                                </DisclosurePanel>
                            </Disclosure>
                            <a href="#"
                                class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">Features</a>
                            <a href="#"
                                class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">Marketplace</a>
                            <a href="#"
                                class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-gray-900 hover:bg-gray-50">Company</a>
                        </div>
                        <div class="py-6">
                            <a href="#"
                                class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-gray-100 hover:bg-gray-50">
                                Login
                            </a>
                        </div>
                    </div>
                </div>
            </DialogPanel>
        </Dialog>
    </header>
</template>
  
  <script setup>
  import { ref } from 'vue'
  import {
    Dialog, DialogPanel, Disclosure,
    DisclosureButton, DisclosurePanel,
    Popover,PopoverButton, PopoverGroup, PopoverPanel,
    Menu, MenuButton, MenuItem, MenuItems 
  } from '@headlessui/vue'
  import {
    Bars3Icon,
    ChartPieIcon,
    CursorArrowRaysIcon,
    FingerPrintIcon,
    SquaresPlusIcon,
    XMarkIcon,
  } from '@heroicons/vue/24/outline'
import { ChevronDownIcon, PhoneIcon, PlayCircleIcon, RectangleGroupIcon } from '@heroicons/vue/20/solid'
import { Link } from '@inertiajs/vue3';


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
      description: 'Get a better understanding where your traffic is coming from',
      href: '#',
      icon: ChartPieIcon,
    },
    {
      name: 'Usuários',
      description: 'Speak directly to your customers with our engagement tool',
      href: route('users.index'),
      icon: CursorArrowRaysIcon,
    },
    { name: 'Cargos', description: 'Your customers’ data will be safe and secure', href: '#', icon: FingerPrintIcon },
    {
      name: 'Permissões',
      description: 'Your customers’ data will be safe and secure',
      href: '#',
      icon: SquaresPlusIcon,
    },
  ]
  const callsToAction = [
    { name: 'Watch demo', href: '#', icon: PlayCircleIcon },
    { name: 'Contact sales', href: '#', icon: PhoneIcon },
    { name: 'View all products', href: '#', icon: RectangleGroupIcon },
  ]
  
  const mobileMenuOpen = ref(false)
  </script>