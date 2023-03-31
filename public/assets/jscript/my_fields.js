
jQuery(function() {
    // var start = moment().subtract(6, 'days');
    // var end = moment();

    // function cb(start, end) {
    //     jQuery('.daterange').html(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
    // }

    // jQuery('.daterange').daterangepicker({
    //     "alwaysShowCalendars": true,
    //     startDate: start,
    //     endDate: end,
    //     ranges: {
    //        'Hoje': [moment(), moment()],
    //        'Ontem': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
    //        'Últimos 7 dias': [moment().subtract(6, 'days'), moment()],
    //        'Próximos 7 dias': [moment(), moment().add(6, 'days')],
    //        'Últimos 30 dias': [moment().subtract(29, 'days'), moment()],
    //        'Próximos 30 dias': [moment(), moment().add(29, 'days')],
    //        'Mês atual': [moment().startOf('month'), moment().endOf('month')],
    //        'Mês anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
    //        'Mês seguinte': [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')],
    //     },
    //     "locale": {
    //         "format": "DD/MM/YYYY",
    //         "customRangeLabel": "Período",
    //         "applyLabel": "Aplicar",
    //         "cancelLabel": "Cancela",
    //         "daysOfWeek": [
    //             "Dom",
    //             "Seg",
    //             "Ter",
    //             "Qua",
    //             "Qui",
    //             "Sex",
    //             "Sab"
    //         ],
    //         "monthNames": [
    //             "Janeiro",
    //             "Fevereiro",
    //             "Março",
    //             "Abril",
    //             "Maio",
    //             "Junho",
    //             "Julho",
    //             "Agosto",
    //             "Setembro",
    //             "Outubro",
    //             "Novembro",
    //             "Dezembro"
    //         ],
    //         "firstDay": 1
    //     },
    // }, cb);

    // cb(start, end);
});


