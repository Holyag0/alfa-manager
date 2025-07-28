<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg max-w-6xl w-full p-6 mx-4 max-h-[95vh] overflow-y-auto">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">Adicionar Responsável</h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <XMarkIcon class="w-6 h-6" />
        </button>
      </div>

      <!-- Usa o componente ResponsavelStep que já tem toda a funcionalidade -->
      <ResponsavelStep 
        :student="student"
        @next="handleGuardianSelected"
      />
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { XMarkIcon } from '@heroicons/vue/20/solid'
import ResponsavelStep from '@/Pages/Matriculas/components/ResponsavelStep.vue'

const props = defineProps({
  student: Object
})

const emit = defineEmits(['close', 'added'])

const handleGuardianSelected = (guardian) => {
  // Se é um responsável existente selecionado
  if (guardian.id) {
    // Vincular responsável existente ao aluno
    router.post(route('students.guardians.attach', props.student.id), {
      guardian_id: guardian.id
    }, {
      onSuccess: () => {
        emit('added')
      },
      onError: (errors) => {
        console.error('Erro ao vincular responsável:', errors)
      }
    })
  } else {
    // Se foi criado um novo responsável, ele já vem com ID do flash success
    // O ResponsavelStep já cria e retorna o responsável criado
    emit('added')
  }
}
</script> 