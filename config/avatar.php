<?php

declare(strict_types=1);


use Vasoft\JokeAvatar\AvatarConfig;

return [
    AvatarConfig::class => static function () use ($env): AvatarConfig {
        return new AvatarConfig()
            ->setSize(32)
            ->setThemes([
                'default' => [
                    'background_colors'    => ['#3498db', '#2980b9', '#1abc9c', '#16a085'],
                    'text_color'           => '#ffffff',
                    'decorative_colors'    => ['#2ecc71', '#e74c3c', '#f39c12', '#9b59b6'],
                    'decorative_min'       => 3,
                    'decorative_max'       => 6,
                    'decorative_size_min'  => 5,
                    'decorative_size_max'  => 30,
                ],
            ]);
    },
];