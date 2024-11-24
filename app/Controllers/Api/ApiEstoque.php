<?php namespace App\Controllers\Api;

use App\Controllers\Auth;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Config\ConfigUsuarioModel;
use App\Models\Estoqu\EstoquEntradaModel;
use App\Models\Estoqu\EstoquFornecedorModel;
use App\Models\Estoqu\EstoquMarcaModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquSaidaModel;
use App\Models\Estoqu\EstoquUndMedidaModel;

class ApiEstoque extends Auth
{
    public $empresa;
    public $produto;
    public $marca;
    public $saida;
    public $entrada;
    public $common;
    public $usuario;
    public $fornecedor;
    public $unidades;

    public function __construct()
    {
        $this->empresa       = new ConfigEmpresaModel();        
        $this->produto       = new EstoquProdutoModel();
        $this->marca         = new EstoquMarcaModel();
        $this->saida         = new EstoquSaidaModel();
        $this->entrada       = new EstoquEntradaModel();
        $this->common        = new CommonModel();
		$this->usuario       = new ConfigUsuarioModel();
		$this->fornecedor    = new EstoquFornecedorModel();
		$this->unidades      = new EstoquUndMedidaModel();
        helper('funcoes_helper');
    }

    /**
     * getSaldo
     * Retorna o Saldo em Estoque dos produtos da empresa informada no parametro
     * @return void
     */
    public function getSaldo(){
        if($this->request->header('Authorization') != null){
            $token = $this->request->header('Authorization')->getValue();
            if($this->validateToken($token) == true){
                $token      = $this->request->header('Authorization')->getValue();
                $inform     = get_object_vars($this->validateToken($token));
                $dados      = get_object_vars($inform['data']);
                $usuario    = $dados['id'];
                log_message('info','Usuário: '.$usuario.' Função: getSaldo');

                $empresa       = $this->request->getVar('empresa');
                $deposito       = $this->request->getVar('deposito');

                $saldos       = $this->produto->getSaldos($deposito, false, $empresa);
                
                // echo $this->api->getLastQuery();
                $prods      = [];
				for($d=0;$d<sizeof($saldos);$d++){
					$chave = $saldos[$d]; 
                    $prods[$d]['proid']        = $chave['pro_id'];
                    $prods[$d]['pronome']      = $chave['pro_nome'];
                    $prods[$d]['undsigla']     = $chave['und_sigla'];
                    log_message('info','Produto: '.$chave['pro_nome'].' Função: getSaldo');
                    log_message('info','Und: '.$chave['und_sigla'].' Função: getSaldo');
                    log_message('info','Und Compra: '.$chave['und_sigla_compra'].' Função: getSaldo');
                    $minimo = $chave['mmi_minimo']?? 0;
                    $maximo = $chave['mmi_maximo']?? 0;
                    if($chave['und_id'] != $chave['und_id_compra']){
                        $conv = $this->unidades->getConversaoDePara($chave['und_id_compra'], $chave['und_id']);
                        if(count($conv) > 0){
                            $expressao = $minimo . ' ' . $conv[0]['cvs_operador'] . ' ' . $conv[0]['cvs_fator'];
                            log_message('info','exp minimo: '.$expressao.' Função: getSaldo');
                            eval('$minimo = ' . $expressao . ';');
                            log_message('info','minimo: '.$minimo.' Função: getSaldo');
                            $expressao = $maximo . ' ' . $conv[0]['cvs_operador'] . ' ' . $conv[0]['cvs_fator'];
                            log_message('info','exp maximo: '.$expressao.' Função: getSaldo');
                            eval('$maximo = ' . $expressao . ';');
                            log_message('info','maximo: '.$maximo.' Função: getSaldo');
                        }
                    }
                    $prods[$d]['minimo']         = (string) formataQuantia($minimo)['qtis'];
                    $prods[$d]['maximo']         = (string) formataQuantia($maximo)['qtis'];
                    $prods[$d]['saldo']         = (string) formataQuantia($chave['saldo'])['qtis'];
                }
                log_message('info','Resultado: '.json_encode($prods).' Função: getSaldo');
                return $this->respond($prods,200);
            } else {
                return $this->respond(['message'=>'Token Inválido'],401);
            }
        } else { 
            return $this->respond(['message'=>'Não Autorizado'],401);
        }
    }

