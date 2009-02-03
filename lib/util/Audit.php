<?php

/**
 * Audit class definition
 *
 * PHP version 5
 *
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
 *
 * @category     Utils
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * The audit class represents a single entry in the audit_log table,
 * and is often used in conjuction with the AuditComponent base class.
 * The audit_log table should be:
 *
 * CREATE TABLE `audit_log` (
 * `audit_id` int(11) NOT NULL auto_increment,
 * `user_id` int(11) NOT NULL,
 * `subject` int(11) NOT NULL,
 * `action` int(11) NOT NULL,
 * `row_id` int(11) NOT NULL,
 * PRIMARY KEY  (`audit_id`)
 * )
 *
 * @package      Utils
 */
class Audit extends DatabaseObject
{
    /**
     * Represents the add value for action
     *
     * @var int
     */
    const ACTION_ADD = 1;

    /**
     * Represents the edit value for action
     *
     * @var int
     */
    const ACTION_EDIT = 2;

    /**
     * Represents the delete value for action
     *
     * @var int
     */
    const ACTION_DELETE = 2;

    /**
     * The user_id field
     *
     * @var int
     */
    public $userId;

    /**
     * The subject table. This is an application-specific code
     *
     * @var int
     */
    public $subject;

    /**
     * An action identifier. One of Audit::ACTION_ADD, Audit::ACTION_EDIT, or
     * Audit::ACTION_DELETE
     *
     * @var int
     */
    public $action;

    /**
     * The primary key of the subject table. This is an int field. I don't
     * care that your freaky table doesn't use int as the primary key.
     *
     * @var int
     */
    public $rowId;

    /**
     * Describes the specifics of the action as a string. This field could
     * support binary at some point in time, but I believe is better left as
     * a straight text description.
     *
     * @var string
     */
    public $data;

    /**
     * The requestobject from implementation
     *
     * @param array $where
     * @return Audit
     */
    public static function from(&$where)
    {
        $us = new Audit();
        $us->merge($where);
        return $us;
    }

    /**
     * Performs validation
     *
     * @return boolean true upon success
     */
    public function validate()
    {
        return Params::validate($this, array(
            'userId'  => 'Missing audit user',
            'subject' => 'Missing audit subject',
            'action'  => 'Missing audit action',
            'rowId'   => 'Missing audit row id'
        ));
    }

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->key = 'audit_id';
        $this->table = 'audit_log';
    }
}

?>
