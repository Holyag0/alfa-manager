<template>
    <Head title="Atribuir-Cargo" />
  <form>
    <div class="space-y-12 ">
      <div class="border-b border-gray-900/10 pb-12 ">
        <h2 class="text-base/7 font-semibold text-gray-900">Personal Information</h2>
        <p class="mt-1 text-sm/6 text-gray-600">Use a permanent address where you can receive mail.</p>

        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 bg-white p-4 rounded-lg">
          <div class="sm:col-span-3">
            <label for="country" class="block text-sm/6 font-medium text-gray-900">Country</label>
            <div class="mt-2 grid grid-cols-1">
              <select id="country" name="country" autocomplete="country-name" class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-1.5 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                <option>United States</option>
                <option>Canada</option>
                <option>Mexico</option>
              </select>
            </div>
          </div>
          <div class="sm:col-span-3">
            <label for="country" class="block text-sm/6 font-medium text-gray-900">Country</label>
            <div class="mt-2 grid grid-cols-1">
              <select id="country" name="country" autocomplete="country-name" class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-1.5 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                <option>United States</option>
                <option>Canada</option>
                <option>Mexico</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="border-b border-gray-900/10 pb-12">
        <h2 class="text-base/7 font-semibold text-gray-900">Notifications</h2>
        <p class="mt-1 text-sm/6 text-gray-600">We'll always let you know about important changes, but you pick what else you want to hear about.</p>

        <div class="mt-10 space-y-10">
         
        </div>
      </div>
    </div>

    <div class="mt-6 flex items-center justify-end gap-x-6">
      <button type="button" class="text-sm/6 font-semibold text-gray-900">Cancel</button>
      <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
    </div>
  </form>
</template>

<script setup>
import { Link,Head } from '@inertiajs/vue3';
</script>