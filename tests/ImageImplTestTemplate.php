<?php
require_once dirname(__FILE__) . '/MyTestCase.php';

define ('TMP_FILENAME', 'blub.png');

/**
 * Test if the contract of Image is followed in all implementations
 */
abstract class ImageImplTestTemplate extends MyTestCase
{
	var $img;
	
	private function modify(Image $image)
	{
		$letter = ImageFactory::getImage(TEST_FOREGROUND);
		$image->addImage($letter, 25, 25);
	}
	
	function setUp()
	{
		$this->img = ImageFactory::getImage(TEST_BACKGROUND);
	}
	
	function tearDown()
	{
		@unlink(TMP_FILENAME);
	}
	
	/**
     * @expectedException FileNotFoundException
     */
	function testConstructUnexistingFile()
	{
		ImageFactory::getImage("/bla/bla/bla");	
	}
	
	/**
     * @expectedException InvalidArgumentException
     */
	function testConstructInvalidWidth()
	{
		ImageFactory::getImage(-5, 5);
	}
	
	/**
     * @expectedException InvalidArgumentException
     */
	function testConstructInvalidHeight()
	{
		ImageFactory::getImage(5, -5);
	}
	
	function testConstructSize()
	{
		$img = ImageFactory::getImage(40, 20);
		$this->assertImageSize("40x20", $img);
	}
	
	
	function testGetHeightWidth()
	{
		$this->assertImageSize("100x100", $this->img);
	}
	
	function testToFile()
	{
		$file2 = $this->img->toFile();
		$this->assertImageSame(TEST_BACKGROUND, $file2);
	}
	
	function testAddImage()
	{
		$this->modify($this->img);
		$this->assertImageNotSame(TEST_BACKGROUND, $this->img);
	}
	
	/**
	 * @depends testAddImage
	 */
	function testCanClone()
	{
		$img2 = clone $this->img;
		
		$this->modify($img2);
		$this->assertImageNotSame($this->img, $img2);
	}
	
	/**
	 * @depends testAddImage
	 */
	function testCopyConstructor()
	{
		$img2 = ImageFactory::getImage($this->img);

		$this->modify($img2);
		$this->assertImageNotSame($this->img, $img2);
	}
	
	/**
	 * @depends testGetHeightWidth
	 */
	function testScale()
	{
		$this->img->scale(10, 200);
		$this->assertImageSize("10x200", $this->img, "Image size has not changed (was: 100x100)");
	}
	
	function testRotate45()
	{
		$this->img->rotate(45);
		$this->assertTrue($this->img->getWidth() > 130, "Image size too small");
		$this->assertTrue($this->img->getHeight() > 130, "Image size too small");
	}
	
	function testOutput()
	{
		ob_start();
		
		$this->img->show();
		
		$imgContent = ob_get_clean();
		file_put_contents(TMP_FILENAME, $imgContent);
		
		$this->assertImageSame($this->img, TMP_FILENAME);
	}
	
	function testSetNewSize()
	{
		$this->img->setNewSize(10, 20);
		$this->assertImageSize("10x20", $this->img, "Image size has not changed (was: 100x100)");
		$this->img->setNewSize(0, 10);
		$this->assertImageSize("10x10", $this->img, "Image size has not changed (was: 10x20)");
	}
}
