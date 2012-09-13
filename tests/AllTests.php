<?php
require_once dirname(__FILE__) . '/GDTest.php';
require_once dirname(__FILE__) . '/GmagickTest.php';
require_once dirname(__FILE__) . '/ImagickTest.php';
require_once dirname(__FILE__) . '/ImageInteractionTest.php';
require_once dirname(__FILE__) . '/GDTest.php';
require_once dirname(__FILE__) . '/GDTest.php';




class AllTests
{
    static $tests = array(
      'GDTest',
      'GmagickTest',
      'ImagickTest',
      'ImageInteractionTest',
      'ImagePlaceholderTest',
      'ImageTest',
    );

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');

        foreach (self::$tests as $test)
        {
            require_once dirname(__FILE__) . "/$test.php";
            $suite->addTestSuite($test);
        }

        return $suite;
    }
}