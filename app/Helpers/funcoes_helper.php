<?php

use App\Models\Config\ConfigMenuModel;
use App\Models\Config\ConfigPerfilItemModel;
use App\Models\Config\ConfigTelaModel;
use App\Models\LogMonModel;
use PHPUnit\Framework\InvalidArgumentException;
use PhpParser\Node\Expr\Cast\Object_;

set_exception_handler(function ($exception) {
    // Exibe a mensagem da exceção em um formato amigável
    echo "<h1>Erro!</h1>";
    echo "<p>Mensagem: " . htmlspecialchars($exception->getMessage()) . "</p>";
    echo "<p>Código: " . $exception->getCode() . "</p>";
    echo "<p>Arquivo: " . htmlspecialchars($exception->getFile()) . "</p>";
    echo "<p>Linha: " . $exception->getLine() . "</p>";
    // Opcional: exibe o rastreamento da pilha
    echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
    // $data['title'] = 'Erro';
    // $data['exception'] = $exception;
    // $data['file'] = $exception->getFile();
    // $data['line'] = $exception->getLine();
    // $data['message'] = htmlspecialchars($exception->getMessage());
    // echo view('errors/html/error_exception', $data);
});

/**
 * detalhesTela
 * Retorna os detalhes da Tela Especificada
 * @param string $nomeTela
 * @return array
 */
function detalhesTela(string $nomeTela, array $retorno)
{
    preg_match('/\\\\([\w]+)$/', $nomeTela, $nome);
    $perfil_usu             = session()->get('usu_perfil_id');
    $tela                 = new ConfigTelaModel();
    $perfil_pit             = new ConfigPerfilItemModel();
    $menu                   = new ConfigMenuModel();
    $busca_tela           = $tela->getTela($nome[1])[0];
    $retorno['title']       = $busca_tela['tel_icone'] . ' ' . $busca_tela['tel_nome'];
    $retorno['controler']   = $busca_tela['tel_controler'];
    $retorno['opc_menu']    = $busca_tela['men_id'];
    $retorno['bt_add']      = $busca_tela['tel_texto_botao'];
    $retorno['perfil_usu']  = $perfil_usu;
    $retorno['it_menu']     = $menu->getMenuPerfil($perfil_usu);

    if ($busca_tela['men_id']) {
        $busca_permissoes       = $perfil_pit->getItemPerfil($perfil_usu, $busca_tela['men_id']);
        if (sizeof($busca_permissoes) == 0) {
            $retorno['permissao']   = false;
        } else {
            $retorno['permissao']   = $busca_permissoes[0]['pit_permissao'];
        }
    } else {
        $retorno['permissao']   = 'CAEX';
    }
    // debug($retorno, false);
    return $retorno;
};;

/**
 * metodosTela
 *  Retorna uma lista de métodos da Tela Especificada
 * @param array $metodos
 * @param string $classe
 * @return string
 */
function metodosTela(array $metodos, string $classe)
{
    $text = '';
    foreach ($metodos as $nome => $valor) {
        if ($valor->class == $classe) {
            $text .= "$valor->name<br>";
        }
    }
    return $text;
};;

/**
 * campos_tabela
 *  Retorna uma lista dos Campos da Tabela Especificada
 * @param array $campos
 * @return string
 */
function campos_tabela(array $campos)
{
    $tipos['char']      = 'Caracter curto';
    $tipos['varchar']   = 'Caracter longo';
    $tipos['mediumtext'] = 'Texto';
    $tipos['text']      = 'Texto';
    $tipos['int']       = 'Inteiro';
    $tipos['bigint']       = 'Inteiro';
    $tipos['float']     = 'Moeda';
    $tipos['decimal']     = 'Decimal';
    $tipos['date']      = 'Data';
    $tipos['time']      = 'Hora';
    $tipos['timestamp'] = 'Data e Hora';
    $tipos['datetime']  = 'Data e Hora';

    $simnao['YES'] = 'Não';
    $simnao['NO']  = 'Sim';

    $chave['PRI'] = "Primária";
    $chave['MUL'] = "Múltipla";
    $chave['UNI'] = "Única";
    $chave['']    = "";

    $text = '<table border="1" class="table table-light table-bordered table-striped table-hover table-responsive">';
    $text .= '<tr>';
    $text .= '<thead class="table-primary">';
    $text .= '<th>Rótulo</th>';
    $text .= '<th>Nome</th>';
    $text .= '<th>Tipo</th>';
    $text .= '<th>Tamanho</th>';
    $text .= '<th>Obrigatório</th>';
    $text .= '<th>Campo Chave</th>';
    $text .= '</thead>';
    $text .= '</tr>';
    $tabela = '';
    foreach ($campos as $camp) {
        if (trim($camp['COLUMN_COMMENT']) != 'Excluído em') {
            if ($tabela != $camp['TABLE_NAME']) {
                $text .= '<tr>';
                $text .= '<td colspan="6"><h3>TABELA => ' . $camp['TABLE_NAME'] . '</h3></td>';
                $text .= '</tr>';
                $tabela = $camp['TABLE_NAME'];
            }
            $text .= '<tr>';
            $text .= '<td>' . $camp['COLUMN_COMMENT'] . '</td>';
            $text .= '<td>' . $camp['COLUMN_NAME'] . '</td>';
            $text .= '<td>' . $tipos[$camp['DATA_TYPE']] . '</td>';
            $text .= '<td>' . $camp['COLUMN_SIZE'] . '</td>';
            $text .= '<td>' . $simnao[$camp['IS_NULLABLE']] . '</td>';
            $text .= '<td>' . $chave[$camp['COLUMN_KEY']] . '</td>';
            $text .= '</tr>';
        }
    }
    $text .= '</table>';
    return $text;
};;

/**
 * relacion_tabela
 *  Retorna uma lista dos Relacionamentos da Tabela Especificada
 * @param array $relacionamentos
 * @return string
 */
function relacion_tabela(array $relacionamentos)
{
    $text = '<table border="1" class="table table-light table-bordered table-striped table-hover table-responsive">';
    $text .= '<tr>';
    $text .= '<thead class="table-primary">';
    $text .= '<th>Nome</th>';
    $text .= '<th>Tabela</th>';
    $text .= '<th>Relacionamento</th>';
    $text .= '</thead>';
    $text .= '</tr>';
    foreach ($relacionamentos as $relac) {
        $text .= '<tr>';
        $text .= '<td>' . $relac['CONSTRAINT_NAME'] . '</td>';
        $text .= '<td>' . $relac['REFERENCED_TABLE_NAME'] . ' (' . $relac['TABLE_COMMENT'] . ')</td>';
        $text .= '<td>' . $relac['TABLE_NAME'] . '[' . $relac['COLUMN_NAME'] . ']  =>  ' . $relac['REFERENCED_TABLE_NAME'] . '[' . $relac['REFERENCED_COLUMN_NAME'] . ']</td>';
        $text .= '</tr>';
    }
    $text .= '</table>';
    return $text;
};;

