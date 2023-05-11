### SalesForce-Api

- Zero Dependencies SalesForce API
- License: MIT license
- These files are Not officially supported by SalesForce.com.
- Questions regarding this software should be directed to daniel.boorn@gmail.com.

How to Install
---------------

Install the `deboorn/salesforce-api` package

```php
require_one 'src/salesforce/api.php'
```

Why?
---------------

Keeping things simple. Zero dependencies equals. Dead simple.

Example of Usage
---------------

```php

$opts = [
    'base_url'       => 'https://test.salesforce.com',
    'client_id'      => 'my-app-client-id',
    'client_secret'  => 'my-app-client-secret',
    'username'       => 'my-username',
    'password'       => 'my-password',
    'security_token' => 'my-security-token',
];

$sf = \SalesForce\Api::forge($opts['base_url'], $opts['client_id'], $opts['client_secret'], $opts['username'], $opts['password'], $opts['security_token']);
$r = $sf->query('SELECT AccountNumber FROM Account LIMIT 10');
var_dump($r);



```
