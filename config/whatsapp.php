<?php

return [
    'token'                => env('META_WHATSAPP_TOKEN', ''),
    'phone_number_id'      => env('META_WHATSAPP_PHONE_NUMBER_ID', ''),
    'business_account_id'  => env('META_WHATSAPP_BUSINESS_ACCOUNT_ID', ''),
    'webhook_verify_token' => env('META_WHATSAPP_WEBHOOK_VERIFY_TOKEN', ''),
    'template_name'        => env('META_WHATSAPP_DEFAULT_TEMPLATE', 'hello_world'),
    'template_language'    => env('META_WHATSAPP_DEFAULT_TEMPLATE_LANGUAGE', 'en_US'),
];
