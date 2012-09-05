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


/* ---------------- CONFIG --------------- */

if (!defined('PREFER_PERFORMANCE_OVER_QUALITY'))
    define('PREFER_PERFORMANCE_OVER_QUALITY', false);

/* ---------------- UTIL CLASSES --------- */

if (!class_exists('IOException')) { class IOException extends RuntimeException {} }
if (!class_exists('FileNotFoundException')) { class FileNotFoundException extends IOException { } }
if (!class_exists('ClassDefNotFoundException')) { class ClassDefNotFoundException extends RuntimeException { } }

if (!function_exists('getTemporaryFilename')) {
    if (!defined('TEMP_FOLDER'))
        define('TEMP_FOLDER', ini_get('upload_tmp_dir'));

    /**
     * Create a filename that doesn't exist yet
     * @param string $suffix    The suffix of the file
     * @return string   A new temporary file
     */
    function getTemporaryFilename($suffix = '')
    {
        $name = tempnam(TEMP_FOLDER, 'tempimage');
        unlink($name);
        return $name . $suffix;
    }
}

if (!function_exists('logger'))
{
    // Default to no-op
    function logger() {}
}

/* Interface that all implementations must do */
interface Image
{
	/**
	 * Method to get the width of the image in pixels.
	 *
	 * @return  integer
	 */
	function getWidth();

	/**
	 * Method to get the height of the image in pixels.
	 *
	 * @return  integer
	 */
	function getHeight();
	
	/**
	 * Write image data into a file
	 * The image type is detected by using the filename (default:png)
	 * @param string $filename Filename where to write it to. If none giving, it is saved into a temporary file.
	 */
	function toFile($filename = '');
	
	/**
	 * Rotate image clockwise
	 * @param float $degrees	Number of degrees to rotate (e.g. 180 to flip it up-down).
	 */
	function rotate($degrees);
	
	/**
	 * Scale image to new size
	 * @param int $newWidth	New width of image
	 * @param int $newHeight New height of image
	 */
	function scale($newWidth, $newHeight);
	
	/**
	 * Add image to self
	 * @param Image $img	Image to add
	 * @param int $top		y-coordinate where to add it
	 * @param int $left		x-coordinate where to add it
	 */
	function addImage(Image $img, $top, $left);
	
	/**
	 * Fits image into a given border box
	 * 
	 * @param Image $img		Image to add to this image
	 * @param SimpleXMLElement $config	Options configuring the details.
	 * 			top:
	 * 			left:
	 * 			height:
	 * 			width:
	 * 			align:
	 * 			valign:
	 */
	function fitIntoImage(Image $img, SimpleXMLElement $config, $scale = true);
	
	/**
	 * Check if needed libraries are available.
	 * @return bool	True if ready to work.
	 */
	static function checkImplementationAvailable();
	
	/**
	 * Output image to browser.
	 */
	function show();
	
	/**
	 * Crop image to new size
	 * 
	 * @param int $width	New width
	 * @param int $height	New height
	 */
	function setNewSize($width, $height);
}



abstract class AbstractImage implements Image
{
	/**
	 * Versatile constructor:
	 * 
	 * new Image($image)
	 * Copy an existing image
	 * 
	 * new Image($filename)
	 * Load an image-file
	 * 
	 * new Image($width, $height)
	 * Creates a new, empty (transparent) image with size $widthx$height
	 * 
	 * @param Image/string/int $arg1
	 * @param int $arg2
	 * @throws InvalidArgumentException	If no matching constructor found, $width or $height negative
	 */
	public function __construct($arg1, $arg2 = null)
	{
	/*	if (gettype($arg1) == gettype($this))
			$this->initCopyConstruct($arg1);
		else */ if ($arg1 instanceof Image)
			$this->initCopyImage($arg1);
		elseif (gettype($arg1) == 'string')
		{
			if (!file_exists($arg1))
				throw new FileNotFoundException("Couldn't load image $arg1: file does not exist");
			$this->initImageLoadFile($arg1);
			
		}
		elseif ($arg2 != null)
		{
			$arg1 = $this->toInt($arg1);
			$arg2 = $this->toInt($arg2);
			
			if ($arg1 < 0 || $arg2 < 0)
				throw new InvalidArgumentException("Invalid height or width of new image");

			$this->initNewImage($arg1, $arg2);
		}
		else
		{
			logger($arg1, 'arg1');
			logger($arg2, 'arg2');
			throw new InvalidArgumentException("Invalid use of image constructor.");
		}
	}
	
