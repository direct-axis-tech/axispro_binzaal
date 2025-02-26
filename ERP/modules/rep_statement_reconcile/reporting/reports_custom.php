<?php

global $reports, $dim;
			
$reports->addReport(RC_BANKING, "_statement_reconcile", trans('Bank Statement w/&Reconcile'),
	array(	trans('Bank Accounts') => 'BANK_ACCOUNTS',
			trans('Start Date') => 'DATEBEGINM',
			trans('End Date') => 'DATEENDM',
			trans('Comments') => 'TEXTBOX',
			trans('Destination') => 'DESTINATION'));
