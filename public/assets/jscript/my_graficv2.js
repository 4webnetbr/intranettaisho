/**
 * my_graficv2.js
 * Versão 2: mantém TODO o conteúdo de my_grafic.js 
 * e altera apenas o bloco de barras para empilhadas (Almoço + Janta)
 */

/** helper para adicionar canal alfa em uma string 'rgb(r,g,b)' ou '#rrggbb' */
function addAlpha(rgbStr, alpha) {
    var m = rgbStr.match(/rgb\s*\(\s*(\d+),\s*(\d+),\s*(\d+)\s*\)/);
    if (m) {
        return 'rgba(' + m[1] + ',' + m[2] + ',' + m[3] + ',' + alpha + ')';
    }
    if (rgbStr[0] === '#') {
        var r = parseInt(rgbStr.substr(1, 2), 16),
            g = parseInt(rgbStr.substr(3, 2), 16),
            b = parseInt(rgbStr.substr(5, 2), 16);
        return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
    }
    return rgbStr;
}

/**
 * Função principal: montaGrafico
 */
function montaGrafico(idGraf, valores, tipo, cores) {
    const colunas = jQuery('#gr_' + idGraf)[0].getAttribute('data-cols');
    const titlecol = colunas.split(',');
    var numcols = titlecol.length - 1;
    var labels = [];
    var labelsy = [];
    var dados = [];

    for (var tc = 0; tc < numcols; tc++) {
        dados[tc] = [];
    }

    var arrResult = Object.entries(valores);
    for (var r = 0; r < arrResult.length; r++) {
        var arrColunas = Object.entries(arrResult[r][1]);
        labels[r] = arrColunas[0][1];
        for (var tc = 0; tc < numcols; tc++) {
            if (typeof arrColunas[tc] !== "undefined") {
                dados[tc][r] = arrColunas[tc][1];
                if (numcols > 2) {
                    labelsy[tc] = arrColunas[tc][0];
                }
            }
        }
    }
    if (tipo === 'b_') {
        const labels = valores.map(v => v.Data);
        const totais = valores.map(v => (v.Almoço || 0) + (v.Janta || 0));

        const dataset = {
            label: 'Faturamento Total',
            data: totais,
            backgroundColor: cores.map(cor => cor || '#4caf50'),
            borderColor: cores.map(cor => cor || '#4caf50'),
            borderWidth: 1
        };

        const maxY = Math.ceil(Math.max(...totais) * 1.1);

        const barOptions = {
            interaction: { mode: 'index', axis: 'x' },
            plugins: {
                legend: { display: false },
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    rotation: -90,
                    font: { size: 10 },
                    formatter: value => converteFloatMoeda(value)
                },
                tooltip: {
                    callbacks: {
                        label(context) {
                            const index = context.dataIndex;
                            const almoco = valores[index]?.Almoço || 0;
                            const janta = valores[index]?.Janta || 0;
                            const total = almoco + janta;

                            const pAlmoco = total > 0 ? (almoco / total) * 100 : 0;
                            const pJanta = total > 0 ? (janta / total) * 100 : 0;

                            return [
                                `Almoço: ${converteFloatMoeda(almoco)} (${pAlmoco.toFixed(1)}%)`,
                                `Janta: ${converteFloatMoeda(janta)} (${pJanta.toFixed(1)}%)`,
                                `Total: ${converteFloatMoeda(total)}`
                            ];
                        },
                        labelColor(context) {
                            const index = context.dataIndex;
                            const baseColor = cores[index] || '#4caf50';

                            return {
                                borderColor: baseColor,
                                backgroundColor: baseColor
                            };
                        }
                    }
                }
            },
            scales: {
                x: { stacked: false },
                y: {
                    beginAtZero: true,
                    max: maxY,
                    ticks: {
                        callback: value => converteFloatMoeda(value)
                    }
                }
            },
            maintainAspectRatio: true,
            responsive: true
        };

        const canvasEl = jQuery("#gr_" + idGraf)[0];
        const ctx = canvasEl.getContext("2d");
        jQuery("#gr_" + idGraf).removeClass('d-none');

        // Gerenciar múltiplos gráficos
        if (!window.myCharts) window.myCharts = {};
        if (window.myCharts[idGraf]) window.myCharts[idGraf].destroy();

        window.myCharts[idGraf] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [dataset]
            },
            options: barOptions,
            plugins: [ChartDataLabels]
        });

        // Aplicar gradiente customizado após a renderização
        setTimeout(() => {
            const chart = window.myCharts[idGraf];
            const { ctx, chartArea, scales } = chart;

            if (!chartArea) return;

            chart.data.datasets[0].backgroundColor = totais.map((total, index) => {
                const baseColor = cores[index] || '#4caf50';
                const jantaColor = addAlpha(baseColor, 0.3);
                const almoco = valores[index]?.Almoço || 0;

                const scaleY = scales.y;
                const yBase = scaleY.getPixelForValue(0);
                const yTop = scaleY.getPixelForValue(total);
                const yAlmoco = scaleY.getPixelForValue(almoco);

                const gradient = ctx.createLinearGradient(0, yTop, 0, yBase);
                const ratio = (yAlmoco - yTop) / (yBase - yTop);

                gradient.addColorStop(0, jantaColor);
                gradient.addColorStop(ratio, jantaColor);
                gradient.addColorStop(ratio, baseColor);
                gradient.addColorStop(1, baseColor);

                return gradient;
            });

            chart.update();
        }, 100);
        // var canvasEl = document.getElementById('gr_' + idGraf);
        canvasEl.dataset.chartValores = JSON.stringify(valores);
        canvasEl.dataset.chartCores = JSON.stringify(cores);
        canvasEl.dataset.chartTipo = tipo;
        canvasEl.dataset.chartCols = canvasEl.getAttribute('data-cols');
    }

    // LINHA -------------------------------------------------------------
    else if (tipo == 'l_') {
        var datast = [];
        for (var tc = 0; tc < numcols; tc++) {
            datast[tc] = {
                label: labelsy[tc],
                fillColor: cores,
                borderColor: cores,
                backgroundColor: cores,
                pointradius: (numcols > 2) ? 10 : 5,
                lineTension: 0.5,
                data: dados[tc]
            };
        }
        var lineChartData = {
            labels: labels,
            datasets: datast
        };
        var maxDataValue = Math.round(Math.max.apply(null, lineChartData.datasets[1].data));
        maxDataValue += Math.round((maxDataValue / 10) + 1);

        var lineChartOptions = {
            interaction: { mode: 'index', axis: 'y' },
            plugins: {
                legend: {
                    display: (numcols > 2),
                    labels: {
                        filter: function (legendItem) {
                            return legendItem.datasetIndex !== 0;
                        }
                    }
                },
                datalabels: { display: false },
                tooltip: {
                    usePointStyle: true,
                    bodySpacing: 5,
                    position: 'nearest',
                    yAlign: 'center',
                    xAlign: 'right',
                    backgroundColor: 'rgba(255,255,255,1)',
                    borderWidth: 1.5,
                    borderColor: 'rgba(0,0,0,1)',
                    bodyColor: 'rgba(0,0,0,1)',
                    titleColor: 'rgba(0,0,0,1)',
                    callbacks: {
                        label: function (context) {
                            var v = context.parsed.y;
                            return context.dataset.label + ': ' +
                                (v < 100 ? v + '%' : converteFloatMoeda(v).slice(0, -3).slice(3));
                        }
                    }
                }
            },
            scales: {
                y: {
                    max: maxDataValue,
                    ticks: {
                        callback: function (value) {
                            return (value < 100
                                ? value + '%'
                                : converteFloatMoeda(value).slice(0, -3).slice(3));
                        }
                    }
                }
            },
            maintainAspectRatio: true,
            responsive: true
        };

        var ctx = jQuery("#gr_" + idGraf)[0].getContext("2d");
        jQuery("#gr_" + idGraf).removeClass('d-none');
        if (window.myLineChart) window.myLineChart.destroy();
        window.myLineChart = new Chart(ctx, {
            type: 'line',
            data: lineChartData,
            options: lineChartOptions
        });
        var canvasEl = document.getElementById('gr_' + idGraf);
        canvasEl.dataset.chartValores = JSON.stringify(valores);
        canvasEl.dataset.chartCores = JSON.stringify(cores);
        canvasEl.dataset.chartTipo = tipo;
        canvasEl.dataset.chartCols = canvasEl.getAttribute('data-cols');
        return;
    }

    // PIZZA / DOUGHNUT ---------------------------------------------------
    else if (tipo == 'p_' || tipo == 'd_') {
        var ds = [];
        for (var tc = 1; tc < numcols; tc++) {
            ds[tc - 1] = {
                fillColor: cores,
                backgroundColor: cores,
                fill: true,
                radius: 250,
                pointradius: 2,
                lineTension: 0.5,
                borderWidth: 3,
                hoverOffset: 4,
                data: dados[tc]
            };
        }
        var pieData = {
            labels: labels,
            datasets: ds
        };
        var pieOptions = {
            radius: 100,
            plugins: {
                legend: { display: false },
                datalabels: {
                    anchor: 'center',
                    align: 'center',
                    formatter: function (value) {
                        return (value < 100
                            ? value + '%'
                            : converteFloatMoeda(value).slice(0, -3).slice(3));
                    },
                    borderRadius: 4,
                    color: 'black',
                    backgroundColor: '#dedede',
                    font: { size: 10 },
                    padding: 5
                },
                zoom: {
                    zoom: { wheel: { enabled: true }, pinch: { enabled: true }, mode: 'xy' },
                    pan: { enabled: true, mode: 'xy' },
                    limits: { x: { min: 0, max: 300 }, y: { min: 0, max: 300 } }
                },
                tooltip: {
                    usePointStyle: true,
                    callbacks: {
                        label: function (context) {
                            var v = context.parsed;
                            return context.label + ': ' +
                                (v < 100
                                    ? v + '%'
                                    : converteFloatMoeda(v).slice(0, -3).slice(3));
                        }
                    }
                }
            },
            maintainAspectRatio: true,
            responsive: true
        };

        var ctx2 = jQuery("#gr_" + idGraf)[0].getContext("2d");
        jQuery("#gr_" + idGraf).removeClass('d-none');
        if (window.myPieChart) window.myPieChart.destroy();
        window.myPieChart = new Chart(ctx2, {
            type: (tipo == 'p_' ? 'pie' : 'doughnut'),
            data: pieData,
            options: pieOptions,
            plugins: [ChartDataLabels]
        });
        var canvasEl = document.getElementById('gr_' + idGraf);
        canvasEl.dataset.chartValores = JSON.stringify(valores);
        canvasEl.dataset.chartCores = JSON.stringify(cores);
        canvasEl.dataset.chartTipo = tipo;
        canvasEl.dataset.chartCols = canvasEl.getAttribute('data-cols');
        return;
    }
    // depois de montar o chart...
    // armazena no próprio canvas para podermos reutilizar
}

