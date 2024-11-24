
function pesquisacep(obj, valor, prefixo) {
    // regex = '\[(\w+)\]';
    // pos = obj['id'].indexOf("__");
    alter = obj.getAttribute('data-alter');
    if (alter) {
        posi = obj.id.indexOf('[') + 1;
        posf = obj.id.indexOf(']');
        pos = obj.id.substr(posi, (posf - posi));
        if (pos >= 0) {
            pos_seq = obj['id'].substring(pos + 2, obj['id'].length);
        }
        //Nova variável "cep" somente com dígitos.
        var cep = valor.replace(/\D/g, '');
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
                    } catch (ex) { }
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
};

function pesquisaCNPJ(valor) {
    //Nova variável "CNPJ" somente com dígitos.
    var CNPJ = valor.replace(/\D/g, '');
    //Verifica se campo CNPJ possui valor informado.
    if (CNPJ != "") {
        //Expressão regular para validar o CEP.
        var validaCNPJ = /(\d{0,2})(\d{0,3})(\d{0,3})(\d{0,4})(\d{0,2})/;
        //Valida o formato do CEP.
        if (validaCNPJ.test(CNPJ)) {
            // testaCliente = CNPJCPFcadastrado(valor); 
            url = window.location.origin + '/buscas/cnpjcpfcadastrado';
            jQuery.ajax({
                type: 'POST',
                async: false,
                dataType: 'json',
                url: url,
                data: { 'cpfcnpf': valor },
                success: function (retorno) {
                    if (retorno.tem == '1') {
                        boxAlert('CNPJ Já Cadastrado', true, '', false, 1, false);
                        jQuery('#for_cnpj').val('');
                        jQuery('#for_cnpj').focus();
                    } else {
                        var url = "https://api-publica.speedio.com.br/buscarcnpj?cnpj=" + CNPJ;

                        // Faz a pesquisa do CEP, tratando o retorno com try/catch para que
                        // caso ocorra algum erro (o CNPJ pode não existir, por exemplo) a
                        // usabilidade não seja afetada, assim o usuário pode continuar//
                        // preenchendo os campos normalmente

                        jQuery.getJSON(url, function (dadosRetorno) {
                            jQuery('#for_razao').val(dadosRetorno['RAZAO SOCIAL']);
                            jQuery('#for_fantasia').val(dadosRetorno['NOME FANTASIA']);
                            jQuery("#for_rua").val(dadosRetorno['TIPO LOGRADOURO'] + ' ' + dadosRetorno['LOGRADOURO']);
                            jQuery("#for_complemento").val(dadosRetorno['COMPLEMENTO']);
                            jQuery("#for_cep").val(dadosRetorno['CEP']);
                            jQuery("#for_bairro").val(dadosRetorno['BAIRRO']);
                            jQuery("#for_cidade").val(dadosRetorno['MUNICIPIO']);
                            jQuery("#for_numero").val(dadosRetorno['NUMERO']);
                            jQuery("#for_fone").val(dadosRetorno['DDD'] + dadosRetorno['TELEFONE']);
                            jQuery("#for_estado").val(dadosRetorno['UF']);
                            // jQuery('#emp_cnae').val(dadosRetorno['CNAE PRINCIPAL CODIGO']);
                            // jQuery('#emp_cnad').val(dadosRetorno['CNAE PRINCIPAL DESCRICAO']);
                            localStorage.setItem('dadoscnpj', dadosRetorno);
                            // pesquisacepcnpj();
                        });
                    }
                }
            });
        } //end if.
        else {
            //CNPJ é inválido.
            // limpa_formulário_CNPJ();
            boxAlert('Formato de CNPJ inválido', true, '', false, 1, false);
        }
    } //end if.
    else {
        //CNPJ sem valor, limpa formulário.
        // limpa_formulário_CNPJ();
    }
};

