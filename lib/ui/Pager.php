<?php

/**
 * Pager class definition
 *
 * PHP version 5
 *
 * LICENSE: This file is a part of the Red Tree Systems framework,
 * and is licensed royalty free to customers who have purchased
 * services from us. Please see http://www.redtreesystems.com for
 * details.
 *
 * @category     UI
 * @package        Utils
 * @author         Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2006 Red Tree Systems, LLC
 * @license        http://www.redtreesystems.com PROPRITERY
 * @version        1.0
 * @link             http://www.redtreesystems.com
 */

/**
 * Pager
 *
 * This class aims to improve pagnation
 *
 * @category     UI
 * @package        Utils
 */
class Pager extends SessionObject
{
 /**
     * Holds the current number of results to display for this page
     * 
     * @access public
     * @var int
     */
    public $resultsPerPage = 10;
    
    /**
     * Holds the current number of results in the set
     * 
     * @access public
     * @var int
     */    
    public $results;
        
    /**
     * Holds the current record pointer start
     * 
     * @access public
     * @var int
     */            
    public $start;
    
    /**
     * Holds the current sort criteria
     * 
     * @access public
     * @var mixed
     */            
    public $sort;
            
    /**
     * Acts as the ordering flag
     * 
     * @access public
     * @var boolean
     */            
    public $asc = true;

    /**
     * Represents the number of total pages
     * 
     * @access public
     * @var int
     */
    public $totalPages;
    
    /**
     * Represents the current page number
     * 
     * @access public
     * @var int
     */
    public $currentPage;
    
    /**
     * Holds the component to use for links
     * 
     * @access public
     * @var mixed
     */
    public $component = null;
    
    /**
     * Holds the action to use for links
     * 
     * @access public
     * @var mixed
     */
    public $action = '';

    /**
     * Holds additional arguments to use to make links
     * 
     * @access public
     * @var array
     */
    public $args = array();
    
    /**
     * Creates an obect of this type, and populates it from $where
     * 
     * @param array $where the object to merge with
     * @return Pager
     */
    public static function From(&$where)
    {
        $us = new Pager();
        $us->merge($where);
        return $us;
    }

    /**
     * Doesn't make sense for this object - we'll just return true
     * 
     * @return boolean
     */
    public function validate()
    {
        return true;
    }
    
    /**
     * Sets the number of results
     * 
     * @param int $results the number of results
     * @return void
     */
    public function setResults($results)
    {
        $this->results = $results;
        
        $this->totalPages = ceil($this->results / $this->resultsPerPage);
        $this->currentPage = ceil($this->start / $this->resultsPerPage); 
    }

    /**
     * Gets the SQL direction for this pager
     * 
     * @return string ASC or DESC
     */
    public function getDirection()
    {
        return ($this->asc ? 'ASC' : 'DESC');
    }
    
    /**
     * Gets the SQL for a limit command. MySQL-specific?
     * 
     * @return string
     */
    public function getLimit()
    {
        return 'LIMIT ' . (int) $this->start . ', ' . (int) $this->resultsPerPage;
    }    
    
    /**
     * Prints a pager inline to the browser
     * 
     * @access public
     * @return void
     */
    public function makeNavigation()
    {
        $pageUp = $this->currentPage;
        $pageDown = (($this->currentPage - 5 >= 0) ? ($this->currentPage - 5) : -1);

        if ((!$this->results) || ($this->totalPages < 2)) { 
            return; 
        }

        if ($this->currentPage) {
            $page = $this->currentPage - 1;

            print '<a href = "' . $this->getPageLink($page, $this->calculateStart($page)) . '">&lt; Prev</a> ';
        }
        
        while (++$pageDown < $this->currentPage) {
            print '<a href = "' . $this->getPageLink($pageDown, $this->calculateStart($pageDown)) . '">' . ($pageDown + 1) . '</a> ';
        }

        print "<b>" . ($this->currentPage + 1) . "</b> ";

        while (++$pageUp < ($this->currentPage + 5) && ($pageUp <= $this->totalPages)) {
            $start = $this->calculateStart($pageUp);
            if ($start < $this->results) {            
                print '<a href = "' . $this->getPageLink($pageUp, $start) . '">' . ($pageUp + 1) . '</a> ';
            }
        }
        
        if ($this->currentPage < $this->totalPages) {
            $page = ($this->currentPage + 1);
            $start = $this->calculateStart($page);
            if ($start < $this->results) {
                print '<a href = "' . $this->getPageLink($page, $start) . '">Next &gt;</a> ';
            }
        }
    }
        
    /**
     * This method is called from makeNavigation(). 
     * 
     * @access protected
     * @param int $page the page number
     * @param int $start where to start
     * @return string
     */
    protected function getPageLink($page, $start)
    {
        global $current;
        
        $args = $this->args;
        $args['start'] = $start;
        
        $component = $this->component ? $this->component : $current->component->getClass();
        $action    = $this->action    ? $this->action    : $current->action->id;

        return call_user_func_array(array($component, 'getActionURI'), array($component, $action, Stage::VIEW, $args));
     }    
    
    /**
     * Calculates, based on the $page, the value of $start
     * 
     * @access private
     * @param int $page the current page number
     * @return int the caclulated start
     */
    private function calculateStart($page)
    {
        return ($page * $this->resultsPerPage);
    }
    
    public function merge(&$with)
    {
        parent::merge($with);        
        
        $this->setResults($this->results);
    }
}

?>
