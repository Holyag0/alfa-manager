<template>
    <a  @click="open=true" href="#" class="text-gray-400 hover:bg-gray-800 hover:text-white group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold">
        Cadastro
    </a>
    <TransitionRoot as="template" :show="open">
        <Dialog class="relative z-50" @close="open = false">
            <div class="fixed inset-0" />

            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
                        <TransitionChild as="template"
                            enter="transform transition ease-in-out duration-500 sm:duration-700"
                            enter-from="translate-x-full" enter-to="translate-x-0"
                            leave="transform transition ease-in-out duration-500 sm:duration-700"
                            leave-from="translate-x-0" leave-to="translate-x-full">
                            <DialogPanel class="pointer-events-auto w-screen max-w-2xl">
                                <form class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                                    <div class="flex-1">
                                        <!-- Header -->
                                        <div class="bg-gray-50 px-4 py-6 sm:px-6">
                                            <div class="flex items-start justify-between space-x-3">
                                                <div class="space-y-1">
                                                    <DialogTitle class="text-base font-semibold text-gray-900">New
                                                        project</DialogTitle>
                                                    <p class="text-sm text-gray-500">Get started by filling in the
                                                        information below to create your new project.</p>
                                                </div>
                                                <div class="flex h-7 items-center">
                                                    <button type="button"
                                                        class="relative text-gray-400 hover:text-gray-500"
                                                        @click="open = false">
                                                        <span class="absolute -inset-2.5" />
                                                        <span class="sr-only">Close panel</span>
                                                        <XMarkIcon class="size-6" aria-hidden="true" />
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Divider container -->
                                        <div class="space-y-6 py-6 sm:space-y-0 sm:divide-y sm:divide-gray-200 sm:py-0">
                                            <!-- Project name -->
                                            <div
                                                class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                                                <div>
                                                    <label for="project-name"
                                                        class="block text-sm/6 font-medium text-gray-900 sm:mt-1.5">Project
                                                        name</label>
                                                </div>
                                                <div class="sm:col-span-2">
                                                    <input type="text" name="project-name" id="project-name"
                                                        class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                                                </div>
                                            </div>

                                            <!-- Project description -->
                                            <div
                                                class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                                                <div>
                                                    <label for="project-description"
                                                        class="block text-sm/6 font-medium text-gray-900 sm:mt-1.5">Description</label>
                                                </div>
                                                <div class="sm:col-span-2">
                                                    <textarea rows="3" name="project-description"
                                                        id="project-description"
                                                        class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                                                </div>
                                            </div>

                                            <!-- Team members -->
                                            <div
                                                class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:items-center sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                                                <div>
                                                    <h3 class="text-sm/6 font-medium text-gray-900">Team Members</h3>
                                                </div>
                                                <div class="sm:col-span-2">
                                                    <div class="flex space-x-2">
                                                        <a v-for="person in team" :key="person.email"
                                                            :href="person.href"
                                                            class="shrink-0 rounded-full hover:opacity-75">
                                                            <img class="inline-block size-8 rounded-full"
                                                                :src="person.imageUrl" :alt="person.name" />
                                                        </a>

                                                        <button type="button"
                                                            class="relative inline-flex size-8 shrink-0 items-center justify-center rounded-full border-2 border-dashed border-gray-200 bg-white text-gray-400 hover:border-gray-300 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                                            <span class="absolute -inset-2" />
                                                            <span class="sr-only">Add team member</span>
                                                            <PlusIcon class="size-5" aria-hidden="true" />
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Privacy -->
                                            <fieldset
                                                class="space-y-2 px-4 sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:space-y-0 sm:px-6 sm:py-5">
                                                <legend class="sr-only">Privacy</legend>
                                                <div class="text-sm/6 font-medium text-gray-900" aria-hidden="true">
                                                    Privacy</div>
                                                <div class="space-y-5 sm:col-span-2">
                                                    <div class="space-y-5 sm:mt-0">
                                                        <div class="relative flex items-start">
                                                            <div class="absolute flex h-6 items-center">
                                                                <input id="privacy-public" name="privacy" value="public"
                                                                    aria-describedby="privacy-public-description"
                                                                    type="radio" checked=""
                                                                    class="relative size-4 appearance-none rounded-full border border-gray-300 before:absolute before:inset-1 before:rounded-full before:bg-white checked:border-indigo-600 checked:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:before:bg-gray-400 forced-colors:appearance-auto forced-colors:before:hidden [&:not(:checked)]:before:hidden" />
                                                            </div>
                                                            <div class="pl-7 text-sm/6">
                                                                <label for="privacy-public"
                                                                    class="font-medium text-gray-900">Public
                                                                    access</label>
                                                                <p id="privacy-public-description"
                                                                    class="text-gray-500">Everyone with the link will
                                                                    see this project.</p>
                                                            </div>
                                                        </div>
                                                        <div class="relative flex items-start">
                                                            <div class="absolute flex h-6 items-center">
                                                                <input id="privacy-private-to-project" name="privacy"
                                                                    value="private-to-project"
                                                                    aria-describedby="privacy-private-to-project-description"
                                                                    type="radio"
                                                                    class="relative size-4 appearance-none rounded-full border border-gray-300 before:absolute before:inset-1 before:rounded-full before:bg-white checked:border-indigo-600 checked:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:before:bg-gray-400 forced-colors:appearance-auto forced-colors:before:hidden [&:not(:checked)]:before:hidden" />
                                                            </div>
                                                            <div class="pl-7 text-sm/6">
                                                                <label for="privacy-private-to-project"
                                                                    class="font-medium text-gray-900">Private to project
                                                                    members</label>
                                                                <p id="privacy-private-to-project-description"
                                                                    class="text-gray-500">Only members of this project
                                                                    would be able to access.</p>
                                                            </div>
                                                        </div>
                                                        <div class="relative flex items-start">
                                                            <div class="absolute flex h-6 items-center">
                                                                <input id="privacy-private" name="privacy"
                                                                    value="private"
                                                                    aria-describedby="privacy-private-to-project-description"
                                                                    type="radio"
                                                                    class="relative size-4 appearance-none rounded-full border border-gray-300 before:absolute before:inset-1 before:rounded-full before:bg-white checked:border-indigo-600 checked:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:before:bg-gray-400 forced-colors:appearance-auto forced-colors:before:hidden [&:not(:checked)]:before:hidden" />
                                                            </div>
                                                            <div class="pl-7 text-sm/6">
                                                                <label for="privacy-private"
                                                                    class="font-medium text-gray-900">Private to
                                                                    you</label>
                                                                <p id="privacy-private-description"
                                                                    class="text-gray-500">You are the only one able to
                                                                    access this project.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr class="border-gray-200" />
                                                    <div
                                                        class="flex flex-col items-start space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                                                        <div>
                                                            <a href="#"
                                                                class="group flex items-center space-x-2.5 text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                                                <LinkIcon
                                                                    class="size-5 text-indigo-500 group-hover:text-indigo-900"
                                                                    aria-hidden="true" />
                                                                <span>Copy link</span>
                                                            </a>
                                                        </div>
                                                        <div>
                                                            <a href="#"
                                                                class="group flex items-center space-x-2.5 text-sm text-gray-500 hover:text-gray-900">
                                                                <QuestionMarkCircleIcon
                                                                    class="size-5 text-gray-400 group-hover:text-gray-500"
                                                                    aria-hidden="true" />
                                                                <span>Learn more about sharing</span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>

                                    <!-- Action buttons -->
                                    <div class="shrink-0 border-t border-gray-200 px-4 py-5 sm:px-6">
                                        <div class="flex justify-end space-x-3">
                                            <button type="button"
                                                class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                                                @click="open = false">Cancel</button>
                                            <button type="submit"
                                                class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Create</button>
                                        </div>
                                    </div>
                                </form>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
  
  <script setup>
  import { ref } from 'vue'
  import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
  import { XMarkIcon } from '@heroicons/vue/24/outline'
  import { LinkIcon, PlusIcon, QuestionMarkCircleIcon } from '@heroicons/vue/20/solid'
  
  const team = [
    {
      name: 'Tom Cook',
      email: 'tom.cook@example.com',
      href: '#',
      imageUrl:
        'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
    },
    {
      name: 'Whitney Francis',
      email: 'whitney.francis@example.com',
      href: '#',
      imageUrl:
        'https://images.unsplash.com/photo-1517365830460-955ce3ccd263?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
    },
    {
      name: 'Leonard Krasner',
      email: 'leonard.krasner@example.com',
      href: '#',
      imageUrl:
        'https://images.unsplash.com/photo-1519345182560-3f2917c472ef?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
    },
    {
      name: 'Floyd Miles',
      email: 'floyd.miles@example.com',
      href: '#',
      imageUrl:
        'https://images.unsplash.com/photo-1463453091185-61582044d556?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
    },
    {
      name: 'Emily Selman',
      email: 'emily.selman@example.com',
      href: '#',
      imageUrl:
        'https://images.unsplash.com/photo-1502685104226-ee32379fefbe?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80',
    },
  ]
  
  const open = ref(false)
  </script>