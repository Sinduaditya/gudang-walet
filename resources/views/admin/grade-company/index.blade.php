@extends('layouts.app')

@section('title', 'Manajemen Grade Company')

@section('content')
<div class="bg-white min-h-screen">
    <div class="w-full px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Manajemen Grade Company</h1>
            <a href="{{ route('grade-company.create') }}"
               class="flex items-center bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Grade Company
            </a>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4" id="alert-success">
            <div class="flex justify-between items-center">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
                <button onclick="document.getElementById('alert-success').remove()" class="text-green-500 hover:text-green-700">
                    ✕
                </button>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4" id="alert-error">
            <div class="flex justify-between items-center">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
                <button onclick="document.getElementById('alert-error').remove()" class="text-red-500 hover:text-red-700">
                    ✕
                </button>
            </div>
        </div>
        @endif

        {{-- <!-- Search Section -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                <div class="flex-1 max-w-md">
                    <label class="flex items-center text-sm text-gray-600 mb-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Cari Grade Company
                    </label>
                    <input type="text" id="searchInput" placeholder="Cari berdasarkan nama atau deskripsi..."
                        onkeyup="searchTable()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </div>
            </div>
        </div> --}}

        <!-- Search Section -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
            <form method="GET" action="{{ route('grade-company.index') }}" class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                <div class="flex-1 max-w-md">
                    <label class="flex items-center text-sm text-gray-600 mb-2">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Cari Grade perusahaan
                    </label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari berdasarkan nama atau deskripsi..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                </div>
                <div>
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200">
                        Cari
                    </button>
                </div>
            </form>
        </div>

        <!-- Table -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="gradeCompanyTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Grade</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gambar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Dibuat</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($gradeCompany as $index => $grade)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $gradeCompany->firstItem() + $index }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $grade->name }}</td>
                            <td class="px-6 py-4">
                                @if($grade->image_url)
                                    <img src="{{ asset('storage/' . $grade->image_url) }}" alt="{{ $grade->name }}" class="w-8 object-cover rounded">
                                @else
                                    <span class="text-gray-400 text-sm">Tidak ada gambar</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $grade->description ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $grade->created_at?->format('d M Y') ?? '-' }}</td>
                            <td class="px-6 py-4 text-center text-sm">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('grade-company.edit', $grade->id) }}"
                                       class="px-3 py-1 bg-blue-100 text-blue-600 rounded hover:bg-blue-200 transition">Edit</a>
                                    <button onclick="confirmDelete({{ $grade->id }}, '{{ $grade->name }}')"
                                            class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200 transition">Hapus</button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-sm text-gray-500">Belum ada data Grade Company</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($gradeCompany->hasPages())
            <div class="bg-white px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Menampilkan {{ $gradeCompany->firstItem() }}–{{ $gradeCompany->lastItem() }} dari {{ $gradeCompany->total() }} data
                    </div>
                    <div>
                        {{ $gradeCompany->links() }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Hapus Grade Company</h3>
        <p class="text-sm text-gray-500 mb-4">Apakah Anda yakin ingin menghapus <span id="gradeName" class="font-semibold text-gray-900"></span>?</p>
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-md text-sm">Batal</button>
                <button type="submit" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-md text-sm">Hapus</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Search Filter
    function searchTable() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#gradeCompanyTable tbody tr');
        rows.forEach(row => {
            const name = row.cells[1]?.innerText.toLowerCase() || '';
            const desc = row.cells[2]?.innerText.toLowerCase() || '';
            row.style.display = name.includes(input) || desc.includes(input) ? '' : 'none';
        });
    }

    // Modal delete
    function confirmDelete(id, name) {
        const baseUrl = `{{ route('grade-company.destroy', ':id') }}`;
        document.getElementById('gradeName').textContent = name;
        document.getElementById('deleteForm').action = baseUrl.replace(':id', id);
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Auto hide alerts
    setTimeout(() => {
        const alerts = document.querySelectorAll('[id^="alert-"]');
        alerts.forEach(alert => {
            if (alert) {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        });
    }, 4000);
</script>
@endsection
