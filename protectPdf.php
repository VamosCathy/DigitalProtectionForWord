<?php
// 所有中间文件都暂时保存在doc文件夹下
// 生成最终pdf后将中间文件删除
// 最终pdf放到指定文件夹下

//convert word to pdf,返回写入的全路径
function convertDocToPdf($originFile){
	// echo "originFile = " . $originFile;
	// $function1_starttime = microtime(true);
    	//获得文件所在位置
    	$originFilePath = pathinfo($originFile,PATHINFO_DIRNAME);
	$outputFileName = uniqid() . ".pdf";
	$command = 'unoconv --format %s --output %s %s';
	$command = sprintf($command,'pdf',$originFilePath . '/' . $outputFileName,$originFile);
	// echo "command = " . $command . PHP_EOL;
	putenv('PATH=/usr/local/bin:/bin:/usr/bin:/usr/local/sbin:/usr/sbin:/sbin:/home/cathy/bin');
	system($command . "&> " . dirname(__file__) . "/log.txt",$output);
	// $function1_endtime = microtime(true);
	// $function1_runtime = $function1_endtime - $function1_starttime;
	// echo "convertDocToPdf run time = " . $function1_runtime . "<br />";	
	return $originFilePath . '/' . $outputFileName;
}

//get pdf total pages
function getPageTotal($pdfpath){
	// echo "pdfpath = " . $pdfpath . PHP_EOL;
	if (!$fp = @fopen($pdfpath,"r")) {
		$error = "fail to open {$pdfpath}";
		echo $error;
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
	echo "pngpath=" . $pngpath . PHP_EOL;
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
	// var_dump($pageNum);
	$pageNum = (int)$pageNum;
	for ($i=0; $i < $pageNum; $i++) { 
		$result = pdfpage2png($pdfpath,$pngpath,$i);
	}
	$function2_endtime = microtime(true);
	$function2_runtime = $function2_endtime - $function2_starttime;
	// echo "pdf2png run time =" . $function2_runtime . "<br />";
}

//merge png to pdf
function png2pdf($pngpath,$outputFile){
	// $function3_starttime = microtime(true);
	$command = 'convert %s %s';
	$outputPath = pathinfo($outputFile,PATHINFO_EXTENSION);
	// $pdfName = uniqid() . ".pdf";
	$command = sprintf($command,$pngpath . '/*.png',$outputFile);
	// echo "png2pdf-command = " . $command;
	// echo $command;
	system($command,$output);
	// $function3_endtime = microtime(true);
	// $function3_runtime = $function3_endtime - $function3_starttime;
	// echo "png2pdf" . $function3_runtime . "<br />";
	return $outputFile;
}

function deldir($dir){
	$dh = opendir($dir);
	while($file = readdir($dh)){
		if($file != "." && $file != ".."){
			$fullPath = $dir . '/' . $file;
			if(!is_dir($fullPath)){
				unlink($fullPath);
			}
			else{
				deldir($fullPath);
			}
		}
	}
	closedir($dh);

	if(rmdir($dir)){
		return true;
	}
	else{
		return false;
	}
}

//传入参数：doc文件路径
$docFile = $argv[1];
$outputFile = $argv[2]; //output PDF file

$docPath = pathinfo($docFile,PATHINFO_DIRNAME);
$docExt = pathinfo($docFile,PATHINFO_EXTENSION);
// $outputPath = pathinfo($outputFile,PATHINFO_DIRNAME);

//convert word to pdf,saved it in images document
$originPdfFile = convertDocToPdf($docFile); //返回可复制pdf的全路径
$pngPath = $docPath . "/images-" . uniqid();
pdf2png($originPdfFile,$pngPath);

//add images together to pdf
$pageNum = getPageTotal($originPdfFile);
$pageNum = (int)$pageNum;
// echo $pngPath;
$finalPdfFile = png2pdf($pngPath,$outputFile);
echo "finalPdfFile = " . $finalPdfFile;
//垃圾清理
unlink($originPdfFile);
deldir($pngPath);
?>
