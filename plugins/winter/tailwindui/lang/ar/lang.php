<?php

return [
    'plugin' => [
        'name' => 'Tailwind UI',
        'description' => 'يوفر مظهرًا يعتمد على TailwindUI للوحة التحكم في Winter CMS.',
        'show_notifications' => 'عرض الإشعارات',
        'View_notifications' => 'مشاهدة الإشعارات',
        'Notifications' => 'الإشعارات',
        'No_notifications' => 'لا توجد إشعارات',
        'View_All' => 'عرض الكل',
    ],

    'branding' => [
        'background_image' => [
            'label' => 'صورة الخلفية',
            'comment' => 'صورة الخلفية المستخدمة في تخطيط الشاشة المنقسمة لتسجيل الدخول.',
        ],
        'auth_layout' => [
            'label' => 'تخطيط المصادقة',
            'simple' => 'بسيط (متمركز)',
            'split' => 'شريط جانبي يسار (منقسم)',
        ],
        'menu_location' => [
            'label' => 'موقع القائمة',
            'top' => 'أعلى',
            'side' => 'جانب',
        ],
        'menu_icons' => [
            'label' => 'موقع الأيقونة',
            'tile' => 'أعلى',
            'inline' => 'بجانب',
            'hidden' => 'مخفي (نص فقط)',
            'only' => 'فقط (بدون نص)',
        ],
    ],

    'permissions' => [
        'manage_own_appearance' => [
            'dark_mode' => 'تغيير تفضيل الوضع الداكن الخاص',
            'menu_location' => 'تغيير موقع القائمة الخاص',
            'item_location' => 'تغيير موقع أيقونات عناصر القائمة الخاصة',
        ],
        'manage_appearance' => [
            'dark_mode' => 'الوضع الداكن',
            'menu_location' => 'موقع القائمة',
            'item_location' => 'موقع العنصر',
        ],

    ],

    'preferences' => [
        'appearance' => 'المظهر',
        'dark_mode' => [
            'auto' => 'اتباع تفضيلات النظام',
            'light' => 'الوضع الفاتح',
            'dark' => 'الوضع الداكن',
        ],
    ],
];
