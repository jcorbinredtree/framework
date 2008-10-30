<?php

/**
 * DMSFile definition
 *
 * PHP version 5
 *
 * LICENSE: This file is a part of the Red Tree Systems framework,
 * and is licensed royalty free to customers who have purchased
 * services from us. Please see http://www.redtreesystems.com for
 * details.
 *
 * @author     Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright  2008 Red Tree Systems, LLC
 * @license    http://www.redtreesystems.com PROPRITERY
 * @version    1.0
 * @link       http://www.redtreesystems.com
 */

/**
 * Sets up the Document Management System File Object
 */
class DMSFile extends DatabaseObject 
{	
	/**
   * 
   * @var int
   * @access public
   */
	
  public $id;
  
  /**
   * 
   * @var DMSFolder
   * @access public
   */
    
  public $folder;

  /**
   * 
   * @var int
   * @access public
   */
  
  public $dmsFolderId;
  
  /**
   * 
   * @var string
   * @access public
   */  
  
  public $fileId;
  
  /**
   * A reference to a DatabaseObjectFile
   *
   * @var DatabaseObjectFile
   */
  public $content;

  /**
   * 
   * @var string
   * @access public
   */
  
  public $keywords;
  
  /**
   * 
   * @var string
   * @access public
   */
  
  public $description;
  
  /**
   * Specifies the allowable groups
   *
   * @var int
   */
  public $aclGroupId;
  
  /**
   * Triggered when you create a DMSFile Object
   * 
   * @access public
   */
  
  public function __construct() {
    $this->table = 'dms_files';
    $this->key = 'dms_file_id';
  }
  
  public function getFolder()
  {
      if ($this->folder) {
          return $this->folder;
      }
      
      $this->folder = new DMSFolder();
      if (!$this->folder->fetch($this->dmsFolderId)) {
          return null;
      }
      
      return $this->folder;
  }
  
  public function fetch($id)
  {
      if (!parent::fetch($id)) {
          return false;
      }
      
      $this->content = new DatabaseObjectFile();
      return $this->content->fetch($this->fileId);
  }
  
  public function create()
  {
      if (!$this->content->create()) {
          return false;
      }
      
      $this->fileId = $this->content->id;
      return parent::create();
  }
  
  public function update()
  {
      throw new Exception("file update not implemented; no plans exist to support this functionality");
  }
  
  public function delete()
  {
      if (!$this->content->delete()) {
          return false;
      }
      
      return parent::delete();
  }
  
  /**
   * assigns class variables to values in incoming array
   * 
   * @access public
   * @static 
   * @param array &$where
   * @return populated DMSFile
   */
  
  public static function from( &$where ) {
    $file = new DMSFile();
    $file->merge( $where );
    return $file;
  }
  
  /**
   * Makes sure variable 'name' is not empty
   * 
   * @access public
   * @return true if name variable exists false otherwise
   */
  
  public function validate() {
    return Params::Validate( $this, array(
      'name' => 'You must name this file'
    ));
  }
}

?>