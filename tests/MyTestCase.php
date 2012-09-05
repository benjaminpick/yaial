<?php
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Util/Filter.php';
require_once dirname(__FILE__) . '/constraints/image.php';

require_once dirname(__FILE__) . '/../src/image.php';
require_once dirname(__FILE__) . '/../src/imageGD.php';
require_once dirname(__FILE__) . '/../src/imageGmagick.php';
require_once dirname(__FILE__) . '/../src/imageImagick.php';
require_once dirname(__FILE__) . '/../src/imagePlaceholder.php';

//PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

define('TEST_BACKGROUND', dirname(__FILE__) . '/files/blank.jpg');
define('TEST_FOREGROUND', dirname(__FILE__) . '/files/a.png');

abstract class MyTestCase extends PHPUnit_Framework_TestCase
{
    /**
	* Asserts that the compared images are same
	*
	* Uses the compare binary of the imagemagick package to compare to images
	* and will fail if the difference between two images is higher then zero.
	*
	* @param string $image New image
	* @param string $expectedImage Image to compare with
	* @param string $message Message to append to the fail message
	* @access public
	* @return void
	*/
    public function assertImageSame( $expectedImage, $image, $message = '' )
    {
    	if ($image instanceof Image)
    		$image = $image->toFile();
    	if ($expectedImage instanceof Image)
    		$expectedImage = $expectedImage->toFile();
    	
        $constraint = new ezcTestConstraintSimilarImage( $expectedImage );

        self::assertThat( $image, $constraint, $message );
    }

    public function assertImageNotSame( $expectedImage, $image, $message = '' )
    {
        if ($image instanceof Image)
    		$image = $image->toFile();
    	if ($expectedImage instanceof Image)
    		$expectedImage = $expectedImage->toFile();
    	
        $constraint = new ezcTestConstraintSimilarImage( $expectedImage );

        self::assertThat( $image, $this->logicalNot($constraint), $message ); 	
    }
    
    /**
	* Asserts that the compared images are similar
	*
	* Uses the compare binary of the imagemagick package to compare to images
	* and will fail if the difference between two images is higher then the
	* defined value.
	*
	* See http://www.imagemagick.org/script/compare.php for details. The
	* difference is logarithmical scaled.
	*
	* @param string $image New image
	* @param string $expectedImage Image to compare with
	* @param string $message Message to append to the fail message
	* @param int $maxDifference Maximum difference between images
	* @access public
	* @return void
	*/
    public function assertImageSimilar( $expectedImage, $image, $message = '', $maxDifference = 0 )
    {
        $constraint = new ezcTestConstraintSimilarImage( $expectedImage, $maxDifference );

        self::assertThat( $image, $constraint, $message );
    }
    
    public function assertImageSize($expectedImageSize, Image $image, $message = '')
    {
    	list($width, $height) = explode('x', $expectedImageSize);
    	$size = $image->getWidth() . 'x' . $image->getHeight();
    	
    	self::assertThat($height, $this->equalTo($image->getHeight()), "Incorrect height (Size is $size)\n" . $message );
    	self::assertThat($width,  $this->equalTo($image->getWidth()), "Incorrect width (Size is $size)\n" . $message );
    }
	
}