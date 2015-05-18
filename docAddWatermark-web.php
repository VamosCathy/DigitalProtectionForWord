<?php
//包含依赖库
require 'pdf-watermarker/vendor/binarystash/fpdf/fpdf.php';
require 'pdf-watermarker/vendor/setasign/fpdi/fpdi.php';
require 'pdf-watermarker/pdfwatermarker/pdfwatermarker.php';
require 'pdf-watermarker/pdfwatermarker/pdfwatermark.php';


//convert word to pdf,返回写入的全路径
function convertDocToPdf($originFilePath){
	$function1_starttime = microtime(true);
	$command = 'unoconv --format %s --output %s %s';
	$outputFileName = uniqid() . ".pdf";
	$command = sprintf($command,'pdf',dirname(__file__) . "/upload/" . $outputFileName,$originFilePath);
	// echo $command;
	putenv('PATH=/usr/local/bin:/bin:/usr/bin:/usr/local/sbin:/usr/sbin:/sbin:/home/cathy/bin');
	// system('touch ' . dirname(__file__) . "/upload/" . $outputFileName);
	system($command . "&> /var/www/html/log.txt",$output);
	$function1_endtime = microtime(true);
	$function1_runtime = $function1_endtime - $function1_starttime;
	echo "convertDocToPdf run time = " . $function1_runtime . "<br />";	
	return $outputFileName;
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
		mkdir($pngpath,0755,true);
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
	// $im->scaleImage(1000,1024,true);
	// echo $pngpath;
	$filename = $pngpath . "/" . basename($pdfpath,".pdf") . "-" . $page . ".png";
	if ($im->writeImage($filename) == true) {
		$Return = $filename;
	}
	return $Return;
}

//pdf file to png
function pdf2png($pdfpath,$pngpath){
	$function2_starttime = microtime(true);
	$pageNum = getPageTotal($pdfpath);
	$pageNum = (int)$pageNum;
	for ($i=0; $i < $pageNum; $i++) { 
		$result = pdfpage2png($pdfpath,$pngpath,$i);
	}
	$function2_endtime = microtime(true);
	$function2_runtime = $function2_endtime - $function2_starttime;
	echo "pdf2png run time =" . $function2_runtime . "<br />";
}

//merge png to pdf
// function png2pdf($pngpath,$pageNum,$outputDirPath,$pureName){
// 	$newPdf = new FPDF();
// 	for ($i=0; $i < $pageNum; $i++) { 
// 		$image = $pngpath . $pureName . '-' . $i . '.png';
// 		$newPdf->AddPage();
// 		$newPdf->Image($image,20,40,600,0,'PNG');
// 	}
// 	$newPdfPath = $outputDirPath . '/new-' . $purename . '.pdf';
// 	$newPdf->Output($newPdfPath);
// 	return $newPdfPath;
// }

function png2pdf($pngpath,$pureName,$outputDirPath){
	$function3_starttime = microtime(true);
	$command = 'convert %s %s';
	$command = sprintf($command,$pngpath . '/*.png',$outputDirPath . 'output.pdf');
	// echo $command;
	system($command,$output);
	$function3_endtime = microtime(true);
	$function3_runtime = $function3_endtime - $function3_starttime;
	echo "png2pdf" . $function3_runtime . "<br />";
	return $outputDirPath . 'output.pdf';
}

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
	echo "productWatermark run time =" . $function4_runtime . "<br />";
	return $path;
}

if($_FILES["file"]["error"] > 0){
	echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
}
else{
	// var_dump($_FILES["file"]);
	if(file_exists($_FILES["file"]["name"])){
		echo $_FILES["file"]["name"] . "already exists.";
	}
	else{
		// echo "ok";
		$path_part = pathinfo($_FILES["file"]["name"],PATHINFO_EXTENSION);
		$wordPath = dirname(__file__) . "/upload/" . uniqid() . '.' . $path_part;
		// echo $wordPath;
		$result = move_uploaded_file($_FILES["file"]["tmp_name"],$wordPath);
		// chmod($wordPath,0766);
	}
}

$input = $_POST;
$text = $input['watermark'];//加入的水印字
// $isSingle = $input['issingle']; //水印是否为单个
$isSingle = true;

$pureName = basename($wordPath,$path_part);

//convert word to pdf,saved it in images document
$pdfName = convertDocToPdf($wordPath);
// $pdfName = "5554a05d2ff85.pdf";
$pdfPath = dirname(__file__) . "/upload/" . $pdfName;
$pngPath = dirname(__file__) . "/upload/images-" . uniqid();
pdf2png($pdfPath,$pngPath);

//add images together to pdf
$pageNum = getPageTotal($pdfPath);
$pageNum = (int)$pageNum;
// echo $pngPath;
$pdfFile = png2pdf($pngPath,$pureName,dirname(__file__) . "/upload/");
$watermarkPath = productWatermark($text);
// echo $pdfFile;

//add watermark
$function5_starttime = microtime(true);
$watermark = new PDFWatermark($watermarkPath);
$watermark->setPosition($isSingle);
$finalPath = dirname(__file__) . "/upload/final-" . uniqid() . '.pdf';
$finalPdf = new PDFWatermarker($pdfFile,$finalPath,$watermark);
$finalPdf->savePdf();
$function5_endtime = microtime(true);
$function5_runtime = $function5_endtime - $function5_starttime;
echo "add watermark run time = " . $function5_runtime;
$finalurl = 'http://192.168.1.188/upload/' . basename($finalPath);
// require_once __DIR__ . '/output.html';
echo '<a href="' . $finalurl . '">download</a>';
?>
