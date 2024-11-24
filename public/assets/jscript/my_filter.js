/**
 * buscaSaldo
 * Processa a busca de Saldos de Estoque
 */
<<<<<<< HEAD
<<<<<<< HEAD
function buscaSaldo() {
=======
function buscaSaldo(){
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
=======
function buscaSaldo() {
>>>>>>> 31cffd33b9425f8976445a0ec535d45f55cf74cd
    var codDep = jQuery.trim(jQuery('#codDep').val());
    // var codlot = jQuery.trim(jQuery('#codLot').val());
    // if(codpro == ''){
    //     boxAlert('O Código do Produto é Obrigatório', true,'',false, 1, false);
    //     return;
    // }
    bloqueiaTela();
    urlBusca = 'SaldoEstoque/lista';
    jQuery.ajax({
        type: 'POST',
        async: true,
        dataType: 'json',
        url: urlBusca,
<<<<<<< HEAD
<<<<<<< HEAD
        data: { 'codDep': codDep },
=======
        data: {'codDep': codDep},
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
=======
        data: { 'codDep': codDep },
>>>>>>> 31cffd33b9425f8976445a0ec535d45f55cf74cd
        success: function (retorno) {
            // console.log(retorno);
            montaListaSaldo(retorno);
            desBloqueiaTela();
        }
    });
};;

function montaListaSaldo(dados) {
    jQuery('#table').DataTable().destroy();
    removeLinhas("table", 0);

    linha = '';
    for (var index in dados) {
        item = dados[index];
        linha = linha + "<tr>";
        linha = linha + "<td class='align-middle'>" + item.codDep + "</td>";
        linha = linha + "<td class='align-middle'>" + item.Coderp + "</td>";
        linha = linha + "<td class='align-middle'>" + item.DescProduto + "</td>";
        linha = linha + "<td class='align-middle'>" + item.lote + "</td>";
<<<<<<< HEAD
<<<<<<< HEAD
        linha = linha + "<td class='align-middle' data-sort='" + item.validadeord + "'>" + item.validade + "</td>";
=======
        linha = linha + "<td class='align-middle' data-sort='"+ item.validadeord +"'>" + item.validade + "</td>";
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
=======
        linha = linha + "<td class='align-middle' data-sort='" + item.validadeord + "'>" + item.validade + "</td>";
>>>>>>> 31cffd33b9425f8976445a0ec535d45f55cf74cd
        linha = linha + "<td class='align-middle text-end pe-4'>" + item.saldo + "</td>";
        linha = linha + "<td class='align-middle text-start'>" + item.und + "</td>";
        linha = linha + "</tr>";
    }
    jQuery("#table tbody").append(linha);
    dtResult('table');
    // configTableDt("table_grupo-cubo");

}

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 31cffd33b9425f8976445a0ec535d45f55cf74cd
function dtResult(tabela) {
    // monta o Datable 
    var table = jQuery('#' + tabela).DataTable(
        {
            "responsive": false,
            "sPaginationType": "full_numbers",
            "aaSorting": [],
            "columnDefs": [
                // { "max-width": "8em", "targets": ['all'] },
                { "min-width": "8em", "targets": ['all'] },
                { "width": "8em", "targets": ['all'] },
                { "className": "text-wrap text-nowrap", "targets": ['all'] },
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
                        text: '<i class="fa fa-print" aria-hidden="true"></i>',
                        titleAttr: 'Enviar para Impressora',
                        title: function () {
                            return document.title + ' - ' + jQuery('#legenda').text();
                        },
                        exportOptions: {
                            columns: [':not(.acao)'],
                        },
                    },
                ]
            },
            // header options
            "orderCellsTop": true,
            "fixedHeader": true,
            "bSortCellsTop": true,
            "pageLength": 50,
            "bPaginate": true,
            "scrollY": '58vh !important',
            "bProcessing": true,
            "bScrollCollapse": true,
            "deferRender": true,
            "sDom": 'ftrBip',
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
<<<<<<< HEAD
=======
function dtResult(tabela)
{
    // monta o Datable 
    var table = jQuery('#'+tabela).DataTable(
    {
        "responsive": false,
        "sPaginationType": "full_numbers",
        "aaSorting": [],
        "columnDefs": [
            // { "max-width": "8em", "targets": ['all'] },
            { "min-width": "8em", "targets": ['all'] },
            { "width": "8em", "targets": ['all'] },
            { "className": "text-wrap text-nowrap", "targets": ['all']},
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
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o" aria-hidden="true"></i>',
                    titleAttr: 'Exportar para Excel',
                    title: function() {
                        return document.title + ' - ' + jQuery('#legenda').text() ; 
                    },
                    filename: function() {
                        return document.title + ' - ' + jQuery('#legenda').text() ; 
                    }, 
                    exportOptions: {
                        columns: [':not(.acao)'],
                    },
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>',
                    titleAttr: 'Exportar para PDF',
                    title: function() {
                        return document.title + ' - ' + jQuery('#legenda').text() ; 
                    },
                    filename: function() {
                        return document.title + ' - ' + jQuery('#legenda').text() ; 
                    }, 
                    exportOptions: {
                        columns: [':not(.acao)'],
                    },
                },
                {
                    extend: 'print', 
                    text: '<i class="fa fa-print" aria-hidden="true"></i>',
                    titleAttr: 'Enviar para Impressora',
                    title: function() {
                        return document.title + ' - ' + jQuery('#legenda').text() ; 
                    },
                    exportOptions: {
                        columns: [':not(.acao)'],
                    },
                },
            ]
        },
        // header options
        "orderCellsTop": true,
        "fixedHeader": true,        
        "bSortCellsTop": true,
        "pageLength": 50,
        "bPaginate": true,
        "scrollY": '58vh !important',
        "bProcessing": true,
        "bScrollCollapse": true,
        "deferRender": true,
        "sDom": 'ftrBip', 
        "language": {
            "url": 'assets/jscript/datatables-lang/pt-BR.json',
        },
        "fnDrawCallback": function (oSettings) {
            jQuery('table#'+tabela+' > tbody > tr').on("mouseover",function() {
                jQuery(this).children().find('.btn').addClass('hover');
            }).on('mouseleave', function () {
                jQuery(this).children().find('.btn').removeClass('hover');
            });
        },
    });
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
=======
>>>>>>> 31cffd33b9425f8976445a0ec535d45f55cf74cd
}


function removeLinhas(idTabela, indice = 1) {
    if (indice != 1) {
        jQuery("#" + idTabela + " tbody").children().remove()
    } else {
        jQuery("#" + idTabela).find("tr:gt(" + indice + ")").remove();
    }
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 31cffd33b9425f8976445a0ec535d45f55cf74cd
    jQuery("#" + idTabela).DataTable().destroy();
}

function carrega_lista(obj, url, nome) {
    var tabela = 'tb_' + nome;

    if (typeof (obj) == "string") {
        param = jQuery('#' + obj).val();
    } else {
        var param = obj.value;
    }
    url = url + "?param=" + param;
    montaListaDados(tabela, url);
}

function carrega_lista_edit(obj, url, nome) {
    var tabela = 'tb_' + nome;

    if (typeof (obj) == "string") {
        param = jQuery('#' + obj).val();
    } else {
        var param = obj.value;
    }
    url = url + "?param=" + param;

    montaListaDadosEdit(tabela, url);
}

function carrega_lista_detail(obj, url, nome) {
    var tabela = 'tb_' + nome;

    if (typeof (obj) == "string") {
        param = jQuery('#' + obj).val();
    } else {
        var param = obj.value;
    }
    url = url + "?param=" + param;
    montaListaDadosDetail(tabela, url);
}

function carrega_saldos() {
    var tabela = 'tb_saldoestoque';

    var empresa = jQuery("#empresa").val();
    var deposito = jQuery("#deposito").val();

    if (deposito != '' && empresa != '') {
        // jQuery('#' + tabela).closest('tr').remove();
        url = "/DashEstoque/busca_dados?empresa=" + empresa + "&deposito=" + deposito;
        montaListaDados(tabela, url);
    }
    // bloqueiaTela();
    // var tabela = 'tb_saldoestoque';

    // var empresa = jQuery("#empresa").val();
    // var deposito = jQuery("#deposito").val();

    // // url = "/DashEstoque/busca_dados";
    // url = "/DashEstoque/busca_dados?empresa=" + empresa + "&deposito=" + deposito;
    // montaListaDados(tabela, url);

    // jQuery.ajax({
    //     type: "POST",
    //     url: url,
    //     async: true,
    //     dataType: 'json',
    //     data: { 'empresa': empresa, 'deposito': deposito },
    //     beforeSend: function () {
    //         removeLinhas('tb_' + busca);
    //     },

    //     success: async function (retorno) {
    //         if (retorno.produtos.length > 0) {
    //             adicionaLinhas('saldoestoque', retorno.produtos);
    //             dataTableTabela('saldoestoque');
    //         }
    //     }
    // });

}
function carrega_contagem(obj) {
    // bloqueiaTela();
    var tabela = 'tb_relcontagem';
    var contagem = jQuery("#contagem").val();

    if (contagem != '') {
        // jQuery('#' + tabela).closest('tr').remove();
        url = "/EstRelContagem/busca_dados?contagem=" + contagem;
        montaListaDados(tabela, url);
    }
}


function carrega_saidas(obj) {
    // bloqueiaTela();
    var tabela = 'tb_relsaidas';
    var periodo = jQuery("#periodo").val();
    var startDate = periodo.substr(0, 10); //jQuery("#periodo").data('daterangepicker').startDate.format('DD/MM/YYYY');
    var endDate = periodo.substr(-10); //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');

    var empresa = jQuery("#empresa").val();
    var deposito = jQuery("#deposito").val();

    if (empresa != '') {
        // jQuery('#' + tabela).closest('tr').remove();
        url = "/EstRelSaida/busca_dados?empresa=" + empresa + "&deposito=" + deposito + "&inicio=" + startDate + "&fim=" + endDate;
        montaListaDados(tabela, url);
    }
}

function carrega_entradas(obj) {
    // bloqueiaTela();
    var tabela = 'tb_relentradas';
    var periodo = jQuery("#periodo").val();
    var startDate = periodo.substr(0, 10); //jQuery("#periodo").data('daterangepicker').startDate.format('DD/MM/YYYY');
    var endDate = periodo.substr(-10); //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');

    var empresa = jQuery("#empresa").val();
    var deposito = jQuery("#deposito").val();

    if (deposito != '' && empresa != '') {
        // jQuery('#' + tabela).closest('tr').remove();
        url = "/EstRelEntrada/busca_dados?empresa=" + empresa + "&deposito=" + deposito + "&inicio=" + startDate + "&fim=" + endDate;
        montaListaDados(tabela, url);
    }
}

function carrega_historico(obj) {
    // bloqueiaTela();
    var tabela = 'tb_relhistorico';
    var periodo = jQuery("#periodo").val();
    var startDate = periodo.substr(0, 10); //jQuery("#periodo").data('daterangepicker').startDate.format('DD/MM/YYYY');
    var endDate = periodo.substr(-10); //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');

    var empresa = jQuery("#empresa").val();
    var deposito = jQuery("#deposito").val();
    var produto = jQuery("#produto").val();

    if (deposito != '' && empresa != '' && produto != '') {
        // table = undefined;
        jQuery('#' + tabela).closest('tr').remove();
        url = "/EstRelHistorico/busca_dados?empresa=" + empresa + "&deposito=" + deposito + "&produto=" + produto + "&inicio=" + startDate + "&fim=" + endDate;
        montaListaDados(tabela, url);
    }
}

function carrega_compras(obj) {
    // bloqueiaTela();
    var tabela = 'tb_relcompras';
    var periodo = jQuery("#periodo").val();
    var startDate = periodo.substr(0, 10); //jQuery("#periodo").data('daterangepicker').startDate.format('DD/MM/YYYY');
    var endDate = periodo.substr(-10); //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');

    var empresa = jQuery("#empresa").val();
    var fornecedor = jQuery("#fornecedor").val();

    if (empresa != '') {
        // jQuery('#' + tabela).closest('tr').remove();
        url = "/EstRelCompra/busca_dados?empresa=" + empresa + "&fornecedor=" + fornecedor + "&inicio=" + startDate + "&fim=" + endDate;
        montaListaDadosGrupo(tabela, url, 1);
    }
<<<<<<< HEAD
=======
>>>>>>> 574b0475ba5dde3449b30249cc4ba8e410e8fcd7
=======
>>>>>>> 31cffd33b9425f8976445a0ec535d45f55cf74cd
}
