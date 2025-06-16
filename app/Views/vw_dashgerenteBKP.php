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
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script type="text/javascript">
  google.charts.load('current',{
    'packages' : [
      'corechart', 
      'bar',
      'table'
    ],
    language: 'pt-BR',

  });
  var classicOptionsdeliv = {
      // subtitle: 'Últimos 30 dias',
      width: 1300,
      height: 400,
      bar: {groupWidth:'80%'},
      chartArea: {
          height: '75%',
          width: '100%',
          left: 30,
          top: 10,
      },
      legend: {
          position: 'bottom',
          textStyle: {
              fontName: 'Whitney HTF Light',
              fontSize: 14,
              color: 'black',
          },
      },
      vAxis: {
          textStyle: {
              fontSize: 12,
              fontName: 'Whitney HTF Light',
          },
      },                                
      hAxis: {
          slantedText: true,
          slantedTextAngle: 45,
          textStyle: {
            fontSize: 12,
            fontName: 'Whitney HTF Light',
          },
      },
      series: {
        0: {targetAxisIndex: '% Almoço'},
        1: {targetAxisIndex: '% Jantar'}
      },
      vAxes: {
        // Adds titles to each axis.
        0: {title: 'Porcentagem'},
      }
    };      
    var classicOptionsfatur = {
      // subtitle: 'Últimos 30 dias',
      width: 1300,
      height: 400,
      bar: {groupWidth:'80%'},
      chartArea: {
          height: '75%',
          width: '100%',
          left: 30,
          top: 10,
      },
      legend: {
          position: 'bottom',
          textStyle: {
              fontName: 'Whitney HTF Light',
              fontSize: 14,
              color: 'black',
          },
      },
      vAxis: {
          textStyle: {
              fontSize: 12,
              fontName: 'Whitney HTF Light',
          },
      },                                
      hAxis: {
          slantedText: true,
          slantedTextAngle: 45,
          textStyle: {
            fontSize: 12,
            fontName: 'Whitney HTF Light',
          },
      },
      series: {
        0: {targetAxisIndex: '% Faturamento do Período'},
        1: {targetAxisIndex: '% Taxa de Serviço'}
      },
      vAxes: {
        // Adds titles to each axis.
        0: {title: 'Porcentagem do Total'},
      }
    };      
    var classicOptionsTable = {
      width: 500,
      height: 300,
    }


</script>

<div id='content' class='container page-content bg-light m-0'>
    <div class='col-lg-12 col-md-12 d-lg-flex d-none'>
    <?
      if (sizeof($secoes)) {
        echo "<ul class='nav nav-tabs border-0 d-none d-lg-flex d-md-flex' id='myTab' role='tablist'>";
        $active = 'active';
        for ($s = 0; $s < sizeof($secoes); $s++) {
          echo "<li class='nav-item ' role='presentation'>";
          $secao = url_amigavel($secoes[$s]);
          echo "<span id='" . $secao . "-valid' 
              class='float-end valid-tab badge rounded-pill bg-danger d-none'>!</span>";
          echo "<button class='nav-link $active' id='" . $secao . "-tab' 
                  data-bs-toggle='tab' data-bs-target='#" . $secao . "' 
                  type='button' role='tab' aria-controls='" . $secao . "' 
                  aria-selected='false'>";
          echo "<i class='far fa-hand-point-right'></i>";
          echo "&nbsp;-&nbsp;" . $secoes[$s];
          echo "</button>";
          echo "</li>";
          $active = '';
        }
        echo "</ul>";
        echo "</div>";
        $active = 'show active';
        echo "<div class='tab-content bg-white' id='myTabContent'>";
          for ($s = 0; $s < count($secoes); $s++) {
            $secao = url_amigavel($secoes[$s]);
            echo "<div class='tab-pane fade p-lg-3 p-2 $active' id='" . $secao . "' role='tabpanel' 
                    aria-labelledby='" . $secao . "-tab' tabindex='0'>";
                  if($secao =='faturamento'){
                    for ($e = 0; $e < count($empresa); $e++) {
                      $emp = $empresa[$e];
                      if(count($emp['fatur']) > 0) {
                        echo "<h3>".$empresa[$e]['emp_apelido']."</h3>";
                        echo "<h5>Faturamento - Últimos 30 dias</h5>";

                        echo "<div id='chart_fatur_$e' style='width: 100%;'></div>";
                        echo "<div id='chart_tbfat_$e' style='width: 50%;margin:0 auto'></div>";
                        echo "<hr class='mb-5' style='border:3px solid black'>";
                      ?>
                        <script>
                          google.charts.setOnLoadCallback(drawchart);
                          
                          function drawchart()
                          {
                            var chartDiv = document.getElementById("chart_fatur_<?=$e?>");
                            var chartTab = document.getElementById("chart_tbfat_<?=$e?>");

                            <?php
                            echo "var dados_array = ". json_encode($emp['fatur']) . ";\n"
                            ?>
                            var data = new google.visualization.DataTable();

                            // Declare columns
                            data.addColumn('string', 'Dia');
                            data.addColumn('number', '% Faturamento do Período');
                            data.addColumn('number', '% Taxa de Serviço do Período');

                            for(let i = 0; i < dados_array.length; i++) {
                              data.addRows([
                                  [dados_array[i]['DataMovimento'],dados_array[i]['FatTotal'],dados_array[i]['TotTaxaServico']],
                              ]);
                            }

                              function drawClassicChart() {
                                var classicChart = new google.visualization.ColumnChart(chartDiv);
                                classicChart.draw(data,classicOptionsfatur);

                                var classicTable = new google.visualization.Table(chartTab);
                                classicTable.draw(data, classicOptionsTable);
                              }
                              drawClassicChart();
                          }

                        </script>
                      <?
                      } 
                    }
                  }
                  if($secao =='delivery'){
                    for ($e = 0; $e < count($empresa); $e++) {
                      $emp = $empresa[$e];
                      if(count($emp['deliv']) > 0) {
                        echo "<h3>".$empresa[$e]['emp_apelido']."</h3>";
                        echo "<h5>Porcentagem de Entregas com mais de 60 minutos - Últimos 30 dias</h5>";

                        echo "<div id='chart_deliv_$e' style='width: 100%;'></div>";
                        echo "<div id='chart_table_$e' style='width: 50%;margin:0 auto'></div>";
                        echo "<hr class='mb-5' style='border:3px solid black'>";
                      ?>
                        <script>
                          google.charts.setOnLoadCallback(drawchart);
                          
                          function drawchart()
                          {
                            var chartDiv = document.getElementById("chart_deliv_<?=$e?>");
                            var chartTab = document.getElementById("chart_table_<?=$e?>");

                            <?php
                            echo "var dados_array = ". json_encode($emp['deliv']) . ";\n"
                            ?>
                            var data = new google.visualization.DataTable();

                            // Declare columns
                            data.addColumn('string', 'Dia');
                            data.addColumn('number', '% Almoço');
                            data.addColumn('number', '% Jantar');

                            for(let i = 0; i < dados_array.length; i++) {
                              data.addRows([
                                  [dados_array[i]['Data'],dados_array[i]['Almoco'],dados_array[i]['Jantar']],
                              ]);
                            }

                              function drawClassicChart() {
                                var classicChart = new google.visualization.ColumnChart(chartDiv);
                                classicChart.draw(data, classicOptionsdeliv);

                                var classicTable = new google.visualization.Table(chartTab);
                                classicTable.draw(data, classicOptionsTable);
                              }
                              drawClassicChart();
                          }

                        </script>
                      <?
                      } 
                    }
                  }
            echo "</div>";
            $active = '';
          }
        echo "</div>";
      }
    ?>
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
<?=$this->endSection();?>
