<?php
require_once dirname(__FILE__) . '/MyTestCase.php';

class DummyImage extends AbstractImage
{
	// Pseudo-Image
	private $height;
	private $width;
	function __construct($width, $height)
	{
		$this->height = $height;
		$this->width = $width;
	}
	public function getHeight() { return $this->height; }
	public function getWidth() { return $this->width; }
	
	// Make this protected function public
	public function prepareDimensionsOffset($width, $height, $align, $valign) {
		return parent::prepareDimensionsOffset($width, $height, $align, $valign);
	}
	
	// Stub methods to avoid compile errors
	protected function initImageLoadFile($filename) {}
	protected function initNewImage($width, $height) {}
	protected function initCopyImage(Image $image) {}
	public function toFile($filename = '') {}
	public function rotate($degrees) {}
	public function scale($newWidth, $newHeight) {}
	public function addImage(Image $img, $top, $left) {}
	public function show() {}
	public function setNewSize($width, $height) {}
}

class ImageTest extends MyTestCase
{
	var $img;
	
	protected function setUp()
	{
		$this->img = new DummyImage(50, 10);
	}
	
	public function testPrepareDimensionsOffsetScaleSmaller()
	{
		$dim = $this->img->prepareDimensionsOffset(100, 10, DummyImage::ALIGN_CENTER, DummyImage::VALIGN_MIDDLE);
		$this->assertEquals(25, $dim->left, "left does not match (center)");
		$this->assertEquals(0, $dim->top, "top does not match (middle)");
		
		$dim = $this->img->prepareDimensionsOffset(100, 10, DummyImage::ALIGN_RIGHT, DummyImage::VALIGN_BOTTOM);
		$this->assertEquals(50, $dim->left, "left does not match (right)");
		$this->assertEquals(0, $dim->top, "top does not match (bottom)");

		$dim = $this->img->prepareDimensionsOffset(50, 50, DummyImage::ALIGN_CENTER, DummyImage::VALIGN_MIDDLE);
		$this->assertEquals(0, $dim->left, "left does not match (center)");
		$this->assertEquals(20, $dim->top, "top does not match (middle)");
		
		$dim = $this->img->prepareDimensionsOffset(50, 50, DummyImage::ALIGN_RIGHT, DummyImage::VALIGN_BOTTOM);
		$this->assertEquals(0, $dim->left, "left does not match (right)");
		$this->assertEquals(40, $dim->top, "top does not match (bottom)");
		
		$dim = $this->img->prepareDimensionsOffset(50, 50, DummyImage::ALIGN_LEFT, DummyImage::VALIGN_TOP);
		$this->assertEquals(0, $dim->left, "left does not match (left)");
		$this->assertEquals(0, $dim->top, "top does not match (top)");
	}
	
	public function testPrepareDimensionsOffsetDoNotScale()
	{
		$dim = $this->img->prepareDimensionsOffset(100, 20, DummyImage::ALIGN_CENTER, DummyImage::VALIGN_MIDDLE);
		$this->assertEquals(50, $dim->width, "Size not correct (width)");
		$this->assertEquals(10, $dim->height, "Size not correct (height)");
		$this->assertEquals(25, $dim->left, "left does not match (center)");
		$this->assertEquals(5, $dim->top, "top does not match (middle)");
		
		$dim = $this->img->prepareDimensionsOffset(100, 20, DummyImage::ALIGN_RIGHT, DummyImage::VALIGN_BOTTOM);
		$this->assertEquals(50, $dim->width, "Size not correct (width)");
		$this->assertEquals(10, $dim->height, "Size not correct (height)");
		$this->assertEquals(50, $dim->left, "left does not match (right)");
		$this->assertEquals(10, $dim->top, "top does not match (bottom)");
		
		$dim = $this->img->prepareDimensionsOffset(100, 20, DummyImage::ALIGN_LEFT, DummyImage::VALIGN_TOP);
		$this->assertEquals(50, $dim->width, "Size not correct (width)");
		$this->assertEquals(10, $dim->height, "Size not correct (height)");
		$this->assertEquals(0, $dim->left, "left does not match (left)");
		$this->assertEquals(0, $dim->top, "top does not match (top)");
	}
	

    /**
     * @expectedException InvalidArgumentException
     */
	public function testExceptionInvalidAlign()
	{
		$dim = $this->img->prepareDimensionsOffset(50, 5, "bla", DummyImage::VALIGN_BOTTOM);
		var_dump($dim);
	}
	
    /**
     * @expectedException InvalidArgumentException
     */
	public function testExceptionInvalidValign()
	{
		$this->img->prepareDimensionsOffset(5, 5, DummyImage::ALIGN_CENTER, "bla");
	}
	
    /**
     * @expectedException InvalidArgumentException
     */
	public function testExceptionInvalidHeight()
	{
		$this->img->prepareDimensionsOffset(5, -5, DummyImage::ALIGN_LEFT, DummyImage::VALIGN_TOP);
	}
	
    /**
     * @expectedException InvalidArgumentException
     */
	public function testExceptionInvalidWidth()
	{
		$this->img->prepareDimensionsOffset(-5, 5, DummyImage::ALIGN_LEFT, DummyImage::VALIGN_TOP);
	}
	
}