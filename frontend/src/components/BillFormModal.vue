<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 p-4 overflow-y-auto"
    role="dialog"
    aria-modal="true"
  >
    <div class="card-elevated max-w-xl w-full bg-white relative my-8">
      <!-- Modal Header -->
      <div class="border-b-[3px] border-black pb-3 mb-6 flex justify-between items-center">
        <div>
          <span class="font-mono text-xs uppercase bg-black text-white px-2 py-0.5">
            {{ isEditing ? 'MODE: EDIT' : 'MODE: CREATE' }}
          </span>
          <h2 class="font-headline text-2xl uppercase tracking-tight text-black mt-1">
            {{ isEditing ? 'EDIT DATA TAGIHAN' : 'TAMBAH TAGIHAN BARU' }}
          </h2>
        </div>
        <button
          @click="closeModal"
          class="font-mono text-xl font-bold px-3 py-1 hover:bg-black hover:text-white border-[2px] border-black"
          aria-label="Tutup"
        >
          X
        </button>
      </div>

      <!-- Server / Global Error Alert -->
      <div
        v-if="errorMessage"
        class="border-[3px] border-raw-error bg-white p-3 mb-6 flex items-start space-x-2"
      >
        <span class="font-mono font-bold text-raw-error text-lg">[!]</span>
        <div class="font-mono text-xs text-raw-error leading-relaxed">
          {{ errorMessage }}
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Nama Tagihan -->
        <div>
          <label class="label-raw" for="nama_tagihan">
            NAMA TAGIHAN *
          </label>
          <input
            id="nama_tagihan"
            v-model="form.nama_tagihan"
            type="text"
            required
            placeholder="CONTOH: LISTRIK PLN / WIFI INDYHOME"
            :class="['input-raw', { 'has-error': errors.nama_tagihan }]"
          />
          <p v-if="errors.nama_tagihan" class="helper-text error">
            {{ errors.nama_tagihan[0] }}
          </p>
        </div>

        <!-- Kategori & Nominal Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Kategori -->
          <div>
            <label class="label-raw" for="kategori">
              KATEGORI *
            </label>
            <input
              id="kategori"
              v-model="form.kategori"
              type="text"
              required
              list="kategori-list"
              placeholder="PILIH ATAU KETIK..."
              :class="['input-raw', { 'has-error': errors.kategori }]"
            />
            <datalist id="kategori-list">
              <option value="Utilitas" />
              <option value="Internet & Telekomunikasi" />
              <option value="Hiburan & Streaming" />
              <option value="Pendidikan" />
              <option value="Asuransi & Kesehatan" />
              <option value="Finansial & Cicilan" />
              <option value="Lainnya" />
            </datalist>
            <p v-if="errors.kategori" class="helper-text error">
              {{ errors.kategori[0] }}
            </p>
          </div>

          <!-- Jumlah (Nominal Rupiah) -->
          <div>
            <label class="label-raw" for="jumlah">
              NOMINAL (RP) *
            </label>
            <input
              id="jumlah"
              v-model.number="form.jumlah"
              type="number"
              min="0"
              step="1000"
              required
              placeholder="CONTOH: 350000"
              :class="['input-raw', { 'has-error': errors.jumlah }]"
            />
            <p v-if="errors.jumlah" class="helper-text error">
              {{ errors.jumlah[0] }}
            </p>
          </div>
        </div>

        <!-- Jatuh Tempo & Status Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Tanggal Jatuh Tempo -->
          <div>
            <label class="label-raw" for="tanggal_jatuh_tempo">
              JATUH TEMPO *
            </label>
            <input
              id="tanggal_jatuh_tempo"
              v-model="form.tanggal_jatuh_tempo"
              type="date"
              required
              :class="['input-raw', { 'has-error': errors.tanggal_jatuh_tempo }]"
            />
            <p v-if="errors.tanggal_jatuh_tempo" class="helper-text error">
              {{ errors.tanggal_jatuh_tempo[0] }}
            </p>
          </div>

          <!-- Status -->
          <div>
            <label class="label-raw" for="status">
              STATUS TAGIHAN *
            </label>
            <select
              id="status"
              v-model="form.status"
              required
              :class="['input-raw cursor-pointer', { 'has-error': errors.status }]"
            >
              <option value="Belum Dibayar">BELUM DIBAYAR</option>
              <option value="Sudah Dibayar">SUDAH DIBAYAR</option>
            </select>
            <p v-if="errors.status" class="helper-text error">
              {{ errors.status[0] }}
            </p>
          </div>
        </div>

        <!-- Catatan -->
        <div>
          <label class="label-raw" for="catatan">
            CATATAN / KETERANGAN
          </label>
          <textarea
            id="catatan"
            v-model="form.catatan"
            rows="3"
            placeholder="CATATAN TAMBAHAN (OPSIONAL)..."
            :class="['input-raw resize-none', { 'has-error': errors.catatan }]"
          ></textarea>
          <p v-if="errors.catatan" class="helper-text error">
            {{ errors.catatan[0] }}
          </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-6 border-t-[3px] border-black">
          <button
            type="button"
            @click="closeModal"
            :disabled="loading"
            class="btn-secondary btn-md"
          >
            BATAL
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="btn-primary btn-md"
          >
            {{ loading ? 'MENYIMPAN...' : (isEditing ? 'UPDATE TAGIHAN' : 'SIMPAN TAGIHAN') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  initialData: {
    type: Object,
    default: null,
  },
  isEditing: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
  errorMessage: {
    type: String,
    default: null,
  },
})

const emit = defineEmits(['submit', 'close'])

const form = reactive({
  nama_tagihan: '',
  kategori: 'Utilitas',
  jumlah: '',
  tanggal_jatuh_tempo: new Date().toISOString().split('T')[0],
  status: 'Belum Dibayar',
  catatan: '',
})

const resetForm = () => {
  form.nama_tagihan = ''
  form.kategori = 'Utilitas'
  form.jumlah = ''
  form.tanggal_jatuh_tempo = new Date().toISOString().split('T')[0]
  form.status = 'Belum Dibayar'
  form.catatan = ''
}

watch(
  () => props.initialData,
  (val) => {
    if (val) {
      form.nama_tagihan = val.nama_tagihan || ''
      form.kategori = val.kategori || 'Utilitas'
      form.jumlah = val.jumlah || ''
      form.tanggal_jatuh_tempo = val.tanggal_jatuh_tempo || new Date().toISOString().split('T')[0]
      form.status = val.status || 'Belum Dibayar'
      form.catatan = val.catatan || ''
    } else {
      resetForm()
    }
  },
  { immediate: true }
)

const closeModal = () => {
  if (!props.loading) {
    emit('close')
  }
}

const handleSubmit = () => {
  emit('submit', { ...form })
}
</script>
