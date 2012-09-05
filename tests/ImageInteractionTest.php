<?php


class ImageInteractionTest extends MyTestCase
{
	public function testAllImplementationCombinations()
	{
		$implementations = ImageFactory::getAvailableImplementations();
		foreach ($implementations as $i)
		{
			foreach ($implementations as $j)
			{
				if ($i != $j)
					$this->assertCombinationWorks($i, $j);
			}
		}
	}
	
	private function assertCombinationWorks($class1, $class2)
	{
		$im = new $class1(TEST_BACKGROUND);
		$gm = new $class2(TEST_FOREGROUND);
		
		$imOld = clone $im;
		$im->addImage($gm, 0, 0);
		$this->assertImageNotSame($imOld, $im, "$class1 and $class2 does not interoperate");
	}
}