<?php
/*
 * This is an example language file. Language file may contain comments
 * like this.
 */

// Or like this. 

/* All comments from reference language file will be copied by 'sync'
 * to other language files.
 * 
 * Here can be also CVS or SVN id tag. It's the only thing which
 * will not be copied, will be holded in language file.
 *
 * $Id: $
 */


/*
 * Next here may be some international setting as charset or format of date or 
 * time or what you wish.
 * This setting is in form: $lang_set['name'] = "value"; 
 * 
 * The $lang_set['charset'] is only required.
 *
 * If some $lang_set from reference file is missing in other files, it 
 * will be added by 'sync'
 */
 
$lang_set['charset'] = "iso-8859-1";
$lang_set['date'] = "Y-m-d";
$lang_set['time'] = "H:m:s";

/*
 * And finaly some language phrases in form $lang_str['phrase'] = 'some string';
 * If some phrase from reference file is missing in other files, it
 * will be added by 'sync' with comment //to translate
 *
 * Some examples:
 */

$lang_str['greeting'] = 				"Ahoj";
$lang_str['how_are_you'] = 				"Jak se máš?";
$lang_str['lang_tools'] = 				"Toto jsou nástroje pro práci s jazykovými mutacemi pro snadnou tvorbu vícejazyèných aplikací";


//php ending tag, leave it alone on single line
?>
