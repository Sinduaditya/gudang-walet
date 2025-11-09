@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard Admin</h1>
        <p class="text-gray-600 mt-2">Overview statistik gudang walet</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m6 4v-8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2h14a2 2 0 002-2z"></path></svg>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-500">Barang Masuk Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-900">125 kg</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16l4-4m0 0l-4-4m4 4H3m6 4v-8a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2H9a2 2 0 01-2-2z"></path></svg>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-500">Barang Keluar Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-900">89 kg</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m-6-4v-4m-2 2h4m14-4v4m-2-2h4m-6 11v4m-2-2h4m-6-4v-4m-2 2h4"></path></svg>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-500">Barang di Grading</p>
                    <p class="text-2xl font-bold text-gray-900">78 kg</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-500">Total Supplier Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">12</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Flow Masuk & Keluar (Per Hari)</h3>
            <div class="h-64 bg-gray-50 rounded flex items-center justify-center">
                <p class="text-gray-500 text-sm">[Placeholder Grafik Batang]</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Barang Dikirim ke DMK (Per Hari)</h3>
            <div class="h-64 bg-gray-50 rounded flex items-center justify-center">
                <p class="text-gray-500 text-sm">[Placeholder Grafik Garis]</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Barang ke Jasa Cuci (Per Nama)</h3>
            <div class="h-64 bg-gray-50 rounded flex items-center justify-center">
                <p class="text-gray-500 text-sm">[Placeholder Grafik Donat]</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Barang Masuk per Supplier (Bulan Ini)</h3>
            <div class="h-64 bg-gray-50 rounded flex items-center justify-center">
                <p class="text-gray-500 text-sm">[Placeholder Grafik Bar Horizontal]</p>
            </div>
        </div>
    </div>

    <div class="mt-8 bg-white rounded-lg shadow-sm">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Aktivitas Terbaru</h3>
        </div>
        
        <div class="divide-y divide-gray-200">
            <div class="p-4 flex items-center space-x-3">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-900">Barang masuk <span class="font-semibold">25kg</span> dari <span class="font-semibold text-blue-600">Supplier A</span></p>
                    <p class="text-xs text-gray-500">2 menit yang lalu</p>
                </div>
            </div>
            
            <div class="p-4 flex items-center space-x-3">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m-6-4v-4m-2 2h4m14-4v4m-2-2h4m-6 11v4m-2-2h4m-6-4v-4m-2 2h4"></path></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-900">Proses grading selesai. Menghasilkan <span class="font-semibold">15kg Grade W1</span>.</p>
                    <p class="text-xs text-gray-500">5 menit yang lalu</p>
                </div>
            </div>
            
            <div class="p-4 flex items-center space-x-3">
                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16l4-4m0 0l-4-4m4 4H3"></path></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-900">Barang keluar <span class="font-semibold">30kg</span> dikirim ke <span class="font-semibold text-blue-600">IDM Demak</span>.</p>
                    <p class="text-xs text-gray-500">10 menit yang lalu</p>
                </div>
            </div>
        </div>
        
        <div class="p-4 text-center">
            <a href="#" class="text-sm text-blue-600 hover:underline font-medium">Lihat semua aktivitas</a>
        </div>
    </div>
</div>
@endsection