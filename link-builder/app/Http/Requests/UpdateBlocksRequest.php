<?php

namespace App\Http\Requests;

use App\Blocks\BlockRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateBlocksRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'blocks' => ['present', 'array'],
            'blocks.*.id' => ['required', 'string'],
            'blocks.*.type' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!BlockRegistry::typeExists($value)) {
                    $fail("The block type '{$value}' is not supported.");
                }
            }],
            'blocks.*.enabled' => ['required', 'boolean'],
            'blocks.*.props' => ['required', 'array'],
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'blocks.present' => 'Blocks array must be present.',
            'blocks.array' => 'Blocks must be an array.',
            'blocks.*.id.required' => 'Each block must have an ID.',
            'blocks.*.type.required' => 'Each block must have a type.',
            'blocks.*.enabled.required' => 'Each block must have enabled property.',
            'blocks.*.enabled.boolean' => 'Block enabled property must be true or false.',
            'blocks.*.props.required' => 'Each block must have props.',
            'blocks.*.props.array' => 'Block props must be an object.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'The given data was invalid.',
            'errors' => $validator->errors(),
        ], 422));
    }

    /**
     * Get validated blocks data with additional structure validation
     */
    public function getValidatedBlocks(): array
    {
        $blocks = $this->validated()['blocks'];
        
        // If blocks array is empty, return it as is (valid case)
        if (empty($blocks)) {
            return [];
        }
        
        // Additional validation: ensure each block follows the contract
        foreach ($blocks as $index => $block) {
            if (!BlockRegistry::validateBlock($block)) {
                throw new HttpResponseException(response()->json([
                    'message' => "Block at index {$index} does not follow the required contract structure.",
                    'errors' => ["blocks.{$index}" => ['Invalid block structure']]
                ], 422));
            }
        }
        
        return $blocks;
    }
}
