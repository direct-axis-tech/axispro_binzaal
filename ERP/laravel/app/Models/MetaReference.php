<?php

namespace App\Models;

use App\Models\Accounting\BankTransaction;
use App\Models\Sales\CustomerTransaction;
use App\Models\Sales\SalesOrder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Accounting\JournalTransaction;
use App\Models\Inventory\StockMove;
use App\Models\Inventory\StockReplacement;
use App\Models\Labour\Contract;
use App\Models\Sales\SalesOrderDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LogicException;
use PDO;

class MetaReference extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '0_meta_references';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Calculates the next reference no
     * 
     * **Note**:   This function only works for frontaccounting core.  
     * Since the write operation needs to be done inside a transaction,
     * we cannot use larave DB functions because - laravel and 
     * frontaccounting both maintain their own connections. Laravel
     * uses PDO while frontaccounting use mysqli
     * 
     * @param int $type
     * @param string $parts The deconstructed array of template parts
     * @param bool $persist Determines if the reference should be persisted.
     * @return int
     * 
     * @throws InvalidArgumentException
     */
    public static function getNextReference($type, $parts, $persist = false, $offset = 0) {
        if (!static::canGetNextReference($type)) {
            throw new InvalidArgumentException("$type is not yet implemented");
        }

        [$template, $prefix, $numericPlaceHolder, $postfix] = $parts;

        if ($persist == true) {
            // If the next reference is being fetched inside a transaction, Lets ensure nobody can have access to it
            $result = static::runQuery(
                "UPDATE `0_meta_references` SET `next_seq` = `next_seq` + 1 WHERE `type` = {$type} AND `template` = '{$template}'",
                "Could not increment the reference"
            );

            // Lazy insertion
            if (!$result) {
                static::store($type, $template, static::parseMaxValue($type, $parts[1], $parts[3]) + 2);
            }
        }

        // Retreive the next reference number
        $nextSeq = static::runQuery(
            "SELECT `next_seq` FROM `0_meta_references` WHERE `type` = {$type} AND `template` = '{$template}'",
            "Could not get next transaction number"
        )['next_seq'] ?? 0;

        // if already persisted, decrement to get the correct reference
        $persist && --$nextSeq;

        // if its not at all present initialize to 1
        if ($nextSeq < 1) {
            $nextSeq = static::parseMaxValue($type, $parts[1], $parts[3]) + 1;
            static::store($type, $template, $nextSeq);
        }

        if (!$persist) {
            $nextSeq += $offset;
        }

        // pad 0 to the left to make it the correct length
        $nextSeq = str_pad($nextSeq, strlen($numericPlaceHolder), "0", STR_PAD_LEFT);
        
        return $prefix . $nextSeq . $postfix;
    }

    /**
     * Determines if the next transaction number can be obtained without a full table scan
     *
     * @param int $type
     * @return boolean
     */
    public static function canGetNextReference($type) {
        $implemented = [
            CustomerTransaction::INVOICE,
            CustomerTransaction::PAYMENT,
            CustomerTransaction::REFUND,
            BankTransaction::CREDIT,
            BankTransaction::DEBIT,
            Contract::TEMPORARY_CONTRACT,
            Contract::CONTRACT,
            StockMove::STOCK_RETURN,
            StockReplacement::STOCK_REPLACEMENT,
            JournalTransaction::PAYROLL,
            Task::TASK,
            SalesOrderDetail::ORDER_LINE_ITEM,
            CustomerTransaction::DELIVERY,
            SalesOrder::ORDER,
        ];

        return in_array($type, $implemented);
    }

    /**
     * Store this combo in the database using front accounting connection
     *
     * @param int $type
     * @param string $template
     * @param int $nextSeq
     * @return string|int
     */
    public static function store($type, $template, $nextSeq) {
        return static::runQuery(
            "INSERT INTO `0_meta_references` (`type`, `template`, `next_seq`) VALUES ({$type}, '{$template}', {$nextSeq})",
            "Could not store new pattern"
        );
    }

    /**
     * Get the next sequence of the type and template
     *
     * **This function will lock the record for updating**
     * 
     * @param int $type
     * @param string $template
     * @return int|null
     */
    public static function getNextSeqForUpdate($type, $template) {
        $result = static::runQuery(
            "SELECT `next_seq` FROM `0_meta_references` WHERE `type` = {$type} AND `template` = '{$template}' FOR UPDATE",
            "Could not get the next seq"
        );

        return $result['next_seq'] ?? null;
    }

    /**
     * Returns the individual blocks from a pattern
     *
     * @param string $pattern
     * @param array $context
     * @return void
     */
    public static function getBlocks($pattern, $context = null)
    {
        $refline_placeholders = refline_placeholders();

        // date based placeholders are always allowed, so default for non-array context is date
		if (!isset($context))
            $context = date(getNativeDateFormat());

        if (is_string($context))
            $context = array('date' => $context);

        $context['user'] = authUser()->id;
        $context['pos'] = authUser()->pos;
        if (!empty($context['dimension'])) {
            $context['dimension'] = data_get($context['dimension'], 'invoice_prefix', '');
        }
        $out = '';

        while(($start = strpos($pattern, '{')) !==false) {
            $out .= substr($pattern, 0, $start);
            $stop = strpos($pattern, '}');
            if ($stop === false) {
                trigger_error("Invalid refline template.", E_USER_WARNING);
                $out .= $pattern; // debug
                break;
            }
            $ph = substr($pattern, $start+1, $stop-$start-1);
            $pattern = substr($pattern, $stop+1);

            if (isset($refline_placeholders[$ph])) {
                if (!isset($context[$refline_placeholders[$ph]]))
                {
                    trigger_error(sprintf("Missing refline context data: '%s'", $refline_placeholders[$ph]), E_USER_WARNING);
                    $out .= $ph; // debug
                } else {
                    switch ($ph)
                    {
                        case 'MM':
                        case 'YY':
                        case 'YYYY':
                            list($day, $month, $year) = explode_date_to_dmy($context['date']);
                            $out .= $ph == 'MM' ? sprintf('%02d', $month) : ($ph == 'YY' ? sprintf('%02d', $year%100): sprintf('%04d', $year));
                            break;
						case 'FF':
						case 'FFFF':
							list($day, $month, $year) = explode_date_to_dmy(static::getFiscalyearBeginForDate($context['date']));
							$out .= $ph == 'FF' ? sprintf('%02d', $year%100): sprintf('%04d', $year);
							break;
                        case 'C':
                            $out .= sprintf('%d', $context['customer']);
                            break;

                        case 'B':
                            $out .= sprintf('%d', $context['branch']);
                            break;

                        case 'S':
                            $out .= sprintf('%d', $context['supplier']);
                            break;

                        case 'L':
                            $out .= sprintf('%s', $context['location']);
                            break;

                        case 'P':
                            $out .= sprintf('%s', $context['pos']);
                            break;
                        
                        case 'DIM':
                            $out .= sprintf('%s', $context['dimension']);
                            break;

                        case 'UU':
                            $out .= sprintf('%02d', $context['user']);
                            break;

                        case 'SUB':
                            $out .= sprintf('%s', $context['type_prefix']);
                            break;
                    }
                }
            } elseif (is_numeric($ph)) {
                $out .= '{'.$ph.'}'; // index placeholder
            }
        }

        $out .= $pattern;	// add postfix

        if (!preg_match('/^([^\{]*)?\{([^\}]*)\}(.*)/', $out, $match)) {	// parse index
            trigger_error("Missing numeric placeholder in refline definition.", E_USER_ERROR);
            throw new LogicException("Missing numeric placeholder in refline definition.");
        }

        return $match;
    }

    /**
     * Get the next reference no for the given type
     *
     * @param int $type The system transacation type
     * @param int $line The id of the reference pattern definition. If empty default definition for the type is used
     * @param array $context The array of context: available as per the refline_options definition
     * @param boolean $persist Decides to mark this reference as used
     * @return string
     */
    public static function getNext($type, $line=null, $context=null, $persist = false, $offset = 0)
    {
        $refline = static::getRefline($type, $line);
        $pattern = $refline->prefix . $refline->pattern;

        return static::parseNext($type, $pattern, $context, $persist, $offset);
    }

    /**
     * Run query depending on the environment
     *
     * @param string $qry
     * @param string $msg
     * @return array|bool
     */
    private static function runQuery($qry, $msg = null)
    {
        if (MetaTransaction::isInLaravel()) {
            $conn = DB::connection()->getPdo();

            $stmt = $conn->query($qry);

            if ($stmt == false) {
                return false;
            }

            // Fetch the results as an associative array
            if (strpos(strtoupper($qry), "SELECT") === 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }

            // Get the number of affected rows
            return $result = $stmt->rowCount();
        }

        $result = db_query($qry, $msg);
        
        if ($result === false) {
            return false;
        }

        if ($result === true) {
            return db_num_affected_rows();
        }

        return $result->fetch_assoc();
    }

    /**
     * Get the bigin date of fiscal year for the give date where it belongs
     *
     * @return string
     */
    private static function getFiscalyearBeginForDate($date)
    {
        $date = date2sql($date);

        $date = DB::table('0_fiscal_year')
            ->where('begin', '<=', $date)
            ->where('end', '>=', $date)
            ->value('begin');

        if (empty($date)) {
            $date = DB::table('0_fiscal_year')->where('id', pref('company.f_year'))->value('begin');
        }

        return sql2date($date);
    }

    /**
     * Get the refernce line defined in databse for the given type
     *
     * @param int $type
     * @param int|array|null $line
     * @return object
     */
    private static function getRefline($type, $line = null)
    {
        $qry = DB::table('0_reflines');
        
        if (isset($line)) {
            $qry->where('id', $line);
        } else {
            $qry->where('trans_type', $type)->whereRaw('`default`');
        }

        return $qry->first();
    }

    public static function dependsOnDimension($type, $line = null) {
        $refLine = static::getRefline($type, $line);

        return Str::contains($refLine->pattern, '{DIM}');
    }

    /**
     * Check if the reference will change due to date change
     *
     * @param int $type
     * @param string $newDate
     * @param string $oldDate
     * @param int $line
     * @return boolean
     */
    public static function willChangeReferenceDueToDateChange($type, $newDate, $oldDate, $line = null)
    {
        $refLine = static::getRefline($type, $line);

        list($nDay, $nMonth, $nYear) = explode_date_to_dmy($newDate);
        list($oDay, $oMonth, $oYear) = explode_date_to_dmy($oldDate);

        if (
            (
                Str::contains($refLine->pattern, '{YY}')
                || Str::contains($refLine->pattern, '{YYYY}')
            )
            && $oYear != $nYear

        ) {
            return true;
        }

        if (Str::contains($refLine->pattern, '{MM}') && $oMonth != $nMonth) {
            return true;
        }

        list($nDay, $nMonth, $nYear) = explode_date_to_dmy(static::getFiscalyearBeginForDate($newDate));
        list($oDay, $oMonth, $oYear) = explode_date_to_dmy(static::getFiscalyearBeginForDate($oldDate));
        if (
            (
                Str::contains($refLine->pattern, '{FF}')
                || Str::contains($refLine->pattern, '{FFFF}')
            )
            && $oYear != $nYear
        ) {
            return true;
        }

        return false;
    }

    /**
     * Parses the next reference
     *
     * @param int $type
     * @param strign $pattern
     * @param array $context
     * @param boolean $persist
     * @return string
     */
    public static function parseNext($type, $pattern, $context=null, $persist = false, $offset = 0)
    {
        $blocks = static::getBlocks($pattern, $context);
		$prefix = $blocks[1];
        $numeric_ph = $blocks[2];
		$postfix = $blocks[3];

        if (static::canGetNextReference($type)) {
            return static::getNextReference($type, $blocks, $persist, $offset);
        }

		$last_ref = static::parseMaxValue($type, $prefix, $postfix);

        if (!$persist) {
            $last_ref += $offset;
        }

        // fill with zeros to the length of original index placeholder
		return $prefix.sprintf('%0'.strlen($numeric_ph).'d', $last_ref+1).$postfix;
    }

    /**
     * Parses the current maximum reference of this transaction
     *
     * @param int $type
     * @param string $pattern
     * @param array $context
     * @param boolean $persist
     * @return string
     */
    private static function parseMaxValue($type, $prefix, $postfix)
    {
        $db_info = MetaTransaction::find($type);
		$trans_table = $db_info->table;
		$type_fld = $db_info->col_type;
		$tno_fld = $db_info->col_trans_no;
		$ref_fld = $db_info->col_reference;

		// retrieve last ref number in the refline from original transaction table
		$sql = "SELECT "
                    ."MAX(CAST(SUBSTR("
                        ."`$ref_fld`, "
                        .(strlen($prefix)+1).", "
                        ."LENGTH(`$ref_fld`) - ".(strlen($postfix)+strlen($prefix))
                    .") AS UNSIGNED)) last_ref"
				." FROM `$trans_table` tbl
					LEFT JOIN `0_voided` v ON tbl.`$tno_fld`=v.id AND v.`type`=$type"
				." WHERE ISNULL(v.id)"
				.($type_fld ? " AND tbl.`$type_fld`=$type" : '')
				." AND `$ref_fld` REGEXP ".quote('^'.preg_quote($prefix).'[0-9]*'.preg_quote($postfix).'$');

		return static::runQuery($sql, 'cannot retrieve last reference')['last_ref'];
    }

    /**
     * Gets the reference or Check for duplicate
     * 
     * $ref != null => check reference is not used (or unique for $trans_no!=0)
	 * $trans!=0 AND $ref=null => retrieve reference for the $type/$trans_no (if any)
     * 
     * @param int $type
     * @param integer $trans_no
     * @param string $ref
     * @return string|false
     */
    public static function get($type, $trans_no=0, $ref=null)
    {
        $db_info = MetaTransaction::find($type);
		$trans_table = $db_info->table;
		$type_fld = $db_info->col_type;
		$tno_fld = $db_info->col_trans_no;
		$ref_fld = $db_info->col_reference;

		$type = quote($type);
		$sql = "SELECT `$ref_fld` as `ref`
				FROM `$trans_table` tbl
					LEFT JOIN `0_voided` v ON 
				tbl.`$tno_fld`=v.id AND v.`type`=$type"
			." WHERE ISNULL(v.id)"
			.($type_fld ? " AND tbl.`$type_fld`=$type" : '');
		if ($ref)
		{
			$sql .= " AND tbl.`$ref_fld`=".quote(trim($ref));
			if ($trans_no)
				$sql .= " AND tbl.`$tno_fld` != ".quote($trans_no);
		} else {
			$sql .= " AND tbl.`$tno_fld`=".quote($trans_no);
		}

		$result = static::runQuery($sql, "could not test for unique reference");
		
        if (!$result)
			return false;

		return $result['ref'];
    }

    /**
     * Check if the reference number is new
     *
     * @param string $ref
     * @param int $type
     * @param integer $trans_no
     * @return boolean
     */
    public static function isNewReference($ref, $type, $trans_no=0)
    {
        return !static::get($type, $trans_no, $ref);
    }

    /**
     * Find the refline from the reference and type
     *
     * @param string $reference
     * @param int $type
     * @return int|null
     */
    private static function findReflineId($reference, $type)
    {
        $sql = "SELECT * FROM `0_reflines` WHERE trans_type=".quote($type)
			." AND CHAR_LENGTH(`prefix`) AND LEFT(".quote($reference).", CHAR_LENGTH(`prefix`)) = `prefix`"
            . " UNION SELECT * FROM `0_reflines` WHERE trans_type=".quote($type)." AND `prefix`=''";

		$line = static::runQuery($sql, "cannot check reference line id");

		return $line ? $line['id'] : null;
    }

    /**
     * Match the reference with the pattern
     *
     * @param string $reference
     * @param int $type
     * @param array|null $context
     * @param int|null $line
     * @param mixed $match
     * @return int|false
     */
    public static function pregMatch($reference, $type, $context=null, $line=null, &$match=null)
    {
        if (!isset($line))
            $line = static::findReflineId($reference, $type, true);

        if (!isset($line))
            return false;

        $refline = static::getRefline($type, $line);

        $regex = preg_quote($refline->prefix.$refline->pattern);
        if (!is_array($context))
            $context = array('date'=>$context);

        $context['user'] = authUser()->id;
        $context['pos'] = authUser()->pos;

        if (is_date(@$context['date']))
        {
            list($year4, $month, $day) = explode("-", date2sql($context['date']));
            $year2 = substr($year4, 2);
            $f_year = explode("-", date2sql(static::getFiscalyearBeginForDate($context['date'])));
            $f_year2 = substr($f_year[0], 2);
            $f_year4 = $f_year[0];
        } else
        {
            $month = '\d{2,}';
            $year2 = '\d{2,}';
            $year4 = '\d{4,}';
            $f_year2 = '\d{2,}';
            $f_year4 = '\d{4,}';
        }

        $dimension = data_get(data_get($context, 'dimension'), 'invoice_prefix', '[A-Z]{0,3}');
        $type_prefix = empty($context['type_prefix']) ? '[A-Z]+' : $context['type_prefix'];
        $cust = empty($context['customer']) ? '\d+' : $context['customer'];
        $supp = empty($context['supplier']) ? '\d+' : $context['supplier'];
        $branch = empty($context['branch']) ? '\d+' : $context['branch'];
        $location = empty($context['location']) ? '\d+' : $context['location'];
        $pos = empty($context['pos']) ? '[a-z0-9]+' : $context['pos'];
        $user = sprintf("%02d", $context['user']);

        $regex = preg_replace(
            array(
                '/\\\{/',	// unquote placeholders
                '/\\\}/',
                '/\{MM\}/',
                '/\{YY\}/',
                '/\{YYYY\}/',
                '/\{FF\}/',
                '/\{FFFF\}/',
                '/\{C\}/',
                '/\{B\}/',
                '/\{S\}/',
                '/\{L\}/',
                '/\{UU\}/',
                '/\{P\}/',
                '/\{\d+}/',
                '/\{DIM\}/',
                '/\{SUB\}/',
            ),
            array(
                '{',
                '}',
                $month,
                $year2,
                $year4,
                $f_year2,
                $f_year4,
                $cust,
                $branch,
                $supp,
                $location,
                $user,
                $pos,
                '(\d+)',
                $dimension,
                $type_prefix
            ), $regex);

        $regex = '"^'.$regex.'"i';

        return preg_match($regex, $reference, $match);
    }

    /**
     * Check reference is valid before add/update transaction.
     *
     * @param string $reference
     * @param int $type
     * @param array|null $context
     * @param int|null $line
     * @return boolean
     */
    public static function isValid($reference, $type, $context=null, $line=null)
    {
        return static::pregMatch($reference, $type, $context, $line) ? 1 : 0;
    }

    /**
     * Save reference (and prepare next) on write transaction.
     *
     * @param int $type
     * @param int $id
     * @param string $reference
     * @param int|null $line
     * @return void
     */
    public static function saveReference($type, $id, $reference, $line = null)
    {
        if ($reference == 'auto')
            return;

        $pdo = DB::connection()->getPdo();
        $sql = "REPLACE `0_refs` SET reference=".$pdo->quote($reference).", type=".$pdo->quote($type).", id=".$pdo->quote($id);

        static::runQuery($sql, "could not update reference entry");
    }
}
