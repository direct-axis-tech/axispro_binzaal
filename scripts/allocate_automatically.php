<?php 

$GLOBALS['path_to_root'] = '../ERP';

require_once __DIR__ . '/../ERP/includes/console_session.inc';
require_once __DIR__ . '/../ERP/includes/ui/allocation_cart.inc';
require_once __DIR__ . '/../ERP/sales/includes/db/custalloc_db.inc';
require_once __DIR__ . '/../ERP/admin/db/before_void_db.inc';

cust_auto_allocate();