jQuery(document).ready(function() {
    /**
     * .editor summernote()
     * Se existir um campo Editor, aplica o summernote do Bootstrap
     * Document Ready my_fields
     */
    if(jQuery('input, textarea').hasClass('editor')){ 
        jQuery('.editor').summernote({
            height: 200,
            lang: 'pt-BR',
            callbacks: {
                onImageUpload: function(files, editor, welEditable) {
                    sendFile(files[0], this);
                } 
            },
        });;
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
          url: '/Util/upload',
          cache: false,
          contentType: false,
          processData: false,
          success: function(url) {
            jQuery(el).summernote('editor.insertImage', url);
          }
        });
    };;

    /**
     * Clique no Campo Editor
     * Document Ready my_fields
     */
    jQuery(".note-editor .dropdown-toggle").on("click",(e) => { 
        if(jQuery( e . currentTarget ) . attr ( "aria-expanded" ) )  { 
            jQuery( e . currentTarget ) . dropdown ( "toggle" ) ;
            jQuery(e.currentTarget.nextElementSibling).toggleClass("show");
        } 
    });;

    /**
     * Clique no Menu
     * Mostra ou oculta elemento pelo clique
     * Document Ready my_fields
     */
    jQuery(".dropdown-menu").on("click",(e) => { 
        jQuery( e . currentTarget ) . toggleClass ( "show" ) 
    });;
    
    /**
     * Show Password
     * Mostra ou oculta a Senha
     * Document Ready my_fields
     */
    jQuery('.show_password').hover(function(e) {
        e.preventDefault();
        field = this.getAttribute('data-field');
        alvo = document.getElementById(field);
        if (jQuery(alvo).attr('type') == 'password' ) {
            jQuery(alvo).attr('type', 'text');
            jQuery('#ada_'+alvo.id).removeClass('bi bi-eye-slash-fill');
            jQuery('#ada_'+alvo.id).addClass('bi bi-eye');
        } else {
            jQuery(alvo).attr('type', 'password');
            jQuery('#ada_'+alvo.id).removeClass('bi bi-eye');
            jQuery('#ada_'+alvo.id).addClass('bi bi-eye-slash-fill');
        }
    });;

    /**
     * Clique CheckBox
     * Coloca ou tira a classe Checked no checkbox
     * Document Ready my_fields
     */
    jQuery('input[type="checkbox"]').click(function(){
        var id = jQuery(this).attr('id');
        var checked =jQuery(this).prop("checked");
        mod = id.match(/[0-9]+/g)[0];
        cla = id.match(/[0-9]+/g)[1];
        if(cla == 0){ // todas as classes
            var opcao = id.substr(0,id.indexOf('['));
            if(opcao == 'pit_all'){
                jQuery("input[id^='pit_consulta["+mod+"]']").prop("checked", checked); 
                jQuery("input[id^='pit_adicao["+mod+"]']").prop("checked", checked); 
                jQuery("input[id^='pit_edicao["+mod+"]']").prop("checked", checked); 
                jQuery("input[id^='pit_exclusao["+mod+"]']").prop("checked", checked); 
                jQuery("input[id^='pit_all["+mod+"]']").prop("checked", checked); 
            } else {
                jQuery("input[id^='"+opcao+"["+mod+"]']").prop("checked", checked); 
            }
        } else {
            var opcao = id.substr(0,id.indexOf('['));
            if(opcao == 'pit_all'){
                jQuery('#pit_consulta\\['+mod+'\\]\\['+cla+'\\]').prop("checked", checked); 
                jQuery('#pit_adicao\\['+mod+'\\]\\['+cla+'\\]').prop("checked", checked); 
                jQuery('#pit_edicao\\['+mod+'\\]\\['+cla+'\\]').prop("checked", checked); 
                jQuery('#pit_exclusao\\['+mod+'\\]\\['+cla+'\\]').prop("checked", checked); 
            }
        }
    });;

    /**
     * Campo Select Dual
     * Se existir um campo Form dual, aplica o DualListBox do Bootstrap
     * Document Ready my_fields
     */
    if (jQuery(".form-dual")[0]){
        jQuery('.form-dual').bootstrapDualListbox({});
    };;

    /**
     * Campo Icone
     * Se existir um campo Icone, aplica o inconPicker
     * Document Ready my_fields
     */
    if (jQuery(".icone")[0]){
        jQuery('.icone').iconpicker();
    };;

    /**
     * Disable Option Select
     * desabilita a opção do Select, se o valor for -1
     * Document Ready my_fields
     */
    if(jQuery("select")[0]){
        jQuery("select option").each(function() {
            var value = this.value;
            if (value == '-1'){
                jQuery(this).prop('disabled', 'true');
            }
        });
    };;

    /**
     * Campo Selpic
     * Se existir um campo selpic, aplica a Select Picker
     * Document Ready my_fields
     */
    if (jQuery(".selpic")[0]){
        jQuery('.selpic').selectpicker();
    };;

    /**
     * Campo Select Dependente
     * Se existir um campo dependente, adiciona o onchange no elemento pai
     * Document Ready my_fields
     */
    jQuery(".dependente").each(function() {
        elemen = jQuery(this)[0];
        busca = elemen.getAttribute('data-busca');
        funcao_busca = "busca_dependente(this,'"+elemen.name+"','"+busca+"','"+elemen.value+"')";
        pai   = elemen.getAttribute('data-pai');
        _chan_ant = jQuery("#"+pai).attr('onchange');
        if(_chan_ant != '' && _chan_ant != undefined){
            jQuery("#"+pai).attr('onchange',_chan_ant+';'+funcao_busca);
        } else {
            jQuery("#"+pai).attr('onchange',funcao_busca);
        }
    });;

    /**
     * Campo Select Desabilitado
     * Desabilita as opções com valor vazio do Select, se o Select for obrigatório
     * Document Ready my_fields
     */
    jQuery("select:required option[value='']").attr("disabled","disabled");
    ;;

})


/**
 * buscar
 * Cria a caixa de listagem, com os resultados da busca do campo "selbusca"
 * @param {string} url - URL de pesquisa
 * @param {object} obj - Campo de Busca 
 * @param {string} lista - Lista de Opções do Objeto
 */
