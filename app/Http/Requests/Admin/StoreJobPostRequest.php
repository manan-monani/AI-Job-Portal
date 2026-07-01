<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobPostRequest extends FormRequest
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
            'department_id' => ['required', 'exists:departments,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'salary_type' => ['required', 'in:negotiable,non-negotiable'],
            'salary_period' => ['required', 'string', 'in:monthly,yearly'],
            'min_salary' => ['required_if:salary_type,non-negotiable', 'nullable', 'numeric', 'min:0'],
            'max_salary' => ['required_if:salary_type,non-negotiable', 'nullable', 'numeric', 'min:0', 'gte:min_salary'],
            'min_experience' => ['required', 'numeric', 'min:0'],
            'max_experience' => ['required', 'numeric', 'gte:min_experience'],
            'job_type' => ['required', 'in:onsite,remote,hybrid'],
            'location' => ['required_if:job_type,onsite,hybrid', 'nullable', 'string', 'max:255'],
            'deadline' => ['required', 'date', 'after:today'],
            'employment_type' => ['required', 'in:full-time,part-time,contract,internship'],
            'internship_duration' => ['required_if:employment_type,internship', 'nullable', 'string', 'max:255'],
            'contract_duration' => ['required_if:employment_type,contract', 'nullable', 'string', 'max:255'],
            'working_hours' => ['required_if:employment_type,part-time', 'nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,published,hidden'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
        ];
    }
}
