<?php global $config; ?>    
    <html xmlns = "http://www.w3.org/1999/xhtml">
        <head>
            <title>Resource Browser</title>          


             
            <link rel = "stylesheet" type = "text/css" href = "<?php echo $this->config->absUri; ?>/css/yui/all.css" /> 
            <script src = "<?php echo $this->config->absUri; ?>/js/all.js" type = "text/javascript"></script>
                          
        </head>         
        <body class = "yui-skin-sam">
            <script type = "text/javascript">
             
                config.category = '<?php echo $this->category; ?>';            
            
                config.absURI = '<?php echo $this->config->absUri; ?>';
            
                config.getFilesURI = '<?php echo $this->config->absUri; ?>/controller.php?task=get-files';
                config.getFoldersURI = '<?php echo $this->config->absUri; ?>/controller.php?task=get-folders';
                config.getOperationsContentURI = '<?php echo $this->config->absUri; ?>/controller.php?task=get-operations-content&category=' + config.category;
                
                config.addFolderURI = '<?php echo $this->config->absUri; ?>/controller.php?task=add-folder&category=' + config.category;
                config.editFolderURI = '<?php echo $this->config->absUri; ?>/controller.php?task=edit-folder';
                config.deleteFolderURI = '<?php echo $this->config->absUri; ?>/controller.php?task=delete-folder';
                
                config.downloadURI = '<?php echo $this->config->absUri; ?>/controller.php?task=download';
                
                config.deleteFileURI = '<?php echo $this->config->absUri; ?>/controller.php?task=delete-file';                
            
            </script>    
                        
            <div id = "dms-panel"></div>  
            
            <div id = "dms-folders">
                <?php $this->display('view/tiles/folders.xml'); ?>
            </div>
            
            <div id = "dms-right-panel">
                <div id = "dms-main">
                    <?php $this->display('view/tiles/main.xml'); ?>
                </div>
                <div id = "dms-operations">
                    <?php $this->display('view/tiles/operations.xml'); ?>
                </div>
            </div>    
            
            <div id = "dialog-container" style = "position:absolute;left:-10000px;top:-10000px;">   
                <div id = "folderDialog">
                    <div class = "hd">Folder</div>
                    <div class = "bd">
                        <form method = "post" action = "#">
                            <div><input type = "hidden" name = "folder_id" id = "folder_id" /></div>
                            <div><input type = "hidden" name = "parent_id" id = "parent_id" /></div>                
                            
                            <label>Folder Name: <input type = "text" name = "folder_name" id = "folder_name" /></label>
                        </form>
                    </div>
                </div>
            
                <div id = "waitDialog"></div> 
                            
                <div id = "fileDialog">
                    <div class = "hd">Add File</div>
                    <div class = "bd">
                        <form method = "post" action = "<?php echo $this->config->absUri; ?>/controller.php?task=add-file" enctype = "multipart/form-data">
                            <div><input type = "hidden" name = "file_folder_id" id = "file_folder_id" /></div>
                                    
                            <table>
                                <tr>
                                    <th><label for = "content">File</label></th>
                                    <td><input type = "file" id = "content" name = "content" /></td>
                                </tr>
                                <tr>
                                    <th><label for = "file_name">Name</label></th>
                                    <td><input type = "text" maxlength = "255" id = "file_name" name = "file_name" /></td>
                                </tr>
                                <tr>
                                    <td colspan = "2" class = "small">(leave blank for default file name. if specified, specify the full extension. ex: new-file-name.pdf)</td>
                                </tr>
                                <tr>
                                    <th><label for = "keywords">Keywords</label></th>
                                    <td><textarea id = "keywords" name = "keywords"></textarea></td>
                                </tr>
                                <tr>
                                    <th><label for = "description">Description</label></th>
                                    <td><textarea id = "description" name = "description"></textarea></td>
                                </tr>
                           </table>
                       </form>
                    </div>    
                </div>       
            </div>            
       </body>
   </html>
