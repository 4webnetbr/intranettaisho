function pesquisacep(obj, valor, prefixo) {
  // regex = '\[(\w+)\]';
  // pos = obj['id'].indexOf("__");
  alter = obj.getAttribute("data-alter");
  if (alter) {
    posi = obj.id.indexOf("[") + 1;
    posf = obj.id.indexOf("]");
    pos = obj.id.substr(posi, posf - posi);
    if (pos >= 0) {
      pos_seq = obj["id"].substring(pos + 2, obj["id"].length);
    }
    //Nova variável "cep" somente com dígitos.
    var cep = valor.replace(/\D/g, "");
    //Verifica se campo cep possui valor informado.
    if (cep != "") {
      //Expressão regular para validar o CEP.
      var validacep = /^[0-9]{8}$/;
      //Valida o formato do CEP.
      if (validacep.test(cep)) {
        // limpa_formulário_cep();
        var url = "https://viacep.com.br/ws/" + cep + "/json/?callback=?";

        // Faz a pesquisa do CEP, tratando o retorno com try/catch para que
        // caso ocorra algum erro (o cep pode não existir, por exemplo) a
        // usabilidade não seja afetada, assim o usuário pode continuar//
        // preenchendo os campos normalmente
        jQuery.getJSON(url, function (dadosRetorno) {
          try {
            jQuery("#" + prefixo + "endereco").val(dadosRetorno.logradouro);
            jQuery("#" + prefixo + "bairro").val(dadosRetorno.bairro);
            jQuery("#" + prefixo + "estado").val(dadosRetorno.uf);
            jQuery("#" + prefixo + "cidade").val(dadosRetorno.localidade);
          } catch (ex) {}
        });
      } //end if.
      else {
        //cep é inválido.
        // limpa_formulário_cep();
        alert("Formato de CEP inválido.");
      }
    } //end if.
    else {
      //cep sem valor, limpa formulário.
      // limpa_formulário_cep();
    }
  }
}

function pesquisaCNPJ(valor) {
  precampo = event.target.id.substr(0, 3);
  //Nova variável "CNPJ" somente com dígitos.
  var CNPJ = valor.replace(/\D/g, "");
  //Verifica se campo CNPJ possui valor informado.
  if (CNPJ != "") {
    //Expressão regular para validar o CEP.
    var validaCNPJ = /(\d{0,2})(\d{0,3})(\d{0,3})(\d{0,4})(\d{0,2})/;
    //Valida o formato do CEP.
    if (validaCNPJ.test(CNPJ)) {
      // testaCliente = CNPJCPFcadastrado(valor);
      url = window.location.origin + "/buscas/cnpjcpfcadastrado";
      jQuery.ajax({
        type: "POST",
        async: false,
        dataType: "json",
        url: url,
        data: { cpfcnpf: valor },
        success: function (retorno) {
          if (retorno.tem == "1") {
            boxAlert("CNPJ Já Cadastrado", true, "", false, 1, false);
            jQuery("#" + precampo + "_cnpj").val("");
            jQuery("#" + precampo + "_cnpj").focus();
          } else {
            // var url = "https://api-publica.speedio.com.br/buscarcnpj?cnpj=" + CNPJ;
            var url = "https://publica.cnpj.ws/cnpj/" + CNPJ;

            // Faz a pesquisa do CEP, tratando o retorno com try/catch para que
            // caso ocorra algum erro (o CNPJ pode não existir, por exemplo) a
            // usabilidade não seja afetada, assim o usuário pode continuar//
            // preenchendo os campos normalmente

            jQuery.getJSON(url, function (dadosRetorno) {
              if (precampo == "for") {
                jQuery("#" + precampo + "_razao").val(
                  dadosRetorno["razao_social"]
                );
                jQuery("#" + precampo + "_fantasia").val(
                  dadosRetorno["estabelecimento"]["nome_fantasia"]
                );
                jQuery("#" + precampo + "_rua").val(
                  dadosRetorno["estabelecimento"]["tipo_logradouro"] +
                    " " +
                    dadosRetorno["estabelecimento"]["logradouro"]
                );
                jQuery("#" + precampo + "_complemento").val(
                  dadosRetorno["estabelecimento"]["complemento"]
                );
                jQuery("#" + precampo + "_cep").val(
                  dadosRetorno["estabelecimento"]["cep"]
                );
                jQuery("#" + precampo + "_bairro").val(
                  dadosRetorno["estabelecimento"]["bairro"]
                );
                jQuery("#" + precampo + "_cidade").val(
                  dadosRetorno["estabelecimento"]["cidade"]["nome"]
                );
                jQuery("#" + precampo + "_numero").val(
                  dadosRetorno["estabelecimento"]["numero"]
                );
                jQuery("#" + precampo + "_fone").val(
                  dadosRetorno["estabelecimento"]["ddd1"] +
                    dadosRetorno["estabelecimento"]["telefone1"]
                );
                jQuery("#" + precampo + "_estado").val(
                  dadosRetorno["estabelecimento"]["estado"]["sigla"]
                );
              } else if (precampo == "emp") {
                jQuery("#" + precampo + "_nome").val(
                  dadosRetorno["razao_social"]
                );
                jQuery("#" + precampo + "_apelido").val(
                  dadosRetorno["estabelecimento"]["nome_fantasia"]
                );
                jQuery("#" + precampo + "_cnae").val(
                  dadosRetorno["estabelecimento"]["atividade_principal"][
                    "classe"
                  ]
                );
                jQuery("#" + precampo + "_cnae_desc").val(
                  dadosRetorno["estabelecimento"]["atividade_principal"][
                    "descricao"
                  ]
                );
              }
              // jQuery('#for_razao').val(dadosRetorno['RAZAO SOCIAL']);
              // jQuery('#for_fantasia').val(dadosRetorno['NOME FANTASIA']);
              // jQuery("#for_rua").val(dadosRetorno['TIPO LOGRADOURO'] + ' ' + dadosRetorno['LOGRADOURO']);
              // jQuery("#for_complemento").val(dadosRetorno['COMPLEMENTO']);
              // jQuery("#for_cep").val(dadosRetorno['CEP']);
              // jQuery("#for_bairro").val(dadosRetorno['BAIRRO']);
              // jQuery("#for_cidade").val(dadosRetorno['MUNICIPIO']);
              // jQuery("#for_numero").val(dadosRetorno['NUMERO']);
              // jQuery("#for_fone").val(dadosRetorno['DDD'] + dadosRetorno['TELEFONE']);
              // jQuery("#for_estado").val(dadosRetorno['UF']);
              // jQuery('#emp_cnae').val(dadosRetorno['CNAE PRINCIPAL CODIGO']);
              // jQuery('#emp_cnad').val(dadosRetorno['CNAE PRINCIPAL DESCRICAO']);
              localStorage.setItem("dadoscnpj", dadosRetorno);
              // pesquisacepcnpj();
            });
          }
        },
      });
    } //end if.
    else {
      //CNPJ é inválido.
      // limpa_formulário_CNPJ();
      boxAlert("Formato de CNPJ inválido", true, "", false, 1, false);
    }
  } //end if.
  else {
    //CNPJ sem valor, limpa formulário.
    // limpa_formulário_CNPJ();
  }
}

