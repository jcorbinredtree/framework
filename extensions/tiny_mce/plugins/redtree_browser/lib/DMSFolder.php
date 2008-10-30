<?php

/**
 * DMSFolder definition
 *
 * PHP version 5
 *
 * LICENSE: This file is a part of the Red Tree Systems framework,
 * and is licensed royalty free to customers who have purchased
 * services from us. Please see http://www.redtreesystems.com for
 * details.
 *
 * @category   IRequestObject
 * @author     Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright  2006 Red Tree Systems, LLC
 * @license    http://www.redtreesystems.com PROPRITERY
 * @version    1.0
 * @link       http://www.redtreesystems.com
 */

/**
 * Sets up the Document Management System Folder Object
 *
 * @category   IRequestObject
 */

class DMSFolder extends DatabaseObject
{
    /**
     * The category this is from
     *
     * @var string
     */
    public $category;
    
    /**
     * The id of the DMSFolder parent
     *
     * @var int
     */
    public $parentId;
    
    /**
     *
     * @var string
     * @access public
     */
    public $name;

    /**
     * Merges passed array with object variables
     * @static
     * @access public
     * @param array
     * @return DMSFolder
     */
    public static function from(&$where) 
    {
        $us = new DMSFolder();
        $us->merge($where);
        return $us;
    }
    
    public static function getTopLevelFolders($category)
    {
        global $database;
        
        $sql = "SELECT * FROM dms_folders WHERE category = '$category' AND (parent_id IS NULL OR parent_id = 0) ORDER BY `name` ASC";
        return $database->queryForResultObjects($sql, 'DMSFolder');        
    }
    
    public function getChildFolders()
    {
        global $database;
        
        $sql = "SELECT * FROM dms_folders WHERE parent_id = $this->id ORDER BY `name` ASC";
        return $database->queryForResultObjects($sql, 'DMSFolder');        
    }
    
    public function getFiles()
    {
        global $database;
        
        $file = new DMSFile();        
        $sql = "SELECT A.$file->key FROM $file->table A INNER JOIN file_info B ON B.file_id = A.file_id 
                WHERE dms_folder_id = $this->id ORDER BY B.`name` ASC";
        
        $out = array();
        $ids = $database->queryForResultValues($sql);
        foreach ($ids as $id) {
            $o = new DMSfile();
            $o->fetch($id);

            array_push($out, $o);
        }
        
        return $out;
    }
    
    public function delete()
    {
        $children = $this->getChildFolders();
        foreach ($children as $folder) {
            if (!$folder->delete()) {
                return false;
            }
        }
        
        $files = $this->getFiles();
        foreach ($files as $file) {
            if (!$file->delete()) {
                return false;
            }
        }
        
        return parent::delete();
    }

    /**
     * Called when DMSFolder object is instantiated
     * @access public
     * @return void
     */
    public function __construct() 
    {
        $this->key = 'dms_folder_id';
        $this->table = 'dms_folders';
    }

    public function validate() {
        return Params::Validate( $this, array(
            'name' => 'Please enter a name for this folder'
      ) );
    }
}

?>