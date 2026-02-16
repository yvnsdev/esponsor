<?php

namespace App\Blocks\Schemas;

class VideoBlockSchema
{
    public static function getSchema(): array
    {
        return [
            'type' => 'video',
            'label' => 'Video Block (YouTube)',            'icon' => 'video',
            'description' => 'Embed a video from YouTube or Vimeo',            'defaults' => [
                'videoUrl' => '',
                'title' => 'My Video',
                'aspectRatio' => '16:9',
                'autoplay' => false,
                'showControls' => true,
                'width' => '100',
            ],
            'fields' => [
                [
                    'name' => 'videoUrl',
                    'label' => 'YouTube URL',
                    'type' => 'url',
                    'placeholder' => 'https://www.youtube.com/watch?v=VIDEO_ID',
                    'rules' => ['required', 'url', 'regex:/^https:\/\/(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)/'],
                ],
                [
                    'name' => 'title',
                    'label' => 'Video Title',
                    'type' => 'text',
                    'placeholder' => 'Enter video title',
                    'rules' => ['required', 'string', 'max:100'],
                ],
                [
                    'name' => 'aspectRatio',
                    'label' => 'Aspect Ratio',
                    'type' => 'select',
                    'placeholder' => 'Choose aspect ratio',
                    'rules' => ['required', 'in:16:9,4:3,1:1'],
                    'options' => [
                        ['value' => '16:9', 'label' => '16:9 (Widescreen)'],
                        ['value' => '4:3', 'label' => '4:3 (Standard)'],
                        ['value' => '1:1', 'label' => '1:1 (Square)'],
                    ],
                ],
                [
                    'name' => 'width',
                    'label' => 'Width (%)',
                    'type' => 'select',
                    'placeholder' => 'Select video width',
                    'rules' => ['required', 'in:50,75,100'],
                    'options' => [
                        ['value' => '50', 'label' => '50%'],
                        ['value' => '75', 'label' => '75%'],
                        ['value' => '100', 'label' => '100%'],
                    ],
                ],
                [
                    'name' => 'autoplay',
                    'label' => 'Autoplay',
                    'type' => 'select',
                    'placeholder' => 'Enable autoplay?',
                    'rules' => ['required', 'boolean'],
                    'options' => [
                        ['value' => false, 'label' => 'No'],
                        ['value' => true, 'label' => 'Yes'],
                    ],
                ],
                [
                    'name' => 'showControls',
                    'label' => 'Show Controls',
                    'type' => 'select',
                    'placeholder' => 'Show video controls?',
                    'rules' => ['required', 'boolean'],
                    'options' => [
                        ['value' => true, 'label' => 'Yes'],
                        ['value' => false, 'label' => 'No'],
                    ],
                ],
                [
                    'name' => 'showCardStyle',
                    'label' => 'Card Style',
                    'type' => 'checkbox',
                    'placeholder' => 'Show video with card design',
                    'rules' => ['nullable', 'boolean'],
                ],
            ],
        ];
    }
}