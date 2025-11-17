@extends('layouts.app')

@section('title', 'Edit Data Grading')

@section('content')
<div class="bg-white min-h-screen">
    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Edit Data Grading</h1>
                <p class="mt-1 text-sm text-gray-600">ID Grading: {{ $sortingResult->id }}</p>
            </div>
            <a href="{{ route('grading-goods.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm">
                Kembali
            </a>
        </div>

        <form method="POST" action="{{ route('grading-goods.update', $sortingResult->id) }}" id="editForm">
            @csrf
            @method('PUT')

            <div class="bg-white shadow-sm border rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Data Utama</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <label for="grading_date" class="block text-sm font-medium text-gray-700">Tanggal Grading</label>
                            <input type="date" name="grading_date" id="grading_date"
                                   value="{{ old('grading_date', $sortingResult->grading_date ? \Carbon\Carbon::parse($sortingResult->grading_date)->format('Y-m-d') : '') }}"
                                   class="mt-1 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md @error('grading_date') border-red-500 @enderror"
                                   required>
                            @error('grading_date')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="receipt_item_id" class="block text-sm font-medium text-gray-700">Nama Grade Supplier (Pilih Item)</label>
                            <select name="receipt_item_id" id="receipt_item_id"
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('receipt_item_id') border-red-500 @enderror"
                                    required>
                                <option value="">-- Pilih Item --</option>
                                @foreach($allReceiptItems as $item)
                                    <option value="{{ $item->id }}"
                                        data-tgl-datang="{{ $item->receipt_date ? \Carbon\Carbon::parse($item->receipt_date)->format('d/m/Y') : 'N/A' }}"
                                        data-berat-gudang="{{ $item->warehouse_weight_grams ?? 0 }}"
                                        {{ old('receipt_item_id', $sortingResult->receipt_item_id) == $item->id ? 'selected' : '' }}
                                    >
                                        {{ $item->grade_supplier_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('receipt_item_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tgl_datang_display" class="block text-sm font-medium text-gray-500">Tanggal Kedatangan (Otomatis)</label>
                            <input type="text" id="tgl_datang_display"
                                   value="{{ $sortingResult->receiptItem->purchaseReceipt->receipt_date ? \Carbon\Carbon::parse($sortingResult->receiptItem->purchaseReceipt->receipt_date)->format('d/m/Y') : '' }}"
                                   class="mt-1 shadow-sm block w-full sm:text-sm border-gray-300 rounded-md bg-gray-100 text-gray-900 font-semibold"
                                   readonly>
                        </div>

                        <div>
                            <label for="berat_gudang_display" class="block text-sm font-medium text-gray-500">Berat Gudang (g) (Otomatis)</label>
                            <input type="text" id="berat_gudang_display"
                                   value="{{ $sortingResult->receiptItem->warehouse_weight_grams ?? 0 }}"
                                   class="mt-1 shadow-sm block w-full sm:text-sm border-gray-300 rounded-md bg-gray-100 text-gray-900 font-semibold"
                                   readonly>
                        </div>

                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700">Jumlah Item</label>
                            <input type="number" name="quantity" id="quantity"
                                   value="{{ old('quantity', $sortingResult->quantity) }}"
                                   class="mt-1 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md @error('quantity') border-red-500 @enderror"
                                   required>
                            @error('quantity')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="grade_company_name" class="block text-sm font-medium text-gray-700">Nama Grade Perusahaan</label>
                            <input type="text" name="grade_company_name" id="grade_company_name"
                                   value="{{ old('grade_company_name', optional($sortingResult->gradeCompany)->name) }}"
                                   class="mt-1 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md @error('grade_company_name') border-red-500 @enderror"
                                   required>
                            @error('grade_company_name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="weight_grams" class="block text-sm font-medium text-gray-700">Berat setelah Grading (g)</label>
                            <input type="number" step="0.01" name="weight_grams" id="weight_grams"
                                   value="{{ old('weight_grams', $sortingResult->weight_grams) }}"
                                   class="mt-1 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md @error('weight_grams') border-red-500 @enderror"
                                   required>
                            @error('weight_grams')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="selisih_display" class="block text-sm font-medium text-gray-500">% Selisih (Otomatis)</label>
                            <input type="text" id="selisih_display"
                                   value="{{ $sortingResult->percentage_difference !== null ? number_format($sortingResult->percentage_difference, 2) . ' %' : '0 %' }}"
                                   class="mt-1 shadow-sm block w-full sm:text-sm border-gray-300 rounded-md bg-gray-100 text-gray-900 font-semibold"
                                   readonly>
                        </div>

                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Catatan</label>
                            <textarea name="notes" id="notes" rows="3"
                                      class="mt-1 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md @error('notes') border-red-500 @enderror"
                                      placeholder="Catatan tambahan (opsional)">{{ old('notes', $sortingResult->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-6 p-6 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                    <button type="submit"
                        class="inline-flex items-center justify-center px-4 py-2.5 bg-blue-500 text-white rounded-md hover:bg-blue-700 w-full sm:w-auto">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ambil elemen-elemen
    const itemSelect = document.getElementById('receipt_item_id');
    const beratGradingInput = document.getElementById('weight_grams');

    const tglDatangDisplay = document.getElementById('tgl_datang_display');
    const beratGudangDisplay = document.getElementById('berat_gudang_display');
    const selisihDisplay = document.getElementById('selisih_display');

    // Fungsi untuk menghitung ulang selisih
    function kalkulasiSelisih() {
        // Ambil nilai 'berat_gudang' dari input display (yang sudah di-update oleh JS)
        const beratGudang = parseFloat(beratGudangDisplay.value) || 0;

        // Ambil nilai 'berat_grading' dari input yang bisa diedit
        const beratGrading = parseFloat(beratGradingInput.value) || 0;

        let selisih = 0;
        if (beratGudang > 0) {
            selisih = ((beratGudang - beratGrading) / beratGudang) * 100;
        }

        // Update display % selisih
        // Kita juga tambahkan logika warna merah
        selisihDisplay.value = selisih.toFixed(2) + ' %';
        if (Math.abs(selisih) > 1.5) {
            selisihDisplay.classList.add('text-red-600');
        } else {
            selisihDisplay.classList.remove('text-red-600');
        }
    }

    // 1. Listener saat "Nama Grade Supplier" (Item) diganti
    itemSelect.addEventListener('change', function() {
        // Ambil <option> yang sedang dipilih
        const selectedOption = this.options[this.selectedIndex];

        // Ambil data-attributes dari option tersebut
        const tglDatang = selectedOption.dataset.tglDatang || 'N/A';
        const beratGudang = selectedOption.dataset.beratGudang || 0;

        // Update field read-only
        tglDatangDisplay.value = tglDatang;
        beratGudangDisplay.value = beratGudang;

        // Hitung ulang selisih
        kalkulasiSelisih();
    });

    // 2. Listener saat "Berat setelah Grading" diketik
    beratGradingInput.addEventListener('input', function() {
        // Hitung ulang selisih
        kalkulasiSelisih();
    });

    // 3. Jalankan kalkulasi saat halaman pertama kali dimuat (untuk jaga-jaga)
    kalkulasiSelisih();
});
</script>
@endsection
