function montaGrafico(idGraf, valores, tipo, cores) {
  var arrResult = Object.entries(valores);
  var colunas = jQuery("#gr_" + idGraf)[0].attributes["data-cols"].value;
  var titlecol = colunas.split(",");
  var numcols = titlecol.length - 1;
  var labels = [];
  var labelsy = [];
  var dados = [];
  for (tc = 0; tc < numcols; tc++) {
    dados[tc] = [];
  }
  for (r = 0; r < arrResult.length; r++) {
    var arrColunas = Object.entries(arrResult[r][1]);
    labels[r] = arrColunas[0][1];
    for (tc = 0; tc < numcols; tc++) {
      if (typeof arrColunas[tc] != "undefined") {
        dados[tc][r] = arrColunas[tc][1];
        if (numcols > 2) {
          labelsy[tc] = arrColunas[tc][0];
        }
      }
    }
  }

  var datast = [];
  if (tipo == "l_") {
    for (tc = 0; tc < numcols; tc++) {
      datast[tc] = {
        label: labelsy[tc],
        fillColor: cores,
        borderColor: cores,
        backgroundColor: cores,
        // fill: true,
        pointradius: 10,
        lineTension: 0.5,
        data: dados[tc],
      };
    }
  } else if (tipo == "p_" || tipo == "d_") {
    for (tc = 1; tc < numcols; tc++) {
      datast[tc - 1] = {
        fillColor: cores,
        backgroundColor: cores,
        fill: true,
        radius: 250,
        pointradius: 2,
        lineTension: 0.5,
        borderWidth: 3,
        hoverOffset: 4,
        data: dados[tc],
      };
    }
  } else if (tipo == "b_") {
    for (tc = 0; tc < dados.length; tc++) {
      datast[tc] = {
        // label: titlecol[tc],
        label: labelsy[tc],
        data: dados[tc],
        backgroundColor: cores,
        borderColor: cores,
        fillColor: cores,
        borderWidth: 1,
      };
    }
  }

  var lineChartData = {
    labels: labels,
    datasets: datast,
  };

  const zoomOptions = {
    limits: {
      x: { min: 0, max: 300, minRange: 10 },
      y: { min: 0, max: 300, minRange: 10 },
    },
    pan: {
      enabled: true,
      mode: "xy",
    },
    zoom: {
      wheel: {
        enabled: true,
        modifierKey: "ctrl",
      },
      pinch: {
        enabled: true,
      },
      mode: "xy",
    },
  };

  var lineChartOptions = {
    radius: 5,
    font: {
      size: 12,
    },
    //Boolean - If we should show the scale at all
    showScale: true,
    //Boolean - Whether grid lines are shown across the chart
    scaleShowGridLines: true,
    //String - Colour of the grid lines
    scaleGridLineColor: "rgba(125,125,125,.05)",
    //Number - Width of the grid lines
    scaleGridLineWidth: 1,
    //Boolean - Whether to show horizontal lines (except X axis)
    scaleShowHorizontalLines: true,
    //Boolean - Whether to show vertical lines (except Y axis)
    scaleShowVerticalLines: true,
    //Boolean - Whether the line is curved between points
    bezierCurve: true,
    //Number - Tension of the bezier curve between points
    bezierCurveTension: 1,
    //Boolean - Whether to show a dot for each point
    pointDot: true,
    //Number - Radius of each point dot in pixels
    pointDotRadius: 1,
    //Number - Pixel width of point dot stroke
    pointDotStrokeWidth: 1,
    //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
    pointHitDetectionRadius: 2,
    //Boolean - Whether to show a stroke for datasets
    datasetStroke: true,
    //Number - Pixel width of dataset stroke
    datasetStrokeWidth: 2,
    //Boolean - Whether to fill the dataset with a color
    datasetFill: false,
    //String - A legend template
    // legendTemplate: '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<datasets.length; i++){%><li><span style="background-color:<%=datasets[i].lineColor%>"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>',
    //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
    maintainAspectRatio: true,
    //Boolean - whether to make the chart responsive to window resizing
    responsive: true,
    suggestedMax: 100,
    // Container for zoom options
    plugins: {
      legend: {
        display: false,
        labels: {
          // This more specific font property overrides the global property
          font: {
            family: "Arial",
            size: 35,
            lineHeight: 1,
          },
        },
      },
      zoom: {
        zoom: {
          wheel: {
            enabled: true,
          },
          pinch: {
            enabled: true,
          },
          mode: "xy",
        },
      },
      /** Imported from a question linked above. 
                Apparently Works for ChartJS V2 **/
      // datalabels: {
      //     anchor: 'end',
      //     align: 'top',
      //     backgroundColor: '#000',
      //     formatter: function (value, context) {
      //         if (value < 100) {
      //             return context.dataIndex + ': ' + Math.round(value * 100) + '%';
      //         } else {
      //             return context.dataIndex + ': ' + converteFloatMoeda(value);
      //         }
      //         // return GetValueFormatted(value) + '%';
      //     },
      //     borderRadius: 4,
      //     color: 'white',
      //     font: {
      //         weight: 'bold'
      //     },
      //     // formatter: Math.round,
      //     padding: 6
      // }
    },
  };

  // lineChartOptions.datasetFill = false;
  var graf = "gr_" + idGraf;
  jQuery("#gr_" + idGraf).removeClass("d-none");

  var ctx = jQuery("#gr_" + idGraf)[0].getContext("2d");
  var instancias = Object.entries(Chart.instances);
  for (i = 0; i < instancias.length; i++) {
    if (Chart.instances[instancias[i][0]].canvas.id == graf) {
      Chart.instances[instancias[i][0]].destroy();
      break;
    }
  }

  if (tipo == "p_") {
    window.myPieChart = new Chart(ctx, {
      type: "pie",
      data: lineChartData,
      options: {
        lineChartOptions,
        radius: 100,
        plugins: {
          datalabels: {
            anchor: "center",
            align: "center",
            formatter: function (value, context) {
              if (value < 100) {
                return value + "%";
              } else {
                return converteFloatMoeda(value).slice(0, -3).slice(3);
              }
            },
            borderRadius: 4,
            color: "black",
            backgroundColor: "#dedede",
            font: {
              size: 10,
            },
            padding: 5,
          },
          zoom: zoomOptions,
          tooltip: {
            usePointStyle: true,
            callbacks: {
              labelPointStyle: function (context) {
                return {
                  pointStyle: "circle",
                  rotation: 0,
                };
              },
              label: function (context) {
                let label = " " + context.label || "";

                if (label) {
                  label += ": ";
                }
                if (context.parsed !== null) {
                  if (context.parsed < 100) {
                    label += context.parsed + "%";
                  } else {
                    label += converteFloatMoeda(context.parsed)
                      .slice(0, -3)
                      .slice(3);
                    // label += parseInt(Math.round(context.parsed));
                  }
                }
                return label;
              },
            },
          },
        },
      },
      plugins: [ChartDataLabels],
    });
    Chart.defaults.font.size = 12;
  } else if (tipo == "d_") {
    window.myPieChart = new Chart(ctx, {
      type: "doughnut",
      data: lineChartData,
      options: {
        lineChartOptions,
        radius: 100,
        plugins: {
          datalabels: {
            anchor: "center",
            align: "center",
            formatter: function (value, context) {
              if (value < 100) {
                return value + "%";
              } else {
                return converteFloatMoeda(value).slice(0, -3).slice(3);
                // return parseInt(Math.round(value));
              }
            },
            borderRadius: 4,
            color: "black",
            backgroundColor: "#dedede",
            font: {
              size: 10,
            },
            padding: 5,
          },
          zoom: zoomOptions,
          tooltip: {
            usePointStyle: true,
            callbacks: {
              labelPointStyle: function (context) {
                return {
                  pointStyle: "circle",
                  rotation: 0,
                };
              },
              label: function (context) {
                let label = " " + context.label || "";

                if (label) {
                  label += ": ";
                }
                if (context.parsed !== null) {
                  if (context.parsed < 100) {
                    label += context.parsed + "%";
                  } else {
                    label += converteFloatMoeda(context.parsed)
                      .slice(0, -3)
                      .slice(3);
                    // label += parseInt(Math.round(context.parsed));
                  }
                }
                return label;
              },
            },
          },
        },
      },
      plugins: [ChartDataLabels],
    });
    Chart.defaults.font.size = 12;
  } else if (tipo == "l_") {
    let rad = numcols > 2 ? 10 : 5;
    let maxDataValue = Math.round(
      Math.max.apply(null, lineChartData.datasets[1].data)
    );
    maxDataValue += Math.round(maxDataValue / 10 + 1);
    window.myLineChart = new Chart(ctx, {
      type: "line",
      data: lineChartData,
      // plugins: [ChartDataLabels],
      options: {
        interaction: {
          mode: "index",
          axis: "y",
        },
        lineChartOptions,
        radius: rad,
        plugins: {
          // ChartDataLabels,
          legend: {
            display: numcols > 2 ? true : false,
            labels: {
              filter: function (legendItem, chartData) {
                if (legendItem.datasetIndex === 0) {
                  return false;
                }
                return true;
              },
            },
          },
          // zoom: zoomOptions,
          tooltip: {
            usePointStyle: true,
            bodySpacing: 5,
            position: "nearest",
            yAlign: "center",
            xAlign: "right",
            displayColors: numcols > 2 ? false : true,
            backgroundColor: "rgba(255, 255, 255, 1)",
            borderWidth: 1.5,
            borderColor: "rgba(0, 0, 0, 1)",
            bodyColor: "rgba(0, 0, 0, 1)",
            titleColor: "rgba(0, 0, 0, 1)",
            callbacks: {
              labelTextColor: function (context) {
                return "rgba(0, 0, 0, 1)";
              },
              labelPointStyle: function (context) {
                return {
                  pointStyle: "circle",
                  rotation: 0,
                };
              },
              label: function (context) {
                let label = context.dataset.label || "";
                if (numcols > 2) {
                  label = context.dataset.label || "";
                }
                if (label) {
                  label += ": ";
                }
                if (context.parsed.y !== null) {
                  if (context.parsed.y < 100) {
                    label +=
                      new Intl.NumberFormat("pt-BR", {
                        style: "decimal",
                      }).format(context.parsed.y) + "%";
                  } else {
                    label += converteFloatMoeda(context.parsed.y)
                      .slice(0, -3)
                      .slice(3);
                    // label += parseInt(Math.round(context.parsed.y));
                  }
                }
                return label;
              },
            },
          },
        },
        scales: {
          y: {
            max: maxDataValue,
            ticks: {
              // Include a percent sign in the ticks
              callback: function (value, index, ticks) {
                if (value < 100) {
                  return value + "%";
                } else {
                  return converteFloatMoeda(value).slice(0, -3).slice(3);
                  // return parseInt(Math.round(value));
                }
              },
            },
          },
        },
      },
    });
    Chart.defaults.font.size = 12;
  } else if (tipo == "b_") {
    datasets = lineChartData.datasets[1];
    let maxDataValue = Math.round(Math.max.apply(null, datasets.data));
    maxDataValue += Math.round(maxDataValue / 10 + 1);
    window.myLineChart = new Chart(ctx, {
      type: "bar",
      data: lineChartData,
      plugins: [ChartDataLabels],
      options: {
        interaction: {
          mode: "index",
          axis: "y",
        },
        lineChartOptions,
        plugins: {
          legend: {
            display: false,
            labels: {
              filter: function (legendItem, chartData) {
                if (legendItem.datasetIndex === 0) {
                  return false;
                }
                return true;
              },
            },
          },
          datalabels: {
            anchor: "end",
            align: "bottom",
            rotation: -90,
            formatter: function (value, context) {
              if (value < 100) {
                return value + "%";
              } else {
                return converteFloatMoeda(parseInt(value))
                  .slice(0, -3)
                  .slice(3);
                // return parseInt(Math.round(value));
              }
            },
            borderRadius: 4,
            color: "black",
            backgroundColor: "#dedede",
            font: {
              size: 10,
            },
            padding: 2,
            // margin: -5
          },
          zoom: zoomOptions,
          tooltip: {
            usePointStyle: true,
            bodySpacing: 5,
            position: "nearest",
            yAlign: "center",
            xAlign: "right",
            displayColors: numcols > 2 ? false : true,
            backgroundColor: "rgba(255, 255, 255, 1)",
            borderWidth: 1.5,
            borderColor: "rgba(0, 0, 0, 1)",
            bodyColor: "rgba(0, 0, 0, 1)",
            titleColor: "rgba(0, 0, 0, 1)",
            callbacks: {
              labelTextColor: function (context) {
                return "rgba(0, 0, 0, 1)";
              },
              labelPointStyle: function (context) {
                return {
                  pointStyle: "circle",
                  rotation: 0,
                };
              },
              label: function (context) {
                let label = context.dataset.label || "";
                if (numcols > 2) {
                  label = context.dataset.label || "";
                }
                if (label) {
                  label += ": ";
                }
                if (context.parsed.y !== null && !isNaN(context.parsed.y)) {
                  if (context.parsed.y < 100) {
                    label +=
                      new Intl.NumberFormat("pt-BR", {
                        style: "decimal",
                      }).format(context.parsed.y) + "%";
                  } else {
                    label += converteFloatMoeda(parseInt(context.parsed.y))
                      .slice(0, -3)
                      .slice(3);
                  }
                }
                return label;
              },
            },
          },
        },
        scales: {
          y: {
            min: 0,
            max: maxDataValue,
            ticks: {
              // Include a percent sign in the ticks
              callback: function (value, index, ticks) {
                if (value < 100) {
                  return value + "%";
                } else {
                  return converteFloatMoeda(value).slice(0, -3).slice(3);
                  // return parseInt(Math.round(value));
                }
              },
            },
          },
        },
      },
    });
    Chart.defaults.font.size = 12;
  }
  return;
}

function randomNumber(min, max) {
  const r = Math.random() * (max - min) + min;
  return Math.floor(r);
}

/**
 * Cria a estrutura do Card que será mostrado
 * @param {Strint} nome
 * @param {string} titulo
 * @param {array} colunas
 * @param {div} dest
 */
function criaViewCard(
  nome,
  titulo,
  colunas,
  agrupa,
  graficos,
  dest,
  ccol = "col-6"
) {
  var cols = "";
  for (c = 0; c < colunas.length; c++) {
    cols = cols + colunas[c] + ",";
  }

  var txt = "";

  txt += jQuery("#" + dest).html();
  txt +=
    '<div id="card_' +
    nome +
    '" class="' +
    ccol +
    ' float-start overflow-y-auto overflow-x-hidden">';
  txt += '<div class="card col-12 me-3 p-2 " id="b_' + nome + '">';
  txt += '<input type="hidden" id="ag_' + nome + '" value="' + agrupa + '" />';
  txt += '<input type="hidden" id="tg_' + nome + '" value="" />';
  txt +=
    '<div id="' +
    nome +
    '" class="card-header bg-info bg-gradient accordion-button p-2" type="button" data-bs-toggle="collapse" data-bs-target="#c_' +
    nome +
    '" aria-expanded="false" aria-controls="c_' +
    nome +
    '">';
  txt += '  <div class="col-11">' + titulo + "</div>";
  txt += "</div>";
  txt +=
    '<div id="c_' +
    nome +
    '" class="card-body p-0 accordion-collapse show" aria-labelledby="headingOne" data-bs-parent="#b_' +
    nome +
    '" >';
  txt +=
    '<div class="d-block float-end text-end py-0 px-1 border-info col-12">';
  txt +=
    '<i id="j_' +
    nome +
    nome +
    '" class="fa-solid fa-arrow-up-right-from-square btn px-1 text-info" onclick="graf_janela(' +
    nome +
    ')"></i>';
  for (g = 0; g < graficos.length; g++) {
    if (graficos[g] == "linhas") {
      icon = "fas fa-chart-line";
      id = "l_" + nome;
    } else if (graficos[g] == "pizza") {
      icon = "fas fa-chart-pie";
      id = "p_" + nome;
    } else if (graficos[g] == "doughnut") {
      icon = "fab fa-codiepie";
      id = "d_" + nome;
    } else if (graficos[g] == "barras") {
      icon = "fas fa-chart-simple";
      id = "b_" + nome;
    }
    txt +=
      '<i id="' + id + '" class="' + icon + ' grafic btn px-1 text-info"></i>';
  }
  // txt += '<i id="r_' + nome + '" class="fas fa-sync refresh btn px-1 text-info"></i>';
  txt +=
    '<i id="t_' +
    nome +
    '" class="fa-solid fa-table-cells btn px-1 text-info" onclick="tabe_janela(' +
    nome +
    ')"></i>';
  txt += "</div>";
  txt +=
    '<canvas class="d-none " id="gr_' +
    nome +
    '" data-cols="' +
    cols +
    '" style="min-width:98%;width:98%" width="980" ></canvas > ';
  txt += "</div>";
  txt +=
    '<div id="s_' + nome + '" class="col-12 text-primary text-center d-none" >';
  txt += '<div class="spinner-border spinner-border-sm">';
  txt += "</div>";
  txt += "Processando...";
  txt += "</div>";
  txt += "</div>";
  txt += "</div>";

  jQuery("#" + dest).html(txt);
}

/**
 * Cria a estrutura do Card que será mostrado
 * @param {Strint} nome
 * @param {string} titulo
 * @param {array} colunas
 * @param {div} dest
 */
function criaCardGraf(nome, titulo, colunas, agrupa, graficos, dest) {
  var cols = "";
  for (c = 0; c < colunas.length; c++) {
    cols = cols + colunas[c] + ",";
  }

  var txt = "";

  txt += jQuery("#" + dest).html();
  txt +=
    '<div id="' +
    nome +
    '" class="col-12 float-start overflow-y-auto overflow-x-hidden">';
  txt += '<input type="hidden" id="ag_' + nome + '" value="' + agrupa + '" />';
  txt += '<input type="hidden" id="tg_' + nome + '" value="" />';
  txt +=
    '<canvas id="gr_' +
    nome +
    '" data-cols="' +
    cols +
    '" style="max-height:85vh;min-width:98%;width:98%" width="980" ></canvas > ';
  txt += "</div>";

  jQuery("#" + dest).html(txt);
}

function graf_janela(obj) {
  var nome = obj.id;
  var colunas = jQuery("#gr_" + nome)[0].attributes["data-cols"].value;
  var tipo = jQuery("#tg_" + nome).val();
  var titulo = jQuery("#" + nome)[0].children[0].innerHTML;
  var periodo = jQuery("#periodo").val();
  var startDate = periodo.substr(0, 10); //jQuery("#periodo").data('daterangepicker').startDate.format('DD/MM/YYYY');
  var endDate = periodo.substr(-10); //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');
  if (endDate == "Considerar") {
    startDate = "01/01/2001";
    endDate = "31/12/2100";
  }
  if (startDate != "") {
    //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');
    var empresa = jQuery("#empresa").val();
    if (empresa == undefined) {
      var pos = nome.lastIndexOf("_");
      empresa = nome.substr(pos + 1);
      if (empresa == "all") {
        empresa = [];
        jQuery.each(jQuery("#empresa\\[\\] option:selected"), function () {
          empresa.push(jQuery(this).val());
        });
      }
    }
    jQuery.post("/DashGerente/secao", {
      colunas: colunas,
      nome: nome,
      tipo: tipo,
      inicio: startDate,
      fim: endDate,
      empresa: empresa,
      busca: nome,
      titulo: titulo,
    });
    var redir = "graph/index";
    redirec_blank(redir);
  }
}

