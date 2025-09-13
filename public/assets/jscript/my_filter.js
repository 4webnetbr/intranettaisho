/**
 * buscaSaldo
 * Processa a busca de Saldos de Estoque
 */
function buscaSaldo() {
  var codDep = jQuery.trim(jQuery("#codDep").val());
  // var codlot = jQuery.trim(jQuery('#codLot').val());
  // if(codpro == ''){
  //     boxAlert('O Código do Produto é Obrigatório', true,'',false, 1, false);
  //     return;
  // }
  bloqueiaTela();
  urlBusca = "SaldoEstoque/lista";
  jQuery.ajax({
    type: "POST",
    async: true,
    dataType: "json",
    url: urlBusca,
    data: { codDep: codDep },
    success: function (retorno) {
      // console.log(retorno);
      montaListaSaldo(retorno);
      desBloqueiaTela();
    },
  });
}

function montaListaSaldo(dados) {
  jQuery("#table").DataTable().destroy();
  removeLinhas("table", 0);

  linha = "";
  for (var index in dados) {
    item = dados[index];
    linha = linha + "<tr>";
    linha = linha + "<td class='align-middle'>" + item.codDep + "</td>";
    linha = linha + "<td class='align-middle'>" + item.Coderp + "</td>";
    linha = linha + "<td class='align-middle'>" + item.DescProduto + "</td>";
    linha = linha + "<td class='align-middle'>" + item.lote + "</td>";
    linha =
      linha +
      "<td class='align-middle' data-sort='" +
      item.validadeord +
      "'>" +
      item.validade +
      "</td>";
    linha =
      linha + "<td class='align-middle text-end pe-4'>" + item.saldo + "</td>";
    linha = linha + "<td class='align-middle text-start'>" + item.und + "</td>";
    linha = linha + "</tr>";
  }
  jQuery("#table tbody").append(linha);
  dtResult("table");
  // configTableDt("table_grupo-cubo");
}

function dtResult(tabela) {
  // monta o Datable
  var table = jQuery("#" + tabela).DataTable({
    responsive: false,
    sPaginationType: "full_numbers",
    aaSorting: [],
    columnDefs: [
      // { "max-width": "8em", "targets": ['all'] },
      { "min-width": "8em", targets: ["all"] },
      { width: "8em", targets: ["all"] },
      { className: "text-wrap text-nowrap", targets: ["all"] },
    ],
    buttons: {
      dom: {
        button: {
          className: "btn btn-outline-primary wauto",
          style: "max-width: 31.5px!important;",
        },
      },
      buttons: [
        {
          extend: "excelHtml5",
          text: '<i class="fa fa-file-excel-o" aria-hidden="true"></i>',
          titleAttr: "Exportar para Excel",
          title: function () {
            return document.title + " - " + jQuery("#legenda").text();
          },
          filename: function () {
            return document.title + " - " + jQuery("#legenda").text();
          },
          exportOptions: {
            columns: [":not(.acao)"],
          },
        },
        {
          extend: "pdfHtml5",
          text: '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>',
          titleAttr: "Exportar para PDF",
          title: function () {
            return document.title + " - " + jQuery("#legenda").text();
          },
          filename: function () {
            return document.title + " - " + jQuery("#legenda").text();
          },
          exportOptions: {
            columns: [":not(.acao)"],
          },
        },
        {
          extend: "print",
          text: '<i class="fa fa-print" aria-hidden="true"></i>',
          titleAttr: "Enviar para Impressora",
          title: function () {
            return document.title + " - " + jQuery("#legenda").text();
          },
          exportOptions: {
            columns: [":not(.acao)"],
          },
        },
      ],
    },
    // header options
    orderCellsTop: true,
    fixedHeader: true,
    bSortCellsTop: true,
    pageLength: 50,
    bPaginate: true,
    scrollY: "58vh !important",
    bProcessing: true,
    bScrollCollapse: true,
    deferRender: true,
    sDom: "ftrBip",
    language: {
      url: "assets/jscript/datatables-lang/pt-BR.json",
    },
    fnDrawCallback: function (oSettings) {
      jQuery("table#" + tabela + " > tbody > tr")
        .on("mouseover", function () {
          jQuery(this).children().find(".btn").addClass("hover");
        })
        .on("mouseleave", function () {
          jQuery(this).children().find(".btn").removeClass("hover");
        });
    },
  });
}

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
  jQuery("#" + idTabela)
    .DataTable()
    .destroy();
}

