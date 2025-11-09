<?php

namespace App\Services\GradeCompany;

use App\Models\GradeCompany;
use Illuminate\Support\Facades\Storage;

class GradeCompanyService
{
    public function getAll(?string $search = null)
    {
        $query = GradeCompany::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate(10)->withQueryString();
    }

    public function getById(int $id)
    {
        return GradeCompany::findOrFail($id);
    }

    public function create(array $data)
    {
        return GradeCompany::create($data);
    }

    public function update(int $id, array $data)
    {
        $gradeCompany = $this->getById($id);
        $gradeCompany->update($data);
        return $gradeCompany;
    }

    public function delete(int $id)
    {
        $gradeCompany = $this->getById($id);

        if ($gradeCompany->image && Storage::disk('public')->exists($gradeCompany->image)) {
            Storage::disk('public')->delete($gradeCompany->image);
        }

        $gradeCompany->delete();
        return true;
    }
}
