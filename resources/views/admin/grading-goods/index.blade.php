@extends('layouts.app')

@section('title', 'Data Grading Barang')

@section('content')
    <div class="bg-white min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Data Grading Barang</h1>
                    <p class="mt-1 text-sm text-gray-600">Daftar hasil grading perusahaan</p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('grading-goods.export') }}" class="flex items-center text-sm text-gray-600 hover:text-gray-800">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" />
                        </svg>
                        Download as Excel
                    </a>

                    <a href="{{ route('grading-goods.step1') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Input Grading Barang
                    </a>
                </div>
            </div>

            <div class="bg-white shadow-sm border rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Tgl Grading</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Nama Grade Supplier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Nama Grade Perusahaan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Tgl Kedatangan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Jumlah Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Berat Gudang (g)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Berat setelah Grading (g)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">% Selisih</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Catatan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($gradings as $i => $grading)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $i + 1 }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $grading->grading_date ? \Carbon\Carbon::parse($grading->grading_date)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $grading->grade_supplier_name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $grading->grade_company_name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $grading->receipt_date ? \Carbon\Carbon::parse($grading->receipt_date)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $grading->quantity ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $grading->warehouse_weight_grams !== null ? number_format($grading->warehouse_weight_grams) : '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $grading->weight_grams !== null ? number_format($grading->weight_grams) : '-' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($grading->percentage_difference !== null)
                                            @php
                                                // Kita pakai 'abs()' (absolute value)
                                                // agar selisih negatif (misal -2%) juga terhitung
                                                $selisih = abs($grading->percentage_difference);
                                            @endphp

                                            <span class="{{ $selisih > 1.5 ? 'text-red-600 font-bold' : 'text-gray-900' }}">
                                                {{ number_format($grading->percentage_difference, 2) . ' %' }}
                                            </span>
                                        @else
                                            <span class="text-gray-900">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $grading->notes ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div>
                                            {{-- Tombol Edit --}}
                                            <a href="{{ route('grading-goods.edit', $grading->id) }}"
                                            class="text-blue-600 hover:text-blue-900 font-medium">
                                            Edit
                                            </a>

                                            {{-- Tombol Delete --}}
                                            <form action="{{ route('grading-goods.destroy', $grading->id) }}"
                                                method="POST"
                                                class="mt-1" {{-- Tambahkan margin top di sini --}}
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-medium">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                        Belum ada data grading.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Jika ingin pagination, pastikan service mengembalikan paginator --}}
            </div>
        </div>
    </div>
@endsection
