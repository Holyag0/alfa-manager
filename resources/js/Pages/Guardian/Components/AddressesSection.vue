<template>
  <div class="bg-white rounded-xl p-6 border border-blue-200 shadow-sm">
    <h4 class="flex items-center text-lg font-semibold text-gray-800 mb-6">
      <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
      </div>
      Endereços
    </h4>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <template v-for="(address, idx) in modelValue" :key="idx">
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 transition-all duration-200 hover:border-blue-300 hover:bg-blue-50/50">
          <!-- Header do card com tipo e ações -->
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
              <select 
                :value="address.address_for" 
                @change="updateAddress(idx, 'address_for', $event.target.value)" 
                class="px-6 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white text-sm font-medium"
              >
                <option value="casa">🏠 Casa</option>
                <option value="trabalho">🏢 Trabalho</option>
                <option value="outro">🏷️ Outro</option>
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
              @click="removeAddress(idx)" 
              class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" 
              title="Remover endereço"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>

          <!-- CEP com busca automática -->
          <div class="mb-4">
            <div class="flex gap-2">
              <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                <input 
                  :value="address.zip_code"
                  @input="updateAddress(idx, 'zip_code', $event.target.value)"
                  @blur="buscarCep(idx)" 
                  maxlength="9" 
                  placeholder="00000-000" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white text-sm" 
                />
              </div>
              <div class="flex items-end">
                <button 
                  type="button" 
                  @click="buscarCep(idx)" 
                  :disabled="address.loading" 
                  class="h-10 px-3 border border-gray-300 rounded-lg bg-white hover:border-blue-400 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="!address.loading" class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                  </svg>
                  <svg v-else class="animate-spin w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                  </svg>
                </button>
              </div>
            </div>
            <div v-if="address.error" class="text-xs text-red-500 mt-1">{{ address.error }}</div>
          </div>

          <!-- Endereço completo -->
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro</label>
              <input 
                :value="address.street" 
                @input="updateAddress(idx, 'street', $event.target.value)"
                placeholder="Rua, Avenida, etc." 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white text-sm" 
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
              <input 
                :value="address.complement" 
                @input="updateAddress(idx, 'complement', $event.target.value)"
                placeholder="Apto, bloco, casa, etc." 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white text-sm" 
              />
            </div>
            <div class="flex gap-3">
              <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                <input 
                  :value="address.neighborhood" 
                  @input="updateAddress(idx, 'neighborhood', $event.target.value)"
                  placeholder="Bairro" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white text-sm" 
                />
              </div>
              <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                <input 
                  :value="address.city" 
                  @input="updateAddress(idx, 'city', $event.target.value)"
                  placeholder="Cidade" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white text-sm" 
                />
              </div>
            </div>
            <div class="flex gap-3">
              <div class="w-24">
                <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                <input 
                  :value="address.number" 
                  @input="updateAddress(idx, 'number', $event.target.value)"
                  placeholder="Nº" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white text-sm" 
                />
              </div>
              <div class="w-20">
                <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                <input 
                  :value="address.state" 
                  @input="updateAddress(idx, 'state', $event.target.value)"
                  maxlength="2" 
                  placeholder="UF" 
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white text-sm" 
                />
              </div>
            </div>
          </div>
        </div>
      </template>
      
      <!-- Card de adicionar endereço -->
      <div @click="addAddress" class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl min-h-[280px] cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition group">
        <PlusIcon class="w-8 h-8 text-gray-400 group-hover:text-blue-600 mb-3" />
        <span class="text-base font-medium text-gray-500 group-hover:text-blue-600">Adicionar endereço</span>
        <span class="text-sm text-gray-400 group-hover:text-blue-500 mt-1">Clique para incluir outro endereço</span>
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

const updateAddress = (index, field, value) => {
  const newAddresses = [...props.modelValue]
  newAddresses[index] = {
    ...newAddresses[index],
    [field]: value
  }
  emit('update:modelValue', newAddresses)
}

const setPrincipal = (index) => {
  emit('update:principalIndex', index)
}

const addAddress = () => {
  const newAddresses = [...props.modelValue, {
    zip_code: '', 
    street: '', 
    number: '', 
    complement: '', 
    neighborhood: '', 
    city: '', 
    state: '', 
    address_for: 'casa', 
    is_primary: false, 
    loading: false, 
    error: '' 
  }]
  emit('update:modelValue', newAddresses)
}

const removeAddress = (index) => {
  if (props.modelValue.length === 1) return
  
  const newAddresses = [...props.modelValue]
  newAddresses.splice(index, 1)
  emit('update:modelValue', newAddresses)
  
  // Ajustar índice principal se necessário
  if (props.principalIndex === index) {
    emit('update:principalIndex', 0)
  } else if (props.principalIndex > index) {
    emit('update:principalIndex', props.principalIndex - 1)
  }
}

const buscarCep = async (idx) => {
  const addr = props.modelValue[idx]
  if (!addr.zip_code || addr.zip_code.length < 8) return
  
  // Atualizar estado de loading
  updateAddress(idx, 'loading', true)
  updateAddress(idx, 'error', '')
  
  try {
    const res = await fetch(`https://viacep.com.br/ws/${addr.zip_code.replace(/\D/g, '')}/json/`)
    const data = await res.json()
    
    if (data.erro) throw new Error('CEP não encontrado')
    
    // Atualizar campos do endereço
    const newAddresses = [...props.modelValue]
    newAddresses[idx] = {
      ...newAddresses[idx],
      street: data.logradouro || '',
      neighborhood: data.bairro || '',
      city: data.localidade || '',
      state: data.uf || '',
      loading: false,
      error: ''
    }
    emit('update:modelValue', newAddresses)
  } catch (e) {
    updateAddress(idx, 'error', 'CEP não encontrado')
    updateAddress(idx, 'street', '')
    updateAddress(idx, 'neighborhood', '')
    updateAddress(idx, 'city', '')
    updateAddress(idx, 'state', '')
    updateAddress(idx, 'loading', false)
  }
}
</script> 