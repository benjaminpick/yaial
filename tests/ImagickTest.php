<?php
require_once dirname(__FILE__) . '/ImageImplTestTemplate.php';

class ImagickTest extends ImageImplTestTemplate
{
	public function setUp()
	{
		ImageFactory::init("ImagickImage", true);
		parent::setUp();
	}
}