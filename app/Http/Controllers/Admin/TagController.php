<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Permissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTagRequest;
use App\Http\Requests\Admin\UpdateStatusRequest;
use App\Http\Requests\Admin\UpdateTagRequest;
use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    public function __construct(protected TagService $tagService) {}

    public function index(): Response
    {
        Gate::authorize(Permissions::TAGS_MANAGE);

        return Inertia::render('Admin/Hiring/Tags/Index', [
            'tags' => $this->tagService->getAll(request()->only('search')),
        ]);
    }

    public function store(StoreTagRequest $request): RedirectResponse
    {
        Gate::authorize(Permissions::TAGS_MANAGE);

        $this->tagService->create($request->validated());

        return back()->with('success', 'Tag created successfully.');
    }

    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        Gate::authorize(Permissions::TAGS_MANAGE);

        $this->tagService->update($tag, $request->validated());

        return back()->with('success', 'Tag updated successfully.');
    }

    public function updateStatus(UpdateStatusRequest $request, Tag $tag): RedirectResponse
    {
        Gate::authorize(Permissions::TAGS_MANAGE);

        $this->tagService->updateStatus($tag, $request->validated()['status']);

        return back()->with('success', 'Tag status updated successfully.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        Gate::authorize(Permissions::TAGS_MANAGE);

        $this->tagService->delete($tag);

        return back()->with('success', 'Tag deleted successfully.');
    }
}
