<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?=APP_NAME;?></title>

  <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <!-- Bootstrap icones -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
  <!-- Estilos personalizados -->
  <?php 
  $nocache = time();
  ?>
    <link rel="stylesheet" href="<?=base_url('assets/css/style.css?nocache='.$nocache);?>">
    <link rel="stylesheet" href="<?=base_url('assets/css/menu.css?nocache='.$nocache);?>">
    <link rel="stylesheet" href="<?=base_url('assets/css/default.css?nocache='.$nocache);?>">
    
  <!-- Scripts personalizados -->
    <script src="<?=base_url('assets/jscript/jquery-3.6.3.js');?>"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

    <script src="<?=base_url('assets/jscript/my_fields.js');?>"></script>
    <script src="<?=base_url('assets/jscript/my_grafic.js');?>"></script>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    
  <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2" ></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1"></script>
  <script src="https://kit.fontawesome.com/dcd9f31768.js" crossorigin="anonymous"></script>

</head>
<body>
    <section class="content">
        <?=$this->renderSection('content');?>
    </section>
</body>
</html>