function tabe_janela(obj) {
  var nome = obj.id;
  var colunas = jQuery("#gr_" + nome)[0].attributes["data-cols"].value;
  var tipo = jQuery("#tg_" + nome).val();
  var titulo = jQuery("#" + nome)[0].children[0].innerHTML;
  var periodo = jQuery("#periodo").val();
  var startDate = periodo.substr(0, 10); //jQuery("#periodo").data('daterangepicker').startDate.format('DD/MM/YYYY');
  var endDate = periodo.substr(-10); //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');
  if (endDate == "Considerar") {
    startDate = "01/01/2001";
    endDate = "31/12/2100";
  }
  if (startDate != "") {
    //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');
    var empresa = jQuery("#empresa").val();
    if (empresa == undefined) {
      var pos = nome.lastIndexOf("_");
      empresa = nome.substr(pos + 1);
    }

    jQuery.post("/DashGerente/secao", {
      colunas: colunas,
      nome: nome,
      tipo: tipo,
      inicio: startDate,
      fim: endDate,
      empresa: empresa,
      busca: nome,
      titulo: titulo,
    });
    var redir = "table/montaTable";
    redirec_blank(redir);
  }
}

function refresh_card(obj, grafico) {
  // bloqueiaTela();
  var busca = obj.id;
  if (!jQuery("#gr_" + busca).hasClass("d-none")) {
    jQuery("#gr_" + busca).addClass("d-none");
  }
  var periodo = jQuery("#periodo").val();
  var startDate = periodo.substr(0, 10); //jQuery("#periodo").data('daterangepicker').startDate.format('DD/MM/YYYY');
  var endDate = periodo.substr(-10); //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');
  if (endDate == "Considerar") {
    startDate = "01/01/2001";
    endDate = "31/12/2100";
  }
  if (startDate != "") {
    //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');

    var empresa = jQuery("#empresa").val();

    url = "/DashGerente/busca_dados";
    jQuery.ajax({
      type: "POST",
      url: url,
      async: true,
      dataType: "json",
      data: {
        busca: busca,
        periodo: periodo,
        inicio: startDate,
        fim: endDate,
        empresa: empresa,
        tipo: grafico,
      },
      beforeSend: function () {
        jQuery("#s_" + busca).toggleClass("d-none");
        removeLinhas("tb_" + busca);
      },

      success: async function (retorno) {
        if (retorno.registros > 0) {
          jQuery("#card_" + busca).removeClass("d-none");
          cores = retorno.cores;
          // delete retorno.cores;
          // adicionaLinhas(busca, retorno.dados);
          if (grafico) {
            jQuery("#tg_" + busca).val(grafico);
            if (busca == "nps_notas") {
              jQuery("#nps_notas")[0].children[0].innerHTML =
                "NPS - Notas Recebidas - Nota Média ";
            }
            if (busca == "nps_clientes") {
              jQuery("#nps_clientes")[0].children[0].innerHTML =
                "NPS - " + retorno.nps + " - Respostas: " + retorno.respostas;
            }
            if (busca == "nps_dia") {
              jQuery("#nps_dia")[0].children[0].innerHTML =
                "NPS médio por dia - Respostas: " + retorno.respostas;
            }
            montaGrafico(busca, retorno.dados, grafico, cores);
          }
        } else {
          if (!jQuery("#card_" + busca).hasClass("d-none")) {
            jQuery("#card_" + busca).addClass("d-none");
          }
        }
      },

      complete: function () {
        jQuery("#s_" + busca).toggleClass("d-none");
        // desBloqueiaTela();
      },
    });
  }
}