/**
 * verCodigo
 *  Retorna o Código Fonte da Tela Especificada
 * @param string $nomeTela
 * @return string
 */
function verCodigo($nomeTela)
{
    $arq    = APPPATH . $nomeTela . '.php';
    $text   = '';
    $text   .= $arq . '<br>';
    // $text  = file_get_contents($arq);
    if (file_exists($arq)) {
        $text_arr  = file($arq);
        for ($l = 0; $l < count($text_arr); $l++) {
            $linha = $text_arr[$l];
            $text .= ($l + 1) . ' => ' . htmlspecialchars($linha);
        }
    } else {
        $text .= 'Tela ainda não Codificada!';
    }
    return $text;
};;

/**
 * toDataBr
 * Retorna uma data em formato americano para o formato brasileiro
 * @param DateTime $datahora
 * @return string
 */
function toDataBr(DateTime $datahora): string
{
    return $datahora->format('d/m/Y H:i:s');
};;

/**
 * dataDbToBr
 * Converte uma data do formato de banco de dados (YYYY-MM-DD) para o formato brasileiro (DD/MM/YYYY).
 *
 * @param string $dataBanco Data no formato YYYY-MM-DD.
 * @return string Data no formato DD/MM/YYYY.
 */
// function dataDbToBr($dataBanco) {
//     // Cria um objeto DateTime a partir da data no formato de banco de dados
//     $data = DateTime::createFromFormat('Y-m-d', $dataBanco);

//     // Verifica se a conversão foi bem-sucedida
//     if (!$data) {
//         throw new InvalidArgumentException("Formato de data inválido.");
//     }

//     // Formata a data para o formato brasileiro
//     return $data->format('d/m/Y');
// };;
/**
 * data_br
 * Converte uma data no formato de banco para o formato brasileiro
 * YYYY-MM-DD => dd/mm/yyyy
 * @param    string
 * @return    string
 */
function dataDbToBr($data_bd)
{
    $ret = '';
    if ($data_bd != '') {
        // debug($data_bd);
        $final_  = '' . substr($data_bd, 10);
        $data_or = substr($data_bd, 0, 10);
        $parts   = explode('-', $data_or);
        // debug($parts);
        $ret = $parts[2] . '/' . $parts[1] . '/' . $parts[0] . $final_;
    }
    // $data_cn = implode('/', array_reverse(explode('-', $data_or)));
    // return $data_bd.' => ' . $ret;
    return $ret;
};;

/**
 * dataBrToDb
 * Converte uma data do formato brasileiro (DD/MM/YYYY) para o formato de banco de dados (YYYY-MM-DD).
 *
 * @param string $dataBrasileira Data no formato DD/MM/YYYY.
 * @return string Data no formato YYYY-MM-DD.
 */
function dataBrToDb($dataBrasileira)
{
    // $sodata = $dataBrasileira;
    $sodata = substr($dataBrasileira, 0, 10);
    $sohora = substr($dataBrasileira, 11, 8);
    // Cria um objeto DateTime a partir da data no formato brasileiro
    $data = DateTime::createFromFormat('d/m/Y', $sodata);

    // Verifica se a conversão foi bem-sucedida
    if (!$data) {
        debug($dataBrasileira);
        throw new \InvalidArgumentException("Formato de data inválido. " . $sodata);
    }

    // Formata a data para o formato de banco de dados
    return $data->format('Y-m-d') . ' ' . $sohora;
}
/**
 * data_db
 * Converte uma data no formato brasileiro para o formato de banco
 * dd/mm/yyyy => YYYY-MM-DD
 * @param    string
 * @return    string
 */
// function dataBrToDb($dataDbToBr)
// {
//     $final_  = '' . substr($dataDbToBr, 10);
//     $data_or = substr($dataDbToBr, 0, 10);
//     return implode('-', array_reverse(explode('/', $data_or))).$final_;
// }

/**
 * numTodataBrToDb
 * Converte um número para o formato de banco
 * YYYYMMDD => YYYY-MM-DD
 * @param    string
 * @return    string
 */
function numTodataBrToDb($data_num)
{
    $ano = substr($data_num, 0, 4);
    $mes = substr($data_num, 4, 2);
    $dia = substr($data_num, -2);
    $hora = '';
    if (strlen($data_num) > 8) {
        $hora = numTohora_db(substr($data_num, 8, 20));
    }
    return $ano . '-' . $mes . '-' . $dia . $hora;
}

/**
 * numTohora_db
 * Converte um número para o formato de banco
 * HHMMSS => HH:MM.SS
 * @param    string
 * @return    string
 */
function numTohora_db($hora_num)
{
    $hora = substr($hora_num, 0, 2);
    $minu = substr($hora_num, 2, 2);
    $seco = substr($hora_num, -2);
    return $hora . ':' . $minu . ':' . $seco;
}

/**
 * dif_tempo
 * Retorna diferença entre data-hora inicial e data-hora final
 * 
 * [#diferenca]
 * [#anos]
 * [#meses]
 * [#dias]
 * [#horas]
 * [#minutos]
 * [#seconds]
 * @param mixed $tempo_ini
 * @param mixed $tempo_fim
 * @return array
 */
