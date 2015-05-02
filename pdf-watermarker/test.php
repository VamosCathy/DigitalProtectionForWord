
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
$text="COPYRIGHT@35kk8.com";
$textColor = array(88,88,88);
$font = 'simhei';
$alpha = 80;
$fontSize = 40;
$angle = 45;
$isSingle = false;
$textRaws = 1;



// $imageSize = $fontSize * 13;
$width = $fontSize * 19;
$height = $fontSize;
$im = @imagecreatetruecolor($width, $height)
      or die('Cannot Initialize new GD image stream');

//保存完整alpha通道信息
imagealphablending($im,false);
imagesavealpha($im,true);

//设置底色透明
$bg = imagecolorallocatealpha($im,255,255,255,127);
imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $bg);

//文字颜色设置
$text_color = imagecolorallocatealpha($im, 255, 255, 255, 80);

//文字内容
$text = mb_convert_encoding($text,'UTF-8',"ASCII,JIS,UTF-8,EUC-JP,SJIS,GB2312,GBK");

//写入水印文字
imagettftext($im, $fontSize, $angle, $fontSize, $imageSize - ($fontSize + 5) * $textRaws, $text_color, 'fonts/kaiti.ttf', $text);

//保存水印
// $path =  sys_get_temp_dir() . '/' . uniqid() . '.png'; //产生临时保存路径
// imagepng($im,$path);
imagepng($im);
imagedestroy($im);
?>