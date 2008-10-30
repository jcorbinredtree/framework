<?php

class DatabaseObjectFile extends DatabaseObject
{
    const FILE_KEY_USER = -1;
    
    public $FILE_TABLE = 'file_info';
    public $FILE_CHUNK_TABLE = 'file_chunks';
    public $CHUNK_SIZE = 65536;
    
    public $name;
    public $mimeType;
    public $size;
    public $chunks;
    public $at;
    
    public $fileKey = '';
    public $dataFile = null;
    
    protected $chunkPtr = 0;
    
    public static function from(&$where) { throw new Exception("no"); }
    public function validate() { throw new Exception("no"); }

    public function __construct()
    {
        $this->key = 'file_id';
        $this->table = $this->FILE_TABLE;
    }
    
    public function create()
    {
        if ($this->fileKey != DatabaseObjectFile::FILE_KEY_USER) {
            $file = $this->getFile();
            if (!$file) {
                return false;
            }
        
            $this->setFileInfo($file);
        }
        
        $this->chunks = (int) ceil($this->size / $this->CHUNK_SIZE);
        $this->at = time();
        
        if (!parent::create()) {
            return false;    
        }
        
        return $this->setChunks();
    }
    
    public function update()
    {
        if ($this->fileKey != DatabaseObjectFile::FILE_KEY_USER) {
            $file = $this->getFile();
            if (!$file) {
                return false;
            }
            
            $this->setFileInfo($file);
        }
        
        $this->chunks = (int) ceil($this->size / $this->CHUNK_SIZE);
        
        if (!$this->purgeChunks()) {
            return false;
        }        
        
        $this->at = time();
        
        if (!parent::update()) {
            return false;    
        }        
        
        return $this->setChunks();
    }
    
    public function purgeChunks()
    {
        global $database;
        
        $sql = "DELETE FROM `$this->FILE_CHUNK_TABLE` WHERE file_id = $this->id";
        if ($database->delete($sql) < 0) {
            return false;
        }

        return true;
    }
    
    public function delete()
    {
        if (!$this->purgeChunks()) {
            return false;
        }
        
        return parent::delete();
    }
    
    protected function getFile()
    {
        if (!array_key_exists($this->fileKey, $_FILES)) {
            return null;
        }
        
        $file = $_FILES[$this->fileKey];
        
        if (!$file['size']) {
            return null;
        }
        
        return $file;
    }
    
    protected function setFileInfo(&$file)
    {
        if (!$this->fileKey) {
            throw new Exception('fileKey property not set');
        }
        
        $this->name = $file['name'];
        $this->mimeType = $file['type'];
        $this->size = $file['size'];
        $this->dataFile = $file['tmp_name'];
    }
    
    public function getNextChunk()
    {
        global $database;
        
        if ($this->chunkPtr > $this->chunks) {
            return null;
        }
        
        $this->chunkPtr++;
        $sql = "SELECT `data` FROM `$this->FILE_CHUNK_TABLE` WHERE file_id = $this->id AND chunkno = $this->chunkPtr LIMIT 1";
        if ($database->query($sql) && $database->count()) {
            return $database->getScalarValue();
        }
        
        return null;
    }
    
    public function stream()
    {
        $this->chunkPtr = 0;
        
        header("Content-Type: $this->mimeType");
        header("Content-Length: $this->size");
        header("Last-Modified:" . gmdate('r', $this->at));
        header("Expires:" . gmdate('r', time() + 3600));
        header("Cache-Control: max-age=3600, must-revalidate");
        header("Pragma: public");
        
        while ($chunk =& $this->getNextChunk()) {
            print $chunk;
            ob_flush();       
            flush();
        }
    }
    
    public function setChunks()
    {
        global $database;
        
        if (!$this->dataFile) {
            throw new Exception("dataFile property not set");
        }
        
        if (!file_exists($this->dataFile)) {
            return null;
        }
        
        $fp = fopen($this->dataFile, 'rb');
        if (!$fp) {
            return false;
        }
        
        $chunkno = 0;
        while (!feof($fp)) {
            $chunkno++;
                        
            $sql = "INSERT INTO `$this->FILE_CHUNK_TABLE` SET file_id = $this->id, chunkno = $chunkno, data = 0x" . bin2hex(fread($fp, ($this->CHUNK_SIZE - 1)));
            if ($database->insert($sql) < 0) {
                return false;
            }            
        }
        
        fclose($fp);
        
        return true;
    }
}

?>