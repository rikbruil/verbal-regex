Verbal RegEx
============
Just a small mental exercise.

Example:
------

```php
<?php

use Rb\VerbalRegex\Statement;

$statement = new Statement();
$statement->find('http')
    ->maybe('s')
    ->then('://')
    ->maybe('www')
    ->anythingBut(' ');
    
$statement->match('http://www.google.com');
```
