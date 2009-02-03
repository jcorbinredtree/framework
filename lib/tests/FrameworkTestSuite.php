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

if (!class_exists('__tctemp')) {
    class __tctemp extends FrameworkTestCase {
        public function testNothing() {
            $this->assertTrue(true, 'nothing to test');
        }
    }
}

class FrameworkTestSuite {
    public static function fromDir($name, $dir)
    {
        $classes = array();

        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if (!preg_match('/[.]php$/i', $file)) {
                continue;
            }

            if ($file == 'suite.php') {
                continue;
            }

            $className = preg_replace('/[.]php$/i', '', $file);
            array_push($classes, $className);

            $file = "$dir/$file";
            require_once $file;
        }
        closedir($dh);

        $test = new GroupTest($name);

        if (count($classes)) {
            foreach ($classes as $class) {
                $test->addTestCase(new $class());
            }
        }
        else {
            $test->addTestCase(new __tctemp());
        }

        $test->run(new DefaultReporter());
    }
}

?>
