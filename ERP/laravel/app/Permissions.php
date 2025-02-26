<?php

namespace App;

use ArrayAccess;
use Exception;
use App\PermissionGroups as G;

class Permissions implements ArrayAccess {
    /*
     |=============================================
     | Site administration
     |=============================================
     */

    /** @var string SA_CREATECOMPANY Install/update companies */
    const SA_CREATECOMPANY = 'SA_CREATECOMPANY';

    /** @var string SA_CREATELANGUAGE Install/update languages */
    const SA_CREATELANGUAGE = 'SA_CREATELANGUAGE';

    /** @var string SA_CREATEMODULES Install/upgrade modules */
    const SA_CREATEMODULES = 'SA_CREATEMODULES';

    /** @var string SA_SOFTWAREUPGRADE Software upgrades */
    const SA_SOFTWAREUPGRADE = 'SA_SOFTWAREUPGRADE';

    /** @var string SA_MONITOR_QUEUES  Can monitor queued jobs*/
    const SA_MONITORQUEUES = 'SA_MONITORQUEUES';


    /*
     |=============================================
     | Company setup
     |=============================================
     */

    /** @var string SA_SETUPCOMPANY Company parameters */
    const SA_SETUPCOMPANY = 'SA_SETUPCOMPANY';

    /** @var string SA_SECROLES Access levels edition */
    const SA_SECROLES = 'SA_SECROLES';

    /** @var string SA_USERS Users setup */
    const SA_USERS = 'SA_USERS';

    /** @var string SA_POSSETUP Point of sales definitions */
    const SA_POSSETUP = 'SA_POSSETUP';

    /** @var string SA_PRINTERS Printers configuration */
    const SA_PRINTERS = 'SA_PRINTERS';

    /** @var string SA_PRINTPROFILE Print profiles */
    const SA_PRINTPROFILE = 'SA_PRINTPROFILE';

    /** @var string SA_PAYTERMS Payment terms */
    const SA_PAYTERMS = 'SA_PAYTERMS';

    /** @var string SA_SHIPPING Shipping ways */
    const SA_SHIPPING = 'SA_SHIPPING';

    /** @var string SA_CRSTATUS Credit status definitions changes */
    const SA_CRSTATUS = 'SA_CRSTATUS';

    /** @var string SA_INVENTORYLOCATION Inventory locations changes */
    const SA_INVENTORYLOCATION = 'SA_INVENTORYLOCATION';

    /** @var string SA_INVENTORYMOVETYPE Inventory movement types */
    const SA_INVENTORYMOVETYPE = 'SA_INVENTORYMOVETYPE';

    /** @var string SA_WORKCENTRES Manufacture work centres */
    const SA_WORKCENTRES = 'SA_WORKCENTRES';

    /** @var string SA_FORMSETUP Forms setup */
    const SA_FORMSETUP = 'SA_FORMSETUP';

    /** @var string SA_CRMCATEGORY Contact categories */
    const SA_CRMCATEGORY = 'SA_CRMCATEGORY';

    /** @var string SA_LIMITEDROLEASSIGN Can only assign less previlaged roles */
    const SA_LIMITEDROLEASSIGN = 'SA_LIMITEDROLEASSIGN';

    /** @var string SA_ENTITY_GROUP Can Add/Manage workflow related groups */
    const SA_ENTITY_GROUP = 'SA_ENTITY_GROUP';

    /*
     |=============================================
     | Common and special functions
     |=============================================
     */

    /** @var string SA_VOIDTRANSACTION Voiding transactions */
    const SA_VOIDTRANSACTION = 'SA_VOIDTRANSACTION';

    /** @var string SA_BACKUP Database backup/restore */
    const SA_BACKUP = 'SA_BACKUP';

    /** @var string SA_VIEWPRINTTRANSACTION Common view/print transactions interface */
    const SA_VIEWPRINTTRANSACTION = 'SA_VIEWPRINTTRANSACTION';

    /** @var string SA_ATTACHDOCUMENT Attaching documents */
    const SA_ATTACHDOCUMENT = 'SA_ATTACHDOCUMENT';

    /** @var string SA_SETUPDISPLAY Display preferences */
    const SA_SETUPDISPLAY = 'SA_SETUPDISPLAY';

    /** @var string SA_CHGPASSWD Password changes */
    const SA_CHGPASSWD = 'SA_CHGPASSWD';

    /** @var string SA_EDITOTHERSTRANS Edit other users transactions */
    const SA_EDITOTHERSTRANS = 'SA_EDITOTHERSTRANS';

    /** @var string SA_VOIDEDTRANSACTIONS Voided Transaction Report */
    const SA_VOIDEDTRANSACTIONS = 'SA_VOIDEDTRANSACTIONS';

    /** @var string SA_AUTOFETCHREPORT Auto Fetch Report */
    const SA_AUTOFETCHREPORT = 'SA_AUTOFETCHREPORT';

    /** @var string OPEN Open for all without restrictions */
    const OPEN = 'SA_OPEN';

    /** @var string ALLOW Allow for this user but not open in general */
    const ALLOWED = 'SA_ALLOW';

    /** @var string DENIED Denied for all without exception */
    const DENIED = 'SA_DENIED';

    /**
     * @var string SA_REP Reports and Analysis
     *
     * **Note:** This Access Level (SA_REP) is only for the UI.
	 * The actual permission cannot be set because,
	 * every pdf/excel report goes through the reports_main.php
     */
    const SA_REP = 'SA_REP';

    /** @var string SA_MGMTREP Management Report */
    const SA_MGMTREP = 'SA_MGMTREP';

    /** @var string SA_ACL_LIST Access Control List */
    const SA_ACL_LIST = 'SA_ACL_LIST';

    /** @var string SA_USERACTIVITY Log - User Activity */
    const SA_USERACTIVITY = 'SA_USERACTIVITY';

    /** @var string SA_MANAGE_WORKFLOW Add/Manage Workflows */
    const SA_MANAGE_WORKFLOW = 'SA_MANAGE_WORKFLOW';

    /** @var string SA_MANAGE_TASKS Manage the tasks */
    const SA_MANAGE_TASKS = 'SA_MANAGE_TASKS';

    /** @var string SA_MANAGE_TASKS Manage the tasks of everybody */
    const SA_MANAGE_TASKS_ALL = 'SA_MANAGE_TASKS_ALL';
    
    /** @var string SA_MANAGE_OWN_TASKS Can Approve/Reject own Tasks|Requests */
    const SA_MANAGE_OWN_TASKS = 'SA_MANAGE_TASKS_ALL_OWN';
    
    /** @var string SA_CANCEL_OTHERS_TASK Can cancel Tasks|Requests initiated by others */
    const SA_CANCEL_OTHERS_TASK = 'SA_CANCEL_OTHERS_TASK';

    /** @var string SA_MANAGE_DOCUMENT_TYPE Manage document types  */
    const SA_MANAGE_DOCUMENT_TYPE = 'SA_MANAGE_DOCUMENT_TYPE';

    /** @var string SA_MANAGE_GROUP_MEMBERS Manage System Reserved Group Members  */
    const SA_MANAGE_GROUP_MEMBERS = 'SA_MANAGE_GROUP_MEMBERS';

    /** @var string SA_VIEW_ONLY_TASK Can Only View Task|Requests */
    const SA_VIEW_ONLY_TASK = 'SA_VIEW_ONLY_TASK';
    

    /*
     |=============================================
     | Sales - configurations
     |=============================================
     */

    /** @var string SA_SALESTYPES Sales types */
    const SA_SALESTYPES = 'SA_SALESTYPES';

    /** @var string SA_SALESPRICE Sales prices edition */
    const SA_SALESPRICE = 'SA_SALESPRICE';

    /** @var string SA_SALESMAN Sales staff maintenance */
    const SA_SALESMAN = 'SA_SALESMAN';

    /** @var string SA_SALESAREA Sales areas maintenance */
    const SA_SALESAREA = 'SA_SALESAREA';

    /** @var string SA_SALESGROUP Sales groups changes */
    const SA_SALESGROUP = 'SA_SALESGROUP';

    /** @var string SA_STEMPLATE Sales templates */
    const SA_STEMPLATE = 'SA_STEMPLATE';

    /** @var string SA_SRECURRENT Recurrent invoices definitions */
    const SA_SRECURRENT = 'SA_SRECURRENT';

    /** @var string SA_CUSTDISCOUNT Items and Discount settings for customer */
    const SA_CUSTDISCOUNT = 'SA_CUSTDISCOUNT';

    /** @var string SA_UPDATECRLMT Credit Limit settings for customer */
    const SA_UPDATECRLMT = 'SA_UPDATECRLMT';


    /*
     |=============================================
     | Sales - features
     |=============================================
     */

    /** @var string SA_SALESTRANSVIEW Sales transactions view */
    const SA_SALESTRANSVIEW = 'SA_SALESTRANSVIEW';

    /** @var string SA_CUSTOMER Sales customer and branches changes */
    const SA_CUSTOMER = 'SA_CUSTOMER';

    /** @var string SA_SALESORDER Sales orders edition */
    const SA_SALESORDER = 'SA_SALESORDER';

    /** @var string SA_SALESDELIVERY Sales deliveries edition */
    const SA_SALESDELIVERY = 'SA_SALESDELIVERY';

    /** @var string SA_SALESINVOICE Sales invoices edition */
    const SA_SALESINVOICE = 'SA_SALESINVOICE';

    /** @var string SA_SALESCREDITINV Sales credit notes against invoice */
    const SA_SALESCREDITINV = 'SA_SALESCREDITINV';

    /** @var string SA_SALESCREDIT Sales freehand credit notes */
    const SA_SALESCREDIT = 'SA_SALESCREDIT';

    /** @var string SA_PRINTSALESCREDIT Print sales credit notes */
    const SA_PRINTSALESCREDIT = 'SA_PRINTSALESCREDIT';

    /** @var string SA_SALESPAYMNT Customer payments entry */
    const SA_SALESPAYMNT = 'SA_SALESPAYMNT';

    /** @var string SA_SALESALLOC Customer payments allocation */
    const SA_SALESALLOC = 'SA_SALESALLOC';

    /** @var string SA_SALESQUOTE Sales quotations */
    const SA_SALESQUOTE = 'SA_SALESQUOTE';

    /** @var string SA_CUSTRCPTVCHR Customer reciept voucher */
    const SA_CUSTRCPTVCHR = 'SA_CUSTRCPTVCHR';

    /** @var string SA_SERVICE_REQUEST Service Request */
    const SA_SERVICE_REQUEST = 'SA_SERVICE_REQUEST';

    /** @var string SA_SALESINVOICEDATEMOD Transaction date modification when invoicing */
    const SA_SALESINVOICEDATEMOD = 'SA_SALESINVOICEDATEMOD';

    /** @var string SA_MKINVFRMSRVREQ Make Invoice from service request */
    const SA_MKINVFRMSRVREQ = 'SA_MKINVFRMSRVREQ';

    /** @var string SA_SRVREQLSTALL See all service request list */
    const SA_SRVREQLSTALL = 'SA_SRVREQLSTALL';

    /** @var string SA_MANAGEINV Manage Invoices */
    const SA_MANAGEINV = 'SA_MANAGEINV';

    /** @var string SA_EDITSALESINVOICE Edit Sales Invoices */
    const SA_EDITSALESINVOICE = 'SA_EDITSALESINVOICE';

    /** @var string SA_MANAGEINVDEP Manage Invoices (Department) */
    const SA_MANAGEINVDEP = 'SA_MANAGEINVDEP';

    /** @var string SA_MANAGEINVALL Manage Invoices (All) */
    const SA_MANAGEINVALL = 'SA_MANAGEINVALL';

    /** @var string SA_UPDATEINVOICE Update Sales Invoice */
    const SA_UPDATEINVOICE = 'SA_UPDATEINVOICE';

    /** @var string SA_CUSTWTHDUPDETAIL Can add customers with duplicate mobile number */
    const SA_CUSTWTHDUPDETAIL = 'SA_CUSTWTHDUPDETAIL';

    /** @var string SA_MODSALESPAYMENT Modify Customer Payment */
    const SA_MODSALESPAYMENT = 'SA_MODSALESPAYMENT';

    /** @var string SA_PRINTSALESINV Print Sales Invoice */
    const SA_PRINTSALESINV = 'SA_PRINTSALESINV';

    /** @var string SA_PRINTSALESRCPT Print Customer Reciept */
    const SA_PRINTSALESRCPT = 'SA_PRINTSALESRCPT';

    /** @var string SA_INVWTHOUTTKN Issue Invoice without token */
    const SA_INVWTHOUTTKN = 'SA_INVWTHOUTTKN';

    /** @var string SA_INVWTHOUTSRVRQST Issue Invoice without service request */
    const SA_INVWTHOUTSRVRQST = 'SA_INVWTHOUTSRVRQST';

    /** @var string SA_SUDOEDITTRANSID Forcly update transaction_id even if there is already transaction_id */
    const SA_SUDOEDITTRANSID = 'SA_SUDOEDITTRANSID';

    /** @var string SA_EDITCMMSSNDUSR Edit the commission owned user */
    const SA_EDITCMMSSNDUSR = 'SA_EDITCMMSSNDUSR';

    /** @var string SA_EDITINDIVTRANSDATE Edit the transaction date of individual transactions */
    const SA_EDITINDIVTRANSDATE = 'SA_EDITINDIVTRANSDATE';

    /** @var string SA_SUDOEDITAPPLCTNID Forcly update application_id even if there is already application_id */
    const SA_SUDOEDITAPPLCTNID = 'SA_SUDOEDITAPPLCTNID';
    
    /** @var string SA_SUDOEDITNARTN Forcly update narration even if there is already narration */
    const SA_SUDOEDITNARTN = 'SA_SUDOEDITNARTN';

