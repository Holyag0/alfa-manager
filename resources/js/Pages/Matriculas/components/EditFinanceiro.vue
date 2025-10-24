<template>
  <div class="space-y-6">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Financeiro da Matrícula</h2>
    
    <!-- Resumo Financeiro -->
    <div v-if="financialSummary" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-cyan-50">
        <h3 class="text-lg font-semibold text-gray-900">Resumo Financeiro</h3>
        <p class="text-sm text-gray-600">Informações financeiras da matrícula</p>
      </div>
      
      <div class="p-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Total em Aberto -->
          <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                </svg>
              </div>
              <div class="ml-3 min-w-0 flex-1">
                <p class="text-xs font-medium text-red-600 truncate">Total em Aberto</p>
                <p class="text-lg font-bold text-red-900 truncate">{{ financialSummary.summary.total_due_formatted }}</p>
              </div>
            </div>
          </div>

          <!-- Total Pago -->
          <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div class="ml-3 min-w-0 flex-1">
                <p class="text-xs font-medium text-green-600 truncate">Total Pago</p>
                <p class="text-lg font-bold text-green-900 truncate">{{ financialSummary.summary.total_payments_formatted }}</p>
              </div>
            </div>
          </div>

          <!-- Faturas Pendentes -->
          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div class="ml-3 min-w-0 flex-1">
                <p class="text-xs font-medium text-yellow-600 truncate">Faturas Pendentes</p>
                <p class="text-lg font-bold text-yellow-900 truncate">{{ financialSummary.summary.pending_invoices }}</p>
              </div>
            </div>
          </div>

          <!-- Status -->
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
              </div>
              <div class="ml-3 min-w-0 flex-1">
                <p class="text-xs font-medium text-blue-600 truncate">Status</p>
                <p class="text-lg font-bold text-blue-900 truncate">{{ financialSummary.summary.payment_status_label }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabela de faturas removida conforme solicitado -->

    <!-- Botão para Adicionar Serviços -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-indigo-50">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Serviços da Matrícula</h3>
            <p class="text-sm text-gray-600">Gerencie os serviços adicionais desta matrícula</p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="openPaymentModal"
              :disabled="enrollmentServices.filter(s => s.status === 'pending').length === 0"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
              </svg>
              Registrar Pagamento
            </button>
            <button
              @click="openAddServicesModal"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
              Adicionar Serviços
          </button>
          </div>
        </div>
      </div>
      
      <div class="p-6">
        <!-- Serviços Selecionados -->
        <div v-if="cartServices.length > 0" class="mb-6">
          <h4 class="text-lg font-medium text-gray-900 mb-4">Serviços Selecionados</h4>
          <div class="space-y-3">
            <div
              v-for="service in cartServices"
              :key="service.id"
              class="flex items-center justify-between p-4 border border-blue-200 rounded-lg bg-blue-50"
            >
              <div class="flex-1">
                <div class="flex items-center space-x-3">
                  <div class="w-4 h-4 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                  </div>
                  <div>
                    <label class="text-sm font-medium text-gray-900">{{ service.name }}</label>
                    <p class="text-sm text-gray-500">{{ service.description || 'Sem descrição' }}</p>
                  </div>
                </div>
              </div>
              <div class="flex items-center space-x-3">
                <span class="text-lg font-semibold text-blue-600">{{ service.formatted_price }}</span>
                <button
                  @click="removeFromCart(service.id)"
                  class="text-red-600 hover:text-red-800 p-1"
                  title="Remover do carrinho"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Total do Carrinho e Botão de Pagamento -->
          <div class="mt-4 p-4 bg-gray-100 rounded-lg">
            <div class="flex items-center justify-between">
              <div>
                <span class="text-lg font-semibold text-gray-900">Total: {{ getFormattedCartTotal() }}</span>
                <p class="text-sm text-gray-500">{{ cartServices.length }} serviço(s) selecionado(s)</p>
              </div>
              <div class="flex space-x-3">
                <button
                  @click="clearCart"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  Limpar
                </button>
                <button
                  @click="processPayment"
                  class="px-6 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                  <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                  </svg>
                  Finalizar
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Lista de serviços já adicionados (contratados) -->
        <div v-if="enrollmentServices.length > 0" class="mb-6">
          <h4 class="text-lg font-medium text-gray-900 mb-4">Serviços Contratados</h4>
          <div class="space-y-3">
            <div
              v-for="service in enrollmentServices"
              :key="service.id"
              @click="openServicePaymentModal(service)"
              class="flex items-center justify-between p-4 border border-gray-200 rounded-lg bg-gray-50 cursor-pointer hover:bg-gray-100 hover:scale-105 transition-all duration-200"
            >
              <div class="flex-1">
                <div class="flex items-center space-x-3">
                  <div class="w-4 h-4 flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                  </div>
                  <div>
                    <label class="text-sm font-medium text-gray-900">{{ service.name }}</label>
                    <p class="text-sm text-gray-500">{{ service.description || 'Sem descrição' }}</p>
                  </div>
                </div>
              </div>
              <div class="text-right">
                <span class="text-lg font-semibold text-gray-900">{{ service.formatted_amount }}</span>
                <div class="flex items-center space-x-2 mt-1">
                  <span
                    :class="[
                      'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                      service.status === 'paid' ? 'bg-green-100 text-green-800' : 
                      service.status === 'refunded' ? 'bg-red-100 text-red-800' : 
                      'bg-yellow-100 text-yellow-800'
                    ]"
                  >
                    {{ service.status_label }}
                </span>
                  <!-- Botões de ação -->
                  <div class="flex items-center space-x-1">
                    <!-- Botão para reativar serviço estornado -->
                    <button
                      v-if="service.status === 'refunded'"
                      @click="reactivateService(service.id)"
                      class="text-blue-600 hover:text-blue-800 p-1"
                      title="Reativar serviço"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                      </svg>
                    </button>
                    <!-- Botão para remover serviço (apenas se não estiver pago ou estornado) -->
                    <button
                      v-if="service.status !== 'paid' && service.status !== 'refunded'"
                      @click="removeService(service.id)"
                      class="text-red-600 hover:text-red-800 p-1"
                      title="Remover serviço"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Estado vazio quando não há serviços -->
        <div v-if="cartServices.length === 0 && enrollmentServices.length === 0" class="text-center py-8">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum serviço adicionado</h3>
          <p class="mt-1 text-sm text-gray-500">Clique em "Adicionar Serviços" para começar.</p>
        </div>
      </div>
    </div>

    <!-- Estado vazio removido - resumo financeiro sempre funcional -->

    <!-- Informação sobre como visualizar detalhes -->
    <div v-if="financialSummary && financialSummary.recent_payments.length > 0" class="bg-blue-50 rounded-lg p-4 border border-blue-200">
      <div class="flex items-center">
        <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
          <p class="text-sm font-medium text-blue-800">💡 Dica</p>
          <p class="text-sm text-blue-700">Clique em qualquer serviço na seção "Serviços Contratados" para ver os detalhes do pagamento.</p>
        </div>
      </div>
    </div>

    <!-- Modal para Adicionar Serviços -->
    <AddServicesModal 
      :show="showAddServicesModal" 
      :enrollment="enrollment"
      @close="closeAddServicesModal"
      @services-added="handleServicesAdded"
    />

    <!-- Modal para Registrar Pagamento -->
    <PaymentModal 
      :show="showPaymentModal" 
      :enrollment="enrollment"
      :selected-invoices="selectedInvoicesForPayment"
      @close="closePaymentModal"
      @payment-processed="handlePaymentProcessed"
    />

    <!-- Modal para Detalhes do Pagamento -->
    <PaymentDetailsModal 
      :show="showPaymentDetailsModal" 
      :payment="selectedPayment"
      @close="closePaymentDetailsModal"
      @request-edit="handleEditRequest"
      @request-refund="handleRefundRequest"
      @request-delete-service="handleDeleteServiceRequest"
    />

    <!-- Modal para Estorno de Pagamento -->
    <RefundPaymentModal 
      :show="showRefundModal" 
      :payment="paymentToRefund"
      @close="closeRefundModal"
      @refund-success="handleRefundSuccess"
    />

    <!-- Modal para Editar Pagamento -->
    <EditPaymentModal 
      :show="showEditPaymentModal" 
      :payment="paymentToEdit"
      :enrollment="enrollment"
      @close="closeEditPaymentModal"
      @payment-updated="handlePaymentUpdated"
    />

    <!-- Flash Messages -->
    <div v-if="flashMessage.message" class="fixed top-4 right-4 z-50">
      <div :class="[
        'px-4 py-3 rounded-md shadow-lg max-w-sm',
        flashMessage.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
      ]">
        <div class="flex items-center">
          <svg v-if="flashMessage.type === 'success'" class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          <svg v-else class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
          <span class="text-sm font-medium">{{ flashMessage.message }}</span>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmação -->
    <ConfirmationModal :show="showDeleteConfirmation" @close="showDeleteConfirmation = false">
      <template #title>
        <span v-if="serviceToDelete?.action === 'reactivate'">Reativar Serviço</span>
        <span v-else-if="serviceToDelete?.action === 'delete'">Deletar Serviço</span>
        <span v-else>Remover Serviço</span>
      </template>
      <template #content>
        <span v-if="serviceToDelete?.action === 'reactivate'">
          Tem certeza que deseja reativar este serviço? Ele voltará ao status "Pendente" e poderá ser pago normalmente.
        </span>
        <span v-else-if="serviceToDelete?.action === 'delete'">
          Tem certeza que deseja deletar este serviço estornado? Esta ação não pode ser desfeita e removerá permanentemente o serviço e seus pagamentos.
        </span>
        <span v-else>
          Tem certeza que deseja remover este serviço? Esta ação não pode ser desfeita.
        </span>
      </template>
      <template #footer>
        <button
          type="button"
          @click="showDeleteConfirmation = false"
          class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          Cancelar
        </button>
        <button
          type="button"
          @click="serviceToDelete?.action === 'reactivate' ? confirmReactivation() : serviceToDelete?.action === 'delete' ? confirmDelete() : confirmRemove()"
          class="ml-3 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <span v-if="serviceToDelete?.action === 'reactivate'">Reativar</span>
          <span v-else-if="serviceToDelete?.action === 'delete'">Deletar</span>
          <span v-else>Remover</span>
        </button>
      </template>
    </ConfirmationModal>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import AddServicesModal from './AddServicesModal.vue'
