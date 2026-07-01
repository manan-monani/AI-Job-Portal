<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDepartmentRequest;
use App\Http\Requests\Admin\UpdateDepartmentRequest;
use App\Http\Requests\Admin\UpdateStatusRequest;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function __construct(protected DepartmentService $departmentService) {}

    public function index(): Response
    {
        Gate::authorize(Permissions::DEPARTMENTS_MANAGE);

        return Inertia::render('Admin/Hiring/Departments/Index', [
            'departments' => $this->departmentService->getAll(request()->only('search')),
        ]);
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        Gate::authorize(Permissions::DEPARTMENTS_MANAGE);

        $this->departmentService->create($request->validated());

        return back()->with('success', 'Department created successfully.');
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        Gate::authorize(Permissions::DEPARTMENTS_MANAGE);

        $this->departmentService->update($department, $request->validated());

        return back()->with('success', 'Department updated successfully.');
    }

    public function updateStatus(UpdateStatusRequest $request, Department $department): RedirectResponse
    {
        Gate::authorize(Permissions::DEPARTMENTS_MANAGE);

        $this->departmentService->updateStatus($department, $request->validated()['status']);

        return back()->with('success', 'Department status updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        Gate::authorize(Permissions::DEPARTMENTS_MANAGE);

        $this->departmentService->delete($department);

        return back()->with('success', 'Department deleted successfully.');
    }
}
