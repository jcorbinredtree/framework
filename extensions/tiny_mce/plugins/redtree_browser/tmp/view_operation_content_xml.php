<?php global $config;  if($this->file){ ?>
        <div class = "dms-file-description">
            <?php $desc = '';  if($this->file->description){  $desc = $this->file->description;  }  else{  $desc = '<p>No description</p>';  } ?>
            
            <div class = "dms-preview-pane">
                <?php if($this->category == 'image'){ ?>
                    <img id = "image-previewer" alt = "preview" />
                <?php }  echo $desc; ?>
            </div>
        </div>
        
        <ul>
            <li><a href = "javascript:ui.fireEvent('fileSelected', ui.currentFile);"><img src = "<?php echo $config->absUri; ?>/icons/accept.png" alt = "select file" /> Choose File</a></li>                
            <li><a href = "javascript:dms.onDownloadFile();"><img src = "<?php echo $config->absUri; ?>/icons/download.png" alt = "download file" /> Download File</a></li>                
        </ul>
   <?php } ?>
   
    <ul>
        <li><a href = "javascript:dms.onAddFile()"><img src = "<?php echo $config->absUri; ?>/icons/page_white_add.png" alt = "add file" /> Add File</a></li>                
        <li><a href = "javascript:dms.onDeleteFile()"><img src = "<?php echo $config->absUri; ?>/icons/page_delete.png" alt = "delete file" /> Delete File</a></li>                            
    </ul>   
    
    <ul>
        <li><a href = "javascript:dms.onAddFolder();"><img src = "<?php echo $config->absUri; ?>/icons/folder_add.png" alt = "add folder" /> Add Folder</a></li>
        <li><a href = "javascript:dms.onEditFolder();"><img src = "<?php echo $config->absUri; ?>/icons/folder_edit.png" alt = "edit folder" /> Edit Folder</a></li>
        <li><a href = "javascript:dms.onDeleteFolder();"><img src = "<?php echo $config->absUri; ?>/icons/folder_delete.png" alt = "delete folder" /> Delete Folder</a></li>
    </ul>
