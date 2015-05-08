<?php
/**
* Sample use of Unoconv class
*  
*/
require 'Unoconv.php';

use Unoconv\Unoconv;

// Converting to PDF
$originFilePath = '笔记.doc';
$outputDirPath  = './';
Unoconv::convertToPdf($originFilePath, $outputDirPath);

// Converting to DOCX
//$originFilePath = 'test.odt';
//$outputDirPath  = './';
//Unoconv::convert($originFilePath, $outputDirPath, 'docx');
?>
