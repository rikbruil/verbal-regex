Verbal RegEx
============
Just a small mental exercise.

Before running, make sure you have [Composer](http://getcomposer.org) installed.

Then:
- Clone the repo
- Run `composer install` (or `composer install --dev` if you want to unit-test)
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
    ->anythingBut(' ', 'domain'); // second argument is the (optional) name for the match
    
$matches = $statement->match('http://www.google.com');

echo $matches['domain']; // echoes 'google.com'
```
