/**
 * mascara
 * Recebe a solicitação e chama a função de Máscara
 * @param {object} obj - Campo onde a máscara será aplicado
 * @param {string} tipo - Tipo da máscara que será aplicada
 */
function mascara(obj,tipo){
    v=obj.value;
    if(tipo == "mcep"){
        v=v.replace(/\D/g,"");                    //Remove tudo o que não é dígito
        v=v.replace(/^(\d{5})(\d)/,"$1-$2");         //Esse é tão fácil que não merece explicações
        return v;
    }
    if(tipo == "mtel"){
        v=v.replace(/\D/g,"");             //Remove tudo o que não é dígito
        v=v.replace(/^(\d{2})(\d)/g,"($1) $2"); //Coloca parênteses em volta dos dois primeiros dígitos
        v=v.replace(/(\d)(\d{4})$/,"$1-$2");    //Coloca hífen entre o quarto e o quinto dígitos
        return v;
    }
    if(tipo == "mcel2"){
        v=v.replace(/\D/g,"");             //Remove tudo o que não é dígito
        v=v.replace(/^(\d{2})(\d)/g,"($1) $2"); //Coloca parênteses em volta dos dois primeiros dígitos
        v=v.replace(/(\d)(\d{4})$/,"$1-$2");    //Coloca hífen entre o quarto e o quinto dígitos
        return v;
    }
    if(tipo == "mcel"){
        var r = v.replace(/\D/g, "");
        r = r.replace(/^0/, "");
        if (r.length > 10) {
          r = r.replace(/^(\d\d)(\d{5})(\d{4}).*/, "($1) $2-$3");
        } else if (r.length > 6) {
          r = r.replace(/^(\d\d)(\d{5})(\d{0,4}).*/, "($1) $2-$3");
        } else if (r.length > 2) {
          r = r.replace(/^(\d\d)(\d{0,5})/, "($1) $2");
        } else {
          r = r.replace(/^(\d*)/, "($1");
        }
        return r;        
    }
    if(tipo == "mcnpj"){
        v=v.replace(/\D/g,"");                           //Remove tudo o que não é dígito
        v=v.replace(/^(\d{2})(\d)/,"$1.$2");             //Coloca ponto entre o segundo e o terceiro dígitos
        v=v.replace(/^(\d{2})\.(\d{3})(\d)/,"$1.$2.$3"); //Coloca ponto entre o quinto e o sexto dígitos
        v=v.replace(/\.(\d{3})(\d)/,".$1/$2");           //Coloca uma barra entre o oitavo e o nono dígitos
        v=v.replace(/(\d{4})(\d)/,"$1-$2");              //Coloca um hífen depois do bloco de quatro dígitos
        return v;
    }
    if(tipo == "mcpf"){
        v=v.replace(/\D/g,"");                    //Remove tudo o que não é dígito
        v=v.replace(/(\d{3})(\d)/,"$1.$2");       //Coloca um ponto entre o terceiro e o quarto dígitos
        v=v.replace(/(\d{3})(\d)/,"$1.$2");       //Coloca um ponto entre o terceiro e o quarto dígitos
                                                //de novo (para o segundo bloco de números)
        v=v.replace(/(\d{3})(\d{1,2})$/,"$1-$2"); //Coloca um hífen entre o terceiro e o quarto dígitos
        return v;
    }
    if(tipo == "mdata"){
        v=v.replace(/\D/g,"");                    //Remove tudo o que não é dígito
        v=v.replace(/(\d{2})(\d)/,"$1/$2");
        v=v.replace(/(\d{2})(\d)/,"$1/$2");
        v=v.replace(/(\d{2})(\d{2})$/,"$1$2");
        return v;
    }
    if(tipo == "mtempo"){
        v=v.replace(/\D/g,"");                    //Remove tudo o que não é dígito
        v=v.replace(/(\d{1})(\d{2})(\d{2})/,"$1:$2.$3");
        return v;
    }
    if(tipo == "mhora"){
        v=v.replace(/\D/g,"");                    //Remove tudo o que não é dígito
        v=v.replace(/(\d{2})(\d)/,"$1h$2");
        return v;
    }
    if(tipo == "mrg"){
        v=v.replace(/\D/g,"");                                      //Remove tudo o que não é dígito
        v=v.replace(/(\d)(\d{7})$/,"$1.$2");    //Coloca o . antes dos últimos 3 dígitos, e antes do verificador
        v=v.replace(/(\d)(\d{4})$/,"$1.$2");    //Coloca o . antes dos últimos 3 dígitos, e antes do verificador
        v=v.replace(/(\d)(\d)$/,"$1-$2");               //Coloca o - antes do último dígito
        return v;
    }
    if(tipo == "mnum"){
        v=v.replace(/\D/g,"");                                      //Remove tudo o que não é dígito
        return v;
    }
    if(tipo == "mvalor"){
        v=v.replace(/\D/g,"");//Remove tudo o que não é dígito
        v=v.replace(/(\d)(\d{8})$/,"$1.$2");//coloca o ponto dos milhões
        v=v.replace(/(\d)(\d{5})$/,"$1.$2");//coloca o ponto dos milhares
    
        v=v.replace(/(\d)(\d{2})$/,"$1,$2");//coloca a virgula antes dos 2 últimos dígitos
        
        return v;
    }
};;

