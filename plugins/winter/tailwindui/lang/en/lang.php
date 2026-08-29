<?php

return [
    'plugin' => [
        'name' => 'Tailwind UI',
        'description' => 'Provides a TailwindUI-based skin for the Winter CMS backend.',
        'show_notifications' => 'Show notifications',
        'View_notifications' => 'View notifications',
        'Notifications' => 'Notifications',
        'No_notifications' => 'No notifications',
        'View_All' => 'View All',
    ],

 



    'branding' => [
        'background_image' => [
            'label' => 'Background Image',
            'comment' => 'The background image used on the Split Layout for the login screen.',
        ],
        'auth_layout' => [
            'label' => 'Authentication Layout',
            'simple' => 'Simple (Centred)',
            'split' => 'Left sidebar (Split)',
        ],
        'menu_location' => [
            'label' => 'Menu Location',
            'top' => 'Top',
            'side' => 'Side',
        ],
        'menu_icons' => [
            'label' => 'Icon Location',
            'tile' => 'Above',
            'inline' => 'Beside',
            'hidden' => 'Hidden (text only)',
            'only' => 'Only (no text)',
        ],
    ],

    'permissions' => [
        'manage_own_appearance' => [
            'dark_mode' => 'Change own dark mode preference',
            'menu_location' => 'Change own menu location',
            'item_location' => 'Change own menu item icon location',
        ],
        'manage_appearance' => [
            'dark_mode' => 'Dark mode',
            'menu_location' => 'Menu location',
            'item_location' => 'Item location',

        ],
    ],

    'preferences' => [
        'appearance' => 'Appearance',
        'dark_mode' => [
            'auto' => 'Follow system preferences',
            'light' => 'Light theme',
            'dark' => 'Dark theme',
        ],
    ],
];
