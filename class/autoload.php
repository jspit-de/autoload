<?php

/**
.---------------------------------------------------------------------------.
|  Software: autoload - PHP Autoloader Class                                |
|   Version: 1.1 Beta                                                       |
|      Date: 2018-02-20                                                     |
| ------------------------------------------------------------------------- |
| Copyright © 2017-2018, Peter Junk (alias jspit). All Rights Reserved.     |
| ------------------------------------------------------------------------- |
| License: Distributed under MIT License                                    |
| This program is distributed in the hope that it will be useful - WITHOUT  |
| ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or     |
| FITNESS FOR A PARTICULAR PURPOSE.                                         |
'---------------------------------------------------------------------------'
*/
class autoload
{
  protected $register = array();
  
  private $loadClasses = array();
  
  private $curClass;
  
  private $usePsr0 = false;
  
  
 /*
  * @param string $mask The pattern for filenames how "*.php" or "class.*.php"
  * may be a list of patterns "class.*.php,*.php"
  * without argument autoload must start with register-method after add-Methods
  */
  public function __construct($mask = false) {
    if($mask) {
      $this->addPath(__DIR__, $mask);
      $this->register();
    }
  }

  /**
   * Register loader with SPL autoloader stack.
   * @return void
   */
  public function register()
  {
     spl_autoload_register(array($this, 'loadClass'));
  }

  /*
   * add a relative path from this directory for search in global Namespace
   * return true if ok or false if error
   * $path: a relative path
   * $searchMask: komma separates list with pattern for filenames as '*.php' (Default) or 'class.*.php'
   * if searchMask use # how class.#.php, classname set tolower
   * return void
   */
  public function addPath($path = "", $searchMask = "*.php")
  {
    $delim = '\\';  //psr-4
    $this->registerPath("", $path, $searchMask , $delim);
  }

  /**
   * Adds a base directory for a namespace prefix.
   *
   * @param string $prefix The namespace prefix.
   * @param string $path A base directory for class files in the
   * namespace.
   * @param string $mask 
   * @return void
   */
  public function addNamespace($prefix, $path, $mask = "*.php")
  {
    $delim = '\\';  //psr-4
    $this->registerPath($prefix, $path, $mask , $delim);
  }

  /**
   * Adds a base directory for psr-0 prefix.
   *
   * @param string $prefix The namespace prefix.
   * @param string $path A base directory for class files in the
   * namespace.
   * @param string $mask 
   * @return void
   */
  public function addPsr0Path($prefix, $path, $mask = "*.php")
  {
    $this->usePsr0 = true;
    $delim = '_';  //psr-0
    $this->registerPath($prefix, $path, $mask , $delim);  
  }
  
  protected function registerPath($prefix, $path, $mask , $delim)
  {
    // normalize namespace prefix
    $prefix = trim($prefix, $delim) .$delim;
    
    //abs Path
    $base_dir = $this->absPath($path) . '/';
    
    // initialize the namespace prefix array
    if (isset($this->register[$prefix]) === false) {
        $this->register[$prefix] = array('delim' => $delim);
    }

    // retain the base directory for the namespace prefix and masks
    foreach(explode(",",$mask) as $curMask) {
      array_push($this->register[$prefix], array($base_dir,$curMask));
    }
  
  }
  
  
  /**
   * Loads the class file for a given class name.
   *
   * @param string $class The fully-qualified class name.
   * @return mixed The mapped file name on success, or boolean false on
   * failure.
   */
  public function loadClass($class)
  {
      $this->curClass = $class;
      //identify delimiter (psr)
      $delim = '\\';
      if($this->usePsr0 AND strpos($class,"_") !== false AND strpos($class,"\\") === false){
        $delim = '_';
      }
      $class = ltrim($class,"\\");
      $prefix = $class;
      // work backwards through the namespace names of the fully-qualified
      // class name to find a mapped file name
      $mapped_file = false;
      while(true) {
          $pos = strrpos($prefix, $delim);
          if($pos === false) {
            $relative_class = $class;
            $prefix = $delim;
          }
          else {
            $prefix = substr($class, 0, $pos + 1);
            // the rest is the relative class name
            $relative_class = substr($class, $pos + 1);
          }
          
          // are there any base directories for this namespace prefix
          // and are the same delimiter
          if(isset($this->register[$prefix]) AND $this->register[$prefix]['delim'] == $delim) {
            $mapped_file = $this->loadMappedFile($prefix, $relative_class, $delim);
            if ($mapped_file) {
              $this->loadClasses[$class] = $mapped_file; 
              return $mapped_file;
            }
          }
          if($pos === false) {
            // never found a mapped file
            trigger_error("Error Autoload: file for class '$class' not found", E_USER_WARNING);
            return false;  
          }
          $prefix = rtrim($prefix, $delim);
      }

  }

