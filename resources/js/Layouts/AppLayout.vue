<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import HeaderMenu from '@/Shared/HeaderMenu.vue';
import FlashMessenger from '@/Shared/FlashMessenger.vue';
import BradCrumbs from '@/Shared/BradCrumbs.vue';
defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};
</script>

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
            <BradCrumbs/>
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