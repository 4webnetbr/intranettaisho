<?=$this->extend('templates/default_template')?>
<?=$this->section('header');?>
<?=view('strut/vw_titulo');?>
<?//=view('strut/vw_header');?>
<?=$this->endSection();?>

<?=$this->section('menu');?>
  <?=view('strut/vw_menu');?>
<?=$this->endSection();?>

<?=$this->section('content');?>
<div id='content' class='container page-content bg-light m-0'>
  <div class="table-responsive col-12">
    <table id="table" class="display compact table table-sm table-info table-striped table-hover table-borderless w-100">
      <thead class="table-default">
        <tr>
        <?
          for ($c = 0; $c < sizeof($colunas); $c++){
            echo "<th class='text-center text-nowrap'><h5>$colunas[$c]</h5></th>";
          }
        ?>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
</div>
<link rel="stylesheet" type="text/css" href="<?=base_url('assets/css/bootstrap-select.css');?>">
<link rel="stylesheet" type="text/css" href="<?=base_url('assets/css/datatables.min.css');?>"/>

<script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/bootstrap-select.js');?>"></script>
<script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/pdfmake.min.js');?>"></script>
<script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/vfs_fonts.js');?>"></script>
<script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/datatables.min.js');?>"></script>
<script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/accent-neutralize.js');?>"></script>
<script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/moment.min.js');?>"></script>
<script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/datetime-moment.js');?>"></script>
<script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/my_lista.js');?>"></script>


<script>
  montaListaDados('table','<?=$url_lista;?>');
</script>
<?=$this->endSection();?>
