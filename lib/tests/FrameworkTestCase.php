<?php
/**
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
 */

class FrameworkTestCase extends UnitTestCase
{
    /**
     * A utility method to truncate the given table(s)
     *
     * @param mixed $tables the table(s) to be truncated. accepts a string or array of strings
     * @return void
     */
    protected function truncate($tables)
    {
        global $database;

        if (!is_array($tables)) {
            $tables = array($tables);
        }

        foreach ($tables as $table) {
            $database->query("TRUNCATE TABLE `$table`");
        }
    }

    /**
     * Populates the given DatabaseObject with dummy data
     *
     * @param DatabaseObject $obj
     * @return void
     */
    protected function populate(DatabaseObject &$obj)
    {
        global $database;

        $fields = $obj->getFields();
        foreach ($fields as $property => $field) {
            $def = $database->getTableFieldDefinition($obj->table, $field);
            if (!$def) {
                continue;
            }

            $type = strtolower(Params::generic($def[0], 'native_type'));
            switch ($type) {
                case 'int':
                case 'integer':
                case 'float':
                case 'currency':
                case 'decimal':
                case 'double':
                case 'real':
                case 'tinyint':
                case 'short':
                case 'long':
                    $obj->$property = mt_rand(0, 100);
                    break;
                case 'date':
                case 'datetime':
                case 'timestamp':
                    $obj->$property = time() + (mt_rand(0, (86400 * 7)) * (mt_rand(0, 1) ? 1 : -1));
                    break;
                case 'var_string':
                    $obj->$property = 'dummy string content '.uniqid();
                    break;
                default:
                    $obj->$property = uniqid();
                    break;
            }
        }
    }
}

?>
