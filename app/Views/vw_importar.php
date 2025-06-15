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


<?=$this->section('content');
?>
    <div id='content' class='container page-content bg-light m-0'>
    <!-- <div id='content' class='vh-auto page-content dashboard dashboard-app dashboard-content '> -->
    <form id="form1" data-alter method="post" autocomplete="off" action="<?= site_url($controler."/".$destino) ?>" 
            class="col-12" enctype="multipart/form-data" >
        <?
        if (sizeof($secoes)) {
            // MOBILE
            echo "<div id='operacoes' class='col-xs-12 d-flex d-lg-none d-md-none bg-light'>";
			echo "<div id='linksecoes' class='col-xs-12 linksecoes'>";
		    echo "<legend style='font-size:1.5em'>Ir para</legend>";
			echo "<ul class='nav nav-pills nav-stacked'>";
            for($s = 0; $s < count($secoes);$s++){
                $active = '';
                $secao = url_amigavel($secoes[$s]);
                if($s == 0){
                    $active = "active";
                }
                echo "<li class='nav-item ' role='presentation'>";
				echo "<button class='nav-link $active' id='".$secao."-tab' data-bs-toggle='tab' data-bs-target='#".$secao."' type='button' role='tab' aria-controls='".$secao."' aria-selected='false'>";
                echo "<i class='far fa-hand-point-right'> </i> - ";
                echo $secoes[$s]."</button>";
                echo "</li>";
            }
			echo "</ul>";
			echo "</div>";
		    echo "</div>";
            echo "<div class='col-lg-12 col-md-12 d-lg-flex d-none'>";
            echo "<ul class='nav nav-tabs border-0 d-none d-lg-flex d-md-flex' id='myTab' role='tablist'>";
			$active = 'active';
            for ($s=0;$s<sizeof($secoes);$s++) {
                echo "<li class='nav-item ' role='presentation'>";
                $secao = url_amigavel($secoes[$s]);
                echo "<span id='".$secao."-valid' class='float-end valid-tab badge rounded-pill bg-danger d-none'>!</span>";
				echo "<button class='nav-link $active' id='".$secao."-tab' data-bs-toggle='tab' data-bs-target='#".$secao."' type='button' role='tab' aria-controls='".$secao."' aria-selected='false'>";
                echo "<i class='far fa-hand-point-right'> </i> - ";
                echo $secoes[$s];
                echo "</button>";
                echo "</li>";
				$active = '';
            }
			echo "</ul>";
            echo "</div>";
			$active = 'show active';
            echo "<div class='tab-content bg-white' id='myTabContent'>";
            for ($s=0;$s<count($secoes);$s++) {
                $secao = url_amigavel($secoes[$s]);
				echo "<div class='tab-pane fade p-lg-3 p-2 $active' id='".$secao."' role='tabpanel' aria-labelledby='".$secao."-tab' tabindex='0'>";
                if(isset($botaosecao[$s])){
                    echo $botaosecao[$s];
                }
                $campos_se = $campos[$s];
                $display      = (isset($displ[$s]))?$displ[$s]:'';
                $contrep   = 0;
                if ($display == 'tabela') {
                    echo "<div id='rep_".$secao."' class='rep_campos d-inline-table table2-responsive rep_$secao' data-".$secao."-index='$contrep'>";
                    $count = 0;
                    for ($c=0;$c<sizeof($campos_se);$c++) {
                        $campos_se_lin = $campos_se[$c];
                        echo "<div class='row tableDiv table2 table-$secao ".(++$count%2 ? "odd" : "even")."' width='100%' data-index='$contrep'>";
                        echo "<div class='col-11'>";
                        for ($cl=0;$cl<(sizeof($campos_se_lin)-2);$cl++) {
                            $quebra = $campos_se_lin[$cl];
                            if($quebra == 'vazio2'){
                                echo "<div class='row col-6 col-lg-6 float-start d-inline-block g-1 align-items-center mb-0' style='height:60px'></div>";
                            } else if($quebra == 'vazio3'){
                                echo "<div class='row col-6 col-lg-6 float-start d-inline-block g-1 align-items-center mb-0' style='height:60px'></div>";
                            } else {
                                // debug($campos_se_lin[$cl]);
                                echo $campos_se_lin[$cl];
                            }
                        }
                        echo "</div>";
                        if (isset($campos_se_lin[$cl])) {
                            echo "<div class='col-1 d-initial h-auto p-0'>";
                            echo "<div class='col-9 d-block float-start text-center p-0'>";
                            echo $campos_se_lin[$cl];
                            if (isset($campos_se_lin[$cl+1])) {
                                echo $campos_se_lin[$cl+1];
                                echo "</div>";
                                $exclui = strripos($campos_se_lin[$cl+1], 'exclui');
                                if($exclui){
                                    echo "<div class='col-3 d-block float-end text-end'>";
                                    echo "<button name='bt_up[$c]' type='button' id='bt_up[$c]' class='btn btn-outline-info btn-sm bt-up $secao mt-0 float-end' onclick='sobe_desce_item(this,\"sobe\",\"$secao\")' title='Acima' data-index='$c'><i class='fa fa-arrow-up' aria-hidden='true'></i></button>";
                                    echo "<button name='bt_down[$c]' type='button' id='bt_down[$c]' class='btn btn-outline-info btn-sm bt-down $secao mt-0 float-end' onclick='sobe_desce_item(this,\"desce\",\"$secao\")' title='Abaixo' data-index='$c'><i class='fa fa-arrow-down' aria-hidden='true'></i></button>";
                                    echo "</div>";
                                }
                            }
                            echo "</div>";
                        }
                        echo "</div>";
                        $contrep++;
                    }
                    echo "</div>";
                } else {
                    $count = 0;
                    for ($c = 0; $c < sizeof($campos_se); $c++) {
                        $quebra = $campos_se[$c];
                        if($quebra == 'vazio2'){
                            $clear = 'clear: right;';
                            if($count%2){
                                $clear = 'clear: left;';
                            }
                            echo "<div class='row col-6 col-lg-6 float-start d-block mb-0' style='height:60px; $clear'></div>";
                        } else if($quebra == 'vazio3'){
                            $clear = 'clear: right;';
                            if($count%3){
                                $clear = 'clear: left;';
                            }
                            echo "<div class='row col-4 col-lg-4 float-start d-block mb-0' style='height:60px; $clear'></div>";
                        } else {
                            echo $campos_se[$c];
                        }
                        $count++;
                    }
                }
                $contrep++;
                echo "<div id='msgimport' class='col-12'></div>";
                echo "</div>";
                $active = '';
            }
            echo "</div>";
        }
		?>
    </form>
    </div>
    <script src="<?=base_url('assets/jscript/bootstrap-select.js');?>"></script>
    <script src="<?=base_url('assets/jscript/my_fields.js');?>"></script>
    <script src="<?=base_url('assets/jscript/summernote-lite.js');?>"></script>
    <script src="<?=base_url('assets/jscript/jquery.bootstrap-duallistbox.js');?>"></script>
    <!-- include summernote-pt-BR -->
    <script src="<?=base_url('assets/jscript/summ-lang/summernote-pt-BR.js');?>"></script>

    <link rel="stylesheet" href="<?=base_url('assets/css/summernote-lite.css');?>">
    <link rel="stylesheet" href="<?=base_url('assets/css/summernote.min.css');?>">
    <link rel="stylesheet" href="<?=base_url('assets/css/bootstrap-duallistbox.css');?>">
    <link rel="stylesheet" href="<?=base_url('assets/css/bootstrap-select.css');?>">

    <!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjscript/latest/moment.min.js"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <script src="<?=base_url('assets/jscript/my_mask.js');?>"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/fontawesome-iconpicker/3.2.0/js/fontawesome-iconpicker.min.js" integrity="sha512-7dlzSK4Ulfm85ypS8/ya0xLf3NpXiML3s6HTLu4qDq7WiJWtLLyrXb9putdP3/1umwTmzIvhuu9EW7gHYSVtCQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fontawesome-iconpicker/3.2.0/css/fontawesome-iconpicker.min.css" integrity="sha512-BfgviGirSi7OFeVB2z9bxp856rzU1Tyy9Dtq2124oRUZSKXIQqpy+2LPuafc2zMd8dNUa+F7cpxbvUsZZXFltQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js" integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0=" crossorigin="anonymous"></script>

    <script src="<?=base_url('assets/jscript/my_consulta.js');?>"></script>
    <script src="<?=base_url('assets/jscript/my_calc_compras.js');?>"></script>
    <script>
        carregamentos_iniciais();
    </script>
    <?
    if(isset($script)){
        echo $script;
    }
    ?>
    <script>
        jQuery("#form1").attr('data-alter', false);
    </script>
<?=$this->endSection();?>
