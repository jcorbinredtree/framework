<?php

/**
 * Mailer definition
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
 * @category     Mailer
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2009 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      3.0
 * @link         http://framework.redtreesystems.com
 */

require_once 'lib/site/SiteModule.php';

class Mailer extends SiteModule
{
    protected $path;

    public function initialize()
    {
        $this->path = $this->moduleDir.'/phpmailer';
        require_once "$this->path/class.phpmailer.php";
    }

    public function getMailer()
    {
        if ($this->site->isTestMode()) {
            $mail = new PhonyMailer();
        } else {
            $mail = new PHPMailer();
        }

        $mail->PluginDir = "$this->path/";

        $opt = $this->site->config->getMailerOptions();
        foreach ($opt as $key => $val) {
            $mail->$key = $opt[$key];
        }

        return $mail;
    }
}

?>
