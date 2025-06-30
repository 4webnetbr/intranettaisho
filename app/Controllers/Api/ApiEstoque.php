<?php

namespace App\Controllers\Api;

use App\Controllers\Auth;
use App\Models\CommonModel;
use App\Models\Config\ConfigEmpresaModel;
use App\Models\Config\ConfigUsuarioModel;
use App\Models\Estoqu\EstoquCompraModel;
use App\Models\Estoqu\EstoquConsumoModel;
use App\Models\Estoqu\EstoquEntradaModel;
use App\Models\Estoqu\EstoquFornecedorModel;
use App\Models\Estoqu\EstoquMarcaModel;
use App\Models\Estoqu\EstoquPedidoModel;
use App\Models\Estoqu\EstoquProdutoModel;
use App\Models\Estoqu\EstoquSaidaModel;
use App\Models\Estoqu\EstoquUndMedidaModel;
use Config\Database;

class ApiEstoque extends Auth
{
    public $empresa;
    public $produto;
    public $marca;
    public $saida;
    public $entrada;
    public $compra;
    public $common;
    public $usuario;
    public $fornecedor;
    public $unidades;
    public $consumo;
    public $pedido;

    public function __construct()
    {
        $this->empresa       = new ConfigEmpresaModel();
        $this->produto       = new EstoquProdutoModel();
        $this->marca         = new EstoquMarcaModel();
        $this->saida         = new EstoquSaidaModel();
        $this->entrada       = new EstoquEntradaModel();
        $this->compra        = new EstoquCompraModel();
        $this->common        = new CommonModel();
        $this->usuario       = new ConfigUsuarioModel();
        $this->fornecedor    = new EstoquFornecedorModel();
        $this->unidades      = new EstoquUndMedidaModel();
        $this->consumo       = new EstoquConsumoModel();
        $this->pedido = new EstoquPedidoModel();
        helper('funcoes_helper');
    }


    public function __call($method, $params)
    {
        // Retorna erro 404 se o método não for encontrado
        return $this->respond(['message' => 'Método Não encontrado'], 404);
    }
    /**
     * getSaldo
     * Retorna o Saldo em Estoque dos produtos da empresa informada no parametro
     * @return void
     */
    public function getSaldo()
    {
        if ($this->request->header('Authorization') != null) {
            $token = $this->request->header('Authorization')->getValue();
            if ($this->validateToken($token) == true) {
                $token      = $this->request->header('Authorization')->getValue();
                $inform     = get_object_vars($this->validateToken($token));
                $dados      = get_object_vars($inform['data']);
                $usuario    = $dados['id'];
                log_message('info', 'Usuário: ' . $usuario . ' Função: getSaldo');

                $empresa       = $this->request->getVar('empresa');
                $deposito       = $this->request->getVar('deposito');

                $saldos       = $this->produto->getSaldos($deposito, false, $empresa);

                // echo $this->api->getLastQuery();
                $prods      = [];
                for ($d = 0; $d < sizeof($saldos); $d++) {
                    $chave = $saldos[$d];
                    $prods[$d]['proid']        = $chave['pro_id'];
                    $prods[$d]['pronome']      = $chave['pro_nome'];
                    $prods[$d]['undsigla']     = $chave['und_sigla'];
                    log_message('info', 'Produto: ' . $chave['pro_nome'] . ' Função: getSaldo');
                    log_message('info', 'Und: ' . $chave['und_sigla'] . ' Função: getSaldo');
                    log_message('info', 'Und Compra: ' . $chave['und_sigla_compra'] . ' Função: getSaldo');
                    $minimo = $chave['mmi_minimo'] ?? 0;
                    $maximo = $chave['mmi_maximo'] ?? 0;
                    if ($chave['und_id'] != $chave['und_id_compra']) {
                        $conv = $this->unidades->getConversaoDePara($chave['und_id_compra'], $chave['und_id']);
                        if (count($conv) > 0) {
                            $expressao = $minimo . ' ' . $conv[0]['cvs_operador'] . ' ' . $conv[0]['cvs_fator'];
                            log_message('info', 'exp minimo: ' . $expressao . ' Função: getSaldo');
                            eval('$minimo = ' . $expressao . ';');
                            log_message('info', 'minimo: ' . $minimo . ' Função: getSaldo');
                            $expressao = $maximo . ' ' . $conv[0]['cvs_operador'] . ' ' . $conv[0]['cvs_fator'];
                            log_message('info', 'exp maximo: ' . $expressao . ' Função: getSaldo');
                            eval('$maximo = ' . $expressao . ';');
                            log_message('info', 'maximo: ' . $maximo . ' Função: getSaldo');
                        }
                    }
                    // debug($chave);
                    $prods[$d]['minimo']         = (string) formataQuantia($minimo)['qtis'];
                    $prods[$d]['maximo']         = (string) formataQuantia($maximo)['qtis'];
                    $prods[$d]['saldo']         = (string) formataQuantia($chave['saldo'])['qtis'];
                }
                log_message('info', 'Resultado: ' . json_encode($prods) . ' Função: getSaldo');
                return $this->respond($prods, 200);
            } else {
                return $this->respond(['message' => 'Token Inválido'], 401);
            }
        } else {
            return $this->respond(['message' => 'Não Autorizado'], 401);
        }
    }