function buscaCNPJ(valor) {
  let precampo;
  precampo = event.target.id.substr(0, 3);
  if (valor && typeof valor === "object") {
    valor = valor.value; // Pega o valor do input
  }
  //Nova variável "CNPJ" somente com dígitos.
  var CNPJ = valor.replace(/\D/g, "");
  //Verifica se campo CNPJ possui valor informado.
  if (CNPJ != "") {
    //Expressão regular para validar o CEP.
    var validaCNPJ = /(\d{0,2})(\d{0,3})(\d{0,3})(\d{0,4})(\d{0,2})/;
    //Valida o formato do CEP.
    if (validaCNPJ.test(CNPJ)) {
      // testaCliente = CNPJCPFcadastrado(valor);
      url = window.location.origin + "/buscas/cnpjcpfcadastrado";
      jQuery.ajax({
        type: "POST",
        async: false,
        dataType: "json",
        url: url,
        data: { cpfcnpf: CNPJ },
        success: function (retorno) {
          if (retorno.tem == "1") {
            jQuery("#" + precampo + "_razao").val(
              retorno.fornecedor["for_razao"]
            );
            jQuery("#" + precampo + "_id").val(retorno.fornecedor["for_id"]);
            jQuery("#" + precampo + "_minimo").val(
              retorno.fornecedor["for_minimo"]
            );
          } else {
            boxAlert("CNPJ Não Cadastrado", true, "", false, 1, false);
          }
        },
      });
    } //end if.
    else {
      boxAlert("Formato de CNPJ inválido", true, "", false, 1, false);
    }
  } //end if.
  else {
  }
}

function pesquisaCPF(valor) {
  if (valor != "") {
    // testaCliente = ;
    url = window.location.origin + "/buscas/cnpjcpfcadastrado";
    jQuery.ajax({
      type: "POST",
      async: false,
      dataType: "json",
      url: url,
      data: { cpfcnpf: valor },
      success: function (retorno) {
        if (retorno.tem == "1") {
          boxAlert("CPF Já Cadastrado", true, "", false, 1, false);
          jQuery("#for_cpf").val("");
          jQuery("#for_cpf").focus();
        }
      },
    });
  }
}

function pesquisaCPFApi(obj) {
  campo = obj.id;
  valor = obj.value;
  var CPF = valor.replace(/\D/g, "");
  nascimento = "25071973";
  if (CPF != "") {
    // testaCliente = ;
    url = window.location.origin + "/buscas/cpfcolabcadastrado";
    jQuery.ajax({
      type: "POST",
      async: false,
      dataType: "json",
      url: url,
      data: { cpfcnpf: valor },
      success: function (retorno) {
        if (retorno.tem == "1") {
          boxAlert("CPF Já Cadastrado", true, "", false, 1, false);
          jQuery("#" + campo).val("");
          jQuery("#" + campo).focus();
        } else {
          token = "447FE5F3-F413-4D66-A79F-D7D8300AFF0B";
          var url =
            "https://www.sintegraws.com.br/api/v1/execute-api.php?token=" +
            token +
            "&cpf=" +
            CPF +
            "&data-nascimento=" +
            nascimento +
            "&plugin=CPF";
          jQuery.ajax({
            method: "GET",
            url: url,
            complete: function (xhr) {
              // Aqui recuperamos o JSON retornado
              response = xhr.responseJSON;

              if (response.status == "OK") {
                // Agora preenchemos os campos com os valores retornados
                //preencher os outros campos
                // ...
                // Aqui exibimos uma mensagem caso tenha ocorrido algum erro
              } else {
                alert(response.message);
              }
            },
          });
        }
      },
    });
  }
}

function pesquisacepcnpj() {
  dadoscnpj = localStorage.getItem("dadoscnpj");
  if (dadoscnpj != undefined) {
    jQuery("#for_rua").val(dadoscnpj["LOGRADOURO"]);
    jQuery("#for_cep").val(dadoscnpj["CEP"]);
    jQuery("#for_bairro").val(dadoscnpj["BAIRRO"]);
    jQuery("#for_cidade").val(dadoscnpj["MUNICIPIO"]);
    jQuery("#for_uf").val(dadoscnpj["UF"]);
  }
}

function busca_dados_material(orig, obj, url, base) {
  var datarr = new Array();
  pref = obj.substring(0, 3);
  posi = orig.id.indexOf("[") + 1;
  posf = orig.id.indexOf("]");
  pos = orig.id.substr(posi, posf - posi);
  // sufi = '';
  // if(orig.id.indexOf('__') > 0){
  //     sufi = orig.id.substr(-3);
  // }
  datarr[0] = {};
  datarr[0].valor = orig.value;
  jQuery.ajax({
    type: "POST",
    async: false,
    dataType: "json",
    url: url,
    data: { campo: datarr },
    success: function (retorno) {
      // jQuery('#'+pref+'_unidade\\['+pos+'\\]').val(retorno.mat_unidade).change();
      // jQuery('[name="'+pref+'_unidade\\['+pos+'\\]"]').selectpicker('destroy');
      // jQuery('[name="'+pref+'_unidade\\['+pos+'\\]"]').selectpicker('deselectAll');
      // jQuery('#'+pref+'_unidade\\['+pos+'\\] option[value='+retorno.mat_unidade+']').attr('selected', 'selected');
      jQuery("#" + pref + "_unidade\\[" + pos + "\\]").selectpicker(
        "val",
        retorno.mat_unidade
      );
      // jQuery('[name="'+pref+'_unidade\\['+pos+'\\]"]').selectpicker('refresh');
      jQuery("#" + pref + "_unitario\\[" + pos + "\\]").val(
        converteFloatMoeda(retorno.mat_compra / retorno.mat_quantia)
      );
      if (retorno.mpc_unitario != null) {
        jQuery("#" + pref + "_unitario\\[" + pos + "\\]").val(
          converteFloatMoeda(retorno.mpc_unitario / 1)
        );
      }
      console.log(retorno);
    },
  });
}

function buscaProdutoMarca(obj, tipo = 0) {
  valor = obj.value;
  if (valor != "") {
    ordem = obj.getAttribute("data-index");
    url = window.location.origin + "/buscas/buscaprodutomarca";
    jQuery.ajax({
      type: "POST",
      async: false,
      dataType: "json",
      url: url,
      data: { marca: valor },
      success: function (retorno) {
        if (retorno.id == -1) {
          // jQuery("#pro_id\\[" + ordem + "\\]").val("");
          // jQuery("#pro_nome\\[" + ordem + "\\]").val("");
          // jQuery("#mar_nome\\[" + ordem + "\\]").val("");
          // jQuery("#conversao\\[" + ordem + "\\]").val("");
          // jQuery("#unm_id\\[" + ordem + "\\]").selectpicker("val", -1);
          // jQuery("#und_id\\[" + ordem + "\\]").selectpicker("val", -1);
          // jQuery("#pro_id").val("");
          // jQuery("#pro_nome").val("");
          jQuery("#mar_nome").val("");
          jQuery("#enp_conversao").val("");
          jQuery("#unm_id").selectpicker("val", -1);
          // jQuery("#und_id").selectpicker("val", -1);
          // abrir cadastro de marca modal
          openModal(window.location.origin + "/EstMarca/add/modal=true");
        } else {
          compara = true;
          if (tipo == 1) {
            compara = comparaProduto(
              retorno.id,
              // jQuery("#pro_id\\[" + ordem + "\\]").val(),
              jQuery("#pro_id").val(),
              retorno.produto,
              // jQuery("#pro_nome\\[" + ordem + "\\]").val()
              jQuery("#pro_nome").val()
            );
          }
          if (compara) {
            // jQuery("#pro_id\\[" + ordem + "\\]").val(retorno.id);
            // jQuery("#pro_nome\\[" + ordem + "\\]").val(retorno.produto);
            // jQuery("#mar_nome\\[" + ordem + "\\]").val(retorno.marca);
            // jQuery("#conversao\\[" + ordem + "\\]").val(retorno.conversao);
            // jQuery("#unm_id\\[" + ordem + "\\]").selectpicker(
            //   "val",
            //   retorno.und_marca
            // );
            // jQuery("#und_id\\[" + ordem + "\\]").selectpicker(
            //   "val",
            //   retorno.und_produ
            // );
            // jQuery("#pro_id").val(retorno.id);
            // jQuery("#pro_nome").val(retorno.produto);
            jQuery("#mar_nome").val(retorno.marca);
            jQuery("#enp_conversao").val(retorno.conversao);
            jQuery("#unm_id").selectpicker("val", retorno.und_marca);
            // jQuery("#und_id").selectpicker("val", retorno.und_produ);
          }
        }
      },
    });
  }
}

function buscaSaldoProduto(obj, campo = "sap_saldo") {
  ordem = obj.getAttribute("data-index");
  prod = jQuery("#pro_id\\[" + ordem + "\\]").val();
  depo = jQuery("#dep_id").val();
  if (depo == "") {
    boxAlert("Informe o Depósito", true, "", false, 1, false);
  } else if (prod != "") {
    url = window.location.origin + "/buscas/buscasaldoproduto";
    jQuery.ajax({
      type: "POST",
      async: false,
      dataType: "json",
      url: url,
      data: { produto: prod, deposito: depo },
      success: function (retorno) {
        jQuery("#" + campo + "\\[" + ordem + "\\]").val(retorno.saldo);
      },
    });
  }
}

function comparaProduto(retornado, original, nomeret, nomepro) {
  if (original == "") {
    return true;
  } else if (retornado != original) {
    boxAlert(
      "Produto Scaneado <br>" +
        retornado +
        " - " +
        nomeret +
        "<br><br>DIFERENTE do Produto Comprado<br>" +
        original +
        " - " +
        nomepro,
      true,
      "",
      false,
      1,
      false
    );
    return false;
  } else {
    return true;
  }
}

function buscaDadosProduto(obj) {
  valor = obj.value;
  ordem = obj.getAttribute("data-index");
  url = window.location.origin + "/buscas/buscaproduto";
  jQuery.ajax({
    type: "POST",
    async: false,
    dataType: "json",
    url: url,
    data: { id: valor },
    success: function (retorno) {
      if (retorno.id == -1) {
        // jQuery('#pro_id\\[' + ordem + '\\]').val('');
        // jQuery('#pro_nome\\[' + ordem + '\\]').val('');
        if (obj.id.indexOf("[") > 0) {
          jQuery("#und_id\\[" + ordem + "\\]").selectpicker("val", -1);
        } else {
          jQuery("#und_id").selectpicker("val", -1);
        }
        // abrir cadastro de marca modal
        openModal(window.location.origin + "/EstMarca/add/modal=true");
      } else {
        // jQuery('#pro_id\\[' + ordem + '\\]').val(retorno.id);
        // jQuery('#pro_nome\\[' + ordem + '\\]').val(retorno.produto);
        if (obj.id.indexOf("[") > 0) {
          jQuery("#und_id\\[" + ordem + "\\]").selectpicker(
            "val",
            retorno.und_produ
          );
        } else {
          jQuery("#und_id").selectpicker("val", retorno.und_produ);
        }
      }
    },
  });
}