function buscar(url, obj, lista){
    var busca = jQuery(obj).val();
    if(busca.length >= 3){
        jQuery("#dd_"+lista).empty();
        jQuery("#dd_"+lista).append("<li><h6 class='dropdown-header disabled'>Buscando...</h6></li>");
        jQuery("#dd_"+lista).css({'position': 'absolute', 'inset': '0px auto auto 0px', 'margin': '0px', 'transform': 'translate(0px, 40px)'});
        jQuery("#dd_"+lista).show('slow');
        jQuery.ajax({
            type: 'POST',
            async: true,
            dataType: 'json',
            url: url,
            data: {'busca': busca},
            success: function (retorno) {
                jQuery("#dd_"+lista).empty();
                jQuery.each(retorno, function(i, item) {
                    jQuery("#dd_"+lista).append("<li><a class='dropdown-item' onclick='seleciona_item(\""+item.id+"\",\""+item.text+"\",\""+lista+"\")'>"+item.text+"</a></li>");
                });
            },
        });
    } else {
        jQuery("#dd_"+lista).empty();
    }
};;


/**
 * seleciona_item
 * trata a seleção de um ítem de um campo do tipo "selbusca"
 * @param {string} id - id do item selecionado
 * @param {string} texto - Texto do item selecionado
 * @param {object} obj - Campo de Busca 
 */
function seleciona_item(id, texto, obj){
    jQuery("#dd_"+obj).css({'position': '', 'inset': '', 'margin': '', 'transform': ''});
    jQuery("#dd_"+obj).hide('slow');
    jQuery('#bus_'+obj).val(texto);
    jQuery('#'+obj).val(id);
    jQuery('#'+obj).trigger('change');
    
};;

/**
 * exclui_campo
 * Exclui um campo, ou uma lista de campos, para adição de ítens
 * @param {string} objdest - nome da div de destino
 * @param {object} obj - Campo que deverá ser excluído 
 */

function exclui_campo(objdest, obj) {
    indice = obj.getAttribute('data-index');
    pos = indice.indexOf('__');
    index = indice.substr(pos+2, indice.length);

    visiveis = jQuery('.rep_campos').not('.d-none').length - 1;

    jQuery('*[data-' + objdest + '-index="' + index + '"]').each(function (i, t) {
        jQuery(this).find('[id*="__'+index+'"]').each(function () {
            jQuery(this).val('');
        });
    });

    jQuery('*[data-' + objdest + '-index="' + index + '"]').addClass('d-none');

    acerta_botoes_rep();
};;

/**
 * repete_campo
 * repete um campo, ou uma lista de campos, para adição de ítens
 * @param {string} objdest - nome da div de destino
 * @param {object} obj - Campo que deverá ser repetido 
 */
