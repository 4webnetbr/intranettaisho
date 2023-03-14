<?=$this->extend('templates/default_template')?>
<?=$this->section('header');?>
<?=view('strut/vw_titulo');?>
<?//=view('strut/vw_header');?>
<?=$this->endSection();?>

<?=$this->section('menu');?>
  <?=view('strut/vw_menu');?>
<?=$this->endSection();?>

<?=$this->section('content');?>
  <div id='content' class='container page-content bg-light'>
  <!-- <div id='content' class='container page-content dashboard dashboard-app dashboard-content'> -->
    <div class="table-responsive">
    <table id="table" class="display compact table table-sm table-info table-striped table-hover table-borderless w-100">
      <thead class="table-default">
      <tr>
      <?
      for($c=0;$c<sizeof($colunas);$c++){
        echo "<th class='text-center'>$colunas[$c]</th>";
      }
      ?>
      </tr>
      </thead>
    </table>
    </div>
  </div>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/jszip-2.5.0/dt-1.13.2/b-2.3.4/b-colvis-2.3.4/b-html5-2.3.4/b-print-2.3.4/cr-1.6.1/date-1.3.0/fc-4.2.1/fh-3.3.1/kt-2.8.1/r-2.4.0/rg-1.3.0/rr-1.3.2/sc-2.1.0/sb-1.4.0/sp-2.1.1/sl-1.6.0/sr-1.2.1/datatables.min.css"/>
 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs5/jszip-2.5.0/dt-1.13.2/b-2.3.4/b-colvis-2.3.4/b-html5-2.3.4/b-print-2.3.4/cr-1.6.1/date-1.3.0/fc-4.2.1/fh-3.3.1/kt-2.8.1/r-2.4.0/rg-1.3.0/rr-1.3.2/sc-2.1.0/sb-1.4.0/sp-2.1.1/sl-1.6.0/sr-1.2.1/datatables.min.js"></script>

    <script>
      jQuery('#table').dataTable({
        "ajax" : "<?=$url_lista;?>",
        "responsive": true,
        "sPaginationType": "full_numbers",
        "aaSorting": [],
        "oLanguage": {
          "sEmptyTable": "Nenhum registro encontrado",
          "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
          "sInfoEmpty": "Nenhum registro",
          "sInfoFiltered": "(Filtrados de _MAX_ registros)",
          "sInfoPostFix": "",
          "sInfoThousands": ".",
          "sLengthMenu": "_MENU_ linhas por página",
          "sLoadingRecords": "Carregando...",
          "sProcessing": "Processando...",
          "sZeroRecords": "Nenhum registro encontrado",
          "sSearch": "Busca: ",
          "oPaginate": {
              "sNext": "Próx. <i class='fa fa-angle-right'></i>",
              "sPrevious": "<i class='fa fa-angle-left'></i> Ante.",
              "sFirst": "<i class='fa fa-angle-double-left'></i> Prim.",
              "sLast": "Ulti. <i class='fa fa-angle-double-right'></i>"
          },
          "oAria": {
            "sSortAscending": ": Ordenar Ascendente",
            "sSortDescending": ": Ordenar Descendente"
          }
        },
        "columnDefs": [
          { "visible": false, "targets": 0 }, 
          { "width": "8em", "targets": -1 },
          { "orderable": false, "targets": -1 },
          { "className": "text-center acao", "targets": -1},
          { "className": "acao", "targets": 0 },
          { "className": "text-wrap", "targets": 1},
        ],
        "bProcessing": true,
        "bScrollCollapse": true,
        "bPaginate": true,
        // "sDom": 'frtBip',
        "fnDrawCallback": function (oSettings) {
          jQuery('table#table > tbody > tr').on("mouseover",function() {
            jQuery(this).children().find('.btn').addClass('hover');
          }).on('mouseleave', function () {
            jQuery(this).children().find('.btn').removeClass('hover');
          });
        }
      });
    </script>
<?=$this->endSection();?>