    /**
     * getCompra
     * Retorna as Compras dos produtos da empresa informada no parametro
     * @return void
     */
    public function getCompra()
    {
        if ($this->request->header('Authorization') != null) {
            $token = $this->request->header('Authorization')->getValue();
            if ($this->validateToken($token) == true) {
                $token      = $this->request->header('Authorization')->getValue();
                $inform     = get_object_vars($this->validateToken($token));
                $dados      = get_object_vars($inform['data']);
                $usuario    = $dados['id'];
                log_message('info', 'Usuário: ' . $usuario . ' Função: getCompra');

                $empresa       = $this->request->getVar('empresa');
                // $deposito       = $this->request->getVar('deposito');

                $compras       =  $this->compra->getCompraProdPendente(false, $empresa);

                // echo $this->api->getLastQuery();
                $prods      = [];
                for ($d = 0; $d < sizeof($compras); $d++) {
                    $chave = $compras[$d];
                    $prods[$d]['proid']        = $chave['pro_id'];
                    $prods[$d]['pronome']      = $chave['pro_nome'];
                    $prods[$d]['undsigla']     = $chave['und_sigla'];
                    log_message('info', 'Produto: ' . $chave['pro_nome'] . ' Função: getCompra');
                    log_message('info', 'Und: ' . $chave['und_sigla'] . ' Função: getCompra');
                    $minimo = $chave['mmi_minimo'] ?? 0;
                    $maximo = $chave['mmi_maximo'] ?? 0;
                    if ($chave['und_id'] != $chave['und_id_compra']) {
                        $conv = $this->unidades->getConversaoDePara($chave['und_id_compra'], $chave['und_id']);
                        if (count($conv) > 0) {
                            $expressao = $minimo . ' ' . $conv[0]['cvs_operador'] . ' ' . $conv[0]['cvs_fator'];
                            log_message('info', 'exp minimo: ' . $expressao . ' Função: getCompra');
                            eval('$minimo = ' . $expressao . ';');
                            log_message('info', 'minimo: ' . $minimo . ' Função: getCompra');
                            $expressao = $maximo . ' ' . $conv[0]['cvs_operador'] . ' ' . $conv[0]['cvs_fator'];
                            log_message('info', 'exp maximo: ' . $expressao . ' Função: getCompra');
                            eval('$maximo = ' . $expressao . ';');
                            log_message('info', 'maximo: ' . $maximo . ' Função: getCompra');
                        }
                    }
                    $prods[$d]['minimo']         = (string) formataQuantia($minimo)['qtis'];
                    $prods[$d]['maximo']         = (string) formataQuantia($maximo)['qtis'];
                    $prods[$d]['saldo']         = "0";
                    $prods[$d]['for_id']        = $chave['for_id'];
                    $prods[$d]['for_nome']      = $chave['for_razao'];
                    $prods[$d]['comid']        = $chave['com_id'];
                    $prods[$d]['datacompra']      = dataDbToBr($chave['com_data']);
                    $prods[$d]['entrega']      = isset($chave['cop_previsao'])?dataDbToBr($chave['cop_previsao']):dataDbToBr($chave['com_previsao']);
                    $prods[$d]['qtia']          = (string) formataQuantia($chave['cop_quantia'])['qtis'];
                    $prods[$d]['valor']          = floatToMoeda($chave['cop_valor']);
                    $prods[$d]['total']          = floatToMoeda($chave['cop_total']);

                    $prods[$d]['codbar']          = '';
                    $produto = $this->compra->getCompraProd($chave['com_id'], $chave['pro_id'])[0];
                    if($produto['gru_controlaestoque'] == 'N'){ // busca a marca, traz preenchida e pede a quantidade
                        $marcax = $this->marca->getMarcaProd($chave['pro_id']);
                        // debug($marcax);
                        if($marcax){
                            $prods[$d]['codbar']          = $marcax[0]['mar_codigo'];
                        }
                    }
                }
                log_message('info', 'Resultado: ' . json_encode($prods) . ' Função: getCompra');
                return $this->respond($prods, 200);
            } else {
                return $this->respond(['message' => 'Token Inválido'], 401);
            }
        } else {
            return $this->respond(['message' => 'Não Autorizado'], 401);
        }
    }