function carrega_lista_cotacao_ant(obj, url, nome) {
  bloqueiaTela();
  $fixo.html("").hide();
  var tabela = " tb_" + nome;

  param = jQuery("#empresa").val();
  param2 = jQuery("#grc_id").val();

  url = url + "?param=" + param + "&param2=" + param2;

  jQuery.ajax({
    type: "POST",
    async: true,
    dataType: "json",
    url: url,
    success: function (itens) {
      prod = "";
      linha = "";

      for (let it = 0; it < itens.length; it++) {
        const element = itens[it];

        if (element.pro_nome != prod) {
          if (prod != "") {
            linha += "</td>";
            linha += "</tr>";
            linha += "</table>";
          }

          // quero que essa linha fique fixa no topo, até que a próxima linha com essa classe atinja o topo
          linha += "<tr class='linha_produto' style='font-size:16px;'>";
          linha +=
            "<td style='text-align:left;min-width: 50% !important;width: 50% !important;'>";
          linha += element.pro_nome;
          linha += "</td>";
          linha += "<td style='width:15% !important;text-align:center'>";
          linha += element.ped_datains;
          linha += "</td>";
          linha += "<td style='width:10% !important;text-align:center'>";
          linha += element.ped_qtia;
          linha += "</td>";
          linha += "<td style='width:10% !important;text-align:center'>";
          linha += element.ped_sugestao;
          linha += "</td>";
          linha += "<td style='width:15% !important;text-align:center'>";
          linha += element.und_sigla;
          linha += "</td>";
          linha += "</tr>";
          linha += "<tr style='font-size:10px;'>";
          linha += "<td colspan='6'>";
          prod = element.pro_nome;
          linha +=
            "<table class='table table-sm table-warning table-condensed table-group-divider table-striped table-hover col-12' >";
          linha += "<thead class='col-12' >";
          linha += "<tr>";
          linha += "<th>";
          linha += "Marca";
          linha += "</th>";
          linha += "<th>";
          linha += "Apresentação";
          linha += "</th>";
          linha += "<th>";
          linha += "Fornecedor 1";
          linha += "</th>";
          linha += "<th>";
          linha += "Fornecedor 2";
          linha += "</th>";
          linha += "<th>";
          linha += "Fornecedor 3";
          linha += "</th>";
          linha += "<th>";
          linha += "Fornecedor 4";
          linha += "</th>";
          linha += "<th>";
          linha += "Fornecedor 5";
          linha += "</th>";
          linha += "<th>";
          linha += "Fornecedor 6";
          linha += "</th>";
          linha += "<th>";
          linha += "Fornecedor 7";
          linha += "</th>";
          linha += "<th>";
          linha += "Fornecedor 8";
          linha += "</th>";
          linha += "<th>";
          linha += "Fornecedor 9";
          linha += "</th>";
          linha += "<th>";
          linha += "Fornecedor 10";
          linha += "</th>";
          linha += "</tr>";
          linha += "</thead>";
        }

        linha += "<tr style='font-size:10px;'>";
        linha += "<td class='align-middle text-start' style='font-size:14px;'>";
        linha += element.mar_nome;
        linha += "</td>";
        linha += "<td class='align-middle text-start' style='font-size:14px;'>";
        linha += element.mar_apresenta;
        linha += "</td>";
        linha += "<td class='td-com-borda-90'>";
        linha += element.pro_id_1;
        linha += element.ped_id_1;
        linha += element.cot_id_1;
        linha += element.cop_id_1;
        linha += element.mar_id_1;
        linha += element.for_id_1;
        linha += element.cof_id_1;
        linha += element.cof_preco_1;
        linha += element.com_quantia_1;
        linha += element.cof_validade_1;
        linha += element.com_previsao_1;
        linha += "</td>";
        linha += "<td class='td-com-borda-90'>";
        linha += element.pro_id_2;
        linha += element.ped_id_2;
        linha += element.cot_id_2;
        linha += element.cop_id_2;
        linha += element.mar_id_2;
        linha += element.for_id_2;
        linha += element.cof_id_2;
        linha += element.cof_preco_2;
        linha += element.com_quantia_2;
        linha += element.cof_validade_2;
        linha += element.com_previsao_2;
        linha += "</td>";
        linha += "<td>";
        linha += element.pro_id_3;
        linha += element.ped_id_3;
        linha += element.cot_id_3;
        linha += element.cop_id_3;
        linha += element.mar_id_3;
        linha += element.for_id_3;
        linha += element.cof_id_3;
        linha += element.cof_preco_3;
        linha += element.com_quantia_3;
        linha += element.cof_validade_3;
        linha += element.com_previsao_3;
        linha += "</td>";
        linha += "<td>";
        linha += element.pro_id_4;
        linha += element.ped_id_4;
        linha += element.cot_id_4;
        linha += element.cop_id_4;
        linha += element.mar_id_4;
        linha += element.for_id_4;
        linha += element.cof_id_4;
        linha += element.cof_preco_4;
        linha += element.com_quantia_4;
        linha += element.cof_validade_4;
        linha += element.com_previsao_4;
        linha += "</td>";
        linha += "<td>";
        linha += element.pro_id_5;
        linha += element.ped_id_5;
        linha += element.cot_id_5;
        linha += element.cop_id_5;
        linha += element.mar_id_5;
        linha += element.for_id_5;
        linha += element.cof_id_5;
        linha += element.cof_preco_5;
        linha += element.com_quantia_5;
        linha += element.cof_validade_5;
        linha += element.com_previsao_5;
        linha += "</td>";
        linha += "<td>";
        linha += element.pro_id_6;
        linha += element.ped_id_6;
        linha += element.cot_id_6;
        linha += element.cop_id_6;
        linha += element.mar_id_6;
        linha += element.for_id_6;
        linha += element.cof_id_6;
        linha += element.cof_preco_6;
        linha += element.com_quantia_6;
        linha += element.cof_validade_6;
        linha += element.com_previsao_6;
        linha += "</td>";
        linha += "<td>";
        linha += element.pro_id_7;
        linha += element.ped_id_7;
        linha += element.cot_id_7;
        linha += element.cop_id_7;
        linha += element.mar_id_7;
        linha += element.for_id_7;
        linha += element.cof_id_7;
        linha += element.cof_preco_7;
        linha += element.com_quantia_7;
        linha += element.cof_validade_7;
        linha += element.com_previsao_7;
        linha += "</td>";
        linha += "<td>";
        linha += element.pro_id_8;
        linha += element.ped_id_8;
        linha += element.cot_id_8;
        linha += element.cop_id_8;
        linha += element.mar_id_8;
        linha += element.for_id_8;
        linha += element.cof_id_8;
        linha += element.cof_preco_8;
        linha += element.com_quantia_8;
        linha += element.cof_validade_8;
        linha += element.com_previsao_8;
        linha += "</td>";
        linha += "<td>";
        linha += element.pro_id_9;
        linha += element.ped_id_9;
        linha += element.cot_id_9;
        linha += element.cop_id_9;
        linha += element.mar_id_9;
        linha += element.for_id_9;
        linha += element.cof_id_9;
        linha += element.cof_preco_9;
        linha += element.com_quantia_9;
        linha += element.cof_validade_9;
        linha += element.com_previsao_9;
        linha += "</td>";
        linha += "<td>";
        linha += element.pro_id_10;
        linha += element.ped_id_10;
        linha += element.cot_id_10;
        linha += element.cop_id_10;
        linha += element.mar_id_10;
        linha += element.for_id_10;
        linha += element.cof_id_10;
        linha += element.cof_preco_10;
        linha += element.com_quantia_10;
        linha += element.cof_validade_10;
        linha += element.com_previsao_10;
        linha += "</td>";
        linha += "</tr>";
      }

      linha += "</td>";
      linha += "</tr>";
      linha += "</table>";

      jQuery("#itens_tabela").html(linha);
      jQuery(".selectpicker").selectpicker();
      jQuery("#divTable").scrollTop(0);
      itens_compra = [];
      jQuery("#itens_tabela_compra").html("");
      desBloqueiaTela();
    },
  });
}

