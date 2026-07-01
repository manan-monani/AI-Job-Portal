<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = now();
        $startOfWeek = $now->copy()->startOfWeek();
        $startOfLastWeek = $now->copy()->subWeek()->startOfWeek();
        $endOfLastWeek = $now->copy()->subWeek()->endOfWeek();

        // 1. Job Stats
        $jobStats = [
            'total_active' => \App\Models\JobPost::where('status', 'published')->count(),
            'published' => \App\Models\JobPost::where('status', 'published')->count(),
            'draft' => \App\Models\JobPost::where('status', 'draft')->count(),
            'hidden' => \App\Models\JobPost::where('status', 'hidden')->count(),
            'trending' => [
                'this_week' => \App\Models\JobPost::whereBetween('created_at', [$startOfWeek, $now])->count(),
                'last_week' => \App\Models\JobPost::whereBetween('created_at', [$startOfLastWeek, $endOfLastWeek])->count(),
            ],
        ];

        // 2. Application Pipeline Stats
        $applicationStages = \App\Models\JobApplication::select('status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all required stages exist in the array with 0 if missing
        $requiredStages = ['pending', 'applied', 'screening', 'shortlisted', 'interview', 'rejected', 'hired'];
        foreach ($requiredStages as $stage) {
            $applicationStages[$stage] = $applicationStages[$stage] ?? 0;
        }

        // 3. Hiring Context & Daily Monitoring
        $topJobs = \App\Models\JobPost::withCount('applications')
            ->where('status', 'published')
            ->orderByDesc('applications_count')
            ->take(5)
            ->get(['id', 'title', 'slug', 'applications_count']);

        $upcomingInterviews = \App\Models\Interview::with(['application.jobPost', 'interviewer'])
            ->where('scheduled_at', '>=', $now)
            ->where('scheduled_at', '<=', $now->copy()->addDays(7))
            ->orderBy('scheduled_at')
            ->get();

        $stalledCandidatesCount = \App\Models\JobApplication::where('status', 'pending')
            ->where('updated_at', '<=', $now->copy()->subDays(7))
            ->count();

        $jobsNearingDeadline = \App\Models\JobPost::where('status', 'published')
            ->where('deadline', '>=', $now)
            ->where('deadline', '<=', $now->copy()->addDays(7))
            ->orderBy('deadline')
            ->get(['id', 'title', 'slug', 'deadline']);

        $pendingReviewsCount = \App\Models\JobApplication::where('status', 'pending')->count();

        $recentlyHired = \App\Models\JobApplication::with(['jobPost' => function ($query) {
            $query->select('id', 'title', 'slug');
        }])
            ->where('status', 'hired')
            ->orderByDesc('updated_at')
            ->take(5)
            ->get(['id', 'job_post_id', 'name', 'email', 'updated_at']);

        return Inertia::render('Admin/Pages/Dashboard', [
            'stats' => [
                'jobs' => $jobStats,
                'applications' => $applicationStages,
                'stalled_candidates_count' => $stalledCandidatesCount,
                'pending_reviews_count' => $pendingReviewsCount,
            ],
            'top_jobs' => $topJobs,
            'upcoming_interviews' => $upcomingInterviews,
            'jobs_nearing_deadline' => $jobsNearingDeadline,
            'recently_hired' => $recentlyHired,
        ]);
    }
}