    /**
     * getmarcacodbar
     * Retorna os dados do produto e da marca pelo cõdigo de barras informado
     * @return void
     */
    public function getmarcacodbar()
    {
        if ($this->request->header('Authorization') != null) {
            $token = $this->request->header('Authorization')->getValue();
            if ($this->validateToken($token) == true) {
                $token      = $this->request->header('Authorization')->getValue();
                $inform     = get_object_vars($this->validateToken($token));
                $dados      = get_object_vars($inform['data']);
                $usuario    = $dados['id'];
                log_message('info', 'Usuário: ' . $usuario . ' Função: getmarcacodbar');

                $empresa       = $this->request->getVar('empresa');
                $deposito      = $this->request->getVar('deposito');
                $codbar        = $this->request->getVar('codbar');

                $produtos            = $this->marca->getMarcaCod($codbar);
                $ret = [];
                if (sizeof($produtos) <= 0) {
                    $ret[0]['pro_id']      = '-1';
                } else {
                    $qtia = formataQuantia(isset($produtos[0]['mar_conversao']) ? $produtos[0]['mar_conversao'] : 0);
                    $ret[0]['proid']        = $produtos[0]['pro_id'];
                    $ret[0]['pronome']      = $produtos[0]['pro_nome'];
                    $ret[0]['promarca']     = $produtos[0]['mar_nome'] . ' - ' . $produtos[0]['mar_apresenta'];
                    $ret[0]['fatorconv']     = $qtia['qtiv'];
                    $ret[0]['undmarca']     = $produtos[0]['und_sigla_marca'];
                    $ret[0]['undprod']      = $produtos[0]['und_sigla_prod'];
                    $ret[0]['undid']        = $produtos[0]['und_prod'];
                    $saldos       = $this->produto->getSaldos($deposito, $produtos[0]['pro_id'], $empresa);
                    $saldo = formataQuantia(isset($saldos[0]['saldo']) ? $saldos[0]['saldo'] : 0);
                    $ret[0]['saldo']         = (string) $saldo['qtis'];
                }
                log_message('info', 'Resultado: ' . json_encode($ret) . ' Função: getmarcacodbar');
                return $this->respond($ret, 200);
            } else {
                return $this->respond(['message' => 'Token Inválido'], 401);
            }
        } else {
            return $this->respond(['message' => 'Não Autorizado'], 401);
        }
    }

    /**
     * gravaSaida
     * Retorna os dados do produto e da marca pelo cõdigo de barras informado
     * @return void
     */
    // public function gravasaida()
    // {
    //     if ($this->request->header('Authorization') != null) {
    //         $token = $this->request->header('Authorization')->getValue();
    //         if ($this->validateToken($token) == true) {
    //             $token      = $this->request->header('Authorization')->getValue();
    //             $inform     = get_object_vars($this->validateToken($token));
    //             $dados      = get_object_vars($inform['data']);
    //             $usuario    = $dados['id'];
    //             $user = $this->usuario->getUsuarioId($usuario);
    //             session()->set('usu_nome', $user[0]['usu_nome']);
    //             log_message('info', 'Usuário: ' . $usuario . ' Função: gravasaida');

    //             $empresa       = $this->request->getVar('empresa');
    //             $deposito      = $this->request->getVar('deposito');
    //             $codbar        = $this->request->getVar('codbar');
    //             $produto        = $this->request->getVar('produto');
    //             $quantia        = $this->request->getVar('quantia');
    //             $destino        = $this->request->getVar('destino');
    //             $unidade        = $this->request->getVar('unidade');
    //             $convers        = $this->request->getVar('convers');

