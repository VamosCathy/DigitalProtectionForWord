<?php
require 'Unoconv.php';

require 'pdf-watermarker/vendor/binarystash/fpdf/fpdf.php';
require 'pdf-watermarker/vendor/setasign/fpdi/fpdi.php';
require 'pdf-watermarker/pdfwatermarker/pdfwatermarker.php';
require 'pdf-watermarker/pdfwatermarker/pdfwatermark.php';

use Unoconv\Unoconv;

// Converting to PDF
$originFilePath = $argv[1];
$pureNameArray = explode('.doc',$originFilePath);
$purename = $pureNameArray[0];
$outputDirPath  = './';

Unoconv::convertToPdf($originFilePath, $outputDirPath);

//figure out the total page of pdf
function getPageTotal($path){
    // 打开文件
	if (!$fp = @fopen($path,"r")) {
		$error = "打开文件{$path}失败";
		return false;
	}
	else {
		$max=0;
		while(!feof($fp)) {
			$line = fgets($fp,255);
			if (preg_match('/\/Count [0-9]+/', $line, $matches)){
				preg_match('/[0-9]+/',$matches[0], $matches2);
				if ($max<$matches2[0]) $max=$matches2[0];
			}
		}
		fclose($fp);
        // 返回页数
		return $max;
	}
}

//pdf to png
function pdf2png($pdf,$path,$page=0)
{  
	if(!is_dir($path))
	{
		mkdir($path,0700,true);
	}
	if(!extension_loaded('imagick'))
	{  
		echo '没有找到imagick！' ;
		return false;
	}  
	if(!file_exists($pdf))
	{  
		echo '没有找到pdf' ;
		return false;  
	}  
	$im = new Imagick();  
   $im->setResolution(200,200);   //设置图像分辨率
   $im->setCompressionQuality(80); //压缩比
   $im->readImage($pdf."[".$page."]"); //设置读取pdf的第一页
   //$im->thumbnailImage(200, 100, true); // 改变图像的大小
   $im->scaleImage(648,1024,true); //缩放大小图像
   $pdfnameArray = explode('.pdf',$pdf);
   $pdfname = $pdfnameArray[0];
   $filename = $path."/". $pdfname . '-' . $page .'.png';
   if($im->writeImage($filename) == true)
   {  
   	$Return  = $filename;  
   }  
   return $Return;  
}

$pageNum = getPageTotal($purename . '.pdf'); 
for($i = 0;$i < $pageNum;$i++){
	pdf2png($purename . '.pdf','images',$i);
} 

$newPdf = new FPDF();
for ($i=0; $i < $pageNum; $i++) { 
	$image = 'pdfToImage/' . $purename . '-' . $i . '.png';
	$newPdf->AddPage();
	$newPdf->Image($image,20,40,600,1000);
}
$newPdfName = 'new-' . $purename . '.pdf';
$newPdf->Output($newPdfName);

$text="你好啊COPYRIGHT@35kk8.com";
$textColor = array(0,255,255);
$font = 'simhei';
$alpha = 80;
$fontSize = 40;
$angle = 45;
$isSingle = false;
$textRaws = 1;
$pdfFile = "";

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
	$watermarker = new PDFWatermarker($newPdfName, 'output_' . $$newPdfName, $watermark); 

//Save the new PDF to its specified location
	$watermarker->savePdf();

?>