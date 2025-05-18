<template>
    <ul role="list" class="divide-y divide-gray-100">
      <li v-for="person in users" :key="person.id" class="flex justify-between gap-x-6 py-5">
        <div class="flex min-w-0 gap-x-4">
          <img class="size-12 flex-none rounded-full bg-gray-50" :src="person.profile_photo_url || fallbackImage" alt=""  />
          <div class="min-w-0 flex-auto">
            <p class="text-sm/6 font-semibold text-gray-900">
              <a href="/#" class="hover:underline">{{ person.name }}</a>
            </p>
            <p class="mt-1 flex text-xs/5 text-gray-500">
              <a :href="`mailto:${person.email}`" class="truncate hover:underline">{{ person.email }}</a>
            </p>
          </div>
        </div>
        <div class="flex shrink-0 items-center gap-x-6">
          <div class="hidden sm:flex sm:flex-col sm:items-end">
            <p class="text-sm/6 text-gray-900">{{ person.role }}</p>
            <p v-if="person.lastSeen" class="mt-1 text-xs/5 text-gray-500">
              Last seen <time :datetime="person.lastSeenDateTime">{{ person.lastSeen }}</time>
            </p>
            <div v-else class="mt-1 flex items-center gap-x-1.5">
              <div class="flex-none rounded-full bg-emerald-500/20 p-1">
                <div class="size-1.5 rounded-full bg-emerald-500" />
              </div>
              <p class="text-xs/5 text-gray-500">Online</p>
            </div>
          </div>
          <Menu as="div" class="relative flex-none">
            <MenuButton class="-m-2.5 block p-2.5 text-gray-500 hover:text-gray-900">
              <span class="sr-only">Open options</span>
              <EllipsisVerticalIcon class="size-5" aria-hidden="true" />
            </MenuButton>
            <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
              <MenuItems class="absolute right-0 z-10 mt-2 w-32 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-hidden">
                <MenuItem v-slot="{ active }">
                  <Link :href="route('user.show',person.id)" :class="[active ? 'bg-gray-50 outline-hidden' : '', 'block px-3 py-1 text-sm/6 text-gray-900']">
                    Ver Perfil
                    <span class="sr-only">, {{ person.name }}</span></Link 
                  >
                </MenuItem>
                <MenuItem v-slot="{ active }">
                  <Link :href="route('atribuir.cargo', person.id)"
                    :class="[active ? 'bg-gray-50' : '', 'block px-3 py-1 text-sm/6 text-gray-900']">
                        Atribuir Cargo
                  <span class="sr-only">, {{ person.name }}</span>
                  </Link>
                </MenuItem>
                <MenuItem v-slot="{ active }">
                  <Link :href="route('desatribuir.cargo', person.id)"
                    :class="[active ? 'bg-gray-50' : '', 'block px-3 py-1 text-sm/6 text-gray-900']">
                        Tirar Cargo
                  <span class="sr-only">, {{ person.name }}</span>
                  </Link>
                </MenuItem>
              </MenuItems>
            </transition>
          </Menu>
        </div>
      </li>
    </ul>
  
</template>
  
  <script setup>
    import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
    import { EllipsisVerticalIcon } from '@heroicons/vue/20/solid'
    import { Link } from '@inertiajs/vue3'
  defineProps({
    users:Object
  })
  
  </script>