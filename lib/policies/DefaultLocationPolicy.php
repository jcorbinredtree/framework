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

class DefaultLocationPolicy implements ILocationPolicy
{
    public function getTemplatesDir()
    {
        global $config;

        return "$config->absPath/SITE/writable/cache/templates";
    }

    public function getLogsDir()
    {
        global $config;

        return "$config->absPath/SITE/writable/logs";
    }

    /**
     * Gets the location of the cache directory.
     * This directory should be writable.
     *
     * @return string the location of the cache directory.
     */
    public function getCacheDir()
    {
        global $config;

        return "$config->absPath/SITE/writable/cache";
    }

    public function logs()
    {
        global $config;

        $logDir = DefaultLocationPolicy::getLogsDir();
        if (file_exists($logDir)) {
            if (!is_writable($logDir)) {
                throw new Exception('The log directory exists, but is not writable');
            }

            $level = ($config->isDebugMode() ? PEAR_LOG_DEBUG : PEAR_LOG_WARNING);
            $test = ($config->isTestMode() ? '.test' : '');
            $config->setLog(Log::singleton('file', $logDir . '/' . date('Y-m-d') . "$test.log", '', null, $level));
        }
        else {
            $config->setLog(Log::singleton('null'));
        }
    }
}

?>
