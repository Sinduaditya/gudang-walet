@extends('layouts.app')

@section('title', 'Detail Stok - ' . $grade->name)

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        {{-- TOMBOL KEMBALI (Di Kanan & Ada Gap) --}}
        <div class="flex justify-end mb-6">
            <a href="{{ route('tracking-stock.get.grade.company') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-medium transition duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>
        </div>

       {{-- SECTION 1: Header Area --}}
        <div class="flex flex-col md:flex-row gap-6 mb-8 items-start">

            {{-- 1. KARTU KIRI: Grade Info (FIXED SIZE) --}}
            <div class="w-64 bg-white border border-gray-200 rounded-2xl p-4 shadow-sm flex-shrink-0">

                {{-- Area Gambar (Kotak Hitam) --}}
                <div class="relative w-full aspect-square bg-black rounded-xl mb-4 flex items-center justify-center overflow-hidden group shadow-inner">

                    {{-- Badge Centang Hijau --}}
                    <div class="absolute top-2 right-2 bg-white rounded-full p-1 shadow-sm z-10">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>

                    @if(!empty($grade->image_url))
                        <img src="{{ $grade->image_url }}"
                             alt="{{ $grade->name }}"
                             class="w-full h-full object-contain p-4 transition-transform duration-300 group-hover:scale-110">
                    @else
                        <div class="flex flex-col items-center text-gray-500 text-xs">
                            <span class="mb-1">No Image</span>
                        </div>
                    @endif
                </div>

                {{-- Nama & Label --}}
                <div class="text-center">
                    <h3 class="text-lg font-bold text-gray-900 uppercase leading-tight">
                        {{ $grade->name }}
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">Grade Quality</p>
                </div>
            </div>

            {{-- 2. KARTU KANAN: Stok & Deskripsi --}}
            <div class="flex-1 w-full flex flex-col gap-4">

                {{-- Kartu Biru (Total Stok) --}}
                <div class="bg-blue-500 rounded-2xl p-6 text-white shadow-md flex flex-col justify-center h-40 relative overflow-hidden">
                    <div class="relative z-10 flex items-center gap-2 mb-2 opacity-90">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span class="text-sm font-bold tracking-widest uppercase">TOTAL STOK GLOBAL</span>
                    </div>

                    <div class="relative z-10 flex items-baseline">
                        <span class="text-5xl font-bold leading-none tracking-tight">
                            {{ number_format($globalStock, 0, ',', '.') }}
                        </span>
                        <span class="ml-3 text-xl font-medium text-blue-100">Gr</span>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm flex-1">
                    <h4 class="text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Deskripsi
                    </h4>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        {{ $grade->description ?? 'Tidak ada deskripsi tambahan untuk grade ini.' }}
                    </p>
                </div>

            </div>
        </div>

        {{-- SECTION 2: Search & Actions --}}
        <div class="mb-6">
             <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <form method="GET" action="{{ route('tracking-stock.detail', $grade->id) }}">
                    <div class="flex flex-col lg:flex-row gap-4">
                        {{-- Search Input --}}
                        <div class="flex-1">
                             <label class="flex items-center text-sm text-gray-600 mb-2">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Cari Lokasi Stok
                            </label>
                            <input type="text"
                                   name="search"
                                   value="{{ request('search') }}"
                                   placeholder="Cari berdasarkan nama lokasi..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        </div>

                        {{-- Buttons Group --}}
                        <div class="flex items-end gap-2">
                            {{-- Search Button --}}
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium transition duration-200 whitespace-nowrap">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                         d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Cari
                            </button>

                            {{-- Reset Button (Selalu Tampil) --}}
                            <a href="{{ route('tracking-stock.detail', $grade->id) }}"
                               class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-medium transition duration-200 whitespace-nowrap">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                {{-- Active Search Display --}}
                @if (request('search'))
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <div class="flex flex-wrap gap-2 items-center">
                            <span class="text-sm text-gray-600">Pencarian aktif:</span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                "{{ request('search') }}"
                            </span>
                        </div>
                    </div>
                @endif
             </div>
        </div>

        {{-- SECTION 3: Table --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h3 class="text-gray-700 font-semibold">Rincian Stok per Lokasi</h3>
                <span class="text-sm text-gray-500">Menampilkan {{ count($locationStocks) }} lokasi</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lokasi</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Tersedia (Gram)</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($locationStocks as $stock)
                            <tr class="hover:bg-blue-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $stock->location->name ?? 'Lokasi Tidak Diketahui' }}</div>
                                    <div class="text-xs text-gray-500">ID: #{{ $stock->location_id }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm font-bold text-gray-900">{{ number_format($stock->total_stock, 0, ',', '.') }}</span>
                                    <span class="text-xs text-gray-500 ml-1">Gr</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($stock->total_stock > 0)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Available</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Empty</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">Data lokasi tidak ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
