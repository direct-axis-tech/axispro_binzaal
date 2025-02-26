<?php
/**
 * Created by PhpStorm.
 * User: hp
 * Date: 9/23/2018
 * Time: 4:13 PM
 */

function payment_status_cell($label,$name,$selected_id=null)
{
    echo "<td>$label</td>
            <td>" . array_selector(
                $name, $selected_id,
                [
                    "0"=>"All",
                    "1"=>"Fully Paid",
                    "2"=>"Not Paid",
                    "3"=>"Partially Paid"
                ]
        ) . "</td>";

}


function payment_method_cell($label,$name,$selected_id=null)
{
    echo "<td>$label</td>
            <td>" . array_selector(
            $name, $selected_id,
            [
                "" => "All",
                "Cash"=>"Cash",
                "CreditCard"=>"CreditCard",
            ]
        ) . "</td>";

}