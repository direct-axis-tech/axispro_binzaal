<?php include "header.php" ?>


<?php

$application = isset($_GET['action']) ? $_GET['action'] : "list";


switch ($application) {

    case "list":
        include_once "customer_list.php";
        break;

    default:
        include_once "customer_list.php";
        break;
}


?>

<?php ?>
<?php include "footer.php"; ?>


<style>

    div.dataTables_wrapper div.dataTables_filter {

        text-align: left !important;

    }

    .dt-buttons {
        float: right; !important;
    }

</style>

<?php if ($application == 'list'): ?>
<script>
$(function () {
    ajaxRequest(route('API_Call', {method: 'get_customers_list'}))
        .done(function (respJson, msg, xhr) {
            if (!respJson.data) {
                return defaultErrorHandler(xhr);
            }

            const tbl = document.getElementById('service_list_table');
            empty(tbl);

            const categoryHeads = respJson.categories.map(category => `<th colspan="2" class="p-0 text-center">${category.description}</th>`).join("\n");
            tbl.appendChild($(
                `<thead>
                    <tr>
                        <th>Ref ID</th>
                        <th>Name</th>
                        <th>P.R.O Name</th>
                        <th>Address</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th>TRN</th>
                        ${categoryHeads}
                    </tr>
                    <tr>
                        <th colspan="7" class="p-0 border-0">&nbsp</th>
                        ${respJson.categories.map(x => '<th class="p-0 text-center fs-8 border-0">Disc</th>\n<th class="p-0 text-center fs-8 border-0">Comm</th>').join("\n")}
                    </tr>
                </thead>`
            )[0])

            const rows = [];
            respJson.data.forEach(row => {
                const categoryData = respJson.categories
                    .map(category => {
                        return (
                              `<td class="text-center">${parseFloat(row[category.id + '_discount']) || ''}</td>`
                            + `\n`
                            + `<td class="text-center">${parseFloat(row[category.id + '_commission']) || ''}</td>`
                        );
                    })
                    .join("\n");

                rows.push(`
                    <tr>
                        <td>${row.debtor_ref}</td>
                        <td>${row.name}</td>
                        <td>${row.salesman_name}</td>
                        <td>${row.address}</td>
                        <td>${row.mobile}</td>
                        <td>${row.debtor_email}</td>
                        <td>${row.tax_id}</td>
                        ${categoryData}
                    </tr>
                `);
            });
            tbl.appendChild($(`<tbody>${rows.join("\n")}</tbody>`)[0]);

            $(tbl).DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'colvis','excel'
                ]
            });
        })
        .fail(defaultErrorHandler);
})
</script>
<?php endif; ?>


