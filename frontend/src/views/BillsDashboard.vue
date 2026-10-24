<template>
  <div class="min-h-screen bg-white text-black flex flex-col">
    <!-- Navbar -->
    <Navbar />

    <!-- Main Content Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
      <!-- Title & Hero Section -->
      <section class="border-b-[5px] border-black pb-6">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
          <div>
            <h1 class="font-headline text-4xl sm:text-5xl lg:text-6xl uppercase tracking-tight leading-none text-black">
              TAGIHAN SAYA
            </h1>
          </div>

          <div class="flex items-center space-x-3">
            <button
              @click="openCreateModal"
              type="button"
              class="btn-primary btn-md flex items-center space-x-2"
            >
              <span>+ TAMBAH TAGIHAN</span>
            </button>
          </div>
        </div>
      </section>

      <!-- Global Error / Alert Banner -->
      <div
        v-if="billsStore.errorMessage"
        class="border-[3px] border-raw-error bg-white p-4 flex items-start justify-between space-x-4"
      >
        <div class="flex items-start space-x-3">
          <span class="font-mono font-bold text-raw-error text-xl">[!]</span>
          <div class="font-mono text-sm text-raw-error">
            <span class="font-bold uppercase">KESALAHAN:</span> {{ billsStore.errorMessage }}
          </div>
        </div>
        <button
          @click="billsStore.clearErrors"
          class="font-mono font-bold text-xs uppercase underline hover:text-raw-error"
        >
          [TUTUP]
        </button>
      </div>

      <!-- Metric Summary Cards (Brutalist Hierarchy) -->
      <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Metric 1: Total Semua Tagihan -->
        <div class="card-raw">
          <div class="flex justify-between items-start mb-2">
            <span class="font-mono text-xs font-bold uppercase tracking-wider text-gray-700">
              TOTAL TAGIHAN
            </span>
            <span class="font-mono text-xs border-[2px] border-black px-2 py-0.5 bg-raw-sunken font-bold">
              {{ billsStore.bills.length }} ITEM
            </span>
          </div>
          <div class="font-headline text-3xl sm:text-4xl text-black">
            {{ formatRupiah(billsStore.totalAmount) }}
          </div>
          <p class="font-body text-xs text-gray-600 mt-2">
            Akumulasi seluruh tagihan aktif di sistem.
          </p>
        </div>

        <!-- Metric 2: Belum Dibayar (Elevated Border 5px for visual tension & importance) -->
        <div class="card-elevated border-raw-error">
          <div class="flex justify-between items-start mb-2">
            <span class="font-mono text-xs font-bold uppercase tracking-wider text-raw-error">
              BELUM DIBAYAR
            </span>
            <span class="chip-error">
              {{ billsStore.unpaidCount }} TERTUNDA
            </span>
          </div>
          <div class="font-headline text-3xl sm:text-4xl text-raw-error">
            {{ formatRupiah(billsStore.unpaidAmount) }}
          </div>
          <p class="font-body text-xs text-gray-700 mt-2 font-medium">
            Segera selesaikan sebelum tanggal jatuh tempo.
          </p>
        </div>

        <!-- Metric 3: Sudah Dibayar -->
        <div class="card-raw border-raw-success">
          <div class="flex justify-between items-start mb-2">
            <span class="font-mono text-xs font-bold uppercase tracking-wider text-raw-success">
              SUDAH DIBAYAR
            </span>
            <span class="chip-success">
              {{ billsStore.paidCount }} LUNAS
            </span>
          </div>
          <div class="font-headline text-3xl sm:text-4xl text-raw-success">
            {{ formatRupiah(billsStore.paidAmount) }}
          </div>
          <p class="font-body text-xs text-gray-600 mt-2">
            Total tagihan yang telah diselesaikan.
          </p>
        </div>
      </section>

      <!-- Filter, Search & View Controls Bar -->
      <section class="border-[3px] border-black bg-raw-sunken p-4 space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          <!-- Filter Chips (RawBlock Spec) -->
          <div class="flex flex-wrap items-center gap-2">
            <span class="font-mono text-xs font-bold uppercase mr-1">FILTER:</span>
            <button
              @click="billsStore.filterStatus = 'ALL'"
              :class="[
                'font-mono text-xs uppercase px-3 py-1 font-bold border-[2px] border-black transition-none',
                billsStore.filterStatus === 'ALL'
                  ? 'bg-black text-white'
                  : 'bg-white text-black hover:bg-black hover:text-white'
              ]"
              type="button"
            >
              SEMUA ({{ billsStore.bills.length }})
            </button>
            <button
              @click="billsStore.filterStatus = 'Belum Dibayar'"
              :class="[
                'font-mono text-xs uppercase px-3 py-1 font-bold border-[2px] border-black transition-none',
                billsStore.filterStatus === 'Belum Dibayar'
                  ? 'bg-raw-error text-white'
                  : 'bg-white text-black hover:bg-raw-error hover:text-white'
              ]"
              type="button"
            >
              BELUM DIBAYAR ({{ billsStore.unpaidCount }})
            </button>
            <button
              @click="billsStore.filterStatus = 'Sudah Dibayar'"
              :class="[
                'font-mono text-xs uppercase px-3 py-1 font-bold border-[2px] border-black transition-none',
                billsStore.filterStatus === 'Sudah Dibayar'
                  ? 'bg-raw-success text-white'
                  : 'bg-white text-black hover:bg-raw-success hover:text-white'
              ]"
              type="button"
            >
              SUDAH DIBAYAR ({{ billsStore.paidCount }})
            </button>
          </div>

          <!-- Search Input & View Switcher -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <!-- Search -->
            <div class="relative min-w-[240px]">
              <input
                v-model="billsStore.searchQuery"
                type="text"
                placeholder="CARI NAMA / KATEGORI..."
                class="input-raw !bg-white text-xs py-1.5"
              />
            </div>

            <!-- View Switcher (Table / Grid) -->
            <div class="flex border-[2px] border-black bg-white">
              <button
                @click="viewMode = 'table'"
                :class="[
                  'px-3 py-1 font-mono text-xs font-bold uppercase transition-none',
                  viewMode === 'table' ? 'bg-black text-white' : 'hover:bg-gray-200'
                ]"
                title="Tampilan Tabel"
                type="button"
              >
                TABEL
              </button>
              <button
                @click="viewMode = 'grid'"
                :class="[
                  'px-3 py-1 font-mono text-xs font-bold uppercase transition-none border-l-[2px] border-black',
                  viewMode === 'grid' ? 'bg-black text-white' : 'hover:bg-gray-200'
                ]"
                title="Tampilan Kartu / Grid"
                type="button"
              >
                GRID
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- Loading State -->
      <div
        v-if="billsStore.loading"
        class="border-[3px] border-black bg-raw-sunken p-12 text-center"
      >
        <div class="font-mono text-base font-bold uppercase tracking-widest animate-pulse">
          [ MEMUAT DATA TAGIHAN DARI SERVER... ]
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-else-if="billsStore.filteredBills.length === 0"
        class="card-raw text-center py-16 px-4 space-y-4"
      >
        <div class="font-mono text-4xl font-bold">[ 0_0 ]</div>
        <h3 class="font-headline text-2xl uppercase tracking-tight">
          TIDAK ADA DATA TAGIHAN DITEMUKAN
        </h3>
        <p class="font-body text-sm text-gray-700 max-w-md mx-auto">
          {{
            billsStore.searchQuery || billsStore.filterStatus !== 'ALL'
              ? 'Tidak ada tagihan yang sesuai dengan filter atau kata kunci pencarian.'
              : 'Belum ada data tagihan yang tersimpan. Mulai catat tagihan baru sekarang!'
          }}
        </p>
        <div class="pt-2">
          <button
            v-if="billsStore.searchQuery || billsStore.filterStatus !== 'ALL'"
            @click="resetFilters"
            class="btn-secondary btn-sm"
            type="button"
          >
            RESET FILTER & PENCARIAN
          </button>
          <button
            v-else
            @click="openCreateModal"
            class="btn-primary btn-md"
            type="button"
          >
            + BUAT TAGIHAN PERTAMA
          </button>
        </div>
      </div>

      <!-- Content Mode: Table View -->
      <section v-else-if="viewMode === 'table'" class="overflow-x-auto border-[3px] border-black">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-black text-white font-mono text-xs uppercase tracking-wider border-b-[3px] border-black">
              <th class="p-3 border-r-[2px] border-gray-700">#</th>
              <th class="p-3 border-r-[2px] border-gray-700">TAGIHAN</th>
              <th class="p-3 border-r-[2px] border-gray-700">KATEGORI</th>
              <th class="p-3 border-r-[2px] border-gray-700">NOMINAL</th>
              <th class="p-3 border-r-[2px] border-gray-700">JATUH TEMPO</th>
              <th class="p-3 border-r-[2px] border-gray-700 text-center">STATUS</th>
              <th class="p-3 text-right">AKSI</th>
            </tr>
          </thead>
          <tbody class="divide-y-[2px] divide-black font-body text-sm">
            <tr
              v-for="(bill, index) in billsStore.filteredBills"
              :key="bill.id || index"
              class="hover:bg-raw-sunken transition-none"
            >
              <!-- Index -->
              <td class="p-3 font-mono text-xs border-r-[2px] border-black font-bold">
                {{ String(index + 1).padStart(2, '0') }}
              </td>

              <!-- Nama Tagihan & Catatan -->
              <td class="p-3 border-r-[2px] border-black">
                <div class="font-headline text-base text-black uppercase">
                  {{ bill.nama_tagihan }}
                </div>
                <div v-if="bill.catatan" class="font-body text-xs text-gray-700 mt-0.5">
                  {{ bill.catatan }}
                </div>
              </td>

              <!-- Kategori -->
              <td class="p-3 border-r-[2px] border-black font-mono text-xs uppercase">
                <span class="border-[1px] border-black px-2 py-0.5 bg-white">
                  {{ bill.kategori || '-' }}
                </span>
              </td>

              <!-- Nominal -->
              <td class="p-3 border-r-[2px] border-black font-mono font-bold text-sm">
                {{ formatRupiah(bill.jumlah) }}
              </td>

              <!-- Tanggal Jatuh Tempo -->
              <td class="p-3 border-r-[2px] border-black font-mono text-xs">
                {{ formatDate(bill.tanggal_jatuh_tempo) }}
              </td>

              <!-- Status -->
              <td class="p-3 border-r-[2px] border-black text-center">
                <button
                  @click="toggleStatus(bill)"
                  type="button"
                  title="Klik untuk ubah status"
                  :class="[
                    bill.status === 'Sudah Dibayar' ? 'chip-success hover:bg-green-100' : 'chip-error hover:bg-red-100',
                    'cursor-pointer'
                  ]"
                >
                  {{ bill.status === 'Sudah Dibayar' ? 'LUNAS' : 'BELUM DIBAYAR' }}
                </button>
              </td>

              <!-- Actions -->
              <td class="p-3 text-right space-x-2">
                <button
                  @click="openEditModal(bill)"
                  class="btn-secondary btn-sm"
                  type="button"
                >
                  EDIT
                </button>
                <button
                  @click="confirmDelete(bill)"
                  class="btn-destructive btn-sm"
                  type="button"
                >
                  HAPUS
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </section>

      <!-- Content Mode: Grid Cards View -->
      <section v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="bill in billsStore.filteredBills"
          :key="bill.id"
          class="card-raw flex flex-col justify-between hover:border-[5px] transition-none"
        >
          <div>
            <!-- Card Header: Category & Status Chip -->
            <div class="flex justify-between items-center pb-3 border-b-[2px] border-black mb-3">
              <span class="font-mono text-xs border-[1px] border-black px-2 py-0.5 uppercase bg-raw-sunken">
                {{ bill.kategori || 'UMUM' }}
              </span>
              <button
                @click="toggleStatus(bill)"
                type="button"
                title="Klik untuk ubah status"
                :class="[
                  bill.status === 'Sudah Dibayar' ? 'chip-success' : 'chip-error',
                  'cursor-pointer'
                ]"
              >
                {{ bill.status === 'Sudah Dibayar' ? 'LUNAS' : 'BELUM DIBAYAR' }}
              </button>
            </div>

            <!-- Title -->
            <h3 class="font-headline text-xl uppercase tracking-tight leading-snug mb-2">
              {{ bill.nama_tagihan }}
            </h3>

            <!-- Nominal -->
            <div class="font-mono text-2xl font-bold text-black mb-3">
              {{ formatRupiah(bill.jumlah) }}
            </div>

            <!-- Due Date -->
            <div class="font-mono text-xs text-gray-800 mb-4 flex items-center space-x-1">
              <span class="font-bold">JATUH TEMPO:</span>
              <span>{{ formatDate(bill.tanggal_jatuh_tempo) }}</span>
            </div>

            <!-- Notes -->
            <div
              v-if="bill.catatan"
              class="bg-raw-sunken border-[2px] border-black p-3 font-mono text-xs text-gray-800 mb-4"
            >
              {{ bill.catatan }}
            </div>
          </div>

          <!-- Bottom Action Buttons -->
          <div class="pt-4 border-t-[3px] border-black flex items-center justify-between space-x-2">
            <button
              @click="toggleStatus(bill)"
              class="font-mono text-xs uppercase underline font-bold hover:text-raw-blue"
              type="button"
            >
              {{ bill.status === 'Sudah Dibayar' ? '→ JADIKAN BELUM' : '→ TANDAI LUNAS' }}
            </button>

            <div class="flex items-center space-x-2">
              <button
                @click="openEditModal(bill)"
                class="btn-secondary btn-sm"
                type="button"
              >
                EDIT
              </button>
              <button
                @click="confirmDelete(bill)"
                class="btn-destructive btn-sm"
                type="button"
              >
                HAPUS
              </button>
            </div>
          </div>
        </div>
      </section>
    </main>

    <!-- Footer System Info -->
    <footer class="border-t-[5px] border-black bg-white mt-12 py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="font-mono text-xs uppercase text-gray-700">
          BERESBAYAR // VUE 3 + LARAVEL SANCTUM
        </div>
        <div class="font-mono text-xs uppercase text-black font-bold">
          [ UTS Konstruksi dan Evolusi Perangkat Lunak ]
        </div>
      </div>
    </footer>

    <!-- Form Modal (Tambah / Edit) -->
    <BillFormModal
      :is-open="isFormModalOpen"
      :is-editing="isEditing"
      :initial-data="selectedBill"
      :loading="billsStore.actionLoading"
      :errors="billsStore.validationErrors"
      :error-message="billsStore.errorMessage"
      @submit="handleFormSubmit"
      @close="closeFormModal"
    />

    <!-- Confirmation Modal (Delete) -->
    <ConfirmModal
      :is-open="isDeleteModalOpen"
      title="HAPUS TAGIHAN"
      message="Apakah Anda yakin ingin menghapus tagihan ini secara permanen?"
      :detail-text="selectedBill ? `${selectedBill.nama_tagihan} (${formatRupiah(selectedBill.jumlah)})` : ''"
      confirm-text="HAPUS PERMANEN"
      :loading="billsStore.actionLoading"
      @confirm="handleDeleteConfirm"
      @close="closeDeleteModal"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useBillsStore } from '@/stores/bills'