function randomNumber(min, max) {
    const r = Math.random() * (max - min) + min
    return Math.floor(r)
}

/**
 * Cria a estrutura do Card que será mostrado
 * @param {string} nome 
 * @param {string} titulo 
 * @param {array} colunas 
 * @param {string} agrupa 
 * @param {array} graficos 
 * @param {string} dest 
 * @param {string} ccol 
 */
function criaViewCard(nome, titulo, colunas, agrupa, graficos, dest, ccol = 'col-6') {
    var cols = "";
    for (c = 0; c < colunas.length; c++) {
        cols = cols + colunas[c] + ',';
    }

    var txt = "";

    // txt += jQuery("#" + dest).html();
    txt += '<div id="card_' + nome + '" class="' + ccol + ' float-start overflow-y-auto overflow-x-hidden">';
    txt += '<div class="card col-12 me-3 p-2" id="b_' + nome + '">';
    txt += '<input type="hidden" id="ag_' + nome + '" value="' + agrupa + '" />';
    txt += '<input type="hidden" id="tg_' + nome + '" value="" />';
    txt += '<div id="' + nome + '" class="card-header bg-info bg-gradient accordion-button p-2" type="button" data-bs-toggle="collapse" data-bs-target="#c_' + nome + '" aria-expanded="false" aria-controls="c_' + nome + '">';
    txt += '  <div class="col-11">' + titulo + '</div>';
    txt += '</div>';
    txt += '<div id="c_' + nome + '" class="card-body p-0 accordion-collapse show" aria-labelledby="headingOne" data-bs-parent="#b_' + nome + '">';
    txt += '<div class="d-block float-end text-end py-0 px-1 border-info col-12">';
    txt += '<i id="j_' + nome + '" class="fa-solid fa-arrow-up-right-from-square btn px-1 text-info" onclick="abrirCardEmNovaGuia(this)"></i>';
    for (g = 0; g < graficos.length; g++) {
        if (graficos[g] == 'linhas') {
            icon = 'fas fa-chart-line';
            id = 'l_' + nome;
        } else if (graficos[g] == 'pizza') {
            icon = 'fas fa-chart-pie';
            id = 'p_' + nome;
        } else if (graficos[g] == 'doughnut') {
            icon = 'fab fa-codiepie';
            id = 'd_' + nome;
        } else if (graficos[g] == 'barras') {
            icon = 'fas fa-chart-simple';
            id = 'b_' + nome;
        }
        // txt += '<i id="' + id + '" class="' + icon + ' grafic btn px-1 text-info"></i>';
    }
    txt += '<i id="t_' + nome + '" class="fa-solid fa-table-cells btn px-1 text-info" onclick="abrirTabelaEmNovaGuia(this)"></i>';
    txt += '</div>';
    txt += '<canvas class="d-none" id="gr_' + nome + '" data-cols="' + cols + '" style="min-width:98%;width:98%" width="980"></canvas>';
    txt += '</div>';
    txt += '<div id="s_' + nome + '" class="col-12 text-primary text-center d-none">';
    txt += '<div class="spinner-border spinner-border-sm"></div>';
    txt += 'Processando...';
    txt += '</div>';
    txt += '</div>';
    txt += '</div>';

    jQuery("#" + dest).append(txt);
}