import PaymentModal from './PaymentModal.vue'
import PaymentDetailsModal from './PaymentDetailsModal.vue'
import RefundPaymentModal from './RefundPaymentModal.vue'
import EditPaymentModal from './EditPaymentModal.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'

const props = defineProps({ 
  enrollment: Object 
})

const financialSummary = ref(null)
const loading = ref(false)

// Variáveis para o modal de serviços
const showAddServicesModal = ref(false)
const cartServices = ref([]) // Carrinho de serviços (sem criar faturas ainda)
const enrollmentServices = ref([]) // Serviços já pagos/adicionados

// Variáveis para o modal de pagamento
const showPaymentModal = ref(false)
const selectedInvoicesForPayment = ref([])

// Variáveis para o modal de detalhes do pagamento
const showPaymentDetailsModal = ref(false)
const selectedPayment = ref(null)

// Variáveis para o modal de estorno
const showRefundModal = ref(false)
const paymentToRefund = ref(null)

// Variáveis para o modal de edição
const showEditPaymentModal = ref(false)
const paymentToEdit = ref(null)

// Variáveis para confirmações e feedback
const showDeleteConfirmation = ref(false)
const serviceToDelete = ref(null)
const flashMessage = ref({ type: '', message: '' })

// Computed properties para formatação
const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const getStatusClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'paid': 'bg-green-100 text-green-800',
    'overdue': 'bg-red-100 text-red-800',
    'cancelled': 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