function pesquisaCPF(valor) {
    if (valor != '') {
        // testaCliente = ; 
        url = window.location.origin + '/buscas/cnpjcpfcadastrado';
        jQuery.ajax({
            type: 'POST',
            async: false,
            dataType: 'json',
            url: url,
            data: { 'cpfcnpf': valor },
            success: function (retorno) {
                if (retorno.tem == '1') {
                    boxAlert('CPF Já Cadastrado', true, '', false, 1, false);
                    jQuery('#for_cpf').val('');
                    jQuery('#for_cpf').focus();
                }
            }
        });
    }
}

function pesquisaCPFApi(obj) {
    campo = obj.id;
    valor = obj.value;
    var CPF = valor.replace(/\D/g, '');
    nascimento = '25071973';
    if (CPF != '') {
        // testaCliente = ; 
        url = window.location.origin + '/buscas/cpfcolabcadastrado';
        jQuery.ajax({
            type: 'POST',
            async: false,
            dataType: 'json',
            url: url,
            data: { 'cpfcnpf': valor },
            success: function (retorno) {
                if (retorno.tem == '1') {
                    boxAlert('CPF Já Cadastrado', true, '', false, 1, false);
                    jQuery('#' + campo).val('');
                    jQuery('#' + campo).focus();
                } else {
                    token = "447FE5F3-F413-4D66-A79F-D7D8300AFF0B";
                    var url = "https://www.sintegraws.com.br/api/v1/execute-api.php?token=" + token + "&cpf=" + CPF + "&data-nascimento=" + nascimento + "&plugin=CPF";
                    jQuery.ajax({
                        method: 'GET',
                        url: url,
                        complete: function (xhr) {

                            // Aqui recuperamos o JSON retornado
                            response = xhr.responseJSON;

                            if (response.status == 'OK') {

                                // Agora preenchemos os campos com os valores retornados
                                //preencher os outros campos
                                // ...

                                // Aqui exibimos uma mensagem caso tenha ocorrido algum erro
                            } else {
                                alert(response.message);
                            }
                        }
                    });
                }
            }
        });
    }
}

function pesquisacepcnpj() {
    dadoscnpj = localStorage.getItem('dadoscnpj');
    if (dadoscnpj != undefined) {
        jQuery("#for_rua").val(dadoscnpj['LOGRADOURO']);
        jQuery("#for_cep").val(dadoscnpj['CEP']);
        jQuery("#for_bairro").val(dadoscnpj['BAIRRO']);
        jQuery("#for_cidade").val(dadoscnpj['MUNICIPIO']);
        jQuery("#for_uf").val(dadoscnpj['UF']);
    }
}

function busca_dados_material(orig, obj, url, base) {
    var datarr = new Array();
    pref = obj.substring(0, 3);
    posi = orig.id.indexOf('[') + 1;
    posf = orig.id.indexOf(']');
    pos = orig.id.substr(posi, (posf - posi));
    // sufi = '';
    // if(orig.id.indexOf('__') > 0){
    //     sufi = orig.id.substr(-3);
    // }   
    datarr[0] = {};
    datarr[0].valor = orig.value;
    jQuery.ajax({
        type: 'POST',
        async: false,
        dataType: 'json',
        url: url,
        data: { campo: datarr },
        success: function (retorno) {
            // jQuery('#'+pref+'_unidade\\['+pos+'\\]').val(retorno.mat_unidade).change();
            // jQuery('[name="'+pref+'_unidade\\['+pos+'\\]"]').selectpicker('destroy');
            // jQuery('[name="'+pref+'_unidade\\['+pos+'\\]"]').selectpicker('deselectAll');
            // jQuery('#'+pref+'_unidade\\['+pos+'\\] option[value='+retorno.mat_unidade+']').attr('selected', 'selected');
            jQuery('#' + pref + '_unidade\\[' + pos + '\\]').selectpicker('val', retorno.mat_unidade);
            // jQuery('[name="'+pref+'_unidade\\['+pos+'\\]"]').selectpicker('refresh');
            jQuery('#' + pref + '_unitario\\[' + pos + '\\]').val(converteFloatMoeda(retorno.mat_compra / retorno.mat_quantia));
            if (retorno.mpc_unitario != null) {
                jQuery('#' + pref + '_unitario\\[' + pos + '\\]').val(converteFloatMoeda(retorno.mpc_unitario / 1));
            }
            console.log(retorno);
        }
    });
}

