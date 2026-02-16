<?php

namespace App\Blocks\Schemas;

class CtaBlockSchema
{
    public static function getSchema(): array
    {
        return [
            'type' => 'cta',
            'label' => 'CTA / Highlight',
            'icon' => 'megaphone',
            'description' => 'Call-to-action with title, text and button',
            'defaults' => [
                'title' => 'Important Announcement',
                'text' => 'Check out our latest updates and special offers!',
                'buttonLabel' => 'Learn More',
                'buttonUrl' => 'https://example.com',
                'style' => 'light', // light or dark
                'iconColor' => '',
                'buttonColor' => '',
                'buttonBorderColor' => ''
            ],
            'fields' => [
                [
                    'name' => 'title',
                    'label' => 'Title',
                    'type' => 'text',
                    'placeholder' => 'Enter CTA title',
                    'rules' => ['required', 'string', 'max:100'],
                ],
                [
                    'name' => 'text',
                    'label' => 'Description',
                    'type' => 'textarea',
                    'placeholder' => 'Enter description text...',
                    'rules' => ['required', 'string', 'max:300'],
                ],
                [
                    'name' => 'buttonLabel',
                    'label' => 'Button Label',
                    'type' => 'text',
                    'placeholder' => 'e.g., Learn More',
                    'rules' => ['required', 'string', 'max:50'],
                ],
                [
                    'name' => 'buttonUrl',
                    'label' => 'Button URL',
                    'type' => 'url',
                    'placeholder' => 'https://example.com',
                    'rules' => ['required', 'url', 'max:500'],
                ],
                [
                    'name' => 'style',
                    'label' => 'Style Variant',
                    'type' => 'select',
                    'placeholder' => 'Choose style',
                    'rules' => ['required', 'in:light,dark'],
                    'options' => [
                        ['value' => 'light', 'label' => 'Light (White background)'],
                        ['value' => 'dark', 'label' => 'Dark (Brand color background)'],
                    ],
                ],
                [
                    'name' => 'iconColor',
                    'label' => 'Icon Color',
                    'type' => 'color',
                    'placeholder' => '#22B8A6',
                    'rules' => ['nullable', 'string', 'max:20'],
                    'help' => 'Change the color of the CTA icon (keeps CTA background controlled by style)'
                ],
                [
                    'name' => 'buttonColor',
                    'label' => 'Button Color (optional)',
                    'type' => 'color',
                    'placeholder' => '#22B8A6',
                    'rules' => ['nullable', 'string', 'max:20'],
                    'help' => 'Override the CTA button background color for this block.'
                ],
                [
                    'name' => 'buttonBorderColor',
                    'label' => 'Button Border Color (optional)',
                    'type' => 'color',
                    'placeholder' => '#22B8A6',
                    'rules' => ['nullable', 'string', 'max:20'],
                    'help' => 'Optional button border color (falls back to button color).'
                ],
                [
                    'name' => 'iconColor',
                    'label' => 'Icon Color',
                    'type' => 'color',
                    'placeholder' => '#22B8A6',
                    'rules' => ['nullable', 'string', 'max:20'],
                    'help' => 'Change the color of the CTA icon (keeps CTA background controlled by style)'
                ],
            ],
        ];
    }
}