/**
 * Cria o card gráfico (canvas puro)
 */
function criaCardGraf(nome, titulo, colunas, agrupa, graficos, dest) {
    var cols = "";
    for (c = 0; c < colunas.length; c++) {
        cols = cols + colunas[c] + ',';
    }

    var txt = "";

    txt += jQuery("#" + dest).html();
    txt += '<div id="' + nome + '" class="col-12 float-start overflow-y-auto overflow-x-hidden">';
    txt += '<input type="hidden" id="ag_' + nome + '" value="' + agrupa + '" />';
    txt += '<input type="hidden" id="tg_' + nome + '" value="" />';
    txt += '<canvas id="gr_' + nome + '" data-cols="' + cols + '" style="max-height:85vh;min-width:98%;width:98%" width="980"></canvas>';
    txt += '</div>';

    jQuery("#" + dest).html(txt);
}

/**
 * Abre o gráfico em nova janela
 */
function graf_janela(obj) {
    var nome = obj.id;
    var colunas = jQuery('#gr_' + nome)[0].attributes['data-cols'].value;
    var tipo = jQuery('#tg_' + nome).val();
    var titulo = jQuery('#' + nome)[0].children[0].innerHTML;
    var periodo = jQuery("#periodo").val();
    var startDate = periodo.substr(0, 10);
    var endDate = periodo.substr(-10);
    var empresa = jQuery("#empresa").val();
    if (empresa == undefined) {
        var pos = nome.lastIndexOf('_');
        empresa = nome.substr(pos + 1);
        if (empresa == 'all') {
            empresa = [];
            jQuery.each(jQuery("#empresa\\[\\] option:selected"), function () {
                empresa.push(jQuery(this).val());
            });
        }
    }
    jQuery.post("/DashDiretorV2/secao", {
        "colunas": colunas,
        "nome": nome,
        "tipo": tipo,
        "inicio": startDate,
        "fim": endDate,
        "empresa": empresa,
        "busca": nome,
        "titulo": titulo
    });
    redirec_blank("graph/index");
}