function buscaProdutoMarca(obj) {
    valor = obj.value;
    if (valor != "") {
        ordem = obj.getAttribute('data-index');
        url = window.location.origin + '/buscas/buscaprodutomarca';
        jQuery.ajax({
            type: 'POST',
            async: false,
            dataType: 'json',
            url: url,
            data: { 'marca': valor },
            success: function (retorno) {
                if (retorno.id == -1) {
                    jQuery('#pro_id\\[' + ordem + '\\]').val('');
                    jQuery('#pro_nome\\[' + ordem + '\\]').val('');
                    jQuery('#mar_nome\\[' + ordem + '\\]').val('');
                    jQuery('#conversao\\[' + ordem + '\\]').val('');
                    jQuery('#unm_id\\[' + ordem + '\\]').selectpicker('val', -1);
                    jQuery('#und_id\\[' + ordem + '\\]').selectpicker('val', -1);
                    // abrir cadastro de marca modal
                    openModal(window.location.origin + '/EstMarca/add/modal=true');
                } else {
                    jQuery('#pro_id\\[' + ordem + '\\]').val(retorno.id);
                    jQuery('#pro_nome\\[' + ordem + '\\]').val(retorno.produto);
                    jQuery('#mar_nome\\[' + ordem + '\\]').val(retorno.marca);
                    jQuery('#conversao\\[' + ordem + '\\]').val(retorno.conversao);
                    jQuery('#unm_id\\[' + ordem + '\\]').selectpicker('val', retorno.und_marca);
                    jQuery('#und_id\\[' + ordem + '\\]').selectpicker('val', retorno.und_produ);
                }
            }
        });
    }
}

function buscaSaldoProduto(obj, campo = 'sap_saldo') {
    ordem = obj.getAttribute('data-index');
    prod = jQuery("#pro_id\\[" + ordem + "\\]").val();
    depo = jQuery("#dep_id").val();
    if (depo == "") {
        boxAlert('Informe o Depósito', true, '', false, 1, false);
    } else if (prod != "") {
        url = window.location.origin + '/buscas/buscasaldoproduto';
        jQuery.ajax({
            type: 'POST',
            async: false,
            dataType: 'json',
            url: url,
            data: { 'produto': prod, 'deposito': depo },
            success: function (retorno) {
                jQuery('#' + campo + '\\[' + ordem + '\\]').val(retorno.saldo);
            }
        });
    }
}


function buscaDadosProduto(obj) {
    valor = obj.value;
    ordem = obj.getAttribute('data-index');
    url = window.location.origin + '/buscas/buscaproduto';
    jQuery.ajax({
        type: 'POST',
        async: false,
        dataType: 'json',
        url: url,
        data: { 'id': valor },
        success: function (retorno) {
            if (retorno.id == -1) {
                // jQuery('#pro_id\\[' + ordem + '\\]').val('');
                // jQuery('#pro_nome\\[' + ordem + '\\]').val('');
                if (obj.id.indexOf('[') > 0) {
                    jQuery('#und_id\\[' + ordem + '\\]').selectpicker('val', -1);
                } else {
                    jQuery('#und_id').selectpicker('val', -1);
                }
                // abrir cadastro de marca modal
                openModal(window.location.origin + '/EstMarca/add/modal=true');
            } else {
                // jQuery('#pro_id\\[' + ordem + '\\]').val(retorno.id);
                // jQuery('#pro_nome\\[' + ordem + '\\]').val(retorno.produto);
                if (obj.id.indexOf('[') > 0) {
                    jQuery('#und_id\\[' + ordem + '\\]').selectpicker('val', retorno.und_produ);
                } else {
                    jQuery('#und_id').selectpicker('val', retorno.und_produ);
                }
            }
        }
    });
}