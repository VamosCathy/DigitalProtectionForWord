<?php
//包含依赖库
require 'pdf-watermarker/vendor/binarystash/fpdf/fpdf.php';
require 'pdf-watermarker/vendor/setasign/fpdi/fpdi.php';
require 'pdf-watermarker/pdfwatermarker/pdfwatermarker.php';
require 'pdf-watermarker/pdfwatermarker/pdfwatermark.php';
require 'pdf-watermarker/fpdf_alpha.php';

//convert word to pdf
function convertDocToPdf($originFilePath,$outputDirPath){
	$command = 'unoconv --format %s --output %s %s';
	$command = sprintf($command,'pdf',$outputDirPath,$originFilePath);
	system($command,$output);

	return $output;
}

//get pdf total pages
function getPageTotal($pdfpath){
	if (!$fp = @fopen($pdfpath,"r")) {
		$error = "fail to open {$pdfpath}";
		return false;
	}
	else {
		$max = 0;
		while (!feof($fp)) {
			$line = fgets($fp,255);
			if (preg_match('/\/Count [0-9]+/', $line, $matches)) {
				preg_match('/[0-9]+/',$matches[0], $matches2);
				if ($max<$matches2[0]) $max=$matches2[0];
			}
		}
		fclose($fp);
		return $max;
	}
}

//single pdf page to png
function pdfpage2png($pdfpath,$pngpath,$page=0){
	if (!is_dir($pngpath)) {
		mkdir($pngpath,0700,true);
	}
	if (!extension_loaded('imagick')) {
		echo 'cannot find imagick';
		return false;
	}
	if (!file_exists($pdfpath)) {
		echo "cannot find pdf";
		return false;
	}
	$im = new Imagick();
	$im->setResolution(200,200);
	$im->setCompressionQuality(80);
	$im->readImage($pdfpath . "[" . $page . "]");
	$im->scaleImage(648,1024,true);
	$filename = $pngpath . "/" . explodeFilename($pdfpath,".pdf") . "-" . $page . ".png";
	if ($im->writeImage($filename) == true) {
		$Return = $filename;
	}
	return $Return;
}

//explode file name with path and format
function explodeFilename($filepath,$format){
	$tmpArray = explode('/',$filepath);
	$tmpNum = count($tmpArray);
	$tmpName = $tmpArray[$tmpNum - 1];
	$nameArray = explode($format,$tmpName);
	$pureName = $nameArray[0];
	return $pureName;
}

//pdf file to png
function pdf2png($pdfpath,$pngpath){
	$pageNum = getPageTotal($pdfpath);
	$pageNum = (int)$pageNum;
	for ($i=0; $i < $pageNum; $i++) { 
		pdfpage2png($pdfpath,$pngpath,$i);
	}
}

//merge png to pdf
function png2pdf($pngpath,$pageNum,$outputDirPath,$pureName){
	$newPdf = new FPDF();
	for ($i=0; $i < $pageNum; $i++) { 
		$image = $pngpath . $pureName . '-' . $i . '.png';
		$newPdf->AddPage();
		$newPdf->ImagePngWithAlpha($image,20,40,600,0);
		$newPdf->Image($image,20,40,600,0,'PNG');
	}
	$newPdfPath = $outputDirPath . '/new-' . $purename . '.pdf';
	$newPdf->Output($newPdfPath);
	return $newPdfPath;
}

//product watermark
function productWatermark($text){
	$fontSize = 40;
	$bbox = imagettfbbox(40,0,'fonts/simhei.ttf',$text);
	$width = $bbox[2] - $bbox[0];
	$height = $bbox[1] - $bbox[7];

	$im = @imagecreatetruecolor($width, $height)
	or die('cannot imitialize new GD image stream');

	//保存完整alpha通道信息
	imagealphablending($im, false);
	imagesavealpha($im,true);

	//设置底色透明
	$bg = imagecolorallocatealpha($im, 255, 255, 255, 127);
	imagefilledrectangle($im,0,0,$width - 1,$height - 1,$bg);

	//文字颜色设置
	$text_color = imagecolorallocatealpha($im,88,88,88,100);

	//写入水印文字
	imagettftext($im,$fontSize,0,$bbox[0],$height - $bbox[1],$text_color,'fonts/simhei.ttf',$text);
	$im = imagerotate($im,45,$bg,0);

	//保存水印
	$path = sys_get_temp_dir() . '/' . uniqid() . '.png';
	imagepng($im,$path);
	imagedestroy($im);
	return $path;
}

$wordPath = $argv[1];
$text = $argv[2];
$isSingle = $argv[3];

//acquire path of word
$wordName = explodeFilename($wordPath,'.doc');
$originPathArray = explode($wordName,$wordPath);
$originPath = $originPathArray[0];

convertDocToPdf($wordPath,$originPath);
$pdfPath = $originPath . '/' . $wordName . '.pdf';
$pngpath = $originPath . 'images/';
pdf2png($pdfPath,$pngpath);

$pageNum = getPageTotal($pdfPath);
$pageNum = (int)$pageNum;
$pdfPath = png2pdf($pngpath,$pageNum,$originPath,$wordName);
$watermarkPath = productWatermark($text);

$watermark = new PDFWatermark($watermarkPath);
$watermark->setPosition($isSingle);
$finalPdf = new PDFWatermarker($pdfPath,'output_' . $pdfFile,$watermark);
$finalPdf->savePdf();
?>