<template>
  <div class="bg-white rounded-xl p-6 border border-blue-200 shadow-sm">
    <h4 class="flex items-center text-lg font-semibold text-gray-800 mb-6">
      <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
      </div>
      Informações de Contato
    </h4>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <template v-for="(contact, idx) in modelValue" :key="idx">
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 transition-all duration-200 hover:border-blue-300 hover:bg-blue-50/50">
          <!-- Header do card -->
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
              <select 
                :value="contact.type" 
                @change="updateContact(idx, 'type', $event.target.value)" 
                class="px-6 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white text-sm font-medium"
              >
                <option value="phone">📱 Telefone</option>
                <option value="email">✉️ E-mail</option>
              </select>
              <button 
                type="button" 
                @click="setPrincipal(idx)" 
                :class="['px-3 py-1 rounded-full text-xs font-medium transition-colors', principalIndex === idx ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-600 hover:bg-blue-100']"
                title="Marcar como principal"
              >
                {{ principalIndex === idx ? '✅ Principal' : ' Principal' }}
              </button>
            </div>
            <button 
              v-if="modelValue.length > 1" 
              type="button" 
              @click="removeContact(idx)" 
              class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" 
              title="Remover contato"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>

          <!-- Campos do contato -->
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ contact.type === 'email' ? 'E-mail' : 'Telefone' }}
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg v-if="contact.type === 'phone'" class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                  </svg>
                  <svg v-else class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </div>
                <input 
                  :value="contact.value" 
                  @input="updateContact(idx, 'value', $event.target.value)"
                  :type="contact.type === 'email' ? 'email' : 'text'" 
                  :placeholder="contact.type === 'phone' ? '(00) 00000-0000' : 'email@exemplo.com'" 
                  class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white" 
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
              <select 
                :value="contact.label" 
                @change="updateContact(idx, 'label', $event.target.value)" 
                class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white"
              >
                <option value="pessoal">📱 WhatsApp/Pessoal</option>
                <option value="fixo">📞 Fixo</option>
                <option value="trabalho">💼 Trabalho</option>
                <option value="emergencia">🚨 Emergência</option>
              </select>
            </div>
          </div>
        </div>
      </template>
      
      <!-- Card de adicionar contato -->
      <div @click="addContact" class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl min-h-[200px] cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition group">
        <PlusIcon class="w-8 h-8 text-gray-400 group-hover:text-blue-600 mb-3" />
        <span class="text-base font-medium text-gray-500 group-hover:text-blue-600">Adicionar contato</span>
        <span class="text-sm text-gray-400 group-hover:text-blue-500 mt-1">Telefone ou e-mail</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { TrashIcon, PlusIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  modelValue: {
    type: Array,
    required: true
  },
  principalIndex: {
    type: Number,
    default: 0
  }
})

const emit = defineEmits(['update:modelValue', 'update:principalIndex'])

const updateContact = (index, field, value) => {
  const newContacts = [...props.modelValue]
  newContacts[index] = {
    ...newContacts[index],
    [field]: value
  }
  
  // Atualizar contact_for quando label muda
  if (field === 'label') {
    newContacts[index].contact_for = value
  }
  
  emit('update:modelValue', newContacts)
}

const setPrincipal = (index) => {
  emit('update:principalIndex', index)
}

const addContact = () => {
  const newContacts = [...props.modelValue, {
    type: 'phone', 
    value: '', 
    label: 'pessoal', 
    is_primary: false, 
    contact_for: 'pessoal' 
  }]
  emit('update:modelValue', newContacts)
}

const removeContact = (index) => {
  if (props.modelValue.length === 1) return
  
  const newContacts = [...props.modelValue]
  newContacts.splice(index, 1)
  emit('update:modelValue', newContacts)
  
  // Ajustar índice principal se necessário
  if (props.principalIndex === index) {
    emit('update:principalIndex', 0)
  } else if (props.principalIndex > index) {
    emit('update:principalIndex', props.principalIndex - 1)
  }
}
</script> 