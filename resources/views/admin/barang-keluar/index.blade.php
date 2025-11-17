@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Barang Keluar</h1>
            <p class="text-gray-600">Kelola penjualan dan transfer stok barang</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg shadow-sm">
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="font-semibold mb-1">Terdapat kesalahan:</p>
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Menu Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            {{-- Card 1: Penjualan Langsung --}}
            <a href="{{ route('barang.keluar.sell.form') }}"
                class="group bg-white rounded-xl shadow-md p-8 border-2 border-transparent hover:border-blue-500 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center mb-4">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-500 transition-all duration-300">
                        <svg class="w-8 h-8 text-blue-600 group-hover:text-white transition-all duration-300" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="ml-4 text-xl font-bold text-gray-800 group-hover:text-blue-600 transition-all duration-300">
                        Penjualan Langsung
                    </h3>
                </div>
                <p class="text-gray-600 mb-4">Catat penjualan barang ke customer dengan mengurangi stok.</p>
                <div class="flex items-center text-blue-600 font-medium group-hover:translate-x-2 transition-all duration-300">
                    Mulai Penjualan
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </div>
            </a>

            {{-- Card 2: Total Stok Grade --}}
            <a href="{{ route('stok.tracking.index') }}"
                class="group bg-white rounded-xl shadow-md p-8 border-2 border-transparent hover:border-amber-500 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center mb-4">
                    <div class="w-14 h-14 bg-amber-100 rounded-lg flex items-center justify-center group-hover:bg-amber-500 transition-all duration-300">
                        <svg class="w-8 h-8 text-amber-600 group-hover:text-white transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 class="ml-4 text-xl font-bold text-gray-800 group-hover:text-amber-600 transition-all duration-300">
                        Total Stok Grade
                    </h3>
                </div>
                <p class="text-gray-600 mb-4">Lihat ringkasan total stok untuk setiap grade di semua lokasi.</p>
                <div class="flex items-center text-amber-600 font-medium group-hover:translate-x-2 transition-all duration-300">
                    Lihat Ringkasan
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </div>
            </a>

            {{-- Card 3: Transfer Internal --}}
            <a href="{{ route('barang.keluar.transfer.step1') }}"
                class="group bg-white rounded-xl shadow-md p-8 border-2 border-transparent hover:border-purple-500 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center mb-4">
                    <div class="w-14 h-14 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-500 transition-all duration-300">
                        <svg class="w-8 h-8 text-purple-600 group-hover:text-white transition-all duration-300"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                    <h3 class="ml-4 text-xl font-bold text-gray-800 group-hover:text-purple-600 transition-all duration-300">
                        Transfer Internal
                    </h3>
                </div>
                <p class="text-gray-600 mb-4">Pindahkan stok barang antar lokasi/gudang internal.</p>
                <div class="flex items-center text-purple-600 font-medium group-hover:translate-x-2 transition-all duration-300">
                    Mulai Transfer
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </div>
            </a>

            {{-- Card 4: Transfer External --}}
            <a href="{{ route('barang.keluar.external-transfer.step1') }}"
                class="group bg-white rounded-xl shadow-md p-8 border-2 border-transparent hover:border-green-500 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center mb-4">
                    <div class="w-14 h-14 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-500 transition-all duration-300">
                        <svg class="w-8 h-8 text-green-600 group-hover:text-white transition-all duration-300"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                        </svg>
                    </div>
                    <h3 class="ml-4 text-xl font-bold text-gray-800 group-hover:text-green-600 transition-all duration-300">
                        Transfer External
                    </h3>
                </div>
                <p class="text-gray-600 mb-4">Terima barang dari supplier/partner eksternal (Jasacuci, dll).</p>
                <div class="flex items-center text-green-600 font-medium group-hover:translate-x-2 transition-all duration-300">
                    Mulai Transfer
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </div>
            </a>

        </div>

    </div>
</div>
@endsection
