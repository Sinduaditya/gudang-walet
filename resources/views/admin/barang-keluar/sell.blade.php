@extends('layouts.app')

@section('title', 'Penjualan Langsung')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Header with Back Button and History Tab Toggle --}}
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Penjualan Langsung</h1>
                    <p class="mt-1 text-sm text-gray-600">Catat penjualan barang ke customer</p>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Tab Toggle Button --}}
                    <button type="button" onclick="toggleHistoryTab()" id="historyToggleBtn"
                        class="inline-flex items-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span id="historyToggleText">Daftar Penjualan Langsung</span>
                    </button>

                    {{-- Back Button --}}
                    <a href="{{ route('barang.keluar.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>


            {{-- Tab Content Container --}}
            <div class="space-y-8">

                {{-- Form Tab (Default Active) --}}
                <div id="formTab" class="tab-content">
                    <div class="bg-white rounded-xl shadow-md border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Form Penjualan</h2>
                            <p class="text-sm text-gray-500 mt-1">Lengkapi data penjualan barang</p>
                        </div>

                        <form action="{{ route('barang.keluar.sell.store') }}" method="POST" class="p-6">
                            @csrf

                            <div class="space-y-6">
                                {{-- Grade Select (changed from searchable to select) --}}
                                <div>
                                    <label class="block font-semibold text-gray-700 mb-2">
                                        Grade Perusahaan <span class="text-red-500">*</span>
                                    </label>

                                    <select name="grade_company_id" id="grade_company_id" required
                                        class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">-- Pilih Grade --</option>
                                        @foreach($gradesWithStock as $g)
                                            <option value="{{ $g['id'] }}" 
                                                data-stock="{{ $g['total_stock_grams'] }}"
                                                {{ old('grade_company_id') == $g['id'] ? 'selected' : '' }}>
                                                {{ $g['name'] }} (Stok: {{ number_format($g['total_stock_grams'], 0, ',', '.') }} gr)
                                            </option>
                                        @endforeach
                                    </select>

                                    {{-- Stock hint --}}
                                    <p id="grade-stock-hint" class="mt-2 text-sm text-gray-500">
                                        Stok tersedia: <span id="grade-stock-value" class="font-semibold">-</span>
                                    </p>

                                    @error('grade_company_id')
                                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Hidden Location (default Gudang Utama) --}}
                                <input type="hidden" name="location_id" id="location_id" value="{{ $defaultLocation->id ?? 1 }}">

                                {{-- Weight & Date --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block font-semibold text-gray-700 mb-2">
                                            Berat Penjualan (gram) <span class="text-red-500">*</span>
                                        </label>
                                        <div class="flex gap-2">
                                            <input type="number" name="weight_grams" id="weight_grams" 
                                                step="0.01" min="0.01" required
                                                value="{{ old('weight_grams') }}"
                                                class="flex-1 border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                                placeholder="Masukkan berat dalam gram">
                                            <button type="button" onclick="checkStock()" id="btnCheckStock"
                                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                                Cek Stok
                                            </button>
                                        </div>
                                        <p id="stock-check-result" class="mt-2 text-sm hidden"></p>
                                        @error('weight_grams')
                                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block font-semibold text-gray-700 mb-2">Tanggal Penjualan</label>
                                        <input type="date" name="transaction_date" 
                                            value="{{ old('transaction_date', date('Y-m-d')) }}"
                                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        @error('transaction_date')
                                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Notes --}}
                                <div>
                                    <label class="block font-semibold text-gray-700 mb-2 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Catatan
                                        <span class="text-gray-400 font-normal text-xs">(Opsional)</span>
                                    </label>
                                    <textarea name="notes" rows="3" 
                                        placeholder="Tambahkan catatan atau keterangan penjualan..."
                                        class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Form Actions --}}
                            <div class="flex items-center gap-3 pt-6 border-t border-gray-200 mt-6">
                                <button type="reset"
                                    class="flex-1 inline-flex items-center justify-center px-4 py-3 border-2 border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 transition-all">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Reset
                                </button>
                                <button type="submit"
                                    class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 focus:ring-4 focus:ring-blue-300 transition-all duration-200 shadow-lg hover:shadow-xl">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Simpan Penjualan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- History Tab (Hidden by default) --}}
                <div id="historyTab" class="tab-content hidden">
                    <div class="bg-white rounded-xl shadow-md border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">Daftar Penjualan Langsung</h2>
                                    <p class="text-sm text-gray-500 mt-1">Daftar transaksi penjualan ke customer</p>
                                </div>
                                <button onclick="toggleHistoryTab()"
                                    class="text-sm text-gray-600 hover:text-gray-800 flex items-center gap-1 px-3 py-1.5 hover:bg-gray-100 rounded transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Tutup
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Tanggal
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Grade
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Lokasi
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Stok Berkurang
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($penjualanTransactions as $tx)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $tx->gradeCompany->name ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $tx->location->name ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                                {{ number_format(abs($tx->quantity_change_grams), 2) }} gr
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                                <div class="flex items-center justify-center gap-3">
                                                    <a href="{{ route('barang.keluar.sell.edit', $tx->id) }}" 
                                                        class="text-blue-600 hover:text-blue-800 font-medium">
                                                        Edit
                                                    </a>

                                                    <form action="{{ route('barang.keluar.sell.destroy', $tx->id) }}" 
                                                        method="POST" class="inline" 
                                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi penjualan ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                            class="text-red-600 hover:text-red-800 font-medium">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                                <div class="flex flex-col items-center">
                                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                    <p class="text-lg font-medium text-gray-900 mb-1">Belum ada data penjualan</p>
                                                    <p class="text-sm text-gray-500">Transaksi penjualan yang sudah dicatat akan muncul di sini</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($penjualanTransactions->hasPages())
                            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-600">
                                        Menampilkan {{ $penjualanTransactions->firstItem() }} -
                                        {{ $penjualanTransactions->lastItem() }} dari
                                        {{ $penjualanTransactions->total() }} transaksi
                                    </div>
                                    <div>
                                        {{ $penjualanTransactions->links() }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Toggle History Tab Function
            function toggleHistoryTab() {
                const formTab = document.getElementById('formTab');
                const historyTab = document.getElementById('historyTab');
                const toggleBtn = document.getElementById('historyToggleBtn');
                const toggleText = document.getElementById('historyToggleText');

                if (historyTab.classList.contains('hidden')) {
                    // Show history, hide form
                    formTab.classList.add('hidden');
                    historyTab.classList.remove('hidden');
                    toggleText.textContent = 'Kembali ke Form';
                    toggleBtn.classList.remove('bg-blue-50', 'text-blue-700', 'border-blue-300');
                    toggleBtn.classList.add('bg-gray-100', 'text-gray-700', 'border-gray-300');
                } else {
                    // Show form, hide history
                    historyTab.classList.add('hidden');
                    formTab.classList.remove('hidden');
                    toggleText.textContent = 'Daftar Penjualan Langsung';
                    toggleBtn.classList.remove('bg-gray-100', 'text-gray-700', 'border-gray-300');
                    toggleBtn.classList.add('bg-blue-50', 'text-blue-700', 'border-blue-300');
                }
            }

            // Grade Selection and Stock Check (simplified for select)
            const gradeSelect = document.getElementById('grade_company_id');
            const gradeStockValue = document.getElementById('grade-stock-value');

            gradeSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    const stock = selectedOption.dataset.stock || 0;
                    gradeStockValue.textContent = new Intl.NumberFormat('id-ID').format(stock) + ' gr';
                    gradeStockValue.classList.remove('text-red-600');
                    gradeStockValue.classList.add('text-green-600');
                    
                    // Auto fetch exact stock from server
                    fetchStockInfo(selectedOption.value);
                } else {
                    gradeStockValue.textContent = '-';
                    gradeStockValue.classList.remove('text-green-600', 'text-red-600');
                }
            });

            function fetchStockInfo(gradeId) {
                fetch(`{{ route('barang.keluar.sell.stock_check') }}?grade_company_id=${gradeId}&location_id={{ $defaultLocation->id }}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.ok) {
                            gradeStockValue.textContent = new Intl.NumberFormat('id-ID').format(data.available_grams) + ' gr';
                            gradeStockValue.classList.remove('text-red-600');
                            gradeStockValue.classList.add('text-green-600');
                        } else {
                            gradeStockValue.textContent = 'Error';
                            gradeStockValue.classList.remove('text-green-600');
                            gradeStockValue.classList.add('text-red-600');
                        }
                    })
                    .catch(() => {
                        gradeStockValue.textContent = 'Error cek stok';
                        gradeStockValue.classList.remove('text-green-600');
                        gradeStockValue.classList.add('text-red-600');
                    });
            }

            function checkStock() {
                const gradeId = document.getElementById('grade_company_id').value;
                const weight = parseFloat(document.getElementById('weight_grams').value || 0);
                const resultEl = document.getElementById('stock-check-result');
                
                if (!gradeId) {
                    showStockResult('Pilih grade terlebih dahulu.', 'error');
                    return;
                }

                if (weight <= 0) {
                    showStockResult('Masukkan berat yang valid.', 'error');
                    return;
                }

                // Show loading
                showStockResult('Mengecek stok...', 'info');

                fetch(`{{ route('barang.keluar.sell.stock_check') }}?grade_company_id=${gradeId}&location_id={{ $defaultLocation->id }}`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.ok) {
                            showStockResult('Gagal mengecek stok.', 'error');
                            return;
                        }

                        const available = parseFloat(data.available_grams);
                        if (available >= weight) {
                            showStockResult(
                                `✓ Stok mencukupi! Tersedia ${new Intl.NumberFormat('id-ID').format(available)} gram.`, 
                                'success'
                            );
                        } else {
                            showStockResult(
                                `⚠ Stok tidak mencukupi! Hanya tersedia ${new Intl.NumberFormat('id-ID').format(available)} gram.`, 
                                'error'
                            );
                        }
                    })
                    .catch(() => {
                        showStockResult('Gagal mengecek stok. Silakan coba lagi.', 'error');
                    });
            }

            function showStockResult(message, type) {
                const resultEl = document.getElementById('stock-check-result');
                resultEl.classList.remove('hidden', 'text-red-600', 'text-green-600', 'text-blue-600');
                
                switch(type) {
                    case 'success':
                        resultEl.classList.add('text-green-600');
                        break;
                    case 'error':
                        resultEl.classList.add('text-red-600');
                        break;
                    case 'info':
                        resultEl.classList.add('text-blue-600');
                        break;
                }
                
                resultEl.textContent = message;
            }

            // Check if there's a page parameter (from pagination), if yes, show history tab
            document.addEventListener('DOMContentLoaded', function() {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('page')) {
                    toggleHistoryTab();
                }

                // Set initial stock if grade is pre-selected
                const selectedGrade = gradeSelect.value;
                if (selectedGrade) {
                    fetchStockInfo(selectedGrade);
                }
            });

            // Reset form handler
            document.querySelector('button[type="reset"]').addEventListener('click', function() {
                setTimeout(() => {
                    gradeStockValue.textContent = '-';
                    gradeStockValue.classList.remove('text-green-600', 'text-red-600');
                    document.getElementById('stock-check-result').classList.add('hidden');
                }, 10);
            });
        </script>
    @endpush
@endsection