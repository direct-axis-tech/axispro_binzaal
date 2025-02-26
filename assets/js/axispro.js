"use strict";


/**
 * Prepared By : Bipin
 * AxisPro
 * 16-10-2019
 */

var global = {};

var AxisPro = {

    Init: function (options) {

        this.InitDatePicker();
        this.InitSelect2();
        this.InitConfig();

    },
    InitDatePicker: function () {

        $('.ap-datepicker').datepicker({
            rtl: KTUtil.isRTL(),
            todayHighlight: true,
            orientation: "bottom left",
            autoclose: this,
            templates: undefined,
            format: "d-M-yyyy",
        });
    },

    InitSelect2: function () {
        initializeSelect2('.ap-select2');
    },

    isRTL: function () {
        return document.getElementsByTagName("html")[0].getAttribute("dir") === 'rtl';
    },

    BlockDiv: function (element, msg) {

        if (!msg) msg = 'Fetching data. Please wait...';

        KTApp.block(element, {
            overlayColor: '#cccccc',
            type: 'v2',
            state: 'primary',
            message: msg,
            size: 'lg'
        });
    },

    UnBlockDiv: function (element) {

        KTApp.unblock(element);
    },


    getFormData: function ($form) {

        var unindexed_array = $form.serializeArray();
        var indexed_array = {};

        $.map(unindexed_array, function (n, i) {
            indexed_array[n['name']] = n['value'];
        });

        return indexed_array;

    },

    FunctionAPICall: function (request_type, params, callback) {

        $.ajax({
            url: route('API_Call', {method: params.method}),
            method: request_type,
            data: params,
        }).done(function (r) {

            var result = JSON.parse(r);
            if (result) {

                if (callback)
                    callback(result);
            }
        });

    },

    APICall: function (request_type, url, params, callback) {

        //AxisPro.BlockDiv("#kt_content");

        $.ajax(url, {
            method: request_type,
            data: params,
        }).done(function (r) {

            //AxisPro.UnBlockDiv("#kt_content");

            var result = JSON.parse(r);
            if (result) {

                if (callback)
                    callback(result);
            }
        });

    },

    PrepareSelectOptions: function (result, value, text, elem_class, placeholder, callback, selected_id) {

        var opts = "";
        if (placeholder === true)
            opts += "<option value=''>Select</option>";

        else if (placeholder && placeholder.length > 1)
            opts += "<option value=''>" + placeholder + "</option>";

        $.each(result, function (key, data) {

            var selected = "";
            if (data[value] == selected_id) selected = "selected";

            opts += "<option " + selected + " value='" + data[value] + "'>" + data[text] + "</option>";
        });
        $("." + elem_class).html(opts);
        $("." + elem_class).select2();

        if (callback)
            callback();

    },

    ShowPopUpReport: function (form) {

        var PopupWindow = window.open('', 'formpopup', 'width=700,height=700,resizeable,scrollbars');
        form.target = 'formpopup';
    },

    InitConfig: function () {

        this.APICall('GET', route('API_Call', {method: 'common_settings'}), {}, function (data) {
        });

    },


    DeleteCustomReport: function (id) {

        swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then(function (result) {
            if (result.value) {

                AxisPro.BlockDiv("#kt_content");

                AxisPro.APICall("POST", route('API_Call', {method: 'delete_custom_report'}), {
                    id: id
                }, function (data) {

                    AxisPro.UnBlockDiv("#kt_content");

                    if (data.status === 'OK') {
                        swal.fire(
                            'Deleted!',
                            data.msg,
                            'success'
                        ).then(function () {
                            window.location.reload();
                        });

                    }
                    else {
                        swal.fire(
                            'Error!',
                             data.msg,
                            'warning'
                        ).then(function () {
                            window.location.reload();
                        });
                    }

                });


            }
        });

    },


    /** AxisPro JSTree Functions */

    JSTree: {


        NodeTypesObj: {

            COA: { // Chart of Accounts
                "default": {
                    "icon": "fa fa-folder kt-font-brand"
                },
                "file": {
                    "icon": "fa fa-file  kt-font-brand"
                },
                "class": {
                    "icon": "fa fa-home kt-font-success",
                    "abbr": 'CLS'
                },
                "group": {
                    "icon": "fa fa-layer-group kt-font-info",
                    "abbr": 'GRP'
                },
                "ledger": {
                    "icon": "fa fa-book kt-font-danger",
                    "abbr": 'LGR'
                },
                "sub_ledger": {
                    "icon": "fa fa-asterisk kt-font-warning",
                    "abbr": 'SLR'
                }
            },


        },

        GenerateCOATree() {

            AxisPro.APICall('GET', route('API_Call', {method: 'chart_of_accounts'}), {}, function (data) {

                $("#ap-coa-tree").jstree({
                    "core": {
                        "themes": {
                            "responsive": false
                        },
                        // so that create works
                        "check_callback": true,
                        'data': data
                    },
                    "types": AxisPro.JSTree.NodeTypesObj.COA,
                    "state": {"key": "demo2"},
                    "plugins": ["contextmenu", "state", "types"],
                    'contextmenu': {
                        'items': AxisPro.JSTree.CustomContextMenuCOA
                    },
                }).on('create_node.jstree', function (e, data) {

                    data.node.text = $("#new-coa-node-name").val();
                    data.node.original.new_node_id = $("#new-coa-code").val();

                    AxisPro.JSTree.SaveCOANode(data, 'create');

                }).on('rename_node.jstree', function (e, data) {

                    AxisPro.JSTree.SaveCOANode(data, 'update');

                }).on('delete_node.jstree', function (e, data) {

                    AxisPro.JSTree.DeleteCOANode(data, 'delete');

                });
            });

        },


        GetAllCOANodeIDs(node_type) {

            var idList = [];
            var jsonNodes = $('#ap-coa-tree').jstree(true).get_json('#', {flat: true});

            $.each(jsonNodes, function (i, val) {

                var id = $(val).attr('id');

                if (id.includes(AxisPro.JSTree.NodeTypesObj.COA[node_type].abbr)) {
                    idList.push(id.split('_')[1]);
                }

            });

            return idList;
        },

        ValidateCOACode: function (node_type, node_id) {

            var all_node_ids = AxisPro.JSTree.GetAllCOANodeIDs(node_type);

            for (var i = 0; i < all_node_ids.length; i++) {

                if (node_id === all_node_ids[i]) {
                    node_id = parseInt(node_id) + 1;
                    node_id = node_id.toString();
                    i = 0;
                }
            }

            return node_id;

        },


        CreateNodePopup: function (data, node_data, pos) {

            node_data.new_node_id = AxisPro.JSTree.ValidateCOACode(node_data.type, node_data.new_node_id);

            global.node = data;
            global.node_data = node_data;
            global.pos = pos;

            var modal = $("#COA_confirm_new_modal");
            modal.modal("show");

            modal.on('shown.bs.modal', function () {
                $("#new-coa-code").val(node_data.new_node_id);
                $("#new-coa-node-name").val(node_data.text).focus();
            })

        },

        CreateNode: function (data, node_data, pos) {

            var inst = $.jstree.reference(data.reference),
                obj = inst.get_node(data.reference);

            if (!pos) pos = 'last';

            inst.create_node(obj, node_data, pos, function (new_node) {
                new_node.data = {file: true, id: node_data.new_node_id};
                // setTimeout(function () {
                //     inst.edit(new_node);
                // }, 0);
            });
        },

        NextCOACode: function (node, creating_node_type) {

            var parent_node_id = node.original.real_id;
            var all_childrens = node.children;
            var children_array = [];
            $.each(all_childrens, function (key, val) {

                var original_child_id = val.split('_')[1];

                if (val.includes(creating_node_type) && original_child_id.startsWith(parent_node_id)) {
                    children_array.push(all_childrens[key])
                }
            });
            var childrens = children_array; //Children with same type
            var childrens_length = childrens.length;
            var last_created_children = '';
            if (childrens_length > 0) {
                last_created_children = childrens[childrens_length - 1];
            }
            var last_created_children_id = 0;
            if (last_created_children !== "") {
                var split_node_id = last_created_children.split("_");
                last_created_children_id = split_node_id[1];
            }

            var parent_node_id_length = parent_node_id.length;
            //Max Length For Ledger Account Code is 5
            var remaining_length_for_node_id = 5 - parent_node_id_length;
            var next_account_code = "";
            if (creating_node_type === 'LGR') {

                var new_node_id = (parseInt(last_created_children_id) + 1).toString();
                new_node_id = new_node_id.padStart(remaining_length_for_node_id, "0");
                if (childrens_length > 0) parent_node_id = '';
                next_account_code = (parent_node_id) + "" + new_node_id;
            }
            else {
                if (childrens_length > 0) parent_node_id = '';
                next_account_code = (parent_node_id) + "" + (parseInt(last_created_children_id) + 1);
            }
            return next_account_code;

        },

        RefreshCOATree(callback) {

            AxisPro.APICall('GET', route('API_Call', {method: 'chart_of_accounts'}), {}, function (data) {

                if (callback) callback(data);

            });
        },


        SaveCOANode: function (data, purpose) {

            var parent = data.node.parent;
            var parent_node_id = 0;

            var parent_node_type = '';

            if (parent) {
                parent_node_id = parent.split("_")[1];
                parent_node_type = parent.split("_")[0];
            }

            var params = {
                text: data.node.text,
                node_id: data.node.original.new_node_id !== undefined ?
                    data.node.original.new_node_id : data.node.original.real_id,
                parent_id: parent_node_id,
                node_type: data.node.original.type,
                purpose: purpose,
                parent_node_type: parent_node_type
            };

            AxisPro.APICall("POST", route('API_Call', {method: 'create_coa_node'}), params, function (data) {

                if (data.status === 'OK') {
                    toastr.success(data.msg);
                }
                else {
                    toastr.error(data.msg);
                }

                $("#COA_confirm_new_modal").modal("hide");

                AxisPro.JSTree.RefreshCOATree(function (data) {

                    var abbr = AxisPro.JSTree.NodeTypesObj.COA[params.node_type].abbr;
                    var created_node = abbr + "_" + params.node_id;

                    $('#ap-coa-tree').jstree(true).settings.core.data = data;
                    $('#ap-coa-tree').jstree(true).deselect_all(true);
                    $('#ap-coa-tree').jstree(true).select_node(created_node);
                    $('#ap-coa-tree').jstree(true).refresh();
                });

            });

        },

        DeleteCOANode: function (data) {

            swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then(function (result) {
                if (result.value) {

                    var params = {
                        node_id: data.original.real_id,
                        node_type: data.original.type,
                    };

                    AxisPro.APICall("POST", route('API_Call', {method: 'delete_coa_node'}), params, function (data) {

                        if (data.status === 'OK')
                            toastr.success(data.msg);
                        else
                            toastr.error(data.msg);

                        AxisPro.JSTree.RefreshCOATree(function (data) {
                            $('#ap-coa-tree').jstree(true).settings.core.data = data;
                            $('#ap-coa-tree').jstree(true).deselect_all(true);
                            $('#ap-coa-tree').jstree(true).refresh();
                        });

                    });

                }
            });

        },


        ChangeCOAGroup: function () {

            swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, change it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then(function (result) {
                if (result.value) {

                    var params = {
                        node_type: $("#COA_CGM_TYPE").val(),
                        node_id: $("#COA_CGM_NODE_ID").val(),
                        parent_id: $("#coa-acc-groups").val(),
                        class_id: $("#coa-acc-class").val()
                    };

                    AxisPro.APICall("POST", route('API_Call', {method: 'change_coa_parent'}), params, function (data) {

                        if (data.status === 'OK')
                            toastr.success(data.msg);
                        else
                            toastr.error(data.msg);

                        AxisPro.JSTree.RefreshCOATree(function (data) {

                            $('#ap-coa-tree').jstree(true).settings.core.data = data;
                            $('#ap-coa-tree').jstree(true).deselect_all(true);
                            $('#ap-coa-tree').jstree(true).refresh();
                        });

                        $("#COA_change_group_modal").modal('hide');

                    });

                }
            });

        },


        COAClassSelectChange: function ($this) {

            var class_id = $($this).val();
            var params = {
                class_id: class_id
            };

            AxisPro.APICall('GET', route('API_Call', {method: 'get_all_coa_groups'}), params, function (data) {
                AxisPro.PrepareSelectOptions(data, 'id', 'name', 'coa-acc-groups', '*None*');
            });

        },


        CustomContextMenuCOA: function (node) { //COA Tree context menus

            var tree = $("#ap-coa-tree").jstree(true);
            var items = {
                //Context menus for classes
                createGroup: {
                    label: "New group",
                    action: function (obj) {
                        var node_data = {
                            text: 'New Group',
                            type: 'group'
                        };

                        node_data.new_node_id = AxisPro.JSTree.NextCOACode(node, "GRP");
                        AxisPro.JSTree.CreateNodePopup(obj, node_data);
                    }
                },

                //Context menus for groups
                createSubGroup: {
                    label: "New sub-group",
                    action: function (obj) {
                        var node_data = {
                            text: 'New sub-group',
                            type: 'group'
                        };

                        node_data.new_node_id = AxisPro.JSTree.NextCOACode(node, "GRP");
                        AxisPro.JSTree.CreateNodePopup(obj, node_data);
                    }
                },
                createLedger: {
                    label: "New ledger",
                    action: function (obj) {
                        var node_data = {
                            text: 'New ledger',
                            type: 'ledger'
                        };
                        node_data.new_node_id = AxisPro.JSTree.NextCOACode(node, "LGR");
                        AxisPro.JSTree.CreateNodePopup(obj, node_data);
                    }
                },
                renameGroup: {
                    label: "Rename this group",
                    action: function (obj) {
                        node.text = (node.text).split(" - ")[1];
                        tree.edit(node);
                    }
                },
                deleteGroup: {
                    label: "Delete this group",
                    action: function (obj) {
                        AxisPro.JSTree.DeleteCOANode(node);
                    }
                },

                //Context menus for Ledgers
                renameLedger: {
                    label: "Rename this ledger",
                    action: function (obj) {
                        node.text = (node.text).split(" - ")[1];
                        tree.edit(node);
                    }
                },
                createSubLedger: {
                    label: "New sub-ledger",
                    action: function (obj) {

                        var node_data = {
                            text: 'New sub-ledger',
                            type: 'sub_ledger'
                        };
                        node_data.new_node_id = AxisPro.JSTree.NextCOACode(node, "SLR");
                        AxisPro.JSTree.CreateNodePopup(obj, node_data);

                    }
                },

                deleteSubLedger: {
                    label: "Delete this sub-ledger",
                    action: function (obj) {
                        AxisPro.JSTree.DeleteCOANode(node);
                    }
                },


                renameSubLedger: {
                    label: "Rename this sub-ledger",
                    action: function (obj) {
                        node.text = (node.text).split(" - ")[1];
                        tree.edit(node);
                    }
                },


                changeParentGroup: {
                    label: "Change group/parent",
                    action: function (obj) {

                        $("#COA_CGM_TYPE").val(node.type);
                        $("#COA_CGM_NODE_ID").val(node.original.real_id);

                        var node_parent = node.parent;
                        var real_parent_id = node_parent.split("_")[1];
                        var parent_node_type = node_parent.split("_")[0];

                        $("#COA_change_group_modal").modal("show");

                        if (node.type === 'group' || node.type === 'ledger') {

                            $('#coa-acc-class-div').show();
                            var class_id = node.original.p_id_one;

                            if (!class_id || class_id === 0) {
                                $('#coa-acc-class').val(1).trigger('change');
                            }
                            else {
                                $('#coa-acc-class').val(class_id).trigger('change');
                            }

                        }
                        else {
                            $('#coa-acc-class-div').hide();
                        }

                        if (parent_node_type === 'CLS')
                            real_parent_id = '';

                        setTimeout(function () {
                            $('#coa-acc-groups').val(real_parent_id).trigger('change');
                        }, 500);

                    }
                },

                deleteLedger: {
                    label: "Delete this ledger",
                    action: function (obj) {
                        AxisPro.JSTree.DeleteCOANode(node);
                    }
                },

            };


            if (node.type === 'class') {

                //Removing menus for groups
                delete items.createSubGroup;
                delete items.createLedger;
                delete items.renameGroup;
                delete items.deleteGroup;

                //removing menus for ledgers
                delete items.renameLedger;
                delete items.createSubLedger;
                delete items.deleteLedger;

                delete items.changeParentGroup;
                delete items.renameSubLedger;
                delete items.deleteSubLedger;


            }

            if (node.type === 'group') {

                //removing menus for class
                delete items.createGroup;

                //removing menus for ledgers
                delete items.renameLedger;
                delete items.createSubLedger;
                delete items.deleteLedger;

                delete items.renameSubLedger;
                delete items.deleteSubLedger;

            }

            if (node.type === 'ledger') {

                //removing menus for groups
                delete items.createSubGroup;
                delete items.createLedger;
                delete items.renameGroup;
                delete items.deleteGroup;

                //removing menus for class
                delete items.createGroup;

                delete items.renameSubLedger;
                delete items.deleteSubLedger;
            }


            if (node.type === 'sub_ledger') {

                //removing menus for groups
                //Removing menus for groups
                delete items.createSubGroup;
                delete items.createLedger;
                delete items.renameGroup;
                delete items.deleteGroup;

                //removing menus for ledgers
                delete items.renameLedger;
                delete items.createSubLedger;
                delete items.deleteLedger;

                delete items.changeParentGroup;

                delete items.createGroup;
            }

            return items;
        }

    }


};

