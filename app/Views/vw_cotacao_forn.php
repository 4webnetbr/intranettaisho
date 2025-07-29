<?= $this->extend('templates/cotacao_template') ?>
<?= $this->section('content'); ?>
<style>
  .content {
    max-width: calc(100vw - 2rem);
    width: calc(100vw - 2rem);
    min-width: calc(100vw - 2rem);
    margin-left: 1rem;
    margin-right: 1rem;
  }

  #cardsContainer {
    max-height: calc(100dvh - 15rem);
    height: fit-content;
    overflow-y: auto;
  }


  @media (max-width: 767.98px) {
    #cardsContainer {
      /* Ajuste a altura conforme quiser — 80vh ocupa 80% da altura da viewport */
      max-height: calc(100dvh - 25rem);
      overflow-y: scroll;
      padding-bottom: 5rem !important;
    }
  }


</style>
<div id='content' class='container-fluid page-content p-2'>
  <form id="form1" data-alter method="post" autocomplete="off" action="<?= site_url(strtolower($controler) . "/" . $destino) ?>"
    class="col-12" enctype="multipart/form-data">
    <div class='col-12 d-block float-start bg-white mb-0 p-2'>
      <?
      if (isset($campos)) {
        for ($cs = 0; $cs < sizeof($campos); $cs++) {
          echo $campos[$cs];
        }
      }
      ?>
      <!-- Coloque este container em algum lugar da sua view (por exemplo, logo abaixo do input #cot_id) -->
      <div id="cardsContainer" class="col-12 m-0 p-0"></div>
    </div>

    </forn>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/6.0.0/bootbox.min.js" defer integrity="sha512-oVbWSv2O4y1UzvExJMHaHcaib4wsBMS5tEP3/YkMP6GmkwRJAa79Jwsv+Y/w7w2Vb/98/Xhvck10LyJweB8Jsw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?= base_url('assets/jscript/my_default.js'); ?>" defer></script>
<script src="<?= base_url('assets/jscript/my_fields.js'); ?>" defer></script>
<script src="<?= base_url('assets/jscript/my_mask.js'); ?>" defer></script>
<script src="<?= base_url('assets/jscript/my_filter.js'); ?>" defer></script>
<script src="<?= base_url('assets/jscript/my_consulta.js'); ?>" defer></script>

<?
if (isset($script)) {
  echo $script;
}
?>
<?= $this->endSection(); ?>