// Carregar dados financeiros
const loadFinancialData = async () => {
  if (!props.enrollment?.id) return
  
  loading.value = true
  try {
    const response = await fetch(`/api/enrollments/${props.enrollment.id}/financial-summary`)
    if (response.ok) {
      const data = await response.json()
      financialSummary.value = data
      
      // Adicionar formatação aos valores
      if (financialSummary.value?.summary) {
        // Garantir que os valores são números antes de formatar
        const totalDue = Number(financialSummary.value.summary.total_due) || 0
        const totalPayments = Number(financialSummary.value.summary.total_payments) || 0
        
        financialSummary.value.summary.total_due_formatted = `R$ ${totalDue.toFixed(2).replace('.', ',')}`
        financialSummary.value.summary.total_payments_formatted = `R$ ${totalPayments.toFixed(2).replace('.', ',')}`
        financialSummary.value.summary.payment_status_label = getPaymentStatusLabel(financialSummary.value.summary.payment_status)
      }
    } else {
      // Se não há dados financeiros, criar um resumo básico
      financialSummary.value = {
        summary: {
          total_due: 0,
          total_payments: 0,
          pending_invoices: 0,
          overdue_invoices: 0,
          payment_status: 'pending',
          total_due_formatted: 'R$ 0,00',
          total_payments_formatted: 'R$ 0,00',
          payment_status_label: 'Pendente'
        },
        recent_invoices: [],
        recent_payments: []
      }
    }
  } catch (error) {
    console.error('Erro ao carregar dados financeiros:', error)
    // Criar resumo básico em caso de erro
    financialSummary.value = {
      summary: {
        total_due: 0,
        total_payments: 0,
        pending_invoices: 0,
        overdue_invoices: 0,
        payment_status: 'pending',
        total_due_formatted: 'R$ 0,00',
        total_payments_formatted: 'R$ 0,00',
        payment_status_label: 'Pendente'
      },
      recent_invoices: [],
      recent_payments: []
    }
  } finally {
    loading.value = false
  }
}