    //             $dados_sai = [
    //                 'sai_data'  => date('Y-m-d'),
    //                 'emp_id'    => $empresa,
    //                 'dep_id'    => $deposito,
    //             ];
    //             log_message('info', 'Saída: ' . json_encode($dados_sai) . ' Função: gravasaida');
    //             if ($this->saida->save($dados_sai)) {
    //                 $sai_id = $this->saida->getInsertID();
    //                 $data_atu = date('Y-m-d H:i:s');
    //                 $dados_pro = [
    //                     'sai_id'        => $sai_id,
    //                     'mar_codigo'    => $codbar,
    //                     'pro_id'        => $produto,
    //                     'und_id'        => $unidade,
    //                     'sap_conversao'   => $convers,
    //                     'sap_quantia'   => $quantia,
    //                     'sap_destino'   => $destino,
    //                     'sap_atualizado' => $data_atu
    //                 ];
    //                 log_message('info', 'Produto: ' . json_encode($dados_pro) . ' Função: gravasaida');
    //                 $salva = $this->common->insertReg('dbEstoque', 'est_saida_produto', $dados_pro);
    //                 if ($salva) {
    //                     log_message('info', 'Resultado: Gravou Produto Função: gravasaida');
    //                     $ret = [];
    //                     return $this->respond($ret, 200);
    //                 } else {
    //                     $this->saida->delete($sai_id);
    //                     log_message('info', 'Resultado: Erro ao Gravar Produto Função: gravasaida');
    //                     return $this->respond(['message' => 'Erro ao Gravar Produto'], 401);
    //                 }
    //             } else {
    //                 return $this->respond(['message' => 'Erro ao Gravar Saída'], 401);
    //             }
    //         } else {
    //             return $this->respond(['message' => 'Token Inválido'], 401);
    //         }
    //     } else {
    //         return $this->respond(['message' => 'Não Autorizado'], 401);
    //     }
    // }
    public function gravasaida()
    {
        if ($this->request->header('Authorization') != null) {
            $token = $this->request->header('Authorization')->getValue();
            if ($this->validateToken($token) == true) {
                $inform = get_object_vars($this->validateToken($token));
                $dados  = get_object_vars($inform['data']);
                $usuario = $dados['id'];

                $user = $this->usuario->getUsuarioId($usuario);
                session()->set('usu_nome', $user[0]['usu_nome']);
                log_message('info', 'Usuário: ' . $usuario . ' Função: gravasaida');

                $empresa  = $this->request->getVar('empresa');
                $deposito = $this->request->getVar('deposito');
                $codbar   = $this->request->getVar('codbar');
                $produto  = $this->request->getVar('produto');
                $quantia  = $this->request->getVar('quantia');
                $destino  = $this->request->getVar('destino');
                $unidade  = $this->request->getVar('unidade');
                $convers  = $this->request->getVar('convers');

                $db = Database::connect();

                $db->transStart();

                $dados_sai = [
                    'sai_data'  => date('Y-m-d'),
                    'emp_id'    => $empresa,
                    'dep_id'    => $deposito,
                ];
                log_message('info', 'Saída: ' . json_encode($dados_sai) . ' Função: gravasaida');

                if (!$this->saida->save($dados_sai)) {
                    $this->saida->transRollback();
                    return $this->respond(['success' => false, 'message' => 'Erro ao gravar saída'], 500);
                }

                $sai_id = $this->saida->getInsertID();
                $data_atu = date('Y-m-d H:i:s');

                $dados_pro = [
                    'sai_id'         => $sai_id,
                    'mar_codigo'     => $codbar,
                    'pro_id'         => $produto,
                    'und_id'         => $unidade,
                    'sap_conversao'  => $convers,
                    'sap_quantia'    => $quantia,
                    'sap_destino'    => $destino,
                    'sap_atualizado' => $data_atu
                ];
                log_message('info', 'Produto: ' . json_encode($dados_pro) . ' Função: gravasaida');

                $salvaProduto = $this->common->insertReg('dbEstoque', 'est_saida_produto', $dados_pro);

                if (!$salvaProduto) {
                    $db->transRollback();
                    return $this->respond(['success' => false, 'message' => 'Erro ao gravar produto'], 500);
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    return $this->respond(['success' => false, 'message' => 'Erro na transação'], 500);
                }

                log_message('info', 'Saída gravada com sucesso Função: gravasaida');
                return $this->respond(['success' => true, 'id_saida' => $sai_id], 200);
            } else {
                return $this->respond(['success' => false, 'message' => 'Token Inválido'], 401);
            }
        } else {
            return $this->respond(['success' => false, 'message' => 'Não autorizado'], 401);
        }
    }

    /**
     * getFornecedor
     * Retorna o Saldo em Estoque dos produtos da empresa informada no parametro
     * @return void
     */
    public function getFornecedor()
    {
        if ($this->request->header('Authorization') != null) {
            $token = $this->request->header('Authorization')->getValue();
            if ($this->validateToken($token) == true) {
                $token      = $this->request->header('Authorization')->getValue();
                $inform     = get_object_vars($this->validateToken($token));
                $dados      = get_object_vars($inform['data']);
                $usuario    = $dados['id'];
                log_message('info', 'Usuário: ' . $usuario . ' Função: getFornecedor');

                $fornec = $this->fornecedor->getFornecedor();

                // echo $this->api->getLastQuery();
                $forns      = [];
                for ($d = 0; $d < sizeof($fornec); $d++) {
                    $chave = $fornec[$d];
                    $forns[$d]['for_id']        = $chave['for_id'];
                    $forns[$d]['for_nome']      = $chave['for_razao'];
                }
                log_message('info', 'Resultado: ' . json_encode($forns) . ' Função: getFornecedor');
                return $this->respond($forns, 200);
            } else {
                return $this->respond(['message' => 'Token Inválido'], 401);
            }
        } else {
            return $this->respond(['message' => 'Não Autorizado'], 401);
        }
    }

