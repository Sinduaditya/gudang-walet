@extends('layouts.app')

@section('title', 'Detail Grading Barang')

@section('content')
    <div class="bg-white min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-8">

            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Detail Grading</h1>
                    <p class="text-sm text-gray-600">ID Grading: #{{ $grading->id }}</p>
                </div>
                <div>
                    <a href="{{ route('grading-goods.index') }}" class="px-3 py-2 bg-gray-100 rounded">Kembali</a>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg border mb-6 p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <div>
                        <p class="text-sm text-gray-500">Tanggal Grading</p>
                        <p class="font-medium">
                            {{ \Carbon\Carbon::parse($grading->grading_date)->format('d/m/Y') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Grade Perusahaan</p>
                        <p class="font-medium">{{ $grading->gradeCompany->name ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Jumlah Item</p>
                        <p class="font-medium">{{ number_format($grading->quantity) }}</p>
                    </div>

                </div>

                @if ($grading->notes)
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">Catatan</p>
                        <p class="text-sm">{{ $grading->notes }}</p>
                    </div>
                @endif
            </div>

            <div class="bg-white shadow rounded-lg border overflow-hidden">
                <div class="px-6 py-4 border-b">
                    <h2 class="font-semibold text-gray-900">Detail Perhitungan Grading</h2>
                </div>

                <div class="p-6">
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs text-gray-500">
                            <tr>
                                <th class="pb-2">Grade Supplier</th>
                                <th class="pb-2">Berat Gudang (gr)</th>
                                <th class="pb-2">Berat Setelah Grading (gr)</th>
                                <th class="pb-2">% Selisih</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr class="border-t">
                                <td class="py-3">
                                    {{ $grading->receiptItem->gradeSupplier->name ?? '-' }}
                                </td>
                                <td class="py-3">
                                    {{ number_format($grading->receiptItem->warehouse_weight_grams ?? 0) }}
                                </td>
                                <td class="py-3">
                                    {{ number_format($grading->weight_grams ?? 0) }}
                                </td>
                                <td class="py-3">
                                    <span class="{{ $grading->percentage_difference > 1.5 ? 'text-red-600 font-semibold' : '' }}">
                                        {{ number_format($grading->percentage_difference, 2) }}%
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
@endsection