    /** @var string SA_CUSTPYMNT_ALL Collect customer payments from all allowed departments */
    const SA_CUSTPYMNT_ALWD = 'SA_CUSTPYMNT_ALWD';
    
    /** @var string SA_SENDINVSMS Send invoice through SMS */
    const SA_SENDINVSMS = 'SA_SENDINVSMS';

    /** @var string SA_EDITSERVICEREQ Edit Service Request */
    const SA_EDITSERVICEREQ = 'SA_EDITSERVICEREQ';

    /** @var string SA_PRINTSERVICEREQ Print Service Request */
    const SA_PRINTSERVICEREQ = 'SA_PRINTSERVICEREQ';

    /** @var string SA_SENSA_DELSERVICEREQDINVSMS SA_DELSERVICEREQ */
    const SA_DELSERVICEREQ = 'SA_DELSERVICEREQ';

    /** @var string SA_SRVREQLNITMINV service request line itemwise invoice  */
    const SA_SRVREQLNITMINV = 'SA_SRVREQLNITMINV';
    
    /** @var string SA_APRVCSHDISCOUNT Approve cash discount in cashier dashboard  */
    const SA_APRVCSHDISCOUNT = 'SA_APRVCSHDISCOUNT';

    /** @var string SA_SALESCREDIT Credit note against contract without using flow */
    const SA_NOSALESCREDITFLOW = 'SA_NOSALESCREDITFLOW';

    /** @var string SA_CHANGERETURNINCDAYS change sales return income days */
    const SA_CHANGERETURNINCDAYS = 'SA_CHANGERETURNINCDAYS';

    /** @var string SA_INV_PREPAID_ORDERS Invoice Prepaid Orders */
    const SA_INV_PREPAID_ORDERS = 'SA_INV_PREPAID_ORDERS';

    /** @var string SA_SALESORDER_VIEW Sales Orders View */
    const SA_SALESORDER_VIEW = 'SA_SALESORDER_VIEW';

    /** @var string SA_SALESORDER_VIEW_DEP Sales Orders View Dep*/
    const SA_SALESORDER_VIEW_DEP = 'SA_SALESORDER_VIEW_DEP';

    /** @var string SA_SALESORDER_VIEW_ALL Sales Orders View All*/
    const SA_SALESORDER_VIEW_ALL = 'SA_SALESORDER_VIEW_ALL';
    
    /** @var string SA_SALES_LINE_VIEW Sales Order Lines View (ALL) */
    const SA_SALES_LINE_VIEW = 'SA_SALES_LINE_VIEW';
    
    /** @var string SA_SALES_LINE_VIEW Sales Order Lines View (Own) */
    const SA_SALES_LINE_VIEW_OWN = 'SA_SALES_LINE_VIEW_OWN';
    
    /** @var string SA_SALES_LINE_VIEW Sales Order Lines View (Dep) */
    const SA_SALES_LINE_VIEW_DEP = 'SA_SALES_LINE_VIEW_DEP';

    /** @var string SA_DIRECTINVORDER Direct Invoice + Job Order */
    const SA_DIRECTINVORDER = 'SA_DIRECTINVORDER';

    /** @var string SA_DIRECTDLVRORDER Job Order With Auto Completion */
    const SA_DIRECTDLVRORDER = 'SA_DIRECTDLVRORDER';

    /** @var string SA_DIRECTINVDLVRORDER Direct Invoice + Job Order With Auto Completion */
    const SA_DIRECTINVDLVRORDER = 'SA_DIRECTINVDLVRORDER';

    /** @var string SA_SENDINVEMAIL Send invoice through email */
    const SA_SENDINVEMAIL = 'SA_SENDINVEMAIL';
    
    /** @var string SA_HIDEDISCOUNT Hide discount from invoice screen */
    const SA_HIDEDISCOUNT = 'SA_HIDEDISCOUNT';
    
    /** @var string SA_SALESLNCMPLTWEXP Job Order Transaction Completion With Add Expense */
    const SA_SALESLNCMPLTWEXP = 'SA_SALESLNCMPLTWEXP';
    
    /** @var string SA_SALESLNEXPONLY Job Order Transaction Add Expense Without Completion */
    const SA_SALESLNEXPONLY = 'SA_SALESLNEXPONLY';
    
    /** @var string SA_RCVPMTWITHOUTDIM Cashier dashboard - All option */
    const SA_RCVPMTWITHOUTDIM = 'SA_RCVPMTWITHOUTDIM';

    /** @var string SA_GOVBNKACTEDITABLE Can edit Govt. Bank Act when invoicing */
    const SA_GOVBNKACTEDITABLE = 'SA_GOVBNKACTEDITABLE';

    /*
     |=============================================
     | Sales - reports
     |=============================================
     */

    /** @var string SA_SALESANALYTIC Sales analytical reports */
    const SA_SALESANALYTIC = 'SA_SALESANALYTIC';

    /** @var string SA_SALESBULKREP Sales document bulk reports */
    const SA_SALESBULKREP = 'SA_SALESBULKREP';

    /** @var string SA_PRICEREP Sales prices listing */
    const SA_PRICEREP = 'SA_PRICEREP';

    /** @var string SA_SALESMANREP Sales staff listing */
    const SA_SALESMANREP = 'SA_SALESMANREP';

    /** @var string SA_CUSTBULKREP Customer bulk listing */
    const SA_CUSTBULKREP = 'SA_CUSTBULKREP';

    /** @var string SA_CUSTSTATREP Customer status report */
    const SA_CUSTSTATREP = 'SA_CUSTSTATREP';

    /** @var string SA_CUSTPAYMREP Customer payments report */
    const SA_CUSTPAYMREP = 'SA_CUSTPAYMREP';

    /** @var string SA_SRVREPORT Service Report (Self Only) */
    const SA_SRVREPORT = 'SA_SRVREPORT';

    /** @var string SA_SRVREPORTALL Service Report (All) */
    const SA_SRVREPORTALL = 'SA_SRVREPORTALL';

    /** @var string SA_SRVREQLI Service Request List */
    const SA_SRVREQLI = 'SA_SRVREQLI';

    /** @var string SA_CSHCOLLECTREP Cash Collection (Self Only) */
    const SA_CSHCOLLECTREP = 'SA_CSHCOLLECTREP';

    /** @var string SA_CSHCOLLECTREPALL Cash Collection (All) */
    const SA_CSHCOLLECTREPALL = 'SA_CSHCOLLECTREPALL';

    /** @var string SA_EMPANALYTIC Employee Analytical Reports (Self Only) */
    const SA_EMPANALYTIC = 'SA_EMPANALYTIC';

    /** @var string SA_EMPANALYTICDEP Employee Analytical Reports (Department) */
    const SA_EMPANALYTICDEP = 'SA_EMPANALYTICDEP';

    /** @var string SA_EMPANALYTICALL Employee Analytical Reports (All) */
    const SA_EMPANALYTICALL = 'SA_EMPANALYTICALL';

    /** @var string SA_CUSTANALYTIC Customer Analytical Reports */
    const SA_CUSTANALYTIC = 'SA_CUSTANALYTIC';

    /** @var string SA_CSHCOLLECTREPDEP Cash Collection (Department) */
    const SA_CSHCOLLECTREPDEP = 'SA_CSHCOLLECTREPDEP';

    /** @var string SA_EMPCOMMAAD Employee Commission Adheed (Self Only) */
    const SA_EMPCOMMAAD = 'SA_EMPCOMMAAD';

    /** @var string SA_EMPCOMMAADDEP Employee Commission Adheed (Department) */
    const SA_EMPCOMMAADDEP = 'SA_EMPCOMMAADDEP';

    /** @var string SA_EMPCOMMAADALL Employee Commission Adheed (All) */
    const SA_EMPCOMMAADALL = 'SA_EMPCOMMAADALL';

    /** @var string SA_CRSALESREP_OWN Credit Invoice Report (Own) */
    const SA_CRSALESREP_OWN = 'SA_CRSALESREP_OWN';

    /** @var string SA_CRSALESREP_DEP Credit Invoice Report (Dep) */
    const SA_CRSALESREP_DEP = 'SA_CRSALESREP_DEP';

    /** @var string SA_CRSALESREP_ALL Credit Invoice Report (All) */
    const SA_CRSALESREP_ALL = 'SA_CRSALESREP_ALL';

    /** @var string SA_SRVREPORTDEP Service Report (Department) */
    const SA_SRVREPORTDEP = 'SA_SRVREPORTDEP';

    /** @var string SA_CUSTDETREP Customer details report */
    const SA_CUSTDETREP = 'SA_CUSTDETREP';

    /** @var string SA_STAFMISTAKREP Staff mistakes report */
    const SA_STAFMISTAKREP = 'SA_STAFMISTAKREP';

    /** @var string SA_SERVICETRANSREP_OWN Management Report - Service Report (Own transactions) */
    const SA_SERVICETRANSREP_OWN = 'SA_SERVICTRANSEREP_OWN';

    /** @var string SA_SERVICETRANSREP_ALL Management Report - Service Report (All transactions) */
    const SA_SERVICETRANSREP_ALL = 'SA_SERVICETRANSREP_ALL';

    /** @var string SA_INVOICEREP Management Report - Invoice Report */
    const SA_INVOICEREP = 'SA_INVOICEREP';

    /** @var string SA_INVOICEREPORT Invoice Report */
    const SA_INVOICEREPORT = 'SA_INVOICEREPORT';

    /** @var string SA_INVOICEPMTREP Management Report - Invoice Payment Report */
    const SA_INVOICEPMTREP = 'SA_INVOICEPMTREP';

    /** @var string SA_SERVICEMSTRREP Management Report - Service Master Report */
    const SA_SERVICEMSTRREP = 'SA_SERVICEMSTRREP';
    
    /** @var string SA_CTGRYSALESREP Category wise sales report */
    const SA_CTGRYSALESREP = 'SA_CTGRYSALESREP';
    
    /** @var string SA_CTGRYSALESREP_ALL Category wise sales report of everybody */
    const SA_CTGRYSALESREP_ALL = 'SA_CTGRYSALESREP_ALL';

    /** @var string SA_CUSTWISEREPPARTICULARS Customer wise sales report particulars */
    const SA_CUSTWISEREPPARTICULARS = 'SA_CUSTWISEREPPARTICULARS';

    /** @var string SA_CUSTWISEALLREP Customer wise sales report all user */
    const SA_CUSTWISEALLREP = 'SA_CUSTWISEALLREP';

    /** @var string SA_CUSTWISEOWNREP Customer wise sales report own user */
    const SA_CUSTWISEOWNREP = 'SA_CUSTWISEOWNREP';

    /** @var string SA_CUSREP Show Custom Reports */
    const SA_CUSREP = 'SA_CUSREP';

    /** @var string SA_CUSREP Custom Reports All */
    const SA_CUSREP_ALL = 'SA_CUSREP_ALL';

    /** @var string SA_CUSREP Custom Reports Department */
    const SA_CUSREP_DEP = 'SA_CUSREP_DEP';

    /** @var string SA_SHOWSERVREP Show Service Reports */
    const SA_SHOWSERVREP = 'SA_SHOWSERVREP';


    /*
     |=============================================
     | Purchase
     |=============================================
     */

    /** @var string SA_PURCHASEPRICING Purchase price changes **/
    const SA_PURCHASEPRICING = 'SA_PURCHASEPRICING';

    /** @var string SA_SUPPTRANSVIEW Supplier transactions view */
    const SA_SUPPTRANSVIEW = 'SA_SUPPTRANSVIEW';

    /** @var string SA_SUPPLIER Suppliers changes */
    const SA_SUPPLIER = 'SA_SUPPLIER';

    /** @var string SA_PURCHASEORDER Purchase order entry */
    const SA_PURCHASEORDER = 'SA_PURCHASEORDER';

    /** @var string SA_SUPPDISC SUPPLIER DISCOUNT AND COMMISSION */
    const SA_SUPPDISC = 'SA_SUPPDISC';

    /** @var string SA_GRN Purchase receive */
    const SA_GRN = 'SA_GRN';

    /** @var string SA_SUPPLIERINVOICE Supplier invoices */
    const SA_SUPPLIERINVOICE = 'SA_SUPPLIERINVOICE';

    /** @var string SA_GRNDELETE Deleting GRN items during invoice entry */
    const SA_GRNDELETE = 'SA_GRNDELETE';

    /** @var string SA_SUPPLIERCREDIT Supplier credit notes */
    const SA_SUPPLIERCREDIT = 'SA_SUPPLIERCREDIT';

    /** @var string SA_SUPPLIERPAYMNT Supplier payments */
    const SA_SUPPLIERPAYMNT = 'SA_SUPPLIERPAYMNT';

    /** @var string SA_SUPPLIERALLOC Supplier payments allocations */
    const SA_SUPPLIERALLOC = 'SA_SUPPLIERALLOC';

    /** @var string SA_SUPPLIERANALYTIC Supplier analytical reports */
    const SA_SUPPLIERANALYTIC = 'SA_SUPPLIERANALYTIC';

    /** @var string SA_SUPPBULKREP Supplier document bulk reports */
    const SA_SUPPBULKREP = 'SA_SUPPBULKREP';

    /** @var string SA_SUPPPAYMREP Supplier payments report */
    const SA_SUPPPAYMREP = 'SA_SUPPPAYMREP';

    /** @var string SA_ITEM_PURCHASE add purchase items */
    const SA_ITEM_PURCHASE = 'SA_ITEM_PURCHASE';

    /** @var string SA_ITEM_DELETE delete purchase items */
    const SA_ITEM_DELETE = 'SA_ITEM_DELETE';


    /*
     |=============================================
     | Inventory
     |=============================================
     */

    /** @var string SA_ITEM Stock items add/edit */
    const SA_ITEM = 'SA_ITEM';

    /** @var string SA_SALESKIT Sales kits */
    const SA_SALESKIT = 'SA_SALESKIT';

