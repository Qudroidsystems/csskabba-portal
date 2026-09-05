<?php

return [

    /*
    | Channels the school has enabled in .env
    | Admin can still choose a subset when sending.
    */
    'channels' => [
        'email'    => env('REMINDER_EMAIL_ENABLED', true),
        'sms'      => env('REMINDER_SMS_ENABLED', false),
        'whatsapp' => env('REMINDER_WHATSAPP_ENABLED', false),
    ],

    /*
    | Where to read parent/student contacts.
    | Adjust column names to match your schema.
    */
    'contacts' => [
        'student_table' => 'studentRegistration',
        'student_pk'    => 'id',

        // Tried in order; first non-empty wins
        'email_fields' => [
            'parent_email',
            'guardian_email',
            'email',
            'father_email',
            'mother_email',
        ],
        'phone_fields' => [
            'parent_phone',
            'guardian_phone',
            'phone',
            'father_phone',
            'mother_phone',
            'phoneno',
            'mobile',
        ],
    ],

    'sms' => [
        'driver'  => env('SMS_DRIVER', 'log'), // log | termii | africastalking
        'from'    => env('SMS_FROM', 'SCHOOL'),
        'termii'  => [
            'api_key' => env('TERMII_API_KEY'),
            'sender'  => env('TERMII_SENDER', 'SCHOOL'),
            'url'     => env('TERMII_URL', 'https://api.ng.termii.com/api/sms/send'),
        ],
        'africastalking' => [
            'username' => env('AT_USERNAME'),
            'api_key'  => env('AT_API_KEY'),
            'from'     => env('AT_FROM', 'SCHOOL'),
        ],
    ],

    'whatsapp' => [
        // meta | twilio | log
        'driver' => env('WHATSAPP_DRIVER', 'log'),

        'meta' => [
            'token'             => env('WHATSAPP_META_TOKEN'),
            'phone_number_id'   => env('WHATSAPP_META_PHONE_NUMBER_ID'),
            'template_name'     => env('WHATSAPP_TEMPLATE_NAME', 'fee_reminder'),
            'template_language' => env('WHATSAPP_TEMPLATE_LANG', 'en'),
        ],

        'twilio' => [
            'sid'        => env('TWILIO_SID'),
            'token'      => env('TWILIO_TOKEN'),
            'from'       => env('TWILIO_WHATSAPP_FROM'), // e.g. whatsapp:+1415...
        ],
    ],

    'school_name' => env('APP_NAME', 'School'),
];