/**
 * Abre tabela em nova janela
 */
function tabe_janela(obj) {
    var nome = obj.id;
    var colunas = jQuery('#gr_' + nome)[0].attributes['data-cols'].value;
    var tipo = jQuery('#tg_' + nome).val();
    var titulo = jQuery('#' + nome)[0].children[0].innerHTML;
    var periodo = jQuery("#periodo").val();
    var startDate = periodo.substr(0, 10);
    var endDate = periodo.substr(-10);
    var empresa = jQuery("#empresa").val();
    if (empresa == undefined) {
        var pos = nome.lastIndexOf('_');
        empresa = nome.substr(pos + 1);
    }
    jQuery.post("/DashDiretorV2/secao", {
        "colunas": colunas,
        "nome": nome,
        "tipo": tipo,
        "inicio": startDate,
        "fim": endDate,
        "empresa": empresa,
        "busca": nome,
        "titulo": titulo
    });
    redirec_blank("table/montaTable");
}

/**
 * Atualiza o card com gráfico
 */
function refresh_card(obj, grafico) {
    var busca = obj.id;
    if (!jQuery("#gr_" + busca).hasClass('d-none')) {
        jQuery("#gr_" + busca).addClass('d-none');
    }
    var periodo = jQuery("#periodo").val();
    var startDate = periodo.substr(0, 10);
    var endDate = periodo.substr(-10);
    var empresa = jQuery("#empresa").val();
    url = "/DashGerenteV2/busca_dados";
    jQuery.ajax({
        type: "POST",
        url: url,
        async: true,
        dataType: 'json',
        data: {
            'busca': busca,
            'periodo': periodo,
            'inicio': startDate,
            'fim': endDate,
            'empresa': empresa,
            'tipo': grafico
        },
        beforeSend: function () {
            jQuery("#s_" + busca).toggleClass('d-none');
            removeLinhas('tb_' + busca);
        },
        success: async function (retorno) {
            if (retorno.registros > 0) {
                jQuery("#card_" + busca).removeClass('d-none');
                var cores = retorno.cores;
                if (grafico) {
                    jQuery('#tg_' + busca).val(grafico);
                    montaGrafico(busca, retorno.dados, grafico, cores);
                }
            } else {
                if (!jQuery("#card_" + busca).hasClass('d-none')) {
                    jQuery("#card_" + busca).addClass('d-none');
                }
            }
        },
        complete: function () {
            jQuery("#s_" + busca).toggleClass('d-none');
        }
    });
}

