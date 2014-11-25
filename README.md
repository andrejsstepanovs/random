# Random

Geneare random

## Setup
chmod +x index.php

## Run
Run from cli

### Random
./index.php 10

### Parameter
./index.php 10 THEQUICKBROWNFOXJUMPSOVERTHELAZYDOG

### Url
./index.php 10 http://google.com


### Unit Test
-- download phpunit.phar
php phpunit.phar -c Tests/Unit/phpunit.xml

#### Other examples

    ./index.php 1000000
    ./index.php 1000000 AB
    ./index.php 10 http://google.com
    ls | ./index.php 100
    echo "AB" | ./index.php 100
