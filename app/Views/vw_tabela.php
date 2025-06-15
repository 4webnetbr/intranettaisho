<?=$this->extend('templates/graph_template')?>

<?=$this->section('content');
?>
    <link rel="stylesheet" type="text/css" href="<?=base_url('assets/css/datatables.min.css');?>"/>
    <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/pdfmake.min.js');?>"></script>
    <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/vfs_fonts.js');?>"></script>
    <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/datatables.min.js');?>"></script>
    <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/accent-neutralize.js');?>"></script>
    <!-- <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/moment.min.js');?>"></script> -->
    <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/datetime-moment.js');?>"></script>
    <style>
        .dataTables_scroll {
            min-height: 75vh;
            height: 77vh;
            max-height: 77vh;
        }
        .dataTables_scrollBody {
            min-height: 72vh;
            height: 72vh;
            max-height: 72vh !important;
        }
    </style>
    <div id='content' class='container page-content bg-light' style='max-height: 98vh;width: 98%'>
        <h4><?=$titulo.' - '.$periodo;?></h4>
        <input type="hidden" id="ag_<?=$nome;?>" value="0" />
        <div class="table-responsive col-12 px-2 overflow-auto" style="max-height: 80vh !important;" > 
        <table id="tb_<?=$nome;?>" class="display compact table table-sm table-info table-striped table-hover table-borderless col-12">
        <thead class="table-default col-12 overflow-x-auto">
        <tr>
        <?
            for ($c = 0; $c < count($cols); $c++) {
                echo "<th>";
                echo $cols[$c];
                echo "</th>";
            }
        ?>
        </tr>
    </thead>
    <tbody class="text-center">
    </tbody>
    </table>
    </div>
    <script src="<?=base_url('assets/jscript/my_mask.js?noc=' . time());?>"></script>
    <script src="<?=base_url('assets/jscript/my_grafic.js');?>"></script>
    <script>
        <?php
            $js_array = json_encode($dados);
            echo "var dados_js = ". $js_array . ";\n";
            echo "var nome    = '".$nome. "';\n";
    	?>
        montaTabela(nome, dados_js);

        table = jQuery('#tb_' + nome).DataTable(
        {
            "retrieve": true,
            // "stateSave": true,
            "sPaginationType": "full_numbers",
            "aaSorting": [],
            "order": 1,
            "columnDefs": [
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
            "scrollY": 'calc(100vh - 15rem)',
            "bProcessing": true,
            "bScrollCollapse": true,
            "deferRender": true,
            "sDom": 'lftrBip',
            "language": {
                "url": '../assets/jscript/datatables-lang/pt-BR.json',
            },
            "fnDrawCallback": function (oSettings) {
                jQuery('table#tb_' + nome + ' > tbody > tr').on("mouseover", function () {
                    jQuery(this).children().find('.btn').addClass('hover');
                }).on('mouseleave', function () {
                    jQuery(this).children().find('.btn').removeClass('hover');
                });
            },
        });
    </script>
<?=$this->endSection();?>
