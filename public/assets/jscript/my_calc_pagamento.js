/**
 * Calcula a Folha de Pagamento
 *
 */
function calcula_pagamento() {
  empresa = jQuery("#emp_id").val();
  registro = (jQuery("#emp_id_registro\\[\\]").val() || []).join(",");
  competencia = jQuery("#competencia").val();
  vtcuritiba = jQuery("#vtcuritiba").val();
  vtmetropolitana = jQuery("#vtmetropolitana").val();
  if (registro == null) {
    boxAlert("Informe a Empresa", true, "", false, 1, false, "Empresa");
  } else if (competencia == "-1") {
    boxAlert("Informe a Competência", true, "", false, 1, false, "Competência");
  } else {
    const mesano = competencia.split("/"); // Divide a string em partes
    const inicio = new Date(mesano[1], mesano[0] - 1, 1); // Lembre-se que o mês começa em 0
    console.log(inicio); // Exibe a data como objeto Date
    const final = new Date(mesano[1], mesano[0], 0);
    console.log(final); // Exibe a data como objeto Date

    var tabela = "tb_pagamento";

    url =
      "/RhPagamento/busca_dados?empresa=" +
      empresa +
      "&registro=" +
      registro +
      "&competencia=" +
      competencia +
      "&vtcur=" +
      vtcuritiba +
      "&vtmet=" +
      vtmetropolitana;
    montaListaDados(tabela, url);
  }
}

/**
 * Calcula Gojeta
 *
 */
function calcula_gorgeta() {
  empresa = jQuery("#emp_id").val();
  competencia = jQuery("#competencia").val();
  valordistribuido = jQuery("#valordistribuido").val();
  if (empresa == null) {
    boxAlert("Informe a Empresa", true, "", false, 1, false, "Empresa");
  } else if (competencia == "-1") {
    boxAlert("Informe a Competência", true, "", false, 1, false, "Competência");
  } else {
    const mesano = competencia.split("/"); // Divide a string em partes
    const inicio = new Date(mesano[1], mesano[0] - 1, 1); // Lembre-se que o mês começa em 0
    console.log(inicio); // Exibe a data como objeto Date
    const final = new Date(mesano[1], mesano[0], 0);
    console.log(final); // Exibe a data como objeto Date

    var tabela = "tb_gorjeta";

    url =
      "/RhGorjeta/busca_dados?empresa=" +
      empresa +
      "&competencia=" +
      competencia +
      "&valordistribuido=" +
      valordistribuido;
    montaListaDados(tabela, url);
  }
}

/**
 * Carrega Solver
 *
 */
function carrega_solver(obj) {
  idobj = obj.id;
  empresa = jQuery("#emp_id").val();
  competencia = jQuery("#competencia").val();
  setor = jQuery("#set_id").val();
  distrib = jQuery("#distribui").val();
  if (empresa == null) {
    if (idobj == "set_id") {
      boxAlert("Informe a Empresa", true, "", false, 1, false, "Empresa");
    }
  } else if (competencia == "-1") {
    if (idobj == "set_id") {
      boxAlert(
        "Informe a Competência",
        true,
        "",
        false,
        1,
        false,
        "Competência"
      );
    }
  } else if (distrib <= 0) {
    if (idobj == "set_id") {
      boxAlert(
        "Informe o Valor Distribuído",
        true,
        "",
        false,
        1,
        false,
        "Valor Distribuído"
      );
    }
  } else if (setor == null) {
    if (idobj == "set_id") {
      boxAlert("Informe o Setor", true, "", false, 1, false, "Setor");
    }
  } else {
    bloqueiaTela();
    var tabela = "tb_solver";

    url =
      "/RhSolver/busca_dados?empresa=" +
      empresa +
      "&competencia=" +
      competencia +
      "&setor=" +
      setor +
      "&distribui=" +
      distrib;
    // url = window.location.origin + '/buscas/buscaprodutomarca';
    jQuery.ajax({
      type: "POST",
      async: false,
      dataType: "json",
      url: url,
      // data: { 'marca': valor },
      success: function (retorno) {
        text = "";
        retorno.data[0].forEach((element) => {
          text += element;
        });
        jQuery("#tbsolver").html(text);
        desBloqueiaTela();
      },
    });
  }
}

