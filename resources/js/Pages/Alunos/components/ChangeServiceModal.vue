<template>
  <!-- Modal de confirmação para troca em lote -->
  <ConfirmChangeServiceModal
    :show="showConfirmModal"
    :preview-data="previewData"
    :processing="processing"
    @confirm="executeChange"
    @cancel="cancelConfirm"
  />

  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[60]" @click="closeModal">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white" @click.stop>
      <!-- Modal header -->
      <div class="flex items-center justify-between pb-4 border-b border-gray-200">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">
            {{ changeAll ? 'Trocar Serviço de Todas as Mensalidades' : 'Trocar Serviço da Mensalidade' }}
          </h3>
          <p class="text-sm text-gray-600">
            {{ changeAll 
              ? 'Selecione um novo serviço para todas as mensalidades do ano letivo' 
              : 'Selecione um novo serviço para esta mensalidade' }}
          </p>
        </div>
        <button
          @click="closeModal"
          class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Modal content -->
      <div class="mt-6">
        <!-- Aviso sobre pagamentos confirmados (apenas para troca individual) -->
        <div v-if="hasConfirmedPayments && !changeAll" class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-red-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1">
              <p class="text-sm font-medium text-red-800">
                Não é possível trocar o serviço
              </p>
              <p class="text-xs text-red-700 mt-1">
                Esta mensalidade possui pagamento confirmado. Estorne o pagamento primeiro.
              </p>
            </div>
          </div>
        </div>

        <!-- Aviso informativo para troca em lote -->
        <div v-if="changeAll" class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
          <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
              <p class="text-sm font-medium text-blue-800">
                Troca em Lote
              </p>
              <p class="text-xs text-blue-700 mt-1">
                Mensalidades com pagamentos confirmados serão automaticamente ignoradas. Você verá um resumo antes de confirmar a operação.
              </p>
            </div>
          </div>
        </div>

        <!-- Informações atuais -->
        <div class="mb-6 bg-gray-50 rounded-lg p-4">
          <h4 class="text-sm font-medium text-gray-900 mb-3">Serviço Atual</h4>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-600">Serviço:</span>
              <span class="font-medium">{{ currentServiceName }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Valor:</span>
              <span class="font-medium">{{ formatCurrency(currentServicePrice) }}</span>
            </div>
            <div v-if="changeAll" class="flex justify-between">
              <span class="text-gray-600">Mensalidades afetadas:</span>
              <span class="font-medium">{{ totalInstallments }} mensalidade(s)</span>
            </div>
          </div>
        </div>

        <form @submit.prevent="submitChange" class="space-y-4">
          <!-- Seleção do novo serviço -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Novo Serviço de Mensalidade <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.classroom_service_id"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              :class="{ 'border-red-300': errors.classroom_service_id }"
              :disabled="loadingServices || hasConfirmedPayments"
            >
              <option value="">
                {{ loadingServices ? 'Carregando serviços...' : 'Selecione o serviço' }}
              </option>
              <option
                v-for="service in availableServices"
                :key="service.id"
                :value="service.id"
              >
                {{ getServiceDisplayName(service) }} - {{ formatCurrency(service.price) }}
              </option>
            </select>
            <p v-if="errors.classroom_service_id" class="mt-1 text-sm text-red-600">{{ errors.classroom_service_id }}</p>
            <p v-if="availableServices.length === 0 && !loadingServices" class="mt-1 text-sm text-yellow-600">
              Nenhum serviço de mensalidade disponível para esta turma. Verifique se existem serviços do tipo "monthly" vinculados à turma e se estão ativos.
            </p>
            <p v-if="availableServices.length > 0" class="mt-1 text-xs text-gray-500">
              {{ availableServices.length }} serviço(s) de mensalidade encontrado(s)
            </p>
          </div>

          <!-- Motivo da troca -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Motivo da Troca (Opcional)
            </label>
            <textarea
              v-model="form.reason"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Descreva o motivo da troca de serviço..."
            ></textarea>
          </div>

          <!-- Botões de Ação -->
          <div class="flex items-center justify-end pt-6 border-t border-gray-200 mt-6">
            <button
              type="button"
              @click="closeModal"
              class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="processing || (hasConfirmedPayments && !changeAll) || loadingServices"
              class="ml-4 inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
            >
              <svg v-if="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ processing ? 'Processando...' : 'Trocar Serviço' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import axios from 'axios'
import ConfirmChangeServiceModal from './ConfirmChangeServiceModal.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  installment: {
    type: Object,
    required: true
  },
  changeAll: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'success'])

const processing = ref(false)
const errors = ref({})
const availableServices = ref([])
const loadingServices = ref(false)
const showConfirmModal = ref(false)
const previewData = ref({
  total_installments: 0,
  will_update: 0,
  will_skip: 0,
  can_update: [],
  cannot_update: [],
  new_service: {}
})

const form = reactive({
  classroom_service_id: '',
  reason: ''
})

const currentServiceName = computed(() => {
  return props.installment?.classroom_service?.service?.name || 
         props.installment?.classroom_service?.service?.name || 
         'Serviço não definido'
})