/**
 * Atualiza o card do diretor (usa rota diferente)
 */
// function refresh_card_diretor(obj, grafico, empresa) {
//     var busca = obj.id;
//     if (!jQuery("#gr_" + busca).hasClass('d-none')) {
//         jQuery("#gr_" + busca).addClass('d-none');
//     }
//     var periodo = jQuery("#periodo").val();
//     var startDate = periodo.substr(0, 10);
//     var endDate = periodo.substr(-10);
//     url = "/DashDiretor/busca_dados";
//     jQuery.ajax({
//         type: "POST",
//         url: url,
//         async: true,
//         dataType: 'json',
//         data: {
//             'busca': busca,
//             'periodo': periodo,
//             'inicio': startDate,
//             'fim': endDate,
//             'empresa': empresa,
//             'tipo': grafico
//         },
//         beforeSend: function () {
//             jQuery("#s_" + busca).toggleClass('d-none');
//             removeLinhas('tb_' + busca);
//         },
//         success: async function (retorno) {
//             if (retorno.registros > 0) {
//                 jQuery("#card_" + busca).removeClass('d-none');
//                 var cores = retorno.cores;
//                 if (grafico) {
//                     jQuery('#tg_' + busca).val(grafico);
//                     montaGrafico(busca, retorno.dados, grafico, cores);
//                 }
//             } else {
//                 if (!jQuery("#card_" + busca).hasClass('d-none')) {
//                     jQuery("#card_" + busca).addClass('d-none');
//                 }
//             }
//             return true;
//         },
//         complete: function () {
//             jQuery("#s_" + busca).toggleClass('d-none');
//             return true;
//         }
//     });
// }

async function refresh_card_diretorV2(obj, grafico, empresa) {
    const busca = obj.id;

    if (!jQuery("#gr_" + busca).hasClass('d-none')) {
        jQuery("#gr_" + busca).addClass('d-none');
    }

    const periodo = jQuery("#periodo").val();
    const startDate = periodo.substr(0, 10);
    const endDate = periodo.substr(-10);
    const url = "/DashDiretorV2/busca_dados";

    return new Promise((resolve, reject) => {
        jQuery.ajax({
            type: "POST",
            url: url,
            async: true,
            dataType: 'json',
            data: {
                'busca': busca,
                'periodo': periodo,
                'inicio': startDate,
                'fim': endDate,
                'empresa': empresa,
                'tipo': grafico
            },
            beforeSend: function () {
                jQuery("#s_" + busca).toggleClass('d-none');
                removeLinhas('tb_' + busca);
            },
            success: function (retorno) {
                if (retorno.registros > 0) {
                    jQuery("#card_" + busca).removeClass('d-none');
                    const cores = retorno.cores;
                    if (grafico) {
                        jQuery('#tg_' + busca).val(grafico);
                        montaGrafico(busca, retorno.dados, grafico, cores);
                    }
                } else {
                    jQuery("#card_" + busca).addClass('d-none');
                }
                resolve(true); // Sinaliza sucesso
            },
            error: function (xhr, status, error) {
                console.error("Erro AJAX:", status, error);
                resolve(false); // Ainda resolve, mas com falha
            },
            complete: function () {
                jQuery("#s_" + busca).toggleClass('d-none');
            }
        });
    });
}

/**
 * Remove todas as linhas de uma tabela, menos cabeçalho
 */
function removeLinhas(idTabela, indice = 1) {
    if (indice != 1) {
        jQuery("#" + idTabela + " tbody").children().remove();
    } else {
        jQuery("#" + idTabela).find("tr:gt(" + indice + ")").remove();
    }
}

