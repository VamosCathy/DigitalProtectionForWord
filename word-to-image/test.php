<?php
$pdf_file = "leetcode-cpp.pdf";
$save_to = "demo.jpg"
$img = new imagick($pdf_file);

//set new format
$img->setImageFormat('jpg');

//save image file 
$img->writeImage($save_to);
?>