	protected abstract function initCopyImage(Image $image);
	protected abstract function initImageLoadFile($filename);
	protected abstract function initNewImage($width, $height);
	
	const ALIGN_LEFT = "left";
	const ALIGN_CENTER = "center";
	const ALIGN_RIGHT = "right";
	
	const VALIGN_TOP = "top";
	const VALIGN_MIDDLE = "middle";
	const VALIGN_BOTTOM = "bottom";
	
	/**
	 * Calculate dimensions and offset
	 * 
	 * @param integer $width		Width of bounding box
	 * @param integer $height		Height of bounding box
	 * @param string $align			How to fit current image into bounding box: align left/center/right (default: center)
	 * @param string $valign		How to fit current image into bounding box: align top/middle/bottom (default: middle)
	 * @return object				Contains the following attributes:
	 * 								<li>boolean scale: Whether the image needs to be scaled
	 * 								<li>integer width: New width of the image
	 * 								<li>integer height: New height of the image
	 * 								<li>integer left: New position of the image (horizontal position relative to bounding box)
	 * 								<li>integer top: New position of the image (vertical position relative to bounding box)
	 * @throws InvalidArgumentException	If invalid align method
	 */
	protected function prepareDimensionsOffset($width, $height, $align = self::ALIGN_CENTER, $valign = self::VALIGN_MIDDLE)
	{
		$width = $this->sanitizePixelValue($width, "Invalid parameter: width");
		$height = $this->sanitizePixelValue($height, "Invalid parameter: height");
		
		if ($width >= $this->getWidth() && $height >= $this->getHeight())
		{
			$dimensions = new stdClass();
			$dimensions->width = $this->getWidth();
			$dimensions->height = $this->getHeight();
			$dimensions->scale = false;
		}
		else
		{
			$dimensions = $this->prepareDimensions($width, $height, self::SCALE_INSIDE);
			$dimensions->scale = true;
		}
		
		$this->prepareOffset($dimensions, $width, $height, $align, $valign);
		
		return $dimensions;
	}
	
	protected function prepareOffset($dimensions, $width, $height, $align, $valign)
	{
		$dimensions->left = 0;
		$dimensions->top = 0;
		
		if ($dimensions->width != $width)
		{
			$align = strtolower($align);
			switch ($align)
			{
				case self::ALIGN_RIGHT:
					$dimensions->left = $width - $dimensions->width;
					break;

				case self::ALIGN_CENTER:
					$dimensions->left = ($width - $dimensions->width) / 2;
					break;	
				
				case self::ALIGN_LEFT:
					break;
					
				default:
					throw new InvalidArgumentException("Invalid align method $align. Valid are: " . self::ALIGN_LEFT . ', ' . self::ALIGN_CENTER . ',' . self::ALIGN_RIGHT);
			}
		}
		
		if ($dimensions->height != $height)
		{
			switch ($valign)
			{
				case self::VALIGN_BOTTOM:
					$dimensions->top = $height - $dimensions->height;
					break;

				case self::VALIGN_MIDDLE:
					$dimensions->top = ($height - $dimensions->height) / 2;
					break;
				
				case self::VALIGN_TOP:
					break;
					
				default:
					throw new InvalidArgumentException("Invalid valign method $valign. Valid are: " . self::VALIGN_TOP . ', ' . self::VALIGN_MIDDLE . ',' . self::VALIGN_BOTTOM);
			}
		}
		
		return $dimensions;
	}
	
	/**
	 * The following code has been copied from Joomla! Platform:
	 * @see https://raw.github.com/joomla/joomla-platform/staging/libraries/joomla/image/image.php
	 * @version 11.4
	 * @license     GNU General Public License version 2 or later
	 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
	 */
	
	/**
	 * @const  integer
	 * @since  11.3
	 */
	const SCALE_FILL = 1;

	/**
	 * @const  integer
	 * @since  11.3
	 */
	const SCALE_INSIDE = 2;

	/**
	 * @const  integer
	 * @since  11.3
	 */
	const SCALE_OUTSIDE = 3;

