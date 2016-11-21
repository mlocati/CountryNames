# PHP library to recognize localized country names thanks to Unicode CLDR 

## Features

- Understand a lot of languages (almost 150 - but some language may not be complete).
- Works with PHP 5.3+
- Requires the mb_string PHP extension (or any package that implements mb_strtolower for UTF-8)

## Install

### With composer

`composer require mlocati/countrynames`

### Without composer

```php
<?php

require_once 'autoload.php';
```

## Sample usage

```php

use MLocati\CountryNames\CountryList;

require_once 'autoload.php'; // Not required if you use composer

$italianList = new CountryList('it');
$italianCountry = $italianList->getCountryByName('italia');

$englishList = new CountryList('en');
$englishCountry = $englishList->getCountryByCode($italianCountry->getCode());

echo $englishCountry->getCanonicalName(); // Dumps 'Italy'
```
