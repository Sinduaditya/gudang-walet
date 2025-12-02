@extends('layouts.app')

@section('title', 'Tracking Stok')

@section('content')
<div class="py-8 px-4" style="background-color: #f8f9fa;">

    {{-- 1. Search Bar Realtime --}}
    <div class="max-w-7xl mx-auto mb-6">
        <div class="relative">
            <!-- Icon -->
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Input Realtime -->
            <input type="text"
                id="gradeSearch"
                class="w-full py-3 pl-10 pr-3 bg-white border border-gray-200 rounded-lg shadow-sm focus:outline-none focus:ring-0"
                placeholder="Cari grade supplier...">
        </div>
    </div>

    {{-- 2. Grid Card Layout --}}
    <div class="max-w-7xl mx-auto">

        <!-- GRID WRAPPER for JS -->
        <div id="gradeGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            @forelse($trackingStocks as $item)

                {{-- Card --}}
                <div class="grade-item bg-white rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-2 transition-all duration-300"
                     data-name="{{ strtolower($item->name) }}">

                    {{-- Area Gambar --}}
                    <div class="relative p-3">
                        <div class="bg-black rounded-xl flex items-center justify-center overflow-hidden"
                             style="height: 200px;">

                            @if(!empty($item->image_url))
                                <img src="{{ $item->image_url }}"
                                    alt="{{ $item->name }}"
                                    class="max-h-full max-w-full object-contain">
                            @else
                                <div class="text-white text-sm">No Image</div>
                            @endif

                        </div>

                        <div class="absolute top-5 right-5 w-7 h-7 bg-white rounded-full shadow-sm border border-gray-200 cursor-pointer"></div>
                    </div>

                    {{-- Nama Grade --}}
                    <div class="text-center px-4 py-3">
                        <h6 class="font-bold uppercase text-sm tracking-wide text-gray-800">
                            {{ $item->name }}
                        </h6>
                    </div>

                    <div class="border-t border-gray-200 mx-4"></div>

                    {{-- Tombol --}}
                    <div class="px-4 py-4">
                        <div class="flex flex-col gap-2">
                            <a href="{{ route('tracking-stock.detail', $item->id) }}"
                                class="w-full text-center py-2.5 px-4 text-sm font-medium bg-blue-500 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                Detail
                            </a>
                            <a href="#"
                                class="w-full text-center py-2.5 px-4 text-sm font-medium bg-green-500 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                                Tracking Stok
                            </a>
                        </div>
                    </div>

                </div>

            @empty
                <div class="col-span-5 text-center py-12">
                    <p class="text-gray-500">Data tidak ditemukan.</p>
                </div>
            @endforelse
        </div>

        {{-- No Results Message --}}
        <div id="noResults" class="hidden text-center text-gray-500 mt-6">
            Tidak ada data ditemukan.
        </div>
    </div>

    {{-- Pagination --}}
    <div class="flex justify-center mt-6">
        {{ $trackingStocks->links() }}
    </div>

</div>

{{-- JavaScript Realtime Search --}}
<script>
document.addEventListener('DOMContentLoaded', function() {

    const searchInput = document.getElementById('gradeSearch');
    const gradeGrid   = document.getElementById('gradeGrid');
    const noResults   = document.getElementById('noResults');

    // Simpan HTML awal (untuk restore ketika search kosong)
    const originalHTML = gradeGrid.innerHTML;

    // Data lengkap dari semua grade (bukan paginate)
    const allGrades = @json($allGrades);

    searchInput.addEventListener('input', function() {
        const term = this.value.toLowerCase().trim();

        // Jika input kosong → tampilkan kembali HTML awal (tanpa refresh)
        if (term === "") {
            gradeGrid.innerHTML = originalHTML;
            noResults.classList.add("hidden");
            return;
        }

        // Filter hasil pencarian
        const results = allGrades.filter(item =>
            item.name.toLowerCase().includes(term)
        );

        // Jika tidak ada hasil
        if (results.length === 0) {
            gradeGrid.innerHTML = "";
            noResults.classList.remove("hidden");
            return;
        }

        noResults.classList.add("hidden");

        // Render ulang card ketika search aktif
        gradeGrid.innerHTML = results.map(item => `
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-2 transition-all duration-300">

                <!-- Area Gambar -->
                <div class="relative p-3">
                    <div class="bg-black rounded-xl flex items-center justify-center overflow-hidden"
                         style="height: 200px;">
                        ${item.image_url
                            ? `<img src="${item.image_url}" class="max-h-full max-w-full object-contain">`
                            : `<div class="text-white text-sm">No Image</div>`
                        }
                    </div>

                    <div class="absolute top-5 right-5 w-7 h-7 bg-white rounded-full shadow-sm border border-gray-200 cursor-pointer"></div>
                </div>

                <!-- Nama -->
                <div class="text-center px-4 py-3">
                    <h6 class="font-bold uppercase text-sm tracking-wide text-gray-800">
                        ${item.name}
                    </h6>
                </div>

                <div class="border-t border-gray-200 mx-4"></div>

                <!-- Tombol -->
                <div class="px-4 py-4">
                    <div class="flex flex-col gap-2">
                        <a href="/tracking-stock/${item.id}"
                            class="w-full text-center py-2.5 px-4 text-sm font-medium bg-blue-500 text-white rounded-lg hover:bg-blue-700 transition-color duration-200">
                            Detail
                        </a>
                        <a href="#"
                            class="w-full text-center py-2.5 px-4 text-sm font-medium bg-green-500 text-white rounded-lg hover:bg-green-700 transition-color duration-200">
                            Tracking Stok
                        </a>
                    </div>
                </div>

            </div>
        `).join('');
    });
});
</script>
@endsection
