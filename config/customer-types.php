<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Customer Type Mode
    |--------------------------------------------------------------------------
    |
    | regular  = normal store. Customers register as Regular only.
    | reseller = store supports reseller requests. Customer can request reseller account.
    | b2b      = store supports company/B2B customers. Customer can request company account.
    | vip      = store supports VIP customers, but admin approval is required.
    |
    | The customer registration form never gives direct customer_type control.
    | It only creates a request. Admin approves from Customers panel.
    |
    */

    'mode' => env('CUSTOMER_TYPE_MODE', 'regular'),

    'allow_reseller_requests' => env('CUSTOMER_ALLOW_RESELLER_REQUESTS', false),
    'allow_company_requests' => env('CUSTOMER_ALLOW_COMPANY_REQUESTS', false),
    'allow_vip_requests' => env('CUSTOMER_ALLOW_VIP_REQUESTS', false),
];
