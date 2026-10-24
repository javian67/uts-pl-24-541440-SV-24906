import { defineStore } from 'pinia'
import apiClient from '@/api/axios'

export const useBillsStore = defineStore('bills', {
  state: () => ({
    bills: [],
    loading: false,
    actionLoading: false,
    errorMessage: null,
    validationErrors: {},
    filterStatus: 'ALL', // 'ALL' | 'Belum Dibayar' | 'Sudah Dibayar'
    searchQuery: '',
  }),

  getters: {
    filteredBills: (state) => {
      let list = state.bills

      if (state.filterStatus !== 'ALL') {
        list = list.filter((b) => b.status === state.filterStatus)
      }

      if (state.searchQuery.trim() !== '') {
        const q = state.searchQuery.toLowerCase()
        list = list.filter(
          (b) =>
            b.nama_tagihan?.toLowerCase().includes(q) ||
            b.kategori?.toLowerCase().includes(q) ||
            b.catatan?.toLowerCase().includes(q)
        )
      }

      return list
    },

    totalAmount: (state) => {
      return state.bills.reduce((sum, b) => sum + Number(b.jumlah || 0), 0)
    },

    unpaidAmount: (state) => {
      return state.bills
        .filter((b) => b.status === 'Belum Dibayar')
        .reduce((sum, b) => sum + Number(b.jumlah || 0), 0)
    },

    paidAmount: (state) => {
      return state.bills
        .filter((b) => b.status === 'Sudah Dibayar')
        .reduce((sum, b) => sum + Number(b.jumlah || 0), 0)
    },

    unpaidCount: (state) => {
      return state.bills.filter((b) => b.status === 'Belum Dibayar').length
    },

    paidCount: (state) => {
      return state.bills.filter((b) => b.status === 'Sudah Dibayar').length
    },
  },

  actions: {
    clearErrors() {
      this.errorMessage = null
      this.validationErrors = {}
    },

    async fetchBills() {
      this.loading = true
      this.clearErrors()

      try {
        const response = await apiClient.get('/bills')
        // In Laravel API resource, data is often wrapped in response.data.data or direct array in response.data
        const rawData = response.data
        if (Array.isArray(rawData)) {
          this.bills = rawData
        } else if (rawData && Array.isArray(rawData.data)) {
          this.bills = rawData.data
        } else if (rawData && typeof rawData === 'object' && rawData.bills) {
          this.bills = rawData.bills
        } else {
          this.bills = []
        }
      } catch (error) {
        if (error.response?.status !== 401) {
          this.errorMessage = error.response?.data?.message || 'Gagal memuat daftar tagihan.'
        }
      } finally {
        this.loading = false
      }
    },

    async createBill(payload) {
      this.actionLoading = true
      this.clearErrors()

      try {
        const formattedPayload = {
          ...payload,
          jumlah: Number(payload.jumlah),
        }

        const response = await apiClient.post('/bills', formattedPayload)
        const newBill = response.data.data || response.data

        // If returned new bill has id, add or refresh
        if (newBill && newBill.id) {
          this.bills.unshift(newBill)
        } else {
          await this.fetchBills()
        }

        return { success: true, data: newBill }
      } catch (error) {
        this.handleError(error)
        return { success: false, error }
      } finally {
        this.actionLoading = false
      }
    },

    async updateBill(id, payload) {
      this.actionLoading = true
      this.clearErrors()

      try {
        const formattedPayload = {
          ...payload,
          jumlah: Number(payload.jumlah),
        }

        const response = await apiClient.put(`/bills/${id}`, formattedPayload)
        const updated = response.data.data || response.data

        const index = this.bills.findIndex((b) => b.id === id)
        if (index !== -1 && updated && updated.id) {
          this.bills[index] = { ...this.bills[index], ...updated }
        } else {
          await this.fetchBills()
        }

        return { success: true, data: updated }
      } catch (error) {
        this.handleError(error)
        return { success: false, error }
      } finally {
        this.actionLoading = false
      }
    },

    async toggleBillStatus(bill) {
      const newStatus = bill.status === 'Sudah Dibayar' ? 'Belum Dibayar' : 'Sudah Dibayar'
      return await this.updateBill(bill.id, {
        ...bill,
        status: newStatus,
      })
    },

    async deleteBill(id) {
      this.actionLoading = true
      this.clearErrors()

      try {
        await apiClient.delete(`/bills/${id}`)
        this.bills = this.bills.filter((b) => b.id !== id)
        return { success: true }
      } catch (error) {
        this.handleError(error)
        return { success: false, error }
      } finally {
        this.actionLoading = false
      }
    },

    handleError(error) {
      if (error.response) {
        const status = error.response.status
        const data = error.response.data

        if (status === 422) {
          this.validationErrors = data.errors || {}
          this.errorMessage = data.message || 'Validasi formulir tagihan gagal.'
        } else {
          this.errorMessage = data.message || `Terjadi kesalahan (Kode: ${status}).`
        }
      } else {
        this.errorMessage = 'Tidak dapat terhubung ke server backend.'
      }
    },
  },
})
