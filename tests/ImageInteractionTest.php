<?php
require_once dirname(__FILE__) . '/MyTestCase.php';


class ImageInteractionTest extends MyTestCase
{
    public static function provideTestImplementations()
    {
        $data = array();

        $implementations = ImageFactory::getAvailableImplementations();
        foreach ($implementations as $i)
        {
            foreach ($implementations as $j)
            {
                $data[] = array($i, $j);
            }
        }

        return $data;
    }

    /**
     * Check all combinations - test is not transitive, so AB might work, but BA might not
     * @dataProvider provideTestImplementations
     */
	public function testImageImplementationCombination($class1, $class2)
	{
        $im = new $class1(TEST_BACKGROUND);
        $gm = new $class2(TEST_FOREGROUND);

        $imOld = clone $im;
        $im->addImage($gm, 0, 0);
        $this->assertImageNotSame($imOld, $im, "$class1 and $class2 does not interoperate");
	}
}