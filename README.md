# Component Library Module

## Installation

`composer require high-five/component-library`

### Load Module

Load the module in the `config/app.php` file:

``` php
<?php

return [
    'modules' => [
        'component-library' => ComponentLibrary::class,
    ],
    'bootstrap' => [
        'component-library'
    ],
];
```

Make sure to run `ddev composer dumpautoload` after adding this