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
                    <a href="{{ route('grading-goods.export') }}"
                        class="flex items-center text-sm text-gray-600 hover:text-gray-800 bg-green-50 hover:bg-green-100 px-3 py-2 rounded-md border border-green-200">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" />
                        </svg>
                        Export as Excel
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

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                <form method="GET" action="{{ route('grading-goods.index') }}"
                    class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">

                    <div class="flex flex-col sm:flex-row gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                            <select name="month"
                                class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Semua Bulan</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                            <select name="year"
                                class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Semua Tahun</option>
                                @for ($year = date('Y'); $year >= 2020; $year--)
                                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                            Filter
                        </button>

                        <a href="{{ route('grading-goods.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm border rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Tgl Grading</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Nama Grade Perusahaan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Jumlah Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Berat setelah Grading (g)</th>
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

                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $grading->grade_company_name ?? '-' }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $grading->quantity ?? '-' }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $grading->weight_grams !== null ? number_format($grading->weight_grams) : '-' }}
                                    </td>

                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="flex flex-col gap-1">
                                            <a href="{{ route('grading-goods.show', $grading->id) }}"
                                                class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                Lihat
                                            </a>

                                            <a href="{{ route('grading-goods.edit', $grading->id) }}"
                                                class="text-blue-600 hover:text-blue-900 font-medium">
                                                Edit
                                            </a>

                                            <form action="{{ route('grading-goods.destroy', $grading->id) }}" method="POST"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-900 font-medium">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        Belum ada data grading.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection
