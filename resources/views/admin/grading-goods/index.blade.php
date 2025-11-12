@extends('layouts.app')

@section('title', 'Data Grading Barang')

@section('content')
    <div class="bg-white min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Data Grading Barang</h1>
                    <p class="mt-1 text-sm text-gray-600">Daftar barang yang telah di grading perusahaan</p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="" class="flex items-center text-sm text-gray-600 hover:text-gray-800">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" />
                        </svg>
                        Download as Excel
                    </a>

                    <a href="{{ route('incoming-goods.step1') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Input Grading Barang
                    </a>
                </div>
            </div>

            <div class="bg-white shadow-sm border rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Tanggal Grading</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Nama by Supplier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Tanggal Kedatangan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Jumlah Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Berat setelah Grading (g)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($receipts as $i => $receipt)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $receipts->firstItem() + $i }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ optional($receipt->receipt_date)->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $receipt->supplier->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ optional($receipt->unloading_date)->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $receipt->receiptItems->count() }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ number_format($receipt->receiptItems->sum('warehouse_weight_grams') / 1000, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('incoming-goods.show', $receipt->id) }}"
                                                class="text-blue-600">Lihat</a>
                                            <button onclick="confirmDelete({{ $receipt->id }})"
                                                class="text-red-600">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        Belum ada data barang masuk.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($receipts->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $receipts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-96">
            <h3 class="font-medium mb-4">Hapus Data</h3>
            <p class="text-sm text-gray-600 mb-4">Apakah Anda yakin ingin menghapus penerimaan ini?</p>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex gap-3">
                    <button type="button" onclick="closeDeleteModal()"
                        class="flex-1 px-3 py-2 bg-gray-200 rounded">Batal</button>
                    <button type="submit" class="flex-1 px-3 py-2 bg-red-600 text-white rounded">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function confirmDelete(id) {
                const modal = document.getElementById('deleteModal');
                const form = document.getElementById('deleteForm');
                form.action = `/admin/incoming-goods/${id}`;
                modal.classList.remove('hidden');
            }

            function closeDeleteModal() {
                document.getElementById('deleteModal').classList.add('hidden');
            }
        </script>
    @endpush
@endsection
