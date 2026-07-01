<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePipelineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'stages' => ['required', 'array', 'min:1'],
            'stages.*.id' => ['nullable', 'integer'],
            'stages.*.type' => ['required', 'string', 'in:system,sorting,assessment,interview'],
            'stages.*.subtype' => ['nullable', 'string', 'in:task,exam,quiz,onsite,phone,online'],
            'stages.*.title' => ['required', 'string', 'max:255'],
            'stages.*.instructions' => ['nullable', 'string'],
            'stages.*.config' => ['nullable', 'array'],
            'stages.*.config.duration' => ['nullable', 'integer', 'min:1'],
            'stages.*.config.total_marks' => ['nullable', 'integer', 'min:1'],
            'stages.*.config.passing_marks' => ['nullable', 'integer', 'min:0'],
            'stages.*.config.due_date' => ['nullable', 'string'],
            'stages.*.config.requires_attachment' => ['nullable', 'boolean'],
            'stages.*.config.scheduled_at' => ['nullable', 'string'],
            'stages.*.config.location' => ['nullable', 'string', 'max:500'],
            'stages.*.config.phone_details' => ['nullable', 'string', 'max:255'],
            'stages.*.config.meeting_link' => ['nullable', 'string', 'max:500'],
            'stages.*.config.meeting_platform' => ['nullable', 'string', 'max:100'],
            'stages.*.sort_order' => ['required', 'integer', 'min:0'],
            'stages.*.is_enabled' => ['boolean'],
            'stages.*.send_mail_on_trigger' => ['boolean'],
            'stages.*.interviewer_ids' => ['nullable', 'array'],
            'stages.*.interviewer_ids.*' => ['integer', 'exists:users,id'],
            'stages.*.criteria' => ['nullable', 'array'],
            'stages.*.criteria.*.label' => ['required_with:stages.*.criteria', 'string', 'max:255'],
            'stages.*.criteria.*.weight' => ['nullable', 'integer', 'min:1'],
            'stages.*.quiz_questions' => ['nullable', 'array'],
            'stages.*.quiz_questions.*.question' => ['required', 'string'],
            'stages.*.quiz_questions.*.options' => ['required', 'array', 'min:2'],
            'stages.*.quiz_questions.*.options.*.option_text' => ['required', 'string', 'max:500'],
            'stages.*.quiz_questions.*.options.*.is_correct' => ['boolean'],
        ];
    }
}
