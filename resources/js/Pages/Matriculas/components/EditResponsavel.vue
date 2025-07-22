<template>
  <div class="space-y-6">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Responsável(is) do Aluno</h2>
    
    <!-- Lista de responsáveis vinculados -->
    <GuardianList 
      :guardians="guardians" 
      :loading="loading"
      @update-guardian-type="updateGuardianType"
      @remove="confirmRemove"
    />

    <!-- Botão para adicionar responsável -->
    <button 
      @click="showAddModal = true"
      class="w-full bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition flex items-center justify-center space-x-2"
      :disabled="loading"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
      </svg>
      <span>Adicionar Responsável</span>
    </button>

    <!-- Modal para adicionar responsável -->
    <AddGuardianModal
      v-if="showAddModal"
      :student="enrollment.student"
      :loading="loading"
      @close="closeAddModal"
      @add="addGuardian"
    />

    <!-- Modal de confirmação para remoção -->
    <RemoveConfirmationModal
      v-if="showRemoveModal"
      :guardian="guardianToRemove"
      :loading="loading"
      @close="showRemoveModal = false"
      @confirm="removeGuardian"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import GuardianList from './Guardian/GuardianList.vue'
import AddGuardianModal from './Guardian/AddGuardianModal.vue'
import RemoveConfirmationModal from './Guardian/RemoveConfirmationModal.vue'

const props = defineProps({ 
  guardians: Array,
  enrollment: Object
})

// Estados reativos
const loading = ref(false)
const showAddModal = ref(false)
const showRemoveModal = ref(false)
const guardianToRemove = ref(null)

// Confirmar remoção
const confirmRemove = (guardian) => {
  guardianToRemove.value = guardian
  showRemoveModal.value = true
}

// Remover responsável - usando Inertia
const removeGuardian = () => {
  if (!guardianToRemove.value) return
  
  loading.value = true
  
  router.delete(route('students.guardians.detach', [props.enrollment.student.id, guardianToRemove.value.id]), {
    onSuccess: () => {
      showRemoveModal.value = false
      guardianToRemove.value = null
    },
    onError: (errors) => {
      console.error('Erro ao remover responsável:', errors)
    },
    onFinish: () => {
      loading.value = false
    }
  })
}

// Atualizar tipo de responsável - usando Inertia
const updateGuardianType = (guardianId, guardianType) => {
  router.patch(route('guardian.update', guardianId), {
    guardian_type: guardianType
  }, {
    preserveState: true,
    preserveScroll: true,
    onError: (errors) => {
      console.error('Erro ao atualizar tipo de responsável:', errors)
    }
  })
}

// Fechar modal de adição
const closeAddModal = () => {
  showAddModal.value = false
}

// Adicionar responsável
const addGuardian = (data) => {
  loading.value = true
  
  if (data.mode === 'search') {
    // Modo: vincular responsável existente
    router.post(route('students.guardians.attach', props.enrollment.student.id), {
      guardian_id: data.guardian_id
    }, {
      onSuccess: () => {
        closeAddModal()
      },
      onError: (errors) => {
        console.error('Erro ao adicionar responsável:', errors)
      },
      onFinish: () => {
        loading.value = false
      }
    })
  } else {
    // Modo: criar novo responsável e vincular
    router.post(route('guardian.store'), data.guardianData, {
      onSuccess: (page) => {
        const createdGuardian = page.props.flash?.guardian
        if (createdGuardian) {
          router.post(route('students.guardians.attach', props.enrollment.student.id), {
            guardian_id: createdGuardian.id
          }, {
            preserveState: false,
            onSuccess: () => {
              closeAddModal()
            },
            onError: (errors) => {
              console.error('Erro ao vincular responsável criado:', errors)
            },
            onFinish: () => {
              loading.value = false
            }
          })
        } else {
          closeAddModal()
          loading.value = false
        }
      },
      onError: (errors) => {
        console.error('Erro ao criar responsável:', errors)
        loading.value = false
      }
    })
  }
}
</script> 