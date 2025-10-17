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
<div id="content" class="container page-content m-0">
  <div class="col-12 d-block p-3 float-start bg-white" style="height: 90vh">
    <?php
    for ($cs = 0; $cs < sizeof($campos); $cs++) {
      echo $campos[$cs];
    }
    ?>
    <div id="indicadores" class="col-12 d-block float-start bg-white" style="max-height:78vh;overflow-y:auto;">
    </div>
  </div>
</div>

<!-- externals -->

<script src="<?= base_url('assets/jscript/bootstrap-select.js'); ?>"></script>
<script src="<?= base_url('assets/jscript/my_fields.js'); ?>"></script>
<!-- aqui usamos a nova biblioteca de gráficos -->

<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/bootstrap-select.css'); ?>" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript" src="<?= base_url('assets/jscript/datetime-moment.js'); ?>"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script src="<?= base_url('assets/jscript/my_mask.js?noc=' . time()); ?>"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"
  integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0="
  crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" />

<script>
async function carrega_dash_compras() {
    // Carregar empresas selecionadas
  const empresas = [];
  const nomempre = [];
  jQuery("#empresa\\[\\] option:selected").each(function() {
    empresas.push(jQuery(this).val());
    nomempre.push(jQuery(this).text());
  });

  const periodo = jQuery("#periodo").val();
  const startDate = periodo.substr(0, 10);
  const endDate = periodo.substr(-10);
  const url = "/DashCompras/busca_dados";

  return new Promise((resolve, reject) => {
    jQuery.ajax({
      type: "POST",
      url: url,
      async: true,
      dataType: "html",
      data: {
        periodo: periodo,
        inicio: startDate,
        fim: endDate,
        empresa: empresas,
      },
      beforeSend: function () {
      },
      success: function (retorno) {
        jQuery('#indicadores').html(retorno);
        resolve(true); // Sinaliza sucesso
      },
      error: function (xhr, status, error) {
        console.error("Erro AJAX:", status, error);
        resolve(false); // Ainda resolve, mas com falha
      },
      complete: function () {
      },
    });
  });
}
</script>
<?= $this->endSection(); ?>