<?php

namespace App\Blocks\Schemas;

class TextBlockSchema
{
    public static function getSchema(): array
    {
        return [
            'type' => 'text',
            'label' => 'Text Block',
            'icon' => 'type',
            'description' => 'Add rich text content',
            'defaults' => [
                'title' => '',
                'body' => 'Your text content here',
                'textAlign' => 'left',
            ],
            'fields' => [
                [
                    'name' => 'title',
                    'label' => 'Title (Optional)',
                    'type' => 'text',
                    'placeholder' => 'Enter title',
                    'rules' => ['nullable', 'string', 'max:200'],
                ],
                [
                    'name' => 'body',
                    'label' => 'Text Content',
                    'type' => 'textarea',
                    'placeholder' => 'Enter your text content...',
                    'rules' => ['required', 'string', 'max:2000'],
                ],
                [
                    'name' => 'textAlign',
                    'label' => 'Text Alignment',
                    'type' => 'select',
                    'placeholder' => 'Choose alignment',
                    'rules' => ['required', 'in:left,center'],
                    'options' => [
                        ['value' => 'left', 'label' => 'Left'],
                        ['value' => 'center', 'label' => 'Center'],
                    ],
                ],
                [
                    'name' => 'showDivider',
                    'label' => 'Show Divider Below',
                    'type' => 'checkbox',
                    'placeholder' => 'Add a divider line below this text',
                    'rules' => ['nullable', 'boolean'],
                ],
            ],
        ];
    }
}