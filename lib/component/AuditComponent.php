<?php

/**
 * AuditComponent class definition
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
 * @category     Components
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      2.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Provides some helper methods for an system that tracks audits
 *
 * @package      Components
 */
abstract class AuditComponent extends Component
{
    /**
     * Records an audit trail
     *
     * @param array $desc the associtive array mapping the
     * db field names from the audit_log table to values
     */
    public function audit($desc)
    {
        $audit = Audit::from($desc);
        if (!$audit->validate()) {
            return false;
        }

        return $audit->create();
    }
}

?>
