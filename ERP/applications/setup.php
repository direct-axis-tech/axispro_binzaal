<?php
/**********************************************************************
    Direct Axis Technology L.L.C.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
class setup_app extends application
{
	function __construct()
	{
		parent::__construct("system", trans($this->help_context = "S&etup"));

		$this->add_module(trans("Company Setup"));
		$this->add_lapp_function(0, trans("&Company Setup"),
			"admin/company_preferences.php?", 'SA_SETUPCOMPANY', MENU_SETTINGS);
		$this->add_lapp_function(0, trans("&User Accounts Setup"),
			"admin/users.php?", 'SA_USERS', MENU_SETTINGS);
		$this->add_lapp_function(0, trans("&Access Setup"),
			"admin/security_roles.php?", 'SA_SECROLES', MENU_SETTINGS);
		$this->add_lapp_function(0, trans("&Display Setup"),
			"admin/display_prefs.php?", 'SA_SETUPDISPLAY', MENU_SETTINGS);
		$this->add_lapp_function(0, trans("Transaction &References"),
			"admin/forms_setup.php?", 'SA_FORMSETUP', MENU_SETTINGS);
		$this->add_rapp_function(0, trans("&Taxes"),
			"taxes/tax_types.php?", 'SA_TAXRATES', MENU_MAINTENANCE);
		$this->add_rapp_function(0, trans("Tax &Groups"),
			"taxes/tax_groups.php?", 'SA_TAXGROUPS', MENU_MAINTENANCE);
		$this->add_rapp_function(0, trans("Item Ta&x Types"),
			"taxes/item_tax_types.php?", 'SA_ITEMTAXTYPE', MENU_MAINTENANCE);
		$this->add_rapp_function(0, trans("System and &General GL Setup"),
			"admin/gl_setup.php?", 'SA_GLSETUP', MENU_SETTINGS);
		$this->add_rapp_function(0, trans("&Fiscal Years"),
			"admin/fiscalyears.php?", 'SA_FISCALYEARS', MENU_MAINTENANCE);
		$this->add_rapp_function(0, trans("&Print Profiles"),
			"admin/print_profiles.php?", 'SA_PRINTPROFILE', MENU_MAINTENANCE);

		$this->add_module(trans("Miscellaneous"));
		$this->add_lapp_function(1, trans("Pa&yment Terms"),
			"admin/payment_terms.php?", 'SA_PAYTERMS', MENU_MAINTENANCE);
		$this->add_lapp_function(1, trans("Shi&pping Company"),
			"admin/shipping_companies.php?", 'SA_SHIPPING', MENU_MAINTENANCE);
		$this->add_rapp_function(1, trans("&Points of Sale"),
			"sales/manage/sales_points.php?", 'SA_POSSETUP', MENU_MAINTENANCE);
		$this->add_rapp_function(1, trans("&Printers"),
			"admin/printers.php?", 'SA_PRINTERS', MENU_MAINTENANCE);
		$this->add_rapp_function(1, trans("Contact &Categories"),
			"admin/crm_categories.php?", 'SA_CRMCATEGORY', MENU_MAINTENANCE);

		$this->add_module(trans("Maintenance"));
		$this->add_lapp_function(2, trans("&Void a Transaction"),
			"admin/void_transaction.php?", 'SA_VOIDTRANSACTION', MENU_MAINTENANCE);
		$this->add_lapp_function(2, trans("View or &Print Transactions"),
			"admin/view_print_transaction.php?", 'SA_VIEWPRINTTRANSACTION', MENU_MAINTENANCE);
		$this->add_lapp_function(2, trans("&Attach Documents"),
			"admin/attachments.php?filterType=20", 'SA_ATTACHDOCUMENT', MENU_MAINTENANCE);
		$this->add_lapp_function(2, trans("System &Diagnostics"),
			"admin/system_diagnostics.php?", 'SA_SOFTWAREUPGRADE', MENU_SYSTEM);

		$this->add_rapp_function(2, trans("&Backup and Restore"),
			"admin/backups.php?", 'SA_BACKUP', MENU_SYSTEM);
//		$this->add_rapp_function(2, trans("Create/Update &Companies"),
//			"admin/create_coy.php?", 'SA_CREATECOMPANY', MENU_UPDATE);
//		$this->add_rapp_function(2, trans("Install/Update &Languages"),
//			"admin/inst_lang.php?", 'SA_CREATELANGUAGE', MENU_UPDATE);
		$this->add_rapp_function(2, trans("Install/Activate &Extensions"),
			"admin/inst_module.php?", 'SA_CREATEMODULES', MENU_UPDATE);
//		$this->add_rapp_function(2, trans("Install/Activate &Themes"),
//			"admin/inst_theme.php?", 'SA_CREATEMODULES', MENU_UPDATE);
//		$this->add_rapp_function(2, trans("Install/Activate &Chart of Accounts"),
//			"admin/inst_chart.php?", 'SA_CREATEMODULES', MENU_UPDATE);
//		$this->add_rapp_function(2, trans("Software &Upgrade"),
//			"admin/inst_upgrade.php?", 'SA_SOFTWAREUPGRADE', MENU_UPDATE);
//
//		$this->add_extensions();
	}
}