    /**
     * gravaEntrada
     * Retorna os dados do produto e da marca pelo cõdigo de barras informado
     * @return void
     */
    // public function gravaentrada()
    // {
    //     if ($this->request->header('Authorization') != null) {
    //         $token = $this->request->header('Authorization')->getValue();
    //         if ($this->validateToken($token) == true) {
    //             $token      = $this->request->header('Authorization')->getValue();
    //             $inform     = get_object_vars($this->validateToken($token));
    //             $dados      = get_object_vars($inform['data']);
    //             $usuario    = $dados['id'];
    //             $user = $this->usuario->getUsuarioId($usuario);
    //             session()->set('usu_nome', $user[0]['usu_nome']);
    //             log_message('info', 'Usuário: ' . $usuario . ' Função: gravaEntrada');

    //             $empresa        = $this->request->getVar('empresa');
    //             $deposito       = $this->request->getVar('deposito');
    //             $codbar         = $this->request->getVar('codbar');
    //             $produto        = $this->request->getVar('produto');
    //             $quantia        = $this->request->getVar('quantia');
    //             $preco          = $this->request->getVar('preco');
    //             $total          = $this->request->getVar('total');
    //             $unidade        = $this->request->getVar('unidade');
    //             $convers        = $this->request->getVar('convers');
    //             $fornece        = $this->request->getVar('fornecedor');
    //             $compra         = $this->request->getVar('compra');
    //             $total          = moedaToFloat($total);

