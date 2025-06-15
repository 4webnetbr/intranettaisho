<?= $this->extend('templates/default_template') ?>

<?= $this->section('header'); ?>
<?= view('strut/vw_titulo'); ?>
<?= $this->endSection(); ?>

<?= $this->section('menu'); ?>
<?= view('strut/vw_menu'); ?>
<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<?= view('strut/vw_rodape'); ?>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/datatables.min.css'); ?>" />
<script type="text/javascript" language="javascript" src="<?= base_url('assets/jscript/pdfmake.min.js'); ?>"></script>
<script type="text/javascript" language="javascript" src="<?= base_url('assets/jscript/vfs_fonts.js'); ?>"></script>
<script type="text/javascript" language="javascript" src="<?= base_url('assets/jscript/datatables.min.js'); ?>"></script>
<script type="text/javascript" language="javascript" src="<?= base_url('assets/jscript/accent-neutralize.js'); ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
  // <script type="text/javascript" language="javascript" src="<?= base_url('assets/jscript/moment.min.js'); ?>">
</script>
<script type="text/javascript" language="javascript" src="<?= base_url('assets/jscript/datetime-moment.js'); ?>"></script>
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/bootstrap-select.css'); ?>">
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/datatables.min.css'); ?>" />
<style>
  .dataTables_scroll {
    min-height: 69vh;
    height: 69vh;
    max-height: 69vh;
  }

  .dataTables_scrollBody {
    min-height: 64vh;
    height: 64vh;
    max-height: 64vh !important;
  }
</style>
<div id='content' class='container page-content m-0'>
  <div class='col-12 d-block p-3 float-start bg-white' style="height: 90vh">
    <?
    if (isset($destino)) { ?>
      <form id="form1" data-alter method="post" autocomplete="off" action="<?= site_url($controler . "/" . $destino) ?>"
        class="col-12" enctype="multipart/form-data">
      <? } ?>
      <?
      if (isset($campos)) {
        for ($cs = 0; $cs < sizeof($campos); $cs++) {
          echo $campos[$cs];
        }
      }
      ?>
      <input type="hidden" id="ag_<?= $nome; ?>" value="0" />
      <div class="table-responsive col-12 px-2 overflow-auto" style="max-height: 80vh !important;">
        <table id="tb_<?= $nome; ?>" class="display compact table table-sm table-info table-striped table-hover table-borderless col-12">
          <thead class="table-default col-12 overflow-x-auto">
            <tr>
              <?
              for ($c = 0; $c < count($colunas); $c++) {
                echo "<th>";
                echo $colunas[$c];
                echo "</th>";
              }
              ?>
            </tr>
          </thead>
          <tbody id='itens_table' class="text-center">
          </tbody>
        </table>
        <?
        if (isset($destino)) { ?>
      </form>
    <? } ?>
  </div>
</div>
<script src="<?= base_url('assets/jscript/bootstrap-select.js'); ?>"></script>
<script src="<?= base_url('assets/jscript/my_fields.js'); ?>"></script>
<script src="<?= base_url('assets/jscript/my_filter.js'); ?>"></script>
<script src="<?= base_url('assets/jscript/my_grafic.js'); ?>"></script>
<script src="<?= base_url('assets/jscript/my_lista.js'); ?>"></script>
<script src="<?= base_url('assets/jscript/my_mask.js?noc=' . time()); ?>"></script>
<link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-select.css'); ?>">

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" />
<?
if (isset($script)) {
  echo $script;
}
?>
<script>
  function carregaContagem(obj, url, destino) {
    bloqueiaTela();
    var tabela = 'tb_' + destino;

    var empresa = jQuery("#emp_id").val();
    var deposito = jQuery("#dep_id").val();

    if (deposito != '' && empresa != '') {
      url = url + "?empresa=" + empresa + "&deposito=" + deposito;
      jQuery.ajax({
        type: 'POST',
        async: true,
        dataType: 'json',
        url: url,
        success: function(retorno) {
          text = '';
          for (let r = 0; r < retorno.length; r++) {
            const element = retorno[r];
            text += "<tr>";
            text += "<td style='text-align:left'>";
            text += element.gru_nome;
            text += "</td>";
            text += "<td style='text-align:left'>";
            text += element.pro_nome;
            text += "</td>";
            text += "<td>";
            text += element.saldo;
            text += "</td>";
            text += "<td style='text-align:left'>";
            text += element.und_nome;
            text += "</td>";
            text += "</tr>";
            text += "<tr>";
            text += "<td colspan='4'>";
            text += "<div class='col-12 d-block bg-primary text-bg-primary'>";
            text += "<div style='text-align:center;' class='col-4 d-inline-flex ms-5 float-start'>";
            text += "Marca";
            text += "</div>";
            text += "<div style='text-align:center' class='col-3 d-inline-flex float-start'>";
            text += "Apresentação";
            text += "</div>";
            text += "<div style='text-align:center' class='col-4 d-inline-flex float-start'>";
            text += "Quantia";
            text += "</div>";
            text += "</div>";
            for (let m = 0; m < element.marcas.length; m++) {
              const marca = element.marcas[m];
              text += "<div style='text-align:left;' class='col-4 d-inline-flex ms-5 float-start'>";
              text += marca.mar_nome;
              text += "</div>";
              text += "<div style='text-align:left' class='col-3 d-inline-flex float-start'>";
              text += marca.mar_apresenta;
              text += "</div>";
              text += "<div style='text-align:right' class='col-4 d-inline-flex float-start'>";
              text += marca.ctp_quantia;
              text += "</div>";
            }
            text += "</div>";
            text += "</td>";
            text += "<td style='display:none'>";
            text += "</td>";
            text += "<td style='display:none'>";
            text += "</td>";
            text += "<td style='display:none'>";
            text += "</td>";
            text += "</tr>";
          }
          jQuery('#itens_table').html(text);
          jQuery('#' + tabela).DataTable({
            "scrollY": 'calc(100vh - 15rem)',
            "columnDefs": [{
              "orderable": false,
              "targets": ['_all']
            }],
            "dom": 't', // Mostra apenas o filtro (search) e a tabela
            "ordering": false, // Habilita a ordenação
            "paging": false, // Desativa a paginação
            "info": true, // Oculta o texto informativo
            "language": {
              "url": 'https://intranet.taisho.com.br/assets/jscript/datatables-lang/pt-BR.json',
            },
          });
          desBloqueiaTela();
        }
      });
    }
  }
</script>

<!-- <script>
      carrega_lista(false, '<?= $url_lista; ?>','<?= $nome; ?>');
    </script> -->
<?= $this->endSection(); ?>