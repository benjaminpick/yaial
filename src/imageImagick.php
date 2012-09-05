<?php
/*
 * YAIAL - Yet Another Image Abstraction Layer
 * Copyright (C) 2011 Yellow Tree
 * @author Benjamin Pick
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * Contact: Benjamin Pick, b.pick@yellowtree.de
 *
 */
 
class ImagickImage extends AbstractImage
{	
	/**
	 * Internal Image Object
	 * @var Imagick
	 */
	private $imagick;
	
	// -------------- Constructor template functions ----------------
	protected function initImageLoadFile($filename) {
		$this->imagick = new Imagick($filename);
	}
	
	protected function initNewImage($width, $height) {
		$this->imagick = new Imagick();
		// By default: Transperent background
		$this->imagick->newimage($width, $height, 'none', 'png');
	}
	
	protected function initCopyImage(Image $image) {
		if ($image instanceof ImagickImage)
		{
			$this->imagick = $image->imagick->clone();
		}
		else
		{
			$this->imagick = new Imagick($image->toFile());
		}
	}
	
	
	public function __clone()
	{
		$this->imagick = $this->imagick->clone();
	}
	
	public function getHeight()
	{
		return $this->imagick->getImageHeight();
	}

	public function getWidth()
	{
		return $this->imagick->getImageWidth();
	}
	
	public function toFile($filename = '') {
		if (empty($filename))
			$filename = getTemporaryFilename('.' . $this->imagick->getimageformat());
		$this->imagick->writeimage($filename);
		
		return $filename;
	}
	
	public function rotate($degrees) {
		$this->imagick->rotateimage('none', $degrees);
	}
	
	
	public function scale($newWidth, $newHeight) 
	{
		if (PREFER_PERFORMANCE_OVER_QUALITY)
			$this->imagick->scaleimage($newWidth, $newHeight);
		else
			$this->imagick->resizeimage($newWidth, $newHeight, imagick::FILTER_LANCZOS, 1);
	}
	
	public function addImage(Image $img, $top, $left) 
	{
		if (!($img instanceof ImagickImage))
			$img = new ImagickImage($img);

		$this->imagick->compositeimage($img->imagick, imagick::COMPOSITE_DEFAULT, $left, $top); // Correct composite mode?
	}
	
	public function show()
	{
		if (!headers_sent())
			header('Content-type: image/' . strtolower($this->imagick->getimageformat()));
		echo $this->imagick;
	}
	
	public static function checkImplementationAvailable()
	{
		return class_exists("Imagick");
	}
	
	public function setNewSize($width, $height)
	{
		$this->imagick->cropimage($width, $height, 0, 0);
		$this->imagick->setimagepage(0,0,0,0);
	}
	
}

ImageFactory::addAvailableImplementation("ImagickImage");
