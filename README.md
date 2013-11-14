verbal-regex
============

Usage:

```php
<?php

$statement = new Rb\VerbalRegex\Statement();
$statement->find('http')
    ->maybe('s')
    ->then('://')
    ->maybe('www')
    ->anythingBut(' ');
    
$statement->match('http://www.google.com');
```
