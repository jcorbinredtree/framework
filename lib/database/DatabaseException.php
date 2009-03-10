<?php

/**
 * DatabaseException class definition
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
 * @category     Database
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      3.0
 * @link         http://framework.redtreesystems.com
 */

class DatabaseException extends RuntimeException
{
    /**
     * @param db Database
     * @param what string
     * @param why string
     */
    public function __construct(Database $db, $what, $why=null)
    {
        parent::__construct("$what failed: $why");
        $this->errorLog($db, $what, $why);
    }

    /**
     * Logs an error message through Config.
     *
     * @param what string what the caller did that went badly
     * @param why string why it didn't work out (optional)
     */
    private function errorLog(Database $db, $what, $why=null)
    {
        $mess = "Database::$what failed: $why";

        if ($db->isTiming()) {
            $lt = $db->getLastTime();
            if (isset($lt)) {
                $mess .= ', '.sprintf('time: %.4f seconds', $lt);
            }
        }

        # Usually looks something like:
        #   Database::action(details), failed: it didn't work out, time: n.mmmm seconds
        $db->getSite()->log->error($mess, 4);
    }
}

?>
