@extends('layouts.app')

@section('title', 'Edit Data Grading')

@section('content')
<div class="bg-gray-50 min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Edit Data Grading</h1>
                <p class="mt-1 text-sm text-gray-600">ID Grading: {{ $sortingResult->id }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('grading-goods.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-md hover:bg-gray-100 text-sm">
                    Kembali
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('grading-goods.update', $sortingResult->id) }}" id="editForm" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white shadow-md border rounded-lg overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Data Utama</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div>
                            <label for="grading_date" class="block text-sm font-medium text-gray-700">Tanggal Grading</label>
                            <input type="date" name="grading_date" id="grading_date"
                                   value="{{ old('grading_date', $sortingResult->grading_date ? \Carbon\Carbon::parse($sortingResult->grading_date)->format('Y-m-d') : '') }}"
                                   class="mt-1 block w-full sm:text-sm border rounded-md bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 @error('grading_date') border-red-500 @enderror"
                                   required>
                            @error('grading_date')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="receipt_item_id" class="block text-sm font-medium text-gray-700">Nama Grade Supplier (Pilih Item)</label>
                            <select name="receipt_item_id" id="receipt_item_id"
                                    class="mt-1 block w-full sm:text-sm border rounded-md bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 @error('receipt_item_id') border-red-500 @enderror"
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
                            <label for="tgl_datang_display" class="block text-sm font-medium text-gray-500">Tanggal Kedatangan</label>
                            <input type="text" id="tgl_datang_display"
                                   value="{{ $sortingResult->receiptItem->purchaseReceipt->receipt_date ? \Carbon\Carbon::parse($sortingResult->receiptItem->purchaseReceipt->receipt_date)->format('d/m/Y') : '' }}"
                                   class="mt-1 block w-full sm:text-sm border rounded-md bg-gray-50 text-gray-800 px-3 py-2 font-medium"
                                   readonly>
                        </div>

                        <div>
                            <label for="berat_gudang_display" class="block text-sm font-medium text-gray-500">Berat Gudang (g)</label>
                            <input type="text" id="berat_gudang_display"
                                   value="{{ $sortingResult->receiptItem->warehouse_weight_grams ?? 0 }}"
                                   class="mt-1 block w-full sm:text-sm border rounded-md bg-gray-50 text-gray-800 px-3 py-2 font-medium"
                                   readonly>
                        </div>

                        <div>
                            <label for="quantity" class="block text-sm font-medium text-gray-700">Jumlah Item</label>
                            <input type="number" name="quantity" id="quantity" min="0"
                                   value="{{ old('quantity', $sortingResult->quantity) }}"
                                   class="mt-1 block w-full sm:text-sm border rounded-md bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 @error('quantity') border-red-500 @enderror"
                                   required>
                            @error('quantity')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="grade_company_name" class="block text-sm font-medium text-gray-700">Nama Grade Perusahaan</label>
                            <input type="text" name="grade_company_name" id="grade_company_name"
                                   value="{{ old('grade_company_name', optional($sortingResult->gradeCompany)->name) }}"
                                   class="mt-1 block w-full sm:text-sm border rounded-md bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 @error('grade_company_name') border-red-500 @enderror"
                                   required>
                            @error('grade_company_name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="weight_grams" class="block text-sm font-medium text-gray-700">Berat setelah Grading (g)</label>
                            <input type="number" step="1" min="0" name="weight_grams" id="weight_grams"
                                   value="{{ old('weight_grams', $sortingResult->weight_grams) }}"
                                   class="mt-1 block w-full sm:text-sm border rounded-md bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 @error('weight_grams') border-red-500 @enderror"
                                   required>
                            @error('weight_grams')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="selisih_display" class="block text-sm font-medium text-gray-500">% Selisih</label>
                            <input type="text" id="selisih_display"
                                   value="{{ $sortingResult->percentage_difference !== null ? number_format($sortingResult->percentage_difference, 2) . ' %' : '0 %' }}"
                                   class="mt-1 block w-full sm:text-sm border rounded-md bg-gray-50 px-3 py-2 font-semibold text-gray-800"
                                   readonly>
                            <p id="selisih_hint" class="mt-1 text-xs text-gray-500">Warna merah menandakan selisih lebih dari 1.5%.</p>
                        </div>

                        <div class="md:col-span-2">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Catatan</label>
                            <textarea name="notes" id="notes" rows="3"
                                      class="mt-1 block w-full sm:text-sm border rounded-md bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 @error('notes') border-red-500 @enderror"
                                      placeholder="Catatan tambahan (opsional)">{{ old('notes', $sortingResult->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                <div class="bg-gray-50 p-4 border-t flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3">
                    <a href="{{ route('grading-goods.index') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-md border border-gray-200 bg-white text-sm text-gray-700 hover:bg-gray-100 w-full sm:w-auto">
                        Batal
                    </a>
                    <button type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm w-full sm:w-auto">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemSelect = document.getElementById('receipt_item_id');
    const beratGradingInput = document.getElementById('weight_grams');

    const tglDatangDisplay = document.getElementById('tgl_datang_display');
    const beratGudangDisplay = document.getElementById('berat_gudang_display');
    const selisihDisplay = document.getElementById('selisih_display');

    function setSelisihStyle(value) {
        // Remove color classes
        selisihDisplay.classList.remove('text-red-600', 'text-green-600', 'text-gray-800');
        // Apply color: red if abs > 1.5, green if <=1.5 and >0, neutral if zero
        if (Math.abs(value) > 1.5) {
            selisihDisplay.classList.add('text-red-600');
        } else if (Math.abs(value) > 0) {
            selisihDisplay.classList.add('text-green-600');
        } else {
            selisihDisplay.classList.add('text-gray-800');
        }
    }

    function kalkulasiSelisih() {
        const beratGudang = parseFloat(beratGudangDisplay.value) || 0;
        const beratGrading = parseFloat(beratGradingInput.value) || 0;

        let selisih = 0;
        if (beratGudang > 0) {
            selisih = ((beratGudang - beratGrading) / beratGudang) * 100;
        }

        selisihDisplay.value = selisih.toFixed(2) + ' %';
        setSelisihStyle(selisih);
    }

    // Saat item berubah: update tgl datang & berat gudang
    itemSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const tglDatang = selectedOption.dataset.tglDatang || 'N/A';
        const beratGudang = selectedOption.dataset.beratGudang || 0;

        tglDatangDisplay.value = tglDatang;
        beratGudangDisplay.value = beratGudang;

        kalkulasiSelisih();
    });

    // Saat berat grading diketik
    beratGradingInput.addEventListener('input', kalkulasiSelisih);

    // Kalkulasi awal
    kalkulasiSelisih();
});
</script>
@endsection
