@extends('layouts.app')

@section('title', 'Detail Stok')

@section('content')
<div class="min-h-screen bg-white py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">

        {{-- SECTION 1: Header Area (Horizontal Layout) --}}
        <div class="flex flex-wrap items-start gap-4 mb-8">

            {{-- 1. Kotak Kiri: Grade Info (dengan background hitam) --}}
            <div class="bg-white border-2 border-black rounded-3xl p-4 flex flex-col items-center justify-center text-center shrink-0">
                {{-- Area Gambar dengan Background Hitam --}}
                <div class="relative mb-3">
                    {{-- Container Gambar Hitam --}}
                    <div class="bg-black rounded-xl flex items-center justify-center overflow-hidden" style="height: 200px; width: 200px;">
                        @if(!empty($grade->image_url))
                            <img src="{{ $grade->image_url }}"
                                alt="{{ $grade->name }}"
                                class="max-h-full max-w-full object-contain">
                        @else
                            <div class="text-white text-sm">No Image</div>
                        @endif
                    </div>

                    {{-- Lingkaran Putih (Radio/Checkbox Style) di Pojok Kanan Atas --}}
                    <div class="absolute top-2 right-2 w-7 h-7 bg-white rounded-full shadow-sm border border-gray-200 cursor-pointer">
                    </div>
                </div>

                {{-- Nama Grade --}}
                <h2 class="text-sm font-bold text-black uppercase leading-tight px-2">
                    {{ $grade->name }}
                </h2>
            </div>

            {{-- 2. Kotak Tengah: Total Stok Global --}}
            <div class="bg-white border-2 border-black rounded-3xl px-6 py-4 shrink-0">
                <p class="text-xs font-semibold text-gray-600 uppercase mb-1">Total Stok Global</p>
                <div class="text-2xl font-bold text-black">
                    {{ number_format($globalStock, 0, ',', '.') }}
                    <span class="text-sm font-medium text-gray-600 ml-1">Gr</span>
                </div>
            </div>

            {{-- 3. Tombol Kanan: Kembali --}}
            <div class="ml-auto">
                <a href="{{ route('tracking-stock.get.grade.company') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </a>
            </div>
        </div>

        {{-- SECTION 2: Search Bar --}}
        <div class="mb-6">
            <form action="{{ url()->current() }}" method="GET" class="flex gap-3">
                {{-- Input Search --}}
                <div class="flex-1">
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           class="w-full px-5 py-3.5 bg-white border-2 border-black rounded-xl text-black placeholder-gray-400 focus:outline-none focus:ring-0 focus:border-black"
                           placeholder="Cari">
                </div>

                {{-- Tombol Search --}}
                <button type="submit"
                        class="px-10 py-3.5 bg-white border-2 border-black rounded-xl font-semibold text-black uppercase hover:bg-gray-50 transition-all">
                    Cari
                </button>
            </form>
        </div>

        {{-- SECTION 3: List Lokasi --}}
        <div class="space-y-3">
            @forelse($locationStocks as $stock)
                {{-- Item List --}}
                <div class="bg-white border-2 border-black rounded-xl px-6 py-4 flex justify-between items-center hover:bg-gray-50 transition-all">

                    {{-- Nama Lokasi (Kiri) --}}
                    <div class="text-base font-semibold text-black uppercase">
                        {{ $stock->location->name ?? 'Unknown' }}
                    </div>

                    {{-- Detail Stok (Kanan) --}}
                    <div class="text-right">
                        <span class="text-sm text-gray-600 font-normal">Total Barang yang masih ada di tempat : </span>
                        <span class="text-lg font-bold text-black">
                            {{ number_format($stock->total_stock, 0, ',', '.') }}
                        </span>
                    </div>

                </div>
            @empty
                {{-- Empty State --}}
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center bg-gray-50">
                    <p class="text-gray-500 text-sm">Data lokasi tidak ditemukan.</p>
                </div>
            @endforelse
        </div>

    </div>
</div>
@endsection
