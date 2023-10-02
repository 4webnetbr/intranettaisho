/**
 * montaListaDados
 * Monta o dataTable com os dados da Classe
 * @param string tabela 
 * @param string url 
 */
function montaListaDados(tabela, url){
    jQuery.fn.dataTable.moment( 'DD/MM/YYYY HH:mm:ss' );    //Formatação com Hora
    jQuery.fn.dataTable.moment('DD/MM/YYYY');    //Formatação sem Hora

    // monta o Datable 
    jQuery('#'+tabela).dataTable(
    {
        "ajax": {
            "url": url,
            "type": "Post",
            "datatype": "json"
        },
        // "retrieve": true,
        // "bJQueryUI": true,
        "responsive": false,
        "sPaginationType": "full_numbers",
        "aaSorting": [],
        "columnDefs": [
            { "visible": false, "targets": [0] }, 
            { "max-width": "8em", "targets": [-1] },
            { "min-width": "8em", "targets": [-1] },
            { "width": "8em", "targets": [-1] },
            { "orderable": false, "targets": [-1] },
            { "className": "text-center acao", "targets": [-1]},
            { "className": "acao", "targets": [0] },
            { "className": "text-wrap text-nowrap", "targets": ['all']},
            { "searchBuilder":{defaultCondition: '='}},
        ],
        "order": [
            [1, 'DESC']
        ],
        "buttons": {
            dom: {
                button: {
                    className: "btn btn-outline-primary btn-sm"
                }
            },
            buttons: [
                {
                    extend: 'searchBuilder', 
                    config:{
                        id: 'bt_filtro',
                        columns: [':not(.acao)'],
                        defaultCondition: '=',
                    },
                    preDefined: {
                        criteria:[
                            {
                                condition: '=',
                            },
                        ]
                    }
                },
                {
                    extend: 'excelHtml5',
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
                    text: 'PDF',
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
                    text: 'Imprimir',
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
        "scrollY": 'calc(100vh - 15rem)',
        "bProcessing": true,
        "bScrollCollapse": true,
        "deferRender": true,
        "sDom": 'lftrBip', 
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

    jQuery('#'+tabela).on('click', 'tbody tr td:not(".acao")', function() {
        link = jQuery(this).parent().find('a')[0].href;
        if(link != null){
            if(link.indexOf('edit/') > -1 || link.indexOf('show/') > -1){
                redireciona(link);
            }
        }
    });
}