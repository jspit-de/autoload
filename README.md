# PHP Autoloader

I wanted something simple and basic that I could drop into any project with a basic directory structure
and that works without limitations that must be met for complex installations like composer.
This autoloader can also be used if there are special patterns for filenames like 
class.{Classname}.php or if the classes are in special directories.
The example of https://www.php-fig.org/psr/psr-4/examples/ served as the basis for this class. 
Important for me was to ensure the testability of the class with the environment 
phpcheck (https://github.com/jspit-de/phpcheck).

### Features

- Very easy to use for classes without namespaces
- May use special pattern for filenames how "class.*.php" 
- Paths for classes with namespaces can be added
- Optional loading and saving in config files

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

Example structure for using PHPMailer (V6.x)
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

Note: The src directory can be easily retrieved by downloading
the current PHPMailer release from github as a ZIP archive, 
opening and copying src into the desired directory

### Demo and Test

http://jspit.de/check/phpcheck.autoload.php

The test shows how the add methods handle paths

http://jspit.de/check/phpcheck.autoload.abspath.php

### Requirements

- PHP 5.4+ (5.3.8+)

### Links

PSR-4 Example Implementations

https://www.php-fig.org/psr/psr-4/examples/
