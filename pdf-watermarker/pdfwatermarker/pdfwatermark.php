<?php
/*
The MIT License (MIT)

Copyright (c) 2012 BinaryStash

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

class PDFWatermark {

	private $_file;
	private $_height;
	private $_width;
	private $_isSingle;
	
	/**
	 * Creates an instance of the watermark
	 *
	 * @param string $file - path to the image file
	 *
	 * @return void
	 */
	function __construct($file) {

		$this->_file = $file;
		$this->_getImageSize( $this->_file );
		
		$this->_isSingle = false;
	}
	
	
	/**
	 * Assess the watermark's dimensions
	 *
	 * @return void
	 */
	private function _getImageSize($image) {
		$is = getimagesize($image);
		$this->_width = $is[0];
		$this->_height = $is[1];
	}
	
	/**
	 * Determine the watermark isSingle
	 *
	 * @param bool $isSingle -  true,false
	 *
	 * @return void
	 */
	public function setPosition($isSingle) {
		$this->_isSingle = $isSingle;
	}
	
	/**
	 * Returns the watermark's isSingle
	 *
	 * @return string
	 */
	public function getPosition() {
		return $this->_isSingle;
	}
	
	/**
	 * Returns the watermark's file path
	 *
	 * @return string
	 */
	public function getFilePath() {
		return $this->_file;
	}
	
	/**
	 * Returns the watermark's height
	 *
	 * @return int
	 */
	public function getHeight() {
		return $this->_height;
	}
	
	/**
	 * Returns the watermark's width
	 *
	 * @return int
	 */
	public function getWidth() {
		return $this->_width;
	}
}