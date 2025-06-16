<?=$this->extend('templates/graph_template')?>

<?=$this->section('content');?>
<div class='col-12 d-none p-3 float-start bg-white' style="height: 10vh">
            <?
            for ($cs=0;$cs<sizeof($campos);$cs++) {
                echo $campos[$cs];
            }
            ?>
        </div>
    <div id='content' class='container page-content bg-light' style='max-height: 95vh;width: 98%'>
        <h4><?=$titulo.' - '.$periodo;?></h4>
        <div id="graphs" class='col-12 d-block float-start bg-white' style="max-height:95vh;overflow-y:auto;">
        </div>
    </div>
    <script src="<?=base_url('assets/jscript/my_mask.js?noc=' . time());?>"></script>
    <script src="<?=base_url('assets/jscript/my_grafic.js');?>"></script>
    <script>
        <?php
            // $js_array = json_encode($dados);
            // $js_cores = json_encode($cores);
            // echo "var dados_js = ". $js_array . ";\n";
            // echo "var cores_js = ". $js_cores . ";\n";
            // echo "var tipo_js = '".$tipo. "';\n";
            // echo "var nome    = '".$nome. "';\n";
            $cols = rtrim($cols,',');
            $acols = explode(',',$cols);
            // debug($acols);
            $scols = '';
            for ($i=0; $i < count($acols) ; $i++) { 
                $scols .= "'".$acols[$i]."',";
            }
            // echo $scols;
            $cols = rtrim($scols,',');
            ?>

        var opc = [<?=$cols;?>];
        var gru = 0;
        var graf = ['<?=$tipodesc;?>'];
        criaCardGraf('<?=$nome;?>', '<?=$titulo;?>', opc, gru, graf, 'graphs');

        let card = jQuery('#<?=$nome;?>')[0];
        let ncard = '<?=$nome;?>';
        let f = "_";
        let count = ncard.split(f).length - 1; 
        if(count == 1){
            refresh_card(card,'<?=$tipo;?>');
        } else {
            var pos = ncard.lastIndexOf('_');
            var empr = ncard.substr(pos+1);
            if(empr == 'all'){
                empr = [];
                jQuery.each(jQuery("#empresa option:selected"), function () {
                    empr.push(jQuery(this).val());
                });
            }
            refresh_card_diretor(card, '<?=$tipo;?>',empr);
        }

        // refresh_card()
        // montaGrafico(nome, dados_js.dados, tipo_js, cores_js);
    </script>
<?=$this->endSection();?>
