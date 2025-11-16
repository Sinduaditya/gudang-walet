@extends('layouts.app') @section('title', 'Input Grading - Step 2')

@section('content')
<div class="bg-white min-h-screen">
    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Input Grading - Step 2</h1>
                <p class="mt-1 text-sm text-gray-600">Lengkapi hasil grading untuk item yang dipilih.</p>
            </div>
            <a href="{{ route('grading-goods.step1') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm">
                Kembali
            </a>
        </div>

        <div class="bg-gray-50 shadow-sm border rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Item</h3>
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Tgl Grading (Step 1)</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $sortingResult->grading_date ? \Carbon\Carbon::parse($sortingResult->grading_date)->format('d/m/Y') : '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Grade Supplier (dari Item)</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ optional($sortingResult->receiptItem->gradeSupplier)->name ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Tgl Datang (Auto-terisi)</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ optional($sortingResult->receiptItem->purchaseReceipt)->receipt_date ? \Carbon\Carbon::parse($sortingResult->receiptItem->purchaseReceipt->receipt_date)->format('d/m/Y') : '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Berat Gudang (Auto-terisi)</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-bold">{{ $sortingResult->receiptItem->warehouse_weight_grams ?? '0' }} gram</dd>
                </div>
            </dl>
        </div>

        <form method="POST" action="{{ route('grading-goods.step2.store', ['id' => $sortingResult->id]) }}">
            @csrf
            <div class="bg-white shadow-sm border rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label for="grade_company_name" class="block text-sm font-medium text-gray-700">Nama Grade Perusahaan</label>
                        <input type="text" name="grade_company_name" id="grade_company_name"
                               value="{{ old('grade_company_name', optional($sortingResult->gradeCompany)->name) }}"
                               class="mt-1 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md @error('grade_company_name') border-red-500 @enderror"
                               placeholder="Contoh: Grade A Super" required>
                        @error('grade_company_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="weight_grams" class="block text-sm font-medium text-gray-700">Berat setelah Grading (gram)</label>
                        <input type="number" step="0.01" name="weight_grams" id="weight_grams"
                               value="{{ old('weight_grams', $sortingResult->weight_grams) }}"
                               class="mt-1 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md @error('weight_grams') border-red-500 @enderror"
                               placeholder="Contoh: 1500.50" required>
                        @error('weight_grams')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700">Jumlah Item</label>
                        <input type="number" name="quantity" id="quantity"
                               value="{{ old('quantity', $sortingResult->quantity) }}"
                               class="mt-1 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md @error('quantity') border-red-500 @enderror"
                               placeholder="Contoh: 100" required>
                        @error('quantity')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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

                <div class="mt-6 pt-6 border-t border-gray-200">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-700">
                        Simpan Hasil Grading
                    </button>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection
