<?= $this->extend('templates/default_template') ?>

<?= $this->section('header'); ?>
<?= view('strut/vw_titulo'); ?>
<?= $this->endSection(); ?>

<?= $this->section('menu'); ?>
<?= view('strut/vw_menu'); ?>
<?= $this->endSection(); ?>

<?= $this->section('footer'); ?>
<?= view('strut/vw_rodape'); ?>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<div id="content" class="container page-content m-0">
  <div class="col-12 d-block p-3 float-start bg-white" style="height: 90vh">
    <?php
    for ($cs = 0; $cs < sizeof($campos); $cs++) {
      echo $campos[$cs];
    }
    ?>
    <div id="graphs" class="col-12 d-block float-start bg-white" style="max-height:78vh;overflow-y:auto;">
    </div>
  </div>
</div>

<!-- externals -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js@3.5.1/dist/chart.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.0.1"></script>

<script src="<?= base_url('assets/jscript/bootstrap-select.js'); ?>"></script>
<script src="<?= base_url('assets/jscript/my_fields.js'); ?>"></script>
<!-- aqui usamos a nova biblioteca de gráficos -->
<script src="<?= base_url('assets/jscript/my_graficv2.js'); ?>"></script>

<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/bootstrap-select.css'); ?>" />
<link rel="stylesheet" type="text/css" href="<?= base_url('assets/css/datatables.min.css'); ?>" />

<script type="text/javascript" src="<?= base_url('assets/jscript/pdfmake.min.js'); ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/jscript/vfs_fonts.js'); ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/jscript/datatables.min.js'); ?>"></script>
<script type="text/javascript" src="<?= base_url('assets/jscript/accent-neutralize.js'); ?>"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript" src="<?= base_url('assets/jscript/datetime-moment.js'); ?>"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script src="<?= base_url('assets/jscript/my_mask.js?noc=' . time()); ?>"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"
  integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0="
  crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" />

