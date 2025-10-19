global = typeof window !== "undefined" ? window : this;

jQuery(document).ready(function () {
  // setInterval(() => {
  //     url = '/buscas/verificaSessao';
  //     jQuery.ajax({
  //         type: 'POST',
  //         async: true,
  //         dataType: 'json',
  //         url: url,
  //         success: function (retorno) {
  //             if (!retorno.sessao) {
  //                 boxAlert('Sistema desconectado por inatividade', false, '/login', true, 1, false, 'Sistema Inativo');
  //             }
  //         },
  //         error: function (xhr, ajaxOptions, thrownError) {
  //             retorno = xhr.statusText;
  //             boxAlert('O sistema retornou o erro ' + retorno, false, '/login', true, 1, false, 'ERRO!!!');
  //         }
  //     });
  // }, 1000);

  jQuery(".div-img-etapa").mousedown(function (ev) {
    if (ev.which == 2) {
      urlimage = this.children[0].src;
      openImgDiv(urlimage, "show_image");
      ev.stopPropagation();
      ev.preventDefault();
      // these two are older ways I like to add to maximize browser compat
      ev.returnValue = false;
      ev.cancelBubble = true;
      return false; //   alert("Right mouse button clicked on element with id myId");
    }
  });
  /**
   * se a div Toast tiver conteúdo mostra por 3 segundos
   * Document Ready my_default
   */
  texto = jQuery(".toast-body").html();
  if (jQuery.trim(texto) != "") {
    jQuery(".toast").toast({
      delay: 1000,
      animation: true,
      autohide: true,
    });
    jQuery(".toast").toast("show");
  }

  jQuery(".toast").on("hide.bs.toast", function () {
    jQuery(".toast-body").html("");
  });

  jQuery("#bt_salvar").on("click", function (event) {
    bloqueiaTela();
    var form = jQuery("#form1");
    // }
    jQuery("[id*='-valid']").addClass("d-none");
    jQuery("[id*='-fival']").removeClass("d-block");
    var form = jQuery(this)[0].form;

    form.classList.add("was-validated");
    isvalido = validador(form);
    if (!isvalido) {
      desBloqueiaTela();
      event.preventDefault();
      event.stopPropagation();
    } else {
      // verificar se o campo select foi alterado
      jQuery("input[data-valid], select[data-valid]").each(async function () {
        let validar = this.getAttribute("data-valid");

        if (!validar || validar === "0") return;
        let valororig = this.getAttribute("data-valor");
        let value = jQuery(this).val();

        // Se for um array (como em selects múltiplos), converte para string
        if (Array.isArray(value)) {
          value = value.join(",");
        }

        if (valororig !== value) {
          event.preventDefault();
          event.stopPropagation();
          boxAlert(20, false, "submit", true, 1, true, "");
        }
      });
    }
  });

  /**
   * Submete o Formulário com ajax
   * Document Ready my_default
   */
  jQuery("#form1, #form_modal").submit(function (event) {
    // se não foi clicado no botão salvar, cancela o submit
    if (
      event.originalEvent.submitter.id != "bt_salvar" &&
      event.originalEvent.submitter.id != "Processar"
    ) {
      return false;
    } else {
      var form = jQuery(this);
      form.addClass("was-validated");

      var tipo = form.attr("type");
      if (tipo != "modal") {
        bloqueiaTela();
      }
      if (tipo != "normal") {
        event.preventDefault();
        // event.stopPropagation()
        jQuery(".moeda").each(function () {
          var valor = converteMoedaFloat(jQuery(this).val());
          jQuery(this).val(valor);
        });
        var disabled = form.find(":input:disabled").removeAttr("disabled");

        var dadosForm = new FormData(this);
        jQuery(".moeda").each(function () {
          var valor = converteFloatMoeda(jQuery(this).val());
          jQuery(this).val(valor);
        });
        disabled.attr("disabled", "disabled");
        console.log(dadosForm.values);
        var url = form.attr("action");
        jQuery.ajax({
          type: "POST",
          headers: { "X-Requested-With": "XMLHttpRequest" },
          url: url,
          processData: false,
          contentType: false,
          dataType: "json",
          data: dadosForm,
          success: function (data) {
            if (tipo != "modal") {
              desBloqueiaTela();
              if (!data.erro) {
                if (data.url) {
                  if (tipo == "novaguia") {
                    redirec_blank(data.url);
                  } else {
                    redireciona(data.url);
                  }
                }
              } else {
                boxAlert(data.msg, data.erro, data.url, false, 1, false);
              }
            } else {
              var myModalEl = document.getElementById("myModal");
              var modal = bootstrap.Modal.getInstance(myModalEl);
              modal.hide();
              jQuery(".toast-body").html(data.msg);
              texto = jQuery(".toast-body").html();
              if (jQuery.trim(texto) != "") {
                jQuery(".toast").toast("show");
                jQuery(".toast").toast({
                  delay: 3000,
                });
                jQuery(".toast").toast({
                  animation: true,
                });
              }
            }
          },
        });
      }
    }
  });

  // VERIFICA SE O CAMPO SOFREU ALTERNATES E CASO POSITIVO, ALTERA A VARIAVE data-alter PARA VERDADEIRO
  // ISSO PODE SER USADO NA SAÍDA DO FORMULÁRIO, PARA TESTAR SE HOUVE ALTERAÇÕES NOS DADOS
  jQuery("body").on("keypress", "input,select,textarea", function (event) {
    // if(event.keyCode > 47 && event.keyCode < 91){
    if (this.getAttribute["data-origin"] != this.value) {
      this.setAttribute("data-alter", true);
      console.log("Alterou");
    }
    // }
  });
});

/**
 * bloqueiaTela
 * Bloqueia a tela e apresenta a msg Processando...
 */
function bloqueiaTela() {
  jQuery("#bloqueiaTela").removeClass("d-none");
  jQuery("#bloqueiaTela").addClass("d-block");
  // jQuery('#bloqueiaTela').css('display', 'block');
}

/**
 * desBloqueiaTela
 * Desbloqueia a Tela
 */
function desBloqueiaTela() {
  jQuery("#bloqueiaTela").removeClass("d-block");
  jQuery("#bloqueiaTela").addClass("d-none");
  // jQuery('#bloqueiaTela').css('display', 'none');
}

/** Função nova
 *  Não faz nada é só pra exemplificar
 */
function naofaznada() {}

/**
 * redireciona
 * Redireciona a execução para a URL fornecida
 * @param {string} url - URL de destino
 */
function redireciona(url) {
  window.location.assign(url);
}

/**
 * redirec_blank
 * Abre a URL fornecida numa nova Janela
 * @param {string} url - URL de destino
 */
function redirec_blank(url) {
  window.open(url, "_blank");
}

/**
 * retorna_url
 * Retorna para a URL anterior
 *
 * @param {int} tipo - Se for igual a 1, volta para a URL anterior gravada
 *
 */
function retorna_url(tipo = 1) {
  if (tipo == 1) {
    location.href = document.referrer;
  } else if (tipo == 2) {
    location.href = history.back();
  }
}

/**
 * openUrlDiv
 * Abre a URL fornecida, dentro da Div informada
 * @param {string} url - URL de destino
 * @param {string} div - nome da Div de destino
 */
function openUrlDiv(url, div) {
  jQuery.ajax({
    type: "POST",
    async: true,
    dataType: "html",
    url: url,
    success: function (retorno) {
      jQuery("#" + div).html(retorno);
    },
    error: function (xhr, ajaxOptions, thrownError) {
      retorno = xhr.statusText;
      boxAlert(retorno, true, "", false, 2, false);
    },
  });
}

/**
 * openOpcMenu
 * Abre a URL fornecida, dentro da Div informada
 * @param {string} url - URL de destino
 * @param {string} div - nome da Div de destino
 */
function openOpcMenu(url) {
  jQuery.ajax({
    type: "POST",
    async: true,
    dataType: "html",
    url: url,
    success: function (retorno) {
      jQuery("#" + div).html(retorno);
    },
    error: function (xhr, ajaxOptions, thrownError) {
      retorno = xhr.statusText;
      boxAlert(retorno, true, "", false, 2, false);
    },
  });
}

/**
 * openPDFModal
 * Abre o PDF informado na URL, numa Janela Modal
 * @param {string} url - URL do PDF
 * @param {string} titulo - titulo da Janela Modal
 */
function openPDFModal(url, titulo) {
  jQuery("#myModalLabel").html(titulo);
  jQuery.ajax({
    type: "POST",
    async: true,
    dataType: "json",
    url: url,
    success: function (retorno) {
      pdfretornado =
        "<embed src='" +
        retorno.arquivo +
        "' type='application/pdf'  width='100%' height='400px' >";
      jQuery("#modal-body").html(pdfretornado);
      const container = document.getElementById("myModal");
      const modal = new bootstrap.Modal(container);
      modal.show();
    },
    error: function (xhr, ajaxOptions, thrownError) {
      retorno = xhr.statusText;
      boxAlert(retorno, true, "", false, 2, false);
    },
  });
}

/**
 * openModal
 * Abre a URL informada, numa Janela Modal
 * @param {string} url - URL
 * @param {string} titulo - titulo da Janela Modal
 */
function openModal(url) {
  jQuery.ajax({
    url: url,
    dataType: "html",
    success: function (data) {
      jQuery(".modal-body").html(data);
      var myModal = new bootstrap.Modal(document.getElementById("myModal"), {});
      // document.onreadystatechange = function () {
      myModal.show();
      //   myModal.addEventListener('shown.bs.modal', () => {
      //     myInput.focus()
      //   })
      // };
    },
  });
}

/**
 * openImgDiv
 * Abre a Imagem fornecida, dentro da Div informada
 * @param {string} Imagem - URL da imagem de destino
 * @param {string} div - nome da Div de destino
 */
function openImgDiv(url, div) {
  text = "";
  text += "<div id='" + div + "' onclick='removeDiv(\"" + div + "\")'>";
  text +=
    "<div class='d-block m-auto h-100 w-100 opacity-50 bg-gray-padrao position-absolute ' style='z-index: 200; top:0' >";
  text += "</div>";
  text +=
    "<div class='d-block m-auto h-75 w-75 border border-2 border-info rounded-3 shadow bg-white position-relative m-auto align-itens-center' style='z-index: 300;'>";
  text +=
    "<img src='" +
    url +
    "' class='d-block position-relative m-auto' style='max-height: 40rem;' />";
  text += "</div>";
  text += "</div>";
  jQuery("body").append(text);
}

/**
 * removeDiv
 * remove a Div Aberta
 * @param {string} div - nome da Div de destino
 */
function removeDiv(div) {
  jQuery("#" + div).remove();
}

/**
 * toogle_div
 * Mostra ou esconde uma Div
 *
 * @param {string} iddiv - id da Div
 */
function toogle_div(iddiv) {
  jQuery("#" + iddiv).toggleClass("d-none");
}

/**
 * excluir
 * Apresenta o box de alerta de exclusão, e caso confirmado, exclui o registro
 *
 * @param {url} url - url de exclusão do Registro
 * @param {string} registro - descrição do Registro que será excluído
 */

function excluir(url, registro) {
  msg =
    "Confirma a Exclusão do Registro?<br><h5 class='text-center'>" +
    registro +
    "</h5>";
  boxAlert(msg, false, url, false, 4, true);
}

/**
 * Cancela
 * Apresenta o box de alerta de exclusão, e caso confirmado, exclui o registro
 *
 * @param {url} url - url de exclusão do Registro
 * @param {string} registro - descrição do Registro que será excluído
 */

function cancela(url, registro) {
  msg =
    "Confirma o Cancelamento do Registro?<br><h5 class='text-center'>" +
    registro +
    "</h5>";
  boxAlert(msg, false, url, false, 4, true, "Cancelar Registro");
}

/**
 * ativInativ
 * Apresenta o box de alerta de Ativação/Inativação, e caso confirmado, ativa ou inativa o registro
 *
 * @param {url} url - url de exclusão do Registro
 * @param {string} registro - descrição do Registro que será excluído
 */

function ativInativ(url, registro, ativo) {
  if (ativo) {
    msg =
      "Confirma a Inativação do Registro?<br><h5 class='text-center'>" +
      registro +
      "</h5>";
    tit = "Inativação de Registro";
  } else {
    msg =
      "Confirma a Ativação do Registro?<br><h5 class='text-center'>" +
      registro +
      "</h5>";
    tit = "Ativação de Registro";
  }
  boxAlert(msg, false, url, false, 4, true, tit);
}

/**
 * boxAlert
 * Cria um box de Alerta para o Usuário
 * Se for confirmação de exclusão, a url deve ser vazia e o box retornará a opção escolhida (Sim/Não)
 *
 * @param {string} msg    - Mensagem que será exibida
 * @param {string} titulo    - Título do Alerta
 * @param {boolean} erro  - verdadeiro, caso seja uma mensagem de ERRO
 * @param {url}     url   - url que será executada no callback
 * @param {boolean} close - verdadeiro, Mostra o botão para fechar o Alerta
 * @param {integer} tipo  - Determina as características do Box 1 = Atenção, 2 = Informação
 * @param {boolean} confirma  - verdadeiro, Mostra os botões Sim ou Não para Confirmação
 */
function boxAlert(
  msg,
  erro = false,
  url,
  close = false,
  tipo = 1,
  confirma = false,
  titulo = ""
) {
  if (tipo == 1) {
    // ATENÇÃO
    titulo =
      '<h3><i class="fas fa-exclamation-triangle"></i><span class="mx-2">Atenção</span></h3>';
  } else if (tipo == 2) {
    // INFORMAÇÃO
    titulo =
      '<h3><i class="fas fa-info-circle"></i><span class="mx-2">Informação</span></h3>';
  }
  if (!confirma) {
    bootbox.alert({
      title: titulo,
      message: "<div class='text-center'><h5>" + msg + "</h5></div>",
      centerVertical: true,
      closeButton: close,
      size: "large",
      className: "rubberBand animated",
      callback: function () {
        if (!erro) {
          if (url != "") {
            redireciona(url);
          }
        }
      },
    });
  } else {
    if (titulo == "") {
      titulo =
        '<h3><i class="fas fa-question-circle"></i><span class="mx-2">Exclusão de Registro</span></h3>';
    } else {
      titulo =
        '<h3><i class="fas fa-question-circle"></i><span class="mx-2">' +
        titulo +
        "</span></h3>";
    }
    bootbox.confirm({
      title: titulo,
      message: "<div class='text-center'><h5>" + msg + "</h5></div>",
      centerVertical: true,
      size: "large",
      closeButton: false,
      swapButtonOrder: true,
      buttons: {
        cancel: {
          label: '<i class="fa fa-times"></i> Não',
          className: "btn-secondary",
        },
        confirm: {
          label: '<i class="fa fa-check"></i> Sim',
          className: "btn-danger ",
        },
      },
      callback: function (result) {
        if (result) {
          if (url.indexOf("location") > -1) {
            eval(url);
          } else {
            if (tipo == 4) {
              jQuery.ajax({
                type: "POST",
                async: true,
                dataType: "json",
                url: url,
                success: function (retorno) {
                  if (retorno.erro) {
                    boxAlert(retorno.msg, true, "", false, 1, false);
                  } else {
                    var arr = location.href.split("/");
                    var controler = arr[0] + "//" + arr[2] + "/" + arr[3];
                    location.href = controler;
                  }
                },
              });
            } else {
              redireciona(url);
            }
          }
        }
      },
    });
  }
  if (tipo == 1) {
    // ATENÇÃO
    jQuery(".modal-header").css("background-color", "red");
    jQuery(".modal-header").css("color", "yellow");
  } else if (tipo == 2) {
    // INFORMAÇÃO
    jQuery(".modal-header").css("background-color", "green");
    jQuery(".modal-header").css("color", "white");
  } else if (tipo > 2) {
    // EXCLUIR
    jQuery(".modal-header").css("background-color", "orange");
    jQuery(".modal-header").css("color", "black");
  }
}

/**
 * abre_tab
 * abre a Url fornecida como parametro, via Ajax, na Tab fornecida
 *
 * @param {url} url    -
 * @param {string} tab    -
 * @param {string} tipo    - para o caso de etapas
 */