const getPaymentStatusLabel = (status) => {
  const labels = {
    'paid': 'Pago',
    'pending': 'Pendente',
    'overdue': 'Vencido',
    'partial': 'Parcial'
  }
  return labels[status] || status
}

// Métodos para o modal de serviços
const openAddServicesModal = () => {
  showAddServicesModal.value = true
}

const closeAddServicesModal = () => {
  showAddServicesModal.value = false
}

const handleServicesAdded = async (selectedServicesData) => {
  try {
    console.log('Serviços adicionados ao carrinho:', selectedServicesData)
    
    // Adicionar serviços ao carrinho (sem criar faturas ainda)
    cartServices.value = [...cartServices.value, ...selectedServicesData]
    
  } catch (error) {
    console.error('Erro ao adicionar serviços ao carrinho:', error)
  }
}

// Funções para gerenciar o carrinho
const removeFromCart = (serviceId) => {
  cartServices.value = cartServices.value.filter(service => service.id !== serviceId)
}

const clearCart = () => {
  cartServices.value = []
}

const getCartTotal = () => {
  return cartServices.value.reduce((total, service) => total + service.price, 0)
}

const getFormattedCartTotal = () => {
  const total = getCartTotal()
  return `R$ ${total.toFixed(2).replace('.', ',')}`
}

// Função para mostrar mensagem flash
const showFlashMessage = (type, message) => {
  flashMessage.value = { type, message }
  setTimeout(() => {
    flashMessage.value = { type: '', message: '' }
  }, 5000)
}

