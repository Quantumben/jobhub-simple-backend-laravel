<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'title' => [
                'required',
                'string',
                'min:2',
                'max:150',
            ],

            'company' => [
                'required',
                'string',
                'min:2',
                'max:150',
            ],

            'location' => [
                'required',
                'string',
                'max:150',
            ],

            'job_type' => [
                'required',

                Rule::in([
                    'full_time',
                    'part_time',
                    'contract',
                    'internship',
                ]),
            ],

            'work_mode' => [
                'required',

                Rule::in([
                    'onsite',
                    'remote',
                    'hybrid',
                ]),
            ],

            'salary_min' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'salary_max' => [
                'nullable',
                'numeric',
                'min:0',
                'gte:salary_min',
            ],

            'description' => [
                'required',
                'string',
                'min:20',
            ],

            'requirements' => [
                'required',
                'string',
                'min:10',
            ],

            'application_url' => [
                'nullable',
                'url',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

        ];
    }
}