import Navbar from '@/components/Navbar.vue'
import BillFormModal from '@/components/BillFormModal.vue'
import ConfirmModal from '@/components/ConfirmModal.vue'

const billsStore = useBillsStore()

// View Mode: 'table' or 'grid'
const viewMode = ref('table')

// Modal States
const isFormModalOpen = ref(false)
const isEditing = ref(false)
const selectedBill = ref(null)

const isDeleteModalOpen = ref(false)

onMounted(async () => {
  await billsStore.fetchBills()
})

const formatRupiah = (val) => {
  if (val === undefined || val === null || isNaN(val)) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val)
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  try {
    const d = new Date(dateString)
    if (isNaN(d.getTime())) return dateString
    return d.toLocaleDateString('id-ID', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
    })
  } catch {
    return dateString
  }
}

const resetFilters = () => {
  billsStore.filterStatus = 'ALL'
  billsStore.searchQuery = ''
}

// Create & Edit Flow
const openCreateModal = () => {
  billsStore.clearErrors()
  selectedBill.value = null
  isEditing.value = false
  isFormModalOpen.value = true
}

const openEditModal = (bill) => {
  billsStore.clearErrors()
  selectedBill.value = { ...bill }
  isEditing.value = true
  isFormModalOpen.value = true
}

const closeFormModal = () => {
  isFormModalOpen.value = false
  selectedBill.value = null
  isEditing.value = false
  billsStore.clearErrors()
}

const handleFormSubmit = async (formData) => {
  if (isEditing.value && selectedBill.value?.id) {
    const res = await billsStore.updateBill(selectedBill.value.id, formData)
    if (res.success) {
      closeFormModal()
    }
  } else {
    const res = await billsStore.createBill(formData)
    if (res.success) {
      closeFormModal()
    }
  }
}

// Status Toggle
const toggleStatus = async (bill) => {
  await billsStore.toggleBillStatus(bill)
}

// Delete Flow
const confirmDelete = (bill) => {
  selectedBill.value = bill
  isDeleteModalOpen.value = true
}

const closeDeleteModal = () => {
  isDeleteModalOpen.value = false
  selectedBill.value = null
}

const handleDeleteConfirm = async () => {
  if (!selectedBill.value?.id) return
  const res = await billsStore.deleteBill(selectedBill.value.id)
  if (res.success) {
    closeDeleteModal()
  }
}
</script>
