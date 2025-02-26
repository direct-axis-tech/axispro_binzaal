<?php

use App\Models\Accounting\Dimension;

/**
 * Determines if option to select the maid is enabled
 *
 * @param integer $dimension_id
 * @param array $items
 * @return boolean
 */
function is_maid_select_enabled($dimension_id, $items) {
    if (Dimension::whereId($dimension_id)->value('center_type') == CENTER_TYPES['DOMESTIC_WORKER']) {
        return true;
    }

    foreach ($items as $item) {
        if (data_get($item, 'maid_id')) {
            return true;
        }
    }

    return false;
}

/**
 * Determines if option to edit govt_fee is enabled
 *
 * @param Dimension $dimension
 * @param array $items
 * @return boolean
 */
function is_govt_fee_editable($dimension) {
    if (data_get($dimension, 'govt_fee_editable_in_purch')) {
        return true;
    }

    return false;
}

/**
 * Determines if option to select so_line_ref is enabled
 *
 * @param Dimension $dimension
 * @return boolean
 */
function is_so_line_ref_enabled($dimension) {
    if (data_get($dimension, 'enable_line_ref_in_purch')) {
        return true;
    }

    return false;
}