    /** @var string SA_ITEMCATEGORY Item categories */
    const SA_ITEMCATEGORY = 'SA_ITEMCATEGORY';

    /** @var string SA_UOM Units of measure */
    const SA_UOM = 'SA_UOM';

    /** @var string SA_CTGRYGROUP Item category groups */
    const SA_CTGRYGROUP = 'SA_CTGRYGROUP';

    /** @var string SA_ITEMSSTATVIEW Stock status view */
    const SA_ITEMSSTATVIEW = 'SA_ITEMSSTATVIEW';

    /** @var string SA_ITEMSTRANSVIEW Stock transactions view */
    const SA_ITEMSTRANSVIEW = 'SA_ITEMSTRANSVIEW';

    /** @var string SA_FORITEMCODE Foreign item codes entry */
    const SA_FORITEMCODE = 'SA_FORITEMCODE';

    /** @var string SA_LOCATIONTRANSFER Inventory location transfers */
    const SA_LOCATIONTRANSFER = 'SA_LOCATIONTRANSFER';

    /** @var string SA_INVENTORYADJUSTMENT Inventory adjustments */
    const SA_INVENTORYADJUSTMENT = 'SA_INVENTORYADJUSTMENT';

    /** @var string SA_STOCK_RETURN Add/Manage Stock Return */
    const SA_STOCK_RETURN = 'SA_STOCK_RETURN';

    /** @var string SA_STOCK_REPLACEMENT Add/Manage Stock Replacement */
    const SA_STOCK_REPLACEMENT = 'SA_STOCK_REPLACEMENT';

    /** @var string SA_REORDER Reorder levels */
    const SA_REORDER = 'SA_REORDER';

    /** @var string SA_ITEMSANALYTIC Items analytical reports and inquiries */
    const SA_ITEMSANALYTIC = 'SA_ITEMSANALYTIC';

    /** @var string SA_ITEMSVALREP Inventory valuation report */
    const SA_ITEMSVALREP = 'SA_ITEMSVALREP';

    /** @var string SA_SALES_ITEM_SEARCH Sales Stock items search */
    const SA_SALES_ITEM_SEARCH = 'SA_SALES_ITEM_SEARCH';

    /*
     |=============================================
     | Fixed Assets
     |=============================================
     */

    /** @var string SA_ASSET Fixed Asset items add/edit */
    const SA_ASSET = 'SA_ASSET';

    /** @var string SA_ASSET_IMPORT Fixed Asset items import */
    const SA_ASSET_IMPORT = 'SA_ASSET_IMPORT';

    /** @var string SA_ASSETCATEGORY Fixed Asset categories */
    const SA_ASSETCATEGORY = 'SA_ASSETCATEGORY';

    /** @var string SA_ASSETCLASS Fixed Asset classes */
    const SA_ASSETCLASS = 'SA_ASSETCLASS';

    /** @var string SA_ASSETSTRANSVIEW Fixed Asset transactions view */
    const SA_ASSETSTRANSVIEW = 'SA_ASSETSTRANSVIEW';

    /** @var string SA_ASSETTRANSFER Fixed Asset location transfers */
    const SA_ASSETTRANSFER = 'SA_ASSETTRANSFER';

    /** @var string SA_ASSETDISPOSAL Fixed Asset disposals */
    const SA_ASSETDISPOSAL = 'SA_ASSETDISPOSAL';

    /** @var string SA_DEPRECIATION Depreciation */
    const SA_DEPRECIATION = 'SA_DEPRECIATION';

    /** @var string SA_ASSETSANALYTIC Fixed Asset analytical reports and inquiries */
    const SA_ASSETSANALYTIC = 'SA_ASSETSANALYTIC';

    /** @var string SA_DEPRECIATION_CATEGORY Depreciation Category */
    const SA_DEPRECIATION_CATEGORY = 'SA_DEPRECIATION_CATEGORY';

    /** @var string SA_ASSETALLOCATION Asset Allocation & Deallocation */
    const SA_ASSETALLOCATION = 'SA_ASSETALLOCATION';


    /*
     |=============================================
     | Manufacturing
     |=============================================
     */

    /** @var string SA_BOM Bill of Materials */
    const SA_BOM = 'SA_BOM';

    /** @var string SA_MANUFTRANSVIEW Manufacturing operations view */
    const SA_MANUFTRANSVIEW = 'SA_MANUFTRANSVIEW';

    /** @var string SA_WORKORDERENTRY Work order entry */
    const SA_WORKORDERENTRY = 'SA_WORKORDERENTRY';

    /** @var string SA_MANUFISSUE Material issues entry */
    const SA_MANUFISSUE = 'SA_MANUFISSUE';

    /** @var string SA_MANUFRECEIVE Final product receive */
    const SA_MANUFRECEIVE = 'SA_MANUFRECEIVE';

    /** @var string SA_MANUFRELEASE Work order releases */
    const SA_MANUFRELEASE = 'SA_MANUFRELEASE';

    /** @var string SA_WORKORDERANALYTIC Work order analytical reports and inquiries */
    const SA_WORKORDERANALYTIC = 'SA_WORKORDERANALYTIC';

    /** @var string SA_WORKORDERCOST Manufacturing cost inquiry */
    const SA_WORKORDERCOST = 'SA_WORKORDERCOST';

    /** @var string SA_MANUFBULKREP Work order bulk reports */
    const SA_MANUFBULKREP = 'SA_MANUFBULKREP';

    /** @var string SA_BOMREP Bill of materials reports */
    const SA_BOMREP = 'SA_BOMREP';


    /*
     |=============================================
     | Dimensions
     |=============================================
     */

    /** @var string SA_DIMTAGS Dimension tags */
    const SA_DIMTAGS = 'SA_DIMTAGS';

    /** @var string SA_DIMTRANSVIEW Dimension view */
    const SA_DIMTRANSVIEW = 'SA_DIMTRANSVIEW';

    /** @var string SA_DIMENSION Dimension entry */
    const SA_DIMENSION = 'SA_DIMENSION';

    /** @var string SA_DIMENSIONREP Dimension reports */
    const SA_DIMENSIONREP = 'SA_DIMENSIONREP';


    /*
     |=============================================
     | General Accounts - configurations
     |=============================================
     */

    /** @var string SA_ITEMTAXTYPE Item tax type definitions */
    const SA_ITEMTAXTYPE = 'SA_ITEMTAXTYPE';

    /** @var string SA_GLACCOUNT GL accounts edition */
    const SA_GLACCOUNT = 'SA_GLACCOUNT';

    /** @var string SA_GLACCOUNTGROUP GL account groups */
    const SA_GLACCOUNTGROUP = 'SA_GLACCOUNTGROUP';

    /** @var string SA_GLACCOUNTCLASS GL account classes */
    const SA_GLACCOUNTCLASS = 'SA_GLACCOUNTCLASS';

    /** @var string SA_QUICKENTRY Quick GL entry definitions */
    const SA_QUICKENTRY = 'SA_QUICKENTRY';

    /** @var string SA_CURRENCY Currencies */
    const SA_CURRENCY = 'SA_CURRENCY';

    /** @var string SA_BANKACCOUNT Bank accounts */
    const SA_BANKACCOUNT = 'SA_BANKACCOUNT';

    /** @var string SA_TAXRATES Tax rates */
    const SA_TAXRATES = 'SA_TAXRATES';

    /** @var string SA_TAXGROUPS Tax groups */
    const SA_TAXGROUPS = 'SA_TAXGROUPS';

    /** @var string SA_FISCALYEARS Fiscal years maintenance */
    const SA_FISCALYEARS = 'SA_FISCALYEARS';

    /** @var string SA_GLSETUP Company GL setup */
    const SA_GLSETUP = 'SA_GLSETUP';

    /** @var string SA_GLACCOUNTTAGS GL Account tags */
    const SA_GLACCOUNTTAGS = 'SA_GLACCOUNTTAGS';

    /** @var string SA_GLCLOSE Closing GL transactions */
    const SA_GLCLOSE = 'SA_GLCLOSE';

    /** @var string SA_GLREOPEN Reopening GL transactions */
    const SA_GLREOPEN = 'SA_GLREOPEN';

    /** @var string SA_MULTIFISCALYEARS Allow entry on non closed Fiscal years */
    const SA_MULTIFISCALYEARS = 'SA_MULTIFISCALYEARS';


    /*
     |=============================================
     | General Accounts - features
     |=============================================
     */

    /** @var string SA_BANKTRANSVIEW Bank transactions view */
    const SA_BANKTRANSVIEW = 'SA_BANKTRANSVIEW';

    /** @var string SA_GLTRANSVIEW GL postings view */
    const SA_GLTRANSVIEW = 'SA_GLTRANSVIEW';

    /** @var string SA_EXCHANGERATE Exchange rate table changes */
    const SA_EXCHANGERATE = 'SA_EXCHANGERATE';

    /** @var string SA_PAYMENT Bank payments */
    const SA_PAYMENT = 'SA_PAYMENT';

    /** @var string SA_DEPOSIT Bank deposits */
    const SA_DEPOSIT = 'SA_DEPOSIT';

    /** @var string SA_BANKTRANSFER Bank account transfers */
    const SA_BANKTRANSFER = 'SA_BANKTRANSFER';

    /** @var string SA_RECONCILE Bank reconciliation */
    const SA_RECONCILE = 'SA_RECONCILE';

    /** @var string SA_JOURNALENTRY Manual journal entries */
    const SA_JOURNALENTRY = 'SA_JOURNALENTRY';

    /** @var string SA_BANKJOURNAL Journal entries to bank related accounts */
    const SA_BANKJOURNAL = 'SA_BANKJOURNAL';

    /** @var string SA_BUDGETENTRY Budget edition */
    const SA_BUDGETENTRY = 'SA_BUDGETENTRY';

    /** @var string SA_STANDARDCOST Item standard costs */
    const SA_STANDARDCOST = 'SA_STANDARDCOST';

    /** @var string SA_ACCRUALS Revenue / Cost Accruals */
    const SA_ACCRUALS = 'SA_ACCRUALS';

    /** @var string SA_LEAVE_ACCRUALS Post leave accruals of employees */
    const SA_LEAVE_ACCRUALS = 'SA_LEAVE_ACCRUALS';

    /** @var string SA_GRATUITY_ACCRUALS Post gratuity accruals of employees */
    const SA_GRATUITY_ACCRUALS = 'SA_GRATUITY_ACCRUALS';

    /** @var string SA_ALLOWJVPREVDATE Allow journal entry on previous date */
    const SA_ALLOWJVPREVDATE = 'SA_ALLOWJVPREVDATE';



    /*
     |=============================================
     | General Accounts - reports
     |=============================================
     */

    /** @var string SA_GLANALYTIC GL analytical reports and inquiries */
    const SA_GLANALYTIC = 'SA_GLANALYTIC';

    /** @var string SA_TAXREP Tax reports and inquiries */
    const SA_TAXREP = 'SA_TAXREP';

    /** @var string SA_BANKREP Bank reports and inquiries */
    const SA_BANKREP = 'SA_BANKREP';

    /** @var string SA_GLREP GL reports and inquiries */
    const SA_GLREP = 'SA_GLREP';

    /** @var string SA_YBCDLYREP YBC - Consolidated Daily Report */
    const SA_YBCDLYREP = 'SA_YBCDLYREP';

    /** @var string SA_PNLREP Profit and Loss Statement */
    const SA_PNLREP = 'SA_PNLREP';

    /** @var string SA_SUBLEDSUMMREP Sub-ledger Summary details Report */
    const SA_SUBLEDSUMMREP = 'SA_SUBLEDSUMMREP';


    /*
     |=============================================
     | HRM - features
     |=============================================
     */
    /** @var string HRM_VIEWTIMESHEET_OWN View/Export Employees' Timesheet - (Own) */
    const HRM_VIEWTIMESHEET_OWN = 'HRM_VIEWTIMESHEET_OWN';

    /** @var string HRM_VIEWTIMESHEET_DEP View/Export Employees' Timesheet - (Dep) */
    const HRM_VIEWTIMESHEET_DEP = 'HRM_VIEWTIMESHEET_DEP';

    /** @var string HRM_VIEWTIMESHEET_ALL View/Export Employees' Timesheet - (All) */
    const HRM_VIEWTIMESHEET_ALL = 'HRM_VIEWTIMESHEET_ALL';

    /** @var string HRM_EDITTIMESHEET_OWN Edit Employees' Timesheet - (Own) */
    const HRM_EDITTIMESHEET_OWN = 'HRM_EDITTIMESHEET_OWN';

    /** @var string HRM_EDITTIMESHEET_DEP Edit Employees' Timesheet - (Dep) */
    const HRM_EDITTIMESHEET_DEP = 'HRM_EDITTIMESHEET_DEP';

    /** @var string HRM_EDITTIMESHEET_ALL Edit Employees' Timesheet - (All) */
    const HRM_EDITTIMESHEET_ALL = 'HRM_EDITTIMESHEET_ALL';

    /** @var string HRM_PAYROLL Process Payroll */
    const HRM_PAYROLL = 'HRM_PAYROLL';

    /** @var string HRM_ADDSHIFT_OWN Add Employees' Shifts - (Own) */
    const HRM_ADDSHIFT_OWN = 'HRM_ADDSHIFT_OWN';

    /** @var string HRM_ADDSHIFT_DEP Add Employees' Shifts - (Dep) */
    const HRM_ADDSHIFT_DEP = 'HRM_ADDSHIFT_DEP';

    /** @var string HRM_ADDSHIFT_ALL Add Employees' Shifts - (All) */
    const HRM_ADDSHIFT_ALL = 'HRM_ADDSHIFT_ALL';

    /** @var string HRM_ADD_EMPLOYEE Add New Employee */
    const HRM_ADD_EMPLOYEE = 'HRM_ADD_EMPLOYEE';

