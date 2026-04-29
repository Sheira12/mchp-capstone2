<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="font-semibold text-gray-800 mb-5">Complete Payment</h3>

    <div v-if="!processing && !paymentUrl">
      <div class="mb-4 p-4 bg-blue-50 rounded-lg">
        <p class="text-sm text-gray-600">Amount Due</p>
        <p class="text-2xl font-bold text-blue-700">₱{{ formatAmount(amount) }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ description }}</p>
      </div>

      <div class="mb-5">
        <label class="form-label">Payment Method</label>
        <div class="grid grid-cols-2 gap-3 mt-2">
          <button v-for="method in methods" :key="method.value"
                  @click="selectedMethod = method.value"
                  class="flex items-center gap-3 p-3 border-2 rounded-lg transition"
                  :class="selectedMethod === method.value ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
            <span class="text-2xl">{{ method.icon }}</span>
            <span class="font-medium text-sm">{{ method.label }}</span>
          </button>
        </div>
      </div>

      <button @click="initiatePayment" :disabled="!selectedMethod" class="w-full btn-primary">
        Pay ₱{{ formatAmount(amount) }} via {{ selectedMethodLabel }}
      </button>
    </div>

    <div v-if="processing" class="text-center py-8">
      <div class="w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-4"></div>
      <p class="text-gray-600">Processing payment...</p>
    </div>

    <div v-if="paymentUrl" class="text-center">
      <div class="mb-4 p-4 bg-green-50 rounded-lg">
        <svg class="w-8 h-8 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="font-semibold text-green-800">Payment link ready!</p>
        <p class="text-sm text-green-600">Click below to complete your payment.</p>
      </div>
      <a :href="paymentUrl" target="_blank" class="w-full btn-primary block text-center">
        Open Payment Page
      </a>
      <p class="text-xs text-gray-400 mt-3">You will be redirected to {{ selectedMethodLabel }} to complete payment.</p>
    </div>

    <div v-if="errorMessage" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  amount:      { type: Number, required: true },
  description: { type: String, default: 'Parish service payment' },
  bookingId:   { type: Number, default: null },
  csrfToken:   { type: String, required: true },
});

const methods = [
  { value: 'gcash',   label: 'GCash',  icon: '💚' },
  { value: 'paymaya', label: 'Maya',   icon: '💙' },
];

const selectedMethod = ref(null);
const processing     = ref(false);
const paymentUrl     = ref(null);
const errorMessage   = ref(null);

const selectedMethodLabel = computed(() =>
  methods.find(m => m.value === selectedMethod.value)?.label ?? ''
);

function formatAmount(val) {
  return parseFloat(val).toLocaleString('en-PH', { minimumFractionDigits: 2 });
}

async function initiatePayment() {
  if (!selectedMethod.value) return;
  processing.value = true;
  errorMessage.value = null;

  try {
    const res = await axios.post('/portal/payments/initiate', {
      method:     selectedMethod.value,
      booking_id: props.bookingId,
      amount:     props.amount,
    }, {
      headers: { 'X-CSRF-TOKEN': props.csrfToken },
    });

    if (res.data.success) {
      paymentUrl.value = res.data.checkout_url;
    } else {
      errorMessage.value = res.data.error ?? 'Payment initiation failed.';
    }
  } catch (e) {
    errorMessage.value = e.response?.data?.message ?? 'An error occurred. Please try again.';
  } finally {
    processing.value = false;
  }
}
</script>
