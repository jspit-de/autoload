# PHP Autoloader

If exist special patterns for filenames like here jspit-de or own directory structures for classes this autoloader can be used.

### Features

- Very easy to use for classes without namespaces
- May use special pattern for filenames how "class.*.php" 
- Paths for classes with namespaces can be added

### Usage

#### All classes are in the same directory

Example structure
+ class
  + autoload.php
  + class.debug.php
  + class.dt.php
+ webroot
   + index.php


```php
//index.php
require __DIR__ . '/../class/autoload.php';
$loader = new autoload("class.*.php);

$today = new dt('today');
debug::write($today);

```

#### Use class with namespace

Example structure
+ class
  + autoload.php
  + class.debug.php
  + class.dt.php
  + PHPMailer
    + src
      + PHPMailer.php
      + SMTP.php
      + Exception.php
+ webroot
   + index.php
   
```php
//index.php
use PHPMailer\PHPMailer\PHPMailer;
require __DIR__ . '/../class/autoload.php';
$loader = new autoload("class.*.php,*.php");

//add autoloaderinfo for PHPMailer
$loader->addNamespace(
    'PHPMailer\\PHPMailer\\',
    __DIR__ . '/../class/PHPMailer/src/',
    '*.php'
); 

$mail = new PHPMailer;
```

### Demo and Test

http://jspit.de/check/phpcheck.jspitautoload.php

### Requirements

- PHP 5.3.8+

### Links

PSR-4 Example Implementations

https://www.php-fig.org/psr/psr-4/examples/

