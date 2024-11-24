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
    var opc= ['Data', ' Faturamento'];
    var gru = 0;
    var graf = ['linhas','barras'];
    criaViewCard('fat_dia', 'Faturamento Diário', opc, gru, graf, 'graphs');

    var opc= ['Dia', ' NPS'];
    var gru = 0;
    var graf = ['linhas','barras'];
    criaViewCard('nps_dia', 'NPS diário', opc, gru, graf, 'graphs');

    var opc= ['Dia da Semana', ' Faturamento'];
    var gru = 0;
    var graf = ['doughnut','pizza','linhas','barras'];
    criaViewCard('fat_sem', 'Faturamento Médio por Dia da Semana', opc, gru, graf, 'graphs');

    var opc= ['Dia da Semana', ' NPS'];
    var gru = 0;
    var graf = ['doughnut','pizza','linhas','barras'];
    criaViewCard('nps_sem', 'NPS Médio por dia da Semana', opc, gru, graf, 'graphs');

    var opc= ['Mês', ' Faturamento'];
    var gru = 0;
    var graf = ['linhas','barras'];
    criaViewCard('fat_mes', 'Faturamento Mensal (-14 meses)', opc, gru, graf, 'graphs');

    var opc= ['Mês', ' NPS'];
    var gru = 0;
    var graf = ['doughnut','pizza','linhas','barras'];
    criaViewCard('nps_mes', 'NPS Médio mensal (-14 meses)', opc, gru, graf, 'graphs');

    var opc = ['Data', ' Almoço',' Jantar'];
    var gru = 0;
    var graf = ['linhas','barras'];
    criaViewCard('atrasos_deliv', '% de atrasos com mais de 60 minutos', opc, gru, graf, 'graphs');

    var opc= ['Clients', ' Avaliações'];
    var gru = 0;
    var graf = ['doughnut','pizza','linhas','barras'];
    criaViewCard('nps_clientes', 'NPS - Clientes', opc, gru, graf, 'graphs');

    function exporta_excel(obj, titulo = '', fn, dl) {
      if(titulo == ''){
        titulo = 'Relatórios Gerenciais';
      }
      jQuery("#"+obj).btechco_excelexport({
        containerid: obj
        , datatype: $datatype.Table
        , filename: titulo
        });
    };
  </script>

  <script>
      function carrega_graficos(){
        if(jQuery("#periodo").val() != ''){
          card1 = jQuery('#fat_dia')[0];
          refresh_card(card1,'b_');
          card7 = jQuery('#nps_dia')[0];
          refresh_card(card7,'b_');
          card2 = jQuery('#fat_sem')[0];
          refresh_card(card2,'b_');
          card6 = jQuery('#nps_sem')[0];
          refresh_card(card6,'b_');
          card8 = jQuery('#fat_mes')[0];
          refresh_card(card8,'b_');
          card9 = jQuery('#nps_mes')[0];
          refresh_card(card9,'b_');
          card3 = jQuery('#atrasos_deliv')[0];
          refresh_card(card3,'b_');
          card5 = jQuery('#nps_clientes')[0];
          refresh_card(card5,'d_');
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

    jQuery(".grafic").on("click", function(){
        var tipo  = this.id.substr(0,2);
        var busca = this.id.substr(2);
        var parent = jQuery("#"+busca)[0];
        refresh_card(parent, tipo);
    });

    </script>
<?=$this->endSection();?>
