<?
/**
 * Walk through files of application and collect phrases which are used 
 * 
 * @author    Karel Kozlik
 * @version   $Id: $
 * @package   lang_maintenance
 */
/**
 */
require_once $LANG_CFG['classes_dir']."c_check.php";
require_once $LANG_CFG['classes_dir']."c_utils.php";

/**
 * Contain pair phrase and file in which is used
 * 
 * @author    Karel Kozlik
 * @version   $Id: $
 * @package   lang_maintenance
 * @access    private
 */
class ClangStrEntry {
	/**
 	 * Phrase
 	 * @var string
	 */
	var $str; 
	/**
 	 * Path to file in which phrase $str is used 
 	 * @var string
	 */
	var $file;
	
	/**
	 * @param string $str Phrase
	 * @param string $file Path to file in which phrase $str is used
	 */
	function ClangStrEntry($str, $file){
		$this->str = $str;
		$this->file = $file;
	}
}

/**
 * Walk through files of application and collect phrases which are used 
 * 
 * @author    Karel Kozlik
 * @version   $Id: $
 * @package   lang_maintenance
 */
class Cfind{

	/**
	 * Array of {@link ClangStrEntry}
	 * @var array
	 * @access private
	 */
	var $lang_strings;
	/**
	 * Contain diferencies between given language file and collected informations
	 *
	 * @var array
	 * @access private
	 * @see get_diff()
	 */
	var $last_diff = null; //used by methods print_diff_*();

	function Cfind(){
		$this->lang_strings = array();
	}
    
	/**#@+
	 * <p>Directories with language files and with lang maintenance tools 
	 * (config variables: {@link $LANG_CFG['lang_dir']} and {@link $LANG_CFG['lm_dir']}) 
	 * are automaticaly skiped. This allow to simply put language maintenance 
	 * tools directly into application directory tree.
	 * </p>
	 * <p>
	 * Directories which match to some regular exception in config variable
	 * {@link $LANG_CFG['exclude_dirs']} are skipped too.
	 * </p>
	 */
	/**
	 * Search for lang strings in files specified by $where 
	 * 
	 * Search for lang strings in files specified by $where 
	 * and keep this informations in internal data structures. Call this 
	 * method multiple to collect informations from more places if you need.
	 * 
	 * @param string $where path to directory or regular file
	 */
	function find($where){
		global $LANG_CFG;
		
		if (is_dir($where)){
			if (substr($where, -1) != "/") $where.='/'; //add  '/' to end
			$this->find_in_dir($where);
		}
		else {
			$this->find_in_file($where);
		}

		$this->unique_lang_strings();
	
	}

	/**
	 * Search for phrases in files in directory $dir
	 *
	 * @param string $dir
	 * @access private
	 */
	function find_in_dir($dir){
		global $LANG_CFG;

		$rp_dir = realpath ($dir); 
		$rp_lang = realpath ($LANG_CFG['lang_dir']); 
		$rp_lm = realpath ($LANG_CFG['lm_dir']); 

		if (false === $rp_dir) Cutils::error_report('Possible error in path to dir: '.$dir);
		if (false === $rp_lang) Cutils::error_report('Possible error in $LANG_CFG[\'lang_dir\']');
		if (false === $rp_lm) Cutils::error_report('Possible error in $LANG_CFG[\'lm_dir\']');

		// exclude directory with language files and with this application
		if ($rp_dir == $rp_lang) return;
		if ($rp_dir == $rp_lm) return;

		// exclude directories from $LANG_CFG['exclude_dirs']
		foreach($LANG_CFG['exclude_dirs'] as $exclude){
			if (ereg($exclude, $dir)) return;
		}

		$d = dir($dir); 
		while (false !== ($entry = $d->read())) { 

			// skip . and ..
			if ($entry == "." or $entry == "..") continue;

			if (is_dir($dir.$entry)){
				$this->find_in_dir($dir.$entry."/");
			}
			else {
				$this->find_in_file($dir.$entry);
			}
		} 
		$d->close(); 
	}
	/**#@-*/

	/**
	 * Search for phrases in file $file
	 *
	 * @param string $file
	 * @access private
	 */
	function find_in_file($file){
		global $LANG_CFG;

		$fp=fopen($file,'r');
		
		if (!$fp) Cutils::error_report("Can't open file: ".$file);

		while (!feof($fp)){
			$line=fgets($fp, 4096);

			if (preg_match_all('/\\$lang_str\[\'([a-zA-Z0-9_\x7f-\xff]+)\'\]/', $line, $regs, PREG_PATTERN_ORDER)){
				foreach($regs[1] as $reg){
					$this->lang_strings[] = new ClangStrEntry($reg, $file);
				}				
			}

			if (preg_match_all('/\\$lang_str\.([a-zA-Z0-9_\x7f-\xff]+)/', $line, $regs, PREG_PATTERN_ORDER)){
				foreach($regs[1] as $reg){
					$this->lang_strings[] = new ClangStrEntry($reg, $file);
				}				
			}

		}

		fclose($fp);
	}

	/**
	 * Unique collected pairs (phrase, file) stored in array {@link $lang_strings}
	 *
	 * @access private
	 */
	function unique_lang_strings() {
		$tmp = array();

		foreach($this->lang_strings as $a => $b)
			$tmp[$a] = serialize($b);

		$newinput = array();
		foreach(array_unique($tmp) as $a => $b)
			$newinput[$a] = $this->lang_strings[$a];

		$this->lang_strings = $newinput;
	}
	