    /** @var string HRM_ADD_EMP_LEAVE Add/Apply Own Leave with workflow */
    const HRM_ADD_EMP_LEAVE = 'HRM_ADD_EMP_LEAVE';

    /** @var string HRM_BULK_EMPLOYEE_UPLOAD Upload employees in bulk from excel/csv */
    const HRM_BULK_EMPLOYEE_UPLOAD = 'HRM_BULK_EMPLOYEE_UPLOAD';

    /** @var string HRM_EDIT_EMPLOYEE Modify Basic Employee details */
    const HRM_EDIT_EMPLOYEE = 'HRM_EDIT_EMPLOYEE';

    /** @var string HRM_EMP_SALARY Increment/Decrement Employee Salary */
    const HRM_EMP_SALARY = 'HRM_EMP_SALARY';

    /** @var string HRM_REDO_PAYSLIP Redo a Processed Payslip */
    const HRM_REDO_PAYSLIP = 'HRM_REDO_PAYSLIP';

    /** @var string HRM_UPD_STFMSTK_PSLP Update staff mistake while processing payslip */
    const HRM_UPD_STFMSTK_PSLP = 'HRM_UPD_STFMSTK_PSLP';

    /** @var string HRM_UPD_COMMISN_PSLP Update Commission while processing payslip */
    const HRM_UPD_COMMISN_PSLP = 'HRM_UPD_COMMISN_PSLP';

    /** @var string HRM_EDITSHIFT Edit Employees' Shift */
    const HRM_EDITSHIFT = 'HRM_EDITSHIFT';

    /** @var string HRM_FINALIZE_PAYROLL Process Finalized Payroll */
    const HRM_FINALIZE_PAYROLL = 'HRM_FINALIZE_PAYROLL';

    /** @var string HRM_JOB_UPDATE Add Employee's Job Update */
    const HRM_JOB_UPDATE = 'HRM_JOB_UPDATE';

    /** @var string HRM_ADD_EMP_CANCELATION Add Employee's Cancelation */
    const HRM_ADD_EMP_CANCELATION = 'HRM_ADD_EMP_CANCELATION';

    /** @var string HRM_HOLD_EMP_SALARY Hold Employee's Salary */
    const HRM_HOLD_EMP_SALARY = 'HRM_HOLD_EMP_SALARY';

    /** @var string HRM_HOLDED_EMP_SALARY View Holded Employee's Salary */
    const HRM_HOLDED_EMP_SALARY = 'HRM_HOLDED_EMP_SALARY';

    /** @var string HR_SYNC_ATTENDANCE Can syncronize the attendance */
    const HRM_SYNCATTD = 'HR_SYNC_ATTENDANCE';

    /** @var string HRM_UPLOAD_DOC Can upload employee's documents */
    const HRM_UPLOAD_DOC = 'HRM_UPLOAD_DOC';
    
     /** @var string HRM_MANAGE_DOC Can view employee's documents */
     const HRM_MANAGE_DOC = 'HRM_MANAGE_DOC';

     /** @var string HRM_MANAGE_DOC_OWN Can view employee's own documents */
     const HRM_MANAGE_DOC_OWN = 'HRM_MANAGE_DOC_OWN';

    /** @var string HRM_EDIT_DOC Can edit employee's documents */
    const HRM_EDIT_DOC = 'HRM_EDIT_DOC';
    
    /** @var string HRM_DELETE_DOC Can download employee's documents */
    const HRM_DELETE_DOC = 'HRM_DELETE_DOC';

    /** @var string HRM_ADD_EMP_LEAVE_ALL can apply leave for all employees with workflow*/
    const HRM_ADD_EMP_LEAVE_ALL = 'HRM_ADD_EMP_LEAVE_ALL';

    /** @var string HRM_DOC_RELEASE_REQ Reqeust for own document release */
    const HRM_DOC_RELEASE_REQ = 'HRM_EMP_DOC_RELEASE_REQ';

    /** @var string HRM_DOC_RELEASE_REQ_ALL Reqeust document release for everybody */
    const HRM_DOC_RELEASE_REQ_ALL = 'HRM_EMP_DOC_RELEASE_REQ_ALL';
    
    /** @var string HRM_MANAGE_LEAVE_ADJUSTMENT Manage Leave Adjustment */
    const HRM_MANAGE_LEAVE_ADJUSTMENT = 'HRM_MANAGE_LEAVE_ADJUSTMENT';

    /** @var string HRM_TIMEOUT_REQUEST Request For Employee Personal Timeouts */
    const HRM_TIMEOUT_REQUEST = 'HRM_TIMEOUT_REQUEST';

    /** @var string HRM_TIMEOUT_REQUEST_ALL Request For All Employee Personal Timeouts */
    const HRM_TIMEOUT_REQUEST_ALL = 'HRM_TIMEOUT_REQUEST_ALL';

    /** @var string HRM_ADD_EMP_LEAVE_DEP can apply leave for department employees with workflow */
    const HRM_ADD_EMP_LEAVE_DEP = 'HRM_ADD_EMP_LEAVE_DEP';

    /** @var string HRM_MANAGE_DEDUCTION Manage Employees Deduction  */
    const HRM_MANAGE_DEDUCTION = 'HRM_MANAGE_DEDUCTION';

    /** @var string HRM_MANAGE_DEDUCTION_ADMIN Manage Employees Deduction with Admin Privilege (Without Workflow) */
    const HRM_MANAGE_DEDUCTION_ADMIN = 'HRM_MANAGE_DEDUCTION_ADMIN';

    /** @var string HRM_MANAGE_REWARDS Manage Employees Rewards  */
    const HRM_MANAGE_REWARDS = 'HRM_MANAGE_REWARDS';

    /** @var string HRM_MANAGE_REWARDS_ADMIN Manage Employees Rewards with Admin Privilege (Without Workflow) */
    const HRM_MANAGE_REWARDS_ADMIN = 'HRM_MANAGE_REWARDS_ADMIN';

    /** @var string HRM_MANAGE_GENERAL_REQUEST Manage General Request Own */
    const HRM_MANAGE_GENERAL_REQUEST = 'HRM_MANAGE_GENERAL_REQUEST';

    /** @var string HRM_MANAGE_GENERAL_REQUEST_ALL Manage General Request All */
    const HRM_MANAGE_GENERAL_REQUEST_ALL = 'HRM_MANAGE_GENERAL_REQUEST_ALL';

    /** @var string HRM_TASK_PERFORMER_DETAILS Manage Task Performer Name & Time In Manage Task */
    const HRM_TASK_PERFORMER_DETAILS = 'HRM_TASK_PERFORMER_DETAILS';

    /*
     |=============================================
     | HRM - reports
     |=============================================
     */
    /** @var string HRM_VIEWATDMETRICS_OWN View Attendance Metrics (Own) */
    const HRM_VIEWATDMETRICS_OWN = 'HRM_VIEWATDMETRICS_OWN';

    /** @var string HRM_VIEWATDMETRICS_DEP View Attendance Metrics (Dep) */
    const HRM_VIEWATDMETRICS_DEP = 'HRM_VIEWATDMETRICS_DEP';

    /** @var string HRM_VIEWATDMETRICS_ALL View Attendance Metrics (All) */
    const HRM_VIEWATDMETRICS_ALL = 'HRM_VIEWATDMETRICS_ALL';

    /** @var string HRM_MODIFYATDMETRICS Modify Attendance Metrics */
    const HRM_MODIFYATDMETRICS = 'HRM_MODIFYATDMETRICS';

    /** @var string HRM_MODIFYATDMETRICS_OWN Modify Attendance Metrics (Own) */
    const HRM_MODIFYATDMETRICS_OWN = 'HRM_MODIFYATDMETRICS_OWN';

    /** @var string HRM_VIEWEMPLOYEES View Employee **/
    const HRM_VIEWEMPLOYEES = 'HRM_VIEWEMPLOYEES';
    
    /** @var string HRM_VIEWEMPLOYEES View Employee **/
    const HRM_VIEWEMPLOYEES_DEP = 'HRM_VIEWEMPLOYEES_DEP';
    
    /** @var string HRM_VIEWEMPLOYEES View Employee **/
    const HRM_VIEWEMPLOYEES_ALL = 'HRM_VIEWEMPLOYEES_ALL';

    /** @var string HRM_VIEWPAYSLIP View Payslip */
    const HRM_VIEWPAYSLIP = 'HRM_VIEWPAYSLIP';

    /** @var string HRM_VIEWPAYSLIP_OWN View Payslip (Own) */
    const HRM_VIEWPAYSLIP_OWN = 'HRM_VIEWPAYSLIP_OWN';

    /** @var string HRM_VIEWPAYSLIP_DEP View Payslip (Dep) */
    const HRM_VIEWPAYSLIP_DEP = 'HRM_VIEWPAYSLIP_DEP';

    /** @var string HRM_VIEWPAYSLIP_ALL View Payslip (All) */
    const HRM_VIEWPAYSLIP_ALL = 'HRM_VIEWPAYSLIP_ALL';

    /** @var string HRM_VIEW_END_OF_SERVICE View End of Service Calculation */
    const HRM_VIEW_END_OF_SERVICE = 'HRM_VIEW_END_OF_SERVICE';

    /** @var string HRM_SALARY_CERTIFICATE View Salary Certificate For Opening Bank Account */
    const HRM_SALARY_CERTIFICATE = 'HRM_SALARY_CERTIFICATE';

    /** @var string HRM_SALARY_TRANSFER_LETTER View Salary Transer Letter */
    const HRM_SALARY_TRANSFER_LETTER = 'HRM_SALARY_TRANSFER_LETTER';
    
    /** @var string HRM_EMPLOYEE_DOCUMENT_VIEW View Employee Document */
    const HRM_EMPLOYEE_DOCUMENT_VIEW = 'HRM_EMPLOYEE_DOCUMENT_VIEW';
    
    /** @var string HRM_MANAGE_PAY_ELEMENTS Manage Pay Elements */
    const HRM_MANAGE_PAY_ELEMENTS = 'HRM_MANAGE_PAY_ELEMENTS';

    /** @var string HRM_EMPLOYEE_SHIFT_VIEW_OWN View Shift Report (Own) */
    const HRM_EMPLOYEE_SHIFT_VIEW_OWN = 'HRM_EMPLOYEE_SHIFT_VIEW_OWN';

    /** @var string HRM_EMPLOYEE_SHIFT_VIEW_DEP View Shift Report (Dep) */
    const HRM_EMPLOYEE_SHIFT_VIEW_DEP = 'HRM_EMPLOYEE_SHIFT_VIEW_DEP';

    /** @var string HRM_EMPLOYEE_SHIFT_VIEW_ALL View Shift Report (All) */
    const HRM_EMPLOYEE_SHIFT_VIEW_ALL = 'HRM_EMPLOYEE_SHIFT_VIEW_ALL';
    
    /** @var string HRM_EMPLOYEE_LEAVE_REPORT View Employee Leave Report (All) */
    const HRM_EMPLOYEE_LEAVE_REPORT = 'HRM_EMPLOYEE_LEAVE_REPORT';

    /** @var string HRM_EMPLOYEE_LEAVE_REPORT_OWN View Employee Leave Report (Own) */
    const HRM_EMPLOYEE_LEAVE_REPORT_OWN = 'HRM_EMPLOYEE_LEAVE_REPORT_OWN';
    
    /** @var string HRM_EMPLOYEE_LEAVE_REPORT_DEP View Employee Leave Report (Dep) */
    const HRM_EMPLOYEE_LEAVE_REPORT_DEP = 'HRM_EMPLOYEE_LEAVE_REPORT_DEP';

    /** @var string HRM_EMP_DEDUCTION_REWARD View Employees Deduction / Rewards */
    const HRM_EMP_DEDUCTION_REWARD = 'HRM_EMP_DEDUCTION_REWARD';

    /** @var string HRM_EMP_DEDUCTION_REWARD_OWN View Employees Deduction / Rewards (Own) */
    const HRM_EMP_DEDUCTION_REWARD_OWN = 'HRM_EMP_DEDUCTION_REWARD_OWN';

    /** @var string HRM_EMP_LEAVE_DETAIL_REPORT View Employees Leave Detail Report (All) */
    const HRM_EMP_LEAVE_DETAIL_REPORT = 'HRM_EMP_LEAVE_DETAIL_REPORT';

    /** @var string HRM_EMP_LEAVE_DETAIL_REPORT_OWN View Employees Leave Detail Report (Own) */
    const HRM_EMP_LEAVE_DETAIL_REPORT_OWN = 'HRM_EMP_LEAVE_DETAIL_REPORT_OWN';

    /** @var string HRM_EMP_LEAVE_DETAIL_REPORT_DEP View Employees Leave Detail Report (Dep) */
    const HRM_EMP_LEAVE_DETAIL_REPORT_DEP = 'HRM_EMP_LEAVE_DETAIL_REPORT_DEP';

    /*
     |=============================================
     | HRM - configurations
     |=============================================
     */
    /** @var string WARNINGCATEGORY Warning Category */
    const WARNINGCATEGORY = 'WARNINGCATEGORY';

    /** @var string WARNINGGRADE Warning Grade */
    const WARNINGGRADE = 'WARNINGGRADE';
    
    /** @var string HRM_SETUP HR setup */
    const HRM_SETUP = 'HRM_SETUP';

    /** @var string HRM_ADD_SHIFT Add/Manage Shifts  */
    const HRM_MANAGE_SHIFT = 'HRM_MANAGE_SHIFT';
     
    /** @var string HRM_MANAGE_DESIGNATION */
    const HRM_MANAGE_DESIGNATION = 'HRM_MANAGE_DESIGNATION';

    /** @var string HRM_DEPARTMENT Manage Departments */
    const HRM_MANAGE_DEPARTMENT = 'HRM_DEPARTMENT';

    /** @var string HRM_COMPANY Manage Company */
    const HRM_MANAGE_COMPANY = 'HRM_MANAGE_COMPANY';

