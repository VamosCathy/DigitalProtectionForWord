<?php
/**
* PDF2PNG   
* @param $pdf  待处理的PDF文件
* @param $path 待保存的图片路径
* @param $page 待导出的页面 -1为全部 0为第一页 1为第二页
* @return      保存好的图片路径和文件名
*/
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
   $filename = $path."/". $pdf . '-' . $page .'.png';
   if($im->writeImage($filename) == true)
   {  
      $Return  = $filename;  
   }  
   return $Return;  
}
$pageNum = getPageTotal("笔记.pdf"); 
for($i = 0;$i < $pageNum;$i++){
	pdf2png('笔记.pdf','images',$i);
}  
//echo '<div align="center"><img src="'.$s.'"></div>';
?>
