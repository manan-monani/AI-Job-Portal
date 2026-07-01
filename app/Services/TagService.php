<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TagService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Tag::query();

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        return $query->latest()->paginate(10)->withQueryString();
    }

    public function create(array $data): Tag
    {
        return Tag::create($data);
    }

    public function update(Tag $tag, array $data): bool
    {
        return $tag->update($data);
    }

    public function updateStatus(Tag $tag, bool $status): bool
    {
        return $tag->update(['status' => $status]);
    }

    public function delete(Tag $tag): ?bool
    {
        return $tag->delete();
    }
}
