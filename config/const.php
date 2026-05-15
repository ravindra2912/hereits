<?php


return [

    "site_setting" => [
        "name" => "Hereits",
        "logo" => env('APP_URL') . '/assets/images/logo.png',
        "logo-bg-black" => env('APP_URL') . '/assets/images/logo.png',
        "small_logo" => env('APP_URL') . '/assets/images/small_logo.png',
        "fevicon" => env('APP_URL') . '/assets/images/fevicon-icon.png',
    ],

    "contact_info" => [
        "phone" => "8306426026",
        "email" => "hereitshelp@gmail.com",
        "address" => "Khar get, Mahuva, Bhavangar Road",
        "upi_id" => "gosaikrishna85-1@okicici",
    ],

    "upi_info" => [
        "payee_name" => "Krishnaben ravindragiri gausvami",
        "upi_id" => "gosaikrishna85-1@okicici",
    ],

    "template_info" => [
        "common", // appointment booking, product, service
        "taxibooking", // taxi booking
    ],

    "common_status" => ["active", "in-active"],

    "legal_page_type" => ["PrivacyPolicy", "TermsAndCondition", "CopyRight", "VendorPolicy", "CancellationAndRefundPolicy"],

    "gender" => ["Male", "Female"],

    "blog_status" => ["active", "in-active"],
    "user_status" => ["active", "in-active"],
    "user_role" => ["Business", 'User'],
    "expert_status" => ["active", "in-active"],
    "banner_status" => ["active", "in-active"],

    "business_type_status" => ["active", "in-active"],

    "business_status" => ["pending", "active", "in-active", 'baned'],
    "business_type" => ["Service", "Product", 'Appointment'],
    "business_rating" => [
        0 => 'No Review',
        1 => 'Bad',
        2 => 'Poor',
        3 => 'Average',
        4 => 'Good',
        5 => 'Excellent',
    ],


    "week_day_name" => ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", 'Sunday'],

    "appointment_status" => ["pending", 'confirmed', 'in_progress', "completed", "cancel", 'cancel_by_user', 'auto_cancelled'],

    "faq_type" => ['General', 'Business', 'Appointment', 'Services', 'Product'],


    "transactions_status" => ['pending', 'paid', 'failed', 'refunded_requested', 'refunded'],
    "payment_gateway" => ['upi_manual', 'cashfree'],
    "purchase_plan_status" => ['pending', 'active', 'expired', 'override'],
    "purchase_status" => ['pending', 'paid', 'failed', 'refunded'],
    "purchase_type" => ['subscription', 'Product', 'service', 'appointment'],

    "product_price_type" => ['FixPrice' => 'Fixed Price', 'PriceInRange' => 'Price Range', 'WithoutPrice' => 'Without Price'],
    "product_images_upload_limit" => 4,
    "product_videos_upload_limit" => 2,

    "service_price_type" => ['FixPrice' => 'Fixed Price', 'PriceInRange' => 'Price Range', 'WithoutPrice' => 'Without Price'],
    "service_status" => ['active', 'in-active'],

    "gallery_status" => ['active', 'in-active'],

    "social_links" => [
        "facebook" => "https://www.facebook.com/",
        "twitter" => "https://x.com/Hereits",
        "instagram" => "https://www.instagram.com/brand_batao/",
        "linkedin" => "https://www.linkedin.com/in/Hereits/",
        "youtube" => "https://www.youtube.com/",
    ],
    "category_type" => ['Services', 'Products'],

    "plan_type" => ['subscription', 'product', 'service'],
    "plan_usage_type" => ['one_time', 'recurring', 'unlimited'],

    "coupon_compatibility" => ['all', 'subscription', 'product', 'service', 'appointment'],

    // Orders 
    'order_status' => [
        'pending',
        'confirmed',
        'processing',
        'ready_to_deliver',
        'delivered',
        'canceled',
        'canceled_by_user'
    ],
    'order_source' => ['web', 'pos'],
    'order_type' => ['delivery', 'pickup', 'in_store'],
    'order_payment_method' => ['cash', 'upi', 'card', 'online'],
    'order_payment_status' => ['pending', 'paid', 'failed', 'refunded'],

    'pos_permissions' => [
        'order' => [
            'create_order' => 'Create Order',
            'view_orders' => 'View Orders',
            'view_all_orders' => 'View All Orders',
            'update_order' => 'Update Order',
            'cancel_order' => 'Cancel Order',
        ],
        'inventory' => [
            'view_inventory' => 'View Inventory',
            'edit_inventory' => 'Edit Inventory',
        ],
        // 'discount' => [
        //     'apply_discount' => 'Apply Discount',
        // ],
        // 'reports' => [
        //     'view_reports' => 'View Reports',
        // ]
    ],

];
