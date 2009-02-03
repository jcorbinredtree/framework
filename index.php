<?php

# Basic example index file
#
# Apache needs to be configured to rewrite all url onto this script like so:
#   http://somehost/some/path/to/the/site
#     becomes
#   http://somehost/some/path/index.php/to/the/site
#     presuming that the site's document root is at /some/path on somehost
#
# Furthermore, all direct requests to the SITE/ directory should be denyed

function onConfig(Config &$config)
{
    $config->setDebugMode(true);
    $config->setDatabaseInfo('mysql://redtreedev:redtreesystems@localhost/fw');
    $config->setDatabaseTestInfo('mysql://redtreedev:redtreesystems@localhost/fw_test');
    $config->addMailerOptions(array(
        'From' => 'bprudent@redtreesystems.com',
        'From Name' => 'Brandon Prudent',
        'Host' => 'localhost'
    ));

    # $config->setDefaultComponent('SomeComponent');
    # $config->setDefaultAction('home');

    # $config->addUrlMapping('some/weird/mapping.html', array('SomeComponent', 'home', 'id=2'));
}

include 'SITE/framework/start.php';

?>