function repete_campo(objdest, obj) {
    var objrep = obj.parentElement;
    var objpre = objdest.substr(0, 3);

    index = jQuery(".rep_" + objdest).length;
    indat = jQuery(".rep_" + objdest).length - 1;

    var $template = jQuery('*[data-' + objdest + '-index="0"]');
    var $pai = $template[0].parentElement;
    $clone = $template
            .clone()
            .attr('data-' + objdest + '-index', index)
            .appendTo($pai);

    jQuery('*[data-' + objdest + '-index="' + index + '"]').removeClass('d-none');
    // Update the name attributes
    jQuery('*[data-' + objdest + '-index="' + index + '"]').each(function (i, t) {
        var primeiro = true;
        jQuery(this).find('[id*="__0"]').each(function () {
            if(!jQuery(this).hasClass('form-check-input')){
                jQuery(this).val('');
            }

            $nome = jQuery(this)[0].name;
            if ($nome == undefined || $nome == '') {
                $nome = jQuery(this).attr('id');
            }
            if ($nome != undefined) {
                pos = $nome.indexOf('__');
                ini = $nome.substr(0, $nome.indexOf('__'));
                fim = $nome.substr(pos + 3);
                // fim = $nome.substr(pos+String(index).length+2);
                jQuery(this).attr('name', ini + '__' + index + fim);
                jQuery(this).attr('id', ini + '__' + index + fim);
            }
            // var tipo = jQuery('#'+ini+'__'+index+fim).attr('type');
            var tipo = jQuery(this)[0].type;
            if(primeiro && tipo != 'hidden' && tipo != undefined){
                jQuery('#'+ini+'__'+index+fim).focus();
                jQuery('#'+ini+'__'+index+fim).trigger('click');
                primeiro = false;
            }

            // Atualiza o evento onchange 
            // Ocorre quando o campo é alterado
            var texto = jQuery(this).attr('onchange');
            if (texto != undefined) {
                var ocor = (texto.match(/__0/g) || []).length;
                if(ocor > 0){
                    var novotexto = texto.replace(/__0/g, '__'+index); 
                    jQuery(this).attr('onchange', novotexto);
                }
            }
            // Atualiza a tag for (refere a label)
            var texto = jQuery(this).attr('for');
            if (texto != undefined) {
                var ocor = (texto.match(/__0/g) || []).length;
                if(ocor > 0){
                    var novotexto = texto.replace(/__0/g, '__'+index); 
                    jQuery(this).attr('for', novotexto);
                }
            }
            // Atualiza o evento onfocus
            // Ocorre quando o campo recebe o foco
            var texto = jQuery(this).attr('onfocus');
            if (texto != undefined) {
                var ocor = (texto.match(/__0/g) || []).length;
                if(ocor > 0){
                    var novotexto = texto.replace(/__0/g, '__'+index); 
                    jQuery(this).attr('onfocus', novotexto);
                }
            }
            // Atualiza o evento onkeyup 
            // Ocorre quando a Tecla pressionada foi solta
            // Ultimo evento disparado no pressionamento de uma tecla
            var texto = jQuery(this).attr('onkeyup');
            if (texto != undefined) {
                var ocor = (texto.match(/__0/g) || []).length;
                if(ocor > 0){
                    var novotexto = texto.replace(/__0/g, '__'+index); 
                    jQuery(this).attr('onkeyup', novotexto);
                }
            }
            // Atualiza o evento onkeydown 
            // Ocorre no momento que uma Tecla é pressionada
            // Primeiro evento disparado no pressionamento de uma tecla
            var texto = jQuery(this).attr('onkeydown');
            if (texto != undefined) {
                var ocor = (texto.match(/__0/g) || []).length;
                if(ocor > 0){
                    var novotexto = texto.replace(/__0/g, '__'+index); 
                    jQuery(this).attr('onkeydown', novotexto);
                }
            }
            // Atualiza o evento onkeypress 
            // Ocorre depos do Pressionamento de uma Tecla
            // Segundo evento disparado no pressionamento de uma tecla
            var texto = jQuery(this).attr('onkeypress');
            if (texto != undefined) {
                var ocor = (texto.match(/__0/g) || []).length;
                if(ocor > 0){
                    var novotexto = texto.replace(/__0/g, '__'+index); 
                    jQuery(this).attr('onkeypress', novotexto);
                }
            }
            // Atualiza o evento onblur
            // Ocorre quando o campo perde o foco
            var texto = jQuery(this).attr('onblur');
            if (texto != undefined) {
                var ocor = (texto.match(/__0/g) || []).length;
                if(ocor > 0){
                    var novotexto = texto.replace(/__0/g, '__'+index); 
                    jQuery(this).attr('onblur', novotexto);
                }
            }
            // Atualiza o atributo data-retorno
            var texto = jQuery(this).attr('data-retorno');
            if (texto != undefined) {
                var ocor = (texto.match(/__0/g) || []).length;
                if(ocor > 0){
                    var novotexto = texto.replace(/__0/g, '__'+index); 
                    jQuery(this).attr('data-retorno', novotexto);
                }
            }
            // Atualiza o atributo data-refer
            var texto = jQuery(this).attr('data-refer');
            if (texto != undefined) {
                var ocor = (texto.match(/__0/g) || []).length;
                if(ocor > 0){
                    var novotexto = texto.replace(/__0/g, '__'+index); 
                    jQuery(this).attr('data-refer', novotexto);
                }
            }
             // Atualiza o atributo data-index
            var texto = jQuery(this).attr('data-index');
            if (texto != undefined) {
                var ocor = (texto.match(/__0/g) || []).length;
                if(ocor > -1){
                    var novotexto = texto.replace(/__0/g, '__'+index); 
                    jQuery(this).attr('data-index', novotexto);
                }
            }
        })
    });
    acerta_botoes_rep();
    return;
};;

/** 
 * acerta_botoes_rep
 * Acerta Botões de Adicionar e Excluir campos
 * 
 */