function refresh_card_diretor(obj, grafico, empresa) {
  // bloqueiaTela();
  var busca = obj.id;
  if (!jQuery("#gr_" + busca).hasClass("d-none")) {
    jQuery("#gr_" + busca).addClass("d-none");
  }
  var periodo = jQuery("#periodo").val();
  var startDate = periodo.substr(0, 10); //jQuery("#periodo").data('daterangepicker').startDate.format('DD/MM/YYYY');
  var endDate = periodo.substr(-10); //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');
  if (endDate == "Considerar") {
    startDate = "01/01/2001";
    endDate = "31/12/2100";
  }
  //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');
  if (startDate != "") {
    url = "/DashDiretor/busca_dados";
    jQuery.ajax({
      type: "POST",
      url: url,
      async: true,
      dataType: "json",
      data: {
        busca: busca,
        periodo: periodo,
        inicio: startDate,
        fim: endDate,
        empresa: empresa,
        tipo: grafico,
      },
      beforeSend: function () {
        jQuery("#s_" + busca).toggleClass("d-none");
        removeLinhas("tb_" + busca);
      },

      success: async function (retorno) {
        if (retorno.registros > 0) {
          jQuery("#card_" + busca).removeClass("d-none");
          cores = retorno.cores;
          if (grafico) {
            jQuery("#tg_" + busca).val(grafico);
            if (busca.substr(0, 7) == "nps_not") {
              jQuery("#" + busca)[0].children[0].innerHTML =
                jQuery("#" + busca)[0].children[0].innerHTML +
                "NPS - Notas Recebidas - Nota Média ";
            }
            if (busca.substr(0, 7) == "nps_cli") {
              jQuery("#" + busca)[0].children[0].innerHTML =
                jQuery("#" + busca)[0].children[0].innerHTML +
                " - NPS - " +
                retorno.nps +
                " - Respostas: " +
                retorno.respostas;
            }
            if (busca.substr(0, 7) == "nps_dia") {
              jQuery("#" + busca)[0].children[0].innerHTML =
                jQuery("#" + busca)[0].children[0].innerHTML +
                " - Respostas: " +
                retorno.respostas;
            }
            if (busca.substr(0, 7) == "nps_mes") {
              jQuery("#" + busca)[0].children[0].innerHTML =
                jQuery("#" + busca)[0].children[0].innerHTML +
                " - Respostas: " +
                retorno.respostas;
            }
            if (busca.substr(0, 7) == "nps_sem") {
              jQuery("#" + busca)[0].children[0].innerHTML =
                jQuery("#" + busca)[0].children[0].innerHTML +
                " - Respostas: " +
                retorno.respostas;
            }
            montaGrafico(busca, retorno.dados, grafico, cores);
          }
        } else {
          if (!jQuery("#card_" + busca).hasClass("d-none")) {
            jQuery("#card_" + busca).addClass("d-none");
          }
        }
      },

      complete: function () {
        jQuery("#s_" + busca).toggleClass("d-none");
      },
    });
  }
}