    /** @var string HRM_MANAGE_HOLIDAY Add/Manage Public Holidays */
    const HRM_MANAGE_HOLIDAY = 'HRM_MANAGE_HOLIDAY';

    /** @var string HRM_MANAGE_LEAVE_CARRY_FORWARD Add/Manage Leave Carry Forward Limit */
    const HRM_MANAGE_LEAVE_CARRY_FORWARD = 'HRM_MANAGE_LEAVE_CARRY_FORWARD';

    /** @var string HRM_MANAGE_PENSION_CONFIG  Add/Manage Employee Pension Configuration */
    const HRM_MANAGE_PENSION_CONFIG = 'HRM_MANAGE_PENSION_CONFIG';

    /** @var string HRM_MANAGE_GENERAL_REQUEST_TYPE Add/Manage General Request Type */
    const HRM_MANAGE_GENERAL_REQUEST_TYPE = 'HRM_MANAGE_GENERAL_REQUEST_TYPE';

    /*
     |=============================================
     | CRM
     |=============================================
     */

    /** @var string SA_RECEPTION_REPORT View Customer reception report */
    const SA_RECEPTION_REPORT = 'SA_RECEPTION_REPORT';

    /** @var string SA_RECEPTION Recieve Customer */
    const SA_RECEPTION = 'SA_RECEPTION';

    /** @var string SA_CUSTOMERS_VISITED Customers List */
    const SA_CUSTOMERS_VISITED = 'SA_CUSTOMERS_VISITED';

    /** @var string SA_RECEPTION_INVOICE Reception Invoice Report */
    const SA_RECEPTION_INVOICE = 'SA_RECEPTION_INVOICE';


    /*
     |=============================================
     | Access to the index of each modules
     |=============================================
     */
    /** @var string HEAD_MENU_SALES Sales Menu */
    const HEAD_MENU_SALES = 'HEAD_MENU_SALES';

    /** @var string HEAD_MENU_PURCHASE Purchase Menu */
    const HEAD_MENU_PURCHASE = 'HEAD_MENU_PURCHASE';

    /** @var string HEAD_MENU_ASSET Fixed Asset Menu */
    const HEAD_MENU_ASSET = 'HEAD_MENU_ASSET';

    /** @var string HEAD_MENU_FINANCE Finance Menu */
    const HEAD_MENU_FINANCE = 'HEAD_MENU_FINANCE';

    /** @var string HEAD_MENU_HR HR Menu */
    const HEAD_MENU_HR = 'HEAD_MENU_HR';

    /** @var string HEAD_MENU_REPORT Report Menu */
    const HEAD_MENU_REPORT = 'HEAD_MENU_REPORT';

    /** @var string HEAD_MENU_SETTINGS Settings Menu */
    const HEAD_MENU_SETTINGS = 'HEAD_MENU_SETTINGS';

    /** @var string HEAD_MENU_LABOUR Labour Menu */
    const HEAD_MENU_LABOUR = 'HEAD_MENU_LABOUR';

    /*
     |=============================================
     | Finance
     |=============================================
     */

    /** @var string SA_CASH_HANDOVER_ALL Place cash handover request for all */
    const SA_CASH_HANDOVER_ALL = 'SA_CASH_HANDOVER_ALL';

    /** @var string SA_CASH_HANDOVER Place cash handover request */
    const SA_CASH_HANDOVER = 'SA_CASH_HANDOVER';

    /** @var string SA_CASH_HANDOVER_LIST See cash handover request list */
    const SA_CASH_HANDOVER_LIST = 'SA_CASH_HANDOVER_LIST';

    /** @var string SA_CASH_HANDOVER_INQ Cash handover report */
    const SA_CASH_HANDOVER_INQ = 'SA_CASH_HANDOVER_INQ';


    /*
     |=============================================
     | Dashboard reports
     |=============================================
     */

    /** @var string SA_DSH_LAST_10_DAYS Sales - Last 10 days */
    const SA_DSH_LAST_10_DAYS = 'SA_DSH_LAST_10_DAYS';

    /** @var string SA_DSH_TOP_5_EMP Top 5 Employees Service Count */
    const SA_DSH_TOP_5_EMP = 'SA_DSH_TOP_5_EMP';

    /** @var string SA_DSH_TOP_5 Top 5 Sales Category Count */
    const SA_DSH_TOP_5 = 'SA_DSH_TOP_5';

    /** @var string SA_DSH_FIND_INV Find Invoices */
    const SA_DSH_FIND_INV = 'SA_DSH_FIND_INV';

    /** @var string SA_DSH_TODAYS_INV Today's Invoices */
    const SA_DSH_TODAYS_INV = 'SA_DSH_TODAYS_INV';

    /** @var string SA_DSH_TODAYS_REC Today's Receipts */
    const SA_DSH_TODAYS_REC = 'SA_DSH_TODAYS_REC';

    /** @var string SA_DSH_CAT_REP Today's Category Report */
    const SA_DSH_CAT_REP = 'SA_DSH_CAT_REP';

    /** @var string SA_DSH_TOP_10_CUST Top 10 Customers */
    const SA_DSH_TOP_10_CUST = 'SA_DSH_TOP_10_CUST';

    /** @var string SA_DSH_TRANS Todays Transaction */
    const SA_DSH_TRANS = 'SA_DSH_TRANS';

    /** @var string SA_DSH_TRANS_ACC Accumulated Transaction */
    const SA_DSH_TRANS_ACC = 'SA_DSH_TRANS_ACC';

    /** @var string SA_DSH_BNK_AC Bank Accounts */
    const SA_DSH_BNK_AC = 'SA_DSH_BNK_AC';

    /** @var string SA_DSH_COLL_BD Total Collection Breakdown */
    const SA_DSH_COLL_BD = 'SA_DSH_COLL_BD';

    /** @var string SA_DSH_AC_CLOSING_BAL Account Closing Balance */
    const SA_DSH_AC_CLOSING_BAL = 'SA_DSH_AC_CLOSING_BAL';

    /** @var string SA_DSH_HRM HRM Dashboard */
    const SA_DSH_HRM = 'SA_DSH_HRM';

    /** @var string SA_DHS_CUST_BAL Customer Balances */
    const SA_DHS_CUST_BAL = 'SA_DHS_CUST_BAL';

    /** @var string SA_DSH_CUST_BAL_AMT Customer Balance Till Date */
    const SA_DSH_CUST_BAL_AMT = 'SA_DSH_CUST_BAL_AMT';

    /** @var string SA_DHS_DEP_SALES Department wise Sales Breakdown */
    const SA_DHS_DEP_SALES = 'SA_DHS_DEP_SALES';

    /** @var string SA_DSH_DEP_SALES_MNTH Department wise Monthly Sales Breakdown */
    const SA_DSH_DEP_SALES_MNTH = 'SA_DSH_DEP_SALES_MNTH';
    

    /*
     |=============================================
     | Labour
     |=============================================
     */
        
    /** @var string SA_CREATE_AGENT Create new agent */
    const SA_CREATE_AGENT = 'SA_CREATE_AGENT';
    
    /** @var string SA_LBR_CREATE Create & Update new labor */
    const SA_LBR_CREATE = 'SA_LBR_CREATE';
    
    /** @var string SA_LBR_CONTRACT Create & Update contracts */
    const SA_LBR_CONTRACT = 'SA_LBR_CONTRACT';

    /** @var string SA_MAID_RETURN Add/Manage Maid Return */
    const SA_MAID_RETURN = 'SA_MAID_RETURN';
    
    /** @var string SA_MAID_REPLACEMENT Add/Manage Maid Replacement */
    const SA_MAID_REPLACEMENT = 'SA_MAID_REPLACEMENT';

    /** @var string SA_LBR_CONTRACT_INSTALLMENT Labour contract installment */
    const SA_LBR_CONTRACT_INSTALLMENT = 'SA_LBR_CONTRACT_INSTALLMENT';

     /** @var string SA_INSTALLMENT_DELETE Installment Delete */
     const SA_INSTALLMENT_DELETE = 'SA_INSTALLMENT_DELETE';

    
    /** @var string SA_CREATE_AGENT Agent Inquiry */
    const SA_AGENT_LIST = 'SA_AGENT_LIST';

    /** @var string SA_LBR_VIEW Labour inquiry */
    const SA_LBR_VIEW = 'SA_LBR_VIEW';
    
    /** @var string SA_LBR_CONTRACT_INQ Labour contract inquiry */
    const SA_LBR_CONTRACT_INQ = 'SA_LBR_CONTRACT_INQ';

    /** @var string SA_MAID_MOVEMENT_REPORT Maid Movement Report */
    const SA_MAID_MOVEMENT_REPORT = 'SA_MAID_MOVEMENT_REPORT';   

    /** @var string SA_LBR_INSTALLMENT_REPORT Installment Report */
    const SA_LBR_INSTALLMENT_REPORT = 'SA_LBR_INSTALLMENT_REPORT';   

    /**
     * variable that holds the list of permissions
     *
     * @var array
     */
    private $areas = [];