// Processar pagamento (criar faturas)
const processPayment = async () => {
  if (cartServices.value.length === 0) {
    showFlashMessage('error', 'Carrinho vazio!')
    return
  }
  
  try {
    // Criar faturas para os serviços do carrinho
    const serviceIds = cartServices.value.map(service => service.id)
    
    const response = await fetch(`/api/enrollments/${props.enrollment.id}/add-services`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        service_ids: serviceIds
      })
    })
    
    if (!response.ok) {
      let errorMessage = 'Erro ao criar faturas'
      try {
        const errorData = await response.json()
        errorMessage = errorData.error || errorData.message || errorMessage
      } catch (e) {
        console.error('Erro ao fazer parse da resposta:', e)
      }
      throw new Error(errorMessage)
    }
    
    const result = await response.json()
    console.log('Faturas criadas:', result)
    
    // Limpar carrinho
    clearCart()
    
    // Recarregar dados financeiros
    await loadFinancialData()
    await loadEnrollmentServices()
    
    showFlashMessage('success', 'Faturas criadas com sucesso! Agora você pode registrar o pagamento.')
    
  } catch (error) {
    console.error('Erro ao criar faturas:', error)
    showFlashMessage('error', `Erro ao criar faturas: ${error.message}`)
  }
}

// Funções para o modal de pagamento
const openPaymentModal = () => {
  // Buscar faturas pendentes para pagamento
  const pendingInvoices = enrollmentServices.value.filter(service => service.status === 'pending')
  
  if (pendingInvoices.length === 0) {
    showFlashMessage('error', 'Não há faturas pendentes para pagamento.')
    return
  }
  
  selectedInvoicesForPayment.value = pendingInvoices
  showPaymentModal.value = true
}

const closePaymentModal = () => {
  showPaymentModal.value = false
  selectedInvoicesForPayment.value = []
}

const handlePaymentProcessed = async () => {
  // Recarregar dados após pagamento
  await loadFinancialData()
  await loadEnrollmentServices()
}

// Funções para o modal de detalhes do pagamento
const openPaymentDetailsModal = (payment) => {
  selectedPayment.value = payment
  showPaymentDetailsModal.value = true
}

const closePaymentDetailsModal = () => {
  showPaymentDetailsModal.value = false
  selectedPayment.value = null
}


// Função para lidar com solicitação de edição
const handleEditRequest = (payment) => {
  // Fechar modal de detalhes
  closePaymentDetailsModal()
  
  // Abrir modal de edição
  paymentToEdit.value = payment
  showEditPaymentModal.value = true
}

// Função para fechar modal de edição
const closeEditPaymentModal = () => {
  showEditPaymentModal.value = false
  paymentToEdit.value = null
}

// Função para lidar com atualização bem-sucedida
const handlePaymentUpdated = async (result) => {
  console.log('Pagamento atualizado:', result)
  
  // Recarregar dados financeiros
  await loadFinancialData()
  await loadEnrollmentServices()
}

// Função para lidar com solicitação de estorno
const handleRefundRequest = (payment) => {
  // Fechar modal de detalhes
  closePaymentDetailsModal()
  
  // Abrir modal de estorno
  paymentToRefund.value = payment
  showRefundModal.value = true
}

// Função para fechar modal de estorno
const closeRefundModal = () => {
  showRefundModal.value = false
  paymentToRefund.value = null
}

// Função para lidar com estorno bem-sucedido
const handleRefundSuccess = async (result) => {
  console.log('Pagamento estornado:', result)
  
  // Recarregar dados financeiros
  await loadFinancialData()
  await loadEnrollmentServices()
}

// Função para reativar serviço estornado
const reactivateService = async (serviceId) => {
  serviceToDelete.value = { id: serviceId, action: 'reactivate' }
  showDeleteConfirmation.value = true
}