/**
 * sair_moeda
 * executada quando sair de um campo do tipo moeda
 * @param {object} obj - Campo origem
 */
function sair_moeda(obj){
	vvalor = converteMoedaFloat(jQuery(obj).val());
	if(vvalor == ''){
		vvalor = 0;
	}
	valormoeda = converteFloatMoeda(vvalor.toFixed(2));
	jQuery(obj).val(valormoeda);
};;

/**
 * entrar_moeda
 * Seleciona o conteúdo do campo, quando entrar em um campo do tipo moeda
 * @param {obje} obj - campo origem
 */
function entrar_moeda(obj){
	jQuery(obj).select();
};;

/**
 * converteMoedaFloat
 * Converte um valor do tipo moeda para Float
 * @param {string} valor - valor a ser convertido
 */
function converteMoedaFloat(valor) {
    if (valor === "") {
        valor = 0;
    } else {
        valor = valor.replace(".", "");
        valor = valor.replace(",", ".");
        valor = parseFloat(valor);
    }
    return valor;
};;

/**
 * converteFloatMoeda
 * Converte um valor do tipo Float para moeda
 * @param {float} valor - Valor a ser convertido
 */
function converteFloatMoeda(valor) {
    var inteiro = null, decimal = null, c = null, j = null;
    var aux = new Array();
    valor = "" + valor;
    pos_decimal = valor.indexOf(".", 0);
    //encontrou o ponto na string
    if (pos_decimal > 0) {
        //separa as partes em inteiro e decimal
        inteiro = valor.substring(0, pos_decimal);
        decimal = valor.substring(pos_decimal + 1, valor.length);
    } else {
        inteiro = valor;
    }

    //pega a parte inteiro de 3 em 3 partes
    for (j = inteiro.length, contador = 0; j > 0; j -= 3, contador++) {
        aux[contador] = inteiro.substring(j - 3, j);
    }

    //percorre a string acrescentando os pontos
    inteiro = "";
    for (contador = aux.length - 1; contador >= 0; contador--) {
        inteiro += aux[contador] + '.';
    }
    //retirando o ultimo ponto e finalizando a parte inteiro

    inteiro = inteiro.substring(0, inteiro.length - 1);

    decimal = parseInt(decimal);
    if (isNaN(decimal)) {
        dec = "00";
    } else {
        decimal = decimal + "0";
        dec = decimal.substring(0, 2);
    }

    valor = inteiro + "," + dec;

    return valor;
};;