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
<div id='content' class='container page-content m-0'>
    <div class='col-12 d-block p-3 float-start bg-white' style="height: 90vh">
    <?
      for ($cs=0;$cs<sizeof($campos);$cs++) {
        echo $campos[$cs];
      }
    ?>
      <div id="graphs" class='col-12 d-block float-start bg-white' style="max-height:78vh;overflow-y:auto;">
      </div>
    </div>
  </div>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2" ></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1"></script>

    <script src="<?=base_url('assets/jscript/jquery-3.6.3.js');?>" defer></script>

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

  <script>

      function carrega_graficos_diretor(){
        jQuery('#graphs').html('');
        indica = jQuery('#indicadores').val();
        if(indica != ''){
          empresas = jQuery('#empresa\\[\\]').val();
          nomempre = [];
          empresas = [];
          jQuery.each(jQuery("#empresa\\[\\] option:selected"), function(){            
              empresas.push(jQuery(this).val());
              nomempre.push(jQuery(this).text())
          });
          var all  = '';
          if(indica == 'fat_dia'){
            var opc= ['Data', ' Faturamento'];
            var gru = 0;
            var graf = ['linhas','barras'];
            var tit = 'Faturamento Diário';
            if(empresas.length > 1){
              var all =  'fat_dia_all';
            }
          } else if(indica == 'fat_sem'){
            var opc= ['Dia da Semana', ' Faturamento Médio'];
            var gru = 0;
            var graf = ['linhas','barras'];
            var tit = 'Faturamento Médio por Dia da Semana';          
            if(empresas.length > 1){
              var all =  'fat_sem_all';
            }
          } else if(indica == 'fat_mes'){
            var opc= ['Mês', ' Faturamento'];
            var gru = 0;
            var graf = ['linhas','barras'];
            var tit = 'Faturamento Mensal (-14 meses)';
            if(empresas.length > 1){
              var all =  'fat_mes_all';
            }
          } else if(indica == 'nps_dia'){
            var opc= ['Dia', ' NPS'];
            var gru = 0;
            var graf = ['linhas','barras'];
            var tit = 'NPS diário';
            if(empresas.length > 1){
              var all =  'nps_dia_all';
            }
          } else if(indica == 'nps_sem'){
            var opc= ['Dia da Semana', ' NPS'];
            var gru = 0;
            var graf = ['linhas','barras'];
            var tit = 'NPS Médio por dia da Semana';
            if(empresas.length > 1){
              var all =  'nps_sem_all';
            }
          } else if(indica == 'nps_mes'){
            var opc= ['Mês', ' NPS'];
            var gru = 0;
            var graf = ['linhas','barras'];
            var tit = 'NPS Médio mensal (-14 meses)';
            if(empresas.length > 1){
              var all =  'nps_mes_all';
            }
          } else if(indica == 'atrasos_deliv'){
            var opc = ['Data', ' Almoço',' Jantar'];
            var gru = 0;
            var graf = ['linhas','barras'];
            var tit  = '% de atrasos com mais de 60 minutos';
            var all  = '';
          }
          for(emp=0;emp<empresas.length;emp++){
              titu =  nomempre[emp]+" - "+tit;
              empr = empresas[emp];
              ncard = indica+"_"+empr;
              criaViewCard(ncard, titu, opc, gru, graf, 'graphs','col-6');
          }
          for(emp=0;emp<empresas.length;emp++){
              empr = empresas[emp];
              ncard = indica+"_"+empr;
              card = jQuery('#'+ncard)[0];
              refresh_card_diretor(card,'b_',empr);
          }
          if(all != ''){
              opc = ['Data'];
              abrev = [];
              abrev[1] = 'TSS';
              abrev[2] = 'YCB';
              abrev[3] = 'YAV';
              abrev[4] = 'DJV';
              abrev[5] = 'DAV';
              abrev[6] = 'DSF';
              abrev[7] = 'YSC';
              abrev[8] = 'CP';
              abrev[9] = 'FSO';
              abrev[10] = 'CSC';
              for(emp=0;emp<empresas.length;emp++){
                empr = abrev[empresas[emp]];
                opc.push(empr);
              }
              titu =  "Comparativo de Empresas - "+tit;
              ncard = all;
              criaViewCard(ncard, titu, opc, gru, graf, 'graphs','col-12');
          }
          if(all != ''){
            empr = empresas;
            card = jQuery('#'+ncard)[0];
            refresh_card_diretor(card,'l_',empr);
          }
        }
      }
      carregamentos_iniciais();
  </script>

    <script>
      // carrega_graficos();

    jQuery(".refresh").on("click", function(){
        // jQuery("#r_"+this.id).addClass("d-none");
        bloqueiaTela();
        var busca = this.id.substr(2);
        var parent = jQuery("#"+busca)[0];
        if(!jQuery(parent).hasClass('collapsed')){
            refresh_card(parent,'b_');
        }
        desBloqueiaTela();
    });

    jQuery('body').on("click",".grafic", function(){
        var tipo  = this.id.substr(0,2);
        var busca = this.id.substr(2);
        var pos = this.id.lastIndexOf('_');
        var empr = this.id.substr(pos+1);
        var parent = jQuery("#"+busca)[0];
        if(empr == 'all'){
          empr = empresas;
        }
        refresh_card_diretor(parent, tipo,empr);
    });

    </script>
<?=$this->endSection();?>