$(document).ready(function () {

    $('#ap-coa-tree').on('keydown.jstree', '.jstree-anchor', $.proxy(function (e) {
        if(e.target.tagName === "INPUT") { return true; }
        if(e.which === 46) {
    
            var ref = $('#ap-coa-tree').jstree(true);
            var node_id = ref.get_selected()[0];
    
            var type = "";
            if (node_id) {
                var type_abbr = node_id.split("_")[0];
                node_id = node_id.split("_")[1];
            }
    
            if(type_abbr === "LGR")
                type = "ledger";
    
            if(type_abbr === "GRP")
                type = "group";
    
            if(type_abbr === "SLR")
                type = "sub_ledger";
    
            ref.original = {
                real_id : node_id,
                type : type
            };
    
            AxisPro.JSTree.DeleteCOANode(ref);
    
        }
    }, $('#ap-coa-tree').jstree(true)));

    AxisPro.Init();

    // getUnreadNotificationCount();


    // setInterval(function () {

    //    getUnreadNotificationCount();

    //}, 3000)


    $('.kt-iconbox').mouseover(function () {

        var text = $(this).find(".kt-link").text();
        if (text.length > 15) {

            if (AxisPro.isRTL()) {
                $(this).find(".kt-link").addClass('marquee-on-hover-left-to-right')
            }
            else {
                $(this).find(".kt-link").addClass('marquee-on-hover')
            }

        }

    }).mouseout(function () {
        $(this).find(".kt-link").removeClass('marquee-on-hover');
        $(this).find(".kt-link").removeClass('marquee-on-hover-left-to-right')
    });


    $(".kt-iconbox__body").click(function () {

        var link = $(this).find(".kt-link");
        var href = link.attr("href");

        if (link.attr('target') === '_blank') {
            window.open(href, '_blank');
        }
        else {

            window.location.href = href;
        }

    });


    $(".axispro-lang-btn").click(function (e) {

        var lang = $(this).data("lang");

        KTApp.blockPage({
            overlayColor: 'blue',
            type: 'v2',
            state: 'primary',
            size: 'lg',
            message: 'Changing Language. Please wait...'
        });

        AxisPro.APICall('POST', route('API_Call', {method: 'change_language'}), {
            lang: lang
        }, function (data) {

            setTimeout(function () {

                if (data.status === 'OK') {
                    window.location.reload();
                }
            }, 3000);

        });

    });


    // $("#notification_icon").click(function () {
    //
    //
    //     getNotifications();
    //
    // });

});


function clean(variable) {

    return (!variable || variable == 'undefined') ? '' : variable

}

function amount(num) {

    if (!num) return '0.00';

    num = parseFloat(num).toFixed(2);

    var num_parts = num.toString().split(".");
    num_parts[0] = num_parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return num_parts.join(".");

}

function isset_empty(variable) {

    return (typeof(variable) != "undefined" && variable !== null)

}