// funcao todas as linhas, deixa só o cabeçalho da tabela
function removeLinhas(idTabela, indice = 1) {
  if (indice != 1) {
    jQuery("#" + idTabela + " tbody")
      .children()
      .remove();
  } else {
    jQuery("#" + idTabela)
      .find("tr:gt(" + indice + ")")
      .remove();
  }
}

function adicionaLinhas(idCard, resultado) {
  var tabela = jQuery("#tb_" + idCard)[0];
  var head = tabela.rows.length - 1;
  var numColuns = tabela.rows[head].cells.length;
  if (resultado.length < 1) {
    linha =
      "<tr><td colspan=" + numColuns + ">Sem Registros no Período</td></tr>";
    jQuery("#tb_" + idCard + " tbody").append(linha);
  } else {
    var arrResult = Object.entries(resultado);
    var agrupa = jQuery("#ag_" + idCard).val();
    if (agrupa == 0) {
      // sem agrupamento
      addSemAgrupar("tb_" + idCard, arrResult);
    } else if (agrupa == 1) {
      // 1 agrupamento
      addGrupo("tb_" + idCard, arrResult);
    } else if (agrupa == 2) {
      // 2 agrupamentos
      addGruSubgrupo("tb_" + idCard, arrResult);
    }
  }
}

/**
 * Monta a Tabela sem agrupamento
 * @param {String} idTabela
 * @param {Object} arrResult
 */
function addSemAgrupar(idTabela, arrResult) {
  var tabela = jQuery("#" + idTabela)[0];
  var head = tabela.rows.length - 1;
  var numColuns = tabela.rows[0].cells.length;
  var total = [];
  for (c = 1; c < numColuns; c++) {
    total[c] = 0;
  }
  for (r = 0; r < arrResult.length; r++) {
    var linha = "<tr>";
    var arrCols = Object.entries(arrResult[r][1]);
    for (c = 0; c < numColuns; c++) {
      classe = "text-start";
      dtsort = "";
      ehnumero = jQuery.isNumeric(arrCols[c][1]);
      ehdata = moment(arrCols[c][1].toString().substring(0, 10), "DD/MM/YYYY");
      if (ehnumero) {
        classe = "text-end pe-5";
        if (
          parseFloat(arrCols[c][1]) > 100 &&
          !Number.isInteger(parseFloat(arrCols[c][1]))
        ) {
          arrCols[c][1] = converteFloatMoeda(parseFloat(arrCols[c][1]));
        }
      } else if (ehdata._isValid) {
        classe = "text-center";
        datafmt = arrCols[c][1].substring(0, 10);
        fim = "";
        if (arrCols[c][1].length > 10) {
          fim = arrCols[c][1].substring(10, arrCols[c][1].length);
        }
        dtsort =
          "data-sort = '" +
          moment(datafmt, "DD/MM/YYYY").format("YYYY-MM-DD") +
          fim +
          "'";
      }
      linha =
        linha +
        "<td " +
        dtsort +
        " class='" +
        classe +
        "'>" +
        arrCols[c][1] +
        "</td>";
      if (c > 0) {
        total[c] = total[c] + parseInt(Math.round(arrCols[c][1]));
      }
    }
    jQuery("#" + idTabela + " tbody").append(linha);
  }
  // linha = "<tr><td><h5><b>Total</b></h5></td>";
  // for (c = 1; c < numColuns; c++) {
  // linha = linha + "<td class='text-end'><h5><b>" + total[c] + "</b></h5></td>";
  // }
  // linha = linha + "</tr>";
  // jQuery("#" + idTabela + " tbody").append(linha);
}