/**
 * Carrega Solver
 *
 */
function carrega_solver(obj) {
  idobj = obj.id;
  empresa = jQuery("#emp_id").val();
  competencia = jQuery("#competencia").val();
  setor = jQuery("#set_id").val();
  distrib = jQuery("#distribui").val();
  if (empresa == null) {
    if (idobj == "set_id") {
      boxAlert("Informe a Empresa", true, "", false, 1, false, "Empresa");
    }
  } else if (competencia == "-1") {
    if (idobj == "set_id") {
      boxAlert(
        "Informe a Competência",
        true,
        "",
        false,
        1,
        false,
        "Competência"
      );
    }
  } else if (distrib <= 0) {
    if (idobj == "set_id") {
      boxAlert(
        "Informe o Valor Distribuído",
        true,
        "",
        false,
        1,
        false,
        "Valor Distribuído"
      );
    }
  } else if (setor == null) {
    if (idobj == "set_id") {
      boxAlert("Informe o Setor", true, "", false, 1, false, "Setor");
    }
  } else {
    bloqueiaTela();
    var tabela = "tb_solver";

    url =
      "/RhSolver/busca_dados?empresa=" +
      empresa +
      "&competencia=" +
      competencia +
      "&setor=" +
      setor +
      "&distribui=" +
      distrib;
    // url = window.location.origin + '/buscas/buscaprodutomarca';
    jQuery.ajax({
      type: "POST",
      async: false,
      dataType: "json",
      url: url,
      // data: { 'marca': valor },
      success: function (retorno) {
        text = "";
        retorno.data[0].forEach((element) => {
          text += element;
        });
        jQuery("#tbsolver").html(text);
        desBloqueiaTela();
      },
    });
  }
}

/**
 * recalcula Solver
 *
 */
function recalcula_solver(tipo, obj = null) {
  // event.preventDefault()
  pctalt = null;
  if (obj != null) {
    if (tipo == 3) {
      pctalt = obj.getAttribute("data-index");
    }
  }
  var form = jQuery("#form1");
  jQuery(".moeda").each(function () {
    var valor = converteMoedaFloat(jQuery(this).val());
    jQuery(this).val(valor);
  });
  var disabled = form.find(":input:disabled").removeAttr("disabled");

  var dadosForm = new FormData(form[0]);
  disabled.attr("disabled", "disabled");

  jQuery(".moeda").each(function () {
    var valor = converteFloatMoeda(jQuery(this).val());
    jQuery(this).val(valor);
  });
  // bloqueiaTela();

  url = "/RhSolver/recalcula_solver/" + tipo;
  if (pctalt != null) {
    url = "/RhSolver/recalcula_solver/" + tipo + "/" + pctalt;
  }
  // url = window.location.origin + '/buscas/buscaprodutomarca';
  jQuery.ajax({
    type: "POST",
    headers: { "X-Requested-With": "XMLHttpRequest" },
    url: url,
    processData: false,
    contentType: false,
    dataType: "json",
    data: dadosForm,
    success: function (retorno) {
      text = "";
      Object.keys(retorno.calculo).forEach((key) => {
        valor = retorno.calculo[key];
        jQuery("#" + key).val(valor);
        // jQuery('#' + key).attr('disabled', 'disabled');
      });
      retorno.funcoes.forEach((element, ind) => {
        Object.keys(element).forEach((key) => {
          valor = element[key];
          jQuery("#" + key + "\\[" + ind + "\\]").val(valor);
          // jQuery('#' + key + '\\[' + ind + '\\]').attr('disabled', 'disabled');
        });
      });
      // retorno.data[0].forEach(element => {
      //     text += element;
      // });
      // jQuery('#tbsolver').html(text);
      // desBloqueiaTela();
    },
  });
}