<script>
  // function carrega_graficos_diretor() {
  //   jQuery('#graphs').html('');
  //   indica = jQuery('#indicadores').val();
  //   if (indica != '') {
  //     // pega empresas selecionadas
  //     empresas = [];
  //     nomempre = [];
  //     jQuery.each(jQuery("#empresa\\[\\] option:selected"), function() {
  //       empresas.push(jQuery(this).val());
  //       nomempre.push(jQuery(this).text());
  //     });
  //     var all = '';
  //     // definição de colunas, título e gráfico por indicador
  //     if (indica == 'fat_dia') {
  //       var opc = ['Data', ' Faturamento'];
  //       var gru = 0;
  //       var graf = ['linhas', 'barras'];
  //       var tit = 'Faturamento Diário';
  //       if (empresas.length > 1) all = 'fat_dia_all';
  //     } else if (indica == 'fat_sem') {
  //       var opc = ['Dia da Semana', ' Faturamento Médio'];
  //       var gru = 0;
  //       var graf = ['linhas', 'barras'];
  //       var tit = 'Faturamento Médio por Dia da Semana';
  //       if (empresas.length > 1) all = 'fat_sem_all';
  //     } else if (indica == 'fat_mes') {
  //       var opc = ['Mês', ' Faturamento'];
  //       var gru = 0;
  //       var graf = ['linhas', 'barras'];
  //       var tit = 'Faturamento Mensal (-14 meses)';
  //       if (empresas.length > 1) all = 'fat_mes_all';
  //     } else if (indica == 'nps_dia') {
  //       var opc = ['Dia', ' NPS'];
  //       var gru = 0;
  //       var graf = ['linhas', 'barras'];
  //       var tit = 'NPS Diário';
  //       if (empresas.length > 1) all = 'nps_dia_all';
  //     } else if (indica == 'nps_sem') {
  //       var opc = ['Dia da Semana', ' NPS'];
  //       var gru = 0;
  //       var graf = ['linhas', 'barras'];
  //       var tit = 'NPS Médio por Dia da Semana';
  //       if (empresas.length > 1) all = 'nps_sem_all';
  //     } else if (indica == 'nps_mes') {
  //       var opc = ['Mês', ' NPS'];
  //       var gru = 0;
  //       var graf = ['linhas', 'barras'];
  //       var tit = 'NPS Médio Mensal (-14 meses)';
  //       if (empresas.length > 1) all = 'nps_mes_all';
  //     } else if (indica == 'atrasos_deliv') {
  //       var opc = ['Data', ' Almoço', ' Jantar'];
  //       var gru = 0;
  //       var graf = ['linhas', 'barras'];
  //       var tit = '% de Atrasos >60min';
  //     }

  //     // cria cards individuais
  //     for (emp = 0; emp < empresas.length; emp++) {
  //       titu = nomempre[emp] + " - " + tit;
  //       empr = empresas[emp];
  //       ncard = indica + "_" + empr;
  //       criaViewCard(ncard, titu, opc, gru, graf, 'graphs', 'col-6');
  //     }
  //     // dispara refresh em cada card
  //     // for (emp = 0; emp < empresas.length; emp++) {
  //     //   empr = empresas[emp];
  //     //   ncard = indica + "_" + empr;
  //     //   card = jQuery('#' + ncard)[0];
  //     //   refresh_card_diretor(card, 'b_', empr);
  //     // }
  //     async function processarEmpresas() {
  //       for (let emp = 0; emp < empresas.length; emp++) {
  //         const empr = empresas[emp];
  //         const ncard = `${indica}_${empr}`;
  //         const card = jQuery('#' + ncard)[0];

  //         const sucesso = await refresh_card_diretor(card, 'b_', empr);

  //         if (!sucesso) {
  //           console.error(`Erro ao carregar dados da empresa ${empr}`);
  //           break;
  //         }
  //       }
  //     }


  //     // se modo "all", monta comparativo
  //     if (all != '') {
  //       opc = ['Data'];
  //       var abrev = [];
  //       abrev[1] = 'TSS';
  //       abrev[2] = 'YCB';
  //       abrev[3] = 'YAV';
  //       abrev[4] = 'DJV';
  //       abrev[5] = 'DAV';
  //       abrev[6] = 'DSF';
  //       abrev[7] = 'YSC';
  //       for (emp = 0; emp < empresas.length; emp++) {
  //         opc.push(abrev[empresas[emp]]);
  //       }
  //       titu = "Comparativo de Empresas - " + tit;
  //       ncard = all;
  //       criaViewCard(ncard, titu, opc, gru, graf, 'graphs', 'col-12');
  //       refresh_card_diretor(jQuery('#' + ncard)[0], 'l_', empresas);
  //     }
  //   }
  // }

  async function carrega_graficos_diretor() {
    jQuery('#graphs').html('');
    const indica = jQuery('#indicadores').val();

    if (!indica) return;

    // Carregar empresas selecionadas
    const empresas = [];
    const nomempre = [];
    jQuery("#empresa\\[\\] option:selected").each(function() {
      empresas.push(jQuery(this).val());
      nomempre.push(jQuery(this).text());
    });

    let opc = [];
    let gru = 0;
    let graf = ['linhas', 'barras'];
    let tit = '';
    let all = '';

    switch (indica) {
      case 'fat_dia':
        opc = ['Data', ' Faturamento'];
        tit = 'Faturamento Diário';
        if (empresas.length > 1) all = 'fat_dia_all';
        break;
      case 'fat_sem':
        opc = ['Dia da Semana', ' Faturamento Médio'];
        tit = 'Faturamento Médio por Dia da Semana';
        if (empresas.length > 1) all = 'fat_sem_all';
        break;
      case 'fat_mes':
        opc = ['Mês', ' Faturamento'];
        tit = 'Faturamento Mensal (-14 meses)';
        if (empresas.length > 1) all = 'fat_mes_all';
        break;
      case 'nps_dia':
        opc = ['Dia', ' NPS'];
        tit = 'NPS Diário';
        if (empresas.length > 1) all = 'nps_dia_all';
        break;
      case 'nps_sem':
        opc = ['Dia da Semana', ' NPS'];
        tit = 'NPS Médio por Dia da Semana';
        if (empresas.length > 1) all = 'nps_sem_all';
        break;
      case 'nps_mes':
        opc = ['Mês', ' NPS'];
        tit = 'NPS Médio Mensal (-14 meses)';
        if (empresas.length > 1) all = 'nps_mes_all';
        break;
      case 'atrasos_deliv':
        opc = ['Data', ' Almoço', ' Jantar'];
        tit = '% de Atrasos >60min';
        break;
    }

    // Criar cards individuais
    empresas.forEach((empr, idx) => {
      const ncard = `${indica}_${empr}`;
      const titulo = `${nomempre[idx]} - ${tit}`;
      criaViewCard(ncard, titulo, opc, gru, graf, 'graphs', 'col-6');
    });

    // Processar gráficos individuais sequencialmente
    for (let emp = 0; emp < empresas.length; emp++) {
      const empr = empresas[emp];
      const ncard = `${indica}_${empr}`;
      const card = jQuery(`#${ncard}`)[0];
      const sucesso = await refresh_card_diretorV2(card, 'b_', empr);
      if (!sucesso) {
        console.error(`Erro ao carregar dados da empresa ${empr}`);
        return;
      }
    }

    // Monta gráfico comparativo só depois que todos os individuais estiverem prontos
    if (all) {
      const abrev = {
        1: 'TSS',
        2: 'YCB',
        3: 'YAV',
        4: 'DJV',
        5: 'DAV',
        6: 'DSF',
        7: 'YSC',
        8: 'CP'
      };

      const opcAll = ['Data', ...empresas.map(e => abrev[e] || e)];
      const tituloAll = `Comparativo de Empresas - ${tit}`;
      criaViewCard(all, tituloAll, opcAll, gru, graf, 'graphs', 'col-12');
      await refresh_card_diretorV2(jQuery(`#${all}`)[0], 'l_', empresas);
    }
  }
</script>
<?= $this->endSection(); ?>