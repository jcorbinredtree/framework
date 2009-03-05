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
 * Furthermore, all direct requests to the SITE/ directory should be denied.
 *
 * An example apache config:
    <Directory /path/to/project>
        RewriteEngine On
        RewriteBase /project

        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.*)$ index.php [L]
    </Directory>
    <Directory /path/to/project/SITE>
        Deny from all
    </Directory>
 */

require_once('SITE/framework/SiteLoader.php');

class MySite extends Site
{
    protected $mode = /* Site::TEST_MODE | */ Site::DEBUG_MODE;

    public function onConfig()
    {
        // This site was developed against this version of the framework
        $this->config->setTargetVersion("3.1");

        $this->config->setDatabaseInfo('mysql://name:pass@localhost/dbname');
        $this->config->addMailerOptions(array(
            'From'      => 'client@example.com',
            'FromName'  => 'Mr Person',
            'Host'      => 'localhost'
        ));
    }
}
Site::doRole('MySite', 'web');

?>
