<?php

namespace App\Blocks\Schemas;

class ImageBlockSchema
{
    public static function getSchema(): array
    {
        return [
            'type' => 'image',
            'label' => 'Image Block',
            'icon' => 'image',
            'description' => 'Add an image with optional caption',
            'defaults' => [
                'imageUrl' => 'https://via.placeholder.com/400x300?text=Your+Image',
                'altText' => 'Image description',
                'caption' => '',
                'width' => '100',
                'alignment' => 'center',
                'borderRadius' => '0',
                'linkUrl' => '',
            ],
            'fields' => [
                [
                    'name' => 'imageUrl',
                    'label' => 'Image URL',
                    'type' => 'image',
                    'placeholder' => 'Enter image URL or upload',
                    'rules' => ['required', 'url'],
                ],
                [
                    'name' => 'altText',
                    'label' => 'Alt Text',
                    'type' => 'text',
                    'placeholder' => 'Describe the image for accessibility',
                    'rules' => ['required', 'string', 'max:200'],
                ],
                [
                    'name' => 'caption',
                    'label' => 'Caption (Optional)',
                    'type' => 'text',
                    'placeholder' => 'Add a caption',
                    'rules' => ['nullable', 'string', 'max:200'],
                ],
                [
                    'name' => 'width',
                    'label' => 'Width (%)',
                    'type' => 'select',
                    'placeholder' => 'Select image width',
                    'rules' => ['required', 'in:25,50,75,100'],
                    'options' => [
                        ['value' => '25', 'label' => '25%'],
                        ['value' => '50', 'label' => '50%'],
                        ['value' => '75', 'label' => '75%'],
                        ['value' => '100', 'label' => '100%'],
                    ],
                ],
                [
                    'name' => 'alignment',
                    'label' => 'Alignment',
                    'type' => 'select',
                    'placeholder' => 'Choose alignment',
                    'rules' => ['required', 'in:left,center,right'],
                    'options' => [
                        ['value' => 'left', 'label' => 'Left'],
                        ['value' => 'center', 'label' => 'Center'],
                        ['value' => 'right', 'label' => 'Right'],
                    ],
                ],
                [
                    'name' => 'borderRadius',
                    'label' => 'Border Radius (px)',
                    'type' => 'select',
                    'placeholder' => 'Select border radius',
                    'rules' => ['required', 'in:0,4,8,16,50'],
                    'options' => [
                        ['value' => '0', 'label' => 'None (0px)'],
                        ['value' => '4', 'label' => 'Small (4px)'],
                        ['value' => '8', 'label' => 'Medium (8px)'],
                        ['value' => '16', 'label' => 'Large (16px)'],
                        ['value' => '50', 'label' => 'Circle (50%)'],
                    ],
                ],
                [
                    'name' => 'linkUrl',
                    'label' => 'Link URL (Optional)',
                    'type' => 'url',
                    'placeholder' => 'Add a link to this image',
                    'rules' => ['nullable', 'url'],
                ],
            ],
        ];
    }
}