function abre_tab(url, tab, tipo) {
  ulprin = jQuery("#tabPrincipal");
  liprin = ulprin.find("li");
  jQuery.ajax({
    type: "POST",
    async: true,
    dataType: "json",
    url: url,
    success: function (retorno) {
      if (tipo == "etapa") {
        tabpos = liprin[0].id.substring(3);
        jQuery("#li-" + tabpos).id = "li-" + tab;
        jQuery("#but-" + tabpos).id = "but-" + tab;
        jQuery("#tab-" + tabpos).id = "tab-" + tab;
        jQuery("#tab-" + tab).html(retorno);
      } else {
        if (jQuery("#li-" + tab).length) {
          jQuery("#tab-" + tab).html(retorno);
        } else {
          textli =
            "<li id='li=" +
            retorno.dados.url_amiga +
            "' class='nav-item' role='presentation'>";
          textli +=
            "    <button class='nav-link active' id='but-" +
            retorno.dados.url_amiga +
            "' data-bs-toggle='tab' data-bs-target='#tab-" +
            retorno.dados.url_amiga +
            "' type='button' role='tab' aria-controls='tab-" +
            retorno.dados.url_amiga +
            "' aria-selected='true'>";
          textli += "    <i class='far fa-hand-point-right'></i>";
          textli += "        &nbsp;-&nbsp;" + retorno.dados.title;
          textli += "    </button>";
          textli += "</li>";
          jQuery("#tabPrincipal").append(textli);
          textpn =
            "<div id='tab-" +
            retorno.dados.url_amiga +
            "' class='tab-pane-prin fade p-lg-3 p-2 active show' role='tabpanel' aria-labelledby='tab-" +
            retorno.dados.url_amiga +
            "' tabindex='0'>";
          textpn += retorno.html;
          textpn += "</div>";
          jQuery("#principal").append(textpn);
        }
      }
    },
    error: function (xhr, ajaxOptions, thrownError) {
      retorno = xhr.statusText;
      boxAlert(retorno, true, "", false, 2, false);
    },
  });
}

/**
 * removerTags
 * Remove os Tags Html do texto fornecido como parâmetro
 *
 * @param {string} html    - Texto original
 * @return {string} texto sem tags
 */
function removerTags(html) {
  const data = new DOMParser().parseFromString(html, "text/html");
  return data.body.textContent || "";
}

/**
 * tiraAcentos
 * Remove os Acentos do texto fornecido como parâmetro
 *
 * @param {string} txt    - Texto original
 * @return {string} texto sem acentos
 */
function tiraAcentos(txt) {
  if (txt != null && txt != "" && txt != undefined) {
    var i = txt.toLowerCase().trim();

    var acentos = "ãáàâäéèêëíìîïõóòôöúùûüç";
    var sem_acentos = "aaaaaeeeeiiiiooooouuuuc";

    for (var x = 0; x < i.length; x++) {
      var str_pos = acentos.indexOf(i.substr(x, 1));
      if (str_pos != -1) {
        i = i.replace(acentos.charAt(str_pos), sem_acentos.charAt(str_pos));
      }
    }

    return i;
  } else {
    return txt;
  }
}

/**
 * Cancelar
 * Cancela a Edição de Dados atual e retorna para a tela de listagem
 * Caso existam informações não Salvas mostra o Box de Confirmação do cancelamento
 *
 */
function cancelar() {
  var arr = location.href.split("/");
  var controler = arr[0] + "//" + arr[2] + "/" + arr[3];
  var elemAlterado = "false";
  jQuery("input, select, textarea").each(function () {
    elemAlterado = this.getAttribute("data-alter"); // Referência do elemento atual
    if (elemAlterado == "true") {
      return false;
    }
  });
  if (elemAlterado == "true") {
    msg =
      "Algumas informações desta página, ainda não foram salvas!!! <br> Confirmando a saída, essas informações serão perdidas definitivamente!<br><br>Confirma Saída?";
    boxAlert(
      msg,
      false,
      "location.href = '" + controler + "'",
      false,
      3,
      true,
      "Registro Alterado"
    );
  } else {
    location.href = controler;
  }
}

function setCookie(name, value, duration) {
  var cookie =
    name +
    "=" +
    escape(value) +
    (duration ? "; duration=" + duration.toGMTString() : "") +
    ";path=/";

  document.cookie = cookie;
}

function getCookie(name) {
  var cookies = document.cookie;
  var prefix = name + "=";
  var begin = cookies.indexOf("; " + prefix);

  if (begin == -1) {
    begin = cookies.indexOf(prefix);

    if (begin != 0) {
      return null;
    }
  } else {
    begin += 2;
  }

  var end = cookies.indexOf(";", begin);

  if (end == -1) {
    end = cookies.length;
  }

  return unescape(cookies.substring(begin + prefix.length, end));
}

function deleteCookie(name) {
  if (getCookie(name)) {
    document.cookie = name + "=" + "; expires=Thu, 01-Jan-70 00:00:01 GMT";
  }
}

