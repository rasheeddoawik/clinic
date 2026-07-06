<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // السماح بالوصول من أي مكان لحل مشكلة الرفع على سيرفر Render الخارجي
    'allowed_origins' => ['*'], 

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false, // نغيرها إلى false عند استخدام '*' ليتوافق مع معايير الأمان للمتصفحات

];