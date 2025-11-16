@extends('layouts.app')

@section('title', 'Detail Barang Masuk')

@section('content')
    <div class="bg-white min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Detail Penerimaan</h1>
                    <p class="text-sm text-gray-600">ID: #{{ $receipt->id }}</p>
                </div>
                <div>
                    <a href="{{ route('incoming-goods.index') }}" class="px-3 py-2 bg-gray-100 rounded">Kembali</a>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg border mb-6 p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Supplier</p>
                        <p class="font-medium">{{ $receipt->supplier->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Kedatangan</p>
                        <p class="font-medium">{{ optional($receipt->receipt_date)->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Bongkar</p>
                        <p class="font-medium">{{ optional($receipt->unloading_date)->format('d/m/Y') }}</p>
                    </div>
                </div>
                @if ($receipt->notes)
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">Catatan</p>
                        <p class="text-sm">{{ $receipt->notes }}</p>
                    </div>
                @endif
            </div>

            <div class="bg-white shadow rounded-lg border overflow-hidden">
                <div class="px-6 py-4 border-b">
                    <h2 class="font-semibold text-gray-900">Item Penerimaan</h2>
                </div>
                <div class="p-6">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500">
                            <tr>
                                <th class="pb-2">Grade</th>
                                <th class="pb-2">Berat Supplier (gr)</th>
                                <th class="pb-2">Berat Gudang (gr)</th>
                                <th class="pb-2">Selisih (gr)</th>
                                <th class="pb-2">Kadar Air (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($receipt->receiptItems as $item)
                                <tr class="border-t">
                                    <td class="py-3">{{ $item->gradeSupplier->name ?? '-' }}</td>
                                    <td class="py-3">{{ number_format($item->supplier_weight_grams) }}</td>
                                    <td class="py-3">{{ number_format($item->warehouse_weight_grams) }}</td>
                                    <td class="py-3">
                                        @if ($item->difference_grams < 0)
                                            <span
                                                class="text-red-600 font-medium">{{ number_format($item->difference_grams) }}</span>
                                            <span class="text-xs text-red-500">(susut)</span>
                                        @elseif($item->difference_grams > 0)
                                            <span
                                                class="text-green-600 font-medium">+{{ number_format($item->difference_grams) }}</span>
                                            <span class="text-xs text-green-500">(bertambah)</span>
                                        @else
                                            <span class="text-gray-600">{{ number_format($item->difference_grams) }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3">{{ $item->moisture_percentage ?? ($item->moisture_percent ?? '-') }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection
