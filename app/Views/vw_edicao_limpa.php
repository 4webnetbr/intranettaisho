<html>
<head>
<!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">  
  <!-- Ícones -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">

  <!-- Estilo principal -->
    <link rel="stylesheet" href="<?=base_url('assets/css/default.css');?>">
  <!-- JQUERY -->
  <script src="<?=base_url('assets/jscript/jquery-3.6.3.js');?>"></script>
  <!-- Scripts personalizados -->
  <script src="https://kit.fontawesome.com/dcd9f31768.js" crossorigin="anonymous"></script>   
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/6.0.0/bootbox.min.js" integrity="sha512-oVbWSv2O4y1UzvExJMHaHcaib4wsBMS5tEP3/YkMP6GmkwRJAa79Jwsv+Y/w7w2Vb/98/Xhvck10LyJweB8Jsw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script src="<?=base_url('assets/jscript/my_default.js');?>"></script>
    <script src="<?=base_url('assets/jscript/my_fields.js');?>"></script>

    <script src="<?=base_url('assets/jscript/bootstrap-select.js');?>"></script>

    <script src="<?=base_url('assets/jscript/summernote-lite.js');?>"></script>
    <script src="<?=base_url('assets/jscript/jquery.bootstrap-duallistbox.js');?>"></script>
    <!-- include summernote-pt-BR -->
    <script src="<?=base_url('assets/jscript/summ-lang/summernote-pt-BR.js');?>"></script>

    <link rel="stylesheet" href="<?=base_url('assets/css/summernote-lite.css');?>">
    <link rel="stylesheet" href="<?=base_url('assets/css/summernote.min.css');?>">
    <link rel="stylesheet" href="<?=base_url('assets/css/bootstrap-duallistbox.css');?>">
    <link rel="stylesheet" href="<?=base_url('assets/css/bootstrap-select.css');?>">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" integrity="sha512-qTXRIMyZIFb8iQcfjXWCO8+M5Tbc38Qi5WzdPOYZHIlZpzBHG3L3by84BBBOiRGiEb7KKtAOAs5qYdUiZiQNNQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script type="text/javascript" language="javascript" src="<?=base_url('assets/jscript/datetime-moment.js');?>"></script>

    <!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjscript/latest/moment.min.js"></script> -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <script src="<?=base_url('assets/jscript/my_mask.js?noc=' . time());?>"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/fontawesome-iconpicker/3.2.0/js/fontawesome-iconpicker.min.js" 
                    integrity="sha512-7dlzSK4Ulfm85ypS8/ya0xLf3NpXiML3s6HTLu4qDq7WiJWtLLyrXb9putdP3/1umwTmzIvhuu9EW7gHYSVtCQ==" 
                    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fontawesome-iconpicker/3.2.0/css/fontawesome-iconpicker.min.css"
                    integrity="sha512-BfgviGirSi7OFeVB2z9bxp856rzU1Tyy9Dtq2124oRUZSKXIQqpy+2LPuafc2zMd8dNUa+F7cpxbvUsZZXFltQ==" 
                    crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js" 
                integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0=" 
                crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" />
</head>
<body>   
<div id='content' class='container page-content bg-light m-5'>
    <form id="formlimpo" method="post"  action="<?= site_url($controler."/".$destino) ?>" class="col-12">
        <?php
            for ($s = 0; $s < count($secoes); $s++) {
                $secao = url_amigavel($secoes[$s]);
                $campos_se = $campos[$s];
                $display      = (isset($displ[$s])) ? $displ[$s] : '';
                $contrep   = 0;
                for ($c = 0; $c < sizeof($campos_se); $c++) {
                    $quebra = false;
                    $quebra = str_contains($campos_se[$c], 'quebralinha');
                    if($quebra){
                        echo "<div class='row col-6 col-lg-6 float-start d-inline-block g-1 align-items-center mb-3' style='height:60px'></div>";
                    } else {
                        echo $campos_se[$c];
                    }
                }
                echo "</div>";
            }
        ?>
    </form>    
</div>
    <script>
        carregamentos_iniciais();
    </script>
    <?php
    if (isset($script)) {
        echo $script;
    }
    ?>
</body>    
</html>