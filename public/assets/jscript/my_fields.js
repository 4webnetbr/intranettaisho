jQuery(document).ready(function () {
  carregamentos_iniciais();
});

function carregamentos_iniciais() {
  var start = moment().subtract(29, "days");
  var end = moment();

  function setValue($el, start, end) {
    $el.val(start.format("DD/MM/YYYY") + " - " + end.format("DD/MM/YYYY"));
  }

  jQuery(".daterange").daterangepicker(
    {
      alwaysShowCalendars: true,
      startDate: start,
      endDate: end,
      autoUpdateInput: false, // deixamos false para poder limpar no "Não Considerar"
      ranges: {
        Hoje: [moment(), moment()],
        Ontem: [moment().subtract(1, "days"), moment().subtract(1, "days")],
        "Não Considerar": [moment().subtract(1, "year"), moment()], // apenas gatilho
        "Últimos 7 dias": [moment().subtract(6, "days"), moment()],
        "Últimos 30 dias": [moment().subtract(29, "days"), moment()],
        "Mês atual": [moment().startOf("month"), moment().endOf("month")],
        "Mês anterior": [
          moment().subtract(1, "month").startOf("month"),
          moment().subtract(1, "month").endOf("month"),
        ],
        "Últimos 12 meses": [moment().subtract(12, "months"), moment()],
        "Últimos 18 meses": [moment().subtract(18, "months"), moment()],
      },
      locale: {
        format: "DD/MM/YYYY",
        customRangeLabel: "Período",
        applyLabel: "Aplicar",
        cancelLabel: "Limpar",
        daysOfWeek: ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sab"],
        monthNames: [
          "Janeiro",
          "Fevereiro",
          "Março",
          "Abril",
          "Maio",
          "Junho",
          "Julho",
          "Agosto",
          "Setembro",
          "Outubro",
          "Novembro",
          "Dezembro",
        ],
      },
    },
    function (start, end, label) {
      const $input = jQuery(this.element);

      if (label === "Não Considerar") {
        $input.val("Não Considerar").trigger("change"); // range nulo
        return;
      }

      setValue($input, start, end);
      $input.trigger("change");
    }
  );

  // valor inicial (últimos 30 dias)
  // jQuery("#aberto.daterange").val("Não Considerar");
  jQuery(".daterange")
    .not("#aberto")
    .each(function () {
      setValue(jQuery(this), start, end);
      jQuery(this).trigger("change");
    });

  var temNumero = /[0-9]/;
  var temMaiusc = /[A-Z]/;
  var temMinusc = /[a-z]/;
  var temSimbol = /[!@#$%*()_+^&}{:;?.]/;
  var temDuplic = /(.)\1/;
  var msg1 = " Pelo menos 6 Digitos";
  var msg2 = " 1 número";
  var msg3 = " 1 Letra MAIÚSCULA";
  var msg4 = " 1 Letra minúscula";
  var msg5 = " 1 Símbolo";
  var msg6 = " Sem caracteres repetidos";
  var linn = '<span class="text-danger">';
  var lino = '<span class="text-success">';
  var tem = '<i class="fa-solid fa-check"></i>';
  var nao = '<i class="fa-solid fa-xmark"></i>';

  /**
   * Validação de Senha Forte
   * Executa validações para Senha Forte
   * Document Ready my_fields
   */
  jQuery(".password").on("keyup", function (e) {
    var passwordsInfo = jQuery("#pass-info");
    const rect = this.getBoundingClientRect();
    const height = jQuery(this).outerHeight();
    const $infoBox = jQuery("#pass-info").appendTo("body");

    val = this.value;
    var valid = true;
    var text = "";
    if (val.length > 0) {
      $infoBox.css({
        top: rect.top + height + "px",
        left: rect.left + "px",
        position: "fixed",
      });
      passwordsInfo.show();
      if (val.length >= 6) {
        text += lino + tem + msg1 + "</span><br>";
      } else {
        valid = false;
        text += linn + nao + msg1 + "</span><br>";
      }
      if (temNumero.test(val)) {
        text += lino + tem + msg2 + "</span><br>";
      } else {
        valid = false;
        text += linn + nao + msg2 + "</span><br>";
      }
      if (temMaiusc.test(val)) {
        text += lino + tem + msg3 + "</span><br>";
      } else {
        valid = false;
        text += linn + nao + msg3 + "</span><br>";
      }
      if (temMinusc.test(val)) {
        text += lino + tem + msg4 + "</span><br>";
      } else {
        valid = false;
        text += linn + nao + msg4 + "</span><br>";
      }
      if (temSimbol.test(val)) {
        text += lino + tem + msg5 + "</span><br>";
      } else {
        valid = false;
        text += linn + nao + msg5 + "</span><br>";
      }
      if (!temDuplic.test(val)) {
        text += lino + tem + msg6 + "</span><br>";
      } else {
        valid = false;
        text += linn + nao + msg6 + "</span><br>";
      }
      if (!jQuery(this).hasClass("is-invalid")) {
        jQuery(this).addClass("is-invalid");
      }
      if (valid) {
        text = lino + tem + " SENHA SEGURA</span><br>";
        passwordsInfo.removeClass("border-danger");
        passwordsInfo.addClass("border-success");
      } else {
        passwordsInfo.removeClass("border-success");
        passwordsInfo.addClass("border-danger");
      }
      passwordsInfo.html(text);
    } else {
      passwordsInfo.hide();
      passwordsInfo.removeClass("border-danger");
      jQuery(this).removeClass("is-invalid");
    }
  });

  /**
   * Se existir campos sortable
   * Document Ready my_fields
   */
  // jQuery('.sort').sortable();

  /**
   * .editor summernote()
   * Se existir um campo Editor, aplica o summernote do Bootstrap
   * Document Ready my_fields
   */
  if (jQuery("input, textarea").hasClass("editor")) {
    jQuery(".editor").summernote({
      height: 200,
      width: "80%",
      lang: "pt-BR",
      callbacks: {
        onImageUpload: function (files, editor, welEditable) {
          sendFile(files[0], this);
        },
      },
    });
  }
  /**
   * sendFile
   * Função utilizada pelo Summernote para envio de arquivos
   * Document Ready my_fields
   */
  function sendFile(file, el) {
    data = new FormData();
    data.append("file", file);
    jQuery.ajax({
      data: data,
      type: "POST",
      url: "/Util/upload",
      cache: false,
      contentType: false,
      processData: false,
      success: function (url) {
        jQuery(el).summernote("editor.insertImage", url);
      },
    });
  }

  /**
   * Clique no Campo Editor
   * Document Ready my_fields
   */
  jQuery(".note-editor .dropdown-toggle").on("click", (e) => {
    if (jQuery(e.currentTarget).attr("aria-expanded")) {
      jQuery(e.currentTarget).dropdown("toggle");
      jQuery(e.currentTarget.nextElementSibling).toggleClass("show");
    }
  });

  /**
   * Clique no Menu
   * Mostra ou oculta elemento pelo clique
   * Document Ready my_fields
   */
  jQuery(".dropdown-menu").on("click", (e) => {
    jQuery(e.currentTarget).toggleClass("show");
  });

  /**
   * Show Password
   * Mostra ou oculta a Senha
   * Document Ready my_fields
   */
  jQuery(".show_password").hover(function (e) {
    e.preventDefault();
    field = this.getAttribute("data-field");
    alvo = document.getElementById(field);
    if (jQuery(alvo).attr("type") == "password") {
      jQuery(alvo).attr("type", "text");
      jQuery("#ada_" + fields).attr("class", "fa fa-eye");
      // jQuery('#ada_'+ fields).removeClass('bi bi-eye-slash-fill');
      // jQuery('#ada_'+ fields).addClass('bi bi-eye');
    } else {
      jQuery(alvo).attr("type", "password");
      jQuery("#ada_" + fields).attr("class", "fa fa-eye-slash");
      // jQuery('#ada_'+ fields).removeClass('bi bi-eye');
      // jQuery('#ada_'+ fields).addClass('bi bi-eye-slash-fill');
    }
  });

  /**
   * Clique upnumber
   * Incrementa o campo number
   * Document Ready my_fields
   */
  jQuery(document).on("click", ".up-num", (event) => {
    event.preventDefault();
    id = event.currentTarget.getAttribute("data-refer");
    inpnumb = document.getElementById(id);
    if (!inpnumb.disabled) {
      step = inpnumb.step ? parseInt(inpnumb.step) : 1;
      maximo = inpnumb.max ? parseInt(inpnumb.max) : 100;
      valororig = inpnumb.value ? parseInt(inpnumb.value) : 0;
      if (valororig < maximo) {
        valor = valororig + step;
        jQuery(inpnumb).val(valor);
      }
      event.stopImmediatePropagation();
      jQuery(inpnumb).trigger("change");
    }
  });

  /**
   * Clique downnumber
   * Decrementa o campo number
   * Document Ready my_fields
   */
  jQuery(document).on("click", ".down-num", (event) => {
    event.preventDefault();
    id = event.currentTarget.getAttribute("data-refer");
    inpnumb = document.getElementById(id);
    if (!inpnumb.disabled) {
      step = inpnumb.step ? parseInt(inpnumb.step) : 1;
      minimo = inpnumb.min ? parseInt(inpnumb.min) : 0;
      valororig = inpnumb.value ? parseInt(inpnumb.value) : 0;
      if (valororig > minimo) {
        valor = valororig - step;
        jQuery(inpnumb).val(valor);
      }
      event.stopImmediatePropagation();
      jQuery(inpnumb).trigger("change");
    }
  });

  /**
   * Clique CheckBox
   * Coloca ou tira a classe Checked no checkbox
   * Document Ready my_fields
   */
  jQuery('input[type="checkbox"]').click(function () {
    var id = jQuery(this).attr("id");
    var opcao = id.substr(0, id.indexOf("["));
    if (opcao.substr(0, 3) != "pit") {
      return;
    }
    var checked = jQuery(this).prop("checked");
    mod = id.match(/[0-9]+/g)[0];
    cla = id.match(/[0-9]+/g)[1];
    if (cla == 0) {
      // todas as classes
      if (opcao == "pit_all") {
        jQuery("input[id^='pit_consulta[" + mod + "]']").prop(
          "checked",
          checked
        );
        jQuery("input[id^='pit_adicao[" + mod + "]']").prop("checked", checked);
        jQuery("input[id^='pit_edicao[" + mod + "]']").prop("checked", checked);
        jQuery("input[id^='pit_exclusao[" + mod + "]']").prop(
          "checked",
          checked
        );
        jQuery("input[id^='pit_all[" + mod + "]']").prop("checked", checked);
      } else {
        jQuery("input[id^='" + opcao + "[" + mod + "]']").prop(
          "checked",
          checked
        );
        if (
          opcao == "pit_consulta" &&
          jQuery("input[id^='pit_consulta[" + mod + "]']").prop("checked") ==
            false
        ) {
          jQuery("input[id^='pit_adicao[" + mod + "]']").prop(
            "checked",
            checked
          );
          jQuery("input[id^='pit_edicao[" + mod + "]']").prop(
            "checked",
            checked
          );
          jQuery("input[id^='pit_exclusao[" + mod + "]']").prop(
            "checked",
            checked
          );
          jQuery("input[id^='pit_all[" + mod + "]']").prop("checked", checked);
        } else {
          if (checked == true) {
            jQuery("input[id^='pit_consulta[" + mod + "]']").prop(
              "checked",
              checked
            );
          }
        }
      }
    } else {
      var opcao = id.substr(0, id.indexOf("["));
      if (opcao == "pit_all") {
        jQuery("input[id^='pit_consulta[" + mod + "][" + cla + "]']").prop(
          "checked",
          checked
        );
        jQuery("input[id^='pit_adicao[" + mod + "][" + cla + "]']").prop(
          "checked",
          checked
        );
        jQuery("input[id^='pit_edicao[" + mod + "][" + cla + "]']").prop(
          "checked",
          checked
        );
        jQuery("input[id^='pit_exclusao[" + mod + "][" + cla + "]']").prop(
          "checked",
          checked
        );
      } else {
        jQuery("input[id^='pit_all[" + mod + "][" + cla + "]']").prop(
          "checked",
          checked
        );
        if (opcao == "pit_consulta" && checked == false) {
          jQuery("input[id^='pit_adicao[" + mod + "][" + cla + "]']").prop(
            "checked",
            checked
          );
          jQuery("input[id^='pit_edicao[" + mod + "][" + cla + "]']").prop(
            "checked",
            checked
          );
          jQuery("input[id^='pit_exclusao[" + mod + "][" + cla + "]']").prop(
            "checked",
            checked
          );
          jQuery("input[id^='pit_all[" + mod + "][" + cla + "]']").prop(
            "checked",
            checked
          );
        } else {
          if (checked == true) {
            jQuery("input[id^='pit_consulta[" + mod + "][" + cla + "]']").prop(
              "checked",
              checked
            );
          }
        }
        if (
          jQuery("input[id^='pit_adicao[" + mod + "][" + cla + "]']").prop(
            "checked"
          ) == false ||
          jQuery("input[id^='pit_edicao[" + mod + "][" + cla + "]']").prop(
            "checked"
          ) == false ||
          jQuery("input[id^='pit_exclusao[" + mod + "][" + cla + "]']").prop(
            "checked"
          ) == false ||
          jQuery("input[id^='pit_consulta[" + mod + "][" + cla + "]']").prop(
            "checked" == false
          )
        ) {
          jQuery("input[id^='pit_all[" + mod + "][" + cla + "]']").prop(
            "checked",
            false
          );
        }
      }
      if (checked == false) {
        jQuery("input[id^='" + opcao + "[" + mod + "][0]']").prop(
          "checked",
          checked
        );
      }
    }
  });

  /**
   * Clique 2opcoes
   * Coloca ou tira a classe Checked no checkbox
   * Document Ready my_fields
   */
  jQuery(document).on("click", ".duasOpcoes", function () {
    obj = this;
    const $obj = jQuery(obj);
    const name = $obj.attr("name");

    jQuery(`input[name="${name}"]`).each(function () {
      if (this !== obj) {
        console.log("Outro radio:", this);
        console.log("ID:", this.id, "Valor:", this.value);
        jQuery(this).closest(".form-check").removeClass("d-none");
        jQuery(this).prop("checked", true);
      } else {
        jQuery(this).closest(".form-check").addClass("d-none");
        jQuery(this).prop("checked", false);
      }
    });
  });

  // obj = this;
  // if (this.localName == 'div') {
  //     obj = this.children[0].children[0];
  // } else if (this.localName == 'label') {
  //     objfor = this.getAttribute('for');
  //     objfor = objfor.replace('[', '\\[');
  //     objfor = objfor.replace(']', '\\]');
  //     obj = jQuery('#' + objfor)[0];
  // }
  // obj.setAttribute('data-alter', true);

  // ordem = obj.getAttribute('data-index');
  // outro = 0;
  // if (ordem == 0) {
  //     outro = 1;
  // }
  // var radio = obj.id.substr(0, obj.id.indexOf('['));
  // obj.checked == false;
  // // jQuery(obj).parents().eq(1).addClass('d-none');
  // // jQuery('#' + radio + '\\[' + outro + '\\]').parents().eq(1).removeClass('d-none');
  // jQuery(obj).closest('.form-check').addClass('d-none');
  // jQuery('#' + radio + '\\[' + outro + '\\]').closest('.form-check').removeClass('d-none');
  // jQuery('#' + radio + '\\[' + outro + '\\]').prop("checked", true);
  // });;

  /**
   * Campo Select Dual
   * Se existir um campo Form dual, aplica o DualListBox do Bootstrap
   * Document Ready my_fields
   */
  if (jQuery(".form-dual")[0]) {
    jQuery(".form-dual").bootstrapDualListbox({});
  }

  /**
   * Campo Icone
   * Se existir um campo Icone, aplica o inconPicker
   * Document Ready my_fields
   */
  if (jQuery(".icone")[0]) {
    jQuery(".icone").iconpicker();
  }

  /**
   * Disable Option Select
   * desabilita a opção do Select, se o valor for -1
   * Document Ready my_fields
   */
  if (jQuery("select")[0]) {
    jQuery("select option").each(function () {
      var value = this.value;
      if (value == "-1") {
        jQuery(this).prop("disabled", "true");
      }
    });
  }

  /**
   * Campo Selpic
   * Se existir um campo selpic, aplica a Select Picker
   * Document Ready my_fields
   */
  // if (jQuery(".selbusca")[0]){
  jQuery("body").on("keydown", ".selbusca", function (event) {
    // jQuery(".selbusca").on('keydown', function () {
    elemen = jQuery(this)[0].children[0];
    if (elemen.tagName == "SELECT") {
      jQuery(elemen).empty();
      jQuery(elemen).selectpicker("refresh");
      busca = elemen.getAttribute("data-busca");
      jQuery(elemen).selectpicker({
        searchAutofocus: true,
        source: {
          data: function (callback) {
            jQuery
              .ajax({
                method: "POST",
                url: busca,
                dataType: "json",
              })
              .then((response) => callback(response.data));
          },
          search: function (callback, page, searchTerm) {
            let data = { page, busca: searchTerm };
            jQuery
              .ajax({
                method: "POST",
                url: busca,
                data,
                dataType: "json",
                success: function (retorno) {
                  jQuery(elemen).empty();
                  jQuery.each(retorno, function (i, item) {
                    jQuery(elemen).append(
                      jQuery("<option>", {
                        value: item.id,
                        text: item.text,
                      })
                    );
                  });
                },
              })
              .then((response) => callback(response.data));
          },
        },
      });
    }
  });

  /**
   * Campo Select Dependente
   * Se existir um campo dependente, adiciona o onchange no elemento pai
   * Document Ready my_fields
   */
  jQuery(".dependente").each(function () {
    elemen = jQuery(this)[0];
    if (elemen.tagName == "SELECT") {
      busca = elemen.getAttribute("data-busca");
      funcao_busca =
        "busca_dependente(this,'" +
        elemen.name +
        "','" +
        busca +
        "','" +
        elemen.value +
        "')";
      pai = elemen.getAttribute("data-pai");
      _chan_ant = jQuery('[name="' + pai + '"]').attr("onchange");
      if (
        _chan_ant == undefined ||
        _chan_ant.substr(0, 16) != "busca_dependente"
      ) {
        if (_chan_ant != "" && _chan_ant != undefined) {
          jQuery('[name="' + pai + '"]').attr(
            "onchange",
            _chan_ant + ";" + funcao_busca
          );
        } else {
          jQuery('[name="' + pai + '"]').attr("onchange", funcao_busca);
        }
        jQuery('[name="' + pai + '"]').trigger("change");
      }
    }
  });

  /**
   * Campo Select Desabilitado
   * Desabilita as opções com valor vazio do Select, se o Select for obrigatório
   * Document Ready my_fields
   */
  jQuery("select:required option[value='']").attr("disabled", "disabled");
  /**
   * Contador de caracteres digitados
   * Mostra quantos caracteres já foram digitados e qual o total de Caracteres aceitos
   *
   */
  jQuery("input:text, textarea").on("keyup", function () {
    var id = jQuery(this)[0].id;
    id = id.replace("[", "\\[");
    id = id.replace("]", "\\]");
    var tam = jQuery(this)[0].maxLength;
    var dig = jQuery(this)[0].value.length;
    tdiv = dig + "/" + tam;
    jQuery("#dc-" + id).removeClass("acabou");
    jQuery("#dc-" + id).html(tdiv);
    if (tam == dig) {
      jQuery("#dc-" + id).addClass("acabou");
    }
    console.log("Tamanho " + tam);
    console.log("Digitado " + dig);
  });

  /**
   * Limpa o contador de Caracteres
   * na saída do input limpa o contador e oculta
   *
   */
  jQuery("input:text, textarea").on("blur", function () {
    var id = jQuery(this)[0].id;
    id = id.replace("[", "\\[");
    id = id.replace("]", "\\]");
    jQuery("#dc-" + id).html("");
  });

  jQuery(".nav-tabs > li > button").on("shown.bs.tab", function (e) {
    jQuery(".tab-pane :input:first").focus();
    jQuery("#" + e.target.id)
      .find("input:first")
      .focus();
  });
}

function oculta_passinfo() {
  jQuery("#pass-info").hide();
}

/**
 * buscar
 * Cria a caixa de listagem, com os resultados da busca do campo "selbusca"
 * @param {string} url - URL de pesquisa
 * @param {object} obj - Campo de Busca
 * @param {string} lista - Lista de Opções do Objeto
 */
function buscar(url, obj, lista) {
  var busca = jQuery(obj).val();
  if (busca.length >= 3) {
    jQuery("#dd_" + lista).empty();
    jQuery("#dd_" + lista).append(
      "<li><h6 class='dropdown-header disabled'>Buscando...</h6></li>"
    );
    jQuery("#dd_" + lista).css({
      position: "absolute",
      inset: "0px auto auto 0px",
      margin: "0px",
      transform: "translate(0px, 40px)",
    });
    jQuery("#dd_" + lista).show("slow");
    jQuery.ajax({
      type: "POST",
      async: true,
      dataType: "json",
      url: url,
      data: { busca: busca },
      success: function (retorno) {
        jQuery("#dd_" + lista).empty();
        jQuery.each(retorno, function (i, item) {
          jQuery("#dd_" + lista).append(
            "<li><a class='dropdown-item' onclick='seleciona_item(\"" +
              item.id +
              '","' +
              item.text +
              '","' +
              lista +
              "\")'>" +
              item.text +
              "</a></li>"
          );
        });
      },
    });
  } else {
    jQuery("#dd_" + lista).empty();
  }
}

/**
 * seleciona_item
 * trata a seleção de um ítem de um campo do tipo "selbusca"
 * @param {string} id - id do item selecionado
 * @param {string} texto - Texto do item selecionado
 * @param {object} obj - Campo de Busca
 */
function seleciona_item(id, texto, obj) {
  jQuery("#dd_" + obj).css({
    position: "",
    inset: "",
    margin: "",
    transform: "",
  });
  jQuery("#dd_" + obj).hide("slow");
  jQuery("#bus_" + obj).val(texto);
  jQuery("#" + obj).val(id);
  jQuery("#" + obj).trigger("change");
}

/**
 * exclui_campo
 * Exclui um campo, ou uma lista de campos, para adição de ítens
 * @param {string} objdest - nome da div de destino
 * @param {object} obj - Campo que deverá ser excluído
 */

function exclui_campo(objdest, obj) {
  indice = parseInt(obj.getAttribute("data-index"));
  jQuery(obj).parents().eq(2).remove();
  jQuery("#form1").attr("data-alter", true);
  acerta_botoes_rep(objdest);
}

/**
 * repete_campo_velho
 * repete um campo, ou uma lista de campos, para adição de ítens
 * @param {string} objdest - nome da div de destino
 * @param {object} obj - Campo que deverá ser repetido
 */
function repete_campo_velho(objdest, obj) {
  var objrep = obj.parentElement;
  var objpre = objdest.substr(0, 3);

  index = jQuery(".rep_" + objdest).length;
  indat = jQuery(".rep_" + objdest).length - 1;

  var $template = jQuery("*[data-" + objdest + '-index="0"]');
  var $pai = $template[0].parentElement;
  $clone = $template
    .clone()
    .attr("data-" + objdest + "-index", index)
    .appendTo($pai);

  jQuery("*[data-" + objdest + '-index="' + index + '"]').removeClass("d-none");
  // Update the name attributes
  jQuery("*[data-" + objdest + '-index="' + index + '"]').each(function (i, t) {
    var primeiro = true;
    jQuery(this)
      .find('[id*="__0"]')
      .each(function () {
        if (!jQuery(this).hasClass("form-check-input")) {
          jQuery(this).val("");
        }

        $nome = jQuery(this)[0].name;
        if ($nome == undefined || $nome == "") {
          $nome = jQuery(this).attr("id");
        }
        if ($nome != undefined) {
          pos = $nome.indexOf("__");
          ini = $nome.substr(0, $nome.indexOf("__"));
          fim = $nome.substr(pos + 3);
          // fim = $nome.substr(pos+String(index).length+2);
          jQuery(this).attr("name", ini + "__" + index + fim);
          jQuery(this).attr("id", ini + "__" + index + fim);
        }
        // var tipo = jQuery('#'+ini+'__'+index+fim).attr('type');
        var tipo = jQuery(this)[0].type;
        if (primeiro && tipo != "hidden" && tipo != undefined) {
          jQuery("#" + ini + "__" + index + fim).focus();
          jQuery("#" + ini + "__" + index + fim).trigger("click");
          primeiro = false;
        }

        // Atualiza o evento onchange
        // Ocorre quando o campo é alterado
        var texto = jQuery(this).attr("onchange");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + index);
            jQuery(this).attr("onchange", novotexto);
          }
        }
        // Atualiza a tag for (refere a label)
        var texto = jQuery(this).attr("for");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + index);
            jQuery(this).attr("for", novotexto);
          }
        }
        // Atualiza o evento onfocus
        // Ocorre quando o campo recebe o foco
        var texto = jQuery(this).attr("onfocus");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + index);
            jQuery(this).attr("onfocus", novotexto);
          }
        }
        // Atualiza o evento onkeyup
        // Ocorre quando a Tecla pressionada foi solta
        // Ultimo evento disparado no pressionamento de uma tecla
        var texto = jQuery(this).attr("onkeyup");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + index);
            jQuery(this).attr("onkeyup", novotexto);
          }
        }
        // Atualiza o evento onkeydown
        // Ocorre no momento que uma Tecla é pressionada
        // Primeiro evento disparado no pressionamento de uma tecla
        var texto = jQuery(this).attr("onkeydown");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + index);
            jQuery(this).attr("onkeydown", novotexto);
          }
        }
        // Atualiza o evento onkeypress
        // Ocorre depos do Pressionamento de uma Tecla
        // Segundo evento disparado no pressionamento de uma tecla
        var texto = jQuery(this).attr("onkeypress");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + index);
            jQuery(this).attr("onkeypress", novotexto);
          }
        }
        // Atualiza o evento onblur
        // Ocorre quando o campo perde o foco
        var texto = jQuery(this).attr("onblur");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + index);
            jQuery(this).attr("onblur", novotexto);
          }
        }
        // Atualiza o atributo data-retorno
        var texto = jQuery(this).attr("data-retorno");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + index);
            jQuery(this).attr("data-retorno", novotexto);
          }
        }
        // Atualiza o atributo data-refer
        var texto = jQuery(this).attr("data-refer");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + index);
            jQuery(this).attr("data-refer", novotexto);
          }
        }
        // Atualiza o atributo data-index
        var texto = jQuery(this).attr("data-index");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > -1) {
            var novotexto = texto.replace(/__0/g, "__" + index);
            jQuery(this).attr("data-index", novotexto);
          }
        }
      });
  });
  acerta_botoes_rep();
  return;
}

/**
 * addCampo
 * add um campo, ou uma lista de campos, para adição de ítens
 * @param {string} url - url de criação dos campos
 * @param {string} objdest - nome da div de destino
 * @param {object} obj - Campo que deverá ser repetido
 */
function addCampo(url, objdest, obj) {
  atual = parseInt(obj.getAttribute("data-index"));
  secao = jQuery(obj).parents().eq(5)[0].id;
  proximo = atual + 1;
  jQuery(".bt-exclui").each(function (i) {
    if (parseInt(this.getAttribute("data-index")) > proximo) {
      proximo = parseInt(this.getAttribute("data-index"));
    }
  });
  let data = { ind: proximo };
  jQuery.ajax({
    type: "POST",
    async: true,
    dataType: "json",
    url: url + "/" + proximo,
    success: function (retorno) {
      text =
        "<div class='row tableDiv " +
        (proximo % 2 === 0 ? "odd" : "even") +
        " table2 table-" +
        objdest +
        "' width='100 % ' data-index=" +
        proximo +
        " >";
      text += "<div class='col-11'>";
      // text = '<table class="table2 table-sm" data-index="' + proximo + '"><tbody><tr>';
      indice = 0;
      for (const ind in retorno) {
        if (ind < retorno.length - 2) {
          quebra = retorno[ind].indexOf("quebralinha");
          oculto = retorno[ind].indexOf("hidden");
          text += retorno[ind];
        }
      }
      text += "</div>";
      text += "<div class='col-1 d-initial h-auto p-0'>";
      text += "<div class='col-9 d-block float-start text-center p-0'>";
      text += retorno[retorno.length - 2];
      text += retorno[retorno.length - 1];
      text += "</div>";
      text += "<div class='col-3 d-block float-end text-end'>";
      text +=
        "<button name='bt_up[" +
        proximo +
        "]' type='button' id='bt_up[" +
        proximo +
        "]' class='btn btn-outline-info btn-sm bt-up mt-0 float-end' onclick='sobe_desce_item(this,\"sobe\",\"" +
        objdest +
        "\")' title='Acima' data-index='" +
        proximo +
        "'><i class='fa fa-arrow-up' aria-hidden='true'></i></button>";
      text +=
        "<button name='bt_down[" +
        proximo +
        "]' type='button' id='bt_down[" +
        proximo +
        "]' class='btn btn-outline-info btn-sm bt-down mt-0 float-end' onclick='sobe_desce_item(this,\"desce\",\"" +
        objdest +
        "\")' title='Abaixo' data-index='" +
        proximo +
        "'><i class='fa fa-arrow-down' aria-hidden='true'></i></button>";
      text += "</div>";
      text += "</div>";
      text += "</div>";
      jQuery("#rep_" + objdest).append(text);
      jQuery("select").selectpicker();
      carregamentos_iniciais();
      acerta_botoes_rep(objdest);
    },
  });
}

