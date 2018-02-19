<?php
//error_reporting(-1);
error_reporting(E_ALL ^ (E_WARNING | E_USER_WARNING));
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');

require __DIR__ . '/../class/autoload.php';
require __DIR__ . '/../check/Mockjspitautoload.php';
require __DIR__ . '/../class/phpcheck.php';

$t = new PHPcheck;

//Tests

$t->start('show Autoloader Config');
//class Mockjspitautoload extends autoload
$loader = new Mockjspitautoload;
$loader->register();
$loader->addNamespace('Foo\Bar','/vendor/foo.bar/src');
$loader->addNamespace('Foo\Bar','/vendor/foo.bar/tests');
$loader->addNamespace('Foo\BarDoom','/vendor/foo.bardoom/src');
$loader->addNamespace('Foo\Bar\Baz\Dib','/vendor/foo.bar.baz.dib/src');
$loader->addNamespace(
  'Foo\Bar\Baz\Dib\Zim\Gir',
  '/vendor/foo.bar.baz.dib.zim.gir/src'
);
$loader->addPsr0Path("Swift", "/XAMPP/htdocs/php/class/extern/Swift");
$t->checkEqual(true, true);

$t->start('show mockfiles');
$loader->setFiles(array(
  '/XAMPP/htdocs/php/class/testclass.php',
  '/XAMPP/htdocs/php/class/class.test_class3.php',
  '/XAMPP/htdocs/php/class/class.test_class4.php',
  '/XAMPP/htdocs/php/class/extern/inc/testclass2.php',
  '/var/www/virtual/jspit/html/class/PHPMailer/src/PHPMailer.php',
  '/XAMPP/htdocs/php/class/extern/Swift/ByteStream/ArrayByteStream.php',
  '/vendor/foo.bar/src/ClassName.php',
  '/vendor/foo.bar/src/DoomClassName.php',
  '/vendor/foo.bar/tests/ClassNameTest.php',
  '/vendor/foo.bardoom/src/ClassName.php',
  '/vendor/foo.bar.baz.dib/src/ClassName.php',
  '/vendor/foo.bar.baz.dib.zim.gir/src/ClassName.php',
  ));
$t->checkEqual(true, true);

$t->start('load testclass global namespace');
$loader->addPath("/XAMPP/htdocs/php/class/");
$result = $loader->loadClass('testclass');
$expect = '/XAMPP/htdocs/php/class/testclass.php';
$t->checkEqual($result, $expect);
  
$t->start('load testclass namespace jspit\\inc');
$loader->addNamespace("jspit\\","/XAMPP/htdocs/php/class/extern/");
$result = $loader->loadClass('\\jspit\\inc\\testclass2');
$expect = '/XAMPP/htdocs/php/class/extern/inc/testclass2.php';
$t->checkEqual($result, $expect);

$t->start('PHPMailer example');
$loader->addNamespace(
  "PHPMailer\\PHPMailer\\",
  "/var/www/virtual/jspit/html/class/PHPMailer/src/"
);
$result = $loader->loadClass('PHPMailer\\PHPMailer\\PHPMailer');
$expect = '/var/www/virtual/jspit/html/class/PHPMailer/src/PHPMailer.php';
$t->checkEqual($result, $expect);

$t->start('psr4 load test_class4 global namespace mask=class.#.php');
$loader->addPath("/XAMPP/htdocs/php/class/","class.#.php");
//if psr0 activ class-names with _ must have a namespace
$result = $loader->loadClass('\\Test_Class4');
$expect = '/XAMPP/htdocs/php/class/class.test_class4.php';
$t->checkEqual($result, $expect);

$t->start('test1 Existing File');
$result = $loader->loadClass('Foo\Bar\ClassName');
$expect = '/vendor/foo.bar/src/ClassName.php';
$t->checkEqual($result, $expect);

$t->start('test2 Existing File');
$result = $loader->loadClass('Foo\Bar\ClassNameTest');
$expect = '/vendor/foo.bar/tests/ClassNameTest.php';
$t->checkEqual($result, $expect);

$t->start('load non existing file');
$result = $loader->loadClass('nonexist');
$t->checkEqual($result, false);

$t->start('test2 non existing file');
$result = $loader->loadClass('No_Vendor\No_Package\NoClass');
$t->checkEqual($result, false);

$t->start('test load deep File');
$result = $loader->loadClass('Foo\Bar\Baz\Dib\Zim\Gir\ClassName');
$expect = '/vendor/foo.bar.baz.dib.zim.gir/src/ClassName.php';
$t->checkEqual($result, $expect);

$t->start('test1 confusion');
$result = $loader->loadClass('Foo\Bar\DoomClassName');
$expect = '/vendor/foo.bar/src/DoomClassName.php';
$t->checkEqual($result, $expect);

$t->start('test2 confusion');
$result = $loader->loadClass('Foo\BarDoom\ClassName');
$expect = '/vendor/foo.bardoom/src/ClassName.php';
$t->checkEqual($result, $expect);

$t->start('test Swift class (psr0)');

$result = $loader->loadClass('Swift_ByteStream_ArrayByteStream');
$expect = '/XAMPP/htdocs/php/class/extern/Swift/ByteStream/ArrayByteStream.php';
$t->checkEqual($result, $expect);

$t->start('get file path from class');
$result = $loader->getFilePath("PHPMailer\\PHPMailer\\PHPMailer");
$expect = '/var/www/virtual/jspit/html/class/PHPMailer/src/PHPMailer.php';
$t->checkEqual($result, $expect);

$t->start('get array Load Classes');
$result = $loader->getLoadClasses();
$t->check($result, is_Array($result) AND !empty($result));

//Ausgabe 
echo $t->getHtml();
