<?=$this->extend('templates/default_template')?>

<?=$this->section('header');?>
  <?=view('strut/vw_titulo');?>
<?=$this->endSection();?>

<?=$this->section('menu');?>
    <?=view('strut/vw_menu');?>
<?=$this->endSection();?>

<?=$this->section('footer');?>
  <?=view('strut/vw_rodape');?>
<?=$this->endSection();?>

<?=$this->section('content');?>
<style>
.dataTables_scroll {
    min-height: 63vh!important;
    height: 63vh!important;
    max-height: 63vh!important;
}  
.dataTables_scrollBody{
    min-height: 57vh!important;
    height: 57vh!important;
    max-height: 57vh!important;
}
</style>

<div id='content' class='container page-content m-0'>
    <div class='col-2 d-block p-3 float-start bg-white' style="height: 90vh">
    <?
      for ($cs=0;$cs<sizeof($campos);$cs++) {
        echo $campos[$cs];
      }
    ?>
        </div>
      <div id="conteudo" class='col-10 d-block float-start bg-white' style="max-height:90vh;overflow-y:auto;">
      </div>
  </div>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2" ></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1"></script>

  <script src="<?=base_url('assets/jscript/bootstrap-select.js');?>"></script>
  <script src="<?=base_url('assets/jscript/my_fields.js');?>"></script>
  <script src="<?=base_url('assets/jscript/my_grafic.js');?>"></script>

  <link rel="stylesheet" type="text/css" href="<?=base_url('assets/css/bootstrap-select.css');?>">
  <link rel="stylesheet" type="text/css" href="<?=base_url('assets/css/datatables.min.css');?>"/>

  <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/bootstrap-select.js');?>"></script>
  <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/pdfmake.min.js');?>"></script>
  <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/vfs_fonts.js');?>"></script>
  <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/datatables.min.js');?>"></script>
  <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/accent-neutralize.js');?>"></script>
  
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/datetime-moment.js');?>"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

  <script src="<?=base_url('assets/jscript/my_mask.js?noc=' . time());?>"></script>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js" 
                integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0=" 
                crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" />

<?=$this->endSection();?>
