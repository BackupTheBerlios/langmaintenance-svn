<?
/**
 * Convert language file to diferent charset
 * 
 * @author    Karel Kozlik
 * @version   $Id: $
 * @package   lang_maintenance
 */
/**
 */
require_once $LANG_CFG['classes_dir']."c_utils.php";

/**
 * Convert language file to diferent charset
 * 
 * @author    Karel Kozlik
 * @version   $Id: $
 * @package   lang_maintenance
 */
class Cconv{
	/**
	 * Convert source file to diferent charset
	 *
	 * Charset of source file is detected automaticaly by value of 'charset'
	 * variable inside it. Target file is stored in same directory as source
	 * and it's name is generated automaticaly. Also 'charset' variable
	 * inside is automaticaly changed.
	 *
	 * @param string $file Path to source file
	 * @param string $target_encoding Charset of target file
	 * @static
	 */
	function convert($file, $target_encoding){
		$fp=fopen($file,'r');
		
		if (!$fp) {
	 		Cutils::error_report("Can't open file: ".$file);
			exit (1);
		}
		
		$source_encoding = null;
	
		/* find source_encoding */
		while (!feof($fp)){
			$line=fgets($fp, 4096);

			$key = Cutils::parse_key_of_lang_set($line);
			if ($key == 'charset') {

				if (!ereg('\\$lang_set\[\'[^\']+\'\][[:blank:]]*=[[:blank:]]*["\']([^\'"]+)["\']', $line, $regs)){
			 		Cutils::error_report("Can't parse source encoding");
					exit (1);
				}
				$source_encoding = $regs[1];
				
			}
		}
		
		if (!$source_encoding){
	 		Cutils::error_report("Can't find source encoding - \$lang_set['charset'] may miss in language file");
			exit (1);
		} 

		if ($source_encoding == $target_encoding){
			echo "Source and target charsets are same - nothing to work\n";
			return;
		}

		// move back to the begining of the file 
		if (!rewind($fp)){
	 		Cutils::error_report("Can't seek to beginning of file");
			exit (1);
		}

		if ((!ereg("^([^-]+)-", basename($file), $regs)) or (!$regs[1])){
	 		Cutils::error_report("Can't get language name");
			exit (1);
		}

		$lang = $regs[1];
		$file_dest = dirname($file)."/".$lang."-".$target_encoding.".php";

		$fpd=fopen($file_dest,'w');
		
		if (!$fpd) {
	 		Cutils::error_report("Can't write file: ".$file_dest);
			exit (1);
		}

		while (!feof($fp)){
			$line=fgets($fp, 4096);

			$key = Cutils::parse_key_of_lang_str($line);
			if (!is_null($key)) {
				$conv = iconv($source_encoding, $target_encoding, $line);
				
				if ($conv === false) {
			 		Cutils::error_report("ERROR when converting line: ".$line);
					exit (1);
				}
				
				fwrite($fpd, $conv);
				continue;				
			}

			$key = Cutils::parse_key_of_lang_set($line);
			if ($key == 'charset') {

				$line = ereg_replace('(\\$lang_set\[\'[^\']+\'\][[:blank:]]*=[[:blank:]]*["\'])[^\'"]+(["\'])', 
								"\\1".$target_encoding."\\2",$line);
				fwrite($fpd, $line);
				continue;
			}

			/* else write the line as is */
			fwrite($fpd, $line);
		}
		

		fclose($fpd);
		fclose($fp);

		echo "Converted OK\n";

	}

}

?>