function dif_tempo($tempo_ini, $tempo_fim)
{
    $tempo_ini = strtotime($tempo_ini);
    $tempo_fim = strtotime($tempo_fim);

    $diff = $tempo_fim - $tempo_ini;
    log_message('info', 'diferenca tempo ' . $diff);
    $ret['diferenca'] = $diff;

    $years = floor($diff / (365 * 60 * 60 * 24));
    $ret['anos'] = $years;

    $months = floor(($diff - $years * 365 * 60 * 60 * 24)
        / (30 * 60 * 60 * 24));
    $ret['meses'] = $months;

    $days = floor(($diff - $years * 365 * 60 * 60 * 24 -
        $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
    $ret['dias'] = $days;

    $hours = floor(($diff - $years * 365 * 60 * 60 * 24
        - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24)
        / (60 * 60));
    $ret['horas'] = $hours;

    $minutes = floor(($diff - $years * 365 * 60 * 60 * 24
        - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24
        - $hours * 60 * 60) / 60);
    $ret['minutos'] = $minutes;

    $seconds = floor(($diff - $years * 365 * 60 * 60 * 24
        - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24
        - $hours * 60 * 60 - $minutes * 60));
    $ret['seconds'] = $seconds;

    return $ret;
};;

/**
 * url_amigavel
 * Retira acentos, substitui espaço por - e
 * deixa tudo minúsculo
 *
 * @param    string
 * @return    string
 */
function url_amigavel($variavel)
{
    $procurar = array('á', 'à', 'ã', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ü', 'ç', ' ', '.', '+');
    $substituir = array('a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'c', '_', '.', '_');
    $variavel = mb_strtolower($variavel);
    $variavel = str_replace($procurar, $substituir, $variavel);
    $variavel = htmlentities($variavel);
    $variavel = preg_replace("/&(acute|cedil|circ|ring|tilde|uml);/", "$1", $variavel);
    return trim($variavel, "-");
};;
/**
 * formata_texto
 * Formata Texto para exibição sem códigos HTML
 * 
 * @param mixed $texto
 * @return string
 */
function formata_texto($texto)
{
    $texto_ret = html_entity_decode(trim(strip_tags(utf8_decode($texto), '<br>')));
    return $texto_ret;
};;

/**
 * debug
 * Mostra mensagens de Debug
 * @param mixed $valor
 * @param mixed $parada // continua ou para o processamento
 * @return void
 */
function debug($valor, $parada = false)
{
    if (is_array($valor) || is_object($valor)) {
        echo "array";
        echo "<div style = 'border:1px solid green; background-color: #ccc; color:green;left:10%;width:80%; max-height:80vh; overflow-y:auto; top:10px; position: relative; z-index:5000'>";
        echo "<pre>";
        print_r($valor);
        echo "</pre>";
        echo "</div>";
    } else {
        echo "<div style = 'border:1px solid green; background-color: #ccc; color:green;left:10%;width:80%; max-height:80vh; overflow-y:auto; top:10px; position: relative; z-index:5000'>";
        echo ($valor);
        echo "</div>";
    }
    if ($parada) {
        exit;
    }
    return;
};;

/**
 * isMobile
 * Retorna se o dispositivo usado para acessar é um dispositivo móvel
 * 
 * @return bool
 */
function isMobile()
{
    $mobile = FALSE;
    $user_agents = array("iPhone", "iPad", "Android", "webOS", "BlackBerry", "iPod", "Symbian", "IsGeneric");

    foreach ($user_agents as $user_agent) {
        if (strpos($_SERVER['HTTP_USER_AGENT'], $user_agent) !== FALSE) {
            $mobile = TRUE;
            break;
        }
    }
    return $mobile;
};;


/**
 * post_url
 * Executa uma URL externa com CURL
 *
 * @param string $url 
 * @param string $params 
 */

function post_url($url, $params = false)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("content-type: application/json"));

    if ($params) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // echo $ch;
    $ret = curl_exec($ch);
    curl_close($ch);
    return $ret;
};;

/**
 * montarLink
 * Monta um link a partir de um texto
 * @param mixed $texto
 * @return mixed
 */
function montarLink($texto)
{
    if (!is_string($texto)) {
        return $texto;
    }

    $er = "/((http|https|ftp|ftps):\/\/(w+\.)?|\.)([a-zA-Z0-9]+|_|-)+(\.(([0-9a-zA-Z]|-|_|\/|\?|=|&)+))+/i";
    preg_match_all($er, $texto, $match);

    foreach ($match[0] as $link) {
        //coloca o 'http://' caso o link não o possua
        if (stristr($link, "http://") === false && stristr($link, "https://") === false) {
            $link_completo = "http://" . $link;
        } else {
            $link_completo = $link;
        }

        $link_len = strlen($link);

        //troca "&" por "&", tornando o link válido pela W3C
        $web_link = str_replace("&", "&amp;", $link_completo);
        $texto = str_ireplace($link, "<a href=\"" . $web_link . "\" target=\"_blank\">" . (($link_len > 60) ?
            substr($web_link, 0, 25) . "..." . substr($web_link, -15) :
            $web_link) . "</a>", $texto);
    }
    return $texto;
};;


/**
 * get_string_between
 * Retorna a String contida, entre a String inicial e String final informada
 * 
 * @param string $string - Texto completo
 * @param string $start  - String inicial
 * @param string $end    - String final
 * @return string
 */
function get_string_between($string, $start, $end)
{
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
};;


/**
 * buscaLog
 * Retorna os Logs do Registro da Tabela informados
 *
 * @param mixed $tabela
 * @param mixed $registro
 * @return array
 */
function buscaLog($tabela, $registro)
{
    $logs      = new LogMonModel();
    $logId = $logs->get_logs_last($tabela, $registro);
    $dados = [];
    $operacao = '';
    if ($logId) {
        foreach ($logId as $document) {
            $operacao    = $document->log_operacao;
            $user_nam    = $document->log_id_usuario;
            $data_alt   = dataDbToBr($document->log_data);
        }
        if ($operacao != '') {
            $dados['operacao']      = $operacao;
            $dados['usua_alterou']  = $user_nam;
            $dados['data_alterou']  = $data_alt;
            $dados['tabela']        = $tabela;
            $dados['registro']      = $registro;
        }
    } else {
        $dados['operacao']      = '';
        $dados['usua_alterou']  = '';
        $dados['data_alterou']  = '';
        $dados['tabela']        = '';
        $dados['registro']      = '';
    }

    return $dados;
};;

/**
 * buscaLog
 * Retorna os Logs do Registro da Tabela informados
 *
 * @param mixed $tabela
 * @param mixed $registro
 * @return array
 */
function buscaLogBatch($tabela, $registros)
{
    $logs = new \App\Models\LogMonModel();
    $logList = $logs->get_logs_lastvarios($tabela, $registros);
    // debug($logList);
    $dados = [];

    foreach ($logList as $log) {
        $registro = $log->last_record->log_id_registro;
        $dados[$registro] = [
            'operacao'     => $log->last_record->log_operacao,
            'usua_alterou' => $log->last_record->log_id_usuario,
            'data_alterou' => dataDbToBr($log->last_record->log_data),
            'tabela'       => $tabela,
            'registro'     => $registro,
        ];
    }

    return $dados;
}

/**
 * buscaLog
 * Retorna os Logs do Registro da Tabela informados
 *
 * @param mixed $tabela
 * @param mixed $registro
 * @return array
 */
function buscaLogTabela($tabela, $registros, $tipo = false)
{
    $logs      = new LogMonModel();
    $logId = $logs->get_logs_lastVarios($tabela, $registros, $tipo);
    $dados = [];
    $operacao = '';
    // $ct = 0;
    if ($logId) {
        foreach ($logId as $document) {
            // debug($document, true);
            $operacao    = $document->last_record->log_operacao;
            $user_nam    = $document->last_record->log_id_usuario;
            $registro    = $document->last_record->log_id_registro;
            $data_alt    = dataDbToBr($document->last_record->log_data);
            // if ($operacao != '') {
            if ((empty($tipo) && !empty($operacao)) || (!empty($tipo) && $operacao == $tipo)) {                
                $dados[$registro]['operacao']      = $operacao;
                $dados[$registro]['usua_alterou']  = $user_nam;
                $dados[$registro]['data_alterou']  = $data_alt;
                $dados[$registro]['tabela']        = $tabela;
                $dados[$registro]['registro']      = $registro;
                // $ct++;
            }
        }
    }

    return $dados;
};;

/**
 * moedaToFloat
 * Converte o Valor em String para um float com ponto
 * separando os decimais
 * @param mixed $valor
 * @return float
 */
function moedaToFloat($valor)
{
    // debug('Chegou '.$valor, false);
    if ($valor == 'null' || $valor == null) {
        $valor_ins = 0;
    } else {
        if (strpos($valor, '.') != '') {
            if (strpos($valor, ',') != '') {
                $valor = str_replace('.', '', $valor);
                $valor = str_replace(',', '.', $valor);
            }
        } else {
            $valor = str_replace(',', '.', $valor);
        }
        // $valor_ins = $valor_ini;
        // } else {
        // $valor_ins = str_replace(',', '.', $valor_ini);
        // }
        // if(strpos($valor_ins,'R$') != ''){
        // $valor_ins = substr($valor_ins,3);
        $valor_ins = floatval(preg_replace("/[^-0-9\.]/", "", $valor));
        // }
    }
    // debug(gettype($valor_ins),false);
    // debug('Saiu '.$valor_ins, false);
    return $valor_ins;
}

function moedaParaFloat($moeda)
{
    debug($moeda);
    // Remove o símbolo da moeda e espaços
    $moeda1 = preg_replace('/[R\$\s]+/', '', $moeda); // Remove 'R$', espaços e múltiplos espaços
    debug($moeda1);
    $moeda2 = str_replace(' ', '', $moeda1); // Remove os pontos (separadores de milhar)
    $moeda3 = str_replace('.', '', $moeda2); // Remove os pontos (separadores de milhar)
    debug($moeda3);
    $moeda4 = str_replace(',', '.', $moeda3); // Substitui a vírgula por ponto
    debug(ltrim($moeda4));
    var_dump(ltrim($moeda4));
    $mofloat = floatval($moeda4);
    debug($mofloat);
    // Converte a string resultante para float
    return floatval(ltrim($moeda4));
}

/**
 * floatToMoeda
 * Converte o Valor em Float para uma String formatada com 2 casas decimais
 * separadas por vírgula
 * @param mixed $valor
 * @return string
 */
// use NumberFormatter;
function floatToMoeda($valor)
{
    // debug('Chegou '.$valor);
    $moeda = $valor;
    if (floatval($valor)) {
        $fmt = numfmt_create('pt-BR', NumberFormatter::CURRENCY);
        $moeda = numfmt_format($fmt, floatval($valor));
    }
    // debug('Saiu '.$valor);
    return $moeda;
};;

/**
 * floatToQuantia
 * Converte o Valor em Float para uma String formatada com
 * as casas decimais informadas no parametro decimais
 * separadas por ponto
 * @param mixed $valor
 * @param mixed $decimais
 * @return string
 */
function floatToQuantia($valor, $decimais = 3)
{
    $quantia = $valor;
    if (fmod((float)$quantia, 1) != 0) { // TEM DECIMAIS
        $fmt = numfmt_create('pt-BR', NumberFormatter::DECIMAL);
        numfmt_set_attribute($fmt, NumberFormatter::MIN_FRACTION_DIGITS, $decimais);
        numfmt_set_attribute($fmt, NumberFormatter::MAX_FRACTION_DIGITS, $decimais);
        $quantia = numfmt_format($fmt, floatval($valor));
    }
    return $quantia;
};;

/**
 * buscaArquivos
 * Busca os arquivos da pasta informada
 * @param string $pastaapp
 * @param boolean $completo
 * @param array $exceto
 * @return array
 */
function buscaArquivos($pastaapp, $completo = true, $exceto = [])
{
    $dir = new DirectoryIterator($pastaapp);
    // array contendo os diretórios permitidos    
    $diretoriosPermitidos = array("app", "Controllers", "Estoque", "Filters", "Helpers", "Libraries", "Views", "Models", "Estoqu", "Rh", "Rechum", "Config", "public", "assets", "jscript");
    $arquivosPermitidos = array("php", "js");

    $arquivos = [];
    foreach ($dir as $file) {
        // verifica se $file é diferente de '.' ou '..'
        if (!$file->isDot()) {
            // listando somente os diretórios
            if ($file->isDir()) {
                // atribui o nome do diretório a variável
                $dirName = $file->getFilename();
                // listando somente o diretório permitido
                if (in_array($dirName, $diretoriosPermitidos)) {
                    // subdiretórios
                    $caminho = $file->getPathname();
                    // chamada da função de recursividade
                    $recurs = recursivo($caminho, $dirName, $diretoriosPermitidos, $arquivosPermitidos, $completo, $exceto);
                    for ($r = 0; $r < count($recurs); $r++) {
                        array_push($arquivos, $recurs[$r]);
                    }
                }
            }
            // listando somente os arquivos do diretório
            if ($file->isFile()) {
                if (in_array($file->getExtension(), $arquivosPermitidos)) {
                    if (!in_array($file->getFilename(), $exceto)) {
                        if ($completo) {
                            $arquiv = $file->getPathname();
                        } else {
                            $arquiv = $file->getBasename('.php');
                        }
                        array_push($arquivos, $arquiv);
                    }
                }
                //
            }
        }
    }
    return $arquivos;
};;

function recursivo($caminho, $dirName, $diretoriosPermitidos, $arquivosPermitidos, $completo, $exceto)
{
    global $dirName;
    $DI = new DirectoryIterator($caminho);
    $arq = [];
    foreach ($DI as $file) {
        if (!$file->isDot()) {
            if ($file->isDir()) {
                // atribui o nome do diretório a variável
                $dirName = $file->getFilename();
                // listando somente o diretório permitido
                if (in_array($dirName, $diretoriosPermitidos)) {
                    // subdiretórios
                    $caminho = $file->getPathname();
                    // chamada da função de recursividade
                    $recurs = recursivo($caminho, $dirName, $diretoriosPermitidos, $arquivosPermitidos, $completo, $exceto);
                    for ($r = 0; $r < count($recurs); $r++) {
                        array_push($arq, $recurs[$r]);
                    }
                }
            }
            // listando somente os arquivos do diretório
            if ($file->isFile()) {
                if (in_array($file->getExtension(), $arquivosPermitidos)) {
                    // atribui o nome do arquivo a variável
                    if (!in_array($file->getFilename(), $exceto)) {
                        $caminho = $file->getPathname();
                        if ($completo) {
                            $arquiv = $file->getPathname();
                        } else {
                            $arquiv = $file->getBasename('.php');
                        }
                        array_push($arq, $arquiv);
                    }
                }
                //
            }
        }
    }
    return $arq;
};;

/**
 * fmtEtiquetaCorBst
 * Formata a Etiqueta de Cor do Bootstrap
 * @param mixed $cor
 * @return string
 */
function fmtEtiquetaCorBst($cor)
{
    $cores["bg-primary"] = "Primária";
    $texto['bg-primary'] = 'text-black';
    $cores['bg-secondary'] = "Secundária";
    $texto['bg-secondary'] = 'text-white';
    $cores['bg-success'] = "Sucesso";
    $texto['bg-success'] = 'text-black';
    $cores['bg-danger'] = "Perigo";
    $texto['bg-danger'] = 'text-black';
    $cores['bg-warning'] = "Atenção";
    $texto['bg-warning'] = 'text-black';
    $cores['bg-info'] = "Informação";
    $texto['bg-info'] = 'text-black';
    $cores['bg-dark'] = "Preto";
    $texto['bg-dark'] = 'text-white';
    $cores['bg-white'] = "Branco";
    $texto['bg-white'] = 'text-black';

    $ret = "<span class='$cor $texto[$cor] px-3 py-1 w-100 d-inline-block rounded rounded-4 text-center text-black text-nowrap'>$cores[$cor]</span>";
    return $ret;
}

/**
 * get_cloudfy_curl
 * Executa a consulta ao Cloudfy
 *
 * @param array $config
 * @param int $filial
 * @param string $tipo -- 'ped' Pedido, 'prod' Produtos
 * @param int $pedido  -- número do pedido
 * @return array
 */

function get_cloudfy_curl($config, $empresa, $inicio = false, $fim = false)
{
    // $inicio = '20250731';
    // $fim = '20250802';
    $api = $config['api_nome'];
    if (!$inicio) {
        $inicio = date('Ymd', strtotime("-1 days"));
    }
    if (!$fim) {
        $fim = date('Ymd');
    }
    debug('Início ' . $inicio);
    debug('Fim    ' . $fim);

    $param = array(
        "Parameters" => array(
            "AccKey" => $config['api_acckey'],
            "TokenKey" => $config['api_tokenkey'],
            "ApiName" => $api,
            "Data" => array(
                "Solicitante" => array(
                    "LoginUsr" => $config['api_login'],
                    "NomeUsr" => $config['api_usuario'],
                    "CodEmpresa" => $empresa['emp_codempresa'],
                    "CodFilial" => $empresa['emp_codfilial'],
                ),
                "Filtros" => array(
                    "DataInicio" => $inicio,
                    "DataFim" => $fim,
                    "SituacaoCupom" => 3,
                    "IdentifDesconto" => 1,
                    "IdentifTaxaServico" => 1,
                    "IdentifConsultaProd" => 1,
                    "IdentifConsultaFormaPagto" => 1,
                    "IdentifConsultaProdCancelados" => 2
                )
            )
        )
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $config['api_url']);
    $payload = json_encode($param);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // log_message('info','ch  '.$ch);
    $ret = curl_exec($ch);
    curl_close($ch);
    return json_decode($ret);
};;

function in_array_r($needle, $haystack, $strict = false)
{
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

/**
 * get_cloudfy_curl
 * Executa a consulta ao Cloudfy
 *
 * @param array $config
 * @param int $filial
 * @param string $tipo -- 'ped' Pedido, 'prod' Produtos
 * @param int $pedido  -- número do pedido
 * @return array
 */

function get_opinae_curl($api, $inicio = false, $fim = false)
{
    if (!$inicio) {
        // $inicio = date('Y-m-d',strtotime("-50 days"));
        $inicio = date('Y-m-d', strtotime("2024-08-20"));
    }
    if (!$fim) {
        $fim = date('Y-m-d');
    }
    // $inicio = date('2024-01-01');
    // $fim = date('2024-06-21');
    // debug('Início '.$inicio);
    // debug('Fim    '.$fim);
    $param = array(
        "startDate" => $inicio,
        "endDate" => $fim,
    );
    // debug($param);

    $payload = http_build_query($param);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api['api_url'] . '?' . $payload);
    // curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic " . $api['api_tokenkey']]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // debug($ch);
    // log_message('info','ch  '.$ch);
    $ret = curl_exec($ch);
    curl_close($ch);
    return json_decode($ret);
};;

function formatCNPJ($cnpj)
{
    // Remove caracteres não numéricos
    $cnpj = preg_replace('/\D/', '', $cnpj);

    // Verifica se o CNPJ possui 14 dígitos
    if (strlen($cnpj) != 14) {
        return 'CNPJ inválido';
    }

    // Formata o CNPJ
    return preg_replace(
        '/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/',
        '$1.$2.$3/$4-$5',
        $cnpj
    );
}

function verificarSeEhTime($string)
{
    $formato = 'H:i:s'; // Formato esperado: HH:MM:SS
    $data = \DateTime::createFromFormat($formato, $string);

    // Verifica se a criação foi bem-sucedida e se a string corresponde ao formato
    return $data && $data->format($formato) === $string;
}

function ehDataValida(string $data, string $formato = 'Y-m-d'): bool
{
    $d = DateTime::createFromFormat($formato, $data);
    return $d && $d->format($formato) === $data;
}


/**
 * Função para calcular o SOMARPRODUTO
 *
 * @param array ...$arrays Matrizes de entrada
 * @return float Soma dos produtos
 */
function somarProduto($array1, $array2)
{
    // Verifica se os arrays têm o mesmo tamanho
    if (count($array1) !== count($array2)) {
        throw new InvalidArgumentException('Os arrays devem ter o mesmo tamanho.');
    }

    $soma = 0;

    // Calcula o produto de cada par de elementos e acumula a soma
    for ($i = 0; $i < count($array1); $i++) {
        $soma += $array1[$i] * $array2[$i];
    }

    return $soma;
}
/**
 * Função para calcular a soma condicional (similar ao SOMASE do Excel)
 *
 * @param array $intervalo Array de células para aplicar o critério
 * @param mixed $criterio Critério para a soma
 * @param array $intervalo_soma Array de células para somar (opcional)
 * @return float Soma dos valores que atendem ao critério
 */
function somase(array $intervalo, $criterio, array $intervalo_soma = null)
{
    // Se o intervalo_soma não for fornecido, usa o intervalo como intervalo_soma
    if ($intervalo_soma === null) {
        $intervalo_soma = $intervalo;
    }

    // Verifica se os intervalos têm o mesmo tamanho
    if (count($intervalo) != count($intervalo_soma)) {
        throw new InvalidArgumentException("Os intervalos devem ter o mesmo tamanho.");
    }

    $soma = 0;

    // Itera sobre os intervalos e aplica o critério
    for ($i = 0; $i < count($intervalo); $i++) {
        // Verifica se o valor atual atende ao critério
        if (verificarCriterio($intervalo[$i], $criterio)) {
            $soma += $intervalo_soma[$i];
        }
    }

    return $soma;
}

/**
 * Função auxiliar para verificar se um valor atende ao critério
 *
 * @param mixed $valor Valor a ser verificado
 * @param mixed $criterio Critério para a verificação
 * @return bool Retorna verdadeiro se o valor atender ao critério
 */
function verificarCriterio($valor, $criterio)
{
    // Se o critério for um número ou string, compara diretamente
    if (is_numeric($criterio) || is_string($criterio)) {
        return $valor == $criterio;
    }

    // Se o critério for uma expressão com operadores, avalia a expressão
    if (is_array($criterio) && isset($criterio['operador']) && isset($criterio['valor'])) {
        switch ($criterio['operador']) {
            case '>':
                return $valor > $criterio['valor'];
            case '<':
                return $valor < $criterio['valor'];
            case '>=':
                return $valor >= $criterio['valor'];
            case '<=':
                return $valor <= $criterio['valor'];
            case '=':
                return $valor == $criterio['valor'];
            default:
                return false;
        }
    }

    // Caso não seja um critério conhecido, retorna falso
    return false;
}

/**
 * Encontra a terceira segunda-feira em um mês específico.
 *
 * @param int $ano Ano no formato YYYY.
 * @param int $mes Mês no formato MM (1 para janeiro, 2 para fevereiro, etc.).
 * @return string|false A terceira segunda-feira no formato YYYY-MM-DD ou false se não houver a terceira segunda-feira no mês.
 */
function dataXnoMes($ano, $mes, $diasemana, $indice)
{
    // Cria o objeto DateTime para o primeiro dia do mês e ano fornecidos
    $data = new \DateTime("$ano-$mes-01");

    // Verifica se o mês e ano são válidos
    if ($data->format('n') != $mes) {
        throw new InvalidArgumentException("Mês inválido.");
    }

    // Variável para contar quantas vezes o diasemana foram encontradas
    $contadorDiasemana = 0;

    // Itera pelos dias do mês
    while ($data->format('n') == $mes) {
        // Verifica se o dia da semana é igual ao dia da semana informado (1 em PHP para segunda-feira)
        if ($data->format('N') == $diasemana) {
            $contadorDiasemana++;

            // Se o contador for igual ao indice, retorna a data
            if ($contadorDiasemana == $indice) {
                return $data->format('Y-m-d');
            }
        }

        // Avança um dia
        $data->modify('+1 day');
    }

    // Retorna false se não encontrar a terceira segunda-feira no mês
    return false;
}

function contarDiasDaSemana($inicio, $fim, $diaDaSemana)
{
    // Valida o dia da semana (0 = domingo, 1 = segunda, ..., 6 = sábado)
    if ($diaDaSemana < 0 || $diaDaSemana > 6) {
        throw new InvalidArgumentException('O dia da semana deve estar entre 0 (domingo) e 6 (sábado).');
    }
    $dtini = new \DateTime($inicio);
    $dtfim = new \DateTime($fim);

    $dtfim->modify('+1 day'); // Inclui o último dia do intervalo

    $contador = 0;

    // Loop através do intervalo de datas
    while ($dtini < $dtfim) {
        // Verifica se o dia atual corresponde ao dia da semana desejado
        if ($dtini->format('w') == $diaDaSemana) {
            $contador++;
        }
        // Avança para o próximo dia
        $dtini->modify('+1 day');
    }

    return $contador;
}

function formataQuantia($qtia)
{
    $ret = [];
    if(!is_null($qtia)){
        // Garantir que ',' seja substituído por '.' para formatação numérica
        $qtia = str_replace(',', '.', $qtia);

        // Garantir que é float
        $qtia = (float) $qtia;


        if (fmod($qtia, 1) == 0) { // NÃO TEM DECIMAIS
            $ret['qtiv'] = str_replace('.', '', intval($qtia));
            $qtia = number_format($qtia, 3, '.', '');
            $ret['qtia'] = "<div class='text-end'>" . (int)$qtia . "</div>";
            $ret['qtis'] = (int)$qtia;
            $ret['dec'] = 0;
        } else { // tem decimais
            $qtia = number_format($qtia, 3, '.', '');
            $decimalPart = explode('.', (string)$qtia)[1];

            $ret['qtiv'] = $qtia;

            if (fmod((int)$decimalPart, 100) == 0) {
                $ret['qtia'] = "<div class='text-end'>" . number_format($qtia, 1, ',', '') . "</div>";
                $ret['qtis'] = number_format($qtia, 1, '.', '');
                $ret['dec'] = 1;
            } else if (fmod((int)$decimalPart, 10) == 0) {
                $ret['qtia'] = "<div class='text-end'>" . number_format($qtia, 2, ',', '') . "</div>";
                $ret['qtis'] = number_format($qtia, 2, '.', '');
                $ret['dec'] = 2;
            } else {
                $ret['qtia'] = "<div class='text-end'>" . number_format($qtia, 3, ',', '') . "</div>";
                $ret['qtis'] = number_format($qtia, 3, '.', '');
                $ret['dec'] = 3;
            }
        }
    } else {
        $ret['qtia'] = "<div class='text-end'>0</div>";
        $ret['qtis'] = '0';
        $ret['dec'] = 0;
        $ret['qtiv'] = '';
    }
    return $ret;
}

function solverLP($objectiveFunc, $targetValue, $variablesBounds, $constraints)
{
    // Inicialização
    $variables = [];
    foreach ($variablesBounds as $bound) {
        // Inicializa variáveis aleatórias dentro dos limites
        $variables[] = mt_rand($bound[0] * 100, $bound[1] * 100) / 100;
    }

    $tolerance = 0.001;
    $maxIterations = 100000;
    $iteration = 0;

    while ($iteration < $maxIterations) {
        $currentValue = $objectiveFunc($variables);

        // Verifica se o valor atual está dentro da margem do valor alvo
        if (abs($currentValue - $targetValue) < $tolerance) {
            return [
                'optimal_value' => $currentValue,
                'variables' => $variables
            ];
        }

        // Ajusta as variáveis
        foreach ($variables as $index => &$var) {
            $originalValue = $var;

            // Testa incremento e decremento
            foreach ([-1, 1] as $direction) {
                $newVar = $originalValue + $direction * $tolerance;

                // Verifica limites
                if ($newVar < $variablesBounds[$index][0] || $newVar > $variablesBounds[$index][1]) {
                    continue;
                }

                $variables[$index] = $newVar;

                // Verifica se atende às restrições
                if (checkConstraints($variables, $constraints)) {
                    $newValue = $objectiveFunc($variables);
                    if (abs($newValue - $targetValue) < abs($currentValue - $targetValue)) {
                        $currentValue = $newValue;
                    } else {
                        // Reverte a mudança se não melhorou
                        $variables[$index] = $originalValue;
                    }
                } else {
                    // Reverte a mudança se não atende às restrições
                    $variables[$index] = $originalValue;
                }
            }
        }

        $iteration++;
    }

    return [
        'optimal_value' => $currentValue,
        'variables' => $variables
    ];
}

function checkConstraints($variables, $constraints)
{
    foreach ($constraints as $constraint) {
        $result = 0;
        foreach ($constraint['coefficients'] as $index => $coef) {
            $result += $coef * $variables[$index];
        }
        // Verifica se a restrição é satisfeita (≤)
        if ($result > $constraint['limit']) {
            return false;
        }
    }
    return true;
}

function solver($valores, $valorTotal)
{
    $somaValores = array_sum($valores);

    if ($somaValores == 0) {
        return [
            'percentual' => 0,
            'valores_ajustados' => array_fill(0, count($valores), 0)
        ]; // Se a soma for zero, retorna um array de zeros
    }

    $percentual = $valorTotal / $somaValores; // Calcula o percentual a ser aplicado
    $resultados = [];

    foreach ($valores as $valor) {
        // Aplica o percentual aos valores originais
        $resultados[] = $valor * $percentual;
    }

    return [
        'percentual' => $percentual,
        'valores_ajustados' => $resultados
    ];
}

function soma(...$args)
{
    $total = 0;

    foreach ($args as $arg) {
        if (is_array($arg)) {
            // Se o argumento for um array, soma os elementos do array
            $total += array_sum($arg);
        } else {
            // Caso contrário, soma o valor diretamente
            $total += $arg;
        }
    }

    return $total;
}

function isValidDate($date, $format = 'Y-m-d')
{
    $dateTime = DateTime::createFromFormat($format, $date);

    // Verifica se a data corresponde ao formato especificado
    return $dateTime && $dateTime->format($format) === $date;
}


function validarEAN13($codigo)
{
    $codigo = preg_replace('/[^0-9]/', '', $codigo);

    if (strlen($codigo) !== 13) {
        return false;
    }

    $soma = 0;
    for ($i = 0; $i < 12; $i++) {
        $digito = (int)$codigo[$i];
        $soma += ($i % 2 === 0) ? $digito : $digito * 3;
    }

    $dvCalculado = (10 - ($soma % 10)) % 10;
    $dvInformado = (int)$codigo[12];

    return $dvCalculado === $dvInformado;
}

function traduzirEmbalagem($packaging)
{
    $mapa = [
        'bag' => 'Saco',
        'bar' => 'Barra',
        'bottle' => 'Garrafa',
        'box' => 'Caixa',
        'bucket' => 'Balde',
        'can' => 'Lata',
        'carton' => 'Cartucho',
        'case' => 'Estojo',
        'container' => 'Recipiente',
        'cup' => 'Copo',
        'dispenser' => 'Dispenser',
        'drum' => 'Tambor',
        'film' => 'Filme',
        'flask' => 'Frasco',
        'flow-pack' => 'Embalagem Flow-Pack',
        'glass' => 'Vidro',
        'jar' => 'Pote',
        'jug' => 'Jarro',
        'net' => 'Rede',
        'package' => 'Embalagem',
        'pack' => 'Pacote',
        'pail' => 'Balde',
        'paper' => 'Papel',
        'plastic' => 'Plástico',
        'pot' => 'Pote',
        'pouch' => 'Bolsa',
        'roll' => 'Rolo',
        'sachet' => 'Sachê',
        'sleeve' => 'Manga',
        'stick' => 'Bastão',
        'strip' => 'Tira',
        'tablet' => 'Comprimido',
        'tray' => 'Bandeja',
        'tube' => 'Tubo',
        'tub' => 'Pote Grande',
        'wrapper' => 'Invólucro',
        'vacuum-pack' => 'Embalagem a Vácuo',
        'skin' => 'Pele Protetora',
        'tin' => 'Lata',
        'envelope' => 'Envelope',
        'multilayer' => 'Multicamadas',
        'aerosol' => 'Aerossol',
        'spray' => 'Spray',
        'canister' => 'Recipiente de Pressão',
    ];

    $traduzida = [];

    foreach (explode(',', $packaging) as $item) {
        $item = trim($item);
        $traduzida[] = $mapa[$item] ?? ucfirst($item);
    }

    return implode(', ', $traduzida);
}

function obterTokenGs1() {
    $clientId     = '60af91e4-4543-447b-aeb9-e22b76ac2af7';
    $clientSecret = 'd863f5cc-09e8-465a-8c31-d723db188136';
    $username     = 'douglas@4web.net.br';
    $password     = 'Nkb@2e00';

    $url = 'https://api.gs1br.org/oauth/access-token';

    // $body = json_encode([
    //     'grant_type' => 'password',
    //     'username'   => $username,
    //     'password'   => $password,
    // ]);

    $body = [
        "grant_type" => "password",
        "username" => "douglas@4web.net.br",
        "password" => "Nkb@2e00"
    ];

    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_USERPWD        => "$clientId:$clientSecret", // faz o Basic Auth corretamente
        CURLOPT_HTTPHEADER     => $headers,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    echo "<pre>";
    echo "Status HTTP: $httpCode\n";
    echo "Resposta:\n$response\n";
    if ($curlError) echo "CURL ERRO: $curlError\n";
    echo "</pre>";

    $json = json_decode($response, true);
    return $json['access_token'] ?? null;
}


function buscarProdutoMultiFonte($gtin) {
    // === 1. GS1 Brasil ===
    // $token = obterTokenGs1();
    // if ($token) {
    //     $url = "https://api-hml.gs1br.org/cnp/gtin/$gtin";
    //     $ch = curl_init($url);
    //     curl_setopt_array($ch, [
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_HTTPHEADER     => [
    //             "Authorization: Bearer $token",
    //             "Accept: application/json"
    //         ]
    //     ]);
    //     $res = curl_exec($ch);
    //     curl_close($ch);
    //     $json = json_decode($res, true);

    //     if (!empty($json['gtin'])) {
    //         return [
    //             'codigo'     => $json['gtin'],
    //             'marca'      => $json['marca'] ?? '',
    //             'descricao'  => $json['descricao'] ?? '',
    //             'imagem'     => $json['imagem_url'] ?? '',
    //             'apresentacao'  => $json['quantidade_itens_conteudo'] ?? '',
    //             'fonte'      => 'GS1 Brasil'
    //         ];
    //     }
    // }

    // === 2. Cosmos ===
    $tokenCosmos = 'f2ILLVIInz49t4bUZnUZdw';
    $url = "https://api.cosmos.bluesoft.com.br/gtins/{$gtin}.json";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "X-Cosmos-Token: $tokenCosmos"
        ]
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($res, true);

    if (!empty($data['gtin'])) {
        return [
            'codigo'     => $data['gtin'],
            'marca'      => $data['brand']['name'] ?? '',
            'descricao'  => $data['description'] ?? '',
            'imagem'     => $data['thumbnail'] ?? '',
            'apresentacao'  => $data['net_weight'] . 'g',
            'fonte'      => 'Cosmos'
        ];
    }

    // === 3. Open Food Facts ===
    $url = "https://world.openfoodfacts.org/api/v0/product/{$gtin}.json";
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true]);
    $res = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($res, true);

    if (!empty($json['product']['product_name'])) {
        $produto = $json['product'];
        $mapa = [
            'bottle' => 'Garrafa', 'box' => 'Caixa', 'can' => 'Lata',
            'jar' => 'Pote', 'pack' => 'Pacote', 'plastic' => 'Plástico',
            'glass' => 'Vidro', 'sachet' => 'Sachê', 'bag' => 'Saco',
            'carton' => 'Cartucho', 'tube' => 'Tubo', 'tray' => 'Bandeja', 'pot' => 'Pote'
        ];

        $embalagem = '';
        if (!empty($produto['packaging'])) {
            $itens = explode(',', strtolower($produto['packaging']));
            $traduzidos = array_map(fn($i) => $mapa[trim($i)] ?? ucfirst(trim($i)), $itens);
            $embalagem = implode(', ', array_unique($traduzidos));
        }

        return [
            'codigo'     => $gtin,
            'marca'      => $produto['brands'] ?? '',
            'descricao'  => $produto['product_name'] ?? '',
            'imagem'     => $produto['image_url'] ?? '',
            'apresentacao'  => $embalagem,
            'fonte'      => 'OpenFoodFacts'
        ];
    }

    // === 4. EANData ===
    $tokenEAN = '1AD225326858CC60';
    $url = "https://eandata.com/feed/?v=3&keycode=$tokenEAN&mode=json&find=$gtin";
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true]);
    $res = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($res, true);

    if (!empty($json['product']['attributes']['product'])) {
        return [
            'codigo'     => $gtin,
            'marca'      => $json['product']['attributes']['manufacturer'] ?? '',
            'descricao'  => $json['product']['attributes']['product'] ?? '',
            'imagem'     => $json['product']['images'][0] ?? '',
            'apresentacao'  => '',
            'fonte'      => 'EANData'
        ];
    }

    // === 5. Produto.xyz ===
    $url = "https://produto.xyz/v1/gtin/$gtin";
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true]);
    $res = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($res, true);

    if (!empty($json['Product']['gtin'])) {
        return [
            'codigo'     => $json['Product']['gtin'],
            'marca'      => $json['Product']['manufacturer'] ?? '',
            'descricao'  => $json['Product']['name'] ?? '',
            'imagem'     => '',
            'apresentacao'  => '',
            'fonte'      => 'Produto.xyz'
        ];
    }

    return [
        'fonte' => '',
        'nome' => 'Não encontrado na base de Pesquisa',
        'marca' => '',
        'quantidade' => '',
        'embalagem' => '',
        'apresentacao' => '',
        'imagem' => null,
        'categorias' => [],
        'origem' => [],
    ];
}


