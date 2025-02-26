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

use App\Models\MetaReference;

include_once($path_to_root . "/includes/db/class.reflines_db.inc");
include_once($path_to_root . "/admin/db/fiscalyears_db.inc");
include_once($path_to_root . "/includes/types.inc");
//---------------------------------------------------------------------------------------------
// 2.4 - further changes toward removing refs table introduced:
//	. all transactions now have references stored in trans table.
//  . all reference related moved to class (is_new_reference yet preserved)
//	. template based reflines implemented
//
// FIXME:
//		- implement refline field in all transaction tables (obsoletes not always accurate find_refline_id)
//		- remove save() and restore_last() - for now preserved for reflines without placeholder
//		- see fixmes below
//		- remove refs table and create view instead (need e.g. CREATE VIEW support in db_import/db_export)

$refline_options = refline_options();
$refline_placeholders = refline_placeholders();

class references 
{
	var $reflines;
	
	function __construct()
	{
		$this->reflines = new reflines_db();
	}

	function _legacy_line($refline)
	{
		return strpbrk($refline['pattern'], '{}') == false;
	}

	function _parse_next($type, $pattern, $context=null, $persist = false)
	{
		return MetaReference::parseNext($type, $pattern, $context, $persist);
	}

    function get_blocks($pattern, $context = null)
    {
        return MetaReference::getBlocks($pattern, $context);
    }

	function _get($type, $trans_no=0, $ref=null)
	{
		return MetaReference::get($type, $trans_no, $ref);
	}

	function is_new_reference($ref, $type, $trans_no=0)
	{
		return MetaReference::isNewReference($ref, $type, $trans_no);
	}

    /**
     * Determines if the reference is unique
     * 
     * **Important!** Only ever call this function inside the same transaction where the write operation is being performed.
     *
     * @param string $ref
     * @param string $ref_backup
     * @param int $type
     * @param integer $trans_no
     * @param array|null $context
     * @param array|null $line
     * @return boolean
     */
    function ensure_uniqueness($ref, $ref_backup, $type, $trans_no = 0, $context = null, $line = null)
    {
        if (!$line) {
            $line = $this->reflines->find_refline_id($ref, $type);
        }

        if (empty($line)) {
            throw new InvalidArgumentException("Specified reference defenition could not be found");
        }

        $refline = $this->reflines->get($line);
        $pattern = $refline['prefix'].$refline['pattern'];
        $isNewTransaction = $trans_no == 0;

        if ($isNewTransaction && MetaReference::canGetNextReference($type)) {
            // Check if the user didn't modify manually
            if ($ref_backup == $ref) {
                $nextRef = $this->get_next($type, $line, $context, true);
                return $nextRef == $ref;
            }

            [$template] = $this->get_blocks($pattern, $context);
            $this->preg_match($ref, $type, $context, $line, $match);
            $nextSeq = MetaReference::getNextSeqForUpdate($type, $template);
            // If the user modified manually, Check if there is already references of same kind in database
            if ($nextSeq) {
                // If its there, check the reference is not from future and is not used.
                return ($match[1] < $nextSeq) && $this->is_new_reference($ref, $type);
            }

            // If the user modified manually and there is no such reference of same kind, it means this
            // is the first of its kind, so start the next reference from the next sequence number
            MetaReference::store($type, $template, ((int)$match[1]) + 1);
            return true;
        }

        // fall back
        return $this->is_new_reference($ref, $type, $trans_no);
    }

	//
	// Get default reference for new transaction.
	//
	function get_next($type, $line=null, $context=null, $persist = false) 
	{
        return MetaReference::getNext($type, $line, $context, $persist);
	}

	/**
 	*	Normalize reference to format allowed by refline (optionally selected by prefix).
 	*	FIXME: currently this is fake function which return either input reference or 
 	*	next reference when no line has been recognized.
	**/
	function normalize($ref, $type, $context, $line=null)
	{
		if (!isset($type)) // inquiries
			return $ref;

		if (!$line)
			$line = $this->reflines->find_refline_id($ref, $type);

		return $this->is_valid($ref, $type, $context, $line) ? $ref : $this->get_next($type, $line, $context);
	}

	//
	// Check reference is valid before add/update transaction.
	// FIXME: does not check leading zeros in number
	//
 	function is_valid($reference, $type, $context=null, $line=null)
	{
		return MetaReference::isValid($reference, $type, $context, $line);
	}

    function preg_match($reference, $type, $context=null, $line=null, &$match=null) {
        return MetaReference::pregMatch($reference, $type, $context, $line, $match);
    }

	//
	//	Save reference (and prepare next) on write transaction.
	//
	function save($type, $id, $reference, $line = null) 
	{
		return MetaReference::saveReference($type, $id, $reference, $line);
	}
	//
	// Restore previous reference (if possible) after voiding transaction.
	//
	function restore_last($type, $id)
	{
		// get refline for removed document reference
		$reference = get_reference($type, $id);
		$line = $this->reflines->find_refline_id($reference, $type);
		$refline = $this->reflines->get($line);

		if ($this->_legacy_line($refline)) // legacy code used with simple templates
		{
			$last = $this->_increment($this->get_next($type, $line), true); // find last reference used in this line
			if ($reference == $last)
			{
				// save last reference as next
			    $sql = "UPDATE ".TB_PREF."reflines SET pattern=SUBSTRING(" . db_escape(trim($last)) .", LENGTH(`prefix`)+1)"
					. " WHERE trans_type = ".db_escape($type) . " AND `id`=".db_escape($line);

				db_query($sql, "The next transaction ref for $type could not be updated");
			}
		}
	}

	//-----------------------------------------------------------------------
	//
	//	Increments (or decrements if $back==true) reference template
	//
	function _increment($reference, $back=false) 
	{
		// Legacy code used when no palceholder is in use:
		//  WA036 will increment to WA037 and so on.
       	// If $reference contains at least one group of digits,
        // extract first didgits group and add 1, then put all together.
        // NB. preg_match returns 1 if the regex matches completely 
        // also $result[0] holds entire string, 1 the first captured, 2 the 2nd etc.
        //
        if (preg_match('/^(\D*?)(\d+)(.*)/', $reference, $result) == 1) 
        {
			list($all, $prefix, $number, $postfix) = $result;
			$dig_count = strlen($number); // How many digits? eg. 0003 = 4
			$fmt = '%0' . $dig_count . 'd'; // Make a format string - leading zeroes
			$val = intval($number + ($back ? ($number<1 ? 0 : -1) : 1));
			$nextval =  sprintf($fmt, $val); // Add one on, and put prefix back on

			return $prefix.$nextval.$postfix;
        }
        else 
            return $reference;
	}

}

//----------------------------------------------------------------------------
//
//	Check if reference was not used so far (for other transaction than $trans_no)
//
function is_new_reference($ref, $type, $trans_no=0)
{
	global $Refs;

	return $Refs->is_new_reference($ref, $type, $trans_no);
}

function get_reference($type, $trans_no)
{
	global $Refs;

	return $Refs->_get($type, $trans_no);
}
