<?php
define ('SS_IMPORTMULTIJOURNAL', 101<<8);

class hooks_import_multijournalentries extends hooks {
	var $module_name = 'import_multijournalentries'; 

	/*
		Install additional menu options provided by module
	*/
	function install_options($app) {
		global $path_to_root;

		switch($app->id) {
			case 'GL':
				$app->add_rapp_function(2, trans('Import &Multiple Jourmal Entries'),
					$path_to_root.'/modules/import_multijournalentries/import_multijournalentries.php', 'SA_CSVMULTIJOURNALIMPORT');
		}
	}

	function install_access()
	{
		$security_sections[SS_IMPORTMULTIJOURNAL] =	trans("Import Multiple Journal Entries");

		$security_areas['SA_CSVMULTIJOURNALIMPORT'] = array(SS_IMPORTMULTIJOURNAL|101, trans("Import Multiple Journal Entries"));

		return array($security_areas, $security_sections);
	}
}
?>