    public function __construct()
    {
        $this->areas = [
            /** Site Administration */
            self::SA_CREATECOMPANY          => [G::SS_SADMIN|1, trans("Install/update companies")],
            self::SA_CREATELANGUAGE         => [G::SS_SADMIN|2, trans("Install/update languages")],
            self::SA_CREATEMODULES          => [G::SS_SADMIN|3, trans("Install/upgrade modules")],
            self::SA_SOFTWAREUPGRADE        => [G::SS_SADMIN|4, trans("Software upgrades")],
            self::SA_MONITORQUEUES          => [G::SS_SADMIN|5, trans("Monitor queued jobs")],

            /** Company Setup */
            self::SA_SETUPCOMPANY           => [G::SS_SETUP|1, trans("Company parameters")],
            self::SA_SECROLES               => [G::SS_SETUP|2, trans("Access levels edition")],
            self::SA_USERS                  => [G::SS_SETUP|3, trans("Users setup")],
            self::SA_POSSETUP               => [G::SS_SETUP|4, trans("Point of sales definitions")],
            self::SA_PRINTERS               => [G::SS_SETUP|5, trans("Printers configuration")],
            self::SA_PRINTPROFILE           => [G::SS_SETUP|6, trans("Print profiles")],
            self::SA_PAYTERMS               => [G::SS_SETUP|7, trans("Payment terms")],
            self::SA_SHIPPING               => [G::SS_SETUP|8, trans("Shipping ways")],
            self::SA_CRSTATUS               => [G::SS_SETUP|9, trans("Credit status definitions changes")],
            self::SA_INVENTORYLOCATION      => [G::SS_SETUP|10, trans("Inventory locations changes")],
            self::SA_INVENTORYMOVETYPE      => [G::SS_SETUP|11, trans("Inventory movement types")],
            self::SA_WORKCENTRES            => [G::SS_SETUP|12, trans("Manufacture work centres")],
            self::SA_FORMSETUP              => [G::SS_SETUP|13, trans("Forms setup")],
            self::SA_CRMCATEGORY            => [G::SS_SETUP|14, trans("Contact categories")],
            self::SA_LIMITEDROLEASSIGN      => [G::SS_SETUP|15, trans("Can only assign less previlaged roles")],
            self::SA_ENTITY_GROUP           => [G::SS_SETUP|16, trans("Can add/manage workflow related groups")],

            /** Special and common functions */
            self::SA_VOIDTRANSACTION 		=> [G::SS_SPEC|1, trans("Voiding transactions")],
            self::SA_BACKUP 				=> [G::SS_SPEC|2, trans("Database backup/restore")],
            self::SA_VIEWPRINTTRANSACTION 	=> [G::SS_SPEC|3, trans("Common view/print transactions interface")],
            self::SA_ATTACHDOCUMENT 		=> [G::SS_SPEC|4, trans("Attaching documents")],
            self::SA_SETUPDISPLAY 			=> [G::SS_SPEC|5, trans("Display preferences")],
            self::SA_CHGPASSWD 				=> [G::SS_SPEC|6, trans("Password changes")],
            self::SA_EDITOTHERSTRANS 		=> [G::SS_SPEC|7, trans("Edit other users transactions")],
            self::SA_VOIDEDTRANSACTIONS     => [G::SS_SPEC|8, 	trans("Voided Transaction Report")],
            /*
            * Note: This Access Level (SA_REP) is only for the UI.
            * The actual permission cannot be set because,
            * every pdf/excel report goes through the reports_main.php
            */
            self::SA_REP 	                => [G::SS_SPEC|9, 	trans("Reports and Analysis")],
            self::SA_MGMTREP	            => [G::SS_SPEC|10, trans("Management Report")],
            self::SA_ACL_LIST 	            => [G::SS_SPEC|11, 	trans("Access Control List")],
            self::SA_USERACTIVITY           => [G::SS_SPEC|12,  trans("Log - User Activity")],
            self::SA_MANAGE_WORKFLOW        => [G::SS_SPEC|13,  trans("Add/Manage Workflows")],
            self::SA_MANAGE_TASKS           => [G::SS_SPEC|14,  trans("Manage Tasks|Requests|Activities")],
            self::SA_MANAGE_TASKS_ALL       => [G::SS_SPEC|15,  trans("Manage Everybody's Tasks|Requests|Activities")],
            self::SA_CANCEL_OTHERS_TASK     => [G::SS_SPEC|16,  trans("Can cancel Tasks|Requests|Activities initiated by others")],
            self::SA_MANAGE_OWN_TASKS       => [G::SS_SPEC|17,  trans("Can Approve/Reject own Tasks|Requests|Activities")],
            self::SA_MANAGE_DOCUMENT_TYPE   => [G::SS_SPEC|18,  trans("Manage Document Types")],
            self::SA_MANAGE_GROUP_MEMBERS   => [G::SS_SPEC|19,  trans("Manage System Reserved Group Members")],
            self::SA_VIEW_ONLY_TASK         => [G::SS_SPEC|20,  trans("Can Only View Tasks|Requests|Activities")],
            self::SA_AUTOFETCHREPORT        => [G::SS_SPEC|21, trans("Auto Fetch Report")],

            /** Sales related functions */
            self::SA_SALESTYPES             => [G::SS_SALES_C|1, trans("Sales types")],
            self::SA_SALESPRICE             => [G::SS_SALES_C|2, trans("Sales prices edition")],
            self::SA_SALESMAN               => [G::SS_SALES_C|3, trans("Sales staff maintenance")],
            self::SA_SALESAREA              => [G::SS_SALES_C|4, trans("Sales areas maintenance")],
            self::SA_SALESGROUP             => [G::SS_SALES_C|5, trans("Sales groups changes")],
            self::SA_STEMPLATE              => [G::SS_SALES_C|6, trans("Sales templates")],
            self::SA_SRECURRENT             => [G::SS_SALES_C|7, trans("Recurrent invoices definitions")],
            self::SA_CUSTDISCOUNT           => [G::SS_SALES_C|8, trans("Items and Discount settings for customer")],
            self::SA_UPDATECRLMT            => [G::SS_SALES_C|9, trans("Credit Limit settings for customer")],

            self::SA_SALESTRANSVIEW         => [G::SS_SALES|1,  trans("Sales transactions view")],
            self::SA_CUSTOMER               => [G::SS_SALES|2,  trans("Sales customer and branches changes")],
            self::SA_SALESORDER             => [G::SS_SALES|3, trans("Sales orders edition")],
            self::SA_SALESDELIVERY          => [G::SS_SALES|4, trans("Sales deliveries edition")],
            self::SA_SALESINVOICE           => [G::SS_SALES|5, trans("Sales invoices edition")],
            self::SA_SALESCREDITINV         => [G::SS_SALES|6, trans("Sales credit notes against invoice")],
            self::SA_SALESCREDIT            => [G::SS_SALES|7, trans("Sales freehand credit notes")],
            self::SA_SALESPAYMNT            => [G::SS_SALES|8, trans("Customer payments entry")],
            self::SA_SALESALLOC             => [G::SS_SALES|9, trans("Customer payments allocation")],
            self::SA_SALESQUOTE             => [G::SS_SALES|10, trans("Sales quotations")],
            self::SA_CUSTRCPTVCHR           => [G::SS_SALES|11, trans("Customer reciept voucher")],
            self::SA_SERVICE_REQUEST        => [G::SS_SALES|12,  trans("Service Request")],
            self::SA_SALESINVOICEDATEMOD    => [G::SS_SALES|13, trans("Transaction date modification when invoicing")],
            self::SA_MANAGEINV			    => [G::SS_SALES|14, trans("Manage Invoices")],
            self::SA_EDITSALESINVOICE	    => [G::SS_SALES|15, trans("Edit Sales Invoices")],
            self::SA_MANAGEINVDEP	 	    => [G::SS_SALES|16, trans("Manage Invoices (Department)")],
            self::SA_MANAGEINVALL	 	    => [G::SS_SALES|17, trans("Manage Invoices (All)")],
            self::SA_UPDATEINVOICE		    => [G::SS_SALES|18, trans("Update Sales Invoice")],
            self::SA_CUSTWTHDUPDETAIL       => [G::SS_SALES|19, trans("Can add customers with duplicate mobile number")],
            self::SA_MODSALESPAYMENT        => [G::SS_SALES|20, trans("Modify Customer Payment")],
            self::SA_PRINTSALESINV          => [G::SS_SALES|21, trans("Print Sales Invoice")],
            self::SA_PRINTSALESRCPT         => [G::SS_SALES|22, trans("Print Customer Reciept")],
            self::SA_INVWTHOUTTKN           => [G::SS_SALES|23, trans("Issue Invoice without token")],
            self::SA_INVWTHOUTSRVRQST       => [G::SS_SALES|24, trans("Issue Invoice without service request")],
            self::SA_SUDOEDITTRANSID        => [G::SS_SALES|25, trans("Forcly update transaction_id even if there is already transaction_id")],
            self::SA_SUDOEDITAPPLCTNID      => [G::SS_SALES|26, trans("Forcly update application_id even if there is already application_id")],
            self::SA_EDITINDIVTRANSDATE     => [G::SS_SALES|27, trans("Edit the transaction date of individual transactions")],
            self::SA_EDITCMMSSNDUSR         => [G::SS_SALES|28, trans("Edit the commission owned user")],
            self::SA_SUDOEDITNARTN          => [G::SS_SALES|29, trans("Forcly update narration even if there is already narration")],
            self::SA_MKINVFRMSRVREQ         => [G::SS_SALES|30, trans("Make Invoice from service request")],
            self::SA_CUSTPYMNT_ALWD         => [G::SS_SALES|31, trans("Receive customer payments from all allowed departments")],
            self::SA_PRINTSALESCREDIT       => [G::SS_SALES|32, trans("Print sales credit notes")],
            self::SA_SENDINVSMS             => [G::SS_SALES|33, trans("Send invoice through SMS")],
            self::SA_EDITSERVICEREQ         => [G::SS_SALES|34, trans("Edit Service Request")],
            self::SA_DELSERVICEREQ          => [G::SS_SALES|35, trans("Delete Service Request")],
            self::SA_SRVREQLNITMINV         => [G::SS_SALES|36, trans("Make line item wise invoice from service request")],
            self::SA_APRVCSHDISCOUNT        => [G::SS_SALES|37, trans("Approve cash discounts in cashier dashboard")],
            self::SA_NOSALESCREDITFLOW      => [G::SS_SALES|38, trans("Credit note against contract without using flow")],
            self::SA_CHANGERETURNINCDAYS    => [G::SS_SALES|39, trans("Change sales return income days")],
            self::SA_PRINTSERVICEREQ        => [G::SS_SALES|40, trans("Print Service Request")],
            self::SA_INV_PREPAID_ORDERS     => [G::SS_SALES|41, trans("Invoice Prepaid Orders")],
            self::SA_SALESORDER_VIEW        => [G::SS_SALES|42, trans("Sales Order View")],
            self::SA_SALESORDER_VIEW_DEP    => [G::SS_SALES|43, trans("Sales Order View (Dep)")],
            self::SA_SALESORDER_VIEW_ALL    => [G::SS_SALES|44, trans("Sales Order View (All)")],        
            self::SA_SALES_LINE_VIEW        => [G::SS_SALES|45, trans("Sales Order Line View (All)")],        
            self::SA_SALES_LINE_VIEW_OWN    => [G::SS_SALES|46, trans("Sales Order Line View (Own)")],        
            self::SA_SALES_LINE_VIEW_DEP    => [G::SS_SALES|47, trans("Sales Order Line View (Dep)")],   
            self::SA_DIRECTINVORDER         => [G::SS_SALES|48, trans("Direct Invoice + Job Order")],
            self::SA_DIRECTDLVRORDER        => [G::SS_SALES|49, trans("Job Order With Auto Completion")],
            self::SA_DIRECTINVDLVRORDER     => [G::SS_SALES|50, trans("Direct Invoice + Job Order With Auto Completion")],
            self::SA_SENDINVEMAIL           => [G::SS_SALES|51, trans("Send invoice through email")], 
            self::SA_HIDEDISCOUNT           => [G::SS_SALES|52, trans("Hide discount from invoice screen")], 
            self::SA_SALESLNEXPONLY         => [G::SS_SALES|53, trans("Job Order Transaction Add Expense Without Completion")],
            self::SA_SALESLNCMPLTWEXP       => [G::SS_SALES|54, trans("Job Order Transaction Completion While Passing Expense")],
            self::SA_RCVPMTWITHOUTDIM       => [G::SS_SALES|55, trans("Cashier dashboard - All option")],
            self::SA_GOVBNKACTEDITABLE      => [G::SS_SALES|56, trans("Can edit Govt. Bank Act when invoicing")],

            self::SA_SALESANALYTIC 	        => [G::SS_SALES_A|1, trans("Sales analytical reports")],
            self::SA_SALESBULKREP 	        => [G::SS_SALES_A|2, trans("Sales document bulk reports")],
            self::SA_PRICEREP 		        => [G::SS_SALES_A|3, trans("Sales prices listing")],
            self::SA_SALESMANREP 	        => [G::SS_SALES_A|4, trans("Sales staff listing")],
            self::SA_CUSTBULKREP 	        => [G::SS_SALES_A|5, trans("Customer bulk listing")],
            self::SA_CUSTSTATREP 	        => [G::SS_SALES_A|6, trans("Customer status report")],
            self::SA_CUSTPAYMREP 	        => [G::SS_SALES_A|7, trans("Customer payments report")],
            self::SA_SRVREPORT			    => [G::SS_SALES_A|8, trans("Service Report (Self Only)")],
            self::SA_SRVREPORTALL 		    => [G::SS_SALES_A|9, trans("Service Report (All)"),],
            self::SA_SRVREQLI 			    => [G::SS_SALES_A|10, trans("Service Request List"),],
            self::SA_CSHCOLLECTREP 		    => [G::SS_SALES_A|11, trans("Cash Collection (Self Only)")],
            self::SA_CSHCOLLECTREPALL 	    => [G::SS_SALES_A|12, trans("Cash Collection (All)")],
            self::SA_EMPANALYTIC		    => [G::SS_SALES_A|13, trans("Employee Analytical Reports (Self Only)")],
            self::SA_EMPANALYTICDEP		    => [G::SS_SALES_A|14, trans("Employee Analytical Reports (Department)")],
            self::SA_EMPANALYTICALL		    => [G::SS_SALES_A|15, trans("Employee Analytical Reports (All)")],
            self::SA_CUSTANALYTIC		    => [G::SS_SALES_A|16, trans("Customer Analytical Reports")],
            self::SA_CSHCOLLECTREPDEP	    => [G::SS_SALES_A|17, trans("Cash Collection (Department)")],
            self::SA_EMPCOMMAAD			    => [G::SS_SALES_A|18, trans("Employee Commission Adheed (Self Only)")],
            self::SA_EMPCOMMAADDEP		    => [G::SS_SALES_A|19, trans("Employee Commission Adheed (Department)")],
            self::SA_EMPCOMMAADALL		    => [G::SS_SALES_A|20, trans("Employee Commission Adheed (All)")],
            self::SA_CRSALESREP_OWN		    => [G::SS_SALES_A|21, trans("Credit Invoice Report (Own)")],
            self::SA_CRSALESREP_DEP		    => [G::SS_SALES_A|22, trans("Credit Invoice Report (Dep)")],
            self::SA_CRSALESREP_ALL		    => [G::SS_SALES_A|23, trans("Credit Invoice Report (All)")],
            self::SA_SRVREPORTDEP		    => [G::SS_SALES_A|24, trans("Service Report (Department)"),],
            self::SA_CUSTDETREP			    => [G::SS_SALES_A|25, trans("Customer details report")],
            self::SA_STAFMISTAKREP		    => [G::SS_SALES_A|26, trans("Staff mistakes report")],
            self::SA_SERVICETRANSREP_OWN    => [G::SS_SALES_A|27, trans("Management Report - Service Report (Own Transactions)")],
            self::SA_SERVICETRANSREP_ALL    => [G::SS_SALES_A|28, trans("Management Report - Service Report (All Transactions)")],
            self::SA_INVOICEREP             => [G::SS_SALES_A|29, trans("Management Report - Invoice Report")],
            self::SA_INVOICEPMTREP          => [G::SS_SALES_A|30, trans("Management Report - Invoice Payment Report")],
            self::SA_SERVICEMSTRREP         => [G::SS_SALES_A|31, trans("Management Report - Service Master Report")],
            self::SA_INVOICEREPORT          => [G::SS_SALES_A|32, trans("Invoice Report")],
            self::SA_CTGRYSALESREP          => [G::SS_SALES_A|33, trans("Category wise sales report")],
            self::SA_CTGRYSALESREP_ALL      => [G::SS_SALES_A|34, trans("Category wise sales report of everybody")],
            self::SA_CUSTWISEREPPARTICULARS => [G::SS_SALES_A|35, trans("Sales summary report particulars")],
            self::SA_CUSTWISEALLREP         => [G::SS_SALES_A|36, trans("Sales summary report all user")],
            self::SA_CUSTWISEOWNREP         => [G::SS_SALES_A|37, trans("Sales summary report own user")],
            self::SA_SRVREQLSTALL           => [G::SS_SALES_A|38, trans("See all Service Requests in service request list")],
            self::SA_CUSREP                 => [G::SS_SALES_A|39, trans("Show Custom Report")],
            self::SA_SHOWSERVREP            => [G::SS_SALES_A|40, trans("Show Service Report")],
            self::SA_CUSREP_ALL             => [G::SS_SALES_A|41, trans("Custom Report (All)")],
            self::SA_CUSREP_DEP             => [G::SS_SALES_A|42, trans("Custom Report (Dep)")],

            /** Purchase related functions */
            self::SA_PURCHASEPRICING        => [G::SS_PURCH_C|1, trans("Purchase price changes")],

            self::SA_SUPPTRANSVIEW          => [G::SS_PURCH|1, trans("Supplier transactions view")],
            self::SA_SUPPLIER               => [G::SS_PURCH|2, trans("Suppliers changes")],
            self::SA_PURCHASEORDER          => [G::SS_PURCH|3, trans("Purchase order entry")],
            self::SA_GRN                    => [G::SS_PURCH|4, trans("Purchase receive")],
            self::SA_SUPPLIERINVOICE        => [G::SS_PURCH|5, trans("Supplier invoices")],
            self::SA_SUPPLIERCREDIT         => [G::SS_PURCH|6, trans("Supplier credit notes")],
            self::SA_SUPPLIERPAYMNT         => [G::SS_PURCH|7, trans("Supplier payments")],
            self::SA_SUPPLIERALLOC          => [G::SS_PURCH|8, trans("Supplier payments allocations")],
            self::SA_GRNDELETE              => [G::SS_PURCH|9, trans("Deleting GRN items during invoice entry")],
            self::SA_ITEM_PURCHASE          => [G::SS_PURCH|10, trans("Stock items add/edit for purchase")],
            self::SA_ITEM_DELETE            => [G::SS_PURCH|11, trans("Stock items delete")],
            self::SA_SUPPDISC 		        => [G::SS_PURCH|12, trans("Supplier items and discounts")],

            self::SA_SUPPLIERANALYTIC       => [G::SS_PURCH_A|1, trans("Supplier analytical reports")],
            self::SA_SUPPBULKREP            => [G::SS_PURCH_A|2, trans("Supplier document bulk reports")],
            self::SA_SUPPPAYMREP            => [G::SS_PURCH_A|3, trans("Supplier payments report")],

            /** Inventory related functions */
            self::SA_ITEM                   => [G::SS_ITEMS_C|1, trans("Stock items add/edit")],
            self::SA_SALESKIT               => [G::SS_ITEMS_C|2, trans("Sales kits")],
            self::SA_ITEMCATEGORY           => [G::SS_ITEMS_C|3, trans("Item categories")],
            self::SA_UOM                    => [G::SS_ITEMS_C|4, trans("Units of measure")],
            self::SA_CTGRYGROUP             => [G::SS_ITEMS_C|5, trans("Item category groups")],
            
            self::SA_ITEMSSTATVIEW          => [G::SS_ITEMS|1, trans("Stock status view")],
            self::SA_ITEMSTRANSVIEW         => [G::SS_ITEMS|2, trans("Stock transactions view")],
            self::SA_FORITEMCODE            => [G::SS_ITEMS|3, trans("Foreign item codes entry")],
            self::SA_LOCATIONTRANSFER       => [G::SS_ITEMS|4, trans("Inventory location transfers")],
            self::SA_INVENTORYADJUSTMENT    => [G::SS_ITEMS|5, trans("Inventory adjustments")],
            self::SA_STOCK_RETURN           => [G::SS_ITEMS|6, trans("Add/Manage Stock Return")],
            self::SA_STOCK_REPLACEMENT      => [G::SS_ITEMS|7, trans("Add/Manage Stock Replacement")],
            
            self::SA_REORDER                => [G::SS_ITEMS_A|1, trans("Reorder levels")],
            self::SA_ITEMSANALYTIC          => [G::SS_ITEMS_A|2, trans("Items analytical reports and inquiries")],
            self::SA_ITEMSVALREP            => [G::SS_ITEMS_A|3, trans("Inventory valuation report")],
            self::SA_SALES_ITEM_SEARCH      => [G::SS_ITEMS_A|4, trans("Sales: Stock Item Search")],

            /** Fixed assets */
            self::SA_ASSET                  => [G::SS_ASSETS_C|1, trans("Fixed Asset items add/edit")],
            self::SA_ASSETCATEGORY          => [G::SS_ASSETS_C|2, trans("Fixed Asset categories")],
            self::SA_ASSETCLASS             => [G::SS_ASSETS_C|4, trans("Fixed Asset classes")],
            self::SA_ASSET_IMPORT           => [G::SS_ASSETS_C|5, trans("Fixed Asset items import")],

            self::SA_ASSETSTRANSVIEW        => [G::SS_ASSETS|1, trans("Fixed Asset transactions view")],
            self::SA_ASSETTRANSFER          => [G::SS_ASSETS|2, trans("Fixed Asset location transfers")],
            self::SA_ASSETDISPOSAL          => [G::SS_ASSETS|3, trans("Fixed Asset disposals")],
            self::SA_DEPRECIATION           => [G::SS_ASSETS|4, trans("Depreciation")],
            self::SA_DEPRECIATION_CATEGORY  => [G::SS_ASSETS|5, trans("Depreciation Category Wise")],
            self::SA_ASSETALLOCATION        => [G::SS_ASSETS|6, trans("Assets Allocation & Deallocation")],

            self::SA_ASSETSANALYTIC         => [G::SS_ASSETS_A|1, trans("Fixed Asset analytical reports and inquiries")],

            /** Manufacturing */
            self::SA_BOM                    => [G::SS_MANUF_C|1, trans("Bill of Materials")],

            self::SA_MANUFTRANSVIEW         => [G::SS_MANUF|1, trans("Manufacturing operations view")],
            self::SA_WORKORDERENTRY         => [G::SS_MANUF|2, trans("Work order entry")],
            self::SA_MANUFISSUE             => [G::SS_MANUF|3, trans("Material issues entry")],
            self::SA_MANUFRECEIVE           => [G::SS_MANUF|4, trans("Final product receive")],
            self::SA_MANUFRELEASE           => [G::SS_MANUF|5, trans("Work order releases")],

            self::SA_WORKORDERANALYTIC      => [G::SS_MANUF_A|1, trans("Work order analytical reports and inquiries")],
            self::SA_WORKORDERCOST          => [G::SS_MANUF_A|2, trans("Manufacturing cost inquiry")],
            self::SA_MANUFBULKREP           => [G::SS_MANUF_A|3, trans("Work order bulk reports")],
            self::SA_BOMREP                 => [G::SS_MANUF_A|4, trans("Bill of materials reports")],

            /** Dimensions */
            self::SA_DIMTAGS                => [G::SS_DIM_C|1, trans("Dimension tags")],
            self::SA_DIMTRANSVIEW           => [G::SS_DIM|1, trans("Dimension view")],
            self::SA_DIMENSION              => [G::SS_DIM|2, trans("Dimension entry")],
            self::SA_DIMENSIONREP           => [G::SS_DIM|3, trans("Dimension reports")],

            /** Banking and general ledger */
            self::SA_ITEMTAXTYPE            => [G::SS_GL_C|1, trans("Item tax type definitions")],
            self::SA_GLACCOUNT              => [G::SS_GL_C|2, trans("GL accounts edition")],
            self::SA_GLACCOUNTGROUP         => [G::SS_GL_C|3, trans("GL account groups")],
            self::SA_GLACCOUNTCLASS         => [G::SS_GL_C|4, trans("GL account classes")],
            self::SA_QUICKENTRY             => [G::SS_GL_C|5, trans("Quick GL entry definitions")],
            self::SA_CURRENCY               => [G::SS_GL_C|6, trans("Currencies")],
            self::SA_BANKACCOUNT            => [G::SS_GL_C|7, trans("Bank accounts")],
            self::SA_TAXRATES               => [G::SS_GL_C|8, trans("Tax rates")],
            self::SA_TAXGROUPS              => [G::SS_GL_C|12, trans("Tax groups")],
            self::SA_FISCALYEARS            => [G::SS_GL_C|9, trans("Fiscal years maintenance")],
            self::SA_GLSETUP                => [G::SS_GL_C|10, trans("Company GL setup")],
            self::SA_GLACCOUNTTAGS          => [G::SS_GL_C|11, trans("GL Account tags")],
            self::SA_GLCLOSE                => [G::SS_GL_C|14, trans("Closing GL transactions")],
            self::SA_GLREOPEN               => [G::SS_GL_C|15, trans("Reopening GL transactions")], // see below
            self::SA_MULTIFISCALYEARS       => [G::SS_GL_C|13, trans("Allow entry on non closed Fiscal years")],

            self::SA_BANKTRANSVIEW          => [G::SS_GL|1, trans("Bank transactions view")],
            self::SA_GLTRANSVIEW            => [G::SS_GL|2, trans("GL postings view")],
            self::SA_EXCHANGERATE           => [G::SS_GL|3, trans("Exchange rate table changes")],
            self::SA_PAYMENT                => [G::SS_GL|4, trans("Bank payments")],
            self::SA_DEPOSIT                => [G::SS_GL|5, trans("Bank deposits")],
            self::SA_BANKTRANSFER           => [G::SS_GL|6, trans("Bank account transfers")],
            self::SA_RECONCILE              => [G::SS_GL|7, trans("Bank reconciliation")],
            self::SA_JOURNALENTRY           => [G::SS_GL|8, trans("Manual journal entries")],
            self::SA_BANKJOURNAL            => [G::SS_GL|11, trans("Journal entries to bank related accounts")],
            self::SA_BUDGETENTRY            => [G::SS_GL|9, trans("Budget edition")],
            self::SA_STANDARDCOST           => [G::SS_GL|10, trans("Item standard costs")],
            self::SA_ACCRUALS               => [G::SS_GL|12, trans("Revenue / Cost Accruals")],
            self::SA_LEAVE_ACCRUALS         => [G::SS_GL|13, trans("Post Employee Leave Accruals")],
            self::SA_GRATUITY_ACCRUALS      => [G::SS_GL|14, trans("Post Employee Gratuity Accruals")],
            self::SA_ALLOWJVPREVDATE        => [G::SS_GL|15, trans("Allow journal entry on previous date")],

            self::SA_GLANALYTIC             => [G::SS_GL_A|1, trans("GL analytical reports and inquiries")],
            self::SA_TAXREP                 => [G::SS_GL_A|2, trans("Tax reports and inquiries")],
            self::SA_BANKREP                => [G::SS_GL_A|3, trans("Bank reports and inquiries")],
            self::SA_GLREP                  => [G::SS_GL_A|4, trans("GL reports and inquiries")],
            self::SA_YBCDLYREP              => [G::SS_GL_A|5, trans("YBC - Consolidated Daily Report")],
            self::SA_PNLREP                 => [G::SS_GL_A|6, trans("Profit and Loss Statement")],
            self::SA_SUBLEDSUMMREP          => [G::SS_GL_A|7, trans("Sub-ledger Summary Report")],

            /** HRM related functionalities */
            self::HRM_VIEWTIMESHEET_OWN     => [G::SS_HRM|1, trans("View/Export Employees' Timesheet - (Own)")],
            self::HRM_VIEWTIMESHEET_DEP     => [G::SS_HRM|2, trans("View/Export Employees' Timesheet - (Dep)")],
            self::HRM_VIEWTIMESHEET_ALL     => [G::SS_HRM|3, trans("View/Export Employees' Timesheet - (All)")],
            self::HRM_EDITTIMESHEET_OWN     => [G::SS_HRM|4, trans("Edit Employees' Timesheet - (Own)")],
            self::HRM_EDITTIMESHEET_DEP     => [G::SS_HRM|5, trans("Edit Employees' Timesheet - (Dep)")],
            self::HRM_EDITTIMESHEET_ALL     => [G::SS_HRM|6, trans("Edit Employees' Timesheet - (All)")],
            self::HRM_PAYROLL               => [G::SS_HRM|7, trans("Process Payroll")],
            self::HRM_ADDSHIFT_OWN          => [G::SS_HRM|8, trans("Add Employees' Shifts - (Own)")],
            self::HRM_ADDSHIFT_DEP          => [G::SS_HRM|9, trans("Add Employees' Shifts - (Dep)")],
            self::HRM_ADDSHIFT_ALL          => [G::SS_HRM|10, trans("Add Employees' Shifts - (All)")],
            self::HRM_ADD_EMPLOYEE          => [G::SS_HRM|11, trans("Add New Employee")],
            self::HRM_ADD_EMP_LEAVE         => [G::SS_HRM|12, trans("Add Employee Leave - (Own With Workflow)")],
            self::HRM_EDIT_EMPLOYEE         => [G::SS_HRM|13, trans("Modify Basic Employee details")],
            self::HRM_EMP_SALARY            => [G::SS_HRM|14, trans("Increment/Decrement Employee Salary")],
            self::HRM_REDO_PAYSLIP          => [G::SS_HRM|15, trans("Redo a Processed Payslip")],
            self::HRM_UPD_STFMSTK_PSLP      => [G::SS_HRM|16, trans("Update staff mistake while processing payslip")],
            self::HRM_UPD_COMMISN_PSLP      => [G::SS_HRM|17, trans("Update Commission while processing payslip")],
            self::HRM_EDITSHIFT             => [G::SS_HRM|18, trans("Edit Employees' Shift")],
            self::HRM_FINALIZE_PAYROLL      => [G::SS_HRM|19, trans("Process Finalized Payroll")],
            self::HRM_JOB_UPDATE            => [G::SS_HRM|20, trans("Add Employee's Job Update")],
            self::HRM_ADD_EMP_CANCELATION   => [G::SS_HRM|21, trans("Add Employee's Cancelation")],
            self::HRM_HOLD_EMP_SALARY       => [G::SS_HRM|22, trans("Hold Employee's Salary")],
            self::HRM_HOLDED_EMP_SALARY     => [G::SS_HRM|23, trans("View Holded Employee's Salary")],
            self::HRM_SYNCATTD              => [G::SS_HRM|24, trans("Syncronize attendance")],
            self::HRM_UPLOAD_DOC            => [G::SS_HRM|25, trans("Upload Employee Document")],
            self::HRM_ADD_EMP_LEAVE_ALL     => [G::SS_HRM|26, trans("Add Employee Leave - (All With Workflow)")],
            self::HRM_DOC_RELEASE_REQ       => [G::SS_HRM|27, trans("Request document release (Own)")],
            self::HRM_DOC_RELEASE_REQ_ALL   => [G::SS_HRM|28, trans("Request document release (All)")],
            self::HRM_BULK_EMPLOYEE_UPLOAD  => [G::SS_HRM|29, trans("Upload employees in bulk from excel")],
            self::HRM_MANAGE_LEAVE_ADJUSTMENT => [G::SS_HRM|30, trans("Manage Leave Adjustment")],
            self::HRM_MANAGE_DOC            => [G::SS_HRM|31, trans("Manage Employee Document (All)")],
            self::HRM_MANAGE_DOC_OWN        => [G::SS_HRM|32, trans("Manage Employee Document (Own)")],
            self::HRM_EDIT_DOC              => [G::SS_HRM|33, trans("Edit Employee Document")],
            self::HRM_DELETE_DOC            => [G::SS_HRM|34, trans("Delete Employee Document")],
            self::HRM_TIMEOUT_REQUEST       => [G::SS_HRM|35, trans("Request For Employee Personal Timeouts (Own)")],
            self::HRM_TIMEOUT_REQUEST_ALL   => [G::SS_HRM|36, trans("Request For Employee Personal Timeouts (All)")],
            self::HRM_ADD_EMP_LEAVE_DEP     => [G::SS_HRM|37, trans("Add Employee Leave - (Dep With Workflow)")],
            self::HRM_MANAGE_DEDUCTION      => [G::SS_HRM|38, trans("Manage Employees Deduction")],
            self::HRM_MANAGE_DEDUCTION_ADMIN => [G::SS_HRM|39, trans("Manage Employees Deduction (Admin Privilege)")],
            self::HRM_MANAGE_REWARDS         => [G::SS_HRM|40, trans("Manage Employees Rewards")],
            self::HRM_MANAGE_REWARDS_ADMIN   => [G::SS_HRM|41, trans("Manage Employees Rewards (Admin Privilege)")],
            self::HRM_MANAGE_GENERAL_REQUEST => [G::SS_HRM|42, trans("Manage General Requests (Own)")],
            self::HRM_MANAGE_GENERAL_REQUEST_ALL => [G::SS_HRM|43, trans("Manage General Requests (All)")],
            self::HRM_TASK_PERFORMER_DETAILS    => [G::SS_HRM|44, trans("Display Action Performer Details in Task Management")],

            self::HRM_VIEWATDMETRICS_OWN        => [G::SS_HRM_A|50, trans("View Attendance Metrics (Own)")],
            self::HRM_VIEWATDMETRICS_DEP        => [G::SS_HRM_A|51, trans("View Attendance Metrics (Dep)")],
            self::HRM_VIEWATDMETRICS_ALL        => [G::SS_HRM_A|52, trans("View Attendance Metrics (All)")],
            self::HRM_MODIFYATDMETRICS          => [G::SS_HRM_A|53, trans("Modify Attendance Metrics")],
            self::HRM_MODIFYATDMETRICS_OWN      => [G::SS_HRM_A|54, trans("Modify Attendance Metrics (Own)")],
            self::HRM_VIEWEMPLOYEES             => [G::SS_HRM_A|55, trans("View Employee")],
            self::HRM_VIEWPAYSLIP               => [G::SS_HRM_A|56, trans("View Payslip")],
            self::HRM_VIEW_END_OF_SERVICE       => [G::SS_HRM_A|57, trans("View End of Service Calculation")],
            self::HRM_SALARY_CERTIFICATE        => [G::SS_HRM_A|58, trans("View Salary Certificate")],
            self::HRM_SALARY_TRANSFER_LETTER    => [G::SS_HRM_A|59, trans("View Salary Transfer Letter")],
            self::HRM_VIEWPAYSLIP_OWN           => [G::SS_HRM_A|60, trans("View Payslip (Own)")],
            self::HRM_VIEWPAYSLIP_DEP           => [G::SS_HRM_A|61, trans("View Payslip (Dep)")],
            self::HRM_VIEWPAYSLIP_ALL           => [G::SS_HRM_A|62, trans("View Payslip (All)")],
            // self::HRM_EMPLOYEE_DOCUMENT_VIEW    => [G::SS_HRM_A|63, trans("View Employee Document")],
            self::HRM_EMPLOYEE_SHIFT_VIEW_OWN   => [G::SS_HRM_A|64, trans("View Employee Shift Report (Own)")],
            self::HRM_EMPLOYEE_SHIFT_VIEW_DEP   => [G::SS_HRM_A|65, trans("View Employee Shift Report (Dep)")],
            self::HRM_EMPLOYEE_SHIFT_VIEW_ALL   => [G::SS_HRM_A|66, trans("View Employee Shift Report (All)")],
            self::HRM_EMPLOYEE_LEAVE_REPORT     => [G::SS_HRM_A|67, trans("View Employee Leave Report")],
            self::HRM_EMPLOYEE_LEAVE_REPORT_OWN => [G::SS_HRM_A|68, trans("View Employee Leave Report (Own)")],
            self::HRM_VIEWEMPLOYEES_DEP         => [G::SS_HRM_A|69, trans("View Employees (Dep)")],
            self::HRM_VIEWEMPLOYEES_ALL         => [G::SS_HRM_A|70, trans("View Employees (All)")],
            self::HRM_EMPLOYEE_LEAVE_REPORT_DEP => [G::SS_HRM_A|71, trans("View Employee Leave Report (Dep)")],
            self::HRM_EMP_DEDUCTION_REWARD      => [G::SS_HRM_A|72, trans("View Employees Deduction / Rewards")],
            self::HRM_EMP_DEDUCTION_REWARD_OWN  => [G::SS_HRM_A|73, trans("View Employees Deduction / Rewards (Own)")],
            self::HRM_EMP_LEAVE_DETAIL_REPORT     => [G::SS_HRM_A|74, trans("View Employees Leave Detail Report (All)")],
            self::HRM_EMP_LEAVE_DETAIL_REPORT_OWN => [G::SS_HRM_A|75, trans("View Employees Leave Detail Report (Own)")],
            self::HRM_EMP_LEAVE_DETAIL_REPORT_DEP => [G::SS_HRM_A|76, trans("View Employees Leave Detail Report (Dep)")],

            self::WARNINGCATEGORY 	        => [G::SS_HRM_C|1, trans("Warning Category")],
            self::WARNINGGRADE 		        => [G::SS_HRM_C|2, trans("Warning Grade")],
            self::HRM_SETUP                 => [G::SS_HRM_C|3, trans("HR setup")],
            self::HRM_MANAGE_SHIFT          => [G::SS_HRM_C|4, trans("Add/Manage Shifts")],
            self::HRM_MANAGE_DESIGNATION    => [G::SS_HRM_C|5, trans("Manage Designations")],
            self::HRM_MANAGE_DEPARTMENT     => [G::SS_HRM_C|6, trans("Manage Departments")],
            self::HRM_MANAGE_PAY_ELEMENTS   => [G::SS_HRM_C|7, trans("Manage Pay Elements")],
            self::HRM_MANAGE_COMPANY        => [G::SS_HRM_C|8, trans("Manage Company")],
            self::HRM_MANAGE_HOLIDAY        => [G::SS_HRM_C|9, trans("Manage Public Holidays")],
            self::HRM_MANAGE_LEAVE_CARRY_FORWARD => [G::SS_HRM_C|10, trans("Manage Leave Carry Forward Limit")],
            self::HRM_MANAGE_PENSION_CONFIG => [G::SS_HRM_C|11, trans("Manage Employee Pension Configuration")],
            self::HRM_MANAGE_GENERAL_REQUEST_TYPE => [G::SS_HRM_C|12, trans("Manage General Request Types")],

            /** CRM related */
            self::SA_RECEPTION_REPORT       => [G::SS_CRM_A|1, trans("View Customer reception report")],
            self::SA_RECEPTION	            => [G::SS_CRM_A|2, trans("Reception")],
            self::SA_CUSTOMERS_VISITED      => [G::SS_CRM_A|3, trans("Customers List")],
            self::SA_RECEPTION_INVOICE      => [G::SS_CRM_A|4, trans("Reception Invoice Report")],

            /** Header menu configuration */
            self::HEAD_MENU_SALES           => [G::SS_HEAD_MENU|1, trans("Sales Menu")],
            self::HEAD_MENU_PURCHASE        => [G::SS_HEAD_MENU|2, trans("Purchase Menu")],
            self::HEAD_MENU_ASSET           => [G::SS_HEAD_MENU|3, trans("Fixed Asset Menu")],
            self::HEAD_MENU_FINANCE         => [G::SS_HEAD_MENU|4, trans("Finance Menu")],
            self::HEAD_MENU_HR              => [G::SS_HEAD_MENU|5, trans("HR Menu")],
            self::HEAD_MENU_REPORT          => [G::SS_HEAD_MENU|6, trans("Report Menu")],
            self::HEAD_MENU_SETTINGS        => [G::SS_HEAD_MENU|7, trans("Settings Menu")],
            self::HEAD_MENU_LABOUR          => [G::SS_HEAD_MENU|8, trans("Labour Menu")],

            /** Finance related */
            self::SA_CASH_HANDOVER_ALL      => [G::SS_FINANCE|1, trans("Place cash handover request for all")],
            self::SA_CASH_HANDOVER          => [G::SS_FINANCE|2, trans("Place cash handover request")],
            self::SA_CASH_HANDOVER_LIST     => [G::SS_FINANCE|3, trans("See cash handover request list")],

            self::SA_CASH_HANDOVER_INQ      => [G::SS_FINANCE_A|1, trans("Cash handover report")],

            /** Dashboard Sections */
            self::SA_DSH_LAST_10_DAYS 	    => [G::SS_DASHBOARD|1, trans("Sales - Last 10 days")],
            self::SA_DSH_TOP_5_EMP		    => [G::SS_DASHBOARD|2, trans("Top 5 Employees Service Count")],
            self::SA_DSH_TOP_5			    => [G::SS_DASHBOARD|3, trans("Top 5 Sales Category Count")],
            self::SA_DSH_FIND_INV		    => [G::SS_DASHBOARD|4, trans("Find Invoices")],
            self::SA_DSH_TODAYS_INV		    => [G::SS_DASHBOARD|5, trans("Today's Invoices")],
            self::SA_DSH_CAT_REP		    => [G::SS_DASHBOARD|6, trans("Today's Category Report")],
            self::SA_DSH_TOP_10_CUST	    => [G::SS_DASHBOARD|7, trans("Top 10 Customers")],
            self::SA_DSH_TRANS			    => [G::SS_DASHBOARD|8, trans("Todays Transaction")],
            self::SA_DSH_TRANS_ACC		    => [G::SS_DASHBOARD|9, trans("Accumulated Transaction")],
            self::SA_DSH_BNK_AC			    => [G::SS_DASHBOARD|10, trans("Bank Accounts")],
            self::SA_DSH_COLL_BD		    => [G::SS_DASHBOARD|11, trans("Total Collection Breakdown")],
            self::SA_DSH_AC_CLOSING_BAL	    => [G::SS_DASHBOARD|12, trans("Account Closing Balance")],
            self::SA_DSH_HRM			    => [G::SS_DASHBOARD|13, trans("HRM Dashboard")],
            self::SA_DHS_CUST_BAL		    => [G::SS_DASHBOARD|14, trans("Customer Balances")],
            self::SA_DSH_CUST_BAL_AMT	    => [G::SS_DASHBOARD|15, trans("Customer Balance Till Date")],
            self::SA_DHS_DEP_SALES		    => [G::SS_DASHBOARD|16, trans("Department wise Sales Breakdown")],
            self::SA_DSH_DEP_SALES_MNTH     => [G::SS_DASHBOARD|17, trans("Department wise Monthly Sales Breakdown")],
            self::SA_DSH_TODAYS_REC         => [G::SS_DASHBOARD|18, trans("Today's Receipt")],

            /* Labour related */
            self::SA_CREATE_AGENT           => [G::SS_LABOUR|1, trans("Create & Update New Agent")],
            self::SA_LBR_CREATE             => [G::SS_LABOUR|2, trans("Create & Update New Labour")],
            self::SA_LBR_CONTRACT           => [G::SS_LABOUR|3, trans("Create & Update New Contract")],
            self::SA_MAID_RETURN            => [G::SS_LABOUR|4, trans("Maid Return Request")],
            self::SA_MAID_REPLACEMENT       => [G::SS_LABOUR|5, trans("Maid Replacement Request")],
            self::SA_LBR_CONTRACT_INSTALLMENT => [G::SS_LABOUR|6, trans("Convert Labour Contract to Installments")],
            self::SA_INSTALLMENT_DELETE     => [G::SS_LABOUR|7, trans("Installment Delete")],
            
            self::SA_AGENT_LIST             => [G::SS_LABOUR_A|1, trans("Agent Inquiry")],
            self::SA_LBR_VIEW               => [G::SS_LABOUR_A|2, trans("Labour Inquiry")],
            self::SA_LBR_CONTRACT_INQ       => [G::SS_LABOUR_A|3, trans("Labour Contract Inquiry")],
            self::SA_MAID_MOVEMENT_REPORT   => [G::SS_LABOUR_A|4, trans("Maid Movement Report")],
            self::SA_LBR_INSTALLMENT_REPORT => [G::SS_LABOUR_A|5, trans("Installment Report")],
            
        ];

        // if (!@$SysPrefs->allow_gl_reopen) is true
        unset($this->areas[self::SA_GLREOPEN]);
    }

    /**
     * Returns the array of permissions
     *
     * @return array
     */
    public function toArray()
    {
        return $this->areas;
    }

    /**
     * Determine if a given offset exists.
     *
     * @param  string  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($this->areas[$key]);
    }

    /**
     * Get the value at a given offset.
     *
     * @param  string  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->areas[$key];
    }

    /**
     * Set the value at a given offset.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     *
     * @throws Exception
     */
    public function offsetSet($key, $value)
    {
        // we don't allow to set value directly
        throw new Exception("Trying to overwrite system permission directly");
    }

    /**
     * Unset the value at a given offset.
     *
     * @param  string  $key
     * @return void
     *
     * @throws Exception
     */
    public function offsetUnset($key)
    {
        // we don't allow to unset a value either
        throw new Exception("Trying to overwrite system permission directly");
    }

    /**
     * Returns the code for the given permission key
     *
     * @param string $key
     * @return int|null
     */
    public function getCode($key) {
        if (!isset($this->areas[$key])) {
            return null;
        }

        return $this->areas[$key][0];
    }
}