function isValidDate(dateString) {
  let date = new Date(dateString);
  return !isNaN(date.getTime());
}

async function permiteNotificacao() {
  if (!("Notification" in window)) {
    console.warn("Este navegador não suporta notificações.");
    return false;
  }

  if (Notification.permission === "granted") {
    return true;
  }

  if (Notification.permission === "denied") {
    console.warn("As notificações estão bloqueadas pelo usuário.");
    return false;
  }

  try {
    const permissao = await Notification.requestPermission();
    if (permissao === "granted") {
      return true;
    } else {
      console.log("O usuário negou a permissão de notificações.");
      return false;
    }
  } catch (erro) {
    console.error("Erro ao solicitar permissão de notificações:", erro);
    return false;
  }
}

async function showNotification(texto) {
  const permitido = await permiteNotificacao();

  if (permitido) {
    const options = {
      body: texto,
      dir: "ltr",
      icon: "/assets/images/alert.png",
    };

    new Notification("Nova Notificação", options);
  }
}

function verificaNotificacao() {
  var usuarioatual = jQuery("#usu_id").val();
  if (usuarioatual != "") {
    var url = "/Notifica/verNotifica";
    let dados = { usuario: usuarioatual };
    retornoAjax = false;
    executaAjax(url, "json", dados);
    if (retornoAjax) {
      if (retornoAjax.novo > 0) {
        jQuery("#bt_notifica").removeClass("btn-outline-light");
        jQuery("#bt_notifica").removeClass("disabled");
        jQuery("#bt_notifica").addClass("btn-outline-warning");

        jQuery("#badgenotif").removeClass("d-none");

        jQuery("#show_notifica").html(retornoAjax.html);
        jQuery("#badgenotif").html(retornoAjax.novo);
      } else {
        jQuery("#bt_notifica").removeClass("btn-outline-warning");

        jQuery("#bt_notifica").addClass("btn-outline-light");
        jQuery("#bt_notifica").addClass("disabled");

        jQuery("#badgenotif").addClass("d-none");

        jQuery("#show_notifica").html("");
        jQuery("#badgenotif").html("");
        // jQuery('#show_notifica').removeClass('show');
      }
    }
  }
}

function viuNotifica($id) {
  var url = "/Notifica/viuNotifica";
  var dados = { id: $id };
  retornoAjax = false;
  executaAjax(url, "json", dados);
  if (retornoAjax) {
    verificaNotificacao();
  }
}

/**
 * executaAjax
 * Executa uma requisição ajax, e retorna o resultado pra quem chamou, ou executa a função informada
 * @param {string} urldest - URL de destino
 * @param {string} tipo - Tipo de Retorno (html, json)
 * @param {array} dados - dados a serem informados
 * @return {Object} retorno
 */
function executaAjax(urldest, tipo = "json", dados = {}, funcao = "") {
  bloqueiaTela();
  var options = {};
  if (dados.entries != undefined) {
    options = {
      method: "POST",
      url: urldest,
      data: dados,
      headers: { "X-Requested-With": "XMLHttpRequest" },
      processData: false,
      contentType: false,
      async: false,
      dataType: tipo,
      beforeSend: function () {
        bloqueiaTela();
      },
      complete: function () {
        desBloqueiaTela();
      },
    };
  } else {
    options = {
      method: "POST",
      cache: false,
      url: urldest,
      data: dados,
      async: false,
      dataType: tipo,
      beforeSend: function () {
        bloqueiaTela();
      },
      complete: function () {
        desBloqueiaTela();
      },
    };
  }
  var request = jQuery.ajax(options);

  request.done(function (retorno) {
    retornoAjax = retorno;
    if (typeof window[funcao] === "function") {
      window[funcao];
    }
  });
  request.fail(function (jqXHR, textStatus) {
    retornoAjax = false;
    if (jqXHR.responseJSON != undefined) {
      msgerro =
        "Erro <br> Código " +
        jqXHR.responseJSON.code +
        "<br> Arquivo: " +
        jqXHR.responseJSON.file +
        "<br> Linha: " +
        jqXHR.responseJSON.line +
        "<br> Mensagem: " +
        jqXHR.responseJSON.message;
    } else {
      msgerro = jqXHR.responseText;
    }
    boxAlert(msgerro, true, "", false, 3, false);
  });
  return retornoAjax;
}

