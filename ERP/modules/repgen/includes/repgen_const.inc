<?php
/* Include Datei repgen_const.inc for PHP Report Generator
   Bauer, 22.1.2002
   Version 0.2
*/

/*   REPGEN constants. For other language copy the file <language>.inc over repgen_const.inc
*/
$GetText->add_domain($_SESSION['language']->code, $path_to_root.'/modules/repgen/lang');

// used in repgen_main
define("CREATE",trans("Create a Report")); // button create
define("SELECT",trans("Select a report for work")); // button select
define("FIRST",trans("we need some data, please insert:"));

// used in repgen_select
define ("CHANGE" , trans("Edit"));  // Constant for change button value
define ("DELETE" , trans("Delete")); // Constant for delete Butteon value
define ("COPY" , trans("Copy")); // Constant for copy Button value
define ("COPYV" , trans("Cp of")); // Constant for copy-text
define ("SEL_SELECT",trans("If you want to change or delete a stored report, then
choose beneath."));
define ("SEL_COLOR",trans("(Green are the Blocks, orange the Functions and gray the reports shown.)"));
                                           // table heads
define ("SHORT" , trans("Shortname"));      // Head of 1. col
define ("LONG" , trans("Long name"));       // Head of 2. col
define ("AUTHOR" , trans("Author"));               // Head od 3. col
define ("CREATIONDATE" , trans("Creation date"));    // Head of 4. col
define ("DESCRIPT",trans("Selection of the Reports"));   // Head of page
define ("LOGOUT",trans("Exit the program"));  // Logout of program
define ("NEW_",trans("Create a new report"));  // Create new Report
define ("NEWBLOCK",trans("Create a new Block"));  // Create new Block
define ("NEWFUNCT",trans("Create a new Function"));  // Create new function

// used in repgen_create
define ("CREATE_BLOCK",trans("Common Data of a Block")); // common data of block
define ("ALTER_BLOCK",trans("Common Data of the Block ")); // common data of block
define ("ID_BLOCK", trans("ID-Number of the Block"));                  // Label of ID
define ("CREATE_FUNCT",trans("Common Data of a Function ")); // common data of function
define ("ALTER_FUNCT",trans("Common Data of the Function ")); // common data of function
define ("ID_FUNCT", trans("ID-Number of the Function"));                  // Label of ID

define ("CREATE_HEAD",trans("Common data of the report")); // common data of report
define ("ALTER_HEAD",trans("Common data of the old report ")); // common data of report
define ("ID" , trans("ID-Number of the report"));                  // Label of ID
define ("DATE" , trans("Creation Date"));                     // Label of DAte
define ("PRINT_FORMAT" , trans("Printer Orientation"));        // Label of Printer
define ("REPORT_TYPE" , trans("Report type"));              // Label of Report type
define ("SQL" , trans("SQL-Statement for record selection"));         // Label of Paper-width
define ("GROUP_NAME" , trans("Name of the group field"));         // Label of Group
define ("GROUP_TYPE" , trans("Group type"));              // Label of Group type
define ("GROUP_ERROR" , trans("Error: Group type = 'New Page on group change' and no field!"));  // Error message of missing Group field
define ("NO_PAGE", trans("No new page when group changes")); // Label of nopage-select
define ("NEW_PAGE", trans("New Page on group change")); // Label of newpage-select
define ("TEST_SEL" , trans("Test the SQL-Statement"));         // Value of Test-SQL Statement-Button
define ("PAGE_REC", trans("Page per record"));                  // Label of report_type single
define ("LINE_REC", trans("Line per record"));                  // Label of report_type class
define ("GRID_REC", trans("Line per record with grid"));                  // Label of report_type classtable
define ("BEAM_REC", trans("Line per record with bar"));                  // Label of report_type classbeam
define ("BEAMGRID_REC", trans("Line per record with bar and grid"));     // Label of report_type classgrid
define ("SELECT_CR", trans("Back to Report Selection(without storage)")); // Label of back-button
define ("PAGE_STORE" , trans("Store and back to Report Selection"));         // Value of Store-Button
define ("PAGE_TEST" , trans("Test of the Function"));         // Value of Test-Button
define ("PAGE_STRINGS" , trans("Page Definition for Strings"));         // Value of button page_strings
define ("PAGE_GRAPHICS" , trans("Page Definition for Graphics"));         // Value of button page_graphics
define ("PHP_ERROR", trans("PHP-Error in Function: "));            // Error message of PHP-Error
define ("PHP_OK", trans("PHP: Function is OK. The result is: "));           // Message 'ok' of function
define ("ERR_FIELD", trans("The field \$field is not part of the function."));           // Message missing $field
define ("A4FORMAT1", trans("Paper Format"));              // Paperformat
define ("ID_ERROR", trans("ID-Number and SQL-statement must not be empty, or the short-name has been used before !!!!")); // Error Message: ID missing
define ("ID_ERROR_BLOCK", trans("The Short-Name of the Block  has been used before!!!!")); // Error Message: ID missing
define ("ERROR_FUNC", trans("The name of the Function must be equal to the short-name:")); // Error Message: Functionname <> shortname
define ("FUNC_DECL", trans("A Function has to be declared, which gives a String as <BR> as a result. The Function name has to be equal to the short-name!<BR>Please Test the function!<BR> If there is a fatal run-time error (e.g. redeclaration of a function) the system may die!!"));
define ("NOTSTORED",trans(" The values have NOT been stored!")); // Error -> not stored
define ("SQL_ERROR",trans(" has an Error"));  // SQL Error message
define ("SQL_ERROR1",trans(" SQL Error: SQL Statement is empty!"));  // SQL Error message
// used in repgen_test_sel
define ("SQL_STATEMENT",trans("You have written this SQL-Statement: "));
define ("SQL_ERG",trans("The first 10 records of the result set are: "));

// used in repgen_strings and repgen_graphics
define ("ITEM_DEF",trans("Definition of a new Item of the report "));
define ("ITEM_CHAR",trans("for Characters "));
define ("ITEM_LINE",trans("for Graphics "));
define ("ALTERNATIVE",trans( " Or as Alternative in Mode Detail "));
define ("ORDER", trans(" Order"));
define ("NUMBER",trans("Number of Char"));
define ("AND_", trans("AND"));
define ("ALIGN", trans("Alignment"));
define ("ELEMENT",trans("Elementtype"));
define ("VALUE_", trans("Value"));
define ("WIDTH", trans("Width in points"));
define ("DBFELD",trans("DB-Field"));
define ("ITEM_HEAD",trans("Table of stored Items "));
define ("OPTIONAL",trans("Optional"));
define ("SUBSTRING",trans("Substring"));
define ("FROM",trans("from"));
define ("TO",trans("to"));
define ("TOTAL",trans("Use only, if DB-Field is numeric"));

       //        Headers of the table columns
define ("IT_TYP",trans("Type "));
define ("IT_ART",trans("Mode "));
define ("IT_FONT",trans("Font "));
define ("IT_FONT_SIZE",trans("FontSize "));
define ("IT_ORD",trans("Order"));
define ("IT_LEN",trans("Number of Ch. "));
define ("IT_X1",trans("X "));
define ("IT_X2",trans("X2/Width "));
define ("IT_Y1",trans("Y "));
define ("IT_Y2",trans("Y2/Height "));
define ("IT_WIDTH",trans("Line-width"));
define ("IT_STRING",trans("String/Fieldname "));
define ("IT_LINE",trans("Line-width "));
define ("IT_STORE",trans("                 Store Item                   "));
define ("IT_BACK"  ,trans("         Back to select a report             "));
define ("IT_PRINT", trans("Show the report"));    // Button druck
define ("IT_HELP", trans("X1/Y1 and X2/Y2 are the ends of a line. <BR> X1/Y1 and width/height give the dimensions of a rectangle."));
define ("ERROR_EMPTY",trans("Please insert a value in X or the Number of Ch. and Type !"));
define ("ERROR_EMPTY_LINE",trans("Insert please values for X1/Y1 and X2/Y2 and line-width!"));
define ("ERROR_ORDER",trans("A value for Order etc. must only be inserted, if Mode=Detail and no value for Y has been given!"));
define ("ERROR_XY",trans("Please insert a value for X and Y!"));
define ("ERROR_MIX",trans("In lines of mode 'Detail' the items with X/Y-values and Orders must not be mixed!"));
define ("ERROR_VALUE",trans("No Value for Item-type 'String' has been declared!"));
define ("ERROR_TO",trans("Error in Substring: To-Value is lower than From-Value!"));
define ("ERROR_TOTAL",trans("Error in Total: Total can only be used with mode 'DE'!"));

define ("BGCOLOR1", "#CCCCCC");   // Background1 of tables
define ("BGCOLOR2", "#DDDDDD");   // Background2 of tables
define ("BGCOLORH", "#D3DCE3");   // Background of Header of tables

// used in repgen_del
define ("BACK" , trans("Back to report selection(without deletion)"));  // Constant for back button value
define ("DEL_BACK" , trans("D E L E T E")); // Constant for delete Button value

define("DEL_REPORT",trans("Do you really want to delete the report %s ?"));
define("DEL_BLOCK", trans("Do you really want to delete the block %s ?"));
define("DEL_FUNC",trans("Do you really want to delete the function %s ?"));

