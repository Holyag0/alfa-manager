<template>
  <div class="space-y-4">
    <label class="block text-sm font-medium text-gray-700">
      Foto do Colaborador
    </label>
    
    <!-- Preview da Foto -->
    <div v-if="previewUrl || currentPhoto" class="mb-4">
      <div class="relative inline-block">
        <img
          :src="previewUrl || currentPhotoUrl"
          alt="Preview da foto"
          class="h-32 w-32 rounded-full object-cover border-4 border-gray-200"
        />
        <button
          v-if="previewUrl || currentPhoto"
          @click="removePhoto"
          type="button"
          class="absolute top-0 right-0 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Input File -->
    <div>
      <input
        ref="fileInput"
        type="file"
        accept="image/jpeg,image/png,image/jpg,image/gif"
        @change="handleFileChange"
        class="hidden"
      />
      <button
        type="button"
        @click="$refs.fileInput.click()"
        class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        {{ previewUrl || currentPhoto ? 'Alterar Foto' : 'Selecionar Foto' }}
      </button>
      <p class="mt-2 text-sm text-gray-500">
        Formatos aceitos: JPEG, PNG, JPG, GIF. Tamanho máximo: 2MB
      </p>
      <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'

const props = defineProps({
  currentPhoto: String,
  modelValue: File
})

const emit = defineEmits(['update:modelValue'])

const fileInput = ref(null)
const previewUrl = ref(null)
const error = ref(null)

const currentPhotoUrl = computed(() => {
  if (props.currentPhoto) {
    return props.currentPhoto.startsWith('http') 
      ? props.currentPhoto 
      : `/storage/${props.currentPhoto}`
  }
  return null
})

const handleFileChange = (event) => {
  const file = event.target.files[0]
  error.value = null

  if (!file) {
    previewUrl.value = null
    emit('update:modelValue', null)
    return
  }

  // Validar tipo
  const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']
  if (!validTypes.includes(file.type)) {
    error.value = 'Formato de arquivo inválido. Use JPEG, PNG, JPG ou GIF.'
    return
  }

  // Validar tamanho (2MB)
  if (file.size > 2 * 1024 * 1024) {
    error.value = 'Arquivo muito grande. Tamanho máximo: 2MB.'
    return
  }

  // Criar preview
  const reader = new FileReader()
  reader.onload = (e) => {
    previewUrl.value = e.target.result
  }
  reader.readAsDataURL(file)

  emit('update:modelValue', file)
}

const removePhoto = () => {
  previewUrl.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
  emit('update:modelValue', null)
}

watch(() => props.modelValue, (newValue) => {
  if (!newValue && !props.currentPhoto) {
    previewUrl.value = null
  }
})
</script>

