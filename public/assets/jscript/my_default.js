jQuery(document).ready(function() {  
    /**
     * se a div Toast tiver conteúdo mostra por 3 segundos
     * Document Ready my_default
     */
    texto = jQuery('.toast-body').html();
    if(jQuery.trim(texto) != ''){
        jQuery(".toast").toast("show");
        jQuery(".toast").toast({
          delay: 3000
        }); 
        jQuery(".toast").toast({
          animation: true
        }); 
    };;

    /**
     * Submete o Formulário com ajax
     * Document Ready my_default
     */
    jQuery('form').submit(function (event) {
        bloqueiaTela();
        var form = jQuery(this);
        var tipo = form.attr('type');
        if(tipo != 'normal'){
            event.preventDefault(); // avoid to execute the actual submit of the form.
            jQuery(".moeda").each(function () {
                var valor = converteMoedaFloat(jQuery(this).val());
                jQuery(this).val(valor);
            });
        
            var dadosForm = new FormData(this);
            console.log(dadosForm);
            var url = form.attr('action');
            jQuery.ajax({
                type: "POST",
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                url: url,
                processData: false,
                contentType: false,
                dataType: 'json',
                // data: form.serialize(), // serializes the form's elements.
                data: dadosForm, // serializes the form's elements.
                success: function(data){
                    desBloqueiaTela();
                    if(tipo == 'novaguia'){
                        redirec_blank(data.url);
                    } else {
                        if(!data.erro){
                            redireciona(data.url)
                        } else {
                            boxAlert(data.msg, data.erro,data.url,false, 2, false);
                        }
                    }
                }
            });
        }
    });;
})

/**
 * bloqueiaTela
 * Bloqueia a tela e apresenta a msg Processando...
 */
function bloqueiaTela(){
    jQuery('#bloqueiaTela').removeClass('d-none');
};;

/**
 * desBloqueiaTela
 * Desbloqueia a Tela
 */
function desBloqueiaTela(){
    jQuery('#bloqueiaTela').addClass('d-none');
};;


/**
 * redireciona
 * Redireciona a execução para a URL fornecida
 * @param {string} url - URL de destino
 */
function redireciona(url){
    window.location.assign(url);
};;

/**
 * redirec_blank
 * Abre a URL fornecida numa nova Janela
 * @param {string} url - URL de destino
 */
function redirec_blank(url){
    window.open(url,'_blank');
};;

/**
 * retorna_url
 * Retorna para a URL anterior
 *
 * @param {int} tipo - Se for igual a 1, volta para a URL anterior gravada
 * 
 */
function retorna_url(tipo = 1){
    if(tipo == 1){
        location.href = document.referrer;
    } else if(tipo == 2){
        location.href = history.back();
    }
 };;

/**
 * openUrlDiv
 * Abre a URL fornecida, dentro da Div informada
 * @param {string} url - URL de destino
 * @param {string} div - nome da Div de destino
 */
function openUrlDiv(url, div){
    jQuery.ajax({
        type: 'POST',
        async: true,
        dataType: 'html',
        url: url,
        success: function (retorno) {
            jQuery("#"+div).html(retorno);
        },
        error: function (xhr, ajaxOptions, thrownError) {
            retorno = xhr.statusText;
            boxAlert(retorno, true,'',false, 2, false);
        }
    });

};;

/**
 * openPDFModal
 * Abre o PDF informado na URL, numa Janela Modal
 * @param {string} url - URL do PDF
 * @param {string} titulo - titulo da Janela Modal
 */
function openPDFModal(url, titulo) {
    jQuery("#myModalLabel").html(titulo);
    jQuery.ajax({
        type: 'POST',
        async: true,
        dataType: 'json',
        url: url,
        success: function (retorno) {
            pdfretornado = "<embed src='"+retorno.arquivo+"' type='application/pdf'  width='100%' height='400px' >";
            jQuery("#modal-body").html(pdfretornado);
            const container = document.getElementById("myModal");
            const modal = new bootstrap.Modal(container);
            modal.show();
        },
        error: function (xhr, ajaxOptions, thrownError) {
            retorno = xhr.statusText;
            boxAlert(retorno, true,'',false, 2, false);
        }
    });
};;