	/**
	 * Method to get the new dimensions for a resized image.
	 *
	 * @param   integer  $width        The width of the resized image in pixels.
	 * @param   integer  $height       The height of the resized image in pixels.
	 * @param   integer  $scaleMethod  The method to use for scaling
	 *
	 * @return  object
	 *
	 * @since   11.3
	 * @throws  InvalidArgumentException
	 *
	 * @codeCoverageIgnore
	 */
	protected function prepareDimensions($width, $height, $scaleMethod)
	{
		// Instantiate variables.
		$dimensions = new stdClass;

		switch ($scaleMethod)
		{
			case self::SCALE_FILL:
				$dimensions->width = intval(round($width));
				$dimensions->height = intval(round($height));
				break;

			case self::SCALE_INSIDE:
			case self::SCALE_OUTSIDE:
				$rx = $this->getWidth() / $width;
				$ry = $this->getHeight() / $height;

				if ($scaleMethod == self::SCALE_INSIDE)
				{
					$ratio = ($rx > $ry) ? $rx : $ry;
				}
				else
				{
					$ratio = ($rx < $ry) ? $rx : $ry;
				}

				$dimensions->width = intval(round($this->getWidth() / $ratio));
				$dimensions->height = intval(round($this->getHeight() / $ratio));
				break;

			default:
				throw new InvalidArgumentException('Invalid scale method.');
				break;
		}

		return $dimensions;
	}
	
	public function fitIntoImage(Image $img, SimpleXMLElement $config, $scale = true)
	{
		logger((string) $this);
		logger($config);
		$width = (float) $config->width * $this->getWidth();
		$height = (float) $config->height * $this->getHeight();
		$left = (float) $config->left * $this->getWidth();
		$top = (float) $config->top * $this->getHeight();
		logger("Want to fit image into new size ($width x $height) at ($left x $top)");
		
		$dimensions = $img->prepareDimensionsOffset($width, $height, (string) $config->align, (string) $config->valign);
		logger($dimensions, "Chosen dimensions");
		if ($scale && $dimensions->scale)
			$img->scale($dimensions->width, $dimensions->height);
		
		$this->addImage($img, $top + $dimensions->top, $left + $dimensions->left);
	}
	
	/**
	 * Convert float to int
	 * @param float $value input value
	 * @return rounded value
	 */
	protected function toInt($value)
	{
		return intval(round(floatval($value)));
	}
	
	protected function sanitizePixelValue($value, $message)
	{
		if ($value < 0)
			throw new InvalidArgumentException($message);
		return $this->toInt($value);
	}
	
	public static function checkImplementationAvailable()
	{
		// By default, assume that there are no dependencies
		return true;
	}
	
	public function __toString()
	{
		return "Image of size " . $this->getWidth() . "x" . $this->getHeight();
	}
}


/**
 * Create an image depending on the chosen implementation
 */
class ImageFactory
{
	private static $currentClassname;
	
	private static $availableImplementations = array();
	
	public static function init($defaultImpl, $forceThisImplementation = false)
	{
		if (in_array($defaultImpl, self::$availableImplementations))
			self::$currentClassname = $defaultImpl;
		elseif (empty(self::$availableImplementations))
			throw new ClassDefNotFoundException("No implementation of Image found.");
		elseif ($forceThisImplementation)
			throw new ClassDefNotFoundException("No implementation found that is called $defaultImpl.");
		else
			self::$currentClassname = self::$availableImplementations[0];
			
		logger("Used graphics implementation: " . self::$currentClassname);
		logger(self::$availableImplementations, "Available are");
	}
	
	public static function addAvailableImplementation($implemenation)
	{
		if (!class_exists($implemenation))
			throw new ClassDefNotFoundException("ImageFactory: Didn't find implementation called ${implemenation}Image.");
			
		$available = call_user_func(array($implemenation, 'checkImplementationAvailable'));
		if ($available)
			self::$availableImplementations[] = $implemenation;
	}
	
	/**
	 * Create new image.
	 * 
	 * new Image($image)
	 * Copy an existing image
	 * 
	 * new Image($filename)
	 * Load an image-file
	 * 
	 * new Image($width, $height)
	 * Creates a new, empty (transparent) image with size $widthx$height
	 * 
	 * @param Image/string/int $arg1
	 * @param int $arg2
	 * @return Image
	 * @throws InvalidArgumentException	If no matching constructor found, $width or $height negative
	 */
	public static function getImage($arg1 = null, $arg2 = null)
	{
		return new self::$currentClassname($arg1, $arg2);
	}
	
	public static function getAvailableImplementations()
	{
		return self::$availableImplementations;
	}
}
