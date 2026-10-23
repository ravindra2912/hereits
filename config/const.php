<?php


return [

    "site_setting" => [
        "name" => "Hereits",
        "logo" => env('APP_URL') . '/assets/images/logo.png',
        "logo-bg-white" => env('APP_URL') . '/assets/images/logo.png',
        "logo-bg-black" => env('APP_URL') . '/assets/images/logo-bg-black.jpg',
        "small_logo" => env('APP_URL') . '/assets/images/small_logo.png',
        "fevicon" => env('APP_URL') . '/assets/images/fevicon-icon.png',
    ],

    "contact_info" => [
        "phone" => "8306426026",
        "email" => " support@hereits.com",
        "address" => "D -102, Shantinagar co opp house society, Vallabhacharya Road,  Raj Mandir Apartment, Varachha, Surat, Gujarat, 395006",
        "upi_id" => "gosaikrishna85-1@okicici",
    ],

    "admin_emails" => "goswamirvi@gmail.com",

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
    "lucky_draw_status" => ["active", "in-active"],

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
    "payment_gateway" => ['upi_manual', 'cashfree', 'razorpay'],
    "purchase_plan_status" => ['pending', 'active', 'expired', 'override'],
    "purchase_status" => ['pending', 'paid', 'failed', 'refunded'],
    "purchase_type" => ['credit'],

    "product_price_type" => ['FixPrice' => 'Fixed Price', 'PriceInRange' => 'Price Range', 'WithoutPrice' => 'Without Price'],
    "product_images_upload_limit" => 5,
    "home_section_limit" => 15,

    "service_price_type" => ['FixPrice' => 'Fixed Price', 'PriceInRange' => 'Price Range', 'WithoutPrice' => 'Without Price'],
    "service_status" => ['active', 'in-active'],

    "gallery_status" => ['active', 'in-active'],

    "social_links" => [
        "facebook" => "https://www.facebook.com/",
        "twitter" => "https://x.com/hereits",
        "instagram" => "https://www.instagram.com/brand_batao/",
        "linkedin" => "https://www.linkedin.com/in/hereits/",
        "youtube" => "https://www.youtube.com/",
    ],
    "category_type" => ['Services', 'Products'],

    "coupon_compatibility" => ['all', 'credit'],

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
    // 'order_payment_method' => ['cash', 'upi', 'card', 'online'],
    'order_payment_status' => ['pending', 'paid', 'failed', 'refunded'],

    'pos_permissions' => [
        'order' => [
            'create_order' => 'Create Order',
            'view_orders' => 'View Orders',
            'view_all_orders' => 'View All Orders',
            'update_order' => 'Update Order',
            'cancel_order' => 'Cancel Order',
        ],
        'quotation' => [
            'view_quotation' => 'View Quotation',
            'view_all_quotations' => 'View All Quotations',
            'create_quotation' => 'Create Quotation',
            'update_quotation' => 'Update Quotation',
            'cancel_quotation' => 'Cancel Quotation',
            'place_order_quotation' => 'Place Order',
            'create_lucky_draw_quotation' => 'Create Lucky Draw',
            'delete_quotation' => 'Delete Quotation',
        ],
        'inventory' => [
            'view_inventory' => 'View Inventory',
            'edit_inventory' => 'Edit Inventory',
        ],
        'transaction' => [
            'view_transactions' => 'View Transactions',
            'edit_transactions' => 'Edit/Verify Transactions',
        ],
        'lucky_draw' => [
            'view_lucky_draw' => 'View My Lucky Draw Links',
            'view_all_lucky_draw' => 'View All Lucky Draw Links',
            'generate_lucky_draw' => 'Generate Scratch Link',
            'edit_lucky_draw' => 'Edit Scratch Link',
        ],
    ],

    'business_permissions' => [
        'department' => [
            'view_department' => 'View Department',
            'add_department' => 'Add Department',
            'edit_department' => 'Edit Department',
            'delete_department' => 'Remove Department',
        ],
        'experts' => [
            'view_experts' => 'View Experts',
            'add_experts' => 'Add Experts',
            'edit_experts' => 'Edit Experts',
            'delete_experts' => 'Remove Experts',
        ],
        'appointments' => [
            'view_appointments' => 'View Appointments',
            'add_appointments' => 'Add Appointments',
            'edit_appointments' => 'Edit Appointments',
            'delete_appointments' => 'Remove Appointments',
        ],
        'product_categories' => [
            'view_product_category' => 'View Categories',
            'add_product_category' => 'Add Categories',
            'edit_product_category' => 'Edit Categories',
            'delete_product_category' => 'Remove Categories',
        ],
        'product_labels' => [
            'view_labels' => 'View Labels',
            'add_labels' => 'Add Labels',
            'edit_labels' => 'Edit Labels',
            'delete_labels' => 'Remove Labels',
        ],
        'products' => [
            'view_products' => 'View Products',
            'add_products' => 'Add Products',
            'edit_products' => 'Edit Products',
            'delete_products' => 'Remove Products',
        ],
        'product_sharing' => [
            'view_product_sharing' => 'View Product Sharing',
            'add_product_sharing' => 'Add Product Sharing',
            'edit_product_sharing' => 'Edit Product Sharing',
            'delete_product_sharing' => 'Remove Product Sharing',
        ],
        'offers' => [
            'view_offers' => 'View Offers',
            'add_offers' => 'Add Offers',
            'edit_offers' => 'Edit Offers',
            'delete_offers' => 'Remove Offers',
        ],
        'service_categories' => [
            'view_service_category' => 'View Categories',
            'add_service_category' => 'Add Categories',
            'edit_service_category' => 'Edit Categories',
            'delete_service_category' => 'Remove Categories',
        ],
        'services' => [
            'view_services' => 'View Services',
            'add_services' => 'Add Services',
            'edit_services' => 'Edit Services',
            'delete_services' => 'Remove Services',
        ],
        'roles' => [
            'view_roles' => 'View Roles',
            'add_roles' => 'Add Roles',
            'edit_roles' => 'Edit Roles',
            'delete_roles' => 'Remove Roles',
        ],
        'staff' => [
            'view_staff' => 'View Staff',
            'add_staff' => 'Add Staff',
            'edit_staff' => 'Edit Staff',
            'delete_staff' => 'Remove Staff',
        ],
        'gallery' => [
            'view_gallery' => 'View Gallery',
            'add_gallery' => 'Add Gallery',
            'edit_gallery' => 'Edit Gallery',
            'delete_gallery' => 'Remove Gallery',
        ],
        'blogs' => [
            'view_blogs' => 'View Blogs',
            'add_blogs' => 'Add Blogs',
            'edit_blogs' => 'Edit Blogs',
            'delete_blogs' => 'Remove Blogs',
        ],
        'lucky_draw' => [
            'view_lucky_draw' => 'View Lucky Draw',
            'add_lucky_draw' => 'Add Lucky Draw',
            'edit_lucky_draw' => 'Edit Lucky Draw',
            'delete_lucky_draw' => 'Remove Lucky Draw',
        ],
        'home_layouts' => [
            'view_home_layouts' => 'View Layouts',
            'add_home_layouts' => 'Add Layouts',
            'edit_home_layouts' => 'Edit Layouts',
            'delete_home_layouts' => 'Remove Layouts',
        ],
        'home_settings' => [
            'view_home_settings' => 'View Settings',
            'add_home_settings' => 'Add Settings',
            'edit_home_settings' => 'Edit Settings',
            'delete_home_settings' => 'Remove Settings',
        ],
        'campaign_leads' => [
            'view_campaign_leads' => 'View Leads',
            'add_campaign_leads' => 'Add Leads',
            'edit_campaign_leads' => 'Edit Leads',
            'delete_campaign_leads' => 'Remove Leads',
        ],
    ],

    'quotation_policy' => "1. Quotation Validity: This estimate is valid until the selected date and time. Prices may change thereafter.
2. Payment Terms: 100% advance payment required prior to dispatch. Overdue payments accrue interest at 24% p.a.
3. Freight & Handling: Packing, forwarding, transport, courier, and delivery charges are extra unless specified.
4. Transit & Risk: Goods travel at the buyer's risk. Responsibility ends once goods leave the seller's premises; the company is not liable for transit delays, loss, or damage after dispatch.
5. Inspection & Claims: A clear video recording of unboxing, showing the products and delivery challan, is mandatory. Missing items or discrepancies must be reported immediately within 24 hours of delivery with video proof. Verbal claims will not be entertained.
6. Returns & Replacements: All sales are final. Product issues must be reported within 24 hours with technical details and video evidence. Returns are accepted only after written confirmation."

];