function acerta_botoes_rep(){
    visiveis = jQuery('.rep_campos').not('.d-none').length - 1;

    jQuery('.bt-exclui').removeClass('d-none');
    jQuery('.bt-repete').addClass('d-none');

    if(visiveis == 0){
        jQuery('.bt-exclui').each(function (i) {
            jQuery(this).addClass('d-none');
        });
        jQuery('.bt-repete').each(function (i) {
            jQuery(this).removeClass('d-none');
        });
    } else {
        jQuery('.rep_campos').not('.d-none').each(function (i) {
            if(i == visiveis){
                jQuery(this).find('.bt-repete').each(function () {
                    jQuery(this).removeClass('d-none');
                });
            }
        });
    }

};;


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
};;


/**
 * busca_dependente
 * Preenche a lista de opções de um campo dependente, conforme a seleção do campo pai
 * @param {object} obj - campo pai
 * @param {object} id_dep - campo dependente
 * @param {url} url_busca - URL de busca de dependentes
 * @param {integer} selec - Dependente pré-selecionado  
 */
function busca_dependente(obj, id_dep, url_busca, selec) {
    var id_res = obj.id;
    var busca = document.getElementById(id_res);
    var buObj = jQuery(busca);
    if (parseInt(jQuery(obj).val()) > 0) {
        var nodes = document.getElementById(id_dep);
        var jqObj = jQuery(nodes);

        jQuery(jqObj).children('option:not(:first)').remove();
        jQuery(jqObj).append(jQuery("<option></option>").attr("value", -1).text('Carregando'));
        var datarr = new Array();
        datarr[0] = {};
        datarr[0].id_dep = jQuery(obj).val();
        jQuery.ajax({
            type: 'POST',
            async: false,
            dataType: 'json',
            url: url_busca,
            data: {campo: datarr},
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

                jQuery(jqObj).children('option').remove();
                if (selec == -1) {
                    jQuery(jqObj).append(jQuery("<option selected></option>").attr("value", -1).text('Escolha ' + nodes.getAttribute('data-label')));
                } else {
                    jQuery(jqObj).append(jQuery("<option></option>").attr("value", -1).text('Escolha ' + nodes.getAttribute('data-label')));
                }
                jQuery.each(retorno, function (key, value) {
                    var options = jQuery(jqObj).prop('options');
                    if (key == selec) {
                        jQuery(jqObj).append(jQuery("<option selected></option>").attr("value", key).text(value));
                    } else {
                        jQuery(jqObj).append(jQuery("<option></option>").attr("value", key).text(value));
                    }
                });
            },
            error: function (xhr, ajaxOptions, thrownError) {
                jQuery(jqObj).children('option').remove();
                jQuery(jqObj).append(jQuery("<option selected></option>").attr("value", -1).text(nodes.getAttribute('placeholder')));
                boxAlert('Ocorreu um Erro!<br>'+xhr.responseJSON.message, true, '', false, 1, false);
            }
        })
    } else {
        var nodes = document.getElementById(id_dep);
        var jqObj = jQuery(nodes);
        jQuery(jqObj).children('option').remove();
        jQuery(jqObj).append(jQuery("<option></option>").attr("value", -1).text(nodes.getAttribute('placeholder')));
    }
};;

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
                jQuery(id)
                    .attr('src', e.target.result)
                    .width(largura)
                    .height(altura); 
        };
 
        reader.readAsDataURL(input.files[0]);
    }
 };;


/**
 * compara_senha
 * Compara senha e contra senha, para garantir a senha correta
 *
 * @param {*} contra - nome do campo da contra senha
 * @param {*} senha  - nome do campo da senha
 */
function compara_senha(contra, senha){
     var contra_senha = jQuery('#'+contra).val();
     var nova_senha   = jQuery('#'+senha).val();
     if(contra_senha != nova_senha){
         jQuery('#msg_senha').html('<b>Senhas não conferem! REVISE!</b>');
         jQuery('#msg_senha').addClass('p-2 px-4');
         jQuery('#bt_salvar').attr('disabled',true);
     } else {
        jQuery('#msg_senha').html('');
        jQuery('#msg_senha').removeClass('p-2 px-4');
        jQuery('#bt_salvar').attr('disabled',false);
    }
 };;


/**
 * calcula_diferenca
 * Calcula a diferença entre 2 valores informados em campos lidos por jQuery
 *
 * @param {string} origem    - id do campo original
 * @param {string} informado - id do campo informado
 * @param {string} retorno   - id do campo de retorno
 */
