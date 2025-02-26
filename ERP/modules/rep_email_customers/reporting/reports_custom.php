<?php

global $reports;

$reports->addReport(RC_CUSTOMER, "_email_customers", trans('Email Customers'),
	array(  trans('Customer') => 'CUSTOMERS_NO_FILTER',
			trans('Email Subject') => 'TEXT',
			trans('Email Body') => 'TEXTBOX',
			trans('email Customers') => 'YES_NO',
			trans('Areas') => 'AREAS',
			trans('Filter Areas') => 'YES_NO',
			trans('Price List') => 'SALESTYPES',
			trans('Filter Price List') => 'YES_NO',
			trans('Salesmen') => 'SALESMEN',
));


