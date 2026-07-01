<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobApplicationRequest;
use App\Models\JobApplication;
use App\Models\JobPost;
use App\Traits\UploadsMedia;
use Illuminate\Support\Facades\Auth;

class JobApplicationController extends Controller
{
    use UploadsMedia;

    public function store(StoreJobApplicationRequest $request, string $slug)
    {
        $jobPost = JobPost::where('slug', $slug)->firstOrFail();
        $validated = $request->validated();
        $user = Auth::user();

        $cvPath = null;
        $cvTitle = null;

        if ($request->hasFile('cv')) {
            $file = $request->file('cv');
            $cvPath = $this->uploadOne($file, 'applications/cvs');
            $cvTitle = $file->getClientOriginalName();
        } elseif ($user) {
            $profileResume = $user->resumes()->whereNull('job_application_id')->first();
            if ($profileResume) {
                $cvPath = $profileResume->file_path;
                $cvTitle = $profileResume->cv_title;
            }
        }

        if (! $cvPath && $jobPost->is_cv_required) { // Assuming jobPost has this field, or just always require if submitted
            // If CV is missing and required, it should have been caught by validation or we handle here
        }

        $application = JobApplication::create([
            'job_post_id' => $jobPost->id,
            'user_id' => $user?->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
        ]);

        if ($cvPath) {
            $application->resume()->create([
                'user_id' => $user?->id,
                'job_post_id' => $jobPost->id,
                'cv_title' => $cvTitle ?? 'Resume',
                'file_path' => $cvPath,
            ]);
        }

        // Trigger background parsing for ATS
        \App\Jobs\ParseResumeJob::dispatch($application->id);

        // Send application confirmation email
        app(\App\Services\MailCommunicationService::class)->sendApplicationConfirmation($application);

        return redirect()->back()->with('success', __('Application submitted successfully!'));
    }
}
