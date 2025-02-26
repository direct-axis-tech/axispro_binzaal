<?php
/**********************************************************************
 * Direct Axis Technology L.L.C.
 * Released under the terms of the GNU General Public License, GPL,
 * as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
 ***********************************************************************/
/**********************************************************************
 * Page for searching item list and select it to item selection
 * in pages that have the item dropdown lists.
 * Author: bogeyman2007 from Discussion Forum. Modified by Joe Hunt
 ***********************************************************************/
$page_security = "SA_ITEM";
$path_to_root = "../..";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/inventory/includes/db/items_db.inc");

if (!isset($_POST['type'])) {
    $_POST['type'] = $_GET['type'] ?? '';
}

set_page_security($_POST['type'], [
    'sales' => "SA_SALES_ITEM_SEARCH"
]);

?>

<?php if (!get_post('search')) { ?>

    <style>
 tr, td, th {
            line-height: 25px !important;
            padding: 0px !important;
        }

        .tablestyle td:hover {
            background: #d7d7d7 !important;
            cursor: pointer;
        }

        td[align=center],.tableheader,.tr_subcat {
            text-align: left !important;
            padding-left: 20px !important;
        }

        .sidenav {
            height: 100%;
            width: 155px;
            position: fixed;
            z-index: 1;
            top: 0;
            left: 0;
            background-color: #edeff5;
            overflow-x: hidden;
            /*overflow-y: scroll;*/
            padding-top: 20px;
            border-right: 1px solid #cccc;
        }

        .sidenav a {
            padding: 4px 4px 4px 25px;
            text-decoration: none;r
            font-size: 15px;
            color: #818181;
            display: block;
            margin-bottom: -5px;
        }

        .sidenav img {
            border: 1px solid #009688;
        }

        .sidenav a:last-child {
            margin-bottom: 15px;
        }

        .sidenav a:hover {
            color: #000000;
        }
        .content {
            margin-left: 185px; /* Same as the width of the sidenav */
        }

        @media screen and (max-height: 450px) {
            .sidenav {padding-top: 15px;}
            /*.sidenav a {font-size: 18px;}*/
        }



        .tile {
            background-color: lightgrey;
            width: 75px;
            border: 1px solid green;
            padding: 1px;
            float: left;
            margin: 6px;
            border: 1px solid black;
        }

        .row_tile_text {
            margin: 5px;
            font-size: 11px;
            height: 64px;
        }
        .tr_subcat,#_category_id_sel {
            display: none;
        }
        body {
           margin-left: 145px !important;
        }
        #_page_body {
            padding: 6px !important;
            margin-left: -139px !important;
        }
        table.tablestyle_noborder td {
            padding: 5px;
            margin-right: 515px;
        }

    </style>


    <script src="../../js/jquery3.3.1.min.js"></script>
    <script type="text/javascript">
        $("input[name='description']").focus();
        $(document).on("change", "#category_id", function () {
            $(this).parents("form")[0].submit();
        });

        $(document).ready(function (e) {
            $("#category_id").parents('tr').hide();
        });

        $(document).on('click', '.tablestyle td', function (e) {
            $(this).find('a').click();
        });

        $(document).on('click', '.featured_cat', function (e) {

            var id = $(this).data('id');
            $("input[name='featured']").val(id);
            $("#category_id").val(-1);
            $("#subcategory_2").val(0);
            $("#subcategory_1").val(0);
            $("#category_id").trigger('change');
        });

        $(document).on('click', '.cat-tile', function (e) {
            var cat_id = $(this).data('id');

            $("select[name='subcategory_1']").val(0);
            $("select[name='subcategory_1']").trigger('0')
            $("#category_id").val(cat_id).trigger('change');
        });


        $(document).on('click', '.subcat1_tile', function (e) {
            var sub_cat1 = $(this).data('id');
            $("select[name='subcategory_1']").val(sub_cat1);
            $("select[name='subcategory_1']").trigger('change')
        });


        $(document).on('click', '.subcat2_tile', function (e) {
            var sub_cat2 = $(this).data('id');
            $("select[name='subcategory_2']").val(sub_cat2);
            $("select[name='subcategory_2']").trigger('change')
            $("button[name=search]").click()
        })


    </script>
<?php } ?>

<?php $mode = get_company_pref('no_item_list');
if ($mode != 0)
    $js = get_js_set_combo_item();
else
    $js = get_js_select_combo_item();

page(trans($help_context = "Items"), true, false, "", $js);

if (get_post("search")) {
    $Ajax->activate("item_tbl");
}