function carrega_lista(obj, url, nome) {
  var tabela = "tb_" + nome;

  if (typeof obj == "string") {
    param = jQuery("#" + obj).val();
  } else {
    var param = obj.value;
  }
  url = url + "?param=" + param;
  montaListaDados(tabela, url);
}

function carrega_lista_edit(obj, url, nome) {
  var tabela = "tb_" + nome;

  if (typeof obj == "string") {
    param = jQuery("#" + obj).val();
  } else {
    var param = obj.value;
  }
  url = url + "?param=" + param;

  montaListaDadosEdit(tabela, url);
}

function carrega_lista_detail(obj, url, nome) {
  var tabela = "tb_" + nome;

  if (typeof obj == "string") {
    param = jQuery("#" + obj).val();
  } else {
    var param = obj.value;
  }
  url = url + "?param=" + param;
  montaListaDadosDetail(tabela, url);
}

function carrega_lista_2param(obj, url, nome) {
  var tabela = "tb_" + nome;

  var empresa = jQuery("#emp_id").val();
  var deposito = jQuery("#dep_id").val();

  if (deposito != "" && empresa != "") {
    url = url + "?empresa=" + empresa + "&deposito=" + deposito;

    montaListaDadosEdit(tabela, url);
  }
}

function carrega_saldos() {
  var tabela = "tb_saldoestoque";

  var empresa = jQuery("#empresa").val();
  var deposito = jQuery("#deposito").val();

  if (deposito != "" && empresa != "") {
    // jQuery('#' + tabela).closest('tr').remove();
    url =
      "/DashEstoque/busca_dados?empresa=" + empresa + "&deposito=" + deposito;
    montaListaDados(tabela, url);
  }
  // bloqueiaTela();
  // var tabela = 'tb_saldoestoque';

  // var empresa = jQuery("#empresa").val();
  // var deposito = jQuery("#deposito").val();

  // // url = "/DashEstoque/busca_dados";
  // url = "/DashEstoque/busca_dados?empresa=" + empresa + "&deposito=" + deposito;
  // montaListaDados(tabela, url);

  // jQuery.ajax({
  //     type: "POST",
  //     url: url,
  //     async: true,
  //     dataType: 'json',
  //     data: { 'empresa': empresa, 'deposito': deposito },
  //     beforeSend: function () {
  //         removeLinhas('tb_' + busca);
  //     },

  //     success: async function (retorno) {
  //         if (retorno.produtos.length > 0) {
  //             adicionaLinhas('saldoestoque', retorno.produtos);
  //             dataTableTabela('saldoestoque');
  //         }
  //     }
  // });
}
function carrega_contagem(obj) {
  // bloqueiaTela();
  var tabela = "tb_relcontagem";
  var contagem = jQuery("#contagem").val();

  if (contagem != "") {
    // jQuery('#' + tabela).closest('tr').remove();
    url = "/EstRelContagem/busca_dados?contagem=" + contagem;
    montaListaDados(tabela, url);
  }
}

function carrega_saidas(obj) {
  // bloqueiaTela();
  var tabela = "tb_relsaidas";
  var periodo = jQuery("#periodo").val();
  var startDate = periodo.substr(0, 10); //jQuery("#periodo").data('daterangepicker').startDate.format('DD/MM/YYYY');
  var endDate = periodo.substr(-10); //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');
  if (endDate == "Considerar") {
    startDate = "01/01/2001";
    endDate = "31/12/2100";
  }
  //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');

  var empresa = jQuery("#empresa").val();
  var deposito = jQuery("#deposito").val();

  if (empresa != "" && startDate != "") {
    // jQuery('#' + tabela).closest('tr').remove();
    url =
      "/EstRelSaida/busca_dados?empresa=" +
      empresa +
      "&deposito=" +
      deposito +
      "&inicio=" +
      startDate +
      "&fim=" +
      endDate;
    montaListaDados(tabela, url);
  }
}

