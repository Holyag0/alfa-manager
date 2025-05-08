<template>
    <nav class="flex border-gray-200" aria-label="Breadcrumb">
      <ol role="list" class="flex w-full max-w-screen-xl px-4 mx-auto space-x-4 sm:px-6 lg:px-8">
        <li class="flex">
          <div class="flex items-center">
            <Link :href="route('dashboard')" class="text-gray-100 hover:text-sky-800">
              <HomeIcon class="flex-shrink-0 w-5 h-5" aria-hidden="true" />
              <span class="sr-only">Home</span>
            </Link>
          </div>
        </li>
        <li v-for="page in pages" :key="page.name" class="flex">
          <div class="flex items-center">
            <ChevronRightIcon class="size-5 shrink-0 text-gray-100" aria-hidden="true" />
            <div v-if="page.href == null">
              <span class="ml-4 text-sm font-medium text-gray-100 hover:sky-800" :aria-current="page.current ? 'page' : undefined">
                {{ page.name }}
              </span>
            </div>
            <div v-else>
              <Link :href="page.href" class="ml-4 text-sm font-medium text-gray-100 hover:text-sky-800" :aria-current="page.current ? 'page' : undefined">
                {{ page.name }}
              </Link>
            </div>
          </div>
        </li>
      </ol>
    </nav>
  </template>
  
  <script setup>
  import { computed } from 'vue'
  import { usePage, Link } from '@inertiajs/vue3'
  import { HomeIcon,ChevronRightIcon } from '@heroicons/vue/20/solid'
  
  // Função que transforma "admin-users" em "Admin Users"
  const humanize = (str) => {
    return str
      .split(/[-_]/)
      .map(s => s.charAt(0).toUpperCase() + s.slice(1))
      .join(' ')
  }
  
  const page = usePage()
  
  const pages = computed(() => {
    const currentRoute = page.url.split('?')[0]
    const segments = currentRoute.split('/').filter(Boolean)
  
    let path = ''
    return segments.map((segment, index) => {
      path += `/${segment}`
      return {
        name: humanize(segment),
        href: path,
        current: index === segments.length - 1
      }
    })
  })
  </script>
  