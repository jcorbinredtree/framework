<?php

class DMSHandler
{
    protected $buffer = array();
    
    public function getBuffer()
    {
        return implode('', $this->buffer);
    }
    
    public function write($data)
    {
        array_push($this->buffer, $data); 
    }
    
    public function viewTemplate($t, $a)
    {
        $template = new DMSTemplate();
        foreach ($a as $n=>$v) {
            $template->$n = $v;
        }
        
        $this->write($template->fetch($t));
    }
    
    public function onGetFiles()
    {
        $folder = new DMSFolder();
        $folder->fetch(Params::post('folder_id'));
            
        if (!$folder->id) {
            return;
        }        
        
        $files = $folder->getFiles();
        
        $this->write("new Array(");
        $first = true;
        foreach ($files as $file) {
            if (!$first) {
                $this->write(',');
            }
            
            $content =& $file->content;
            
            $content->id = $file->id;
            $size = round($content->size / 1024);
            if ($size < 1) {
                $size = 1;
            }
            
            $content->size = sprintf('%d', $size) . 'Kb';
            
            $this->write(json_encode($content));
            
            $first = false;
        }
        $this->write(")");
    }
    
    public function onGetFolders()
    {
        $folder = new DMSFolder();
        $folder->fetch(Params::post('folder_id'));
     
        $folders = $folder->getChildFolders();
        
        $this->write('new Array(');
        $first = true;
        foreach ($folders as $node) {
            if (!$first) {
                $this->write(',');
            }

            $this->write(json_encode($node));

            $first = false;
        }
        $this->write(')');        
    }

    public function onGetOperationsContent()
    {
        $folder = new DMSFolder();
        $folder->fetch(Params::post('folder_id'));
        
        $file = null;
        if ($fid = Params::post('file_id')) {
            $file = new DMSFile();
            $file->fetch($fid);
        }
        
        $this->viewTemplate('view/operation-content.xml', array(
            'file' => $file,
            'category' => Params::GET('category'),
            'folder' => $folder
        ));
    }
    
    public function onAddFile()
    {
        header('Content-Type: text/plain');
        
        $file = new DMSFile();
        $file->dmsFolderId = Params::post('file_folder_id');
        $file->keywords = Params::post('keywords');
        $file->description = Params::post('description');
        
        $file->content = new DatabaseObjectFile();
        $file->content->fileKey = 'content';
                
        if ($name = Params::POST('file_name')) {
            $_FILES['content']['name'] = $name;
        }
                
        if (!$file->create()) {
            header('HTTP/1.1 500 Internal Server Error');
            exit(0);
        }
        
        $this->write('OK');
    }
    
    public function onDeleteFile()
    {
        $file = new DMSFile();
        $file->fetch(Params::post('file_id'));        
        $file->delete();
    }
    
    public function onAddFolder()
    {
        $folder = new DMSFolder();
        
        $folder->parentId = Params::POST('parent_id');
        if (!$folder->parentId) {
            $folder->parentId = null;
        }
        
        $folder->name = Params::POST('folder_name');
        $folder->category = Params::GET('category');
        
        if (!$folder->create()) {
            header('HTTP/1.1 500 Internal Server Error');
            print "-1";
            exit(0);
        }
        
        print $folder->id;
    }
    
    public function onEditFolder()
    {
        $folder = new DMSFolder();
        $folder->fetch(Params::POST('folder_id'));
        $folder->name = Params::POST('folder_name');
        if (!$folder->update()) {
            header('HTTP/1.1 500 Internal Server Error');
            exit(0);
        }        
    }
    
    public function onDeleteFolder()
    {
        $folder = new DMSFolder();
        $folder->fetch(Params::post('folder_id'));
        $folder->delete();
    }
    
    /**
     * outputs requested file through browser
     *
     * @access public
     * @return void
     */

    public function onDownloadFile() 
    {
        global $current;
        
        $file = new DMSFile();        
        if (!$file->fetch(Params::REQUEST('id'))) {
            return;
        }
        
        if ($file->aclGroupId && (!$current->user || !$current->user->in($file->aclGroupId))) {
            header("HTTP/1.1 403 Forbidden");
            die("I'm sorry, you don't have access to this resource");
        }
        
        $folder =& $file->getFolder();        
        header('Content-Disposition: ' . (($folder->category == 'file') ? 'attachment' : 'inline') . ';filename="' . $file->content->name . '"');        
                
        $file->content->stream();
        exit(0);
    }
}

?>