  /**
   * Load the mapped file for a namespace prefix and relative class.
   *
   * @param string $prefix The namespace prefix.
   * @param string $relative_class The relative class name.
   * @return mixed Boolean false if no mapped file can be loaded, or the
   * name of the mapped file that was loaded.
   */
  protected function loadMappedFile($prefix, $relative_class, $delim)
  {
    $relative_class = str_replace($delim, '/', $relative_class);
    list($pathPart, $className) = $this->splitNsClassname($relative_class,'/');
    
    // look through base directories for this namespace prefix
    foreach ($this->register[$prefix] as $key => $baseDirAndMask) {
        if(! is_numeric($key)) continue;
        // replace the namespace prefix and relative_class
        list($baseDir,$mask) = $baseDirAndMask;
        
        if($mask == "") {
          $file = $baseDir . $pathPart .  $className . '.php';
        }
        elseif(strpos($mask,"*") !== false) {
          $file = $baseDir . $pathPart . str_replace("*",$className,$mask);
        }
        elseif(strpos($mask,"#") !== false) {
          //search className as lowercase
          $file = $baseDir . $pathPart . str_replace("#",strtolower($className),$mask);
        }
        else {
          //unknown mask
          return false;
        }
        // if the mapped file exists, require it
        if ($this->requireFile($file)) {
          return $file;
        }
    }

    // never found it
    return false;
  }

  /**
   * If a file exists, require it from the file system.
   *
   * @param string $file The file to require.
   * @return bool True if the file exists, false if not.
   */
  protected function requireFile($file)
  {
    if (file_exists($file)) {
      require $file;
      if(class_exists($this->curClass, false)) {
        return true;
      }
      else {
        trigger_error(
          "Error Autoload: file '$file' not contain class '".$this->curClass."'",
          E_USER_WARNING
        );
        return false;
      }
    }
    return false;
  }
  
 /*
  * return a array with register infos
  * may use for tests and debugging 
  */
  public function getConfig() 
  {  
    return $this->register;
  }

 /*
  * get a array with pairs 'classname' => 'PathAndFileName'
  */
  public function getLoadClasses()
  {
    return $this->loadClasses;
  }
  
 /*
  * get path and filename from given className
  * class: object or full Class-Name (with Namespace if use) 
  * return false if $class not found
  */
  public function getFilePath($class)
  {
    $className = is_object($class) ? get_class($class) : $class;
    if(array_key_exists($className, $this->loadClasses)) {
      return $this->loadClasses[$className];
    }
    return false;
  }
    
 /*
  * return array('"Bar\foo\","baz") from str = "Bar\foo\baz"
  */
  protected function splitNsClassname($str, $delimiter = "\\")
  {
    $pos = strrpos($str,$delimiter);
    if($pos > 0) {
      return array(
        substr($str,0,$pos+1),
        substr($str,$pos+1)
      );
    }
    return array("",trim($str,$delimiter));
  
  }
 /*
  * expands all resolves references to /./, /../ 
  * and return a absolute pathname
  */
  protected function absPath($path)
  {
    $path = strtr($path,'\\','/');
    if($path == "..") $path .= "/";
    if($path == ".") {
      $path = strtr(getcwd(),'\\','/');
    }
    elseif(substr($path,0,2) == './') {
      $path = strtr(getcwd(),'\\','/').substr($path,1);
    }
    elseif(!preg_match('~^(\w+:|/)~',$path)){
      $path = strtr(__DIR__,'\\','/')."/".$path;
    }
    
    for($count=1; strpos($path,"/../") !== false AND $count > 0; ){
      $path = preg_replace('~/[^/\.]+/\.\./~','/',$path,-1, $count);
    }
    
    return rtrim($path,"/");  
  }

}
