<?php
require_once dirname(__FILE__) . '/ImageImplTestTemplate.php';

class GmagickTest extends ImageImplTestTemplate
{
	public function setUp()
	{
		ImageFactory::init("GmagickImage", true);
		parent::setUp();
	}
}