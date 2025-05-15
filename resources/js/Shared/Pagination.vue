<template>
    <nav class="flex items-center justify-between border-t border-gray-200 px-4 sm:px-0 mt-10" aria-label="Pagination">
      <!-- Botão "Previous" -->
      <div class="-mt-px flex w-0 flex-1">
      </div>
  
      <!-- Links das Páginas -->
      <div class="hidden md:-mt-px md:flex">
        <template v-for="(link, index) in links" :key="index">
          <!-- Página ativa -->
          <a
            v-if="link.active"
            href="#"
            class="inline-flex items-center border-t-2 border-indigo-500 px-4 pt-4 text-sm font-medium text-indigo-600"
            aria-current="page"
            v-html="link.label"
          ></a>
          <!-- Páginas normais -->
          <a
            v-else-if="link.url"
            :href="link.url"
            class="inline-flex items-center border-t-2 border-transparent px-4 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
            v-html="link.label"
          ></a>
          <!-- Páginas inativas (e.g., "...") -->
          <span
            v-else
            class="inline-flex items-center border-t-2 border-transparent px-4 pt-4 text-sm font-medium text-gray-500 cursor-not-allowed"
            v-html="link.label"
          ></span>
        </template>
      </div>
  
      <!-- Botão "Next" -->
      <div class="-mt-px flex w-0 flex-1 justify-end">
      </div>
    </nav>
  </template>
  
  <script setup>
  import { ArrowLongLeftIcon, ArrowLongRightIcon } from '@heroicons/vue/24/solid'
  
  const props = defineProps({
    links: {
      type: Object,
      required: true,
    },
  });
  </script>