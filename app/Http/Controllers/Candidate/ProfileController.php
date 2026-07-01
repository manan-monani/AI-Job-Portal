<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCandidateProfileRequest;
use App\Traits\UploadsMedia;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends Controller
{
    use UploadsMedia;

    public function edit()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load(['customerProfile', 'resumes' => function ($query) {
            $query->whereNull('job_application_id')->latest();
        }]);

        $resume = $user->resumes->first();

        return Inertia::render('Candidate/Pages/ProfileEdit', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'profile_image' => $user->profile_image,
                'customer_profile' => $user->customerProfile ? [
                    'phone' => $user->customerProfile->phone,
                ] : null,
                'resume' => $resume ? [
                    'cv_title' => $resume->cv_title,
                    'file_path' => $resume->file_path,
                ] : null,
            ],
        ]);
    }

    public function update(UpdateCandidateProfileRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (array_key_exists('password', $validated) && $validated['password']) {
            $user->update([
                'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            ]);
        }

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                $this->deleteOne($user->profile_image);
            }
            $user->update([
                'profile_image' => $this->uploadOne($request->file('profile_image'), 'profiles/images'),
            ]);
        }

        $user->customerProfile()->updateOrCreate(
            ['user_id' => $user->id],
            ['phone' => $validated['phone']]
        );

        if ($request->hasFile('cv')) {
            $file = $request->file('cv');
            $cvPath = $this->uploadOne($file, 'profiles/cvs');

            // Delete old profile resume if it exists
            $oldResume = $user->resumes()->whereNull('job_application_id')->first();
            if ($oldResume) {
                $this->deleteOne($oldResume->file_path);
                $oldResume->delete();
            }

            $user->resumes()->create([
                'cv_title' => $file->getClientOriginalName(),
                'file_path' => $cvPath,
            ]);
        }

        return redirect()->back()->with('success', __('Profile updated successfully!'));
    }
}
