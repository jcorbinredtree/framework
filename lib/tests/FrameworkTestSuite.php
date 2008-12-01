<?php

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