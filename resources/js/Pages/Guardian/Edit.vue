<template>
  <div class="max-w-6xl mx-auto py-10">
    <FlashMessenger />
    
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Editar Responsável</h1>
          <p class="text-sm text-gray-600 mt-1">
            {{ guardian.name }} • ID: {{ guardian.id }} • Cadastrado em: {{ formatDate(guardian.created_at) }}
          </p>
        </div>
        <div class="flex space-x-3">
          <button 
            @click="goBack"
            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
          >
            <ArrowLeftIcon class="w-4 h-4 mr-2" />
            Voltar
          </button>
        </div>
      </div>
    </div>

    <form @submit.prevent="updateGuardian" class="space-y-6">
      <!-- Dados Pessoais -->
      <PersonalDataSection 
        v-model="personalData" 
        :errors="errors"
      />

      <!-- Endereços -->
      <AddressesSection 
        v-model="form.addresses" 
        v-model:principalIndex="principalAddressIndex"
      />

      <!-- Contatos -->
      <ContactsSection 
        v-model="form.contacts" 
        v-model:principalIndex="principalContactIndex"
      />

      <!-- Responsabilidade -->
      <ResponsibilitySection 
        v-model="responsibilityData" 
        :errors="errors"
      />

      <!-- Dados Profissionais -->
      <ProfessionalDataSection 
        v-model="professionalData" 
        :errors="errors"
      />

      <!-- Observações -->
      <NotesSection 
        v-model="notesData" 
        :errors="errors"
      />

      <!-- Botões -->
      <div class="flex items-center justify-end pt-6 border-t border-gray-200 bg-white rounded-lg p-6 shadow-sm">
        <div class="flex space-x-3">
          <button 
            type="button"
            @click="goBack"
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            :disabled="processing"
          >
            Cancelar
          </button>
          <button 
            type="submit"
            :disabled="processing"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <span v-if="processing">Salvando...</span>
            <span v-else>Salvar Alterações</span>
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/20/solid'
import FlashMessenger from '@/Shared/FlashMessenger.vue'
import PersonalDataSection from './Components/PersonalDataSection.vue'
import AddressesSection from './Components/AddressesSection.vue'
import ContactsSection from './Components/ContactsSection.vue'
import ResponsibilitySection from './Components/ResponsibilitySection.vue'
import ProfessionalDataSection from './Components/ProfessionalDataSection.vue'
import NotesSection from './Components/NotesSection.vue'

const props = defineProps({
  guardian: Object
})

const processing = ref(false)
const errors = ref({})

const principalContactIndex = ref(0)
const principalAddressIndex = ref(0)

// Dados divididos em seções reativas
const personalData = reactive({
  name: props.guardian.name || '',
  cpf: props.guardian.cpf || '',
  rg: props.guardian.rg || '',
  birth_date: props.guardian.birth_date || '',
  gender: props.guardian.gender || '',
  marital_status: props.guardian.marital_status || ''
})

const responsibilityData = reactive({
  guardian_type: props.guardian.guardian_type || 'titular',
  relationship: props.guardian.relationship || ''
})

const professionalData = reactive({
  occupation: props.guardian.occupation || '',
  workplace: props.guardian.workplace || ''
})

const notesData = reactive({
  notes: props.guardian.notes || ''
})

const form = reactive({
  contacts: props.guardian.contacts?.length ? 
    props.guardian.contacts.map((contact, idx) => {
      if (contact.is_primary) principalContactIndex.value = idx
      return {
        type: contact.type,
        value: contact.value,
        label: contact.label || 'pessoal',
        is_primary: contact.is_primary,
        contact_for: contact.contact_for || contact.label || 'pessoal'
      }
    }) : 
    [{ type: 'phone', value: '', label: 'pessoal', is_primary: true, contact_for: 'pessoal' }],
  addresses: props.guardian.addresses?.length ? 
    props.guardian.addresses.map((address, idx) => {
      if (address.is_primary) principalAddressIndex.value = idx
      return {
        zip_code: address.zip_code || '',
        street: address.street || '',
        number: address.number || '',
        complement: address.complement || '',
        neighborhood: address.neighborhood || '',
        city: address.city || '',
        state: address.state || '',
        address_for: address.address_for || 'casa',
        is_primary: address.is_primary,
        loading: false,
        error: ''
      }
    }) : 
    [{ zip_code: '', street: '', number: '', complement: '', neighborhood: '', city: '', state: '', address_for: 'casa', is_primary: true, loading: false, error: '' }]
})

const updateGuardian = () => {
  processing.value = true
  errors.value = {}

  // Preparar dados para envio
  const addresses = form.addresses.map((addr, idx) => ({
    zip_code: addr.zip_code,
    street: addr.street,
    number: addr.number,
    complement: addr.complement,
    neighborhood: addr.neighborhood,
    city: addr.city,
    state: addr.state,
    address_for: addr.address_for,
    is_primary: principalAddressIndex.value === idx,
  }))
  
  const contacts = form.contacts.map((contact, idx) => ({
    type: contact.type,
    value: contact.value,
    label: contact.label,
    is_primary: principalContactIndex.value === idx,
    contact_for: contact.label,
  }))

  const payload = {
    ...personalData,
    ...responsibilityData,
    ...professionalData,
    ...notesData,
    status: props.guardian.status || 'active',
    addresses,
    contacts,
  }

  router.patch(route('guardian.update', props.guardian.id), payload, {
    preserveScroll: true,
    onSuccess: () => {
      // Success message será mostrada via flash
    },
    onError: (responseErrors) => {
      errors.value = responseErrors
    },
    onFinish: () => {
      processing.value = false
    }
  })
}

const goBack = () => {
  window.history.back()
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('pt-BR')
}
</script> 