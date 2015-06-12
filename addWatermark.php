<?php

//包含依赖库
require 'pdf-watermarker/vendor/binarystash/fpdf/fpdf.php';
require 'pdf-watermarker/vendor/setasign/fpdi/fpdi.php';
require 'pdf-watermarker/pdfwatermarker/pdfwatermarker.php';
require 'pdf-watermarker/pdfwatermarker/pdfwatermark.php';

//product watermark
function productWatermark($text){
	$function4_starttime = microtime(true);
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
	$function4_endtime = microtime(true);
	$function4_runtime = $function4_endtime - $function4_starttime;
	// echo "productWatermark run time =" . $function4_runtime . "<br />";
	return $path;
}

$isSingle = true;
$text = $argv[1]; //需要插入的文字水印
$originFile = $argv[2]; //input pdf file
$originFileDir = pathinfo($originFile,PATHINFO_DIRNAME);

$watermarkPath = productWatermark($text);

//add watermark
$watermark = new PDFWatermark($watermarkPath);
$watermark->setPosition($isSingle);
$finalPdfPath = $originFileDir . "/" . uniqid() . ".pdf";
$finalPdf = new PDFWatermarker($originFile, $finalPdfPath,$watermark);
$finalPdf->savePdf();
echo "finalPdfPath = " . $finalPdfPath . PHP_EOL;
