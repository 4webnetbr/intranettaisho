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
<link rel="stylesheet" type="text/css" href="<?=base_url('assets/css/datatables.min.css');?>"/>
    <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/pdfmake.min.js');?>"></script>
    <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/vfs_fonts.js');?>"></script>
    <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/datatables.min.js');?>"></script>
    <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/accent-neutralize.js');?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
    // <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/moment.min.js');?>"></script>
    <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/datetime-moment.js');?>"></script>
    <link rel="stylesheet" type="text/css" href="<?=base_url('assets/css/bootstrap-select.css');?>">
    <link rel="stylesheet" type="text/css" href="<?=base_url('assets/css/datatables.min.css');?>"/>
    <style>
        .dataTables_scroll {
            min-height: 68vh;
            height: 68vh;
            max-height: 68vh;
        }
        .dataTables_scrollBody {
            min-height: 62vh;
            height: 62vh;
            max-height: 62vh !important;
        }
    </style>
<div id='content' class='container page-content m-0'>
    <div class='col-12 d-block p-3 float-start bg-white' style="height: 90vh">
    <?
      for ($cs=0;$cs<sizeof($campos);$cs++) {
        echo $campos[$cs];
      }
    ?>
        <input type="hidden" id="ag_<?=$nome;?>" value="0" />
        <div class="table-responsive col-12 px-2 overflow-auto" style="max-height: 80vh !important;" > 
        <table id="tb_<?=$nome;?>" class="compact table table-sm table-striped table-info table-hover col-12">
          <thead class="table-default col-12 overflow-x-auto">
          <tr>
          <?
              for ($c = 0; $c < count($cols); $c++) {
                  echo "<th  class='text-center'>";
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
  </div>
  <script src="<?=base_url('assets/jscript/bootstrap-select.js');?>"></script>
  <script src="<?=base_url('assets/jscript/my_fields.js');?>"></script>
  <script src="<?=base_url('assets/jscript/my_filter.js');?>"></script>
  <script src="<?=base_url('assets/jscript/my_grafic.js');?>"></script>
  <script src="<?=base_url('assets/jscript/my_lista.js');?>"></script>
  <script src="<?=base_url('assets/jscript/my_mask.js?noc=' . time());?>"></script>

  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" />
<?=$this->endSection();?>
