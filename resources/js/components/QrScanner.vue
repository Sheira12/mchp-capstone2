<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <h3 class="font-semibold text-gray-800 mb-4">QR Code Scanner</h3>

    <div v-if="!scanning && !result">
      <p class="text-sm text-gray-500 mb-4">Scan a QR code to verify a document or booking.</p>
      <button @click="startScanning" class="btn-primary">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
        Start Scanning
      </button>
    </div>

    <div v-if="scanning">
      <div id="qr-reader" class="w-full max-w-sm mx-auto rounded-lg overflow-hidden"></div>
      <button @click="stopScanning" class="mt-3 btn-secondary text-sm">Stop</button>
    </div>

    <div v-if="result" class="mt-4">
      <div v-if="result.valid" class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-center gap-2 mb-3">
          <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span class="font-semibold text-green-800">Verified</span>
        </div>
        <dl class="space-y-1 text-sm">
          <div v-for="(value, key) in result.data" :key="key" class="flex justify-between">
            <dt class="text-gray-500 capitalize">{{ key.replace(/_/g, ' ') }}</dt>
            <dd class="font-medium text-gray-800">{{ value }}</dd>
          </div>
        </dl>
      </div>
      <div v-else class="bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span class="font-semibold text-red-800">Invalid QR Code</span>
        </div>
        <p class="text-sm text-red-600 mt-1">{{ result.message }}</p>
      </div>
      <button @click="reset" class="mt-3 btn-secondary text-sm">Scan Another</button>
    </div>

    <div v-if="error" class="mt-3 text-sm text-red-600">{{ error }}</div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Html5Qrcode } from 'html5-qrcode';

const scanning = ref(false);
const result   = ref(null);
const error    = ref(null);
let scanner    = null;

async function startScanning() {
  scanning.value = true;
  result.value   = null;
  error.value    = null;

  await new Promise(r => setTimeout(r, 100)); // wait for DOM

  scanner = new Html5Qrcode('qr-reader');
  try {
    await scanner.start(
      { facingMode: 'environment' },
      { fps: 10, qrbox: { width: 250, height: 250 } },
      onScanSuccess,
      () => {}
    );
  } catch (e) {
    error.value = 'Camera access denied or not available.';
    scanning.value = false;
  }
}

async function onScanSuccess(decodedText) {
  await stopScanning();

  // Extract token from URL or use raw text
  const tokenMatch = decodedText.match(/\/verify\/([a-f0-9]+)/);
  const token = tokenMatch ? tokenMatch[1] : decodedText;

  try {
    const res = await axios.get(`/api/verify/${token}`);
    result.value = res.data;
  } catch (e) {
    result.value = { valid: false, message: 'Document not found or invalid QR code.' };
  }
}

async function stopScanning() {
  if (scanner) {
    try { await scanner.stop(); } catch {}
    scanner = null;
  }
  scanning.value = false;
}

function reset() {
  result.value = null;
  error.value  = null;
}
</script>
