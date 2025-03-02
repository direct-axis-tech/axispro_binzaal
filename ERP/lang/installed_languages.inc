<?php

/* How to make new entries here for non-packaged languages:

-- 'code' should match the name of the directory for the language under \lang
.-- 'name' is the name that will be displayed in the language selection list (in Users and Display Setup)
-- 'rtl' only needs to be set for right-to-left languages like Arabic and Hebrew
-- 'encoding' used in translation file
-- 'version' always set to '' for manually installed languages.
-- 'path' installation path related to FA root (e.g. 'lang/en_US').
*/


$installed_languages = array (
  0 => 
  array (
    'code' => 'C',
    'name' => 'English',
    'encoding' => 'utf-8',
  ),
  1 => 
  array (
    'name' => 'Arabic',
    'package' => 'ar_EG',
    'code' => 'ar_EG',
    'encoding' => 'utf-8',
    'version' => '2.4.1-3',
    'path' => 'lang/ar_EG',
    'rtl' => true,
  ),
  2 => 
  array (
    'name' => 'Hindi',
    'package' => 'hi_IN',
    'code' => 'hi_IN',
    'encoding' => 'utf-8',
    'version' => '2.4.2-2',
    'path' => 'lang/hi_IN',
  ),
);

$dflt_lang = 'C';
