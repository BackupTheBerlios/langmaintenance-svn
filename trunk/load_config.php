<?
/**
 * Find config file and load it
 * 
 * @author    Karel Kozlik
 * @version   $Id: $
 * @package   lang_maintenance
 */

	$LANG_CFG=array();
	//default values

	/**
	 * directory where application for maintenance is localized
	 *
	 * @global string $LANG_CFG['app_dir']
	 */
	$LANG_CFG['app_dir'] = realpath(dirname(__FILE__)."/../..")."/";
	
	/**
	 * directory where language files is localized
	 *
	 * @global string $LANG_CFG['lang_dir']
	 */
	$LANG_CFG['lang_dir'] = realpath(dirname(__FILE__)."/../../lang")."/";

	/**
	 * directory where lang maintenance tools is localized
	 *
	 * @global string $LANG_CFG['lm_dir']
	 */
	$LANG_CFG['lm_dir'] = realpath(dirname(__FILE__))."/";

	/**
	 * directory where classes (core) is localized
	 *
	 * @global string $LANG_CFG['classes_dir']
	 */
	$LANG_CFG['classes_dir'] = realpath(dirname(__FILE__)."/classes")."/";

	/**
	 * default destination charset used by command 'convert'
	 *
	 * @global string $LANG_CFG['dest_charset']
	 */
	$LANG_CFG['dest_charset'] = "utf-8";

	/**
	 * default reference file used by command 'sync'
	 *
	 * @global string $LANG_CFG['reference_file']
	 */
	$LANG_CFG['reference_file'] = "english-iso-8859-1.php";

	/**
	 * directories that may command 'find' exclude when walking throught use regular expressions
	 *
	 * @global array $LANG_CFG['exclude_dirs']
	 */
	$LANG_CFG['exclude_dirs'] = array();
	
	//get current directory
	$cfg_dir = realpath(".");
	$cfg_file = "lang_maintenance.conf";
	
	$conf = null;
	
	do {
		if (file_exists($cfg_dir."/".$cfg_file)) {
			$conf = $cfg_dir."/".$cfg_file;
			break;
		}
		
		// save cfg_dir
		$cfg_dir_bak = $cfg_dir;
		// and try updir
		$cfg_dir = realpath($cfg_dir."/..");

		//until the root
	} while ($cfg_dir_bak != $cfg_dir);
	
	if (is_null($conf)) $conf = dirname(__FILE__)."/default.conf";

	/**
	 * Configuration file
	 */
	require_once($conf);	
	
	unset ($conf);
	unset ($cfg_dir);
	unset ($cfg_dir_bak);
	unset ($cfg_file);
	
?>
