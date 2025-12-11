@extends('layouts.app')

@section('title', 'Detail IDM')

@section('content')
    <div class="bg-white min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Detail IDM</h1>
                <p class="mt-1 text-sm text-gray-600">{{ $idmManagement->gradeCompany->name ?? 'IDM' }}</p>
            </div>

            <div class="bg-white shadow-sm border rounded-lg overflow-hidden p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-200">Grade</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-200">Berat Awal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-200">Berat Perut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-200">Berat Kaki</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-200">Susut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border border-gray-200">Harga</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border border-gray-200">
                                    {{ $idmManagement->gradeCompany->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border border-gray-200">
                                    {{ number_format($idmManagement->initial_weight, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border border-gray-200">
                                    {{ number_format($idmManagement->details->where('grade_idm_name', 'perutan')->sum('weight'), 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border border-gray-200">
                                    {{ number_format($idmManagement->details->where('grade_idm_name', 'kakian')->sum('weight'), 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border border-gray-200">
                                    {{ number_format($idmManagement->shrinkage, 2) }} gr
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border border-gray-200">
                                    Rp {{ number_format($idmManagement->estimated_selling_price, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end border-t border-gray-200 pt-4">
                    <div class="text-right">
                        <span class="text-gray-600 font-medium mr-2">Total Harga :</span>
                        <span class="text-xl font-bold text-gray-900">
                            Rp {{ number_format($idmManagement->estimated_selling_price, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <a href="{{ route('manajemen-idm.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Kembali
                </a>
            </div>
        </div>
    </div>
@endsection
