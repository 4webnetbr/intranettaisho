<?php 
namespace App\Controllers\Setup;
use App\Controllers\BaseController;
use App\Libraries\Campos;
use App\Models\Setup\SetupDicDadosModel;

class SetFuncoes extends BaseController
{
    public $data = [];
    public $dicionario;

    public function __construct()
    {
        $this->data      = session()->getFlashdata('dados_classe');
        $this->permissao = $this->data['permissao'];
        if ($this->data['erromsg'] != '') {
            $this->__erro();
        }
    }

    function __erro() 
    {
        echo view('vw_semacesso', $this->data);
    }

    public function index()
    {
        $this->data['colunas'] = ['Arquivo', 'Arquivo', 'Função', 'Descrição',  'Ações'];
        $this->data['url_lista'] = base_url(
            $this->data['controler'] . '/lista'
        );

        echo view('vw_lista', $this->data);
    }

    public function lista()
    {
        $result = [];
        $pasta = FCPATH.'/assets/jscript';
        $diretorio = dir($pasta);
        while(($arquivo = $diretorio->read()) !== false) {
            $arquivos[] = $arquivo;
        }
         
        asort($arquivos);
        foreach ($arquivos as $arquivo) {
            if(substr($arquivo, 0,3) == 'my_'){
                $conteudo = file_get_contents($pasta.'/'.$arquivo);
                $coments = explode('/**',$conteudo);
                for($c=0;$c<count($coments);$c++){
                    $fim = stripos($coments[$c], '*/');
                    $funcao = substr($coments[$c],0,$fim);
                    $lin_func = explode('*',$funcao);
                    if(isset($lin_func[1])){
                        $nome = $lin_func[1];
                        if(isset($lin_func[2])){
                            $descricao = $lin_func[2];
                        } else {
                            $nome = '';
                            $descricao = $lin_func[1];
                        }
                        $detalhes = '';
                        if (strpbrk($this->permissao, 'C')) {
                            $detalhes = anchor(
                                $this->data['controler'] . "/show/" .$arquivo.'/'.$c,
                                '<i class="far fa-eye"></i>',
                                [
                                    'class' => 'btn btn-outline-info btn-sm mx-1',
                                    'data-mdb-toggle' => 'tooltip',
                                    'data-mdb-placement' => 'top',
                                    'title' => 'Código da Função', 
                                ]
                            );
                        }
                        $arquiv = anchor(
                            $this->data['controler'] . "/show/" .$arquivo.'/'.$c,$arquivo);
                        $result[] = [
                            $arquivo,
                            $arquiv,
                            $nome,
                            $descricao,
                            $detalhes,
                        ];
                    }
                }
            }
        }
        $pasta = FCPATH.'../app/Helpers';
        $diretorio = dir($pasta);
        while(($arquivo = $diretorio->read()) !== false) {
            // debug($arquivo);
            $arquiphp[] = $arquivo;
        }
         
        asort($arquiphp);
        foreach ($arquiphp as $arquivo) {
            if(substr($arquivo, 0,1) != '.'){
                $conteudo = file_get_contents($pasta.'/'.$arquivo);
                $coments = explode('/**',$conteudo);
                for($c=0;$c<count($coments);$c++){
                    $fim = stripos($coments[$c], '*/');
                    $funcao = substr($coments[$c],0,$fim);
                    $lin_func = explode('*',$funcao);
                    if(isset($lin_func[1])){
                        $nome = $lin_func[1];
                        if(isset($lin_func[2])){
                            $descricao = $lin_func[2];
                        } else {
                            $nome = '';
                            $descricao = $lin_func[1];
                        }
                        $detalhes = anchor(
                            $this->data['controler'] . "/show/" .$arquivo.'/'.$c,
                            '<i class="far fa-eye"></i>',
                            [
                                'class' => 'btn btn-outline-info btn-sm mx-1',
                                'data-mdb-toggle' => 'tooltip',
                                'data-mdb-placement' => 'top',
                                'title' => 'Código da Função', 
                            ]
                        );
                        $arquiv = anchor(
                            $this->data['controler'] . "/show/" .$arquivo.'/'.$c,$arquivo);
                        $result[] = [
                            $arquivo,
                            $arquiv,
                            $nome,
                            $descricao,
                            $detalhes,
                        ];
                    }
                }
            }
        }
        $functions = [
            'data' => $result,
        ];

        echo json_encode($functions);
    }


    public function show($arquivo, $posicao)
    {
        $ext = substr($arquivo, -3);
        if(trim($ext) == '.js'){
            $pasta = FCPATH.'/assets/jscript';
        } else {
            $pasta = FCPATH.'../app/Helpers';
        }
        $conteudo = file_get_contents($pasta.'/'.$arquivo);
        $coments    = explode('/**',$conteudo);
        // debug($posicao, false);
        // debug($coments[$posicao],false);
        $fim        = stripos($coments[$posicao], ';;');
        if(!$fim){
            $fim = strlen($coments[$posicao]);
        }
        // debug($fim, false);
        $funcao     = htmlspecialchars(substr($coments[$posicao],0,$fim));
        $lin_func   = explode('*',$funcao);
        // debug('Função', false);
        // debug($funcao, false);
        // debug($lin_func, false);
        $nome = $lin_func[0];
        if(trim($nome) == ''){
            $nome = $lin_func[1];
        }
        // debug($nome,false);
        $linhaini = 0;
        // debug($nome, false);
        $fp = fopen($pasta.'/'.$arquivo, "r");

        $conta = 0;
        while(!feof($fp)) { // loop em todas as linhas
            $linha = trim(fgets($fp, 4096)); // le 4096bytes ou ate o final da linha
            // debug($linha,false);
            if(stripos($linha, trim($nome))){
                break;
            }
            $conta++;
        }
        $funcao = 'Arquivo: '.$arquivo.'<br>Linha Inicial: '.$conta.'<br><br>/**'.$funcao;
        // debug($funcao);
        $this->def_campos($funcao);

        $secao[0] = 'Código Fonte';
        $campos[0][0] = $this->def_funcao;

        $this->data['secoes'] = $secao;
        $this->data['campos'] = $campos;
        $this->data['destino'] = 'store';

        echo view('vw_edicao', $this->data);
    }

    public function def_campos($funcao = false)
    {
        $func = new Campos();
        $func->objeto = 'show';
        $func->nome = 'codigo';
        $func->id = 'codigo';
        $func->label = 'Código Fonte da Função';
        $func->size = '100%';
        $func->tamanho = 'auto';
        $func->valor = '<pre>'.$funcao.'</pre>';
        // $codi->valor = print_r($fonte);
        $this->def_funcao = $func->create();
    }

}