    /**
     * getmarcacodbar
     * Retorna os dados do produto e da marca pelo cõdigo de barras informado
     * @return void
     */
    public function getmarcacodbar(){
        if($this->request->header('Authorization') != null){
            $token = $this->request->header('Authorization')->getValue();
            if($this->validateToken($token) == true){
                $token      = $this->request->header('Authorization')->getValue();
                $inform     = get_object_vars($this->validateToken($token));
                $dados      = get_object_vars($inform['data']);
                $usuario    = $dados['id'];
                log_message('info','Usuário: '.$usuario.' Função: getmarcacodbar');

                $empresa       = $this->request->getVar('empresa');
                $deposito      = $this->request->getVar('deposito');
                $codbar        = $this->request->getVar('codbar');

                $produtos            = $this->marca->getMarcaCod($codbar);
                $ret = [];
                if(sizeof($produtos) <= 0){
                    $ret[0]['pro_id']      = '-1';
                } else {
                    $qtia = formataQuantia(isset($produtos[0]['mar_conversao'])?$produtos[0]['mar_conversao']:0);
                    $ret[0]['proid']        = $produtos[0]['pro_id'];
                    $ret[0]['pronome']      = $produtos[0]['pro_nome'];
                    $ret[0]['promarca']     = $produtos[0]['mar_nome'].' - '.$produtos[0]['mar_apresenta'];
                    $ret[0]['fatorconv']     = $qtia['qtiv'];
                    $ret[0]['undmarca']     = $produtos[0]['und_sigla_marca'];
                    $ret[0]['undprod']      = $produtos[0]['und_sigla_prod'];
                    $ret[0]['undid']        = $produtos[0]['und_prod'];
                    $saldos       = $this->produto->getSaldos($deposito, $produtos[0]['pro_id'], $empresa);
                    $saldo = formataQuantia(isset($saldos[0]['saldo'])?$saldos[0]['saldo']:0);
                    $ret[0]['saldo']         = (String) $saldo['qtis'];
                }
                log_message('info','Resultado: '.json_encode($ret).' Função: getmarcacodbar');
                return $this->respond($ret,200);
            } else {
                return $this->respond(['message'=>'Token Inválido'],401);
            }
        } else { 
            return $this->respond(['message'=>'Não Autorizado'],401);
        }
    }

    /**
     * gravaSaida
     * Retorna os dados do produto e da marca pelo cõdigo de barras informado
     * @return void
     */
    public function gravasaida(){
        if($this->request->header('Authorization') != null){
            $token = $this->request->header('Authorization')->getValue();
            if($this->validateToken($token) == true){
                $token      = $this->request->header('Authorization')->getValue();
                $inform     = get_object_vars($this->validateToken($token));
                $dados      = get_object_vars($inform['data']);
                $usuario    = $dados['id'];
                $user = $this->usuario->getUsuarioId($usuario);
                session()->set('usu_nome', $user[0]['usu_nome']);
                log_message('info','Usuário: '.$usuario.' Função: gravasaida');

                $empresa       = $this->request->getVar('empresa');
                $deposito      = $this->request->getVar('deposito');
                $codbar        = $this->request->getVar('codbar');
                $produto        = $this->request->getVar('produto');
                $quantia        = $this->request->getVar('quantia');
                $destino        = $this->request->getVar('destino');
                $unidade        = $this->request->getVar('unidade');
                $convers        = $this->request->getVar('convers');

                $dados_sai = [
                    'sai_data'  => date('Y-m-d'),
                    'emp_id'    => $empresa,
                    'dep_id'    => $deposito,
                ];
                log_message('info','Saída: '.json_encode($dados_sai).' Função: gravasaida');
                if ($this->saida->save($dados_sai)) {
                    $sai_id = $this->saida->getInsertID();
                    $data_atu = date('Y-m-d H:i:s');
                    $dados_pro = [
                        'sai_id'        => $sai_id,
                        'mar_codigo'    => $codbar,
                        'pro_id'        => $produto,
                        'und_id'        => $unidade,
                        'sap_conversao'   => $convers,
                        'sap_quantia'   => $quantia,
                        'sap_destino'   => $destino,
                        'sap_atualizado' => $data_atu
                    ];
                    log_message('info','Produto: '.json_encode($dados_pro).' Função: gravasaida');
                    $salva = $this->common->insertReg('dbEstoque','est_saida_produto',$dados_pro);
                    if($salva){
                        log_message('info','Resultado: Gravou Produto Função: gravasaida');
                        $ret = [];
                        return $this->respond($ret,200);
                    } else {
                        return $this->respond(['message'=>'Erro ao Gravar Produto'],401);
                    }
                } else {
                    return $this->respond(['message'=>'Erro ao Gravar Saída'],401);
                }
            } else {
                return $this->respond(['message'=>'Token Inválido'],401);
            }
        } else { 
            return $this->respond(['message'=>'Não Autorizado'],401);
        }
    }
 
