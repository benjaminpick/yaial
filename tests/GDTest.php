<?php
require_once dirname(__FILE__) . '/ImageImplTestTemplate.php';

class GDTest extends ImageImplTestTemplate
{
	public function setUp()
	{
		ImageFactory::init("GDImage", true);
		parent::setUp();
	}
}