<?
/**
 * Synchronization of language files
 * 
 * @author    Karel Kozlik
 * @version   $Id: $
 * @package   lang_maintenance
 */
/**
 */
require_once $LANG_CFG['classes_dir']."c_check.php";
require_once $LANG_CFG['classes_dir']."c_utils.php";

/**#@+
 * @access private
 */
/**
 * Unspecified type of line in language file
 */
define ("LNG_LINE_TYPE_OTHER", 0);
/**
 * Line containing string <i>$lang_str</i>
 */
define ("LNG_LINE_TYPE_STR", 1);
/**
 * Line containing national setting <i>$lang_set</i>
 */
define ("LNG_LINE_TYPE_SET", 2);
/**
 * Line containing CVS $id
 */
define ("LNG_LINE_TYPE_CVS", 3);
/**
 * Line containing php ending tag
 */
define ("LNG_LINE_TYPE_ENDTAG", 4);
/**#@-*/

/**
 * Collect information of line from language file
 * 
 * @author    Karel Kozlik
 * @version   $Id: $
 * @package   lang_maintenance
 * @access    private
 */
class CLangLine{
	/**
	 * type of line (LNG_LINE_TYPE_*)
	 * @var int
	 */
	var $type;
	/**
	 * content of line
	 * @var string
	 */
	var $content;
	/**
	 * for lines of type {@link LNG_LINE_TYPE_STR} or {@link LNG_LINE_TYPE_SET} contain key
	 * @var string
	 */
	var $key;
	/**
	 * flag if line was already stored to output file
	 * @var bool
	 */
	var $f_used = false;

	/**
	 * @param int $type type of line (LNG_LINE_TYPE_*)
	 * @param string $content content of line
	 * @param string $key for lines of type {@link LNG_LINE_TYPE_STR} or {@link LNG_LINE_TYPE_SET} contain key
	 */
	function CLangLine($type, $content, $key){
		$this->type = $type;
		$this->content = $content;
		$this->key = $key;
	}
}

/**
 * Synchronization of language files
 * 
 * @author    Karel Kozlik
 * @version   $Id: $
 * @package   lang_maintenance
 */
class Csync {
	/**#@+
	 * @access private
	 */
	/**
	 * Structured reference file in associative array
	 *
	 * Contain references for all phrases and all line types to {@link $ref_index}
	 *
	 * @var array
	 */
	var $ref_index;
	/**
	 * Lines of reference file
	 *
	 * Array of {@link CLangLine}
	 *
	 * @var array
	 */
	var $ref_lines;
	/**
	 * Path to reference file
	 * @var string
	 */
	var $ref_file;
	/**#@-*/
	/**
	 * Should be unused phrases deleted or leave they on end of file
	 * @var bool
	 */
	var $delete_unused_phrases = false;

	/**
	 * @param string $ref_file Path to reference language file with which all other files will be synchronized
	 */
	function Csync($ref_file){
		$this->ref_file = $ref_file;

		$chk = new Ccheck($ref_file);

		if ($chk->check_duplicities()) {
			Cutils::error_report("Duplicities found in file: ".$ref_file." - can't continue");
			exit (1);
		}

		unset($chk); //free memory

		$this->make_index_of_entries($ref_file, $this->ref_index, $this->ref_lines);
	}

	/**
	 * Parse language file and transport it to internal structure
	 *
	 * @param string $lang_file Path to language file
	 * @param array $index
	 * @param array $lines array of {@link CLangLine}
	 * @access private
	 */
	function make_index_of_entries($lang_file, &$index, &$lines){

		$fp=fopen($lang_file,'r');
		
		if (!$fp) {
			fwrite(STDERR, "Can't open file: ".$lang_file."\n");
			exit;
		}
		
		$index = array();
		$lines = array();
		$line_nr = 0;
		
		//read all keys of $lang_str into array
		$index = array();
		while (!feof($fp)){
			$line_nr++;
			$line=fgets($fp, 4096);
		
			$key = Cutils::parse_key_of_lang_str($line);
			if (!is_null($key)) {
				$lines[$line_nr] = new CLangLine(LNG_LINE_TYPE_STR, $line, $key);
				$index["str"][$key]=$line_nr;
				continue;
			}

			$key = Cutils::parse_key_of_lang_set($line);
			if (!is_null($key)) {
				$lines[$line_nr] = new CLangLine(LNG_LINE_TYPE_SET, $line, $key);
				$index["set"][$key]=$line_nr;
				continue;
			}
			
			if(ereg('\\$Id:[^$]*\\$', $line)){
				$lines[$line_nr] = new CLangLine(LNG_LINE_TYPE_CVS, $line, null);
				$index["cvs"]=$line_nr;
				continue;
			}

			if(ereg("^[[:blank:]]*\\?(>)[[:blank:]]*[\n\r]*$", $line)){
				$lines[$line_nr] = new CLangLine(LNG_LINE_TYPE_ENDTAG, $line, null);
				$index["endtag"]=$line_nr;
				continue;
			}

			//else
			$lines[$line_nr] = new CLangLine(LNG_LINE_TYPE_OTHER, $line, null);

		}
		
		fclose($fp);
		
		return;
	
	}

