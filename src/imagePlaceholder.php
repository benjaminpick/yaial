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
 
/**
 * Image that only holds the correct image size
 * 
 * Needs: GD2
 */
class PlaceholderImage extends AbstractImage
{
	// Pseudo-Image
	private $height;
	private $width;

	public function getHeight() { return $this->height; }
	public function getWidth() { return $this->width; }
	
	protected function initImageLoadFile($filename) {
		$size = getimagesize($filename);
	}
	protected function initNewImage($width, $height) {
		$this->height = $height;
		$this->width = $width;	
	}
	protected function initCopyImage(Image $image) {
		$this->height = $image->getHeight();
		$this->width = $image->getWidth();
	}
	
	public static function checkImplementationAvailable()
	{
		return function_exists('getimagesize');
	}
	
	public function toFile($filename = '') {}
	public function rotate($degrees) {
		$oldHeight = $this->height;
		$oldWidth = $this->width;
		$degrees = deg2rad($degrees);

		$this->width =  $this->toInt(abs($oldHeight * sin($degrees)) + abs($oldWidth * cos($degrees)));
		$this->height = $this->toInt(abs($oldHeight * cos($degrees)) + abs($oldWidth * sin($degrees))); 
	}
	
	public function scale($newWidth, $newHeight) {
		$this->width = $newWidth;
		$this->height = $newHeight;
	}
	public function addImage(Image $img, $top, $left) {}
	public function show() {}
	public function setNewSize($width, $height) {
		if ($width != 0)
			$this->width = $width;
		if ($height != 0)
			$this->height = $height;
	}
}