/**
 * openModal
 * Abre a URL informada, numa Janela Modal
 * @param {string} url - URL 
 * @param {string} titulo - titulo da Janela Modal
 */
function openModal(url){
    jQuery.ajax({
        url: url,
        dataType: 'html',
        success: function(data){
            var myModal = new bootstrap.Modal(document.getElementById("myModal"), {});
            document.onreadystatechange = function () {
              myModal.show();
            };
        }
    });
};;



/**
 * toogle_div
 * Mostra ou esconde uma Div
 *
 * @param {string} iddiv - id da Div
 */
function toogle_div(iddiv){
     jQuery('#'+iddiv).toggleClass('d-none'); 
 };;

/**
 * excluir
 * Apresenta o box de alerta de exclusão, e caso confirmado, exclui o registro
 * 
 * @param {url} url - url de exclusão do Registro
 * @param {string} registro - descrição do Registro que será excluído
 */
 function excluir(url, registro){
    msg = "Confirma a Exclusão do Registro?<br><h5 class='text-center'>"+registro+"</h5>";
    boxAlert(msg, false, url, false, 3, true);
};;

/**
 * boxAlert
 * Cria um box de Alerta para o Usuário
 *
 * @param {string} msg    - Mensagem que será exibida
 * @param {boolean} erro  - verdadeiro, caso seja uma mensagem de ERRO
 * @param {url}     url   - url que será executada no callback
 * @param {boolean} close - verdadeiro, Mostra o botão para fechar o Alerta
 * @param {integer} tipo  - Determina as características do Box 1 = Atenção, 2 = Informação
 * @param {boolean} confirma  - verdadeiro, Mostra os botões Sim ou Não para Confirmação
 */
function boxAlert(msg, erro = false, url, close = false, tipo = 1, confirma = false){
    if(tipo == 1){ // ATENÇÃO
        titulo = '<h3><i class="fas fa-exclamation-triangle"></i><span class="mx-2">Atenção</span></h3>';
    } else if(tipo == 2){ // INFORMAÇÃO
        titulo = '<h3><i class="fas fa-info-circle"></i><span class="mx-2">Informação</span></h3>';
    }
    if(!confirma){
        bootbox.alert({
            title: titulo,
            message: "<div class='text-center'><h5>"+msg+"</h5></div>",
            centerVertical: true,
            closeButton: close,
            size: 'small',
            className: 'rubberBand animated',
            callback: function () {
                if(!erro){
                    redireciona(url);
                }
            }     
        });
    } else {
        bootbox.confirm({
            title: '<h3><i class="fas fa-question-circle"></i><span class="mx-2">Exclusão de Registro</span></h3>',
            message: "<div class='text-center'><h5>"+msg+"</h5></div>",
            centerVertical: true,
            size: 'large',
            closeButton: false,
            swapButtonOrder: true,
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> Não',
                    className: 'btn-secondary',
                },
                confirm: {
                    label: '<i class="fa fa-check"></i> Sim',
                    className: 'btn-danger ',
                },
            },
            callback: function (result) {
                if(result){ 
                    redireciona(url);
                }
            }
        });
    }
    if(tipo == 1){ // ATENÇÃO
        jQuery('.modal-header').css("background-color", "red");
        jQuery('.modal-header').css("color", "yellow");
    } else if(tipo == 2){ // INFORMAÇÃO
        jQuery('.modal-header').css("background-color", "green");
        jQuery('.modal-header').css("color", "white");
    } else if(tipo == 3){ // EXCLUIR
        jQuery('.modal-header').css("background-color", "orange");
        jQuery('.modal-header').css("color", "black");
    }
};;
