<?
/**
 * Validation of language files
 * 
 * @author    Karel Kozlik
 * @version   $Id$
 * @package   lang_maintenance
 */
/**
 */
require_once $LANG_CFG['classes_dir']."c_utils.php";

/**
 * Validation of language files
 * 
 * Class is used for checking if language file has correct structure.
 * Currently is only tested if keys of language files are unique.
 * 
 * @author    Karel Kozlik
 * @version   $Id$
 * @package   lang_maintenance
 */
class Ccheck {
	/**
	 * Path to validated file
	 * @var string
	 * @access private
	 */
	var $filename;
	/**
	 * Associative array contain line number for each phrase
	 * @var array
	 * @access private
	 */
	var $lang_struct = null;
	/**
	 * Array contain found duplicities as strings (errors messages)
	 * @var array
	 * @access private
	 */
	var $dupl_str;
	/**
	 * Number of duplicities
	 * @var int
	 * @access private
	 */
	var $duplicities;

	/**
	 * @param string $filename Path to language file which may be validated
	 */
	function Ccheck($filename){
		$this->filename = $filename;
	}
	
	/**
	 * Parse language file, make index of phrases in it and store it in internal variables
	 * @access private
	 */
	function make_index_of_phrases(){
		$this->dupl_str = array();
		
		$fp=fopen($this->filename,'r');
		
		if (!$fp) {
			Cutils::error_report("Can't open file: ".$this->filename);
			exit (1);
		}

		//read all keys of $lang_str into array
		$line_nr=0;
		$this->lang_struct=array();
		$this->duplicites=0;
		while (!feof($fp)){
			$line=fgets($fp, 4096);
			$line_nr++;

			//parse key of $lang_str array	
			$key = Cutils::parse_key_of_lang_str($line);
			if (is_null($key)) continue;

			if (isset($this->lang_struct[$key])){
				$this->dupl_str[] = "duplicated key: ".$key.", lines: ".$this->lang_struct[$key]." and ".$line_nr;
				$this->duplicites++;
				continue;
			}
		
			$this->lang_struct[$key]=$line_nr;
		}
	
		fclose($fp);

		return;
	}

	/**
	 * Return associative array containing numbers of lines where phrases are located in language file
	 * @return array
	 */
	function get_index_of_phrases(){
		if (is_null($this->lang_struct)) $this->make_index_of_phrases();
	
		return $this->lang_struct;
	}

	/**
	 * Check if language file contain some duplicities
	 *
	 * If some duplicities are found, messages are displayed to error output
	 * @return int number of founded duplicities 
	 */
	function check_duplicities(){
		if (is_null($this->lang_struct)) $this->make_index_of_phrases();
		
		foreach($this->dupl_str as $row){
			fwrite(STDERR, $row."\n");
		}

		return $this->duplicites;
	}
}
?>
