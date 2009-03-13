<?php

/*
 * Basic example index file
 *
 * Apache needs to be configured to rewrite all url onto this script like so:
 * http://example.com/some/path/to/the/site
 *     becomes
 * http://example.com/some/path/index.php/to/the/site
 *     presuming that the site's document root is at /some/path on example.com
 */

// This site was developed against this version of the framework
$FrameworkTargetVersion = '3.1';
require_once 'framework/Loader.php';

class CMSStageSite extends Site
{
    protected $mode = /* Site::MODE_TEST | */ Site::MODE_DEBUG;

    public static $Modules = array(
        // 'I18N'
        // 'Session'
        'CMS'
    );
}
Site::set('CMSStageSite')->handle();

# vim:set sw=4 ts=4 expandtab:
?>