    /**
     * getFornecedor
     * Retorna o Saldo em Estoque dos produtos da empresa informada no parametro
     * @return void
     */
    public function getFornecedor(){
        if($this->request->header('Authorization') != null){
            $token = $this->request->header('Authorization')->getValue();
            if($this->validateToken($token) == true){
                $token      = $this->request->header('Authorization')->getValue();
                $inform     = get_object_vars($this->validateToken($token));
                $dados      = get_object_vars($inform['data']);
                $usuario    = $dados['id'];
                log_message('info','Usuário: '.$usuario.' Função: getFornecedor');

                $fornec = $this->fornecedor->getFornecedor();
                
                // echo $this->api->getLastQuery();
                $forns      = [];
				for($d=0;$d<sizeof($fornec);$d++){
					$chave = $fornec[$d]; 
                    $forns[$d]['for_id']        = $chave['for_id'];
                    $forns[$d]['for_nome']      = $chave['for_razao'];
                }
                log_message('info','Resultado: '.json_encode($forns).' Função: getFornecedor');
                return $this->respond($forns,200);
            } else {
                return $this->respond(['message'=>'Token Inválido'],401);
            }
        } else { 
            return $this->respond(['message'=>'Não Autorizado'],401);
        }
    }
 
    /**
     * gravaEntrada
     * Retorna os dados do produto e da marca pelo cõdigo de barras informado
     * @return void
     */
    public function gravaentrada(){
        if($this->request->header('Authorization') != null){
            $token = $this->request->header('Authorization')->getValue();
            if($this->validateToken($token) == true){
                $token      = $this->request->header('Authorization')->getValue();
                $inform     = get_object_vars($this->validateToken($token));
                $dados      = get_object_vars($inform['data']);
                $usuario    = $dados['id'];
                $user = $this->usuario->getUsuarioId($usuario);
                session()->set('usu_nome', $user[0]['usu_nome']);
                log_message('info','Usuário: '.$usuario.' Função: gravaEntrada');

                $empresa       = $this->request->getVar('empresa');
                $deposito      = $this->request->getVar('deposito');
                $codbar        = $this->request->getVar('codbar');
                $produto        = $this->request->getVar('produto');
                $quantia        = $this->request->getVar('quantia');
                $preco        = $this->request->getVar('preco');
                $total        = $this->request->getVar('total');
                $unidade        = $this->request->getVar('unidade');
                $convers        = $this->request->getVar('convers');
                $fornece        = $this->request->getVar('fornecedor');
                $total = moedaToFloat($total);

                $dados_ent = [
                    'ent_data'  => date('Y-m-d'),
                    'emp_id'    => $empresa,
                    'for_id'    => $fornece,
                    'dep_id'    => $deposito,
                    'com_id'    => null,
                    'ent_valor'    => $total,
                ];
                log_message('info','Entrada: '.json_encode($dados_ent).' Função: gravaentrada');
                if ($this->entrada->save($dados_ent)) {
                    $ent_id = $this->entrada->getInsertID();
                    $data_atu = date('Y-m-d H:i:s');
                    $valor = moedaToFloat($preco);
                    // debug($valor);
                    // debug($total,true);
                    $dados_pro = [
                        'ent_id'        => $ent_id,
                        'mar_codigo'    => $codbar,
                        'pro_id'        => $produto,
                        'und_id'        => $unidade,
                        'enp_quantia'   => $quantia,
                        'enp_valor'     => $valor,
                        'enp_conversao'   => $convers,
                        'enp_total'   => $total,
                        'enp_atualizado' => $data_atu
                    ];
                    log_message('info','Produto: '.json_encode($dados_pro).' Função: gravaentrada');
                    $salva = $this->common->insertReg('dbEstoque','est_entrada_produto',$dados_pro);
                    if($salva){
                        log_message('info','Resultado: Gravou Produto Função: gravaentrada');
                        $ret = [];
                        return $this->respond($ret,200);
                    } else {
                        return $this->respond(['message'=>'Erro ao Gravar Produto'],401);
                    }
                } else {
                    return $this->respond(['message'=>'Erro ao Gravar Entrada'],401);
                }
            } else {
                return $this->respond(['message'=>'Token Inválido'],401);
            }
        } else { 
            return $this->respond(['message'=>'Não Autorizado'],401);
        }
    }
    
}