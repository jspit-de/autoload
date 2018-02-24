<?php
//error_reporting(-1);
error_reporting(E_ALL ^ (E_WARNING | E_USER_WARNING));
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=UTF-8');

define("CLASSPATH", __DIR__ . '/../class');
require CLASSPATH.'/phpcheck.php';
require CLASSPATH.'/autoload.php';

/*
 * Simulate some autoload function for tests
 */
class mockAbsPath extends autoload
{
  public static $curDir = __DIR__;
  public static $curWD = __DIR__;
  
  public static function getClassDir()
  {
    return self::$curDir;
  }
  
  public static function getCurWorkDir()
  {
    return self::$curWD;
  }
 
}
//$loader = new mockAbsPath;

$t = new PHPcheck;

//Tests

$t->start('exist versions info');
$info = $t->getClassVersion("autoload");
$t->check($info, !empty($info));

$t->start('emulate classDir');
//testClassDir: contain class autoloload.php
$testClassDir = "/var/www/html/class";
mockAbsPath::$curDir = $testClassDir;
$t->check($testClassDir,true);

$t->start('emulate CurWorkDir');
//testCurWD: the project dir
$testCurWD = "/var/www/html/project";
mockAbsPath::$curWD = $testCurWD;
$t->check($testCurWD,true);

$t->start('check getClassDir()');
// get dir contain class autoload
$result = mockAbsPath::getClassDir();
$t->checkEqual($result, $testClassDir); 

$t->start('check a absolute path');
$path = "/var/www/html";
$result = mockAbsPath::absPath($path);
$expect = $path;
$t->checkEqual($result, $expect);

$t->start('check a absolute path');
$path = "/var/www/html/";  //with last /
$result = mockAbsPath::absPath($path);
//result without last /
$expect = $path = "/var/www/html"; ;
$t->checkEqual($result, $expect);

$t->start('check abs path with rel. parts');
$result = mockAbsPath::absPath('/foo/bar/baz/../../sub');
$expect = '/foo/sub';
$t->checkEqual($result, $expect);

$t->start('check rel path');
//relative paths have the class directory as base
$result = mockAbsPath::absPath('php/check/');
$expect = $testClassDir."/php/check";
$t->checkEqual($result, $expect);

$t->start('check abs path with ..');
//the directory above the class directory
$path = mockAbsPath::getClassDir() . "/..";
$result = mockAbsPath::absPath($path);
$expect = "/var/www/html";
$t->checkEqual($result, $expect);

$t->start('check abs path with inside /../');
//a directory at the same level as the class directory
$path = mockAbsPath::getClassDir() . "/../include/";
$result = mockAbsPath::absPath($path);
$expect = "/var/www/html/include";
$t->checkEqual($result, $expect);

$t->start('check empty path');
$result = mockAbsPath::absPath('');
$expect = $testClassDir;
$t->checkEqual($result, $expect);

$t->start('check cur dir');
// paths starting with. or .. have 
// the current word directory as the basis
$result = mockAbsPath::absPath('.');
$expect = $testCurWD;
$t->checkEqual($result, $expect);

$t->start('check cur dir and path');
$result = mockAbsPath::absPath('./php/');
$expect = $testCurWD."/php";
$t->checkEqual($result, $expect);

$t->start('check ../');
$result = mockAbsPath::absPath('../');
$expect = "/var/www/html";
$t->checkEqual($result, $expect);

$t->start('check ..');
$result = mockAbsPath::absPath('..');
$expect = "/var/www/html";
$t->checkEqual($result, $expect);

//Ausgabe 
echo $t->getHtml();