function get_sults_curl($api)
{
    // debug($param);

    $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $api['api_url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: ".$api['api_tokenkey'],
                "Content-Type: application/json;charset=UTF-8"
            ],
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', 'Erro ao consumir API: ' . $error);
            return false;
        }

        return json_decode($response, true);    
};;


function apenasNumeros(string $texto): string
{
    return preg_replace('/\D/', '', $texto);
}

function formatarCPF(string $texto): ?string
{
    // Remove tudo que não for número
    $numeros = preg_replace('/\D/', '', $texto);

    // Verifica se tem exatamente 11 dígitos
    if (strlen($numeros) !== 11) {
        return null; // Retorna null se não for um CPF válido
    }

    // Formata como CPF
    return preg_replace(
        "/(\d{3})(\d{3})(\d{3})(\d{2})/",
        "$1.$2.$3-$4",
        $numeros
    );
}

function mostra_botao($campo, $condic)
{
    $valido = false;
    if ($condic['cond'] == 'contem') {
        if (in_array($campo, $condic['valor'])) {
            $valido = true;
        }
    }
    if ($condic['cond'] == 'igual') {
        if (is_array($condic['valor'])) {
            if (in_array($campo, $condic['valor'])) {
                $valido = true;
            }
        } else {
            if ($campo == $condic['valor']) {
                $valido = true;
            }
        }
    }
    if ($condic['cond'] == 'maior') {
        if ($campo > $condic['valor']) {
            $valido = true;
        }
    }
    if ($condic['cond'] == 'menor') {
        if ($campo < $condic['valor']) {
            $valido = true;
        }
    }
    if ($condic['cond'] == 'maiorouigual') {
        if ($campo >= $condic['valor']) {
            $valido = true;
        }
    }
    if ($condic['cond'] == 'menorouigual') {
        if ($campo <= $condic['valor']) {
            $valido = true;
        }
    }
    return $valido;
}