/**
 * executaAjax
 * Executa uma requisição ajax, e retorna o resultado pra quem chamou, ou executa a função informada
 * @param {string} urldest - URL de destino
 * @param {string} tipo - Tipo de Retorno (html, json)
 * @param {array} dados - dados a serem informados
 * @return {Object} retorno
 */
function executaAjaxWait(urldest, tipo = "json", dados = {}, funcao = "") {
  executandoAjax = true;
  // Bloqueia a tela
  bloqueiaTela();

  return new Promise((resolve, reject) => {
    jQuery.ajax({
      method: "POST",
      cache: false,
      url: urldest,
      data: dados,
      dataType: tipo,
      success: function (retorno) {
        resolve(retorno);
      },
      error: function (jqXHR, textStatus) {
        let msgerro;
        if (jqXHR.responseJSON !== undefined) {
          msgerro =
            "Erro <br> Código " +
            jqXHR.responseJSON.code +
            "<br> Arquivo: " +
            jqXHR.responseJSON.file +
            "<br> Linha: " +
            jqXHR.responseJSON.line +
            "<br> Mensagem: " +
            jqXHR.responseJSON.message;
        } else {
          msgerro = jqXHR.responseText;
        }
        boxAlert(msgerro, true, "", false, 3, false);
        reject(false);
      },
      complete: function () {
        // Desbloqueia a tela após a conclusão
        executandoAjax = false;
        desBloqueiaTela();
      },
    });
  });
}

function validador(form) {
  let valido = true;

  jQuery("input").each(function () {
    const $el = jQuery(this);
    if ($el.closest(".d-none").length > 0) {
      $el.prop("required", false);
      $el.removeAttr("pattern");
      $el.removeClass("is-valid is-invalid");
    }
  });

  if (!form.checkValidity()) {
    jQuery(".was-validated :invalid").each(function () {
      const el = this;
      const $el = jQuery(el);

      const nid = escIdColchetes(el.id);
      const tab = escIdColchetes(el.closest(".tab-pane")?.id);
      const $erro = jQuery("#" + nid + "-fival");
      const $tabVal = jQuery("#" + tab + "-valid");

      $erro.removeClass("d-none").addClass("d-block");
      if ($tabVal.length) $tabVal.removeClass("d-none");

      const nome = el.title || "";
      let mensagens = [];

      if (el.validity.badInput) {
        mensagens.push("Valor inserido é inválido");
      }
      if (el.validity.patternMismatch) {
        mensagens.push("Valor não corresponde ao formato esperado agora");
      }
      if (el.validity.rangeOverflow) {
        mensagens.push("Valor máximo permitido é " + el.max);
      }
      if (el.validity.rangeUnderflow) {
        mensagens.push("Valor mínimo permitido é " + el.min);
      }
      if (el.validity.stepMismatch) {
        mensagens.push("Valor não é permitido para este intervalo.");
      }
      if (el.validity.tooLong) {
        mensagens.push(
          "O campo " +
            nome +
            ", aceita no máximo " +
            el.maxLength +
            " caracteres"
        );
      }
      if (el.validity.tooShort) {
        mensagens.push(
          "Digite pelo menos " + el.minLength + " caracteres para " + nome
        );
      }
      if (el.validity.typeMismatch) {
        mensagens.push("Valor não corresponde ao tipo esperado");
      }
      if (el.validity.valueMissing) {
        mensagens.push(nome + " é Obrigatório!");
      }

      const mensagemFinal = mensagens.join("<br>");
      $erro.html(mensagemFinal);

      valido = false;
    });
  }

  return valido;
}

function escIdColchetes(id) {
  if (typeof id !== "string") return id;

  // Só escapa se ainda não estiver escapado
  return id
    .replace(/(?<!\\)\[/g, "\\[") // escapa [ se não tiver barra antes
    .replace(/(?<!\\)\]/g, "\\]"); // escapa ] se não tiver barra antes
}

function mostranoToast(texto, alerta = false) {
  if (texto != "") {
    showToast(texto, alerta);
  }
  showNotification(texto);
}
function getContrastYIQ(hexcolor) {
  hexcolor = hexcolor.replace("#", "");
  const r = parseInt(hexcolor.substr(0, 2), 16);
  const g = parseInt(hexcolor.substr(2, 2), 16);
  const b = parseInt(hexcolor.substr(4, 2), 16);
  const yiq = (r * 299 + g * 587 + b * 114) / 1000;
  return yiq >= 128 ? "black" : "white";
}