/**
 * Monta a Tabela com 2 Colunas e 1 agrupamento
 * Totaliza por agrupamento e por total
 * @param {String} idTabela
 * @param {Object} arrResult
 */
function addGrupo(idTabela, arrResult) {
  var tabela = jQuery("#" + idTabela)[0];
  var numColuns = tabela.rows[1].cells.length;
  var total = [];
  var subtotal = [];
  for (c = 1; c < numColuns; c++) {
    total[c] = 0;
    subtotal[c] = 0;
  }
  $grupo = "";
  $cc = 0;
  for (r = 0; r < arrResult.length; r++) {
    var linha = "<tr>";
    var arrCols = Object.entries(arrResult[r][1]);
    if ($grupo != arrCols[0][1]) {
      if ($grupo != "") {
        linha = linha + "<td><b>Total " + $grupo + "</b></td>";
        for (c = 1; c < numColuns; c++) {
          linha =
            linha + "<td class='text-end'><h5>" + subtotal[c] + "</h5></td>";
        }
        linha = linha + "</tr>";
        // linha = linha+"<td class='text-end'><b>"+$subtotal+"</b></td></tr><tr>";
      }
      linha =
        linha +
        "<td colspan='" +
        numColuns +
        "'><h4>" +
        arrCols[0][1] +
        "</h4></td></tr><tr>";
      $cc = 1;
      for (c = 1; c < numColuns; c++) {
        subtotal[c] = 0;
      }
      $grupo = arrCols[0][1];
    }
    for (c = 0; c < numColuns; c++) {
      classe = "text-start";
      if (moment.isDate(arrCols[c + $cc][1])) {
        classe = "text-center";
      } else if (jQuery.isNumeric(arrCols[c + $cc][1])) {
        classe = "text-end";
      }
      linha =
        linha + "<td class='" + classe + "'>" + arrCols[c + $cc][1] + "</td>";
      // linha = linha+"<td>"+arrCols[c + $cc][1]+"</td>";
      if (c > 0) {
        if ($grupo != "") {
          subtotal[c] = subtotal[c] + parseInt(Math.round(arrCols[c + $cc][1]));
        }
        total[c] = total[c] + parseInt(Math.round(arrCols[c + $cc][1]));
      }
    }
    jQuery("#" + idTabela + " tbody").append(linha);
  }
  linha = "<tr><td><b>Total " + $grupo + "</b></td>";
  for (c = 1; c < numColuns; c++) {
    linha = linha + "<td class='text-end'><h5>" + subtotal[c] + "</h5></td>";
  }
  linha = linha + "</tr>";
  jQuery("#" + idTabela + " tbody").append(linha);

  linha = "<tr><td><h5><b>Total</b></h5></td>";
  for (c = 1; c < numColuns; c++) {
    linha =
      linha + "<td class='text-end'><h5><b>" + total[c] + "</b></h5></td>";
  }
  linha = linha + "</tr>";
  jQuery("#" + idTabela + " tbody").append(linha);
}

/**
 * Monta a Tabela com 2 Colunas e 2 agrupamentos
 * Totaliza por agrupamento e por total
 * @param {String} idTabela
 * @param {Object} arrResult
 */