function calcula_diferenca(origem, informado, retorno){
    var orig = converteMoedaFloat(jQuery('#'+origem).val());
    var info = converteMoedaFloat(jQuery('#'+informado).val());
    var dife = orig - info;
    jQuery("#"+retorno).val(converteFloatMoeda(dife));
};;

/**
 * habilita_campos
 * Mostra ou oculta campos na tela de edição
 *
 * @param {string} condicao    - campo a ser testado
 * @param {string} valor       - valor a ser testado
 * @param {string} ocultos     - campos que serão ocultados
 */
function habilita_campos(condicao, valor, ocultos){
    if(jQuery('#'+condicao).val() == valor){
        jQuery('div[id^="ig_"]').show();
        oculto = ocultos.split(',');
        for(o = 0; o < oculto.length; o++){
            jQuery('#ig_'+oculto[o]).hide();
        }
    }
};;


/**
 * acerta_campos_cad_menu
 * Função específica para o CAdastro de Menus
 * Faz os acertos dos campos conforme a Hierarquia
 * @param {object} hierarquia  - Campo da Hierarquia
 */
function acerta_campos_cad_menu(hierarquia){
    jQuery('#ig_men_modulo_id').show();
    jQuery('#ig_men_menu_id').show();
    jQuery('#ig_men_classe_id').show();
    jQuery('#ig_men_submenu_id').show();
    jQuery('#bus_men_modulo_id').prop('required',false);
    jQuery('#men_menu_id').prop('required',true);
    jQuery('#bus_men_classe_id').prop('required',true);
    val_hier = hierarquia.value;
    if(val_hier == 1){ // Raiz do Menu
        jQuery('#ig_men_modulo_id').hide();
        jQuery('#ig_men_menu_id').hide();
        jQuery('#ig_men_submenu_id').hide();
        jQuery('#men_menu_id').prop('required',false);
    } else if(val_hier == 2){ // Menu
        jQuery('#ig_men_classe_id').hide();
        jQuery('#ig_men_menu_id').hide();
        jQuery('#ig_men_submenu_id').hide();
        jQuery('#bus_men_classe_id').prop('required',false);
        jQuery('#men_menu_id').prop('required',false);
    } else if(val_hier == 3){ // SubMenu
        jQuery('#ig_men_classe_id').hide();
        jQuery('#ig_men_submenu_id').hide();
        jQuery('#bus_men_classe_id').prop('required',false);
    } else if(val_hier == 4){ // Opção do Menu
        jQuery('#ig_men_modulo_id').hide();
        jQuery('#men_menu_id').prop('required',false);
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
function busca_atributos(tipo, opcao, etiqueta, icone){
    opc = jQuery("#"+opcao).val();
    if(opc != ''){
        url = '/buscas/busca_modulo_id';
        if(tipo == 'classe'){
            url = '/buscas/busca_classe_id';
        }
        jQuery.ajax({
            type: "POST",
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            url: url,
            async: true,
            dataType: 'json',
            data:{'busca':opc},
            success: function(data){
                jQuery("#"+etiqueta).val(data[0].text);
                jQuery("#"+icone).val(data[0].icone);
            }
        });
    }
};;


/**
 * busca_textselect
 * Pega o texto do Select e coloca no campo
 * @param {object} obj  - Select de Origem
 * @param {string} id_destino  - Campo de Destino
 */
function busca_textselect(obj, id_destino) { //pega o texto do select informado
    var id_res = obj.id;
    var val_ant = jQuery("#" + id_destino).val();
	if(val_ant != undefined && val_ant.length() > 0){
		val_ant += " - ";
	}
    if (obj.nodeName == "SELECT") {
		if(obj.selectedOptions[0].value >= 0){
			var valor = obj.selectedOptions[0].text;
		} else {
			var valor = '';
		}
    } else {
        var valor = obj.value;
    }
    if(val_ant != undefined){
        var val_fim = (val_ant+valor).trim();
        jQuery("#" + id_destino).val(val_fim);
    }
}

function busca_selectvalue(obj, id_destino) { //pega o valor do select informado
    var id_res = obj.id;
    var valor = obj.value;
    jQuery("#" + id_destino).val(valor).trigger('change');
}