	/**
	 * Synchronize given language file with reference file
	 *
	 * @param string $lang_file Path to language file
	 */
	function sync($lang_file){

		if (realpath($this->ref_file) == realpath($lang_file)){
			echo "Reference and synchronized files are same - nothing to do\n";
			return;
		}

		$chk = new Ccheck($lang_file);

		if ($chk->check_duplicities()) {
			Cutils::error_report("Duplicities found in file: ".$lang_file." - can't continue");
			exit (1);
		}

		unset($chk); //free memory

		$this->make_index_of_entries($lang_file, $index, $lines);
		
		if (!rename($lang_file, $lang_file.".temp")) {
			Cutils::error_report("Can't rename file: ".$lang_file);
			exit (1);
		}

		$fp=fopen($lang_file,'w');
		
		if (!$fp) {
			Cutils::error_report("Can't create file: ".$lang_file);
			exit (1);
		}
		
		foreach($this->ref_lines as $ref_line){
			if ($ref_line->type == LNG_LINE_TYPE_STR){
				if (isset($index['str'][$ref_line->key])){
					fwrite($fp, $lines[$index['str'][$ref_line->key]]->content);
					$lines[$index['str'][$ref_line->key]]->f_used = true;
				}
				else{
					$write_str = $ref_line->content;
					
					if (!ereg("//to translate[[:blank:]]*$", $write_str)){
						//trim ending \n
						$write_str = rtrim($write_str);
						$write_str.="\t//to translate \n";
					}

					fwrite($fp, $write_str);
				}
				continue;
			}
			if ($ref_line->type == LNG_LINE_TYPE_SET){
				if (isset($index['set'][$ref_line->key])){
					fwrite($fp, $lines[$index['set'][$ref_line->key]]->content);
					$lines[$index['set'][$ref_line->key]]->f_used = true;
				}
				else{
					fwrite($fp, $ref_line->content);
				}
				continue;
			}
			elseif ($ref_line->type == LNG_LINE_TYPE_CVS){
				if (isset($index['cvs'])){
					fwrite($fp, $lines[$index['cvs']]->content);
				}
				else{
					fwrite($fp, ereg_replace('\\$Id:[^$]*\\$', '$Id: $', $ref_line->content));
				}
				continue;
			}
			elseif ($ref_line->type == LNG_LINE_TYPE_ENDTAG){
				// skip endtag we must write unused entries first
				continue;
			}
			elseif ($ref_line->type == LNG_LINE_TYPE_OTHER){
				fwrite($fp, $ref_line->content);
				continue;
			}
			else{
				Cutils::error_report("Unsuported type of line: ".$ref_line->type);
				exit (1);
			}
		}
		
		// add unused entries from $lang_file to end

		if (!$this->delete_unused_phrases){
			$first = true;
			foreach($lines as $line){
				if (!$line->f_used and $line->type == LNG_LINE_TYPE_STR){
					if ($first){
						$first = false;
						fwrite($fp, "\n\n/****************************************************/\n");
						fwrite($fp, "/* strings which are missing in reference lang file */\n");
						fwrite($fp, "/****************************************************/\n\n");
					}
				
					fwrite($fp, $line->content);
				}
			}
		}
		
		fwrite($fp, "?>\n");
		
		fclose($fp);

		if (!unlink($lang_file.".temp")) {
			fwrite(STDERR, "Warning: can't remove temporary file: ".$lang_file.".temp\n");
		}

	}


}

?>
