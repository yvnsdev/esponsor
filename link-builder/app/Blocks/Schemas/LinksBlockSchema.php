<?php

namespace App\Blocks\Schemas;

class LinksBlockSchema
{
    public static function getSchema(): array
    {
        return [
            'type' => 'links',
            'label' => 'Links Block',
            'icon' => 'link',
            'description' => 'Add multiple clickable links',
            'defaults' => [
                'title' => '',
                'bgColor' => '',
                'textColor' => '',
                'links' => [
                    [
                        'label' => 'Example Link',
                        'url' => 'https://example.com',
                        'icon' => 'link',
                        'backgroundColor' => '#3b82f6',
                        'textColor' => '#ffffff',
                        'style' => 'primary',
                    ],
                ],
            ],
            'fields' => [
                [
                    'name' => 'title',
                    'label' => 'Section Title (Optional)',
                    'type' => 'text',
                    'placeholder' => 'Enter section title',
                    'rules' => ['nullable', 'string', 'max:100'],
                ],
                [
                    'name' => 'bgColor',
                    'label' => 'Block Background Color (optional)',
                    'type' => 'color',
                    'placeholder' => '#ffffff',
                    'rules' => ['nullable', 'string', 'max:20'],
                    'help' => 'Override default button background for this links block.'
                ],
                [
                    'name' => 'textColor',
                    'label' => 'Block Text Color (optional)',
                    'type' => 'color',
                    'placeholder' => '#ffffff',
                    'rules' => ['nullable', 'string', 'max:20'],
                    'help' => 'Override default button text color for this links block.'
                ],
                [
                    'name' => 'links',
                    'label' => 'Links',
                    'type' => 'array',
                    'placeholder' => 'Manage your links',
                    'rules' => ['required', 'array', 'min:1'],
                    'subFields' => [
                        [
                            'name' => 'label',
                            'label' => 'Link Text',
                            'type' => 'text',
                            'placeholder' => 'Enter link text',
                            'rules' => ['required', 'string', 'max:100'],
                        ],
                        [
                            'name' => 'url',
                            'label' => 'URL',
                            'type' => 'url',
                            'placeholder' => 'https://example.com',
                            'rules' => ['required', 'url'],
                        ],
                                [
                            'name' => 'icon',
                            'label' => 'Icon',
                            'type' => 'icon',
                            'placeholder' => 'Choose an icon',
                            'rules' => ['nullable', 'string', 'max:50'],
                        ],
                        [
                            'name' => 'backgroundColor',
                            'label' => 'Link Background Color (optional)',
                            'type' => 'color',
                            'placeholder' => '#22B8A6',
                            'rules' => ['nullable', 'string', 'max:20'],
                            'help' => 'Optional background color for this specific link.'
                        ],
                        [
                            'name' => 'textColor',
                            'label' => 'Link Text Color (optional)',
                            'type' => 'color',
                            'placeholder' => '#FFFFFF',
                            'rules' => ['nullable', 'string', 'max:20'],
                            'help' => 'Optional text color for this specific link.'
                        ],
                        [
                            'name' => 'style',
                            'label' => 'Button Style',
                            'type' => 'select',
                            'placeholder' => 'Choose style',
                            'rules' => ['required', 'in:primary,secondary,ghost'],
                            'options' => [
                                ['value' => 'primary', 'label' => 'Primary (Filled)'],
                                ['value' => 'secondary', 'label' => 'Secondary (Outlined)'],
                                ['value' => 'ghost', 'label' => 'Ghost (Minimal)'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}