/**
 * repete_campo
 * repete um campo, ou uma lista de campos, para adição de ítens
 * @param {string} objdest - nome da div de destino
 * @param {object} obj - Campo que deverá ser repetido
 */
function repete_campo(objdest, obj) {
  var objrep = obj.parentElement;
  var objpre = objdest.substr(0, 3);

  atual = parseInt(obj.getAttribute("data-index"));

  proximo = atual + 1;
  jQuery(".bt-exclui").each(function (i) {
    if (parseInt(this.getAttribute("data-index")) > proximo) {
      proximo = parseInt(this.getAttribute("data-index"));
    }
  });

  var $template = jQuery("*[data-" + objdest + '-index="0"]');
  var $pai = $template[0].parentElement;
  $clone = $template
    .clone()
    .attr("data-" + objdest + "-index", proximo)
    .appendTo($pai);

  jQuery("*[data-" + objdest + '-index="' + proximo + '"]').removeClass(
    "d-none"
  );
  // Update the name attributes
  jQuery("*[data-" + objdest + '-index="' + proximo + '"]').each(function (
    i,
    t
  ) {
    var primeiro = true;
    jQuery(this)
      .find('[id*="[' + atual + ']"]')
      .each(function () {
        if (!jQuery(this).hasClass("form-check-input")) {
          jQuery(this).val("");
        }

        $nome = jQuery(this)[0].name;
        if ($nome == undefined || $nome == "") {
          $nome = jQuery(this).attr("id");
        }
        if ($nome != undefined) {
          pos = $nome.indexOf("__");
          ini = $nome.substr(0, $nome.indexOf("__"));
          fim = $nome.substr(pos + 3);
          jQuery(this).attr("name", ini + "__" + proximo + fim);
          jQuery(this).attr("id", ini + "__" + proximo + fim);
        }
        var tipo = jQuery(this)[0].type;
        if (primeiro && tipo != "hidden" && tipo != undefined) {
          jQuery("#" + ini + "__" + proximo + fim).focus();
          jQuery("#" + ini + "__" + proximo + fim).trigger("click");
          primeiro = false;
        }

        // Atualiza o evento onchange
        // Ocorre quando o campo é alterado
        var texto = jQuery(this).attr("onchange");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + proximo);
            jQuery(this).attr("onchange", novotexto);
          }
        }
        // Atualiza a tag for (refere a label)
        var texto = jQuery(this).attr("for");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + proximo);
            jQuery(this).attr("for", novotexto);
          }
        }
        // Atualiza o evento onfocus
        // Ocorre quando o campo recebe o foco
        var texto = jQuery(this).attr("onfocus");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + proximo);
            jQuery(this).attr("onfocus", novotexto);
          }
        }
        // Atualiza o evento onkeyup
        // Ocorre quando a Tecla pressionada foi solta
        // Ultimo evento disparado no pressionamento de uma tecla
        var texto = jQuery(this).attr("onkeyup");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + index);
            jQuery(this).attr("onkeyup", novotexto);
          }
        }
        // Atualiza o evento onkeydown
        // Ocorre no momento que uma Tecla é pressionada
        // Primeiro evento disparado no pressionamento de uma tecla
        var texto = jQuery(this).attr("onkeydown");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + proximo);
            jQuery(this).attr("onkeydown", novotexto);
          }
        }
        // Atualiza o evento onkeypress
        // Ocorre depos do Pressionamento de uma Tecla
        // Segundo evento disparado no pressionamento de uma tecla
        var texto = jQuery(this).attr("onkeypress");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + proximo);
            jQuery(this).attr("onkeypress", novotexto);
          }
        }
        // Atualiza o evento onblur
        // Ocorre quando o campo perde o foco
        var texto = jQuery(this).attr("onblur");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + proximo);
            jQuery(this).attr("onblur", novotexto);
          }
        }
        // Atualiza o atributo data-retorno
        var texto = jQuery(this).attr("data-retorno");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + proximo);
            jQuery(this).attr("data-retorno", novotexto);
          }
        }
        // Atualiza o atributo data-refer
        var texto = jQuery(this).attr("data-refer");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > 0) {
            var novotexto = texto.replace(/__0/g, "__" + proximo);
            jQuery(this).attr("data-refer", novotexto);
          }
        }
        // Atualiza o atributo data-index
        var texto = jQuery(this).attr("data-index");
        if (texto != undefined) {
          var ocor = (texto.match(/__0/g) || []).length;
          if (ocor > -1) {
            var novotexto = texto.replace(/__0/g, "__" + proximo);
            jQuery(this).attr("data-index", novotexto);
          }
        }
      });
  });
  acerta_botoes_rep();
  return;
}
/**
 * sobe_desce_item
 * Sobe ou desce um ítem da lista
 * @param {object} obj - Campo que deverá ser repetido
 * @param {integer} sobedesce - Sobe 0, Desce 1
 */
function sobe_desce_item(obj, sobedesce, repete, indtab = -1) {
  fim = 999;
  atual = parseInt(obj.getAttribute("data-index"));
  origem = "";
  destino = "";
  if (sobedesce == "sobe") {
    // sobe
    nova_pos = atual - 1;
  } else {
    // desce
    nova_pos = atual + 1;
  }
  repetetab = repete;
  if (indtab >= 0) {
    repetetab += "\\[" + indtab + "\\]";
  }
  jQuery("#form1").attr("data-alter", true);

  jQuery("#rep_" + repetetab + " .table-" + repete).each(function (i) {
    // PRIMEIRO O ELEMENTO Q VAI DEIXAR DE SER É ALTERADO PARA 999
    if (parseInt(this.getAttribute("data-index")) == nova_pos) {
      jQuery(this).attr("data-index", fim);
      // jQuery(this).children().children().each(function (e) {
      // jQuery(this).children().each(function (e) {
      jQuery(this).each(function (e) {
        var labels = jQuery(this).find("label[for*=\\]]");
        if (labels.length > 0) {
          altera_index(labels, nova_pos, fim);
        }
        var inputs = jQuery(this).find("input[id*=\\]]");
        if (inputs.length > 0) {
          altera_index(inputs, nova_pos, fim);
        }
        var selects = jQuery(this).find("select[id*=\\]]");
        if (selects.length > 0) {
          altera_index(selects, nova_pos, fim);
        }
        var botoesid = jQuery(this).find("button[id*=\\]]");
        if (botoesid.length > 0) {
          altera_index(botoesid, nova_pos, fim);
        }
        var botoes = jQuery(this).find("button[data-id*=\\]]");
        if (botoes.length > 0) {
          altera_index(botoes, nova_pos, fim);
        }
        var divs = jQuery(this).find("div[id*=\\]]");
        if (divs.length > 0) {
          altera_index(divs, nova_pos, fim);
        }
        // });
      });
    }
  });
  jQuery("#rep_" + repetetab + " .table-" + repete).each(function (i) {
    // MUDA O ATUAL PARA A NOVA POSIÇÃO
    if (parseInt(this.getAttribute("data-index")) == atual) {
      jQuery(this).attr("data-index", nova_pos);
      // jQuery(this).children().children().each(function (e) {
      // jQuery(this).children().each(function (e) {
      jQuery(this).each(function (e) {
        var labels = jQuery(this).find("label[for*=\\]]");
        if (labels.length > 0) {
          altera_index(labels, atual, nova_pos);
        }
        var inputs = jQuery(this).find("input[id*=\\]]");
        if (inputs.length > 0) {
          altera_index(inputs, atual, nova_pos);
        }
        var selects = jQuery(this).find("select[id*=\\]]");
        if (selects.length > 0) {
          altera_index(selects, atual, nova_pos);
        }
        var botoesid = jQuery(this).find("button[id*=\\]]");
        if (botoesid.length > 0) {
          altera_index(botoesid, atual, nova_pos);
        }
        var botoes = jQuery(this).find("button[data-id*=\\]]");
        if (botoes.length > 0) {
          altera_index(botoes, atual, nova_pos);
        }
        var botoes = jQuery(this).find("button[data-index*=\\]]");
        if (botoes.length > 0) {
          altera_index(botoes, atual, nova_pos);
        }
        var divs = jQuery(this).find("div[id*=\\]]");
        if (divs.length > 0) {
          altera_index(divs, atual, nova_pos);
        }
        // });
      });
      destino = this;
    }
  });
  jQuery("#rep_" + repetetab + " .table-" + repete).each(function (i) {
    // volta o 999 para a posição do Atual
    if (parseInt(this.getAttribute("data-index")) == fim) {
      jQuery(this).attr("data-index", atual);
      // jQuery(this).children().children().each(function (e) {
      // jQuery(this).children().each(function (e) {
      jQuery(this).each(function (e) {
        var labels = jQuery(this).find("label[for*=\\]]");
        if (labels.length > 0) {
          altera_index(labels, fim, atual);
        }
        var inputs = jQuery(this).find("input[id*=\\]]");
        if (inputs.length > 0) {
          altera_index(inputs, fim, atual);
        }
        var selects = jQuery(this).find("select[id*=\\]]");
        if (selects.length > 0) {
          altera_index(selects, fim, atual);
        }
        var botoesid = jQuery(this).find("button[id*=\\]]");
        if (botoesid.length > 0) {
          altera_index(botoesid, fim, atual);
        }
        var botoes = jQuery(this).find("button[data-id*=\\]]");
        if (botoes.length > 0) {
          altera_index(botoes, fim, atual);
        }
        var divs = jQuery(this).find("div[id*=\\]]");
        if (divs.length > 0) {
          altera_index(divs, fim, atual);
        }
        // });
      });
      origem = this;
    }
  });
  jQuery(origem).swap(destino);
  acerta_botoes_rep(repete, indtab);
}

(function (jQuery) {
  jQuery.fn.swap = function (anotherElement) {
    var a = jQuery(this).get(0);
    var b = jQuery(anotherElement).get(0);
    var swap = document.createElement("span");
    a.parentNode.insertBefore(swap, a);
    b.parentNode.insertBefore(a, b);
    swap.parentNode.insertBefore(b, swap);
    swap.remove();
  };
})(jQuery);

function altera_index(obj, ind_a, ind_n) {
  i_ant = "[" + ind_a + "]";
  console.log("I Antes " + i_ant);
  i_dep = "[" + ind_n + "]";
  console.log("I Depois " + i_dep);
  for (i = 0; i < obj.length; i++) {
    if (obj[i].getAttribute("for") != undefined) {
      id_antes = obj[i].getAttribute("for").toString();
      console.log("Antes " + id_antes);
      id_depois = id_antes.replace(i_ant, i_dep);
      console.log("Depois " + id_depois);
      obj[i].htmlFor = id_depois;
    } else if (obj[i].getAttribute("data-id") != undefined) {
      id_antes = obj[i].getAttribute("data-id").toString();
      console.log("Antes " + id_antes);
      id_depois = id_antes.replace(i_ant, i_dep);
      console.log("Depois " + id_depois);
      jQuery(obj[i]).attr("data-id", id_depois.toString());
    } else if (obj[i].getAttribute("data-index") != undefined) {
      id_antes = obj[i].getAttribute("data-index").toString();
      console.log("Antes " + id_antes);
      id_depois = id_antes.replace(ind_a, ind_n);
      console.log("Depois " + id_depois);
      jQuery(obj[i]).attr("data-index", id_depois.toString());
    } else if (obj[i].getAttribute("data-selec") != undefined) {
      id_antes = obj[i].getAttribute("data-selec").toString();
      console.log("Antes " + id_antes);
      id_depois = id_antes.replace(ind_a, ind_n);
      console.log("Depois " + id_depois);
      jQuery(obj[i]).attr("data-selec", id_depois.toString());
    }
    id_antes = obj[i].id.toString();
    console.log("Antes " + id_antes);
    id_depois = id_antes.replace(i_ant, i_dep);
    console.log("Depois " + id_depois);
    obj[i].id = id_depois;
    if (obj[i].name != undefined) {
      nm_antes = obj[i].name.toString();
      console.log("Antes " + nm_antes);
      nm_depois = nm_antes.replace(i_ant, i_dep);
      console.log("Depois " + nm_depois);
      obj[i].name = nm_depois;
      console.log("Nome Depois" + obj[i].name);
    }
  }
}