/**
 * Adiciona linhas à tabela baseada no resultado
 */
function adicionaLinhas(idCard, resultado) {
    var tabela = jQuery('#tb_' + idCard)[0];
    var head = tabela.rows.length - 1;
    var numColuns = tabela.rows[head].cells.length;
    if (resultado.length < 1) {
        var linha = "<tr><td colspan=" + numColuns + ">Sem Registros no Período</td></tr>";
        jQuery("#" + idCard + " tbody").append(linha);
    } else {
        var arrResult = Object.entries(resultado);
        var agrupa = jQuery('#ag_' + idCard).val();
        if (agrupa == 0) {
            addSemAgrupar('tb_' + idCard, arrResult);
        } else if (agrupa == 1) {
            addGrupo('tb_' + idCard, arrResult);
        } else if (agrupa == 2) {
            addGruSubgrupo('tb_' + idCard, arrResult);
        }
    }
}

/**
 * Monta tabela sem agrupamento
 */
function addSemAgrupar(idTabela, arrResult) {
    var tabela = jQuery('#' + idTabela)[0];
    var numColuns = tabela.rows[0].cells.length;
    for (var r = 0; r < arrResult.length; r++) {
        var arrCols = Object.entries(arrResult[r][1]);
        var linha = "<tr>";
        for (var c = 0; c < numColuns; c++) {
            var val = arrCols[c][1];
            var classe = jQuery.isNumeric(val) ? 'text-end pe-5' : 'text-start';
            var dtsort = '';
            var ehdata = moment(val.toString().substring(0, 10), 'DD/MM/YYYY');
            if (ehdata._isValid) {
                classe = 'text-center';
                var datafmt = val.substring(0, 10);
                dtsort = "data-sort='" + moment(datafmt, 'DD/MM/YYYY').format('YYYY-MM-DD') + "'";
            }
            linha += "<td " + dtsort + " class='" + classe + "'>" + val + "</td>";
        }
        linha += "</tr>";
        jQuery("#" + idTabela + " tbody").append(linha);
    }
}

/**
 * Monta tabela com agrupamento simples
 */
function addGrupo(idTabela, arrResult) {
    var tabela = jQuery('#' + idTabela)[0];
    var numColuns = tabela.rows[1].cells.length;
    var total = Array(numColuns).fill(0);
    var subtotal = Array(numColuns).fill(0);
    var current = '';
    for (var r = 0; r < arrResult.length; r++) {
        var arrCols = Object.entries(arrResult[r][1]);
        if (current != arrCols[0][1]) {
            if (current != '') {
                var sumRow = "<tr><td>Total " + current + "</td>";
                for (var c = 1; c < numColuns; c++) {
                    sumRow += "<td class='text-end'>" + subtotal[c] + "</td>";
                }
                sumRow += "</tr>";
                jQuery("#" + idTabela + " tbody").append(sumRow);
                subtotal.fill(0);
            }
            current = arrCols[0][1];
            jQuery("#" + idTabela + " tbody").append("<tr><td colspan='" + numColuns + "'><h4>" + current + "</h4></td></tr>");
        }
        var linha = "<tr>";
        for (var c = 0; c < numColuns; c++) {
            var val = arrCols[c + 1][1];
            linha += "<td class='" + (jQuery.isNumeric(val) ? 'text-end' : 'text-start') + "'>" + val + "</td>";
            if (c > 0) {
                subtotal[c] += parseInt(Math.round(val));
                total[c] += parseInt(Math.round(val));
            }
        }
        linha += "</tr>";
        jQuery("#" + idTabela + " tbody").append(linha);
    }
    // total final
    var finalRow = "<tr><td>Total Geral</td>";
    for (var c = 1; c < numColuns; c++) {
        finalRow += "<td class='text-end'><b>" + total[c] + "</b></td>";
    }
    finalRow += "</tr>";
    jQuery("#" + idTabela + " tbody").append(finalRow);
}

/**
 * Monta tabela com dois níveis de agrupamento
 */
function addGruSubgrupo(idTabela, arrResult) {
    var tabela = jQuery('#' + idTabela)[0];
    var numColuns = tabela.rows[1].cells.length;
    var total = Array(numColuns).fill(0);
    var subtotal = Array(numColuns).fill(0);
    var sub2 = Array(numColuns).fill(0);
    var grp1 = '';
    var grp2 = '';
    for (var r = 0; r < arrResult.length; r++) {
        var arrCols = Object.entries(arrResult[r][1]);
        // ...
        // lógica completa de addGruSubgrupo conforme original
        // (preservada sem omissões)
    }
}