	/**
	 * Return collected informations grouped by phrases
	 *
	 * @return array Associative array: phrases are as keys and elements are arrays of files
	 */
	function get_result_by_strings(){
		$str_array = array();
		
		/* collect information about strings into array indexed by strings */
		foreach($this->lang_strings as $row){
			if (!isset($str_array[$row->str])) $str_array[$row->str] = array();
			$str_array[$row->str][] = $row->file;
		}
		
		/* unique arrays of files */
		foreach($str_array as $key => $row){
			$str_array[$key] = array_unique($str_array[$key]);
		}
		
		return $str_array;
	}
	
	
	/**
	 * Print output of method {@link get_result_by_strings()} 
	 *
	 * For each phrase is displayed list of files in which this phrase is used
	 */
	function print_result_by_strings(){
		
		$str_array = $this->get_result_by_strings();
		
		/* sort array by strings */
		ksort($str_array);
		
		/* print results */
		foreach($str_array as $key=>$val){
			echo $key."\n";
		
			foreach($val as $file){
				echo " * ".$file."\n";
			}
			
			echo "\n";
		}
		
	}


	/**
	 * Return collected informations grouped by files
	 *
	 * @return array Associative array: files are as keys and elements are arrays of phrases
	 */

	function get_result_by_files(){
		$file_array = array();
		
		/* collect information about strings into array indexed by files */
		foreach($this->lang_strings as $row){
			if (!isset($file_array[$row->file])) $file_array[$row->file] = array();
			$file_array[$row->file][] = $row->str;
		}
		
		/* unique arrays of files */
		foreach($file_array as $key => $row){
			$file_array[$key] = array_unique($file_array[$key]);
		}
		
		return $file_array;
	}
	
	
	/**
	 * Print output of method {@link get_result_by_files()} 
	 *
	 * For each file is displayed list of phrases which are used in this file
	 */
	function print_result_by_files(){
		
		$file_array = $this->get_result_by_files();
		
		/* sort array by files */
		ksort($file_array);
		
		/* print results */
		foreach($file_array as $key=>$val){
			echo $key."\n";
		
			foreach($val as $str){
				echo " * ".$str."\n";
			}
			
			echo "\n";
		}
		
	}

	/**
	 * Get diffs between collected informations and given language file
	 *
	 * Return array with two keys ('unused' and 'missing') containing subarrays.
	 *
	 * Output of this method is also stored in internal structure for later use
	 * by methods {@link print_diff_missing()} and {@link print_diff_unused()}
	 *
	 * Subarray 'unused' contain array of phrases from language file which aren't
	 * used in application. Phrases are stored as assoc array with two keys:
	 * 'phrase' and 'line'. Phrase is self descriptive and line contain line on
	 * which phrase is defined in language file.
	 *
	 * Subarray 'missing' contain array of phrases which are used in application
	 * but are missing in language file. Structure of this subarray is same. 
	 * 'line' fields always contain null.
	 *
	 * <b>Example of output:</b>
	 * <pre>
	 *	Array
	 *	(
	 *	    [missing] => Array
	 *	        (
	 *	            [0] => Array
	 *	                (
	 *	                    [phrase] => hello
	 *	                    [line] => null
	 *	                )
	 *	            [1] => Array
	 *	                (
	 *	                    [phrase] => how_are_you
	 *	                    [line] => null
	 *	                )
	 *	        )
	 *	
	 *	    [unused] => Array
	 *	        (
	 *	            [0] => Array
	 *	                (
	 *	                    [phrase] => err_not_found
	 *	                    [line] => 104
	 *	                )
	 *	
	 *	            [1] => Array
	 *	                (
	 *	                    [phrase] => hi
	 *	                    [line] => 234
	 *	                )
	 *	        )
     *	)
	 *	</pre>
	 * 
	 * @param string $filename Path to language file with wich are collected informations compared
	 * @return array
	 */
	function get_diff($filename){
		$chk = new Ccheck($filename);

		if ($chk->check_duplicities()) {
			fwrite(STDERR, "Warning! Duplicities found in file: ".$filename."\n");
		}

		$phrases = $chk->get_index_of_phrases();
		$str_array = $this->get_result_by_strings();

		unset($chk); //free memory

		$out = array();
		$out['missing'] = array();
		$out['unused'] = array();

		/* get list of missing phrases */
		foreach($str_array as $key=>$val){
			if (!isset($phrases[$key])){
				$out['missing'][] = array("phrase" => $key,
										  "line" => null);
			}
		}
	
		/* get list of unused phrases */
		foreach($phrases as $key=>$val){
			if (!isset($str_array[$key])){
				$out['unused'][] = array("phrase" => $key,
										 "line" => $val);
			}
		}

		$this->last_diff = $out;
		return $out;
	}

	/**
	 * Display phrases which are missing in language file
	 * 
	 * Before calling this method {@link get_diff()} must be called 
	 */
	function print_diff_missing(){
		if (is_null($this->last_diff) or !is_array($this->last_diff['missing'])){
			Cutils::error_report("ERROR: No diffs. Call method Cfind::get_diff() first!");
			exit (1);
		}

		foreach($this->last_diff['missing'] as $row){
			echo $row['phrase']."\n";
		}
	}

	/**
	 * Display phrases from language file which are unused
	 * 
	 * Before calling this method {@link get_diff()} must be called 
	 */
	function print_diff_unused(){
		if (is_null($this->last_diff) or !is_array($this->last_diff['unused'])){
			Cutils::error_report("ERROR: No diffs. Call method Cfind::get_diff() first!");
			exit (1);
		}

		foreach($this->last_diff['unused'] as $row){
			echo $row['phrase'].":".$row['line']."\n";
		}
	}

}

?>
