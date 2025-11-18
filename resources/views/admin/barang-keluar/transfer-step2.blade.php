@extends('layouts.app')

@section('title', 'Transfer Internal - Konfirmasi')

@section('content')

    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Transfer Internal</h1>
                <p class="mt-1 text-sm text-gray-600">Konfirmasi data transfer</p>
            </div>

            <div class="mb-8 bg-white rounded-lg shadow-sm border p-6">
                <div class="flex items-center justify-between max-w-3xl mx-auto">

                    <div class="flex flex-col items-center flex-1">
                        {{-- Dibiarkan hijau untuk "selesai" --}}
                        <div
                            class="w-10 h-10 flex items-center justify-center rounded-full bg-green-600 text-white shadow-sm">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <span class="mt-2 text-xs sm:text-sm font-medium text-gray-900">Data Transfer</span>
                    </div>

                    {{-- PERBAIKAN: Diubah ke purple --}}
                    <div class="flex-1 h-0.5 bg-purple-600 mx-2 sm:mx-4 -mt-6"></div>

                    <div class="flex flex-col items-center flex-1">
                        {{-- PERBAIKAN: Diubah ke purple --}}
                        <div
                            class="w-10 h-10 flex items-center justify-center rounded-full bg-purple-500 text-white font-semibold text-sm shadow-sm">
                            2
                        </div>
                        <span class="mt-2 text-xs sm:text-sm font-medium text-purple-600">Konfirmasi</span>
                    </div>

                </div>
            </div>


            <form action="{{ route('barang.keluar.transfer.store') }}" method="POST">
                @csrf

                <div class="bg-white rounded-xl shadow-md border border-gray-200 mb-6">
                    {{-- PERBAIKAN: Diubah ke purple --}}
                    <div class="px-6 py-4 border-b border-gray-200 bg-purple-50">
                        <h2 class="text-lg font-semibold text-gray-900">Detail Transfer</h2>
                        <p class="text-sm text-gray-600 mt-1">Periksa kembali data sebelum menyimpan</p>
                    </div>

                    <div class="p-6">
                        {{-- PERBAIKAN: Diubah ke purple/indigo --}}
                        <div
                            class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-lg p-6 border-2 border-purple-200">
                            <div class="space-y-4">
                                {{-- PERBAIKAN: Diubah ke purple --}}
                                <div class="flex items-center justify-between pb-3 border-b border-purple-200">
                                    <span class="text-sm font-medium text-gray-600 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        Grade
                                    </span>
                                    <span class="font-semibold text-gray-900">{{ $grade->name }}</span>
                                </div>

                                <div class="bg-white rounded-lg p-4 shadow-sm">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <p class="text-xs text-gray-500 mb-1">Dari</p>
                                            <p class="font-semibold text-gray-900">{{ $fromLocation->name }}</p>
                                        </div>
                                        <div class="px-4">
                                            {{-- PERBAIKAN: Diubah ke purple --}}
                                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 text-right">
                                            <p class="text-xs text-gray-500 mb-1">Ke</p>
                                            <p class="font-semibold text-gray-900">{{ $toLocation->name }}</p>
                                        </div>
                                    </div>
                                </div>

                                {{-- PERBAIKAN: Diubah ke purple --}}
                                <div class="flex items-center justify-between pb-3 border-b border-purple-200">
                                    <span class="text-sm font-medium text-gray-600 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                                        </svg>
                                        Berat Transfer
                                    </span>
                                    {{-- PERBAIKAN: Diubah ke purple --}}
                                    <span class="font-semibold text-purple-700 text-lg">
                                        {{ number_format($step1Data['weight_grams']) }} gram
                                        <span class="text-sm text-gray-600">
                                            ({{ number_format($step1Data['weight_grams'] / 1000, 2) }} kg)
                                        </span>
                                    </span>
                                </div>

                                @if (!empty($step1Data['transfer_date']))
                                    {{-- PERBAIKAN: Diubah ke purple --}}
                                    <div class="flex items-center justify-between pb-3 border-b border-purple-200">
                                        <span class="text-sm font-medium text-gray-600 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Tanggal Transfer
                                        </span>
                                        <span class="font-semibold text-gray-900">
                                            {{ \Carbon\Carbon::parse($step1Data['transfer_date'])->format('d/m/Y') }}
                                        </span>
                                    </div>
                                @endif

                                @if (!empty($step1Data['notes']))
                                    <div>
                                        <span class="text-sm font-medium text-gray-600 flex items-center gap-2 mb-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Catatan
                                        </span>
                                        <p class="text-sm text-gray-700 bg-white rounded p-3">{{ $step1Data['notes'] }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <h4 class="text-sm font-semibold text-yellow-800 mb-1">⚠️ Perhatian</h4>
                            <ul class="text-sm text-yellow-700 list-disc list-inside space-y-1">
                                <li>Stok akan dikurangi dari <strong>{{ $fromLocation->name }}</strong></li>
                                <li>Stok akan ditambahkan ke <strong>{{ $toLocation->name }}</strong></li>
                                <li>Pastikan data sudah benar sebelum menyimpan</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="grade_company_id" value="{{ $step1Data['grade_company_id'] }}">
                <input type="hidden" name="from_location_id" value="{{ $step1Data['from_location_id'] }}">
                <input type="hidden" name="to_location_id" value="{{ $step1Data['to_location_id'] }}">
                <input type="hidden" name="weight_grams" value="{{ $step1Data['weight_grams'] }}">
                <input type="hidden" name="transfer_date" value="{{ $step1Data['transfer_date'] ?? '' }}">
                <input type="hidden" name="notes" value="{{ $step1Data['notes'] ?? '' }}">

                <div class="flex items-center gap-3">
                    <a href="{{ route('barang.keluar.transfer.step1') }}"
                        class="flex-1 inline-flex items-center justify-center px-6 py-3 border-2 border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </a>
                    <button type="submit" {{-- PERBAIKAN: Diubah ke purple --}}
                        class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-purple-800 focus:ring-4 focus:ring-purple-300 transition-all duration-200 shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Konfirmasi & Simpan Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
