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
 
class GDImage extends AbstractImage
{	
	/**
	 * Internal Image Object
	 * @var Imagick
	 */
	private $gd;
	
	private $ext;
	// -------------- Constructor template functions ----------------
	protected function initImageLoadFile($filename) {
		$path = pathinfo($filename);
		$ext = strtolower($path['extension']);
		switch ($ext)
		{
			case 'png':
				$this->gd = imagecreatefrompng($filename);
				$this->ext = 'png';		
				break;
			case 'jpg':
			case 'jpeg':
				$this->gd = imagecreatefromjpeg($filename);
				$this->ext = 'jpg';			
				break;
			default:
				throw new InvalidArgumentException("Invalid file type: " . $ext);
		}
		if (!$this->gd)
			throw new IOException("Could not load image $filename");
			
		$this->allocateBlack();
	}
	
	protected function allocateBlack()
	{
		$this->black = imagecolorallocate($this->gd, 0, 0, 0);
		imagecolortransparent($this->gd, $this->black);
	}
	
	protected function initNewImage($width, $height) {
		$this->gd = imagecreatetruecolor($width, $height);
		$this->allocateBlack();
		$this->ext = 'png';
	}
	
	protected function initCopyImage(Image $image) {
		if ($image instanceof GDImage)
		{
			$this->gd = $image->gd;
			$this->ext = $image->ext;
			$this->rotate(0);
		}
		else
		{
			$this->initImageLoadFile($image->toFile());
		}
	}
	
	
	public function __clone()
	{
		$this->rotate(0);
	}
	
	public function getHeight()
	{
		return imagesy($this->gd);
	}

	public function getWidth()
	{
		return imagesx($this->gd);
	}
	
	public function toFile($filename = '') {
		if (empty($filename))
			$filename = getTemporaryFilename('.' . $this->ext);

		switch ($this->ext)
		{
			case 'png':
				imagepng($this->gd, $filename);
				break;
			case 'jpg':
				imagejpeg($this->gd, $filename);
				break;
		}
		
		return $filename;
	}
	
	public function rotate($degrees) {
		if (!isset($this->black))
			$this->allocateBlack();
		$this->gd = imagerotate($this->gd, - $degrees, $this->black);
	}
	
	
	public function scale($newWidth, $newHeight) 
	{
		$old = $this->gd;
		$oldWidth = $this->getWidth();
		$oldHeight = $this->getHeight();
		
		$this->initNewImage($newWidth, $newHeight);
		imagecopyresampled($this->gd, $old, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);
	}
	
	public function addImage(Image $img, $top, $left) 
	{
		if (!($img instanceof GDImage))
			$img = new GDImage($img);

		imagecopy($this->gd, $img->gd, $left, $top, 0, 0, $img->getWidth(), $img->getHeight());
	}
	
	public function show()
	{
		switch ($this->ext)
		{
			case 'png':
				if (!headers_sent())
					header('Content-type: image/png');
				imagepng($this->gd);
				break;
			case 'jpg':
				if (!headers_sent())
					header('Content-type: image/jpeg');
				imagejpeg($this->gd);
				break;
		}
	}
	
	public static function checkImplementationAvailable()
	{
		return function_exists('imagecreatetruecolor');
	}
	
	public function setNewSize($width, $height)
	{
		if ($width == 0)
			$width = $this->getWidth();
		if ($height == 0)
			$height = $this->getHeight();
		
		
		$old_gd = $this->gd;
		$old_width = $this->getWidth();
		$old_height = $this->getHeight();
		$this->initNewImage($width, $height);
		imagecopy($this->gd, $old_gd, 0, 0, 0, 0, $old_width, $old_height);
	}

}

ImageFactory::addAvailableImplementation("GDImage");
