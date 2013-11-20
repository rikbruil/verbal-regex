Verbal RegEx
============
[![Build Status](https://drone.io/github.com/rikbruil/verbal-regex/status.png)](https://drone.io/github.com/rikbruil/verbal-regex/latest)

Just a small mental exercise.

Before running, make sure you have [Composer](http://getcomposer.org) installed.

Then:
- Clone the repo
- Run `composer install` (or `composer install --no-dev` if you dont't want to run tests)
- Run the exampe: `php example.php`
- ????
- Profit

Example:
------

```php
<?php

use Rb\VerbalRegex\Statement;

$statement = new Statement();
$statement->find('http')
    ->maybe('s')
    ->then('://')
    ->maybe('www.')
    ->endsWith()
    ->anythingBut(' ', 'domain'); // 2nd argument is the (optional) name for the match
    
$matches = $statement->match('http://www.google.com');

echo $matches['domain']; // echoes 'google.com'
```