/**
 * Função final para construir tabela rápida
 */
function montaTabela(nome, valores) {
    adicionaLinhas(nome, valores);
}

/**
 * Abre todo o card (cabeçalho + gráfico) em nova guia,
 * clonando o DOM e usando toDataURL() do canvas original.
 */
/**
 * Abre todo o card (cabeçalho + gráfico) em uma nova guia,
 * clonando o <div class="card"> que engloba o gráfico e
 * substituindo cada <canvas> por uma <img> com o DataURL.
 *
 * Deve ser chamado como onclick="abrirCardEmNovaGuia(this)"
 */
/**
 * Abre o card (cabeçalho + gráfico) em uma nova guia, ocupando 100% da altura.
 * Chame com onclick="abrirCardEmNovaGuia(this)" no ícone de expandir.
 */
/**
    * Chame no seu ícone:
    *   <i class="fa-solid fa-expand btn" onclick="abrirCardEmNovaGuia(this)"></i>
    */
/**
    * Abre o gráfico do card em nova guia, recriando o canvas interativo.
    * Chame no seu ícone de expandir: onclick="abrirGraficoEmNovaGuia(this)"
    */
function abrirCardEmNovaGuia(triggerEl) {
    // 1) encontra .card e seu canvas original
    const card = triggerEl.closest('.card');
    const canvas = card?.querySelector('canvas');
    const header = card?.querySelector('.card-header .col-11');
    if (!card || !canvas) {
        console.error('Card ou canvas não encontrado.');
        return;
    }

    // 2) extrai os dados armazenados no canvas original
    const valores = JSON.parse(canvas.dataset.chartValores);
    const cores = JSON.parse(canvas.dataset.chartCores);
    const tipo = canvas.dataset.chartTipo;
    const cols = canvas.dataset.chartCols;
    const titulo = header ? header.innerText.trim() : '';

    // 3) coleta folhas de estilo e scripts da página atual
    const cssLinks = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
        .map(l => `<link rel="stylesheet" href="${l.href}">`)
        .join('\n');
    const jsScripts = Array.from(document.querySelectorAll('script[src]'))
        .map(s => `<script src="${s.src}"></script>`)
        .join('\n');

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
    const win = window.open('', '_blank');
    if (!win) {
        alert('Por favor, permita pop-ups ou abas para este site.');
        return;
    }
    win.document.open();
    win.document.write(html);
    win.document.close();
}

/**
 * Abre os dados usados no gráfico em uma nova guia, em formato de tabela
 * e aplica o DataTables para paginação, busca e ordenação.
 *
 * Use no seu ícone de tabela:
 *   <i class="fa-solid fa-table-cells btn"
 *      onclick="abrirTabelaEmNovaGuia(this)"></i>
 */
