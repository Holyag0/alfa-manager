<template>
  <div class="p-6 max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Editar Matrícula</h1>
    <FormEnrollment
      :students="students"
      :guardians="guardians"
      :classrooms="classrooms"
      :initial-enrollment="enrollment"
      @submitted="handleSubmitted"
      :is-edit="true"
    />
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import FormEnrollment from './components/FormEnrollment.vue';

const props = defineProps({
  enrollment: { type: Object, required: true },
  students: { type: Array, default: () => [] },
  guardians: { type: Array, default: () => [] },
  classrooms: { type: Array, default: () => [] },
});

function handleSubmitted() {
  router.visit(route('matriculas.index'));
}
</script> 