// Função para confirmar reativação
const confirmReactivation = async () => {
  if (!serviceToDelete.value) return
  
  try {
    const response = await fetch(`/api/enrollments/${props.enrollment.id}/services/${serviceToDelete.value.id}/reactivate`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    
    if (!response.ok) {
      let errorMessage = 'Erro ao reativar serviço'
      try {
        const errorData = await response.json()
        errorMessage = errorData.error || errorData.message || errorMessage
      } catch (e) {
        console.error('Erro ao fazer parse da resposta:', e)
      }
      throw new Error(errorMessage)
    }
    
    const result = await response.json()
    showFlashMessage('success', result.message || 'Serviço reativado com sucesso!')
    
    // Recarregar dados
    await loadFinancialData()
    await loadEnrollmentServices()
    
  } catch (error) {
    console.error('Erro ao reativar serviço:', error)
    showFlashMessage('error', `Erro ao reativar serviço: ${error.message}`)
  } finally {
    showDeleteConfirmation.value = false
    serviceToDelete.value = null
  }
}

// Função para lidar com solicitação de deleção de serviço
const handleDeleteServiceRequest = (payment) => {
  // Fechar modal de detalhes
  closePaymentDetailsModal()
  
  serviceToDelete.value = { id: payment.invoice_id, action: 'delete' }
  showDeleteConfirmation.value = true
}

// Função para confirmar deleção
const confirmDelete = async () => {
  if (!serviceToDelete.value) return
  
  try {
    const response = await fetch(`/api/enrollments/${props.enrollment.id}/services/${serviceToDelete.value.id}/permanent-delete`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    
    if (!response.ok) {
      let errorMessage = 'Erro ao deletar serviço'
      try {
        const errorData = await response.json()
        errorMessage = errorData.error || errorData.message || errorMessage
      } catch (e) {
        console.error('Erro ao fazer parse da resposta:', e)
      }
      throw new Error(errorMessage)
    }
    
    const result = await response.json()
    showFlashMessage('success', result.message || 'Serviço deletado com sucesso!')
    
    // Recarregar dados
    await loadFinancialData()
    await loadEnrollmentServices()
    
  } catch (error) {
    console.error('Erro ao deletar serviço:', error)
    showFlashMessage('error', `Erro ao deletar serviço: ${error.message}`)
  } finally {
    showDeleteConfirmation.value = false
    serviceToDelete.value = null
  }
}

// Função para abrir modal de detalhes do pagamento do serviço
const openServicePaymentModal = async (service) => {
  // Buscar pagamentos relacionados ao serviço
  const payment = financialSummary.value.recent_payments.find(p => p.invoice_id === service.id)
  
  if (payment) {
    selectedPayment.value = payment
    showPaymentDetailsModal.value = true
  } else {
    // Se não há pagamento, mostrar mensagem informativa
    alert('Este serviço ainda não possui pagamentos registrados.')
  }
}

// Carregar serviços já adicionados à matrícula
const loadEnrollmentServices = async () => {
  if (!props.enrollment?.id) return
  
  try {
    const response = await fetch(`/api/enrollments/${props.enrollment.id}/services`)
    if (response.ok) {
      const data = await response.json()
      enrollmentServices.value = data
    }
  } catch (error) {
    console.error('Erro ao carregar serviços da matrícula:', error)
  }
}

// Remover serviço da matrícula
const removeService = async (invoiceId) => {
  serviceToDelete.value = { id: invoiceId, action: 'remove' }
  showDeleteConfirmation.value = true
}

// Função para confirmar remoção
const confirmRemove = async () => {
  if (!serviceToDelete.value) return
  
  try {
    const response = await fetch(`/api/enrollments/${props.enrollment.id}/services/${serviceToDelete.value.id}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    
    if (!response.ok) {
      let errorMessage = 'Erro ao remover serviço'
      try {
        const errorData = await response.json()
        errorMessage = errorData.error || errorData.message || errorMessage
      } catch (e) {
        console.error('Erro ao fazer parse da resposta:', e)
      }
      throw new Error(errorMessage)
    }
    
    const result = await response.json()
    console.log('Serviço removido:', result)
    
    // Recarregar dados
    await loadFinancialData()
    await loadEnrollmentServices()
    
    showFlashMessage('success', 'Serviço removido com sucesso!')
    
  } catch (error) {
    console.error('Erro ao remover serviço:', error)
    showFlashMessage('error', `Erro ao remover serviço: ${error.message}`)
  } finally {
    showDeleteConfirmation.value = false
    serviceToDelete.value = null
  }
}

onMounted(() => {
  loadFinancialData()
  loadEnrollmentServices()
})
</script> 