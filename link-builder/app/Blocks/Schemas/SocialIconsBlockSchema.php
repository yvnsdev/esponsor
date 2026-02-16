<?php

namespace App\Blocks\Schemas;

class SocialIconsBlockSchema
{
    public static function getSchema(): array
    {
        return [
            'type' => 'social-icons',
            'label' => 'Social Icons',
            'icon' => 'share-2',
            'description' => 'Display social media icons in a row',
            'defaults' => [
                'title' => '',
                'hoverColor' => '',
                'socials' => [
                    [
                        'platform' => 'instagram',
                        'url' => 'https://instagram.com/yourusername',
                    ],
                ],
            ],
            'fields' => [
                [
                    'name' => 'title',
                    'label' => 'Section Title (Optional)',
                    'type' => 'text',
                    'placeholder' => 'Follow me on social media',
                    'rules' => ['nullable', 'string', 'max:100'],
                ],
                [
                    'name' => 'socials',
                    'label' => 'Social Links',
                    'type' => 'array',
                    'placeholder' => 'Manage your social links',
                    'rules' => ['required', 'array', 'min:1'],
                    'subFields' => [
                        [
                            'name' => 'platform',
                            'label' => 'Platform',
                            'type' => 'icon',
                            'placeholder' => 'Choose social platform',
                            'rules' => ['required', 'string'],
                        ],
                        [
                            'name' => 'url',
                            'label' => 'Profile URL',
                            'type' => 'url',
                            'placeholder' => 'https://...',
                            'rules' => ['required', 'url'],
                        ],
                    ],
                ],
                [
                    'name' => 'hoverColor',
                    'label' => 'Hover Color',
                    'type' => 'color',
                    'placeholder' => '#22B8A6',
                    'rules' => ['nullable', 'string', 'max:20'],
                    'help' => 'Color used when hovering social icons for this block.'
                ],
            ],
        ];
    }
}
