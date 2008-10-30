<?php

include_once "setup.php";

$template = new DMSTemplate();
$template->assign('config', $config);
$template->assign('category', $_GET['type']);
$template->assign('folders', DMSFolder::getTopLevelFolders($template->category));
$template->display("view/container.xml");

?>
<!-- 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{#redtree_browser_dlg.title}</title>
    <script type="text/javascript" src="../../tiny_mce_popup.js"></script>
    <script type="text/javascript" src="js/browser_dialog.js"></script>
    <link href="css/browser_dialog.css" rel="StyleSheet" type="text/css" />
</head>
<body>

<form onsubmit="RedtreeBrowserDialog.insert();return false;" action="#">
    <div id="pathwayPanel">
        <label for="pathway">{#redtree_browser_dlg.path}:&nbsp;</label><input type="text" size="20" id="pathway" name="pathway" />
    </div>
    <div id="folderListPanel">
        <select name="folder_list" id="folder_list" size="5"></select>
    </div>
    <div id="fileListPanel">
        <select name="file_list" id="file_list" size="5"></select>
    </div>
    <div class="clear"></div>
    <div class="mceActionPanel">
        <div style="float: left">
            <input type="button" id="insert" name="insert" value="{#insert}" onclick="RedtreeBrowserDialog.insert();" />
        </div>

        <div style="float: right">
            <input type="button" id="cancel" name="cancel" value="{#cancel}" onclick="tinyMCEPopup.close();" />
        </div>
    </div>
</form>

</body>
</html>
 -->