/**
 * acerta_botoes_rep
 * Acerta Botões de Adicionar e Excluir campos
 *
 */
function acerta_botoes_rep(repete, pos = -1) {
  repetepos = repete;
  if (pos >= 0) {
    repetepos += "\\[" + pos + "\\]";
  }
  visiveis = jQuery("#rep_" + repetepos + " .bt-repete").length;
  ultimo = visiveis - 1;

  jQuery("#rep_" + repetepos + " .bt-repete").removeClass("d-none");
  jQuery("#rep_" + repetepos + " .bt-exclui").removeClass("d-none");
  jQuery("#rep_" + repetepos + " .bt-up").removeClass("d-none");
  jQuery("#rep_" + repetepos + " .bt-down").removeClass("d-none");
  jQuery("#rep_" + repetepos + " .bt-repete").addClass("d-none");

  if (visiveis == 1) {
    // quando tem só 1, não pode excluir
    jQuery("#rep_" + repetepos + " .bt-exclui").addClass("d-none");
    jQuery("#rep_" + repetepos + " .bt-up").addClass("d-none");
    jQuery("#rep_" + repetepos + " .bt-down").addClass("d-none");
  }
  // o botão de Adicionar só aparece no último
  jQuery(jQuery("#rep_" + repetepos + " .bt-repete")[ultimo]).removeClass(
    "d-none"
  );
  jQuery(jQuery("#rep_" + repetepos + " .bt-up")[0]).addClass("d-none");
  jQuery(jQuery("#rep_" + repetepos + " .bt-down")[ultimo]).addClass("d-none");
}
/**
 * acerta_botoes_rep
 * Acerta Botões de Adicionar e Excluir campos
 *
 */
function acerta_botoes_rep(repete, pos = -1) {
  repetepos = repete;
  if (pos >= 0) {
    repetepos += "\\[" + pos + "\\]";
  }
  visiveis = jQuery("#rep_" + repetepos + " .bt-repete").length;
  ultimo = visiveis - 1;

  jQuery("#rep_" + repetepos + " .bt-repete").removeClass("d-none");
  jQuery("#rep_" + repetepos + " .bt-exclui").removeClass("d-none");
  jQuery("#rep_" + repetepos + " .bt-up").removeClass("d-none");
  jQuery("#rep_" + repetepos + " .bt-down").removeClass("d-none");
  jQuery("#rep_" + repetepos + " .bt-repete").addClass("d-none");

  if (visiveis == 1) {
    // quando tem só 1, não pode excluir
    jQuery("#rep_" + repetepos + " .bt-exclui").addClass("d-none");
    jQuery("#rep_" + repetepos + " .bt-up").addClass("d-none");
    jQuery("#rep_" + repetepos + " .bt-down").addClass("d-none");
  }
  // o botão de Adicionar só aparece no último
  jQuery(jQuery("#rep_" + repetepos + " .bt-repete")[ultimo]).removeClass(
    "d-none"
  );
  jQuery(jQuery("#rep_" + repetepos + " .bt-up")[0]).addClass("d-none");
  jQuery(jQuery("#rep_" + repetepos + " .bt-down")[ultimo]).addClass("d-none");
}

/**
 * testa_dep
 * trata se o campo pai, de um dependente, está preenchido
 * Caso não esteja, muda o foco para o campo pai
 * @param {object} id_dep - Campo Pai do Dependente
 */
function testa_dep(id_dep) {
  var nodes = document.getElementById(id_dep);
  var jqObj = jQuery(nodes);
  if (parseInt(jQuery(jqObj).val()) < 1) {
    jQuery(jqObj).focus();
  }
}

/**
 * busca_dependente
 * Preenche a lista de opções de um campo dependente, conforme a seleção do campo pai
 * @param {object} obj - campo pai
 * @param {object} id_dep - campo dependente
 * @param {url} url_busca - URL de busca de dependentes
 * @param {integer} selec - Dependente pré-selecionado
 */
function busca_dependente(obj, id_dep, url_busca, selec) {
  if (parseInt(jQuery(obj).val()) > 0) {
    var nodes = document.getElementById(id_dep);
    var jqObj = jQuery(nodes);

    var datarr = new Array();
    datarr[0] = {};
    datarr[0].id_dep = jQuery(obj).val();
    jQuery.ajax({
      type: "POST",
      async: false,
      dataType: "json",
      url: url_busca,
      data: { busca: jQuery(obj).val() },
      success: function (retorno) {
        console.log(retorno);
        arr_ret = [];
        jQuery.each(retorno, function (key, value) {
          arr_ret[key] = value;
        });
        arr_ret.sort(function (a, b) {
          return a[1] < b[1] ? -1 : a[1] > b[1] ? 1 : 0;
        });
        console.log(arr_ret);

        jQuery('[name="' + id_dep + '"]')
          .children("option")
          .remove();
        jQuery.each(retorno, function (key, value) {
          if (value.id == selec || retorno.length == 1) {
            jQuery('[name="' + id_dep + '"]').append(
              jQuery("<option selected></option>")
                .attr("value", value.id)
                .text(value.text)
            );
          } else {
            jQuery('[name="' + id_dep + '"]').append(
              jQuery("<option></option>")
                .attr("value", value.id)
                .text(value.text)
            );
          }
        });
        jQuery('[name="' + id_dep + '"]').selectpicker("destroy");
        jQuery('[name="' + id_dep + '"]').selectpicker("deselectAll");
        if (retorno.length == 1) {
          jQuery('[name="' + id_dep + '"]').selectpicker("val", retorno[0].id);
          jQuery('[name="' + id_dep + '"]').trigger("change");
        }
      },
      error: function (xhr, ajaxOptions, thrownError) {
        jQuery(jqObj).children("option").remove();
        jQuery(jqObj).append(
          jQuery("<option selected></option>")
            .attr("value", -1)
            .text(nodes.getAttribute("placeholder"))
        );
        boxAlert(
          "Ocorreu um Erro!<br>" + xhr.responseText,
          true,
          "",
          false,
          1,
          false
        );
      },
    });
  } else {
    var nodes = document.getElementById(id_dep);
    var jqObj = jQuery(nodes);
    jQuery(jqObj).children("option").remove();
    jQuery(jqObj).append(
      jQuery("<option></option>")
        .attr("value", -1)
        .text(nodes.getAttribute("placeholder"))
    );
  }
}

/**
 * readURL
 * Le um arquivo de imagem local e mostra na tela
 * @param {object} input  - campo file
 * @param {object} id     - destino da imagem
 * @param {integer} largura - largura da Div onde será mostrada a imagem
 * @param {integer} altura - altura da Div onde será mostrada a imagem
 */
function readURL(input, id, largura, altura) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();

    reader.onload = function (e) {
      jQuery(id).attr("src", e.target.result).width(largura).height(altura);
    };

    reader.readAsDataURL(input.files[0]);
  }
}

/**
 * compara_senha
 * Compara senha e contra senha, para garantir a senha correta
 *
 * @param {*} contra - nome do campo da contra senha
 * @param {*} senha  - nome do campo da senha
 */
function compara_senha(contra, senha) {
  var contra_senha = jQuery("#" + contra).val();
  var nova_senha = jQuery("#" + senha).val();
  if (contra_senha != nova_senha) {
    jQuery("#msg_senha").html("<b>Senhas não conferem! REVISE!</b>");
    jQuery("#msg_senha").addClass("p-2 px-4");
    jQuery("#bt_salvar").attr("disabled", true);
  } else {
    jQuery("#msg_senha").html("");
    jQuery("#msg_senha").removeClass("p-2 px-4");
    jQuery("#bt_salvar").attr("disabled", false);
  }
}

/**
 * calcula_diferenca
 * Calcula a diferença entre 2 valores informados em campos lidos por jQuery
 *
 * @param {string} origem    - id do campo original
 * @param {string} informado - id do campo informado
 * @param {string} retorno   - id do campo de retorno
 */
function calcula_diferenca(origem, informado, retorno) {
  var orig = converteMoedaFloat(jQuery("#" + origem).val());
  var info = converteMoedaFloat(jQuery("#" + informado).val());
  var dife = orig - info;
  jQuery("#" + retorno).val(converteFloatMoeda(dife));
}

/**
 * habilita_campos
 * Mostra ou oculta campos na tela de edição
 *
 * @param {string} condicao    - campo a ser testado
 * @param {string} valor       - valor a ser testado
 * @param {string} ocultos     - campos que serão ocultados
 */
function habilita_campos(condicao, valor, ocultos) {
  if (jQuery("#" + condicao).val() == valor) {
    jQuery('div[id^="ig_"]').show();
    oculto = ocultos.split(",");
    for (o = 0; o < oculto.length; o++) {
      jQuery("#ig_" + oculto[o]).hide();
    }
  }
}

/**
 * busca_atributos
 * Retorna a Etiqueta e o Icone da Opção selecionada
 * Faz os acertos dos campos conforme a Hierarquia
 * @param {string} tipo  - Identifica se é Módulo ou Classe
 * @param {object} opcao  - Opcao Selecionada
 * @param {string} etiqueta  - campo da Etiqueta
 * @param {string} icone  - Campo do ícone
 *
 */
function busca_atributos(tipo, opcao, etiqueta, icone) {
  opc = jQuery("#" + opcao).val();
  if (opc != "") {
    url = "/buscas/busca_modulo_id";
    if (tipo == "tela") {
      url = "/buscas/busca_tela_id";
    }
    jQuery.ajax({
      type: "POST",
      headers: { "X-Requested-With": "XMLHttpRequest" },
      url: url,
      async: true,
      dataType: "json",
      data: { busca: opc },
      success: function (data) {
        jQuery("#" + etiqueta).val(data[0].text);
        jQuery("#" + icone).val(data[0].icone);
      },
    });
  }
}

/**
 * busca_textselect
 * Pega o texto do Select e coloca no campo
 * @param {object} obj  - Select de Origem
 * @param {string} id_destino  - Campo de Destino
 */
function busca_textselect(obj, id_destino) {
  //pega o texto do select informado
  var id_res = obj.id;
  var val_ant = jQuery("#" + id_destino).val();
  if (val_ant != undefined && val_ant.length() > 0) {
    val_ant += " - ";
  }
  if (obj.nodeName == "SELECT") {
    if (obj.selectedOptions[0].value >= 0) {
      var valor = obj.selectedOptions[0].text;
    } else {
      var valor = "";
    }
  } else {
    var valor = obj.value;
  }
  if (val_ant != undefined) {
    var val_fim = (val_ant + valor).trim();
    jQuery("#" + id_destino).val(val_fim);
  }
}

function busca_selectvalue(obj, id_destino) {
  //pega o valor do select informado
  var id_res = obj.id;
  var valor = obj.value;
  jQuery("#" + id_destino)
    .val(valor)
    .trigger("change");
}

/**
 * validaSenha
 * valida se a Senha contém os caracteres obrigatórios
 * Pelo menos
 * 1 letra maiuscula
 * 1 letra minuscula
 * 1 símbolo
 * 1 número
 * sem caracteres repetidos
 * @param {object} obj  - Campo de Origem
 */