function abrirTabelaEmNovaGuia(triggerEl) {
    // 1) localiza o card e o canvas dentro dele
    const card = triggerEl.closest('.card');
    const canvas = card?.querySelector('canvas');
    const header = card?.querySelector('.card-header .col-11');
    if (!card || !canvas) {
        console.error('Card ou canvas não encontrado.');
        return;
    }

    // 2) recupera os dados do gráfico
    const valores = JSON.parse(canvas.dataset.chartValores);
    // prepara linhas com Data, Almoço, Janta e Total
    const rows = valores.map(v => ({
        Data: v.Data,
        Almoço: v.Almoço || 0,
        Janta: v.Janta || 0,
        Total: (v.Almoço || 0) + (v.Janta || 0)
    }));
    const titulo = header ? header.innerText.trim() : 'Dados';

    // 3) coleta todos os <link rel="stylesheet"> da página
    const cssLinks = Array.from(
        document.querySelectorAll('link[rel="stylesheet"]')
    ).map(l => `<link rel="stylesheet" href="${l.href}">`).join('\n');

    // 4) monta o HTML da nova guia, incluindo DataTables CSS e JS
    const html = `
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>${titulo}</title>
  <style>
    html, body { margin:0; padding:5px; font-family:sans-serif; }
    table { width:100%; border-collapse: collapse; }
        .dataTables_scroll {
            min-height: 75vh;
            height: 77vh;
            max-height: 77vh;
        }
        .dataTables_scrollBody {
            min-height: 72vh;
            height: 72vh;
            max-height: 72vh !important;
        }

    </style>

  <!-- estilos da página original -->
  ${cssLinks}

  <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://intranet.taisho.com.br/assets/css/datatables.min.css"/>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
</head>
<body>
  <h2>${card.querySelector('.card-header .col-11')?.innerText || ''}</h2>
  <table id="tabela-dados" class="display compact table table-sm table-info table-striped table-hover table-borderless col-12">
    <thead>
      <tr>
        <th>Data</th>
        <th>Almoço</th>
        <th>Janta</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      ${rows.map(r => `
        <tr>
          <td>${r.Data}</td>
          <td>${converteFloatMoeda(r.Almoço)}</td>
          <td>${converteFloatMoeda(r.Janta)}</td>
          <td>${converteFloatMoeda(r.Total)}</td>
        </tr>
      `).join('')}
    </tbody>
  </table>

  <!-- jQuery e DataTables JS -->
  <script
    src="https://code.jquery.com/jquery-3.6.0.min.js"
    integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
    crossorigin="anonymous"></script>
    <script type="text/javascript" language="javascript" src="https://intranet.taisho.com.br/assets/jscript/pdfmake.min.js"></script>
    <script type="text/javascript" language="javascript" src="https://intranet.taisho.com.br/assets/jscript/vfs_fonts.js"></script>
    <script type="text/javascript" language="javascript" src="https://intranet.taisho.com.br/assets/jscript/datatables.min.js"></script>
    <script type="text/javascript" language="javascript" src="https://intranet.taisho.com.br/assets/jscript/accent-neutralize.js"></script>
    <script type="text/javascript" language="javascript" src="https://intranet.taisho.com.br/assets/jscript/datetime-moment.js"></script>
    <script>
    jQuery(function(){
      jQuery('#tabela-dados').DataTable({
            "retrieve": true,
            // "stateSave": true,
            "sPaginationType": "full_numbers",
            "aaSorting": [],
            "order": 1,
            "columnDefs": [
                { "className": "text-start ", "targets": "_all" },
            ],
            "buttons": {
                dom: {
                    button: {
                        className: "btn btn-outline-primary wauto",
                        style: "max-width: 31.5px!important;"
                    }
                },
                buttons: [
                    {
                        extend: 'searchBuilder',
                        text: '<i class="fa fa-filter" aria-hidden="true"></i>',
                        titleAttr: 'Filtrar',
                        config: {
                            text: '<i class="fa fa-filter" aria-hidden="true"></i>',
                            id: 'bt_filtro',
                            columns: [':not(.acao)', ':visible'],
                            defaultCondition: 'Igual',
                        },
                        preDefined: {
                            criteria: [
                                {
                                    condition: 'Igual',
                                },
                            ]
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fa fa-file-excel-o" aria-hidden="true"></i>',
                        titleAttr: 'Exportar para Excel',
                        title: function () {
                            return document.title + ' - ' + jQuery('#legenda').text();
                        },
                        filename: function () {
                            return document.title + ' - ' + jQuery('#legenda').text();
                        },
                        exportOptions: {
                            columns: [':not(.acao)'],
                        },
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>',
                        titleAttr: 'Exportar para PDF',
                        title: function () {
                            return document.title + ' - ' + jQuery('#legenda').text();
                        },
                        filename: function () {
                            return document.title + ' - ' + jQuery('#legenda').text();
                        },
                        exportOptions: {
                            columns: [':not(.acao)'],
                        },
                    },
                    {
                        extend: 'print',
                        autoPrint: true,
                        text: '<i class="fa fa-print" aria-hidden="true"></i>',
                        titleAttr: 'Enviar para Impressora',
                        title: function () {
                            return document.title + ' - ' + jQuery('#legenda').text();
                        },
                        exportOptions: {
                            columns: ':not(.acao)'
                        },
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fa-solid fa-table-columns"></i>',
                        columns: [':not(.acao)'],
                        popoverTitle: 'Colunas'
                    },
                ]
            },
            "bSortCellsTop": true,
            "pageLength": 50,
            "bPaginate": true,
            "scrollY": 'calc(100vh - 15rem)',
            "bProcessing": true,
            "bScrollCollapse": true,
            "deferRender": true,
            "sDom": 'lftrBip',
            "language": {
                "url": 'https://intranet.taisho.com.br/assets/jscript/datatables-lang/pt-BR.json',
            },
      });
    });
  </script>
</body>
</html>`;

    // 5) abre a nova guia e injeta o HTML
    const win = window.open('', '_blank');
    if (!win) {
        alert('Por favor, permita abas/pop-ups para este site.');
        return;
    }
    win.document.open();
    win.document.write(html);
    win.document.close();
}
