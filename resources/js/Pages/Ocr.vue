<script setup>
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const file = ref(null)
const loading = ref(false)
const error = ref('')
const result = ref(null)

function onFile(e) {
  file.value = e.target.files[0] ?? null
  error.value = ''
  result.value = null
}

async function submit() {
  if (!file.value) { error.value = 'Selecciona un archivo.'; return }
  loading.value = true; error.value = ''; result.value = null
  try {
    const fd = new FormData()
    fd.append('imagen', file.value)
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    const res = await fetch('/ocr-ine', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf },
      body: fd,
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data?.error || 'Error en OCR')
    result.value = data
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="OCR" />
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">OCR</h2>
    </template>

    <div class="p-6 space-y-4">
      <form @submit.prevent="submit" class="space-y-3">
        <input type="file" accept="image/*,.pdf" @change="onFile" class="block" />
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded" :disabled="loading">
          {{ loading ? 'Procesando...' : 'Procesar OCR' }}
        </button>
      </form>

      <div v-if="error" class="text-red-600">{{ error }}</div>

      <div v-if="result" class="space-y-2">
        <div v-if="result.clave_elector"><strong>Clave Elector:</strong> {{ result.clave_elector }}</div>
        <div v-if="result.curp"><strong>CURP:</strong> {{ result.curp }}</div>
        <div v-if="result.cic"><strong>CIC:</strong> {{ result.cic }}</div>
        <div>
          <strong>Texto completo:</strong>
          <pre class="bg-gray-100 p-3 rounded whitespace-pre-wrap">{{ result.texto_completo }}</pre>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>