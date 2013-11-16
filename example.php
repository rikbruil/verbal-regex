<?php

require_once 'vendor/autoload.php';

use Rb\VerbalRegex\Statement;

$statement = new Statement();
$statement->find('http')
    ->maybe('s')
    ->then('://')
    ->maybe('www.')
    ->endsWith()
    ->anythingBut(' ', 'domain'); // second argument is the (optional) name for the match

$matches = $statement->match('http://www.google.com');

echo $matches['domain']; // echoes 'google.com'