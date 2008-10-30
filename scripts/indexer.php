<?php

/*
 * autoload in Application.php
 */

require '../Config.php';

$config = new Config();
require "$config->absPath/lib/application/Application.php";

if (!$config->cli) {
    die("this can only be run from the command line");
}

$_SESSION = array();

Application::requireMinimum();

$database = new Database();
$database->log = $database->time = $config->isDebugMode();

function absToRel($path)
{
	global $config;
	
	return preg_replace('|^' . $config->absPath . '[/]|i', '', $path);
}

function found($file)
{
    global $config;
        
    /*
     * make sure each new directory gets the compilation hints file first
     */
    if (is_dir($file)) {    	
        $file = "$file/compile.php";    
        if (file_exists($file)) {
        	print "compilation file $file\n";
        	
	    	/*
	    	 * compilation hints
	    	 */
	        $classes = $css = $js = null;
	        $dir = dirname($file);
	        include $file;
	        
	        if ($classes && is_array($classes) && count($classes)) {	        	
	        	foreach ($classes as $desc) {
                    if (array_key_exists('path', $desc)) {
                        $desc['path'] = absToRel($dir) . '/' . $desc['path'];
                    }
	        		
	        		
	                $item = new ApplicationItem();
	                Params::arrayToObject($desc, $item);
	                $item->create();
	        	}
	        }
            
            if ($css && is_array($css) && count($css)) {
                throw new Exception('not implemented');
            }
            
            if ($js && is_array($js) && count($js)) {
                throw new Exception('not implemented');
            }
	        
	        $classes = $css = $js = null;
        }

        return;
    }
    
    /*
     * exclude non php files
     */
    if (!preg_match('|[.]php$|', $file)) {
    	return;
    }
    
    /*
     * exclude compile.php files
     */
    if (preg_match('|[/]compile[.]php|', $file)) {
    	return;
    }
    
    $item = new ApplicationItem();
    $item->path = absToRel($file);
    $item->class = preg_replace('|^(?:.+)[/](.+?)(?:[.]php)$|', '$1', $item->path);
    
    /*
     * if we already have this class (via compile rules) then ignore it
     */
    if (ApplicationItem::classExists($item->class)) {
    	return;
    }
    
    /*
     * exclude known directories (that don't contain framework code)
     */
    if (   preg_match('|^build[/]|', $item->path) 
        || preg_match('|^logs[/]|' , $item->path) 
        || preg_match('|^cache[/]|', $item->path) 
        || preg_match('|^scripts[/]|', $item->path)
        || preg_match('|^extensions[/]|', $item->path))
    {
        print "skipping $item->path\n";
        return;        
    }
    
    switch ($item->path) {
        case 'admin/index.php':
        case 'admin/login.php':
        case 'index.php':
            print "skipping $item->path\n";
            return;
    }    
    
    $item->create();    
}

$database->executef('TRUNCATE TABLE application_map');

File::find('found', $config->absPath, false);

?>