    //             $dados_ent = [
    //                 'ent_data'  => date('Y-m-d'),
    //                 'emp_id'    => $empresa,
    //                 'for_id'    => $fornece,
    //                 'dep_id'    => $deposito,
    //                 'com_id'    => $compra,
    //                 'ent_valor'    => $total,
    //             ];
    //             log_message('info', 'Entrada: ' . json_encode($dados_ent) . ' Função: gravaentrada');
    //             if ($this->entrada->save($dados_ent)) {
    //                 $ent_id = $this->entrada->getInsertID();
    //                 $data_atu = date('Y-m-d H:i:s');
    //                 $valor = moedaToFloat($preco);
    //                 // debug($valor);
    //                 // debug($total,true);
    //                 $dados_pro = [
    //                     'ent_id'        => $ent_id,
    //                     'mar_codigo'    => $codbar,
    //                     'pro_id'        => $produto,
    //                     'und_id'        => $unidade,
    //                     'enp_quantia'   => $quantia,
    //                     'enp_valor'     => $valor,
    //                     'enp_conversao'   => $convers,
    //                     'enp_total'   => $total,
    //                     'enp_atualizado' => $data_atu
    //                 ];
    //                 log_message('info', 'Produto: ' . json_encode($dados_pro) . ' Função: gravaentrada');
    //                 $salva = $this->common->insertReg('dbEstoque', 'est_entrada_produto', $dados_pro);
    //                 if ($salva) {
    //                     log_message('info', 'Resultado: Gravou Produto Função: gravaentrada');
    //                     if ($compra != null) {
    //                         $dados_com = [
    //                             'com_id'    => $compra,
    //                             'com_status' => 'R',
    //                         ];
    //                         $this->compra->save($dados_com);
    //                     }
    //                     $ret = [];
    //                     return $this->respond($ret, 200);
    //                 } else {
    //                     return $this->respond(['message' => 'Erro ao Gravar Produto'], 401);
    //                 }
    //             } else {
    //                 return $this->respond(['message' => 'Erro ao Gravar Entrada'], 401);
    //             }
    //         } else {
    //             return $this->respond(['message' => 'Token Inválido'], 401);
    //         }
    //     } else {
    //         return $this->respond(['message' => 'Não Autorizado'], 401);
    //     }
    // }
    public function gravaentrada()
    {
        if ($this->request->header('Authorization') != null) {
            $token = $this->request->header('Authorization')->getValue();
            if ($this->validateToken($token) == true) {
                $inform  = get_object_vars($this->validateToken($token));
                $dados   = get_object_vars($inform['data']);
                $usuario = $dados['id'];

                $user = $this->usuario->getUsuarioId($usuario);
                session()->set('usu_nome', $user[0]['usu_nome']);
                log_message('info', 'Usuário: ' . $usuario . ' Função: gravaentrada');

                $key = $this->request->getHeaderLine('Idempotency-Key');

                if (!$key) {
                    return $this->fail('Idempotency key required', 400);
                }

                $cache = cache();

                if ($cache->get($key)) {
                    return $this->respond(['message' => 'Duplicate request ignored'], 200);
                }

                $empresa    = $this->request->getVar('empresa');
                $deposito   = $this->request->getVar('deposito');
                $codbar     = $this->request->getVar('codbar');
                $produto    = $this->request->getVar('produto');
                $quantia    = $this->request->getVar('quantia');
                $preco      = $this->request->getVar('preco');
                $total      = $this->request->getVar('total');
                $unidade    = $this->request->getVar('unidade');
                $convers    = $this->request->getVar('convers');
                $fornece    = $this->request->getVar('fornecedor');
                $compra     = $this->request->getVar('compra');

                $total      = moedaToFloat($total);
                $valor      = moedaToFloat($preco);

                $db = Database::connect(); // Instancia o DB
                $db->transStart(); // <<< INICIA TRANSACAO

                $dados_ent = [
                    'ent_data'   => date('Y-m-d'),
                    'emp_id'     => $empresa,
                    'for_id'     => $fornece,
                    'dep_id'     => $deposito,
                    'com_id'     => $compra,
                    'ent_valor'  => $total,
                ];
                log_message('info', 'Entrada: ' . json_encode($dados_ent) . ' Função: gravaentrada');

                if (!$this->entrada->save($dados_ent)) {
                    $db->transRollback();
                    return $this->respond(['success' => false, 'message' => 'Erro ao gravar entrada'], 500);
                }

                $ent_id = $this->entrada->getInsertID();
                $data_atu = date('Y-m-d H:i:s');

                $quantia = formataQuantia($quantia)['qtiv'];
                $convers = formataQuantia($convers)['qtiv'];

                $dados_pro = [
                    'ent_id'         => $ent_id,
                    'mar_codigo'     => $codbar,
                    'pro_id'         => $produto,
                    'und_id'         => $unidade,
                    'enp_quantia'    => $quantia,
                    'enp_valor'      => $valor,
                    'enp_conversao'  => $convers,
                    'enp_total'      => $total,
                    'enp_atualizado' => $data_atu
                ];
                log_message('info', 'Produto: ' . json_encode($dados_pro) . ' Função: gravaentrada');

                $salvaProduto = $this->common->insertReg('dbEstoque', 'est_entrada_produto', $dados_pro);

                if (!$salvaProduto) {
                    $db->transRollback();
                    return $this->respond(['success' => false, 'message' => 'Erro ao gravar produto'], 500);
                }

                if ($compra != null) {
                    $completo = $this->compra->getCompraVsEntrada($compra)[0];
                    if($completo['entrada_completa'] == 1){
                        $dados_com = [
                            'com_id'    => $compra,
                            'com_status'    => 'R',
                        ];
                        $this->compra->save($dados_com);
                    }
                }

                // verifica se o código de barras existe
                $existecodbar = $this->marca->getMarcaCod($codbar);
                if(count($existecodbar) == 0){ // codbar não existe, insere a marca
                    $dados_mar = [
                        'pro_id'         => $produto,
                        'mar_codigo'         => $codbar,
                        'mar_nome'         => 'Marca não cadastrada',
                        'und_id'         => '',
                        'mar_apresenta'         => '',
                        'mar_conversao'         => $convers,
                    ];
                    // debug($dados_mar,true);
                    $salvar = $this->marca->save($dados_mar);
                }

                $db->transComplete(); // <<< FINALIZA TRANSACAO

                if ($db->transStatus() === false) {
                    return $this->respond(['success' => false, 'message' => 'Erro na transação'], 500);
                }

                $cache->save($key, true, 300);
                
                log_message('info', 'Entrada gravada com sucesso Função: gravaentrada');
                return $this->respond(['success' => true, 'id_entrada' => $ent_id], 200);
            } else {
                return $this->respond(['success' => false, 'message' => 'Token inválido'], 401);
            }
        } else {
            return $this->respond(['success' => false, 'message' => 'Não autorizado'], 401);
        }
    }