const currentServicePrice = computed(() => {
  return Number(
    props.installment?.classroom_service?.service?.price ??
    props.installment?.classroom_service?.price ??
    0
  )
})

const totalInstallments = computed(() => {
  if (!props.changeAll) return 1
  return props.installment?.monthly_fee?.total_installments || 0
})

const hasConfirmedPayments = computed(() => {
  if (!props.changeAll) {
    // Verificar se esta parcela tem pagamento confirmado
    if (!props.installment.payments || props.installment.payments.length === 0) {
      return false
    }
    return props.installment.payments.some(p => p.status === 'confirmed')
  } else {
    // Verificar se alguma parcela do contrato tem pagamento confirmado
    // Isso seria verificado no backend, mas podemos fazer uma verificação básica aqui
    // O backend vai validar isso também
    return false // Deixar o backend validar
  }
})

const loadAvailableServices = async () => {
  loadingServices.value = true
  errors.value = {}
  try {
    // Buscar turma da matrícula
    const enrollment = props.installment?.monthly_fee?.enrollment
    if (!enrollment?.classroom_id) {
      errors.value.classroom_service_id = 'Matrícula não possui turma vinculada'
      availableServices.value = []
      return
    }

    // Buscar serviços de mensalidade da turma
    const response = await axios.get(`/api/classrooms/${enrollment.classroom_id}/services`, {
      params: {
        type: 'monthly'
      }
    })
    
    availableServices.value = response.data || []
    
    // Log para debug (pode remover em produção)
    if (availableServices.value.length === 0) {
      console.warn('Nenhum serviço de mensalidade encontrado para a turma:', enrollment.classroom_id)
    } else {
      console.log('Serviços encontrados:', availableServices.value.length)
      console.log('Detalhes dos serviços:', availableServices.value.map(s => ({
        id: s.id,
        service_id: s.service_id,
        service_name: s.service?.name,
        price: s.price,
        is_active: s.is_active
      })))
    }
  } catch (error) {
    console.error('Erro ao carregar serviços:', error)
    errors.value.classroom_service_id = 'Erro ao carregar serviços disponíveis: ' + (error.response?.data?.message || error.message)
    availableServices.value = []
  } finally {
    loadingServices.value = false
  }
}

const getServiceDisplayName = (classroomService) => {
  // Tentar obter nome do serviço relacionado
  if (classroomService.service?.name) {
    return classroomService.service.name
  }
  
  // Fallback: usar descrição do ClassroomService
  if (classroomService.description) {
    return classroomService.description
  }
  
  // Último fallback
  return 'Serviço #' + classroomService.id
}

const closeModal = () => {
  emit('close')
}

const submitChange = async () => {
  errors.value = {}

  // Se for troca em lote, mostrar modal de confirmação primeiro
  if (props.changeAll) {
    try {
      // Buscar preview da operação
      const previewResponse = await axios.post(
        `/api/installments/${props.installment.id}/preview-change-all-services`,
        {
          classroom_service_id: parseInt(form.classroom_service_id)
        }
      )
      
      previewData.value = previewResponse.data
      
      // Se não há parcelas para atualizar, mostrar erro
      if (previewData.value.will_update === 0) {
        alert('Não há mensalidades que possam ser alteradas. Todas possuem pagamentos confirmados.')
        return
      }
      
      // Mostrar modal de confirmação
      showConfirmModal.value = true
    } catch (error) {
      if (error.response?.data?.errors) {
        errors.value = error.response.data.errors
      } else {
        alert('Erro ao pré-visualizar troca de serviço: ' + (error.response?.data?.message || error.message))
      }
    }
  } else {
    // Troca individual - executar diretamente
    await executeChange()
  }
}

const executeChange = async () => {
  processing.value = true
  errors.value = {}
  showConfirmModal.value = false

  try {
    const endpoint = props.changeAll
      ? `/api/installments/${props.installment.id}/change-all-services`
      : `/api/installments/${props.installment.id}/change-service`

    const data = {
      classroom_service_id: parseInt(form.classroom_service_id),
      reason: form.reason || null
    }

    const response = await axios.post(endpoint, data)

    emit('success')
    
    // Mensagem personalizada baseada na resposta
    const message = response.data?.message || 
      (props.changeAll 
        ? 'Serviço de todas as mensalidades trocado com sucesso!' 
        : 'Serviço da mensalidade trocado com sucesso!')
    
    alert(message)
    closeModal()
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      alert('Erro ao trocar serviço: ' + (error.response?.data?.message || error.message))
    }
  } finally {
    processing.value = false
  }
}

const cancelConfirm = () => {
  showConfirmModal.value = false
}

watch(() => props.show, (newValue) => {
  if (newValue) {
    form.classroom_service_id = ''
    form.reason = ''
    errors.value = {}
    loadAvailableServices()
  }
})

onMounted(() => {
  if (props.show) {
    loadAvailableServices()
  }
})

const formatCurrency = (value) => {
  if (value === null || value === undefined || isNaN(value)) {
    return 'R$ 0,00'
  }
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}
</script>

