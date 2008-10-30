<?php

if (!function_exists('json_encode'))
{
  function json_encode($a=false)
  {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a))
      {
	if (is_float($a))
	  {
	    // Always use "." for floats.
	    return floatval(str_replace(",", ".", strval($a)));
	  }

	if (is_string($a))
	  {
	    static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
	    return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
	  }
	else
	  return $a;
      }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
      {
	if (key($a) !== $i)
	  {
	    $isList = false;
	    break;
	  }
      }
    $result = array();
    if ($isList)
      {
	foreach ($a as $v) $result[] = json_encode($v);
	return '[' . join(',', $result) . ']';
      }
    else
      {
	foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
	return '{' . join(',', $result) . '}';
      }
  }
}

$__class = '';

function include_class($file)
{
    global $__class;
    
    if (preg_match("|/$__class.php$|", $file)) {  
        include_once $file;
    }
}

function __autoload($class)
{
    global $config, $__class;
    
    if (!$config || !class_exists('File')) {
        return;
    }
    
    $__class = $class;
    File::find('include_class', "$config->absPath/lib");
}

/*
 * use the framework config
 * 
 * include_once "Config.php";
 */
include_once "../../../../Config.php";

$config = new Config();

session_name($config->getCookieName());
session_start();
if (!array_key_exists('user_id', $_SESSION)) {
    //die("please log in first");
}

$config->absPath = dirname(__FILE__);
$config->absUri = 'http://' . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']);
$config->compileDir = "$config->absPath/tmp/";

include_once "$config->absPath/lib/util/File.php";

$database = new Database();

?>