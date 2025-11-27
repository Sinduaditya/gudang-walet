@extends('layouts.app')

@section('title', 'Transfer External - Step 2')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Progress Steps --}}
        <div class="mb-8">
            <div class="flex items-center justify-center">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 bg-green-600 rounded-full">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <span class="ml-3 text-green-600 font-medium">Data Transfer</span>
                </div>
                
                <div class="flex-1 mx-4 h-1 bg-green-600 rounded"></div>
                
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 bg-green-600 rounded-full">
                        <span class="text-white font-semibold text-sm">2</span>
                    </div>
                    <span class="ml-3 text-green-600 font-medium">Konfirmasi</span>
                </div>
            </div>
        </div>

        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Konfirmasi Transfer External</h1>
            <p class="text-gray-600 mt-2">Pastikan data transfer ke jasa cuci sudah benar sebelum disimpan</p>
        </div>

        {{-- Confirmation Card --}}
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-8 py-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-bold text-white">Transfer ke Jasa Cuci</h2>
                        <p class="text-green-100 text-sm">Transfer barang dari Gudang Utama ke jasa cuci eksternal</p>
                    </div>
                </div>
            </div>

            {{-- Content --}}
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Transfer Info --}}
                    <div class="space-y-6">
                        <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2">Informasi Transfer</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Grade Perusahaan</label>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $grade->name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Berat Transfer</label>
                                <p class="mt-1">
                                    <span class="text-2xl font-bold text-green-600">{{ number_format($step1Data['weight_grams'], 2) }}</span>
                                    <span class="text-gray-600 ml-1">gram</span>
                                    <span class="text-sm text-gray-500 ml-2">({{ number_format($step1Data['weight_grams'] / 1000, 3) }} kg)</span>
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Tanggal Transfer</label>
                                <p class="mt-1 text-lg font-semibold text-gray-900">
                                    {{ \Carbon\Carbon::parse($step1Data['transfer_date'] ?? now())->format('d F Y') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Location Info --}}
                    <div class="space-y-6">
                        <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2">Lokasi Transfer</h3>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-2">Lokasi Asal</label>
                                <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                    <span class="font-semibold text-gray-900">{{ $fromLocation->name }}</span>
                                </div>
                            </div>

                            <div class="flex justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-2">Jasa Cuci Tujuan</label>
                                <div class="flex items-center p-3 bg-green-50 rounded-lg border border-green-200">
                                    <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="font-semibold text-green-900">{{ $toLocation->name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Notes Section --}}
                @if(!empty($step1Data['notes']))
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Catatan</h3>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-gray-700">{{ $step1Data['notes'] }}</p>
                    </div>
                </div>
                @endif

                {{-- Warning --}}
                <div class="mt-8 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-amber-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <h4 class="font-medium text-amber-800">Perhatian!</h4>
                            <p class="text-sm text-amber-700 mt-1">
                                Setelah konfirmasi, stok di Gudang Utama akan berkurang sebesar <strong>{{ number_format($step1Data['weight_grams'], 2) }} gram</strong>. 
                                Pastikan data sudah benar sebelum melanjutkan.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row justify-between items-center mt-8 pt-6 border-t border-gray-200 space-y-4 sm:space-y-0">
                    <a href="{{ route('barang.keluar.external-transfer.step1') }}"
                        class="w-full sm:w-auto px-6 py-3 bg-gray-600 text-white rounded-lg font-medium hover:bg-gray-700 transition-colors text-center">
                        ← Kembali ke Step 1
                    </a>

                    <form action="{{ route('barang.keluar.external-transfer.store') }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit"
                            class="w-full sm:w-auto px-8 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors">
                            ✓ Konfirmasi Transfer
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection