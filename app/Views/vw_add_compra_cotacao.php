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
  #linhaProdutoFixada table {
    width: 100%;
    border-collapse: collapse;
  }

  #linhaProdutoFixada td,
  #linhaProdutoFixada th {
    padding: 5px;
    background-color: #d1e7dd;
    /* font-weight: bold; */
  }

  .tdComprado {
    background-color: green !important;
    border: 2px green solid !important;
  }

  .dropdown-menu.show {
    z-index: 10000;
  }

  /* 1. Faça o <td> referente “pai” atuar como posição de referência */
  .td-com-borda-90 {
    position: relative;
    /* necessário para o ::after “ancorar” no td */
    /* opcional: padding para dar espaço interno e visualizar melhor */
    padding: 8px;
  }

  /* 2. Use ::after para criar a borda direita com 90% da altura */
  .td-com-borda-90::after {
    content: "";
    position: absolute;
    top: 5%;
    /* deixa 5% de “margem” acima */
    bottom: 5%;
    /* deixa 5% de “margem” abaixo => total de 90% */
    right: 0;
    /* colado na borda direita do td */
    border-right: 1px solid #000;
    /* largura/cor da borda */
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
      <div id="divTable" class="col-12 px-1" style="max-height: 65vh !important;overflow:auto">
        <div class="accordion" id="accProdutos">
        </div>
      </div>
      <div id="divCompras" class="col-12 mt-3 px-2 overflow-auto" style="max-height: 20vh !important;overflow:auto">
        <table id="tb_compra_<?= $nome; ?>" class="table table-sm table-primary table-hover table-borderless col-12">
          <thead class="table-primary col-12 sticky-top">
            <tr>
              <th>Fornecedor</th>
              <th>Itens</th>
              <th>Total</th>
              <th>Ped. Mínimo</th>
              <th>&nbsp;&nbsp;&nbsp;</th>
            </tr>
          </thead>
          <tbody id="itens_tabela_compra" class="text-center mt-3">
          </tbody>
        </table>
      </div>
      <?
      if (isset($destino)) { ?>
      </form>
    <? } ?>
    <!-- </div> -->
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

  </script>
  <?= $this->endSection(); ?>