function addGruSubgrupo(idTabela, arrResult) {
  var tabela = jQuery("#" + idTabela)[0];
  var numColuns = tabela.rows[1].cells.length;
  $total = 0;
  $subtotal = 0;
  var total = [];
  var subtotal = [];
  var subsubtotal = [];
  for (c = 1; c < numColuns; c++) {
    total[c] = 0;
    subtotal[c] = 0;
    subsubtotal[c] = 0;
  }
  $grupo = ""; // =[0][1]
  $subgrupo = ""; // =[1][1]
  $cc = 0;
  for (r = 0; r < arrResult.length; r++) {
    var linha = "<tr>";
    var arrCols = Object.entries(arrResult[r][1]);
    if ($subgrupo != arrCols[1][1] || $grupo != arrCols[0][1]) {
      if ($subgrupo != "") {
        linha = linha + "<td><b>Total " + $subgrupo + "</b></td>";
        for (c = 1; c < numColuns; c++) {
          linha =
            linha + "<td class='text-end'><b>" + subsubtotal[c] + "</b></td>";
        }
        linha = linha + "</tr>";
      }
      if ($grupo != arrCols[0][1]) {
        if ($grupo != "") {
          linha = linha + "<td><b>Total " + $grupo + "</b></td>";
          for (c = 1; c < numColuns; c++) {
            linha =
              linha + "<td class='text-end'><b>" + subtotal[c] + "</b></td>";
          }
          linha = linha + "</tr>";
        }
        linha =
          linha +
          "<td colspan='" +
          numColuns +
          "'><h4 class='bg-success-opacity text-start' >" +
          arrCols[0][1] +
          "</h4></td></tr><tr>";
        $cc = 2;
        for (c = 1; c < numColuns; c++) {
          subtotal[c] = 0;
        }
        $grupo = arrCols[0][1];
      }
      linha =
        linha +
        "<td colspan='" +
        numColuns +
        "'><h4>" +
        arrCols[1][1] +
        "</h4></td></tr><tr>";
      $cc = 2;
      for (c = 1; c < numColuns; c++) {
        subsubtotal[c] = 0;
      }
      $subgrupo = arrCols[1][1];
    }
    for (c = 0; c < numColuns; c++) {
      classe = "text-start";
      if (moment.isDate(arrCols[c + $cc][1])) {
        classe = "text-center";
      } else if (jQuery.isNumeric(arrCols[c + $cc][1])) {
        classe = "text-end";
      }
      linha =
        linha + "<td class='" + classe + "'>" + arrCols[c + $cc][1] + "</td>";
      // linha = linha+"<td>"+arrCols[c + $cc][1]+"</td>";
      if (c > 0) {
        if ($grupo != "") {
          subtotal[c] = subtotal[c] + parseInt(Math.round(arrCols[c + $cc][1]));
        }
        if ($subgrupo != "") {
          subsubtotal[c] =
            subsubtotal[c] + parseInt(Math.round(arrCols[c + $cc][1]));
        }
        total[c] = total[c] + parseInt(Math.round(arrCols[c + $cc][1]));
      }
    }
    jQuery("#" + idTabela + " tbody").append(linha);
  }
  linha = "<tr><td><b>Total " + $subgrupo + "</b></td>";
  for (c = 1; c < numColuns; c++) {
    linha = linha + "<td class='text-end'><b>" + subsubtotal[c] + "</b></td>";
  }
  linha = linha + "</tr>";
  jQuery("#" + idTabela + " tbody").append(linha);

  linha = "<tr><td><b>Total " + $grupo + "</b></td>";
  for (c = 1; c < numColuns; c++) {
    linha = linha + "<td class='text-end'><b>" + subtotal[c] + "</b></td>";
  }
  linha = linha + "</tr>";
  jQuery("#" + idTabela + " tbody").append(linha);

  linha = "<tr><td><h5><b>Total </b></h5></td>";
  for (c = 1; c < numColuns; c++) {
    linha =
      linha + "<td class='text-end'><h5><b>" + total[c] + "</b></h5></td>";
  }
  linha = linha + "</tr>";
  jQuery("#" + idTabela + " tbody").append(linha);
}

function montaTabela(nome, valores) {
  adicionaLinhas(nome, valores);
}

function abrirCardEmNovaGuia(triggerEl) {
  // 1) encontra .card e seu canvas original
  const card = triggerEl.closest(".card");
  const canvas = card?.querySelector("canvas");
  const header = card?.querySelector(".card-header .col-11");
  if (!card || !canvas) {
    console.error("Card ou canvas não encontrado.");
    return;
  }

  // 2) extrai os dados armazenados no canvas original
  const valores = JSON.parse(canvas.dataset.chartValores);
  const cores = JSON.parse(canvas.dataset.chartCores);
  const tipo = canvas.dataset.chartTipo;
  const cols = canvas.dataset.chartCols;
  const titulo = header ? header.innerText.trim() : "";

  // 3) coleta folhas de estilo e scripts da página atual
  const cssLinks = Array.from(
    document.querySelectorAll('link[rel="stylesheet"]')
  )
    .map((l) => `<link rel="stylesheet" href="${l.href}">`)
    .join("\n");
  const jsScripts = Array.from(document.querySelectorAll("script[src]"))
    .map((s) => `<script src="${s.src}"></script>`)
    .join("\n");

  // 4) monta o HTML da nova guia com flexbox e canvas 1700x900
  const html = `
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>${titulo}</title>
  <style>
    html, body {
      margin:0; padding:0;
      height:100vh; width:100vw;
      display:flex; flex-direction:column;
    }
    #header {
      background:#00bcd4; color:#fff;
      padding:8px 12px; flex:0 0 auto;
      font-size:1.1em;
    }
    #chart-wrap {
      flex:1 1 auto;
      display:flex; align-items:center; justify-content:center;
      background:#fff;
      overflow:hidden;
      height: calc(100vh - 50px);
    }
    #gr_full {
    }
  </style>
  ${cssLinks}
</head>
<body>
  <div id="header">${titulo}</div>
  <div id="chart-wrap">
    <canvas id="gr_full"
            data-cols="${cols}"
            style="display: block;position: inherit;height: 100% !important;width: 100% !important;"></canvas>
  </div>

  ${jsScripts}

  <script>
    jQuery(function(){
      // recria o gráfico no canvas de 1700x900
      montaGrafico('full',
                   ${JSON.stringify(valores)},
                   '${tipo}',
                   ${JSON.stringify(cores)});
    });
  </script>
</body>
</html>`;

  // 5) abre nova guia e injeta o HTML
  const win = window.open("", "_blank");
  if (!win) {
    alert("Por favor, permita pop-ups ou abas para este site.");
    return;
  }
  win.document.open();
  win.document.write(html);
  win.document.close();
}
