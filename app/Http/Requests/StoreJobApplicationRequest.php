<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                \Illuminate\Validation\Rule::unique('job_applications')->where(fn ($query) => $query->where('job_post_id', $this->route('slug') ? \App\Models\JobPost::where('slug', $this->route('slug'))->value('id') : $this->job_post_id)),
            ],
            'phone' => 'required|string|max:20',
            'message' => 'nullable|string',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // 5MB limit
        ];
    }
}
