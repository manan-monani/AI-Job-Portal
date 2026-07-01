<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DepartmentService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Department::query();

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        return $query->latest()->paginate(10)->withQueryString();
    }

    public function create(array $data): Department
    {
        return Department::create($data);
    }

    public function update(Department $department, array $data): bool
    {
        return $department->update($data);
    }

    public function updateStatus(Department $department, bool $status): bool
    {
        return $department->update(['status' => $status]);
    }

    public function delete(Department $department): ?bool
    {
        return $department->delete();
    }
}
