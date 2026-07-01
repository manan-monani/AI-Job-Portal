<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $applications = $user->applications()
            ->with('jobPost.department')
            ->when($request->status, function ($query, $status) {
                if ($status === 'rejected') {
                    return $query->where('status', 'rejected');
                }
                if ($status === 'pending') {
                    return $query->where('status', 'pending');
                }

                return $query;
            })
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($app) => [
                'id' => $app->id,
                'job_title' => $app->jobPost->title,
                'job_slug' => $app->jobPost->slug,
                'applied_at' => $app->created_at->format('M d, Y'),
                'status' => $app->status,
                'email' => $app->email,
                'department' => $app->jobPost->department?->name,
            ]);

        return Inertia::render('Candidate/Pages/Dashboard', [
            'applications' => $applications,
            'filters' => $request->only(['status']),
        ]);
    }
}
