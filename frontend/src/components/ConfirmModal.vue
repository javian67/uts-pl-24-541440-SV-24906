<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 p-4"
    role="dialog"
    aria-modal="true"
  >
    <div class="card-elevated max-w-lg w-full bg-white relative">
      <!-- Header -->
      <div class="border-b-[3px] border-black pb-3 mb-4 flex justify-between items-center">
        <h3 class="font-headline text-2xl uppercase tracking-wide text-black m-0">
          {{ title }}
        </h3>
        <button
          @click="close"
          class="font-mono text-xl font-bold px-2 hover:bg-black hover:text-white border-[2px] border-black"
          aria-label="Tutup"
        >
          X
        </button>
      </div>

      <!-- Content -->
      <div class="space-y-4 mb-6">
        <p class="font-body text-base text-black">
          {{ message }}
        </p>
        <div v-if="detailText" class="p-3 bg-raw-sunken border-[2px] border-black font-mono text-xs text-black break-words">
          {{ detailText }}
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-4 border-t-[3px] border-black">
        <button
          @click="close"
          :disabled="loading"
          class="btn-secondary btn-md"
          type="button"
        >
          BATAL
        </button>
        <button
          @click="confirm"
          :disabled="loading"
          class="btn-destructive btn-md"
          type="button"
        >
          {{ loading ? 'MEMPROSES...' : confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'KONFIRMASI AKSI',
  },
  message: {
    type: String,
    default: 'Apakah Anda yakin ingin melanjutkan tindakan ini? Data yang dihapus tidak dapat dipulihkan.',
  },
  detailText: {
    type: String,
    default: '',
  },
  confirmText: {
    type: String,
    default: 'HAPUS',
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['confirm', 'close'])

const confirm = () => {
  emit('confirm')
}

const close = () => {
  if (!props.loading) {
    emit('close')
  }
}
</script>
