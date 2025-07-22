<template>
  <div class="p-6 max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Nova Matrícula</h1>
    <FormEnrollment :students="students" :guardians="guardians" :classrooms="classrooms" @submitted="handleSubmitted" />
  </div>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';
import FormEnrollment from './components/FormEnrollment.vue';

const props = defineProps({
  students: { type: Array, default: () => [] },
  guardians: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
});

function handleSubmitted() {
  router.visit(route('matriculas.index'));
}
</script> 