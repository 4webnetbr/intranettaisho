<?php
  header("Set-Cookie: cross-site-cookie=whatever; SameSite=Strict;");
  if(!isset($title)){
    $title = $controler;
  }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=$title;?> | <?=APP_NAME?></title>
    <link rel="shortcut icon" href="/assets/images/favicon.ico" type="image/x-icon">

  <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">  
  <!-- Ícones -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">

  <!-- Estilo principal -->
    <link rel="stylesheet" href="<?=base_url('assets/css/default.css');?>">
  <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?=base_url('assets/css/menu.css');?>">

    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <link rel="stylesheet" type="text/css" href="<?=base_url('assets/css/datatables.min.css');?>"/>
    <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/pdfmake.min.js');?>"></script>
    <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/vfs_fonts.js');?>"></script>
    <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/datatables.min.js');?>"></script>
    <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/accent-neutralize.js');?>"></script>
    <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/datetime-moment.js');?>"></script>
    <link rel="stylesheet" type="text/css" href="<?=base_url('assets/css/bootstrap-select.css');?>">
    <link rel="stylesheet" type="text/css" href="<?=base_url('assets/css/datatables.min.css');?>"/>

  <?php
    if(isset($styles) && strlen($styles)>0){
      $styles = explode(',',$styles);
      for ($s=0; $s < sizeof($styles); $s++) {
        $sty    = base_url('assets/css/'.$styles[$s].'.css');
        $style = "<link rel='stylesheet' href='".$sty."'>";
        echo $style;
      }
    }
  ?>
  
  <!-- Links JAVASCRIPTS -->
  <!-- JQUERY -->
    <script src="<?=base_url('assets/jscript/jquery-3.6.3.js');?>"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js" integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0=" crossorigin="anonymous"></script>
  <!-- Scripts personalizados -->
    <script src="https://kit.fontawesome.com/dcd9f31768.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/6.0.0/bootbox.min.js" integrity="sha512-oVbWSv2O4y1UzvExJMHaHcaib4wsBMS5tEP3/YkMP6GmkwRJAa79Jwsv+Y/w7w2Vb/98/Xhvck10LyJweB8Jsw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>


    <script src="<?=base_url('assets/jscript/my_default.js');?>"></script>
    <script src="<?=base_url('assets/jscript/my_menu.js');?>"></script>
  <?php
    if(isset($scripts) && strlen($scripts)>0){
      $scripts = explode(',',$scripts);
      for ($s=0; $s < sizeof($scripts); $s++) {
        $scr    = base_url('assets/jscript/'.$scripts[$s].'.js');
        $script = "<script src=".$scr."></script>";
        echo $script;
      }
    }
  ?>
    
  <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>  
</head>
<body class="user-select-none">
  <!-- início do preloader -->
  <div id="bloqueiaTela">
    <div id="preloader" class='preloader bloqTela d-block align-items-center justify-content-center'></div>
    <div class='bg-info px-5 py-3 border border-1 border-dark rounded-pill msgprocessando'> 
      <div class='spinner-border spinner-border-sm mx-2'></div>
      Processando...
    </div>
  </div>
    <!-- fim do preloader -->   
    <section class="header">
        <?=$this->renderSection('titulo');?>
    </section>
    <section class="menu">
        <?=$this->renderSection('menu');?>
    </section>
    <section class="content">
        <?=$this->renderSection('content');?>
    </section>
    <section class="manutencao">
        <?=$this->renderSection('manutencao');?>
    </section>
    <section class="footer">
        <?=$this->renderSection('footer');?>
    </section>
    <?=view('strut/vw_modal');?>

    <div class="toast bg-success position-absolute end-0 top-0 text-white fade z-1 hide" id="myToast" data-animation="true" data-bs-autohide="true">
      <div class="toast-header">
          <strong class="me-auto"><i class="bi bi-info-circle"></i> Informação</strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
      </div>
      <div class="toast-body">
        <?= session('msg');?>
      </div>
    </div>
</body>
</html>
<script src="<?=base_url('assets/jscript/bootstrap-select.js');?>"></script>
<script src="<?=base_url('assets/jscript/my_fields.js');?>"></script>
<script src="<?=base_url('assets/jscript/my_filter.js');?>"></script>
<script src="<?=base_url('assets/jscript/my_grafic.js');?>"></script>
<script src="<?=base_url('assets/jscript/my_lista.js');?>"></script>
<script src="<?=base_url('assets/jscript/my_mask.js?noc=' . time());?>"></script>
<link rel="stylesheet" href="<?=base_url('assets/css/bootstrap-select.css');?>">

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" />
  <?
  if(isset($script)){
      echo $script;
  }
  ?>
  <script src="<?=base_url('assets/jscript/my_calc_compras.js');?>"></script>

<script>
//<![CDATA[
  jQuery( window ).on("load", function() {
    jQuery('#bloqueiaTela').delay(100).fadeOut(100); 
  })
//]]>  

</script>