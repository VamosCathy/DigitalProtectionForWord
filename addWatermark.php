<?php

//包含依赖库
require 'pdf-watermarker/vendor/binarystash/fpdf/fpdf.php';
require 'pdf-watermarker/vendor/setasign/fpdi/fpdi.php';
require 'pdf-watermarker/pdfwatermarker/pdfwatermarker.php';
require 'pdf-watermarker/pdfwatermarker/pdfwatermark.php';

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
$text = "Copyright@www.shangke.com"; //设置默认水印文字
$originFile = ''; //input pdf file
$outputFile = ''; //output pdf file

$i = 0;
$flag = 0; //输入参数是否正确地标志

while($i < $argc){
    if($argv[$i] == '--text'){
        $text = $argv[++$i]; //需要输入的文字水印
    }
    else if($argv[$i] == '--input'){
        $originFile = $argv[++$i]; //input pdf file
        $flag++;
    }
    else if($argv[$i] == '--output'){
        $outputFile = $argv[++$i]; //output pdf file
        $flag++;
    }
    else
        ++$i;
}

if($flag != 2){
    exit("用法错误。命令格式：php addWatermark.php --text 水印文字 --input 源pdf文件路径 --output 输出pdf文件路径");
}

$originFileDir = pathinfo($originFile,PATHINFO_DIRNAME);
$outputFileDir = pathinfo($outputFile,PATHINFO_DIRNAME);

$watermarkPath = productWatermark($text);

//add watermark
$watermark = new PDFWatermark($watermarkPath);
$watermark->setPosition($isSingle);
// $finalPdfPath = $originFileDir . "/" . uniqid() . ".pdf";
$finalPdf = new PDFWatermarker($originFile, $outputFile,$watermark);
$finalPdf->savePdf();
echo "finalPdfPath = " . $outputFile . PHP_EOL;
?>
