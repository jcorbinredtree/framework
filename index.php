<?php

/*
 * Basic example index file
 *
 * Apache needs to be configured to rewrite all url onto this script like so:
 * http://example.com/some/path/to/the/site
 *     becomes
 * http://example.com/some/path/index.php/to/the/site
 *     presuming that the site's document root is at /some/path on example.com
 *
 * See apache.conf for more setup details
 */

// This site was developed against this version of the framework
//   See Loader.php for what else can be set here
$FrameworkTargetVersion = '3.1';
require_once('framework/Loader.php');

class MySite extends Site
{
    public static $Modules = array(
      // A module is currently just something loadable as:
      //   lib/MODULE/module.php
      //
      // The framework currently has the following modules:
      //   I18N
      //   Session
      //   Mailer
      //   Database
      //   PageSystem
      //   TemplateSystem
    );
    protected $mode = /* Site::MODE_TEST | */ Site::MODE_DEBUG;
}
Site::set('MySite')->handle();

?>