function rgbToHex(rgb) {
  const result = rgb.match(/\d+/g);
  return result
    ? "#" + result.map((x) => (+x).toString(16).padStart(2, "0")).join("")
    : "#000000";
}

function showToast(message, isAlert = false) {
  let msg_id = null;
  let titulo = "Informação";
  let corpo = message;
  let fundo = isAlert ? "#dc3545" : "#198754";
  let classeFundo = isAlert ? "bg-danger" : "bg-success";
  let icone = `<i class="bi ${
    isAlert ? "bi-exclamation-triangle" : "bi-info-circle"
  }"></i>`;
  let tituloHTML = `${icone} ${titulo}`;

  if (!isNaN(message)) {
    msg_id = msg_cfg[parseInt(message) - 1];
    if (msg_id) {
      titulo = msg_id.msg_titulo || titulo;
      corpo = msg_id.msg_mensagem || corpo;
      tituloHTML = msg_id.msg_desc_tipo || `${icone} ${titulo}`;
      if (msg_id.msg_cor) {
        if (msg_id.msg_cor.startsWith("#")) {
          fundo = msg_id.msg_cor;
          classeFundo = "";
        } else {
          classeFundo = msg_id.msg_cor;
          fundo = null; // será determinado via CSS real
        }
      }
    }
  }

  const container = document.getElementById("toast-container");
  const toast = document.createElement("div");
  toast.className = `toast align-items-center ${classeFundo}`.trim();

  // Aplicar contraste adequado ao texto
  let textoCor = "white";
  if (fundo && fundo.startsWith("#")) {
    toast.style.backgroundColor = fundo;
    textoCor = getContrastYIQ(fundo);
    toast.style.color = textoCor;
  } else if (classeFundo) {
    // cria elemento temporário para obter a cor real
    const temp = document.createElement("div");
    temp.className = classeFundo;
    temp.style.display = "none";
    document.body.appendChild(temp);

    const computed = getComputedStyle(temp).backgroundColor;
    const hex = rgbToHex(computed);
    textoCor = getContrastYIQ(hex);
    toast.classList.add(`text-${textoCor}`);
    document.body.removeChild(temp);
  }

  // Cabeçalho
  const header = document.createElement("div");
  header.className = "toast-header";
  header.style.color = fundo; // título sempre preto

  const title = document.createElement("strong");
  title.className = "me-auto";
  title.innerHTML = tituloHTML;

  const closeBtn = document.createElement("button");
  closeBtn.type = "button";
  closeBtn.className = "btn-close";
  closeBtn.style.color = "black";
  closeBtn.setAttribute("aria-label", "Fechar");

  header.appendChild(title);
  header.appendChild(closeBtn);

  // Corpo
  const body = document.createElement("div");
  body.className = "toast-body";
  body.textContent = corpo;

  toast.appendChild(header);
  toast.appendChild(body);
  container.appendChild(toast);

  // Força reflow e ativa a animação
  void toast.offsetHeight;
  toast.classList.add("toast-animar");

  const timeout = setTimeout(() => {
    toast.classList.remove("toast-animar");
    toast.addEventListener("transitionend", () => toast.remove());
  }, 4000);

  closeBtn.addEventListener("click", () => {
    clearTimeout(timeout);
    toast.classList.remove("toast-animar");
    toast.addEventListener("transitionend", () => toast.remove());
  });
}

function rhDemitir(url) {
  msg = "Confirma a Demissão do Colaborador?";
  boxAlert(msg, false, url, false, 4, true, "Demissão");

  // jQuery.ajax({
  //   type: "GET",
  //   async: true,
  //   dataType: "json",
  //   url: url,
  //   success: function (retorno) {
  //     if (!retorno.erro) {
  //       window.location.reload();
  //     } else {
  //       boxAlert(retorno.msg, true, "", false, 2, false);
  //     }
  //   },
  //   error: function (xhr, ajaxOptions, thrownError) {
  //     retorno = xhr.statusText;
  //     boxAlert(retorno, true, "", false, 2, false);
  //   },
  // });
}
