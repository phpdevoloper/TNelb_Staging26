<?php
return [
 'environment' => env('PAYU_ENV', 'test'),
 'key' => env('PAYU_KEY'),
 'salt' => env('PAYU_SALT'),
 'payment_url' => env('PAYU_ENV', 'test') === 'production'
 ? 'https://secure.payu.in/_payment'
 : 'https://test.payu.in/_payment',
 'verify_url' => env('PAYU_ENV', 'test') === 'production'
 ? 'https://info.payu.in/merchant/postservice.php?form=2'
 : 'https://test.payu.in/merchant/postservice.php?form=2',
 'connect_timeout' => (int) env('PAYU_CONNECT_TIMEOUT', 15),
 'timeout' => (int) env('PAYU_TIMEOUT', 30),
];
