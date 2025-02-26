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

/**
 * Security sections groups various areas on both functionality and privilege levels.
 * Often analytic inquires are available only for management, and configuration
 * for administration or management staff. This is why we have those three
 * section type inside near every FA module.
 *
 * Section codes 0-99 are reserved for core FA functionalities.
 * Every security section can contain up to 256 different areas.
 * External modules can extend security roles system by adding rows to 
 * $security_sections and $security_areas using section codes >=100.
 * Security areas and sections created by extension modules/plugins
 * have dynamically assigned 3-byte integer codes. The highest byte is zero
 * for sections/areas defined in this file, and extid+1 for those defined 
 * by extensions 
 */
$security_sections = app(App\PermissionGroups::class);

/**
 * This table stores security areas available in FA. 
 * Key is area identifier used to check user rights, values are
 * codes stored for each role in security_roles table and description used
 * in roles editor.
 *
 * Set of allowed access areas codes is retrieved during user login from
 * security_roles table, and cached in user profile.
 *
 * Special value 'SA_OPEN' is used for publicly available pages like login/logout.
 */
$security_areas = app(App\Permissions::class);

/*
	This function should be called whenever we want to extend core access level system
	with new security areas and/or sections i.e.: 
	. on any page with non-standard security areas
	. in security roles editor
	The call should be placed between session.inc inclusion and page() call.
	Up to 155 security sections and 155 security areas for any extension can be installed.
*/
function add_access_extensions()
{
	global $security_areas, $security_sections, $installed_extensions;

	foreach($installed_extensions as $extid => $ext) {
		$accext = hook_invoke($ext['package'], 'install_access', $dummy);
		if ($accext == null) continue;

		$scode = 100;
		$acode = 100;
		$extsections = $accext[1];
		$extareas = $accext[0];
		$extcode = $extid<<16;
		
		$trans = array();
		foreach($extsections as $code =>$name) {
			$trans[$code] = $scode<<8;
			// reassign section codes
			$security_sections[$trans[$code]|$extcode] = $name;
			$scode++;
		}
		foreach($extareas as $code => $area) {
			$section = $area[0]&0xff00;
			// extension modules:
			// if area belongs to nonstandard section
			// use translated section codes and
			// preserve lower part of area code
			if (isset($trans[$section])) {
				$section = $trans[$section];
			} 
				// otherwise assign next available
				// area code >99

//            display_error(print_r($section ,true));

			$area[0] = $extcode | $section | ($acode++);
			$security_areas[$code] = $area;
		}
	}
}

function check_edit_access($name)
{
	global $input_security;

	$access = @$input_security[$name];

	if (!$access)
		$access = @$input_security['']; // default access level

	if (!$access)
		return true; // if constraint is not defined edit access is allowed

	return  user_check_access($access);
}
/*
	Returns POST value or null if edit access to $name control is forbidden.
*/
function access_post($name, $dflt=null)
{
	if (!check_edit_access($name))
		return $dflt;
	else
		return get_post($name, $dflt);
}

/*
	Returns numeric input value or null if edit access to $name control is forbidden.
*/
function access_num($name, $dflt=null)
{
	if (!check_edit_access($name))
		return $dflt;
	else
		return input_num($name, $dflt);
}
