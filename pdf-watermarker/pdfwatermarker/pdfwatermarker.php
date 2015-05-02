<?php
/*
The MIT License (MIT)

Copyright (c) 2012 BinaryStash

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

class PDFWatermarker {
	
	private $_originalPdf;
	private $_newPdf;
	private $_tempPdf;
	private $_watermark;
	private $_specificPages; //可指定在某些水印上不加水印
	
	/**
	 * Creates an instance of the watermarker
	 *
	 * @param string $originalPDF - inputted PDF path
	 * @param string $newPDF - outputted PDF path
	 * @param mixed $watermark Watermark - watermark object
	 *
	 * @return void
	 */
	public function __construct($originalPdf,$newPdf,$watermark) {
		
		$this->_originalPdf = $originalPdf;
		$this->_newPdf = $newPdf;
		$this->_tempPdf = new FPDI();
		$this->_watermark = $watermark;
		$this->_specificPages = array();
		
		try {
			$this->_validateAssets();
		} catch (Exception $e) {
			echo "Caught exception:", $e->getMessage(),"\n";
			exit;
		}
		
	}

	
	/**
	 * Ensures that the watermark and the PDF file are valid
	 *
	 * @return void
	 */
	private function _validateAssets() {
		
		if ( !file_exists( $this->_originalPdf ) ) {
			throw new Exception("Inputted PDF file doesn't exist");
		}
		else if ( !file_exists( $this->_watermark->getFilePath() ) ) {
			throw new Exception("Watermark doesn't exist.");
		}
		
	}
	
	/**
	 * Loop through the pages while applying the watermark
	 *
	 * @return void
	 */
	private function _updatePDF() {
		
		$totalPages = $this->_getTotalPages();
		
		for($ctr = 1; $ctr <= $totalPages; $ctr++){
			
			$this->_importPage($ctr);
			
			if ( !in_array($ctr, $this->_specificPages ) || empty( $this->_specificPages ) ) {
				$this->_watermarkPage($ctr);
			}
			else {
				$this->_watermarkPage($ctr, false);
			}
			
		}
		
	}
	
	/*
	 * Get total number of pages
	 *
	 * @return int 
	 */
	private function _getTotalPages() {
		return $this->_tempPdf->setSourceFile($this->_originalPdf);
	}
	
	/**
	 * Import page
	 *
	 * @param int $page_number - page number
	 *
	 * @return void
	 */
	private function _importPage($page_number) {
		
		$templateId = $this->_tempPdf->importPage($page_number);
		$templateDimension = $this->_tempPdf->getTemplateSize($templateId); //返回该页面的宽和高：array('w' => ..., 'h' => ...)
		
		//居然还可以定向，牛逼！
		if ( $templateDimension['w'] > $templateDimension['h'] ) {
			$orientation = "L";
		}
		else {
			$orientation = "P";
		}

		$this->_tempPdf->DefOrientation = $orientation;

		$this->_tempPdf->addPage($orientation,array($templateDimension['w'],$templateDimension['h']));
		
	}
	
	/**
	 * Apply the watermark to a specific page
	 *
	 * @param int $page_number - page number
	 * @param bool $watermark_visible - (optional) Make the watermark visible. True by default.
	 *
	 * @return void
	 */
	private function _watermarkPage($page_number, $watermark_visible = true) { //在指定页面加水印
		
		$templateId = $this->_tempPdf->importPage($page_number);
		//页面尺寸
		$templateDimension = $this->_tempPdf->getTemplateSize($templateId);
		
		//水印尺寸
		$wWidth = ($this->_watermark->getWidth() / 96) * 25.4; //in mm
		$wHeight = ($this->_watermark->getHeight() / 96) * 25.4; //in mm

		if ( $watermark_visible ) {//水印可见
			$this->_tempPdf->useTemplate($templateId);
			if ($this->_watermark->getPosition()) { //只有单个水印
				$x = ( $templateDimension['w'] - $wWidth ) / 2 ;
				$y = ( $templateDimension['h'] - $wHeight ) / 2 ;
				$this->_tempPdf->Image($this->_watermark->getFilePath(),$x,$y,-96);
			}
			else{ //有多个水印
				//计算水印行数
				$watermarkRowNum = $templateDimension['h'] / $wHeight + 1;
				//计算水印列数
				$watermarkColNum = $templateDimension['w'] / $wWidth + 1;

				for ($countCol=1; $countCol <= $watermarkColNum ; $countCol++) { 
					for ($countRow=1; $countRow <= $watermarkRowNum ; $countRow++) { 
						$this->_tempPdf->Image($this->_watermark->getFilePath(),($countCol - 1) * $wWidth,($countRow - 1) * $wHeight,-96);
					}
				}
			}
		}
		else {
			$this->_tempPdf->useTemplate($templateId);
		}
		
	}
	
	/**
	 * Set page range
	 *
	 * @param int $startPage - the first page to be watermarked
	 * @param int $endPage - (optional) the last page to be watermarked
	 *
	 * @return void
	 */
	public function setPageRange($startPage=1, $endPage=null) {
		
		$end = $endPage !== null ? $endPage : $this->_getTotalPages();
		
		$this->_specificPages = array();
		
		for ($ctr = $startPage; $ctr <= $end; $ctr++ ) {
			$this->_specificPages[] = $ctr;
		}
		
	}

	
	/**
	 * Save the PDF to the specified location
	 *
	 * @return void
	 */
	public function savePdf() {
		$this->_updatePDF();
		$this->_tempPdf->Output($this->_newPdf);
	}
}
?>