function carrega_lista_cotacao_marca(obj, url, nome) {
  bloqueiaTela();
  //   $fixo.html("").hide();
  //   var tabela = " tb_" + nome;

  param = jQuery("#empresa").val();
  param2 = jQuery("#grc_id").val();

  url = url + "?param=" + param + "&param2=" + param2;

  jQuery.ajax({
    type: "POST",
    async: true,
    dataType: "json",
    url: url,
    success: function (itens) {
      prod = "";
      linha = "";

      for (let it = 0; it < itens.length; it++) {
        const element = itens[it];
        iddiv = element.pro_id;
        if (element.pro_nome != prod) {
          if (prod != "") {
            linha += "</div>";
            linha += "</div>";
            linha += "</div>";
            linha += "</div>";
            linha += "</div>";
          }
          marca = "";
          prod = element.pro_nome;
          linha += '<div class="accordion-item">';
          linha +=
            '  <h2 class="accordion-header border border-botton-1" id="heading' +
            iddiv +
            '">';
          linha +=
            '      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' +
            iddiv +
            '" aria-expanded="true" aria-controls="collapse' +
            iddiv +
            '">';
          linha +=
            '          <div class="col-4"><b>Produto</b><br>' +
            element.pro_nome +
            "</div>";
          linha +=
            '          <div class="col-2"><b>Data Solic</b><br>' +
            element.ped_datains +
            "</div>";
          linha +=
            '          <div class="col-2"><b>Quantia</b><br>' +
            element.ped_qtia +
            "</div>";
          linha +=
            '          <div class="col-2"><b>Sugestão</b><br>' +
            element.ped_sugestao +
            "</div>";
          linha +=
            '          <div class="col-2"><b>Und.</b><br>' +
            element.und_sigla +
            "</div>";
          linha += "      </button>";
          linha += "  </h2>";
          linha +=
            '  <div id="collapse' +
            iddiv +
            '" class="accordion-collapse collapse" aria-labelledby="heading' +
            iddiv +
            '" data-bs-parent="#accProdutos">';
          linha +=
            '      <div class="accordion-body p-1"  style="max-height:50vh; height:50vh; overflow-y: auto">';
          linha += '      <div class="d-block float-start col-12 p-0 ">';
          linha +=
            '          <div class="d-inline-flex float-start col-1 fw-bold">Marca:</div>';
          linha +=
            '          <div class="d-inline-flex float-start col-3 fw-bold">Fornecedor</div>';
          linha +=
            '          <div class="d-inline-flex float-start col-2 fw-bold">Validade</div>';
          linha +=
            '          <div class="d-inline-flex float-start col-2 fw-bold">Preço</div>';
          linha +=
            '          <div class="d-inline-flex float-start col-2 fw-bold">Quantia</div>';
          linha +=
            '          <div class="d-inline-flex float-start col-2 fw-bold">Prev. Entrega</div>';
          linha += "      </div>";
          linha +=
            '      <div class="d-block float-start col-12 p-0" style="max-height:45vh; overflow-y: auto">';
        }
        for (f = 1; f < 11; f++) {
          if (marca != element.mar_id) {
            if (marca != "") {
              linha += "      </div>";
              linha += "      </div>";
            }
            let classeParOuImpar = it % 2 === 0 ? "odd" : "even";
            linha +=
              '      <div class="d-block float-start col-12 p-0 border border-1 ' +
              classeParOuImpar +
              '">';
            linha +=
              '          <div class="d-block float-start col-1 fw-bold text-vertical fs-3" style="height: 50%; width:auto;">' +
              element.mar_nome +
              "<br>" +
              element.mar_apresenta +
              "</div>";
            linha += '      <div class="d-block float-start col-11 p-0">';
            marca = element.mar_id;
          }
          linha += '      <div class="d-block float-start col-12 p-0">';
          linha +=
            '          <div class="d-inline-flex float-start col-4">' +
            f +
            " " +
            element["pro_id_" + f] +
            element["mar_id_" + f] +
            element["ped_id_" + f] +
            element["cot_id_" + f] +
            element["cop_id_" + f] +
            element["for_id_" + f] +
            "</div>";
          linha +=
            '          <div class="d-inline-flex float-start col-2">' +
            element["cof_id_" + f] +
            element["cof_validade_" + f] +
            "</div>";
          linha +=
            '          <div class="d-inline-flex float-start col-2">' +
            element["cof_preco_" + f] +
            "</div>";
          linha +=
            '          <div class="d-inline-flex float-start col-2">' +
            element["com_quantia_" + f] +
            "</div>";
          linha +=
            '          <div class="d-inline-flex float-start col-2">' +
            element["com_previsao_" + f] +
            "</div>";
          linha += "</div>";
        }
      }
      linha += "</div>";
      linha += "</div>";
      linha += "</div>";
      jQuery("#accProdutos").html(linha);
      jQuery(".selectpicker").selectpicker();
      jQuery("#divTable").scrollTop(0);
      itens_compra = [];
      jQuery("#itens_tabela_compra").html("");

      desBloqueiaTela();
      const container = document.getElementById("divTable");

      document.querySelectorAll(".accordion-button").forEach((button) => {
        button.addEventListener("click", function () {
          const targetCollapse = document.querySelector(this.dataset.bsTarget);

          targetCollapse.addEventListener(
            "shown.bs.collapse",
            function () {
              const item = targetCollapse.closest(".accordion-item");

              // Calcula a posição do item em relação ao container
              const offsetTop = item.offsetTop - 60;

              // Scrolla o container para que o item fique no topo
              container.scrollTo({
                top: offsetTop,
                behavior: "smooth",
              });
            },
            { once: true }
          );
        });
      });
    },
  });
}

