/**
 * montaListaDados
 * Monta o dataTable com os dados da Classe
 * @param string tabela 
 * @param string url 
 */
var table;

function montaListaDados(tabela, url) {
    jQuery.fn.dataTable.moment('DD/MM/YYYY HH:mm:ss');    //Formatação com Hora
    jQuery.fn.dataTable.moment('DD/MM/YYYY');    //Formatação sem Hora
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 31cffd33b9425f8976445a0ec535d45f55cf74cd
    if (table != undefined) {
        table.ajax.url(url).load(null, true);
    } else {
        // monta o Datable 
        table = new DataTable('#' + tabela,
            // table = jQuery('#' + tabela).DataTable(
            {
                "ajax": {
                    "url": url,
                    "type": "Post",
                    "datatype": "json"
                },
                "retrieve": true,
                "stateSave": true,
                "sPaginationType": "full_numbers",
                // "aaSorting": [],
                "columnDefs": [
                    { "visible": false, "targets": [0] },
                    { "max-width": "8em", "targets": [-1] },
                    { "min-width": "8em", "targets": [-1] },
                    { "width": "8em", "targets": [-1] },
                    // { "orderable": false, "targets": [-1] },
                    { "searchable": false, "targets": [-1] },
                    { "className": "text-center acao text-nowrap", "targets": [-1] },
                    { "className": "acao", "targets": [0] },
                    { "className": "text-start cell-border", "targets": "_all" },
                    // { "searchBuilder":{defaultCondition: '='}},
                ],
                "buttons": {
                    dom: {
                        button: {
                            className: "btn btn-outline-primary wauto",
                            style: "max-width: 31.5px!important;"
                        }
                    },
                    buttons: [
                        {
                            extend: 'searchBuilder',
                            text: '<i class="fa fa-filter" aria-hidden="true"></i>',
                            titleAttr: 'Filtrar',
                            config: {
                                text: '<i class="fa fa-filter" aria-hidden="true"></i>',
                                id: 'bt_filtro',
                                columns: [':not(.acao)', ':visible'],
                                defaultCondition: 'Igual',
                            },
                            preDefined: {
                                criteria: [
                                    {
                                        condition: 'Igual',
                                    },
                                ]
                            }
                        },
                        {
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel-o" aria-hidden="true"></i>',
                            titleAttr: 'Exportar para Excel',
                            title: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            filename: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            exportOptions: {
                                columns: [':not(.acao)'],
                            },
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>',
                            titleAttr: 'Exportar para PDF',
                            title: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            filename: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            exportOptions: {
                                columns: [':not(.acao)'],
                            },
                        },
                        {
                            extend: 'print',
                            autoPrint: true,
                            text: '<i class="fa fa-print" aria-hidden="true"></i>',
                            titleAttr: 'Enviar para Impressora',
                            title: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            exportOptions: {
                                columns: ':not(.acao)'
                            },
                        },
                        {
                            extend: 'colvis',
                            text: '<i class="fa-solid fa-table-columns"></i>',
                            columns: [':not(.acao)'],
                            popoverTitle: 'Colunas'
                        },
                    ]
<<<<<<< HEAD
                },
                "bSortCellsTop": true,
                "pageLength": 50,
                "bPaginate": true,
                // "sScrollX": "100vw",
                "scrollY": 'calc(100vh - 15rem)',
                "bProcessing": true,
                "bScrollCollapse": true,
                "deferRender": true,
                "sDom": 'lftrBip',
                "language": {
                    "url": 'assets/jscript/datatables-lang/pt-BR.json',
                },
                "fnDrawCallback": function (oSettings) {
                    jQuery('table#' + tabela + ' > tbody > tr').on("mouseover", function () {
                        jQuery(this).children().find('.btn').addClass('hover');
                    }).on('mouseleave', function () {
                        jQuery(this).children().find('.btn').removeClass('hover');
                    });
                },
            });

        jQuery('#' + tabela).on('click', 'tbody tr td:not(".acao")', function () {
            link = jQuery(this).parent().find('a')[0].href;
            if (link != null) {
                if (link.indexOf('edit/') > -1 || link.indexOf('show/') > -1) {
                    redireciona(link);
                }
=======

    // monta o Datable 
    table = jQuery('#' + tabela).DataTable(
        {
            fixedColumns: {
                left: 0,
                right: 1
            },
            // "serverSide": true,
            "ajax": {
                "url": url,
                "type": "Post",
                "datatype": "json"
            },
            "retrieve": true,
            // "bJQueryUI": true,
            // "responsive": true,
            "stateSave": true,
            "sPaginationType": "full_numbers",
            "aaSorting": [],
            "columnDefs": [
                { "visible": false, "targets": [0] },
                { "max-width": "8em", "targets": [-1] },
                { "min-width": "8em", "targets": [-1] },
                { "width": "8em", "targets": [-1] },
                { "orderable": false, "targets": [-1] },
                { "searchable": false, "targets": [-1] },
                { "className": "text-center acao", "targets": [-1] },
                { "className": "acao", "targets": [0] },
                { "className": "text-start text-nowrap", "targets": "_all" },
                // { "searchBuilder":{defaultCondition: '='}},
            ],
            "buttons": {
                dom: {
                    button: {
                        className: "btn btn-outline-primary wauto",
                        style: "max-width: 31.5px!important;"
                    }
                },
                buttons: [
                    {
                        extend: 'searchBuilder',
                        text: '<i class="fa fa-filter" aria-hidden="true"></i>',
                        titleAttr: 'Filtrar',
                        config: {
                            text: '<i class="fa fa-filter" aria-hidden="true"></i>',
                            id: 'bt_filtro',
                            columns: [':not(.acao)', ':visible'],
                            defaultCondition: 'Igual',
                        },
                        preDefined: {
                            criteria: [
                                {
                                    condition: 'Igual',
                                },
                            ]
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fa fa-file-excel-o" aria-hidden="true"></i>',
                        titleAttr: 'Exportar para Excel',
                        title: function () {
                            return document.title + ' - ' + jQuery('#legenda').text();
                        },
                        filename: function () {
                            return document.title + ' - ' + jQuery('#legenda').text();
                        },
                        exportOptions: {
                            columns: [':not(.acao)'],
                        },
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>',
                        titleAttr: 'Exportar para PDF',
                        title: function () {
                            return document.title + ' - ' + jQuery('#legenda').text();
                        },
                        filename: function () {
                            return document.title + ' - ' + jQuery('#legenda').text();
                        },
                        exportOptions: {
                            columns: [':not(.acao)'],
                        },
                    },
                    {
                        extend: 'print',
                        autoPrint: true,
                        text: '<i class="fa fa-print" aria-hidden="true"></i>',
                        titleAttr: 'Enviar para Impressora',
                        title: function () {
                            return document.title + ' - ' + jQuery('#legenda').text();
                        },
                        exportOptions: {
                            columns: ':not(.acao)'
                        },
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fa-solid fa-table-columns"></i>',
                        columns: [':not(.acao)'],
                        popoverTitle: 'Colunas'
                    },
                ]
            },
            // header options
            // "orderCellsTop": true,
            // "fixedHeader": false,        
            "bSortCellsTop": true,
            "pageLength": 50,
            "bPaginate": true,
            // "autoWidth":true,
            // "scrollX": true,
            // "scrollX": '100%',
            "sScrollX": "100vw",
            "scrollY": 'calc(100vh - 15rem)',
            "bProcessing": true,
            "bScrollCollapse": true,
            "deferRender": true,
            "sDom": 'lftrBip',
            "language": {
                "url": 'assets/jscript/datatables-lang/pt-BR.json',
            },
            "fnDrawCallback": function (oSettings) {
                jQuery('table#' + tabela + ' > tbody > tr').on("mouseover", function () {
                    jQuery(this).children().find('.btn').addClass('hover');
                }).on('mouseleave', function () {
                    jQuery(this).children().find('.btn').removeClass('hover');
                });
            },
        });

    jQuery('#' + tabela).on('click', 'tbody tr td:not(".acao")', function () {
        link = jQuery(this).parent().find('a')[0].href;
        if (link != null) {
            if (link.indexOf('edit/') > -1 || link.indexOf('show/') > -1) {
                redireciona(link);
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
            }
        });

<<<<<<< HEAD
=======
                },
                "bSortCellsTop": true,
                "pageLength": 50,
                "bPaginate": true,
                // "sScrollX": "100vw",
                "scrollY": 'calc(100vh - 15rem)',
                "bProcessing": true,
                "bScrollCollapse": true,
                "deferRender": true,
                "sDom": 'lftrBip',
                "language": {
                    "url": 'assets/jscript/datatables-lang/pt-BR.json',
                },
                "fnDrawCallback": function (oSettings) {
                    jQuery('table#' + tabela + ' > tbody > tr').on("mouseover", function () {
                        jQuery(this).children().find('.btn').addClass('hover');
                    }).on('mouseleave', function () {
                        jQuery(this).children().find('.btn').removeClass('hover');
                    });
                },
            });

        jQuery('#' + tabela).on('click', 'tbody tr td:not(".acao")', function () {
            link = jQuery(this).parent().find('a')[0].href;
            if (link != null) {
                if (link.indexOf('edit/') > -1 || link.indexOf('show/') > -1) {
                    redireciona(link);
                }
            }
        });

>>>>>>> 31cffd33b9425f8976445a0ec535d45f55cf74cd
        table.on('draw', function () {
            jQuery('.buttons-colvis').removeClass('dropdown-toggle');
        });
        table.on('draw.dt', function (e, settings) {
            let api = new DataTable.Api(settings);

            settings.aoColumns.forEach((c, i) => {
                if (c.sType.includes('num')) {
                    api
                        .cells(null, i, { page: 'current' })
                        .nodes()
                        .to$()
                        .addClass('text-end');
                }
            });
        });

    }
}

/**
 * montaListaDados
 * Monta o dataTable com os dados da Classe
 * @param string tabela 
 * @param string url 
 */
var table;

function montaListaDadosEdit(tabela, url) {
    jQuery.fn.dataTable.moment('DD/MM/YYYY HH:mm:ss');    //Formatação com Hora
    jQuery.fn.dataTable.moment('DD/MM/YYYY');    //Formatação sem Hora
    if (table != undefined) {
        table.ajax.url(url).load(null, true);
    } else {
        // monta o Datable 
        table = new DataTable('#' + tabela,
            // table = jQuery('#' + tabela).DataTable(
            {
                "ajax": {
                    "url": url,
                    "type": "Post",
                    "datatype": "json"
                },
                "order": [],
                "retrieve": true,
                "stateSave": true,
                "sPaginationType": "full_numbers",
                // "aaSorting": [],
                "columnDefs": [
                    { "visible": false, "targets": [0] },
                    { "max-width": "8em", "targets": [-1] },
                    { "min-width": "8em", "targets": [-1] },
                    { "width": "8em", "targets": [-1] },
                    // { "orderable": false, "targets": [-1] },
                    { "searchable": false, "targets": [-1] },
                    { "className": "text-center acao text-nowrap", "targets": [-1] },
                    { "className": "acao", "targets": [0] },
                    { "className": "text-start cell-border", "targets": "_all" },
                    // { "searchBuilder":{defaultCondition: '='}},
                ],
                "buttons": {
                    dom: {
                        button: {
                            className: "btn btn-outline-primary wauto",
                            style: "max-width: 31.5px!important;"
                        }
                    },
                    buttons: [
                        {
                            extend: 'searchBuilder',
                            text: '<i class="fa fa-filter" aria-hidden="true"></i>',
                            titleAttr: 'Filtrar',
                            config: {
                                text: '<i class="fa fa-filter" aria-hidden="true"></i>',
                                id: 'bt_filtro',
                                columns: [':not(.acao)', ':visible'],
                                defaultCondition: 'Igual',
                            },
                            preDefined: {
                                criteria: [
                                    {
                                        condition: 'Igual',
                                    },
                                ]
                            }
                        },
                        {
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel-o" aria-hidden="true"></i>',
                            titleAttr: 'Exportar para Excel',
                            title: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            filename: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            exportOptions: {
                                columns: [':not(.acao)'],
                            },
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>',
                            titleAttr: 'Exportar para PDF',
                            title: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            filename: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            exportOptions: {
                                columns: [':not(.acao)'],
                            },
                        },
                        {
                            extend: 'print',
                            autoPrint: true,
                            text: '<i class="fa fa-print" aria-hidden="true"></i>',
                            titleAttr: 'Enviar para Impressora',
                            title: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            exportOptions: {
                                columns: ':not(.acao)'
                            },
                        },
                        {
                            extend: 'colvis',
                            text: '<i class="fa-solid fa-table-columns"></i>',
                            columns: [':not(.acao)'],
                            popoverTitle: 'Colunas'
                        },
                    ]
                },
                "bSortCellsTop": true,
                "pageLength": 100,
                "bPaginate": true,
                // "sScrollX": "100vw",
                "scrollY": 'calc(100vh - 15rem)',
                "bProcessing": true,
                "bScrollCollapse": true,
                "deferRender": true,
                "sDom": 'lftrBip',
                "language": {
                    "url": 'https://intranet.taisho.com.br/assets/jscript/datatables-lang/pt-BR.json',
                },
                "fnDrawCallback": function (oSettings) {
                    jQuery('table#' + tabela + ' > tbody > tr').on("mouseover", function () {
                        jQuery(this).children().find('.btn').addClass('hover');
                    }).on('mouseleave', function () {
                        jQuery(this).children().find('.btn').removeClass('hover');
                    });
                },
            });

        jQuery('#' + tabela).on('click', 'tbody tr td:not(".acao")', function () {
            link = jQuery(this).parent().find('a')[0].href;
            if (link != null) {
                if (link.indexOf('edit/') > -1 || link.indexOf('show/') > -1) {
                    redireciona(link);
                }
            }
        });
        // Activate an inline edit on click of a table cell
        table.on('draw', function () {
            jQuery('.buttons-colvis').removeClass('dropdown-toggle');
        });
        table.on('draw.dt', function (e, settings) {
            let api = new DataTable.Api(settings);

            settings.aoColumns.forEach((c, i) => {
                if (c.sType.includes('num')) {
                    api
                        .cells(null, i, { page: 'current' })
                        .nodes()
                        .to$()
                        .addClass('text-end');
                }
            });
        });

    }
}

/**
 * montaListaDadosDetails
 * Monta o dataTable com os dados da Classe
 * @param string tabela 
 * @param string url 
 */
var tabledet;

function montaListaDadosDetail(tabela, url) {
    jQuery.fn.dataTable.moment('DD/MM/YYYY HH:mm:ss');    //Formatação com Hora
    jQuery.fn.dataTable.moment('DD/MM/YYYY');    //Formatação sem Hora
    if (tabledet != undefined) {
        tabledet.ajax.url(url).load(null, true);
    } else {
        // monta o Datable 
        tabledet = new DataTable('#' + tabela,
            // table = jQuery('#' + tabela).DataTable(
            {
                "processing": true,
                // "serverSide": true,
                "ajax": {
                    "url": url,
                    "type": "Post",
                    "datatype": "json"
                },
                "retrieve": true,
                // "stateSave": true,
                "sPaginationType": "full_numbers",
                "aaSorting": [],
                "columnDefs": [
                    { "visible": false, "targets": [0] },
                    { "max-width": "8em", "targets": [-1] },
                    { "min-width": "8em", "targets": [-1] },
                    { "width": "8em", "targets": [-1] },
                    { "orderable": false, "targets": [-1] },
                    { "searchable": false, "targets": [-1] },
                    { "className": "text-center acao text-nowrap", "targets": [-1] },
                    { "className": "dt-control acao", "targets": [1] },
                    { "className": "acao", "targets": [0] },
                    { "className": "text-start cell-border", "targets": "_all" },
                    // { "searchBuilder":{defaultCondition: '='}},
                ],
                "buttons": {
                    dom: {
                        button: {
                            className: "btn btn-outline-primary wauto",
                            style: "max-width: 31.5px!important;"
                        }
                    },
                    buttons: [
                        {
                            extend: 'searchBuilder',
                            text: '<i class="fa fa-filter" aria-hidden="true"></i>',
                            titleAttr: 'Filtrar',
                            config: {
                                text: '<i class="fa fa-filter" aria-hidden="true"></i>',
                                id: 'bt_filtro',
                                columns: [':not(.acao)', ':visible'],
                                defaultCondition: 'Igual',
                            },
                            preDefined: {
                                criteria: [
                                    {
                                        condition: 'Igual',
                                    },
                                ]
                            }
                        },
                        {
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel-o" aria-hidden="true"></i>',
                            titleAttr: 'Exportar para Excel',
                            title: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            filename: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            exportOptions: {
                                columns: [':not(.acao)'],
                            },
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>',
                            titleAttr: 'Exportar para PDF',
                            title: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            filename: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            exportOptions: {
                                columns: [':not(.acao)'],
                            },
                        },
                        {
                            extend: 'print',
                            autoPrint: true,
                            text: '<i class="fa fa-print" aria-hidden="true"></i>',
                            titleAttr: 'Enviar para Impressora',
                            title: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            exportOptions: {
                                columns: ':not(.acao)'
                            },
                        },
                        {
                            extend: 'colvis',
                            text: '<i class="fa-solid fa-table-columns"></i>',
                            columns: [':not(.acao)'],
                            popoverTitle: 'Colunas'
                        },
                    ]
                },
                "bSortCellsTop": true,
                "pageLength": 50,
                "bPaginate": true,
                // "sScrollX": "100vw",
                "scrollY": 'calc(100vh - 15rem)',
                "bProcessing": true,
                "bScrollCollapse": true,
                "deferRender": true,
                "sDom": 'lftrBip',
                "language": {
                    "url": 'assets/jscript/datatables-lang/pt-BR.json',
                },
                "fnDrawCallback": function (oSettings) {
                    jQuery('table#' + tabela + ' > tbody > tr').on("mouseover", function () {
                        jQuery(this).children().find('.btn').addClass('hover');
                    }).on('mouseleave', function () {
                        jQuery(this).children().find('.btn').removeClass('hover');
                    });
                },
            });

        jQuery('#' + tabela).on('click', 'tbody tr td:not(".acao")', function (e) {
            let tr = e.target.closest('tr');
            let row = tabledet.row(tr);

            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
            }
            else {
                for (let r = 0; r < tabledet.rows()[0].length; r++) {
                    rrow = tabledet.row(r);
                    if (rrow.child.isShown()) {
                        // This row is already open - close it
                        rrow.child.hide();
                    }
                }
                row.child(format(row.data())).show();
            }
        });

        tabledet.on('draw', function () {
            jQuery('.buttons-colvis').removeClass('dropdown-toggle');
        });
    }
}
// Formatting function for row details - modify as you need
function format(d) {
    text = '';
    text += "<div class='ps-3 col-12 pb-3 m-0 bg-info-subtle'>";
    text += "<div class='row col-12 bg-gray-padrao'>";
    for (let t = 0; t < d.col_details.tit.length; t++) {
        const tit = d.col_details.tit[t];
        const tam = d.col_details.tam[t];
        text += "<div class='fw-bold text-center " + tam + "'>";
        text += tit;
        text += "</div>";
    }
    text += "</div>";
    for (let p = 0; p < d.details.length; p++) {
        const prod = d.details[p];
        text += "<div class='row col-12'>";
        for (let f = 0; f < d.col_details.cam.length; f++) {
            const camp = d.col_details.cam[f];
            const tam = d.col_details.tam[f];
            var talig = ' text-start';
            if ((!isNaN(parseFloat(prod[camp])) && isFinite(prod[camp])) || prod[camp].substr(0, 2) == 'R$') {
                talig = ' text-end';
            } else if (isValidDate(prod[camp])) {
                talig = ' text-center';
            }
            text += "<div class='border border-1 " + tam + talig + "'>";
            text += prod[camp];
            text += "</div>";
        }
        text += "</div>";
    }
    text += "</div>";
    return (text);
}

/**
 * montaListaDadosGrupo
 * Monta o dataTable com os dados da Classe
 * @param string tabela 
 * @param string url 
 */
var tablegrupo;

function montaListaDadosGrupo(tabela, url, groupColumn = 0) {
    jQuery.fn.dataTable.moment('DD/MM/YYYY HH:mm:ss');    //Formatação com Hora
    jQuery.fn.dataTable.moment('DD/MM/YYYY');    //Formatação sem Hora
    if (tablegrupo != undefined) {
        tablegrupo.ajax.url(url).load(null, true);
    } else {
        // monta o Datable 
        tablegrupo = new DataTable('#' + tabela,
            // table = jQuery('#' + tabela).DataTable(
            {
                "ajax": {
                    "url": url,
                    "type": "Post",
                    "datatype": "json"
                },
                "retrieve": true,
                // "stateSave": true,
                "sPaginationType": "full_numbers",
                "aaSorting": [groupColumn],
                "columnDefs": [
                    { visible: false, targets: [0, groupColumn] },
                    { "min-width": "8em", "targets": "_all" },
                    { "width": "8em", "targets": "_all" },
                    { "orderable": false, "targets": "_all" },
                    { "searchable": false, "targets": "_all" },
                    { "className": "text-center acao text-nowrap", "targets": [-1] },
                    { "className": "acao", "targets": [0] },
                    { "className": "agrupando", "targets": groupColumn },
                    { "className": "text-start ", "targets": "_all" },
                ],
                "buttons": {
                    dom: {
                        button: {
                            className: "btn btn-outline-primary wauto",
                            style: "max-width: 31.5px!important;"
                        }
                    },
                    buttons: [
                        {
                            extend: 'searchBuilder',
                            text: '<i class="fa fa-filter" aria-hidden="true"></i>',
                            titleAttr: 'Filtrar',
                            config: {
                                text: '<i class="fa fa-filter" aria-hidden="true"></i>',
                                id: 'bt_filtro',
                                columns: [':not(.acao)', ':visible'],
                                defaultCondition: 'Igual',
                            },
                            preDefined: {
                                criteria: [
                                    {
                                        condition: 'Igual',
                                    },
                                ]
                            }
                        },
                        {
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel-o" aria-hidden="true"></i>',
                            titleAttr: 'Exportar para Excel',
                            title: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            filename: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            exportOptions: {
                                columns: [':not(.acao)'],
                            },
                        },
                        {
                            extend: 'pdfHtml5',
                            text: '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>',
                            titleAttr: 'Exportar para PDF',
                            title: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            filename: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            exportOptions: {
                                columns: [':not(.acao)'],
                            },
                        },
                        {
                            extend: 'print',
                            autoPrint: true,
                            text: '<i class="fa fa-print" aria-hidden="true"></i>',
                            titleAttr: 'Enviar para Impressora',
                            title: function () {
                                return document.title + ' - ' + jQuery('#legenda').text();
                            },
                            exportOptions: {
                                columns: ':not(.acao)'
                            },
                        },
                        {
                            extend: 'colvis',
                            text: '<i class="fa-solid fa-table-columns"></i>',
                            columns: [':not(.acao)'],
                            popoverTitle: 'Colunas'
                        },
                    ]
                },
                "bSortCellsTop": true,
                "pageLength": 50,
                "bPaginate": true,
                // "sScrollX": "100vw",
                "scrollY": 'calc(100vh - 15rem)',
                "bProcessing": true,
                "bScrollCollapse": true,
                "deferRender": true,
                "sDom": 'lftrBip',
                "language": {
                    "url": 'assets/jscript/datatables-lang/pt-BR.json',
                },
                "fnDrawCallback": function (oSettings) {
                    jQuery('table#' + tabela + ' > tbody > tr').on("mouseover", function () {
                        jQuery(this).children().find('.btn').addClass('hover');
                    }).on('mouseleave', function () {
                        jQuery(this).children().find('.btn').removeClass('hover');
                    });
                },
                drawCallback: function (settings) {
                    var api = this.api();
                    var rows = api.rows({ page: 'current' }).nodes();
                    var last = null;

                    api.column(groupColumn, { page: 'current' })
                        .data()
                        .each(function (group, i) {
                            if (last !== group) {
                                $(rows)
                                    .eq(i)
                                    .before(
                                        '<tr class="group text-start"><td colspan="15">' +
                                        group +
                                        '</td></tr>'
                                    );

                                last = group;
                            }
                        });
                },
            });

        jQuery('#' + tabela).on('click', 'tbody tr td:not(".acao")', function () {
            link = jQuery(this).parent().find('a')[0].href;
            if (link != null) {
                if (link.indexOf('edit/') > -1 || link.indexOf('show/') > -1) {
                    redireciona(link);
                }
            }
        });

        // Order by the grouping
        jQuery('#' + tabela + ' tbody').on('click', 'tr.group', function () {
            var currentOrder = tablegrupo.order()[0];
            if (currentOrder[0] === groupColumn && currentOrder[1] === 'asc') {
                tablegrupo.order([groupColumn, 'desc']).draw();
            }
            else {
                tablegrupo.order([groupColumn, 'asc']).draw();
            }
        });

        tablegrupo.on('draw', function () {
            jQuery('.buttons-colvis').removeClass('dropdown-toggle');
        });
        tablegrupo.on('draw.dt', function (e, settings) {
            let api = new DataTable.Api(settings);

            settings.aoColumns.forEach((c, i) => {
                if (c.sType.includes('num')) {
                    api
                        .cells(null, i, { page: 'current' })
                        .nodes()
                        .to$()
                        .addClass('text-end');
                }
            });
        });

        // }
    }
}
<<<<<<< HEAD
=======
    table.on('draw', function () {
        jQuery('.buttons-colvis').removeClass('dropdown-toggle');
        // var colsvis = table.columns().visible();
        // for(c=1;c<colsvis.length;c++){
        //     if(!colsvis[c]){
        //         jQuery("#showcolumn option[value='" + c + "']").selectpicker().removeAttr("selected");
        //     }
        // }
        // jQuery("#showcolumn").selectpicker('');
        // jQuery("#showcolumn").removeClass('d-none');
        // jQuery("#showcolumn").parent().removeClass('d-none');
        // console.log('Redraw occurred at: ' + new Date().getTime());
    });
}

// function showcolumn(){
//     select = jQuery('#showcolumn');
//     for(let i = 0; i < select[0].options.length; i++)
//     {
//         col = select[0].options[i].value;
//         let column = table.column(col);
//         ver = false;
//         if (select[0].options[i].selected)
//         {
//             ver = true;
//         }
//         column.visible(ver);
//     }        
// }
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
=======
>>>>>>> 31cffd33b9425f8976445a0ec535d45f55cf74cd