function carrega_entradas(obj) {
  // bloqueiaTela();
  var tabela = "tb_relentradas";
  var periodo = jQuery("#periodo").val();
  var startDate = periodo.substr(0, 10); //jQuery("#periodo").data('daterangepicker').startDate.format('DD/MM/YYYY');
  var endDate = periodo.substr(-10); //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');
  if (endDate == "Considerar") {
    startDate = "01/01/2001";
    endDate = "31/12/2100";
  }
  //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');

  var empresa = jQuery("#empresa").val();
  var deposito = jQuery("#deposito").val();

  if (deposito != "" && empresa != "" && startDate != "") {
    // jQuery('#' + tabela).closest('tr').remove();
    url =
      "/EstRelEntrada/busca_dados?empresa=" +
      empresa +
      "&deposito=" +
      deposito +
      "&inicio=" +
      startDate +
      "&fim=" +
      endDate;
    montaListaDados(tabela, url);
  }
}

function carrega_historico(obj) {
  // bloqueiaTela();
  var tabela = "tb_relhistorico";
  var periodo = jQuery("#periodo").val();
  var startDate = periodo.substr(0, 10); //jQuery("#periodo").data('daterangepicker').startDate.format('DD/MM/YYYY');
  var endDate = periodo.substr(-10); //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');
  if (endDate == "Considerar") {
    startDate = "01/01/2001";
    endDate = "31/12/2100";
  }
  //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');

  var empresa = jQuery("#empresa").val();
  var deposito = jQuery("#deposito").val();
  var produto = jQuery("#produto").val();

  if (deposito != "" && empresa != "" && produto != "" && startDate != "") {
    // table = undefined;
    jQuery("#" + tabela)
      .closest("tr")
      .remove();
    url =
      "/EstRelHistorico/busca_dados?empresa=" +
      empresa +
      "&deposito=" +
      deposito +
      "&produto=" +
      produto +
      "&inicio=" +
      startDate +
      "&fim=" +
      endDate;
    montaListaDados(tabela, url);
  }
}

function carrega_compras(obj) {
  // bloqueiaTela();
  var tabela = "tb_relcompras";
  var periodo = jQuery("#periodo").val();
  var startDate = periodo.substr(0, 10); //jQuery("#periodo").data('daterangepicker').startDate.format('DD/MM/YYYY');
  var endDate = periodo.substr(-10); //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');
  if (endDate == "Considerar") {
    startDate = "01/01/2001";
    endDate = "31/12/2100";
  }
  //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');

  var empresa = jQuery("#empresa").val();
  var fornecedor = jQuery("#fornecedor").val();

  if (empresa != "" && startDate != "") {
    // jQuery('#' + tabela).closest('tr').remove();
    url =
      "/EstRelCompra/busca_dados?empresa=" +
      empresa +
      "&fornecedor=" +
      fornecedor +
      "&inicio=" +
      startDate +
      "&fim=" +
      endDate;
    montaListaDadosGrupo(tabela, url, 1);
  }
}

function carrega_pedidos(obj) {
  // bloqueiaTela();
  var tabela = "tb_relpedidos";
  var periodo = jQuery("#periodo").val();
  var startDate = periodo.substr(0, 10); //jQuery("#periodo").data('daterangepicker').startDate.format('DD/MM/YYYY');
  var endDate = periodo.substr(-10); //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');
  if (endDate == "Considerar") {
    startDate = "01/01/2001";
    endDate = "31/12/2100";
  }
  //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');
  if (endDate == "Considerar") {
    startDate = "01/01/2001";
    endDate = "31/12/2100";
  }
  var empresa = jQuery("#empresa").val();

  if (empresa != "" && startDate != "") {
    // jQuery('#' + tabela).closest('tr').remove();
    url =
      "/EstRelPedido/busca_dados?empresa=" +
      empresa +
      "&inicio=" +
      startDate +
      "&fim=" +
      endDate;
    montaListaDadosGrupo(tabela, url, 1);
  }
}

function carrega_movimentos(obj) {
  // bloqueiaTela();
  var tabela = "tb_relmovimentos";
  var periodo = jQuery("#periodo").val();
  var startDate = periodo.substr(0, 10); //jQuery("#periodo").data('daterangepicker').startDate.format('DD/MM/YYYY');
  var endDate = periodo.substr(-10); //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');
  if (endDate == "Considerar") {
    startDate = "01/01/2001";
    endDate = "31/12/2100";
  }
  //jQuery("#periodo").data('daterangepicker').endDate.format('DD/MM / YYYY');

  var empresa = jQuery("#empresa").val();
  // var deposito = jQuery("#deposito").val();

  if (empresa != "" && periodo != "" && startDate != "") {
    // jQuery('#' + tabela).closest('tr').remove();
    url =
      "/EstRelMovimento/busca_dados?empresa=" +
      empresa +
      "&inicio=" +
      startDate +
      "&fim=" +
      endDate;
    montaListaDados(tabela, url, 1);
  }
}

// Suponha que exista um container no DOM com id "#cardsContainer":
// <div id="cardsContainer"></div>

// Container para os cards (se não existir, cria dentro do <body>)
function carregaCotacao() {
  var cotId = jQuery.trim(jQuery("#cot_id").val());
  var forId = jQuery.trim(jQuery("#for_id").val());
  if (forId != "") {
    var url = window.location.origin + "/EstCotForn/listaforn";

    jQuery.ajax({
      type: "POST",
      url: url,
      dataType: "json",
      data: { cotacao: cotId, fornecedor: forId },
      success: function (retorno) {
        var container = jQuery("#cardsContainer").empty();
        let rowIndex = 0; // contador de linhas
        // Para cada linha do JSON
        retorno.data.forEach(function (item) {
          // mapeia índice → label e valor
          u_cons = item[5];
          u_comp = item[6];
          var campos = {
            Produto: item[3],
            Marca: item[4],
            // Apresentação: item[5],
            // Und: item[6],
            Quantia: item[7],
            [`Preço ${u_cons}`]: item[8],
            [`Preço ${u_comp}`]: item[9],
            Validade: item[10],
            "Entrega(dias)": item[11],
            Observação: item[12],
          };

          // monta o card
          let cardClass =
            rowIndex % 2 === 0
              ? "card mb-2 w-100 bg-gray-padrao"
              : "card mb-2 w-100";
          var card = jQuery("<div>", { class: cardClass });
          var body = jQuery("<div>", { class: "card-body" });
          let row = jQuery("<div>", { class: "row" });

          // depois de usar a linha, incremente o contador
          rowIndex++;

          // pra cada campo, cria uma coluna que no mobile ocupa 12 e no desktop 2
          let lastLabel = null;

          jQuery.each(campos, function (label, valor) {
            let colClass = "col-12 col-lg-1 mb-2";
            if (
              label === "Produto" ||
              label === "Marca" ||
              label === "Und" ||
              label.startsWith("Preço")
            ) {
              colClass = "col-12 col-lg-2 mb-2";
            } else if (label === "Observação") {
              colClass = "col-12 col-lg-12 mb-2";
            }

            const col = jQuery("<div>", { class: colClass });

            // Oculta coluna se label for igual ao anterior
            if (label === lastLabel) {
              col.addClass("d-none");
            }

            col.html(
              "<strong>" +
                '<span class="text-start text-nowrap">' +
                label +
                ":</span>" +
                "</strong><br>" +
                '<span class="text-start">' +
                valor +
                "</span>"
            );

            row.append(col);
            lastLabel = label; // Atualiza o label anterior
          });

          body.append(row);
          card.append(body);
          container.append(card);
          container.removeClass("d-none");
        });
      },
      error: function () {
        alert("Falha ao carregar cotações.");
      },
    });
  }
}
