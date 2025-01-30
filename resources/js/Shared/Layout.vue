<template>
    <div class="min-h-full">
      <div class="bg-gradient-to-t from-white via-emerald-500  to-emerald-500 pb-32">
        <nav as="nav" class="bg-gradient-to-b from-sky-500  to-emerald-500" >
          <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="border-b -mt-4 border-white">
              <div><HeaderMenu/></div>
            </div>
          </div>
        </nav>
        <header class="py-6">
          <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <FlashMessenger/>
            <h1 class="text-3xl font-bold tracking-tight text-white">Dashb</h1>
          </div>
        </header>
      </div>
      <main class="-mt-32">
        <div class="mx-auto max-w-7xl px-4 pb-12 sm:px-6 lg:px-8">
          <!-- <div class="rounded-lg bg-white px-5 py-6 shadow sm:px-6"> -->
            <slot/>
          <!-- </div> -->
        </div>
      </main>
    </div>
  </template>
  
  <script setup>
  import HeaderMenu from '../Shared/HeaderMenu.vue';
  import FlashMessenger from '../Shared/FlashMessenger.vue';
  </script>