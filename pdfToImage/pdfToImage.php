<?php
class PdfToImage{
	//figure out the total pages of pdf
	public static function getPageTotal($pdfpath){
		if(!$fp = @fopen($pdfpath,"r")){
			$error = "fail to open {$pdfpath}";
			return false;
		}
		else{
			$max = 0;
			while (!feof($fp)) {
				$line = fgets($fp,255);
				if (preg_match('/\/Count [0-9]+/',$line, $matches)) {
					preg_match('/[0-9]+/',$matches[0], $matches2);
					if ($max<$matches2[0]) $max=$matches2[0];
				}
			}
			fclose($fp);
			return $max;//string
		}
	}
	//pdf to png
}
?>