    /**
     * getConsumo
     * @return void
     */
    public function getConsumo()
    {
        if ($this->request->header('Authorization') != null) {
            $token = $this->request->header('Authorization')->getValue();
            if ($this->validateToken($token) == true) {
                $token      = $this->request->header('Authorization')->getValue();
                $inform     = get_object_vars($this->validateToken($token));
                $dados      = get_object_vars($inform['data']);
                $usuario    = $dados['id'];
                log_message('info', 'Usuário: ' . $usuario . ' Função: getConsumo');

                $empresa       = $this->request->getVar('empresa');
                $deposito       = $this->request->getVar('deposito');

                $produtos =  $this->produto->getProduto();

                // echo $this->api->getLastQuery();
                $prods      = [];
                for ($d = 0; $d < sizeof($produtos); $d++) {
                    $chave = $produtos[$d];
                    $duracao = 0;
                    $consumo = 0;

                    $cons = $this->consumo->getConsumoProdAnt(false, $chave['pro_id'], $empresa);

                    if ($cons) {
                        $duracao = $cons[0]['con_duracao'];
                        $consumo = $cons[0]['con_consumo'];
                    }

                    $prods[$d]['proid']        = $chave['pro_id'];
                    $prods[$d]['pronome']      = $chave['pro_nome'];
                    $prods[$d]['undsigla']     = $chave['und_completa'];
                    $prods[$d]['consumo']         = (string) formataQuantia($consumo)['qtis'];
                    $prods[$d]['duracao']         = (string) formataQuantia($duracao)['qtis'];
                }
                log_message('info', 'Resultado: ' . json_encode($prods) . ' Função: getConsumo');
                return $this->respond($prods, 200);
            } else {
                return $this->respond(['message' => 'Token Inválido'], 401);
            }
        } else {
            return $this->respond(['message' => 'Não Autorizado'], 401);
        }
    }

    /**
     * gravaSaida
     * Retorna os dados do produto e da marca pelo cõdigo de barras informado
     * @return void
     */
    public function gravaconsumo()
    {
        if ($this->request->header('Authorization') != null) {
            $token = $this->request->header('Authorization')->getValue();
            if ($this->validateToken($token) == true) {
                $token      = $this->request->header('Authorization')->getValue();
                $inform     = get_object_vars($this->validateToken($token));
                $dados      = get_object_vars($inform['data']);
                $usuario    = $dados['id'];
                $user = $this->usuario->getUsuarioId($usuario);
                session()->set('usu_nome', $user[0]['usu_nome']);
                log_message('info', 'Usuário: ' . $usuario . ' Função: gravaconsumo');

                $empresa       = $this->request->getVar('empresa');
                $produto        = $this->request->getVar('produto');
                $consumo        = $this->request->getVar('consumo');
                $duracao        = $this->request->getVar('duracao');

                $dados_con = [
                    'emp_id'    => $empresa,
                    'pro_id'    => $produto,
                    'con_consumo'  => $consumo,
                    'con_duracao'  => $duracao,
                ];
                // Verifica se o registro existe
                $existingRecord = $this->consumo->where('emp_id', $empresa)
                    ->where('pro_id', $produto)
                    ->first();

                if ($existingRecord) {
                    // Se o registro já existe, faz o update
                    $dados_con['con_id'] = $existingRecord['con_id']; // Setando o ID do registro existente para o update
                }

                // Agora, seja insert ou update, o save vai funcionar

                log_message('info', 'Consumo: ' . json_encode($dados_con) . ' Função: gravaconsumo');
                if ($this->consumo->save($dados_con)) {
                    return $this->respond(['message' => 'Gravou Consumo'], 200);
                } else {
                    return $this->respond(['message' => 'Erro ao Gravar Consumo'], 401);
                }
            } else {
                return $this->respond(['message' => 'Token Inválido'], 401);
            }
        } else {
            return $this->respond(['message' => 'Não Autorizado'], 401);
        }
    }

