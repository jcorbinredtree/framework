<?php
/**
 * LICENSE: The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 *
 * The Original Code is Red Tree Systems Code.
 *
 * The Initial Developer of the Original Code is Red Tree Systems, LLC. All Rights Reserved.
 */

function insert($label, $parentId)
{
	$item = new CMSItem();
	$item->seo = new CMSSEOProperties();
	$item->label = $label;

	if (!$item->create()) {
		die("wtf man");
	}

	if ($parentId) {
	   if (!CMSRelationship::add($item->id, $parentId)) {
         die('no luv');
       }
	}
	else {
	   $relationship = new CMSRelationship();
       $relationship->itemId = $item->id;
       $relationship->relationId = null;
       $relationship->relationship = 'parent';
       $relationship->distance = 0;
       if (!$relationship->create()) {
            die('relationship issues');
       }
	}

    return $item->id;
}

function getLabel()
{
	global $words;

    $numWords = mt_rand(1, 3);
    $label = '';

    for ($i = 0; $i < $numWords; $i++) {
    	if ($i) {
    		$label .= ' ';
    	}

    	$index = mt_rand(0, (count($words) - 1));
    	$label .= $words[$index];
    }

    return $label;
}

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

$current = new Current();

$topLevel = array('Home', 'About', 'Contact Us', 'Blogs', 'Sponsor');

$words = array('Corporate', 'Sponsor', 'About', 'Mission', 'Contractors', 'Contact',
               'Sponsor', 'Template', 'Ipsum', 'Agenda', 'Binder', 'Blog', 'Web 2.0',
               'RFP', 'Finance', 'Accounting', 'Product', 'Mission', 'Statement',
               'Proactive');

print "TRUNCATE `application_data`;
TRUNCATE `cms_content`;
TRUNCATE `cms_items`;
TRUNCATE `cms_item_relationships`;
TRUNCATE `cms_item_tags`;
TRUNCATE `cms_menu`;
TRUNCATE `cms_panels`;
TRUNCATE `cms_seo_properties`;\n";

foreach ($topLevel as $word) {
	$topLevelId = insert($word, 0);

	$children = mt_rand(5, 15);
	for ($i = 0; $i < $children; $i++) {
		$childId = insert(getLabel(), $topLevelId);

		$subTree = mt_rand(5, 15);
		for ($x = 0; $x < $subTree; $x++) {
			insert(getLabel(), $childId);
		}
	}
}

?>
