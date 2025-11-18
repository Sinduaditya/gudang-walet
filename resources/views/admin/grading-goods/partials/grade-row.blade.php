<div class="flex justify-between items-center mb-3">
    <h4 class="font-medium text-sm text-gray-700">Grade {{ $index + 1 }}</h4>
    @if($index > 0)
        <button type="button" onclick="removeGrade(this)" 
            class="text-red-600 hover:text-red-800 text-sm">
            Hapus
        </button>
    @endif
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <!-- Grade Company Name -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Grade Perusahaan</label>
        <input type="text" name="grades[{{ $index }}][grade_company_name]" required
            value="{{ old('grades.' . $index . '.grade_company_name', $grade['grade_company_name'] ?? '') }}"
            placeholder="Contoh: A, B, C, Super"
            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('grades.'.$index.'.grade_company_name') border-red-500 @enderror"
            list="grade-company-options">
        
        @error('grades.'.$index.'.grade_company_name')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Weight -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Berat (gram)</label>
        <input type="number" step="0.01" name="grades[{{ $index }}][weight_grams]" required
            value="{{ old('grades.' . $index . '.weight_grams', $grade['weight_grams'] ?? '') }}"
            class="grade-weight w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('grades.'.$index.'.weight_grams') border-red-500 @enderror"
            onchange="calculateTotalWeight()">
        
        @error('grades.'.$index.'.weight_grams')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Quantity -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Item</label>
        <input type="number" name="grades[{{ $index }}][quantity]" required
            value="{{ old('grades.' . $index . '.quantity', $grade['quantity'] ?? '') }}"
            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('grades.'.$index.'.quantity') border-red-500 @enderror">
        
        @error('grades.'.$index.'.quantity')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<!-- Notes for this grade -->
<div class="mt-3">
    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Grade Ini</label>
    <input type="text" name="grades[{{ $index }}][notes]"
        value="{{ old('grades.' . $index . '.notes', $grade['notes'] ?? '') }}"
        placeholder="Catatan khusus untuk grade ini (opsional)"
        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
</div>

<!-- Datalist for grade company options -->
@if($index === 0)
<datalist id="grade-company-options">
    @foreach($allGradeCompanies as $gradeCompany)
        <option value="{{ $gradeCompany->name }}">
    @endforeach
</datalist>
@endif