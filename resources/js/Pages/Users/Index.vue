<template>
    <div class="lg:flex lg:items-center lg:justify-between">
      <div class="min-w-0 flex-1">
        <h2 class="text-2xl/7 font-bold text-sky-700 sm:truncate sm:text-3xl sm:tracking-tight">
            Usuários
        </h2>
        <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
          <div class="mt-2 flex items-center text-sm text-gray-500">
            <BriefcaseIcon class="mr-1.5 size-5 shrink-0 text-gray-400" aria-hidden="true" />
            Full-time
          </div>
          <div class="mt-2 flex items-center text-sm text-gray-500">
            <MapPinIcon class="mr-1.5 size-5 shrink-0 text-gray-400" aria-hidden="true" />
            Remote
          </div>
          <div class="mt-2 flex items-center text-sm text-gray-500">
            <CurrencyDollarIcon class="mr-1.5 size-5 shrink-0 text-gray-400" aria-hidden="true" />
            $120k &ndash; $140k
          </div>
          <div class="mt-2 flex items-center text-sm text-gray-500">
            <CalendarIcon class="mr-1.5 size-5 shrink-0 text-gray-400" aria-hidden="true" />
            Closing on January 9, 2020
          </div>
        </div>
      </div>
      <div class="mt-5 flex lg:ml-4 lg:mt-0">
        <span class="hidden sm:block">
          <button type="button" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            <PencilIcon class="-ml-0.5 mr-1.5 size-5 text-gray-400" aria-hidden="true" />
            Edit
          </button>
        </span>
  
        <span class="ml-3 hidden sm:block">
          <Link type="button" :href="route('admin.users.index')"
                class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            <CogIcon class="-ml-0.5 mr-1.5 size-5 text-gray-400" aria-hidden="true" />
            Config
          </Link>
        </span>
  
        <span class="sm:ml-3">
          <Create/>
        </span>
  
        <!-- Dropdown -->
        <Menu as="div" class="relative ml-3 sm:hidden">
          <MenuButton class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:ring-gray-400">
            More
            <ChevronDownIcon class="-mr-1 ml-1.5 size-5 text-gray-400" aria-hidden="true" />
          </MenuButton>
  
          <transition enter-active-class="transition ease-out duration-200" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
            <MenuItems class="absolute right-0 z-10 -mr-1 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5 focus:outline-none">
              <MenuItem v-slot="{ active }">
                <a href="#" :class="[active ? 'bg-gray-100 outline-none' : '', 'block px-4 py-2 text-sm text-gray-700']">Edit</a>
              </MenuItem>
              <MenuItem v-slot="{ active }">
                <a href="#" :class="[active ? 'bg-gray-100 outline-none' : '', 'block px-4 py-2 text-sm text-gray-700']">View</a>
              </MenuItem>
            </MenuItems>
          </transition>
        </Menu>
      </div>
    </div>
    <div class="mt-8">
      <TableUser :users="users.data" />
    </div>
    
  </template>
  
  <script setup>
  import Create from './Create.vue';
  import {
    BriefcaseIcon,
    CalendarIcon,
    ChevronDownIcon,
    CurrencyDollarIcon,
    LinkIcon,
    MapPinIcon,
    PencilIcon,
    CogIcon
  } from '@heroicons/vue/20/solid'
  import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
  import TableUser from './components/TableUser.vue';
  import { Link } from '@inertiajs/vue3';
    defineProps({
      users:Object
    })
    </script>
    