function top_selling_services($category_id = 6)
{
    global $SysPrefs;
    $limit = 10;
    $today = Today();
    $begin = begin_fiscalyear();
    $begin1 = date2sql($begin);
    $today1 = date2sql($today);
    $sql = "SELECT SUM((trans.unit_price * trans.quantity) * d.rate) AS total, s.stock_id, s.description, 
            SUM(trans.quantity) AS qty, SUM((s.material_cost + s.overhead_cost + s.labour_cost) * trans.quantity) AS costs FROM
            " . TB_PREF . "debtor_trans_details AS trans, " . TB_PREF . "stock_master AS s, " . TB_PREF . "debtor_trans AS d 
            WHERE trans.stock_id=s.stock_id AND trans.debtor_trans_type=d.type AND trans.debtor_trans_no=d.trans_no
            AND (d.type = " . ST_SALESINVOICE . " OR d.type = " . ST_CUSTCREDIT . ") ";

    $sql .= "AND tran_date >= '$begin1' ";
    $sql .= "AND tran_date <= '$today1' AND s.category_id=$category_id GROUP by s.stock_id ORDER BY total DESC, s.stock_id 
        LIMIT $limit";
    $result = db_query($sql);

    $stock_ids = [];
    while ($row = db_fetch($result)) {
        array_push($stock_ids, db_escape($row['stock_id']));
    }
    return $stock_ids;
}


