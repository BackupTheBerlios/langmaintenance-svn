<?
/**
 * Various functions
 * 
 * @author    Karel Kozlik
 * @version   $Id: $
 * @package   lang_maintenance
 */
/**
 * Various functions
 * 
 * @author    Karel Kozlik
 * @version   $Id: $
 * @package   lang_maintenance
 */
class Cutils{

	/**
	 * Parse given string if contain language string "$lang_str['something']"
	 *
	 * If contain it return the key, otherwise return null
	 *
	 * @param string $str
	 * @return string
	 * @static
	 */
	function parse_key_of_lang_str($str){
		if (!ereg('^[[:blank:]]*\\$lang_str\[\'([^\']+)\'\]', $str, $regs)) return null;
		return $regs[1];
	}

	/**
	 * Parse given string if contain national setting "$lang_set['something']"
	 *
	 * If contain it return the key, otherwise return null
	 *
	 * @param string $str
	 * @return string
	 * @static
	 */
	function parse_key_of_lang_set($str){
		if (!ereg('^[[:blank:]]*\\$lang_set\[\'([^\']+)\'\]', $str, $regs)) return null;
		return $regs[1];
	}
	
	/**
	 * Report error message $msg and terminate script execution
	 *
	 * @param string $msg
	 * @static
	 */
	function error_report($msg){
		fwrite(STDERR, $msg."\n");
		exit (1);
	}
}
?>
