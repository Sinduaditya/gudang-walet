@extends('layouts.app')

@section('title', 'Detail Stok')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        {{-- SECTION 1: Header Area (Horizontal Layout) --}}
        <div class="flex flex-wrap items-start gap-6 mb-8">

            {{-- 1. Kotak Kiri: Grade Info (dengan background hitam) --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6 flex flex-col items-center justify-center text-center shrink-0 shadow-lg hover:shadow-xl transition-shadow duration-300">
                {{-- Area Gambar dengan Background Hitam --}}
                <div class="relative mb-4">
                    {{-- Container Gambar Hitam --}}
                    <div class="bg-gradient-to-br from-gray-900 to-black rounded-2xl flex items-center justify-center overflow-hidden shadow-inner" style="height: 220px; width: 220px;">
                        @if(!empty($grade->image_url))
                            <img src="{{ $grade->image_url }}"
                                alt="{{ $grade->name }}"
                                class="max-h-full max-w-full object-contain p-4">
                        @else
                            <div class="text-gray-400 text-sm flex flex-col items-center">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>No Image</span>
                            </div>
                        @endif
                    </div>

                    {{-- Badge Indicator --}}
                    <div class="absolute -top-2 -right-2 w-8 h-8 bg-green-500 rounded-full shadow-lg border-4 border-white flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>

                {{-- Nama Grade --}}
                <h2 class="text-lg font-bold text-gray-800 uppercase leading-tight px-2 tracking-wide">
                    {{ $grade->name }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">Grade Quality</p>
            </div>

            {{-- 2. Kotak Tengah: Total Stok Global --}}
            <div class="bg-gradient-to-br from-blue-600 to-blue-700 text-white rounded-2xl px-8 py-6 shrink-0 shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
                <div class="flex items-center gap-3 mb-2">
                    <svg class="w-6 h-6 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <p class="text-sm font-medium text-blue-100 uppercase tracking-wide">Total Stok Global</p>
                </div>
                <div class="text-4xl font-bold">
                    {{ number_format($globalStock, 0, ',', '.') }}
                    <span class="text-lg font-medium text-blue-200 ml-2">Gr</span>
                </div>
            </div>

            {{-- 3. Tombol Kanan: Kembali --}}
            <div class="ml-auto">
                <a href="{{ route('tracking-stock.get.grade.company') }}"
                class="inline-flex items-center px-6 py-3 bg-white text-gray-700 rounded-xl hover:bg-gray-50 shadow-md hover:shadow-lg transition-all duration-300 font-medium border border-gray-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </a>
            </div>
        </div>

        {{-- SECTION 2: Search Bar --}}
        <div class="mb-8">
            <form action="{{ url()->current() }}" method="GET" class="flex gap-3">
                {{-- Input Search --}}
                <div class="flex-1 relative">
                    <svg class="absolute left-5 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           class="w-full pl-14 pr-5 py-4 bg-white border border-gray-300 rounded-xl text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                           placeholder="Cari lokasi...">
                </div>

                {{-- Tombol Search --}}
                <button type="submit"
                        class="px-12 py-4 bg-blue-600 text-white rounded-xl font-semibold uppercase hover:bg-blue-700 transition-all duration-300 shadow-md hover:shadow-lg">
                    Cari
                </button>
            </form>
        </div>

        {{-- SECTION 3: List Lokasi --}}
        <div class="space-y-4">
            @forelse($locationStocks as $stock)
                {{-- Item List --}}
                <div class="bg-white border border-gray-200 rounded-xl px-8 py-5 flex justify-between items-center hover:shadow-lg transition-all duration-300 group hover:border-blue-300">

                    {{-- Nama Lokasi (Kiri) --}}
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-lg flex items-center justify-center group-hover:from-blue-200 group-hover:to-blue-300 transition-all duration-300">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 uppercase tracking-wide">
                                {{ $stock->location->name ?? 'Unknown' }}
                            </h3>
                            <p class="text-xs text-gray-500">Lokasi Penyimpanan</p>
                        </div>
                    </div>

                    {{-- Detail Stok (Kanan) --}}
                    <div class="text-right">
                        <p class="text-xs text-gray-500 font-medium mb-1">Stok Tersedia</p>
                        <div class="flex items-baseline gap-2 justify-end">
                            <span class="text-3xl font-bold text-gray-800">
                                {{ number_format($stock->total_stock, 0, ',', '.') }}
                            </span>
                            <span class="text-sm font-medium text-gray-500">Gram</span>
                        </div>
                    </div>

                </div>
            @empty
                {{-- Empty State --}}
                <div class="border-2 border-dashed border-gray-300 rounded-2xl p-12 text-center bg-white">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-gray-500 text-base font-medium">Data lokasi tidak ditemukan</p>
                    <p class="text-gray-400 text-sm mt-1">Coba ubah kata kunci pencarian Anda</p>
                </div>
            @endforelse
        </div>

    </div>
</div>
@endsection