function carrega_lista_cotacao(obj, url, nome) {
  bloqueiaTela();
  //   $fixo.html("").hide();
  //   var tabela = " tb_" + nome;

  param = jQuery("#empresa").val();
  param2 = jQuery("#grc_id").val();

  url = url + "?param=" + param + "&param2=" + param2;

  jQuery.ajax({
    type: "POST",
    async: true,
    dataType: "json",
    url: url,
    success: function (itens) {
      prod = "";
      linha = "";

      for (let it = 0; it < itens.length; it++) {
        const element = itens[it];
        iddiv = element.pro_id;
        if (element.pro_nome != prod) {
          if (prod != "") {
            linha += "</div>";
            linha += "</div>";
            linha += "</div>";
            linha += "</div>";
            linha += "</div>";
          }
          marca = "";
          prod = element.pro_nome;
          linha += '<div class="accordion-item">';
          linha +=
            '  <h2 class="accordion-header border border-botton-1" id="heading' +
            iddiv +
            '">';
          linha +=
            '      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' +
            iddiv +
            '" aria-expanded="true" aria-controls="collapse' +
            iddiv +
            '">';
          linha +=
            '          <div class="col-2"><b>Grupo</b><br>' +
            element.grc_nome +
            "</div>";
          linha +=
            '          <div class="col-4"><b>Produto</b><br>' +
            element.pro_nome +
            "</div>";
          linha +=
            '          <div class="col-2"><b>Data Solic</b><br>' +
            element.ped_datains +
            "</div>";
          linha +=
            '          <div class="col-1"><b>Quantia</b><br>' +
            element.ped_qtia +
            "</div>";
          linha +=
            '          <div class="col-1"><b>Sugestão</b><br>' +
            element.ped_sugestao +
            "</div>";
          linha +=
            '          <div class="col-2"><b>Und.</b><br>' +
            element.und_compra +
            "</div>";
          linha += "      </button>";
          linha += "  </h2>";
          linha +=
            '  <div id="collapse' +
            iddiv +
            '" class="accordion-collapse collapse" aria-labelledby="heading' +
            iddiv +
            '" data-bs-parent="#accProdutos">';
          linha +=
            '      <div class="accordion-body p-1"  style="max-height:50vh; height:50vh; overflow-y: auto">';
          linha += '      <div class="d-block float-start col-12 p-0 ">';
          linha +=
            '          <div class="d-inline-flex float-start col-3 fw-bold"><div class="col-12 text-center">Fornecedor</div></div>';
          linha +=
            '          <div class="d-inline-flex float-start col-2 fw-bold"><div class="col-12 text-center">Marca:</div></div>';
          linha +=
            '          <div class="d-inline-flex float-start col-1 fw-bold"><div class="col-12 text-center">Validade</div></div>';
          linha +=
            '          <div class="d-inline-flex float-start col-1 fw-bold"><div class="col-12 text-center">R$ ' +
            element.und_consumo +
            "</div></div>";
          linha +=
            '          <div class="d-inline-flex float-start col-1 fw-bold"><div class="col-12 text-center">R$ ' +
            element.und_compra +
            "</div></div>";
          linha +=
            '          <div class="d-inline-flex float-start col-1 fw-bold"><div class="col-12 text-center">Quantia</div></div>';
          linha +=
            '          <div class="d-inline-flex float-start col-2 fw-bold"><div class="col-12 text-center">Prev. Entrega</div></div>';
          linha += "      </div>";
          linha +=
            '      <div class="d-block float-start col-12 p-0" style="max-height:45vh; overflow-y: auto">';
        }
        for (f = 1; f < 11; f++) {
          // if (marca != element.mar_id) {
          //   if (marca != "") {
          //     linha += "      </div>";
          //     linha += "      </div>";
          //   }
          //   let classeParOuImpar = it % 2 === 0 ? "odd" : "even";
          //   linha +=
          //     '      <div class="d-block float-start col-12 p-0 border border-1 ' +
          //     classeParOuImpar +
          //     '">';
          //   linha +=
          //     '          <div class="d-block float-start col-1 fw-bold text-vertical fs-3" style="height: 50%; width:auto;">' +
          //     element.mar_nome +
          //     "<br>" +
          //     element.mar_apresenta +
          //     "</div>";
          //   linha += '      <div class="d-block float-start col-12 p-0">';
          //   marca = element.mar_id;
          // }
          linha += '      <div class="d-block float-start col-12 p-0">';
          linha +=
            '          <div class="d-inline-flex float-start col-3">' +
            f +
            " " +
            element["pro_id_" + f] +
            element["ped_id_" + f] +
            element["cot_id_" + f] +
            element["cop_id_" + f] +
            element["for_id_" + f] +
            "</div>";
          linha +=
            '          <div class="d-inline-flex float-start col-2">' +
            element["mar_id_" + f] +
            "</div>";
          linha +=
            '          <div class="d-inline-flex float-start col-1 me-3">' +
            element["cof_id_" + f] +
            element["cof_validade_" + f] +
            "</div>";
          linha +=
            '          <div class="d-inline-flex float-start col-1 me-3">' +
            element["cof_preco_" + f] +
            "</div>";
          linha +=
            '          <div class="d-inline-flex float-start col-1 me-3">' +
            element["cof_precoundcompra_" + f] +
            "</div>";
          linha +=
            '          <div class="d-inline-flex float-start col-1 me-3">' +
            element["com_quantia_" + f] +
            "</div>";
          linha +=
            '          <div class="d-inline-flex float-start col-2">' +
            element["com_previsao_" + f] +
            "</div>";
          if (
            element["cof_observacao_" + f] != "" &&
            element["cof_observacao_" + f] != null
          ) {
            linha +=
              '          <div class="d-inline-flex float-start col-12 px-5 mb-3">' +
              "Obs.: " +
              element["cof_observacao_" + f] +
              "</div>";
          }
          linha += "</div>";
        }
      }
      linha += "</div>";
      linha += "</div>";
      linha += "</div>";
      jQuery("#accProdutos").html(linha);
      jQuery(".selectpicker").selectpicker();
      jQuery("#divTable").scrollTop(0);
      itens_compra = [];
      jQuery("#itens_tabela_compra").html("");

      desBloqueiaTela();
      const container = document.getElementById("divTable");

      document.querySelectorAll(".accordion-button").forEach((button) => {
        button.addEventListener("click", function () {
          const targetCollapse = document.querySelector(this.dataset.bsTarget);

          targetCollapse.addEventListener(
            "shown.bs.collapse",
            function () {
              const item = targetCollapse.closest(".accordion-item");

              // Calcula a posição do item em relação ao container
              const offsetTop = item.offsetTop - 60;

              // Scrolla o container para que o item fique no topo
              container.scrollTo({
                top: offsetTop,
                behavior: "smooth",
              });
            },
            { once: true }
          );
        });
      });
    },
  });
}