start_form(false, false, $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']);

hidden('type');
start_table(TABLESTYLE_NOBORDER);

$categories = get_item_categories(false);
$logo_dir = $path_to_root . "/themes/daxis/images/";

$feat_immigration ='<a href="#" class="featured_amer featured_cat" data-id="' . $SysPrefs->prefs['immigration_category'] . '">
<img style="margin: 5px; display:block;" src="default_category_image.png" width="75" height="75" alt="" />FREQUENT IMMIGRATION</a>';

$feat_tasheel ='<a href="#" class="featured_amer featured_cat" data-id="' . $SysPrefs->prefs['tasheel_category'] . '">
<img style="margin: 5px; display:block;" src="default_category_image.png" width="75" height="75" alt="" />FREQUENT TASHEEL</a>';

echo '<div class="sidenav">';



//Category Permission
$user = get_user($_SESSION["wa_current_user"]->user);
$permitted_categories = "0";
if(!empty($user["permitted_categories"]))
    $permitted_categories = $user["permitted_categories"];

$permitted_categories = explode(",",$permitted_categories);
$get_string = strval(parse_url($_SERVER['HTTP_REFERER'],PHP_URL_QUERY));
parse_str($get_string, $get_array);
$show_items = isset($get_array["amp;show_items"]) ? $get_array["amp;show_items"] : "";


$show_items = isset($_GET['show']) ? $_GET['show'] : '';

$tasheel_categories = [];


while ($row = db_fetch($categories)) {


    if($row["is_tasheel"] == 1) {

        array_push($tasheel_categories,$row["category_id"]);

    }


    //Category Permission
    if($show_items == 'ts') {

        if($row["is_tasheel"] != 1 )
            continue;

    }

//    if($show_items == 'tb') {
//
//        if($row["is_tasheel"] != 1 || $row["category_id"] != $SysPrefs->prefs['tadbeer_category'])
//            continue;
//
//    }

    if($show_items == "all" AND !in_array($row['category_id'],$permitted_categories))
        continue;


    if($show_items == 'all' && $row['is_tasheel'] == 1) {
        continue;
    }


    //END -- Category Permission

    $logo = $logo_dir . "cat_logo_" . $row["description"] . ".png";

    if (!file_exists($logo)) {
        $logo = "default_category_image_.png";
    }

    echo '<a href="#" class="cat-tile" data-id="' . $row['category_id'] . '"><img style="margin: 5px; display:block;" src="'.$logo.'" width="75" height="75" alt="" />'.$row['description'].'</a>';

    if ($row['category_id'] == $SysPrefs->prefs['immigration_category']) {
//        echo $feat_immigration;
    }
    if ($row['category_id'] == $SysPrefs->prefs['tasheel_category']) {
//        echo $feat_tasheel;
    }

}

//echo '</ul>';
echo '</div>';

$Ajax->activate('_page_body');

echo '<div class="row_tiles" id="row_tiles">';

$subcategory_tiles = [];
$tile_class='';
if(isset($_POST['category_id']) && !empty($_POST['category_id'])) {
    $subcategory_tiles = get_subcategory(0, $_POST['category_id']);
    $tile_class = 'subcat1_tile';
}

if(isset($_POST['subcategory_1']) && !empty($_POST['subcategory_1'])) {

    $subcategory_tiles = get_subcategory( $_POST['subcategory_1'],$_POST['category_id']);
    $tile_class = 'subcat2_tile';
}



foreach ($subcategory_tiles as $key=>$value) {
    //$exp=explode('-',$value);

    echo '<div class="tile '.$tile_class.'" data-id="'.$key.'">
             <p class="row_tile_text">'.$value[1].'</p>
             <p class="row_tile_text">'.$value[0].'</p>
</div>';

}





echo '</div>';




start_row();

stock_categories_list_row(trans(""), 'category_id', null, "Select category", true, false);

if ($_POST['category_id'] > 0) {
    $_POST['featured'] = 0;
}

if (!isset($_POST['featured']))
    $_POST['featured'] = 0;

hidden('featured', $_POST['featured']);

$Ajax->activate('featured');
//SUBCATEGORY OPTION
$subcategory_1 = get_subcategory(0, $_POST['category_id']);
if (empty($subcategory_1)) {
    $subcategory_1 = ["All"];
}
$options = array('select_submit' => true, 'disabled' => null);
echo '<tr>';
echo '<td style="width: 25%"  class="tr_subcat" >';
echo '<label class="label"> Sub Category 1 : </label>' . array_selector('subcategory_1', null, $subcategory_1, $options)."</td>";
$Ajax->activate('subcategory_1');

if (isset($_POST['subcategory_1'])) {

    $subcategory_2 = get_subcategory($_POST['subcategory_1'], $_POST['category_id']);
    if (empty($subcategory_2) || empty($_POST['subcategory_1'])) {
        $subcategory_2 = ["All"];
    }

    $options = array('select_submit' => false, 'disabled' => null);
    echo '<td style="width: 25%" class="tr_subcat"><label class="label"> Sub Category 2 : </label>' . array_selector('subcategory_2', null, $subcategory_2, $options) . '</td>';

    $Ajax->activate('subcategory_2');
}


echo '<td style="width: 25%; display: block" class="tr_subcat"><label class="label">Description : </label>
<input style="height: 32px !important;" type="text" name="description" size="30" maxlength="30" value=""></td>';


//text_cells(trans("Description"), "description", null, null, null, null, null, null, "class='searchbox'");


echo "<td style='vertical-align: bottom;'>".submit("search", trans("Search"), "", trans("Search items"), "default")."</td></tr>";

end_row();

end_table();

end_form();

div_start("item_tbl");
start_table(TABLESTYLE);

$th = array( trans("Description"),trans('Service Charge'),trans('Govt. Fee'),trans('Total'));
table_header($th);

$k = 0;
$name = $_GET["client_id"];

$featured_stock_ids = [];
if (!empty($_POST['featured'])) {
    $featured_stock_ids = top_selling_services($_POST['featured']);
}

$result = get_items_search(get_post("description"), @$_GET['type'], get_post("category_id"), $featured_stock_ids);

//pp($permitted_categories);

while ($myrow = db_fetch_assoc($result)) {


    if(!in_array($myrow['category_id'],$permitted_categories))
        continue;


    if($show_items == 'ts') {

        if(!in_array(intval($myrow['category_id']),$tasheel_categories))
            continue;

    }





    alt_table_row_color($k);
    $value = $myrow['item_code'];
//    if ($mode != 0) {
//        $text = $myrow['description'];
//        ahref_cell(trans("Select"), 'javascript:void(0)', '', 'setComboItem(window.opener.document, "' . $name . '",  "' . $value . '", "' . $text . '")');
//    } else {
//        ahref_cell(trans("Select"), 'javascript:void(0)', '', 'selectComboItem(window.opener.document, "' . $name . '", "' . $value . '")');
//    }
//    label_cell($myrow["item_code"]);

    $desc = $myrow["description"];
    if (!empty($myrow["long_description"])) {
        if ($myrow["long_description"] != $desc)
            $desc = $myrow["long_description"] . "<br>" . $desc;
    }


    if ($mode != 0) {
        $text = $desc;
        ahref_cell(trans($desc), 'javascript:void(0)', '', 'setComboItem(window.opener.document, "' . $name . '",  "' . $value . '", "' . $text . '")');
    } else {
        ahref_cell(trans($desc), 'javascript:void(0)', '', 'selectComboItem(window.opener.document, "' . $name . '", "' . $value . '")');
    }


    $govt_amount = $myrow["govt_fee"]+$myrow["bank_service_charge"]+$myrow["bank_service_charge_vat"]+$myrow["pf_amount"];
    $total = $govt_amount+$myrow["price"];
    label_cell(number_format2($myrow["price"],2));
    label_cell(number_format2($govt_amount,2));
    if($myrow["is_tasheel"]=='1')
    {
        $total = $govt_amount;
    }
    label_cell(number_format2($total,2));
//    label_cell($desc);
//    label_cell($myrow["category"]);
    end_row();
}

$Ajax->activate('item_tbl');


set_focus('description');




end_table(1);

div_end();
end_page(true);



