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
class general_ledger_app extends application
{
	function __construct()
	{
		parent::__construct("GL", trans($this->help_context = "Banking and General Ledger"));

		$this->add_module(trans("Transactions"));
		$this->add_lapp_function(0, trans("&Payment Voucher"),
			"gl/gl_bank.php?NewPayment=Yes", 'SA_PAYMENT', MENU_TRANSACTION);
//        $this->add_lapp_function(0, trans("&Payments"),
//            "#", 'SA_PAYMENT', MENU_TRANSACTION);


		$this->add_lapp_function(0, trans("&Receipt Voucher"),
			"gl/gl_bank.php?NewDeposit=Yes", 'SA_DEPOSIT', MENU_TRANSACTION);
//        $this->add_lapp_function(0, trans("&Deposits"),
//            "#", 'SA_DEPOSIT', MENU_TRANSACTION);



        $this->add_lapp_function(0, trans("E-Dirham Recharge"),
            "gl/edirham_recharge.php?", 'SA_BANKTRANSFER', MENU_TRANSACTION);



		$this->add_lapp_function(0, trans("Bank Account &Transfers"),
			"gl/bank_transfer.php?", 'SA_BANKTRANSFER', MENU_TRANSACTION);
//        $this->add_lapp_function(0, trans("Bank Account &Transfers"),
//            "#", 'SA_BANKTRANSFER', MENU_TRANSACTION);

		$this->add_rapp_function(0, trans("&Journal Entry"),
			"gl/gl_journal.php?NewJournal=Yes", 'SA_JOURNALENTRY', MENU_TRANSACTION);
//        $this->add_rapp_function(0, trans("&Journal Entry"),
//            "#", 'SA_JOURNALENTRY', MENU_TRANSACTION);


//		$this->add_rapp_function(0, trans("&Budget Entry"),
//			"gl/gl_budget.php?", 'SA_BUDGETENTRY', MENU_TRANSACTION);
//        $this->add_rapp_function(0, trans("&Budget Entry"),
//            "#", 'SA_BUDGETENTRY', MENU_TRANSACTION);


		$this->add_rapp_function(0, trans("&Manual Bank Reconciliation"),
			"gl/bank_account_reconcile.php?", 'SA_RECONCILE', MENU_TRANSACTION);

        $this->add_rapp_function(0, trans("Reconciliation"),
            "gl/reconciliation.php?", 'SA_RECONCILE', MENU_TRANSACTION);


//		$this->add_rapp_function(0, trans("Revenue / &Costs Accruals"),
//			"gl/accruals.php?", 'SA_ACCRUALS', MENU_TRANSACTION);
        $this->add_rapp_function(0, trans("Revenue / &Costs Accruals"),
            "#", 'SA_ACCRUALS', MENU_TRANSACTION);

		$this->add_module(trans("Inquiries and Reports"));
		$this->add_lapp_function(1, trans("&Journal Inquiry"),
			"gl/inquiry/journal_inquiry.php?", 'SA_GLANALYTIC', MENU_INQUIRY);

        $this->add_lapp_function(1, trans("&Voucher Inquiry"),
            "gl/inquiry/voucher_inquiry.php?", 'SA_GLANALYTIC', MENU_INQUIRY);

		$this->add_lapp_function(1, trans("GL &Inquiry"),
			"gl/inquiry/gl_account_inquiry.php?", 'SA_GLTRANSVIEW', MENU_INQUIRY);
//		$this->add_lapp_function(1, trans("Bank Account &Inquiry"),
//			"gl/inquiry/bank_inquiry.php?", 'SA_BANKTRANSVIEW', MENU_INQUIRY);
		$this->add_lapp_function(1, trans("Ta&x Inquiry"),
			"gl/inquiry/tax_inquiry.php?", 'SA_TAXREP', MENU_INQUIRY);

		$this->add_rapp_function(1, trans("Trial &Balance"),
			"gl/inquiry/gl_trial_balance.php?", 'SA_GLANALYTIC', MENU_INQUIRY);
//        $this->add_rapp_function(1, trans("Trial &Balance"),
//            "#", 'SA_GLANALYTIC', MENU_INQUIRY);


		$this->add_rapp_function(1, trans("Balance &Sheet Drilldown"),
			"gl/inquiry/balance_sheet.php?", 'SA_GLANALYTIC', MENU_INQUIRY);
//        $this->add_rapp_function(1, trans("Balance &Sheet Drilldown"),
//            "#", 'SA_GLANALYTIC', MENU_INQUIRY);


		$this->add_rapp_function(1, trans("&Profit and Loss Drilldown"),
			"gl/inquiry/profit_loss.php?", 'SA_GLANALYTIC', MENU_INQUIRY);
//        $this->add_rapp_function(1, trans("&Profit and Loss Drilldown"),
//            "#", 'SA_GLANALYTIC', MENU_INQUIRY);


		$this->add_rapp_function(1, trans("Banking &Reports"),
			"reporting/reports_main.php?Class=5", 'SA_BANKREP', MENU_REPORT);
		$this->add_rapp_function(1, trans("General Ledger &Reports"),
			"reporting/reports_main.php?Class=6", 'SA_GLREP', MENU_REPORT);

		$this->add_module(trans("Maintenance"));
		$this->add_lapp_function(2, trans("Bank &Accounts"),
			"gl/manage/bank_accounts.php?", 'SA_BANKACCOUNT', MENU_MAINTENANCE);
		$this->add_lapp_function(2, trans("&Quick Entries"),
			"gl/manage/gl_quick_entries.php?", 'SA_QUICKENTRY', MENU_MAINTENANCE);
		$this->add_lapp_function(2, trans("Account &Tags"),
			"admin/tags.php?type=account", 'SA_GLACCOUNTTAGS', MENU_MAINTENANCE);
		$this->add_lapp_function(2, "","");
		$this->add_lapp_function(2, trans("&Currencies"),
			"gl/manage/currencies.php?", 'SA_CURRENCY', MENU_MAINTENANCE);
		$this->add_lapp_function(2, trans("&Exchange Rates"),
			"gl/manage/exchange_rates.php?", 'SA_EXCHANGERATE', MENU_MAINTENANCE);

		$this->add_rapp_function(2, trans("&GL Accounts"),
			"gl/manage/gl_accounts.php?", 'SA_GLACCOUNT', MENU_ENTRY);
		$this->add_rapp_function(2, trans("GL Account &Groups"),
			"gl/manage/gl_account_types.php?", 'SA_GLACCOUNTGROUP', MENU_MAINTENANCE);
		$this->add_rapp_function(2, trans("GL Account &Classes"),
			"gl/manage/gl_account_classes.php?", 'SA_GLACCOUNTCLASS', MENU_MAINTENANCE);
		$this->add_rapp_function(2, trans("&Closing GL Transactions"),
			"gl/manage/close_period.php?", 'SA_GLCLOSE', MENU_MAINTENANCE);
		$this->add_rapp_function(2, trans("&Revaluation of Currency Accounts"),
			"gl/manage/revaluate_currencies.php?", 'SA_EXCHANGERATE', MENU_MAINTENANCE);

		$this->add_extensions();
	}
}