    /**
     * getSolicitacao
     * @return void
     */
    public function getSolicitacao()
    {
        if ($this->request->header('Authorization') != null) {
            $token = $this->request->header('Authorization')->getValue();
            if ($this->validateToken($token) == true) {
                $token      = $this->request->header('Authorization')->getValue();
                $inform     = get_object_vars($this->validateToken($token));
                $dados      = get_object_vars($inform['data']);
                $usuario    = $dados['id'];
                log_message('info', 'Usuário: ' . $usuario . ' Função: getSolicitacao');

                $empresa       = $this->request->getVar('empresa');
                $deposito       = $this->request->getVar('deposito');

                $produtos =  $this->produto->getProdutoPedido(false, $empresa);

                log_message('info', 'Resultado: ' . json_encode($produtos) . ' Função: getSolicitacao');

                // echo $this->api->getLastQuery();
                $prods      = [];
                $dados_pedid = [];
                for ($d = 0; $d < sizeof($produtos); $d++) {
                    $prod = $produtos[$d];
                    $saldo = $prod['saldo'];
                    $consumo = $prod['con_consumo'];
                    $duracao = $prod['con_duracao'];
                    $tempore = $prod['con_tporeposicao'];
                    $fcc     = ($prod['pro_fcc'] > 0) ? $prod['pro_fcc'] : 1;
                    $saldo   = $prod['saldo'];
                    $saldo   = $saldo / $fcc;
                    $sugestao = 0;
                    $indice = 0;
                    if ($consumo > 0 || $duracao > 0) {
                        if ($consumo > 0) {
                            $indice = $consumo / 7;
                        } else {
                            $consumo = 0;
                        }
                        if ($duracao > 0) {
                            $indduracao = 1 / $duracao;
                            if ($indduracao > $consumo) {
                                $indice = $indduracao;
                            }
                        }
                        $indice = $indice / $fcc;
                        $sugestao = ($indice * $tempore) - $saldo;
                    }
                    if ($sugestao < 0) {
                        $sugestao = 0;
                    }
                    $sugestao = ceil($sugestao);
                    // debug($prod);
                    $dados_pedid[$d]['sugestao']    = formataQuantia(intval($sugestao), 3)['qtiv'];

                    $dados_pedid[$d]['proid']      = $prod['pro_id'];
                    $dados_pedid[$d]['grunome']    = $prod['gru_nome'];
                    $dados_pedid[$d]['pronome']    = $prod['pro_nome'];
                    $dados_pedid[$d]['saldo']       = formataQuantia(intval($saldo), 3)['qtiv'];
                    $dados_pedid[$d]['undsigla']   = $prod['und_sigla_compra'];
                    $dados_pedid[$d]['peddata']    = "<div id='ped_data[$d]'>" . dataDbToBr($prod['ped_data']) . "</div>";
                    $dados_pedid[$d]['pedqtia']    = $prod['ped_qtia'];
                    $dados_pedid[$d]['undid']      = $prod['und_id_compra'];
                    $dados_pedid[$d]['pedjustifica']      = $prod['ped_justifica'];
                    $dados_pedid[$d]['grucontrolaestoque']      = $prod['gru_controlaestoque'];
                }
                log_message('info', 'Resultado: ' . json_encode($dados_pedid) . ' Função: getSolicitacao');
                return $this->respond($dados_pedid, 200);
            } else {
                return $this->respond(['message' => 'Token Inválido'], 401);
            }
        } else {
            return $this->respond(['message' => 'Não Autorizado'], 401);
        }
    }

    /**
     * gravasolicitacao
     * Retorna os dados do produto e da marca pelo cõdigo de barras informado
     * @return void
     */
    public function gravasolicitacao()
    {
        if ($this->request->header('Authorization') != null) {
            $token = $this->request->header('Authorization')->getValue();
            if ($this->validateToken($token) == true) {
                $token      = $this->request->header('Authorization')->getValue();
                $inform     = get_object_vars($this->validateToken($token));
                $dados      = get_object_vars($inform['data']);
                $usuario    = $dados['id'];
                $user = $this->usuario->getUsuarioId($usuario);
                session()->set('usu_nome', $user[0]['usu_nome']);
                log_message('info', 'Usuário: ' . $usuario . ' Função: gravasolicitacao');

                $empresa        = $this->request->getVar('empresa');
                $produto        = $this->request->getVar('produto');
                $undid          = $this->request->getVar('undid');
                $sugestao       = $this->request->getVar('sugestao');
                $quantia        = $this->request->getVar('quantia');
                $justi          = $this->request->getVar('justificativa');
                $data           = new  \DateTime();

                $dados_ped = [
                    'ped_data'  => $data->format('Y-m-d'),
                    'pro_id'    => $produto,
                    'emp_id'    => $empresa,
                    'ped_qtia'  => $quantia,
                    'und_id'    => $undid,
                    'ped_justifica'    => $justi,
                    'ped_sugestao'    => $sugestao,
                ];
                // Verifica se o registro existe
                $existingRecord = $this->pedido->where('emp_id', $empresa)
                    ->where('pro_id', $produto)->where('ped_data', $data->format('Y-m-d'))
                    ->first();

                if ($existingRecord) {
                    // Se o registro já existe, faz o update
                    $dados_ped['ped_id'] = $existingRecord['ped_id']; // Setando o ID do registro existente para o update
                }

                // Agora, seja insert ou update, o save vai funcionar

                log_message('info', 'Solicitação: ' . json_encode($dados_ped) . ' Função: gravasolicitacao');
                if ($this->pedido->save($dados_ped)) {
                    return $this->respond(['message' => 'Gravou Solicitação'], 200);
                } else {
                    return $this->respond(['message' => 'Erro ao Gravar Solicitação'], 401);
                }
            } else {
                return $this->respond(['message' => 'Token Inválido'], 401);
            }
        } else {
            return $this->respond(['message' => 'Não Autorizado'], 401);
        }
    }
}
