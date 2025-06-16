<?php

namespace App\Libraries;

use SoapClient;

class SoapSapiens
{
    public $soapc;

    public function __construct($servico)
    {
        $serv = 'http://hc170915cqn3007.cloudhialinx.com.br:12030/g5-senior-services/sapiens_Synccom_' . $servico . '?wsdl';
        // debug($serv);
        $this->soapc = new SoapClient($serv);
        // debug($this->soapc->__getFunctions());
    }

    public function clientesSapiens()
    {
        #Instanciando o SoapClient com o WSDL o qual vamos acessar
        $client = new SoapClient('http://hc170915cqn3007.cloudhialinx.com.br:12030/g5-senior-services/sapiens_Synccom_senior_g5_co_ger_cad_clientes?wsdl');
        #Operação a ser executada
        $function = 'obterCliente';
        #Montando o payload de requisição
        $parameters = array(
                        'user'            => 'Smart2',
                        'password'        => 'omyjano1',
                        'encryption'      => 0,
                        'parameters'      => array(
                                'codigoEmpresa'   => 1,
                                'codigoFilial'    => 1,
                                'codigoCliente'   => 8
                            )
                    );
        #Sobrescrevendo endpoint do serviço
        $arguments = array('obterCliente' => array( $parameters));

        $options = array('location' => 'http://services.senior.com.br');

        #Chamada do serviço
        $result = $client->__soapCall($function, $parameters);

        echo 'Response: ';
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }

    public function depositosSapiens($funcao)
    {
        #Operação a ser executada
        $function = $funcao;
        #Montando o payload de requisição
        $parameters = array(
                        'user'            => 'IntCeqweb',
                        'password'        => 'soPR#JOV@omVs',
                        'encryption'      => 0,
                        'parameters'      => array(
                                'codEmp'   => 1,
                                'codFil'    => 1,
                            )
                    );
        #Sobrescrevendo endpoint do serviço

        #Chamada do serviço
        $result = $this->soapc->__soapCall($function, $parameters);
        // echo 'Response: ';
        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";
        $this->soapc = null;
        return $result;
    }

    public function estoquePorDeposito($deposito)
    {
        #Operação a ser executada
        $function = 'EstoqueporDeposito';
        #Montando o payload de requisição
        $parameters = array(
                        'user'            => 'IntCeqweb',
                        'password'        => 'soPR#JOV@omVs',
                        'encryption'      => 0,
                        'parameters'      => array(
                                'codEmp'   => 1,
                                'codFil'    => 1,
                                'codDep'    => $deposito,
                            )
                    );
        #Chamada do serviço
        $result = $this->soapc->__soapCall($function, $parameters);
        $this->soapc = null;

        return $result;
    }

    public function transacoesEstoque($funcao)
    {
        #Operação a ser executada
        $function = $funcao;
        #Montando o payload de requisição
        $parameters = array(
                        'user'            => 'IntCeqweb',
                        'password'        => 'soPR#JOV@omVs',
                        'encryption'      => 0,
                        'parameters'      => array(
                                'codEmp'   => 1,
                            )
                    );
        #Sobrescrevendo endpoint do serviço

        #Chamada do serviço
        $result = $this->soapc->__soapCall($function, $parameters);
        // echo 'Response: ';
        // echo "<pre>";
        // print_r($result);
        // echo "</pre>";
        $this->soapc = null;
        return $result;
    }

    public function saldoEstoqueSapiensLista()
    {
        #Operação a ser executada
        $function = 'Exportar_3';
        #Montando o payload de requisição
        $parameters = array(
                        'user'            => 'Smart2',
                        'password'        => 'omyjano1',
                        'encryption'      => 0,
                        'parameters'      => array(
                            'codEmp'   => 1,
                            'codFil'    => 1,
                            'identificadorSistema'    => 'CEQWEB3',
                            'quantidadeRegistros' => 1000,
                            'tipoIntegracao'    => 'T'
                        )
                    );
        #Chamada do serviço
        $result = $this->soapc->__soapCall($function, $parameters);

        return $result;
    }
}