function validaSenha(obj) {
  var senha = obj.value;
  if (senha.length > 0) {
    let regex =
      /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%*()_+^&}{:;?.])(?:([0-9a-zA-Z!@#$%;*(){}_+^&])(?!\1)){6,8}$/;
    if (regex.test(senha)) {
      jQuery(obj).removeClass("is-invalid");
      return true;
    } else {
      jQuery(obj).addClass("is-invalid");
      jQuery(obj).focus();
    }
  }
}

// MUDA O TIPO DE PESSOA FÍSICA OU JURÍDICA taisho
function muda_pessoa(obj) {
  if (jQuery("input[id^='" + obj + "']:checked").val() == "F") {
    // CPF
    jQuery("#ig_for_cnpj").removeClass("d-inline-flex");
    jQuery("#ig_for_cnpj").addClass("d-none");
    jQuery("#for_cnpj").attr("data-salva", 0);
    jQuery("#ig_for_cpf").removeClass("d-none");
    jQuery("#ig_for_cpf").addClass("d-inline-flex");
    jQuery("#for_cpf").attr("data-salva", 1);
    jQuery(
      "#for_razao"
    )[0].parentNode.parentNode.children[0].children[0].innerText = "Nome";
    jQuery(
      "#for_fantasia"
    )[0].parentNode.parentNode.children[0].children[0].innerText = "Apelido";
    jQuery("#for_razao").attr("placeholder", "Informe Nome");
    jQuery("#for_fantasia").attr("placeholder", "Informe Apelido");
  } else {
    // CNPJ
    jQuery("#ig_for_cpf").removeClass("d-inline-flex");
    jQuery("#ig_for_cpf").addClass("d-none");
    jQuery("#for_cpf").attr("data-salva", 0);
    jQuery("#ig_for_cnpj").removeClass("d-none");
    jQuery("#ig_for_cnpj").addClass("d-inline-flex");
    jQuery("#for_cnpj").attr("data-salva", 1);
    jQuery(
      "#for_razao"
    )[0].parentNode.parentNode.children[0].children[0].innerText =
      "Razão Social";
    jQuery(
      "#for_fantasia"
    )[0].parentNode.parentNode.children[0].children[0].innerText = "Fantasia";
    jQuery("#for_razao").attr("placeholder", "Informe Razão Social");
    jQuery("#for_fantasia").attr("placeholder", "Informe Fantasia");
  }
}

/**
 * ValidaCPF
 * valida o CPF informado
 * @param {object} obj  - Campo de Origem
 */
function ValidaCPF(obj) {
  var valor = obj.value;
  // Remove caracteres inválidos do valor
  valor = valor.replace(/[^0-9]/g, "");

  // Captura os 9 primeiros dígitos do CPF
  // Ex.: 02546288423 = 025462884
  var digitos = valor.substr(0, 9);

  // Faz o cálculo dos 9 primeiros dígitos do CPF para obter o primeiro dígito
  var novo_cpf = calc_digitos_posicoes(digitos);

  // Faz o cálculo dos 10 dígitos do CPF para obter o último dígito
  var novo_cpf = calc_digitos_posicoes(novo_cpf, 11);

  // Verifica se o novo CPF gerado é idêntico ao CPF enviado
  if (novo_cpf === valor) {
    // CPF válido
    jQuery(obj).removeClass("is-invalid");
    return true;
  } else {
    // CPF inválido
    // boxAlert('CPF Inválido', true, '', true, 1, false);
    jQuery(obj).addClass("is-invalid");
    jQuery(obj).focus();
    return false;
  }
}

/**
 * calc_digitos_posicoes
 *
 * Multiplica dígitos vezes posições
 *
 * @param string digitos Os digitos desejados
 * @param string posicoes A posição que vai iniciar a regressão
 * @param string soma_digitos A soma das multiplicações entre posições e dígitos
 * @return string Os dígitos enviados concatenados com o último dígito
 */
function calc_digitos_posicoes(digitos, posicoes = 10, soma_digitos = 0) {
  // Garante que o valor é uma string
  digitos = digitos.toString();
  // Faz a soma dos dígitos com a posição
  // Ex. para 10 posições:
  //   0    2    5    4    6    2    8    8   4
  // x10   x9   x8   x7   x6   x5   x4   x3  x2
  //   0 + 18 + 40 + 28 + 36 + 10 + 32 + 24 + 8 = 196
  for (var i = 0; i < digitos.length; i++) {
    // Preenche a soma com o dígito vezes a posição
    soma_digitos = soma_digitos + digitos[i] * posicoes;
    // Subtrai 1 da posição
    posicoes--;
    // Parte específica para CNPJ
    // Ex.: 5-4-3-2-9-8-7-6-5-4-3-2
    if (posicoes < 2) {
      // Retorno a posição para 9
      posicoes = 9;
    }
  }
  // Captura o resto da divisão entre soma_digitos dividido por 11
  // Ex.: 196 % 11 = 9
  soma_digitos = soma_digitos % 11;
  // Verifica se soma_digitos é menor que 2
  if (soma_digitos < 2) {
    // soma_digitos agora será zero
    soma_digitos = 0;
  } else {
    // Se for maior que 2, o resultado é 11 menos soma_digitos
    // Ex.: 11 - 9 = 2
    // Nosso dígito procurado é 2
    soma_digitos = 11 - soma_digitos;
  }
  // Concatena mais um dígito aos primeiro nove dígitos
  // Ex.: 025462884 + 2 = 0254628842
  var cpf = digitos + soma_digitos;
  // Retorna
  return cpf;
}

function formata_campo(objtipo, campo_alvo) {
  tipo = objtipo.value;
  url = "/buscas/busca_tipo_contato";
  jQuery.ajax({
    type: "POST",
    headers: { "X-Requested-With": "XMLHttpRequest" },
    url: url,
    async: true,
    dataType: "json",
    data: { busca: tipo },
    success: function (data) {
      if (data[0].text == "celu" || data[0].text == "whats") {
        jQuery("[id='" + campo_alvo + "']").prop("type", "tel");
        jQuery("[id='" + campo_alvo + "']").prop(
          "pattern",
          /^\(\d{2}\) \d{4,5}\-\d{4}$/
        );
        jQuery("[id='" + campo_alvo + "']").prop("style", "text-align: left");
        jQuery("[id='" + campo_alvo + "']").prop(
          "placeholder",
          "Informe Celular"
        );
        jQuery("[id='" + campo_alvo + "']").prop(
          "aria-describedby",
          "ad_" + campo_alvo
        );
        jQuery("[id='" + campo_alvo + "']").prop(
          "data-original-title",
          "Informe um Celular válido! (99) 99999-9999"
        );
        jQuery("[id='" + campo_alvo + "']").prop(
          "title",
          "Informe um Celular válido! (99) 99999-9999"
        );
        jQuery("[id='" + campo_alvo + "']").keyup(function () {
          mascara(this, "mcel2");
        });
      } else if (data[0].text == "fone") {
        jQuery("[id='" + campo_alvo + "']").prop("type", "tel");
        jQuery("[id='" + campo_alvo + "']").prop(
          "pattern",
          /^\(\d{2}\) \d{4}\-\d{4}$/
        );
        jQuery("[id='" + campo_alvo + "']").prop("style", "text-align: left");
        jQuery("[id='" + campo_alvo + "']").prop("placeholder", "Informe Fone");
        jQuery("[id='" + campo_alvo + "']").prop(
          "aria-describedby",
          "ad_" + campo_alvo
        );
        jQuery("[id='" + campo_alvo + "']").prop(
          "data-original-title",
          "Informe um Fone válido! (99) 9999-9999"
        );
        jQuery("[id='" + campo_alvo + "']").prop(
          "title",
          "Informe um Fone válido! (99) 9999-9999"
        );
        jQuery("[id='" + campo_alvo + "']").attr(
          "onkeyup",
          mascara(this, "mtel")
        );
      } else if (data[0].text == "email") {
        jQuery("[id='" + campo_alvo + "']").prop("type", "email");
        jQuery("[id='" + campo_alvo + "']").prop(
          "pattern",
          /^[\w\.=-]+@[\w\.-]+\.[\w]{2,3}$/
        );
        jQuery("[id='" + campo_alvo + "']").prop("style", "text-align: left");
        jQuery("[id='" + campo_alvo + "']").prop(
          "aria-describedby",
          "ad_" + campo_alvo
        );
        jQuery("[id='" + campo_alvo + "']").prop(
          "placeholder",
          "Informe E-mail"
        );
        jQuery("[id='" + campo_alvo + "']").prop(
          "data-original-title",
          "Informe um E-mail válido!"
        );
        jQuery("[id='" + campo_alvo + "']").prop(
          "title",
          "Informe um E-mail válido!"
        );
      } else if (data[0].text == "url" || data[0].text == "site") {
        jQuery("[id='" + campo_alvo + "']").prop("type", "url");
        jQuery("[id='" + campo_alvo + "']").prop(
          "pattern",
          /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/
        );
        jQuery("[id='" + campo_alvo + "']").prop("style", "text-align: left");
        jQuery("[id='" + campo_alvo + "']").prop(
          "aria-describedby",
          "ad_" + campo_alvo
        );
        jQuery("[id='" + campo_alvo + "']").prop("placeholder", "Informe url");
        jQuery("[id='" + campo_alvo + "']").prop(
          "data-original-title",
          "Informe uma url válida!"
        );
        jQuery("[id='" + campo_alvo + "']").prop(
          "title",
          "Informe uma url válida!"
        );
      }
      jQuery("[id='" + campo_alvo + "']").focus();
    },
  });
}

/**
 * ValidaCPF
 * valida o CPF informado
 * @param {object} obj  - Campo de Origem
 */
// function ValidaCPF(obj) {
//     var valor = obj.value;
//     // Remove caracteres inválidos do valor
//     valor = valor.replace(/[^0-9]/g, '');

//     // Captura os 9 primeiros dígitos do CPF
//     // Ex.: 02546288423 = 025462884
//     var digitos = valor.substr(0, 9);

//     // Faz o cálculo dos 9 primeiros dígitos do CPF para obter o primeiro dígito
//     var novo_cpf = calc_digitos_posicoes(digitos);

//     // Faz o cálculo dos 10 dígitos do CPF para obter o último dígito
//     var novo_cpf = calc_digitos_posicoes(novo_cpf, 11);

//     // Verifica se o novo CPF gerado é idêntico ao CPF enviado
//     if (novo_cpf === valor) {
//         // CPF válido
//         jQuery(obj).removeClass('is-invalid');
//         return true;
//     } else {
//         // CPF inválido
//         // boxAlert('CPF Inválido', true, '', true, 1, false);
//         jQuery(obj).addClass('is-invalid');
//         jQuery(obj).focus();
//         return false;
//     }
// };;

// /**
// * calc_digitos_posicoes
// *
// * Multiplica dígitos vezes posições
// *
// * @param string digitos Os digitos desejados
// * @param string posicoes A posição que vai iniciar a regressão
// * @param string soma_digitos A soma das multiplicações entre posições e dígitos
// * @return string Os dígitos enviados concatenados com o último dígito
// */
// function calc_digitos_posicoes(digitos, posicoes = 10, soma_digitos = 0) {
//     // Garante que o valor é uma string
//     digitos = digitos.toString();
//     // Faz a soma dos dígitos com a posição
//     // Ex. para 10 posições:
//     //   0    2    5    4    6    2    8    8   4
//     // x10   x9   x8   x7   x6   x5   x4   x3  x2
//     //   0 + 18 + 40 + 28 + 36 + 10 + 32 + 24 + 8 = 196
//     for (var i = 0; i < digitos.length; i++) {
//         // Preenche a soma com o dígito vezes a posição
//         soma_digitos = soma_digitos + (digitos[i] * posicoes);
//         // Subtrai 1 da posição
//         posicoes--;
//         // Parte específica para CNPJ
//         // Ex.: 5-4-3-2-9-8-7-6-5-4-3-2
//         if (posicoes < 2) {
//             // Retorno a posição para 9
//             posicoes = 9;
//         }
//     }
//     // Captura o resto da divisão entre soma_digitos dividido por 11
//     // Ex.: 196 % 11 = 9
//     soma_digitos = soma_digitos % 11;
//     // Verifica se soma_digitos é menor que 2
//     if (soma_digitos < 2) {
//         // soma_digitos agora será zero
//         soma_digitos = 0;
//     } else {
//         // Se for maior que 2, o resultado é 11 menos soma_digitos
//         // Ex.: 11 - 9 = 2
//         // Nosso dígito procurado é 2
//         soma_digitos = 11 - soma_digitos;
//     }
//     // Concatena mais um dígito aos primeiro nove dígitos
//     // Ex.: 025462884 + 2 = 0254628842
//     var cpf = digitos + soma_digitos;
//     // Retorna
//     return cpf;
// };;

// function pesquisacep(obj, valor) {
//     // regex = '\[(\w+)\]';
//     // pos = obj['id'].indexOf("__");
//     posi = obj.id.indexOf('[') + 1;
//     posf = obj.id.indexOf(']');
//     pos = obj.id.substr(posi, (posf - posi));
//     if (pos >= 0) {
//         pos_seq = obj['id'].substring(pos + 2, obj['id'].length);
//     }
//     //Nova variável "cep" somente com dígitos.
//     var cep = valor.replace(/\D/g, '');
//     //Verifica se campo cep possui valor informado.
//     if (cep != "") {
//         //Expressão regular para validar o CEP.
//         var validacep = /^[0-9]{8}$/;
//         //Valida o formato do CEP.
//         if (validacep.test(cep)) {
//             // limpa_formulário_cep();
//             var url = "https://viacep.com.br/ws/" + cep + "/json/?callback=?";
//             // var uf = [];
//             // uf['AC'] = '1';
//             // uf['AL'] = '2';
//             // uf['AM'] = '3';
//             // uf['AP'] = '4';
//             // uf['BA'] = '5';
//             // uf['CE'] = '6';
//             // uf['DF'] = '7';
//             // uf['ES'] = '8';
//             // uf['GO'] = '9';
//             // uf['MA'] = '10';
//             // uf['MT'] = '11';
//             // uf['MS'] = '12';
//             // uf['MG'] = '13';
//             // uf['PA'] = '14';
//             // uf['PB'] = '15';
//             // uf['PR'] = '16';
//             // uf['PE'] = '17';
//             // uf['PI'] = '18';
//             // uf['RJ'] = '19';
//             // uf['RN'] = '20';
//             // uf['RS'] = '21';
//             // uf['RO'] = '22';
//             // uf['RR'] = '23';
//             // uf['SC'] = '24';
//             // uf['SP'] = '25';
//             // uf['SE'] = '26';
//             // uf['TO'] = '27';

//             // Faz a pesquisa do CEP, tratando o retorno com try/catch para que
//             // caso ocorra algum erro (o cep pode não existir, por exemplo) a
//             // usabilidade não seja afetada, assim o usuário pode continuar//
//             // preenchendo os campos normalmente
//             jQuery.ajax({
//                 type: 'POST',
//                 async: false,
//                 dataType: 'json',
//                 url: url,
//                 success: function (dadosRetorno) {
//                     jQuery("#for_rua").val(dadosRetorno.logradouro);
//                     jQuery("#for_bairro").val(dadosRetorno.bairro);
//                     jQuery("#for_estado").val(dadosRetorno.uf);
//                     jQuery("#for_cidade").val(dadosRetorno.localidade);
//                 }
//             });
//         } //end if.
//         else {
//             //cep é inválido.
//             // limpa_formulário_cep();
//             alert("Formato de CEP inválido.");
//         }
//     } //end if.
//     else {
//         //cep sem valor, limpa formulário.
//         // limpa_formulário_cep();
//     }
// };

// function pesquisaCNPJ(valor, relacao, pref = 'cli') {
//     //Nova variável "CNPJ" somente com dígitos.
//     var id = jQuery('#' + pref + '_id').val();
//     var CNPJ = valor.replace(/\D/g, '');
//     //Verifica se campo CNPJ possui valor informado.
//     if (CNPJ != "") {
//         //Expressão regular para validar o CEP.
//         var validaCNPJ = /(\d{0,2})(\d{0,3})(\d{0,3})(\d{0,4})(\d{0,2})/;
//         //Valida o formato do CEP.
//         if (validaCNPJ.test(CNPJ)) {
//             // testaCliente = CNPJCPFcadastrado(valor);
//             url = window.location.origin + '/buscas/cnpjcpfcadastrado';
//             jQuery.ajax({
//                 type: 'POST',
//                 async: false,
//                 dataType: 'json',
//                 url: url,
//                 data: { 'cpfcnpf': valor, 'relacao': relacao },
//                 success: function (retorno) {
//                     if (retorno.tem == '1' && retorno.id != id) {
//                         boxAlert('CNPJ Já Cadastrado', true, '', false, 1, false);
//                         jQuery('#' + pref + '_cnpj').val('');
//                         jQuery('#' + pref + '_cnpj').focus();
//                     } else {
//                         var url = "https://api-publica.speedio.com.br/buscarcnpj?cnpj=" + CNPJ;

//                         // Faz a pesquisa do CEP, tratando o retorno com try/catch para que
//                         // caso ocorra algum erro (o CNPJ pode não existir, por exemplo) a
//                         // usabilidade não seja afetada, assim o usuário pode continuar//
//                         // preenchendo os campos normalmente

//                         jQuery.getJSON(url, function (dadosRetorno) {
//                             jQuery('#' + pref + '_nome').val(dadosRetorno['RAZAO SOCIAL']);
//                             jQuery('#' + pref + '_apelido').val(dadosRetorno['NOME FANTASIA']);
//                             jQuery('#' + pref + '_cnae').val(dadosRetorno['CNAE PRINCIPAL CODIGO']);
//                             jQuery('#' + pref + '_cnad').val(dadosRetorno['CNAE PRINCIPAL DESCRICAO']);
//                             localStorage.setItem('dadoscnpj', dadosRetorno);
//                             pesquisacepcnpj(dadosRetorno);
//                         });
//                     }
//                 }
//             });
//         } //end if.
//         else {
//             //CNPJ é inválido.
//             // limpa_formulário_CNPJ();
//             boxAlert('Formato de CNPJ inválido', true, '', false, 1, false);
//         }
//     } //end if.
//     else {
//         //CNPJ sem valor, limpa formulário.
//         // limpa_formulário_CNPJ();
//     }
// };

// function pesquisaCPF(valor, relacao) {
//     if (valor != '') {
//         // testaCliente = ;
//         url = window.location.origin + '/buscas/cnpjcpfcadastrado';
//         jQuery.ajax({
//             type: 'POST',
//             async: false,
//             dataType: 'json',
//             url: url,
//             data: { 'cpfcnpf': valor, 'relacao': relacao },
//             success: function (retorno) {
//                 if (retorno.tem == '1') {
//                     boxAlert('CPF Já Cadastrado', true, '', false, 1, false);
//                     jQuery('#cli_cpf').val('');
//                     jQuery('#cli_cpf').focus();
//                 }
//             }
//         });
//     }
// }

// function pesquisacepcnpj(dadoscnpj) {
//     // dadoscnpj = localStorage.getItem('dadoscnpj');
//     if (dadoscnpj != undefined) {
//         jQuery("#for_cep\\[0\\]").val(dadoscnpj['CEP']);
//         // faz a busca pelo CEP
//         jQuery("#for_cep\\[0\\]").trigger('blur');

//         rua = dadoscnpj['TIPO LOGRADOURO'] + ' ' + dadoscnpj['LOGRADOURO'];
//         jQuery("#for_rua\\[0\\]").val(rua);
//         jQuery("#for_bairro\\[0\\]").val(dadoscnpj['BAIRRO']);
//         jQuery("#for_numero\\[0\\]").val(dadoscnpj['NUMERO']);
//         if (dadoscnpj['COMPLEMENTO'] != '') {
//             jQuery("#for_compl\\[0\\]").val(dadoscnpj['COMPLEMENTO']);
//         }
//     }
// }

function identArquivo(obj) {
  arquivo = obj.files[0].name;
  obj.labels[0].textContent = arquivo;
}

function atualiza_ponto(obj) {
  ordem = obj.getAttribute("data-index");
  id = jQuery("#pon_id\\[" + ordem + "\\]").val();
  ent1 = jQuery("#pon_ent1\\[" + ordem + "\\]").val();
  sai1 = jQuery("#pon_sai1\\[" + ordem + "\\]").val();
  ent2 = jQuery("#pon_ent2\\[" + ordem + "\\]").val();
  sai2 = jQuery("#pon_sai2\\[" + ordem + "\\]").val();
  url = "/RhPonto/atualizaPonto";
  jQuery.ajax({
    type: "POST",
    async: true,
    dataType: "json",
    url: url,
    data: { id: id, ent1: ent1, sai1: sai1, ent2: ent2, sai2: sai2 },
    success: function (retorno) {
      jQuery("#pon_atestado").val(retorno.atestado);
      jQuery("#pon_folga").val(retorno.folga);
      jQuery("#pon_banco").val(retorno.banco);
      jQuery("#pon_abono").val(retorno.abono);
      jQuery("#pon_dayoff").val(retorno.dayoff);
      jQuery("#pon_falta").val(retorno.falta);
      jQuery("#pon_ferias").val(retorno.ferias);
      jQuery("#pon_inss").val(retorno.inss);
      jQuery("#pon_neutro").val(retorno.neutro);
      jQuery("#pon_vazio").val(retorno.vazio);
    },
  });
}

function gravaPedido(obj) {
  // alterado = obj.getAttribute("data-alter");
  // if (alterado) {
  ordem = obj.getAttribute("data-index");
  id = jQuery("#ped_id\\[" + ordem + "\\]").val();
  qtia = jQuery("#ped_qtia\\[" + ordem + "\\]").val();
  if (qtia > 0) {
    suge = jQuery("#ped_sugestao\\[" + ordem + "\\]").val();
    just = jQuery("#ped_justifica\\[" + ordem + "\\]").val();
    cont = jQuery("#gru_controlaestoque\\[" + ordem + "\\]").val();
    // if (suge != qtia &&
    //     just.trim().split(/\s+/).filter(Boolean).length < 2 &&
    //     cont == 'S'
    // ) {
    //     boxAlert('Informe a Justificativa, com pelo menos 2 palavras', true, '', false, 1, false);
    //     // jQuery("#ped_justifica\\[" + ordem + "\\]").focus();
    // } else {
    prod = jQuery("#pro_id\\[" + ordem + "\\]").val();
    unid = jQuery("#und_id\\[" + ordem + "\\]").val();
    empr = jQuery("#empresa").val();
    url = "/EstPedido/store";
    jQuery.ajax({
      type: "POST",
      async: true,
      dataType: "json",
      url: url,
      data: {
        ped_id: id,
        pro_id: prod,
        und_id: unid,
        ped_qtia: qtia,
        ped_justifica: just,
        ped_sugestao: suge,
        emp_id: empr,
      },
      success: function (retorno) {
        if (retorno.erro) {
          boxAlert(retorno.msg, retorno.erro, "", false, 1, false);
        } else {
          jQuery("#ped_id\\[" + ordem + "\\]").val(retorno.id);
          jQuery("#ped_data\\[" + ordem + "\\]").html(retorno.ped_data);
          jQuery(".toast-body").html(retorno.msg);
          jQuery(".toast").toast({
            delay: 500,
            animation: true,
          });
          jQuery(".toast").toast("show");
        }
      },
      error: function () {
        boxAlert("Erro ao Gravar o Pedido.", true, "");
      },
    });
    // }
  } else if (id != "") {
    if (qtia == "") {
      jQuery("#ped_qtia\\[" + ordem + "\\]").val(0);
    }
    url = "/EstPedido/delete/" + id;
    jQuery.ajax({
      type: "POST",
      async: true,
      dataType: "json",
      url: url,
      success: function (retorno) {
        jQuery("#ped_id\\[" + ordem + "\\]").val("");
        jQuery("#ped_data\\[" + ordem + "\\]").html(retorno.ped_data);
        jQuery("#ped_justifica\\[" + ordem + "\\]").html(retorno.ped_justifica);
        jQuery(".toast-body").html(retorno.msg);
        jQuery(".toast").toast({
          delay: 500,
          animation: true,
        });
        jQuery(".toast").toast("show");
      },
      error: function () {
        boxAlert("Erro ao Excluir o Pedido.", true, "");
      },
    });
  }
  // }
}

function gravaConsumo(obj) {
  ordem = obj.getAttribute("data-index");
  id = jQuery("#con_id\\[" + ordem + "\\]").val();
  con = parseInt(jQuery("#con_consumo\\[" + ordem + "\\]").val());
  dur = parseInt(jQuery("#con_duracao\\[" + ordem + "\\]").val());
  if (con > 0 || dur > 0) {
    prod = jQuery("#pro_id\\[" + ordem + "\\]").val();
    empr = jQuery("#empresa").val();
    url = "/EstConsumo/store";
    jQuery.ajax({
      type: "POST",
      async: true,
      dataType: "json",
      url: url,
      data: {
        con_id: id,
        pro_id: prod,
        con_consumo: con,
        con_duracao: dur,
        emp_id: empr,
      },
      success: function (retorno) {
        if (retorno.erro) {
          boxAlert(retorno.msg, retorno.erro, "", false, 1, false);
        } else {
          jQuery("#con_id\\[" + ordem + "\\]").val(retorno.id);
          jQuery(".toast-body").html(retorno.msg);
          jQuery(".toast").toast({
            delay: 500,
            animation: true,
          });
          jQuery(".toast").toast("show");
        }
      },
      error: function () {
        boxAlert("Erro ao Gravar o Consumo.", true, "");
      },
    });
  } else if (id != "" && con == 0 && dur == 0) {
    // if (min == '') {
    //     jQuery("#mmi_minimo\\[" + ordem + "\\]").val(0);
    // }
    url = "/EstConsumo/delete/" + id;
    jQuery.ajax({
      type: "POST",
      async: true,
      dataType: "json",
      url: url,
      success: function (retorno) {
        jQuery("#con_id\\[" + ordem + "\\]").val("");
        jQuery(".toast-body").html(retorno.msg);
        jQuery(".toast").toast({
          delay: 500,
          animation: true,
        });
        jQuery(".toast").toast("show");
      },
      error: function () {
        boxAlert("Erro ao Excluir o Consumo.", true, "");
      },
    });
  }
}

function gravaContagem(obj) {
  ordem = obj.getAttribute("data-index");
  idcta = jQuery("#cta_id").val();
  idctp = jQuery("#ctp_id\\[" + ordem + "\\]").val();

  contag = 0;
  let classePR = jQuery(obj)
    .attr("class")
    .split(" ")
    .find((c) => c.startsWith("pr"));
  jQuery(`input.${classePR}`).each(function () {
    ordite = this.getAttribute("data-index");
    if (jQuery(this).val() > 0) {
      valite = parseFloat(jQuery(this).val());
      fckite = parseFloat(jQuery("#ctp_fck\\[" + ordite + "\\]").val());
      if (fckite == 0) {
        fckite = 1;
      }
      contag += valite * fckite;
    }
  });
  conta = contag;
  saldo = parseFloat(jQuery("#ctp_saldo\\[" + ordem + "\\]").val());
  if (conta != saldo) {
    prod = jQuery("#pro_id\\[" + ordem + "\\]").val();
    unid = jQuery("#und_id\\[" + ordem + "\\]").val();
    empr = jQuery("#emp_id").val();
    depo = jQuery("#dep_id").val();
    if (depo == "") {
      boxAlert("Informe o Depósito", true, "", true, 1, false, "Atenção");
    } else {
      url = "/EstContagem/storecont";
      var dados = {
        cta_id: idcta,
        ctp_id: idctp,
        und_id: unid,
        pro_id: prod,
        ctp_quantia: conta,
        emp_id: empr,
        dep_id: depo,
      };
      jQuery.ajax({
        type: "POST",
        async: true,
        dataType: "json",
        url: url,
        data: dados,
        success: function (retorno) {
          if (retorno.erro) {
            boxAlert(retorno.msg, retorno.erro, "", false, 1, false);
          } else {
            jQuery("#cta_id").val(retorno.idcta);
            jQuery("#ctp_id\\[" + ordem + "\\]").val(retorno.idctp);
            jQuery(".toast-body").html(retorno.msg);
            jQuery(".toast").toast({
              delay: 500,
              animation: true,
            });
            jQuery(".toast").toast("show");
          }
        },
        error: function () {
          boxAlert("Erro ao Gravar a Contagem.", true, "");
        },
      });
    }
  }
}

function gravaMinmax(obj) {
  ordem = obj.getAttribute("data-index");
  id = jQuery("#mmi_id\\[" + ordem + "\\]").val();
  min = parseFloat(
    jQuery("#mmi_minimo\\[" + ordem + "\\]")
      .val()
      .replace(/,/g, ".")
  );
  max = parseFloat(
    jQuery("#mmi_maximo\\[" + ordem + "\\]")
      .val()
      .replace(/,/g, ".")
  );
  if (min > 0 && max > 0) {
    if (min < max) {
      prod = jQuery("#pro_id\\[" + ordem + "\\]").val();
      empr = jQuery("#empresa").val();
      url = "/EstMinmax/store";
      jQuery.ajax({
        type: "POST",
        async: true,
        dataType: "json",
        url: url,
        data: {
          mmi_id: id,
          pro_id: prod,
          mmi_minimo: min,
          mmi_maximo: max,
          emp_id: empr,
        },
        success: function (retorno) {
          if (retorno.erro) {
            boxAlert(retorno.msg, retorno.erro, "", false, 1, false);
          } else {
            jQuery("#mmi_id\\[" + ordem + "\\]").val(retorno.id);
            jQuery(".toast-body").html(retorno.msg);
            jQuery(".toast").toast({
              delay: 500,
              animation: true,
            });
            jQuery(".toast").toast("show");
          }
        },
        error: function () {
          boxAlert("Erro ao Gravar o Mínimo e Máximo.", true, "");
        },
      });
    } else {
      boxAlert(
        "Máximo tem que ser maior que o Mínimo",
        true,
        "",
        false,
        1,
        false
      );
    }
  } else if (id != "" && min == 0 && max == 0) {
    // if (min == '') {
    //     jQuery("#mmi_minimo\\[" + ordem + "\\]").val(0);
    // }
    url = "/EstMinmax/delete/" + id;
    jQuery.ajax({
      type: "POST",
      async: true,
      dataType: "json",
      url: url,
      success: function (retorno) {
        jQuery("#mmi_id\\[" + ordem + "\\]").val("");
        jQuery(".toast-body").html(retorno.msg);
        jQuery(".toast").toast({
          delay: 500,
          animation: true,
        });
        jQuery(".toast").toast("show");
      },
      error: function () {
        boxAlert("Erro ao Excluir o Mínimo e Máximo.", true, "");
      },
    });
  }
}
function atualizaProdutosMinmax() {
  bloqueiaTela();
  empr = jQuery("#empresa").val();
  url = "/EstMinmax/atualizaProdutosMinmax";
  jQuery.ajax({
    type: "POST",
    async: true,
    dataType: "json",
    url: url,
    data: { emp_id: empr },
    success: function (retorno) {
      if (retorno.erro) {
        boxAlert(retorno.msg, retorno.erro, "", false, 1, false);
      } else {
        text = "";
        retorno.campos[0].forEach((element) => {
          text += element;
        });

        jQuery("#produtosMmi").html(text);
      }
      desBloqueiaTela();
    },
    error: function () {
      desBloqueiaTela();
    },
  });
}

function atualizaProdutosPedido() {
  bloqueiaTela();
  empr = jQuery("#empresa").val();
  url = "/EstPedido/atualizaProdutosPedido";
  jQuery.ajax({
    type: "POST",
    async: true,
    dataType: "json",
    url: url,
    data: { emp_id: empr },
    success: function (retorno) {
      if (retorno.erro) {
        boxAlert(retorno.msg, retorno.erro, "", false, 1, false);
      } else {
        text = "";
        retorno.campos[0].forEach((element) => {
          text += element;
        });

        jQuery("#produtosPed").html(text);
      }
      desBloqueiaTela();
    },
    error: function () {
      desBloqueiaTela();
    },
  });
}

function atualizaProdutosCompra() {
  bloqueiaTela();
  empr = jQuery("#empresa").val();
  url = "/EstCompra/atualizaProdutosCompra";
  jQuery.ajax({
    type: "POST",
    async: true,
    dataType: "json",
    url: url,
    data: { emp_id: empr },
    success: function (retorno) {
      if (retorno.erro) {
        boxAlert(retorno.msg, retorno.erro, "", false, 1, false);
      } else {
        text = "";
        retorno.campos[0].forEach((element) => {
          text += element;
        });
        jQuery("#produtosCompra").html(text);
        jQuery("select").selectpicker();
      }
      desBloqueiaTela();
    },
    error: function () {
      desBloqueiaTela();
    },
  });
}

function atualizaCargosQuadro() {
  bloqueiaTela();
  empr = jQuery("#empresa").val();
  url = "/RhQuadro/atualizaCargosQuadro";
  jQuery.ajax({
    type: "POST",
    async: true,
    dataType: "json",
    url: url,
    data: { emp_id: empr },
    success: function (retorno) {
      if (retorno.erro) {
        boxAlert(retorno.msg, retorno.erro, "", false, 1, false);
      } else {
        text = "";
        retorno.campos[0].forEach((element) => {
          text += element;
        });

        jQuery("#cargosQua").html(text);
      }
      desBloqueiaTela();
    },
    error: function () {
      desBloqueiaTela();
    },
  });
}

function gravaCompra(obj) {
  ordem = obj.getAttribute("data-index");
  id = jQuery("#com_id\\[" + ordem + "\\]").val();
  qtia = jQuery("#cop_quantia\\[" + ordem + "\\]").val();
  erro = false;
  if (qtia > 0) {
    forn = jQuery("#for_id\\[" + ordem + "\\]").val();
    if (forn == null || forn == "") {
      boxAlert("Informe o Fornecedor", true, "", false, 1, false);
      erro = true;
    }
    unit = converteMoedaFloat(jQuery("#cop_valor\\[" + ordem + "\\]").val());
    if (unit == null || unit == "" || unit == 0) {
      boxAlert("Informe o Preço", true, "", false, 1, false);
      erro = true;
    }
    if (!erro) {
      pedi = jQuery("#ped_id\\[" + ordem + "\\]").val();
      copi = jQuery("#cop_id\\[" + ordem + "\\]").val();
      prod = jQuery("#pro_id\\[" + ordem + "\\]").val();
      unid = jQuery("#und_id\\[" + ordem + "\\]").val();
      prev = jQuery("#com_previsao\\[" + ordem + "\\]").val();
      tota = converteMoedaFloat(jQuery("#cop_total\\[" + ordem + "\\]").val());
      empr = jQuery("#empresa").val();
      url = "/EstCompra/store";
      jQuery.ajax({
        type: "POST",
        async: true,
        dataType: "json",
        url: url,
        data: {
          com_id: id,
          ped_id: pedi,
          pro_id: prod,
          cop_quantia: qtia,
          emp_id: empr,
          for_id: forn,
          com_previsao: prev,
          cop_valor: unit,
          cop_total: tota,
          cop_id: copi,
          und_id: unid,
        },
        success: function (retorno) {
          if (retorno.erro) {
            boxAlert(retorno.msg, retorno.erro, "", false, 1, false);
          } else {
            jQuery("#com_id\\[" + ordem + "\\]").val(retorno.com_id);
            jQuery("#cop_id\\[" + ordem + "\\]").val(retorno.cop_id);
            jQuery(".toast-body").html(retorno.msg);
            jQuery(".toast").toast({
              delay: 1000,
              animation: true,
            });
            jQuery(".toast").toast("show");
          }
        },
        error: function () {
          boxAlert("Erro ao Gravar a Compra.", true, "");
        },
      });
    }
  } else if (id != "") {
    if (qtia == "") {
      jQuery("#cop_quantia\\[" + ordem + "\\]").val(0);
    }
    url = "/EstCompra/delete/" + id;
    jQuery.ajax({
      type: "POST",
      async: true,
      dataType: "json",
      url: url,
      success: function (retorno) {
        jQuery("#com_id\\[" + ordem + "\\]").val("");
        jQuery("#cop_id\\[" + ordem + "\\]").val("");
        jQuery("#cop_valor\\[" + ordem + "\\]").val("");
        jQuery(".toast-body").html(retorno.msg);
        jQuery(".toast").toast({
          delay: 500,
          animation: true,
        });
        jQuery(".toast").toast("show");
      },
      error: function () {
        boxAlert("Erro ao Excluir a Compra.", true, "");
      },
    });
  }
}

function gravaCotforn(obj) {
  var $form = jQuery("form");
  // Atualiza os valores dos campos moeda para float
  jQuery(".moeda").each(function () {
    var valor = converteMoedaFloat(jQuery(this).val());
    jQuery(this).val(valor);
  });

  // Captura todos os registros válidos (cof_preco > 0)
  let dados = {};
  let valido = false;

  jQuery('input[name^="cof_precoundcompra"]').each(function (index) {
    let precoUndCompra = parseFloat(jQuery(this).val()) || 0;

    const $inputPrecoVenda = jQuery(`input[name="cof_preco[${index}]"]`);
    let precoVenda = $inputPrecoVenda.length
      ? parseFloat($inputPrecoVenda.val()) || 0
      : null;

    // Se precoVenda for válido e precoUndCompra for zero, copia o valor
    if (precoVenda !== null) {
      if (precoUndCompra > 0 && precoVenda === 0) {
        precoVenda = precoUndCompra;
        $inputPrecoVenda.val(precoVenda);
      } else if (precoVenda > 0 && precoUndCompra === 0) {
        precoUndCompra = precoVenda;
        jQuery(this).val(precoUndCompra);
      }
    }

    if (precoUndCompra > 0 || (precoVenda !== null && precoVenda > 0)) {
      valido = true;

      dados[`pro_id[${index}]`] = jQuery(
        `input[name="pro_id[${index}]"]`
      ).val();
      dados[`mar_id[${index}]`] = jQuery(
        `input[name="mar_id[${index}]"]`
      ).val();
      dados[`ctp_id[${index}]`] = jQuery(
        `input[name="ctp_id[${index}]"]`
      ).val();
      dados[`cof_precoundcompra[${index}]`] = precoUndCompra;

      if (precoVenda !== null) {
        dados[`cof_preco[${index}]`] = precoVenda;
      }

      dados[`cof_validade[${index}]`] = jQuery(
        `input[name="cof_validade[${index}]"]`
      ).val();
    }
  });

  if (!valido) {
    alert("Informe o preço de pelo menos um Produto");
    return; // Impede o envio
  }
  // Adiciona outros campos do formulário (fora dos arrays de produto)
  $form.serializeArray().forEach((field) => {
    if (
      !field.name.startsWith("pro_id") &&
      !field.name.startsWith("mar_id") &&
      !field.name.startsWith("ctp_id") &&
      !field.name.startsWith("cof_preco") &&
      !field.name.startsWith("cof_validade")
    ) {
      dados[field.name] = field.value;
    }
  });

  // var dadosForm = new FormData($form[0]);
  // var dadosForm = $form.serialize();
  // console.log(dadosForm.values);
  var url = $form.attr("action");
  jQuery.ajax({
    type: "POST",
    headers: { "X-Requested-With": "XMLHttpRequest" },
    url: url,
    // processData: false,
    // contentType: false,
    dataType: "json",
    data: dados,
    success: function (data) {
      desBloqueiaTela();
      if (!data.erro) {
        boxAlert(data.msg, data.erro, "", false, 1, false);
        carregaCotacao();
      } else {
        boxAlert(data.msg, data.erro, data.url, false, 1, false);
      }
    },
    error: function () {
      boxAlert("Erro ao Gravar a Cotação.", true, "");
    },
  });
}

function gravaQuadro(obj) {
  ordem = obj.getAttribute("data-index");
  id = jQuery("#qua_id\\[" + ordem + "\\]").val();
  vaga = jQuery("#quf_vagas\\[" + ordem + "\\]").val();
  seto = jQuery("#set_id\\[" + ordem + "\\]").val();
  qufi = jQuery("#quf_id\\[" + ordem + "\\]").val();
  cagi = jQuery("#cag_id\\[" + ordem + "\\]").val();
  sala = converteMoedaFloat(jQuery("#quf_salario\\[" + ordem + "\\]").val());
  pctp = converteMoedaFloat(
    jQuery("#quf_participacao\\[" + ordem + "\\]").val()
  );
  empr = jQuery("#empresa").val();
  url = "/RhQuadro/store";
  jQuery.ajax({
    type: "POST",
    async: true,
    dataType: "json",
    url: url,
    data: {
      qua_id: id,
      quf_id: qufi,
      cag_id: cagi,
      set_id: seto,
      quf_vagas: vaga,
      emp_id: empr,
      quf_salario: sala,
      quf_participacao: pctp,
    },
    success: function (retorno) {
      if (retorno.erro) {
        boxAlert(retorno.msg, retorno.erro, "", false, 1, false);
      } else {
        jQuery("#qua_id\\[" + ordem + "\\]").val(retorno.qua_id);
        jQuery("#quf_id\\[" + ordem + "\\]").val(retorno.quf_id);
        jQuery(".toast-body").html(retorno.msg);
        jQuery(".toast").toast({
          delay: 500,
          animation: true,
        });
        jQuery(".toast").toast("show");
      }
    },
    error: function () {
      boxAlert("Erro ao Gravar o Quadro.", true, "");
    },
  });
}

function alteraTextoInfo(primeiro, segundo, destino, texto) {
  let text1 = jQuery("#" + primeiro + " option:selected").text();
  let text2 = jQuery("#" + segundo + " option:selected").text();
  let txt = texto.replace("TEXTO1", text1);
  let txtfim = txt.replace("TEXTO2", text2);
  jQuery("#ti_" + destino).html(txtfim);
}

function duplicarSolicitacao(url) {
  $empresa = jQuery("#empresa").val();
  url = url + "/" + $empresa;
  jQuery.ajax({
    type: "POST",
    async: true,
    dataType: "json",
    url: url,
    success: function (retorno) {
      if (retorno.erro) {
        boxAlert(retorno.msg, retorno.erro, "", false, 1, false);
      } else {
        jQuery("#tb_produtos").DataTable().ajax.reload();
      }
    },
  });
}

function geraCotacao() {
  bloqueiaTela();

  const prazo = jQuery("#cot_prazo").val();
  const url = window.location.origin + "/EstCotacao/geraCotacoesPorGrupo";

  // Coletar apenas os produtos com radio marcado como "1"
  const produtosSelecionados = [];
  jQuery('input.mark[type="radio"]').each(function () {
    if (jQuery(this).prop("checked") && this.value === "1") {
      const id = this.id.replace("Cot_", "");
      produtosSelecionados.push(id);
    }
  });

  jQuery.ajax({
    type: "POST",
    async: true,
    dataType: "json",
    url: url,
    data: {
      cot_prazo: prazo,
      produtos: produtosSelecionados, // Envia os IDs selecionados
    },
    success: function (data) {
      desBloqueiaTela();
      if (!data.erro) {
        if (data.url) {
          redireciona(data.url);
        }
      } else {
        boxAlert(data.msg, data.erro, data.url, false, 1, false);
      }
    },
    error: function () {
      boxAlert("Erro ao Gerar a Cotação.", true, "");
    },
  });
}

function gravaCotacao(obj) {
  ordem = obj.getAttribute("data-index");
  indice = obj.getAttribute("data-ordemcot");
  precocompra = converteMoedaFloat(
    jQuery(
      "#cof_precoundcompra\\[" + ordem + "\\][data-ordemcot='" + indice + "']"
    ).val()
  );
  preco = converteMoedaFloat(
    jQuery(
      "#cof_preco\\[" + ordem + "\\][data-ordemcot='" + indice + "']"
    ).val()
  );
  if (parseFloat(preco) === 0) {
    preco = precocompra;
  }
  if (parseFloat(precocompra) + parseFloat(preco) === 0) {
    return;
  }
  cofid = jQuery(
    "#cof_id\\[" + ordem + "\\][data-ordemcot='" + indice + "']"
  ).val();
  valid = jQuery(
    "#cof_validade\\[" + ordem + "\\][data-ordemcot='" + indice + "']"
  ).val();
  forid = jQuery(
    "#for_id\\[" + ordem + "\\][data-ordemcot='" + indice + "'] :selected"
  ).val();
  marid = jQuery(
    "#mar_id\\[" + ordem + "\\][data-ordemcot='" + indice + "']"
  ).val();
  if (forid === null || forid === "") {
    boxAlert("Informe o Fornecedor", false, "", true, 1, false, "Atenção");
    obj.value = 0;
    // jQuery("#for_id\\[" + ordem + "\\]").focus();
  } else if (marid === null || marid === "") {
    boxAlert("Informe a Marca", false, "", true, 1, false, "Atenção");
    obj.value = 0;
  } else {
    valid = jQuery(
      "#cof_validade\\[" + ordem + "\\][data-ordemcot='" + indice + "']"
    ).val();
    cotac = jQuery(
      "#cot_id\\[" + ordem + "\\][data-ordemcot='" + indice + "']"
    ).val();
    proid = jQuery(
      "#pro_id\\[" + ordem + "\\][data-ordemcot='" + indice + "']"
    ).val();
    copid = jQuery(
      "#ctp_id\\[" + ordem + "\\][data-ordemcot='" + indice + "']"
    ).val();
    url = window.location.origin + "/EstCotacao/storeforn";
    jQuery.ajax({
      type: "POST",
      async: true,
      dataType: "json",
      url: url,
      data: {
        for_id: forid,
        validade: valid,
        cotacao: cotac,
        produto: proid,
        marca: marid,
        preco: preco,
        precocompra: precocompra,
        cop_id: copid,
        cof_id: cofid,
      },
      success: function (retorno) {
        if (retorno.erro) {
          boxAlert(retorno.msg, retorno.erro, "", false, 1, false);
        } else {
          jQuery(
            "#for_id\\[" + ordem + "\\][data-ordemcot='" + indice + "']"
          ).attr("disabled", "disabled");
          jQuery(
            "#for_id\\[" + ordem + "\\][data-ordemcot='" + indice + "']"
          ).attr("readonly", "readonly");
          jQuery(
            "#cof_id\\[" + ordem + "\\][data-ordemcot='" + indice + "']"
          ).val(retorno.ok);
          jQuery("#for_id\\[" + ordem + "\\][data-ordemcot='" + indice + "']")
            .next("button")
            .attr("aria-disabled", "true")
            .addClass("disabled");
          jQuery(".toast-body").html(retorno.msg);
          jQuery(".toast").toast({
            delay: 5000,
            animation: true,
          });
          jQuery(".toast").toast("show");
        }
      },
    });
  }
}

if (typeof itens_compra === "undefined") {
  var itens_compra = [];
}
function gravaPreCompra(obj) {
  const ordem = obj.getAttribute("data-index");
  const indice = obj.getAttribute("data-ordemcot");
  const preco = converteMoedaFloat(
    jQuery(`#cof_precoundcompra\\[${ordem}\\][data-ordemcot='${indice}']`).val()
  );
  const quantia = parseFloat(
    jQuery(`#ped_qtia\\[${ordem}\\][data-ordemcot='${indice}']`).val()
  );

  if (preco === 0) return;

  const forid = jQuery(
    `#for_id\\[${ordem}\\][data-ordemcot='${indice}'] :selected`
  ).val();
  if (!forid) {
    boxAlert("Informe o Fornecedor", false, "", true, 1, false, "Atenção");
    obj.value = 0;
    return;
  }

  const pedid = jQuery(
    `#ped_id\\[${ordem}\\][data-ordemcot='${indice}']`
  ).val();
  const proid = jQuery(
    `#pro_id\\[${ordem}\\][data-ordemcot='${indice}']`
  ).val();
  const marid = jQuery(
    `#mar_id\\[${ordem}\\][data-ordemcot='${indice}']`
  ).val();
  const previsao = jQuery(
    `#cop_previsao\\[${ordem}\\][data-ordemcot='${indice}']`
  ).val();
  const total = converteFloatMoeda(preco * quantia);

  const itemExists =
    itens_compra?.[forid]?.itens?.[pedid]?.[proid]?.[marid] !== undefined;

  if (quantia === 0) {
    if (!itemExists) return; // Se não existe, não faz nada

    // Remove o item
    delete itens_compra[forid].itens[pedid][proid][marid];

    if (Object.keys(itens_compra[forid].itens[pedid][proid]).length === 0)
      delete itens_compra[forid].itens[pedid][proid];
    if (Object.keys(itens_compra[forid].itens[pedid]).length === 0)
      delete itens_compra[forid].itens[pedid];
    if (Object.keys(itens_compra[forid].itens).length === 0) {
      delete itens_compra[forid].itens;
      delete itens_compra[forid];
    }

    return;
  }

  if (!itemExists) {
    const fornome = jQuery(
      `#for_id\\[${ordem}\\][data-ordemcot='${indice}'] :selected`
    ).text();
    let minimo = 0;

    jQuery.ajax({
      type: "POST",
      async: false,
      dataType: "json",
      url: `${window.location.origin}/Buscas/buscaDadosFornecedor`,
      data: { campo: [{ valor: forid }] },
      success: (retorno) => {
        minimo = retorno["for_minimo"] ?? 0;
      },
    });

    if (!itens_compra[forid]) {
      itens_compra[forid] = { forid, nome: fornome, minimo, itens: {} };
    }

    itens_compra[forid].itens[pedid] = itens_compra[forid].itens[pedid] || {};
    itens_compra[forid].itens[pedid][proid] =
      itens_compra[forid].itens[pedid][proid] || {};
  }

  // Atualiza ou insere item
  itens_compra[forid].itens[pedid][proid][marid] = {
    ordem,
    indice,
    pedido: pedid,
    produto: proid,
    marca: marid,
    quantia,
    previsao,
    valor: preco,
    total,
  };

  // Atualiza tabela de itens
  jQuery("#itens_tabela_compra").html("");
  for (const fornecedor of Object.values(itens_compra)) {
    const itensCount = Object.keys(fornecedor.itens).length;
    const totalFornecedor = converteFloatMoeda(
      somarValores(itens_compra[fornecedor.forid])
    );
    const minimo = converteFloatMoeda(fornecedor.minimo);

    let linha = `<tr>
      <td style='text-align:left'>${fornecedor.nome}</td>
      <td>${itensCount}</td>
      <td style='text-align:right'>${totalFornecedor}</td>
      <td style='text-align:right'>${minimo}</td>`;

    if (converteMoedaFloat(totalFornecedor) > parseFloat(fornecedor.minimo)) {
      const safeElement = structuredClone(fornecedor);
      safeElement.itens = Object.fromEntries(
        Object.entries(safeElement.itens).map(([k, v]) => [String(k), v])
      );
      linha += `<td><button class='btn btn-success' data-element='${JSON.stringify(
        safeElement
      ).replace(
        /'/g,
        "&#39;"
      )}' onclick='gravaCompraCot(this)'>Gerar Pedido</button></td>`;
    } else {
      linha += "<td></td>";
    }

    linha += "</tr>";
    jQuery("#itens_tabela_compra").append(linha);
  }
}

function somarValores(fornecedor) {
  let total = 0;

  for (let proid in fornecedor.itens) {
    for (let marid in fornecedor.itens[proid]) {
      for (let pedid in fornecedor.itens[proid][marid]) {
        const item = fornecedor.itens[proid][marid][pedid];
        if (item.total) {
          total += converteMoedaFloat(item.total);
        }
      }
    }
  }

  return total;
}

function gravaCompraCot(obj) {
  const element = JSON.parse(obj.getAttribute("data-element"));

  // Remove itens com quantia = 0
  for (let pedido in element.itens) {
    for (let produto in element.itens[pedido]) {
      for (let marca in element.itens[pedido][produto]) {
        const item = element.itens[pedido][produto][marca];
        if (item.quantia === 0) {
          delete element.itens[pedido][produto][marca];
        }
      }

      if (Object.keys(element.itens[pedido][produto]).length === 0) {
        delete element.itens[pedido][produto];
      }
    }

    if (Object.keys(element.itens[pedido]).length === 0) {
      delete element.itens[pedido];
    }
  }

  // Verifica se algum item tem mar_id vazio ou nulo
  for (let pedido in element.itens) {
    for (let produto in element.itens[pedido]) {
      for (let marca in element.itens[pedido][produto]) {
        const item = element.itens[pedido][produto][marca];
        if (!item.marca) {
          boxAlert(
            `Produto sem marca selecionada.`,
            false,
            "",
            true,
            1,
            false,
            "Atenção"
          );
          return;
        }
      }
    }
  }

  const empr = jQuery("#empresa").val();
  const url = "/EstCompraCotacao/storecot";

  jQuery.ajax({
    type: "POST",
    async: true,
    dataType: "json",
    url: url,
    data: { emp_id: empr, compras: element },
    success: function (retorno) {
      if (retorno.erro) {
        boxAlert(retorno.msg, retorno.erro, "", false, 1, false);
      } else {
        for (let pedido in element.itens) {
          for (let produto in element.itens[pedido]) {
            for (let marca in element.itens[pedido][produto]) {
              const item = element.itens[pedido][produto][marca];

              const seletor = (id) =>
                `#${id}\\[${item.ordem}\\][data-ordemcot='${item.indice}']`;

              jQuery(seletor("for_id")).attr({
                disabled: true,
                readonly: true,
              });
              jQuery(seletor("cof_id")).val(retorno.ok);
              jQuery(seletor("for_id"))
                .next("button")
                .attr("aria-disabled", "true")
                .addClass("disabled");

              [
                "cof_preco",
                "cof_precoundcompra",
                "ped_qtia",
                "com_previsao",
                "cof_validade",
              ].forEach((field) => {
                jQuery(seletor(field)).attr({ disabled: true, readonly: true });
              });

              jQuery(seletor("cof_validade"))
                .parents()
                .eq(3)
                .addClass("tdComprado");
              jQuery(obj).text("Comprado").prop("disabled", true);

              const pdfUrl = `${window.location.origin}/CriaPdf2025/PedidoCompra/${retorno.com_id}`;
              redirec_blank(pdfUrl);
            }
          }
        }

        jQuery(".toast-body").html(retorno.msg);
        jQuery(".toast").toast({ delay: 3000, animation: true }).toast("show");
      }
    },
  });
}

function checkRadioButtons(obj) {
  const value = parseInt(obj.value);

  if (value !== 0 && value !== 1) {
    console.error("O valor deve ser 0 ou 1.");
    return;
  }

  // Agrupar radio buttons pelo atributo name
  const groups = {};

  document.querySelectorAll('input.mark[type="radio"]').forEach((radio) => {
    const name = radio.name;
    if (!groups[name]) {
      groups[name] = [];
    }
    groups[name].push(radio);
  });

  // Marcar o radio com o valor correspondente e disparar o click
  for (let group in groups) {
    const radios = groups[group];
    const target = radios.find((r) => r.value != String(value));
    if (target) {
      target.checked = true;
      target.dispatchEvent(new Event("click", { bubbles: true }));
    }
  }
}

function buscarProdutoPorCodBarras() {
  const codigoBarras = jQuery("#mar_codigo")
    .val()
    .replace(/[^0-9]/g, "");

  if (codigoBarras.length > 10) {
    if (codigoBarras.length !== 13) {
      boxAlert("Código de barras inválido. Deve conter 13 dígitos.", true, "");
      return;
    }

    jQuery.ajax({
      url: window.location.origin + "/buscas/buscacodbar",
      type: "POST",
      data: { codigo: codigoBarras },
      dataType: "json",
      success: function (res) {
        if (res.erro) {
          boxAlert(res.erro, true, "");
          return;
        }

        // Aqui você pode usar os dados retornados como quiser
        console.log("Produto encontrado:", res);

        if (jQuery("#mar_nome").val() == "") {
          jQuery("#mar_nome").val(res.marca);
        }
        if (jQuery("#mar_apresenta").val() == "") {
          jQuery("#mar_apresenta").val(res.apresentacao);
        }

        text = "";
        if (res.fonte != "") {
          text += "<b>Fonte: </b>" + res.fonte + "<br>";
          text += "<b>Produto: </b>" + res.descricao + "<br>";
          text += "<b>Marca: </b>" + res.marca + "<br>";
          text += "<b>Apresentação: </b>" + res.apresentacao + "<br>";
          if (res.imagem != null) {
            text +=
              '<img src="' + res.imagem + '" style="max-width:90%" /><br>';
          }
        } else {
          text += "<b>Produto" + res.nome + "</b><br>";
        }
        jQuery("#dados_produto").html(text);
        // // Exemplo de uso:
        // jQuery("#nome_produto").val(res.nome);
        // jQuery("#marca_produto").val(res.marca);
        // jQuery("#apresentacao_produto").val(res.apresentacao);
      },
      error: function () {
        boxAlert("Erro ao consultar o código de barras.", true, "");
      },
    });
  }
}

function mudaObrigatorio(obj, regra, fields) {
  if (typeof obj === "object" && obj !== null) {
    nomecampo = obj.name;
  } else {
    nomecampo = obj;
  }
  nomecampo = nomecampo.replaceAll("[", "\\[");
  nomecampo = nomecampo.replaceAll("]", "\\]");
  valor = jQuery('input[name="' + nomecampo + '"]:checked').val();
  campos = fields.split(",");
  if (valor == regra) {
    if (jQuery.type(campos) == "array") {
      for (v = 0; v < campos.length; v++) {
        campos[v] = escIdColchetes(campos[v]);
        jQuery("#" + campos[v]).attr("required", "required");
      }
    } else {
      campos = escIdColchetes(campos);
      jQuery("#" + campos).attr("required", "required");
    }
  } else {
    if (jQuery.type(campos) == "array") {
      for (v = 0; v < campos.length; v++) {
        campos[v] = escIdColchetes(campos[v]);
        jQuery("#" + campos[v]).attr("required", false);
      }
    } else {
      campos = escIdColchetes(campos);
      jQuery("#" + campos).attr("required", false);
    }
  }
}
