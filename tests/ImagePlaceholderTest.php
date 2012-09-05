<?php
require_once dirname(__FILE__) . '/MyTestCase.php';

class ImagePlaceholderTest extends MyTestCase
{
	var $img;
	public function setUp()
	{
		ImageFactory::init("GmagickImage", true);
		parent::setUp();
		$this->img = new PlaceholderImage(100, 100);
	}
	
	public function testRotate()
	{
		$this->img->rotate(45);
		$this->assertImageSize("141x141", $this->img);
		
		
		$this->img = new PlaceholderImage(100, 100);
		$this->img->rotate(-45);
		$this->assertImageSize("141x141", $this->img);
	}

	public function testRotate2()
	{
		$this->img = new PlaceholderImage(200, 100);
		$this->img->rotate(90);
		$this->assertImageSize("100x200", $this->img);
	}
	
	public function testRotate3()
	{
		$this->img = new PlaceholderImage(200, 100);
		$this->img->rotate(-30);
		$this->assertTrue($this->img->getHeight() > 0, "Invalid size: " . $this->img);
		$this->assertTrue($this->img->getWidth() > 0, "Invalid size: " . $this->img);
	}
	
	public function testRotateExtensively()
	{
		for ($i = -360; $i < 360; $i+= 10)
		{
			$img = new PlaceholderImage(200, 100);
			$img2 = ImageFactory::getImage(200, 100);
		
			$img->rotate($i);
			$img2->rotate($i);
			
			$size = $img2->getWidth() . 'x' . $img2->getHeight();
			$this->assertImageSize($size, $img);
		}
	}
	
}
