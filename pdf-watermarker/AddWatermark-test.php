<?php
//包含依赖库
// set_include_path(dirname(__FILE__) . '/');
require 'vendor/binarystash/fpdf/fpdf.php';
require 'vendor/setasign/fpdi/fpdi.php';
require 'pdfwatermarker/pdfwatermarker.php';
require 'pdfwatermarker/pdfwatermark.php';

define("TEXT", "-text"); //水印文字
define("TEXT_COLOR", "-textColor"); //水印字颜色
define("FONT", "-font"); //水印字体
define("ALPHA", "-alpha"); //水印透明度
define("FONTSIZE", "-fontSize"); //水印字体大小
define("ANGLE", "-angle"); //水印角度
define("ISSINGLE", "-isSingle"); //设置水印为单个还是铺满

//设置默认值
$text="你好啊COPYRIGHT@35kk8.com";
$textColor = array(0,255,255);
$font = 'simhei';
$alpha = 80;
$fontSize = 40;
$angle = 45;
$isSingle = false;
$textRaws = 1;
$pdfFile = "";

$args = count($argv);

function usage(){
	echo "Usage: php AddWatermark-test.php [OPTION] <pdf file>\n" + 
		"   -text <string>         String to add in watermark\n" + 
		"   -textColor <R G B>     RGB value of watermark\n" + 
		"   -font <string>         The font of watermark,options:simhei,kaiti\n" + 
		"   -fontSize <number>     The font size of watermark\n" + 
		"   -alpha <number>        The alpha value of watermark,it determines watermark's transparent degree\n" + 
		"   -angle <number>        The angle of watermark\n" + 
		"   -isSingle <bool>       Determine whether there is only one watermark in each page.\n"
		;
	exit;
}

for ($i=1; $i < $args; $i++) { 
	if ($argv[$i] == TEXT) {
		$i++;
		if ($i >= $args) {
			usage();
		}
		$text = $argv[$i];
	}
	elseif ($argv[$i] == TEXT_COLOR) {
		$i++;
		if ($i >= $args) {
			usage();
		}
		$textColor[0] = $argv[$i];
		$i++;
		if ($i >= $args) {
			usage();
		}
		$textColor[1] = $argv[$i];
		$i++;
		if ($i >= $args) {
			usage();
		}
		$textColor[2] = $argv[$i];
	}
	elseif ($argv[$i] == FONT) {
		$i++;
		if ($i >= $args) {
			usage();
		}
		$font = $argv[$i];
	}
	elseif ($argv[$i] == ALPHA) {
		$i++;
		if ($i >= $args) {
			usage();
		}
		$alpha = $argv[$i];
	}
	elseif ($argv[$i] == FONTSIZE) {
		$i++;
		if ($i >= $args) {
			usage();
		}
		$fontSize = $argv[$i];
	}
	elseif ($argv[$i] == ANGLE) {
		$i++;
		if ($i >= $args) {
			usage();
		}
		$angle = $argv[$i];
	}
	elseif ($argv[$i] == ISSINGLE) {
		$i++;
		if ($i >= $args) {
			usage();
		}
		$isSingle = $argv[$i];
	}
	else{
		$pdfFile = $argv[$i];
	}
}
// echo ini_get('display_errors');

if ($pdfFile == NULL) {
	usage();
}
else{
	$bbox = imagettfbbox($fontSize,0,'fonts/' . $font . '.ttf',$text);
	$width = $bbox[2] - $bbox[0];
	$height = $bbox[1] - $bbox[7];

	$im = @imagecreatetruecolor($width, $height)
	or die('Cannot Initialize new GD image stream');

//保存完整alpha通道信息
	imagealphablending($im,false);
	imagesavealpha($im,true);

//设置底色透明
	$bg = imagecolorallocatealpha($im,255,255,255,127);
	imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $bg);

//文字颜色设置
	$text_color = imagecolorallocatealpha($im, $textColor[0], $textColor[1], $textColor[2], $alpha);

//文字内容
	$text = mb_convert_encoding($text,'UTF-8',"ASCII,JIS,UTF-8,EUC-JP,SJIS,GB2312,GBK");

//写入水印文字
	imagettftext($im, $fontSize, 0, $bbox[0], $height - $bbox[1], $text_color, 'fonts/' . $font . '.ttf', $text);
	$im = imagerotate($im,$angle,$bg,0);

//保存水印
	$path =  sys_get_temp_dir() . '/' . uniqid() . '.png'; //产生临时保存路径
	imagepng($im,$path);
	imagedestroy($im);

//Specify path to image. The image must have a 96 DPI resolution.
	$watermark = new PDFWatermark($path);

//Set the position
	$watermark->setPosition($isSingle);

//Specify the path to the existing pdf, the path to the new pdf file, and the watermark object
	$watermarker = new PDFWatermarker($pdfFile, 'output_' . $pdfFile, $watermark); 

//Save the new PDF to its specified location
	$watermarker->savePdf();
} 
?>