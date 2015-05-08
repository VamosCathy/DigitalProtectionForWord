<?php
//convert word to pdf
function convertDocToPdf($originFilePath,$outputDirPath){
	$command = 'unoconv --format %s --output %s %s';
	$command = sprintf($command,'pdf',$outputDirPath,$originFilePath);
	system($command,$output);

	return $output;
}
convertDocToPdf('笔记.doc','../');
?>