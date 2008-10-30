<?php

include_once "setup.php";

$task = Params::request('task');
$handler = new DMSHandler();

switch ($task) {
    case 'get-files':
        $handler->onGetFiles();
        break;                
    case 'get-folders':
        $handler->onGetFolders();
        break;        
    case 'get-operations-content':
        $handler->onGetOperationsContent();
        break;        
    case 'add-folder':
        $handler->onAddFolder();
        break;        
    case 'edit-folder':
        $handler->onEditFolder();
        break;        
    case 'delete-folder':
        $handler->onDeleteFolder();
        break;        
    case 'download':
        $handler->onDownloadFile();
        break;        
    case 'add-file':
        $handler->onAddFile();
        break;        
    case 'delete-file':
        $handler->onDeleteFile();
        break;
    default:
        die("unknown task");
}

print $handler->getBuffer();

?>