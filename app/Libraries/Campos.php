<?php namespace App\Libraries;

use CodeIgniter\Libraries;

/**
 * Campos_cust
 * Criação de Campo Customizado
 * @param string    $objeto     - informa o tipo de campo a ser criado
 * @param boolean   $obrigatorio- verdadeiro se o campo for obrigatorio
 * @param boolean   $leitura    - verdadeiro se o campo for somente leitura
 * @param string    $tipo       - informa o tipo Customizado do campo
 * @param string    $nome       - Nome do Campo
 * @param string    $id         - Id do Campo
 * @param string    $label      - Rótulo do Campo
 * @param string    $place      - placeholder do Campo
 * @param string    $valor      - Valor inicial do Campo
 * @param int       $size       - Quantia de Caracteres do Campo
 * @param int       $max_size   - Quantia de Caracteres máximo do Campo
 * @param int       $tamanho    - Largura do campo
 * @param string    $funcao_chan- Função que será executada na alteracao do campo
 * @param string    $funcao_blur- Função que será executada na saída do campo
 * @param string    $refer      - Campo de Referência
 * @param string    $classe     - Classe personalizada a ser aplicado no campo
 * @param string    $tipo_arquivo - Tipos de Arquivo suportados pelo campo imagem
 * @param string    $hint       - Hint (tooltip) a ser mostrado no campo
 * @param int       $minimo     - Valor mínimo para o campo do tipo number
 * @param int       $maximo     - Valor máximo para o campo do tipo number
 * @param int       $step       - Valor de incremento do campo do tipo number
 * @param array     $opcoes     - Array de elementos da lista
 * @param int       $selecionado - Item selecionado da lista
 * @param array     $selecmulti - Itens selecionados quando o campo é multiselect
 * @param string    $busca      - URL de busca do campo selbusca e do campo dependente
 * @param boolean   $repete     - verdadeiro, se o campo pode ser repetido
 * @param string    $tipo_form  - Formato de disposição dos campos no Formulário (inline, vertical)
 * @param string    $pasta      - Pasta do arquivo de imagem
 * @param string    $img_name   - Nome do arquivo de Imagem pré-carregada
 * @param string    $pai        - nome do campo pai, para um campo dependente
 */
class Campos
{
    public $objeto;         // input, select, textarea, botao, file
    public $obrigatorio     = false;
    public $leitura         = false;
    public $tipo;
    public $nome;
    public $id;
    public $label;
    public $place           = '';
    public $valor           = '';
    public $size            = 30;
    public $max_size        = 30;
    public $tamanho         = 50;
    public $funcao_chan     = '';
    public $funcao_blur     = '';
    public $refer           = '';
    public $classe          = '';
    public $tipo_arquivo    = '';
    public $hint            = '';
    public $minimo          = 0;
    public $maximo          = 100;
    public $step            = 1;
    public $opcoes          = [];
    public $selecionado     = "";
    public $selecmulti      = [];
    public $busca           = '';
    public $repete          = false;
    public $tipo_form       = 'inline';
    public $pasta           = '';
    public $img_name        = '';

    // DEPENDENTE
    public $pai       = '';

    
    public function __construct()
    {
        helper('form');
    }
    
    public function create() : string
    {
        if ($this->objeto == 'botao') {
            $ret = $this->cr_botao();
        } else if ($this->objeto == 'oculto') {
            $ret = $this->cr_oculto();
        } else {
            $ret = "<div id='ig_$this->id' class='row g-1 align-items-center'>";
            if ($this->objeto == 'show') {
                $ret .= $this->cr_show();
            }
            if ($this->objeto == 'input') {
                $ret .= $this->cr_input();
            }

            if ($this->objeto == 'daterange') {
                $ret .= $this->cr_daterange();
            }
            
            if ($this->objeto == 'texto') {
                $ret .= $this->cr_texto();
            }

            if ($this->objeto == 'textoarea') {
                $ret .= $this->cr_textoarea();
            }

            if ($this->objeto == 'checkbox') {
                $ret .= $this->cr_checkbox();
            }

            if ($this->objeto == 'radio') {
                $ret .= $this->cr_radio();
            }

            if ($this->objeto == 'dual') {
                $ret .= $this->cr_dual();
            }

            if ($this->objeto == 'multiple') {
                $ret .= $this->cr_multiple();
            }

            if ($this->objeto == 'select') {
                $ret .= $this->cr_select();
            }
            if ($this->objeto == 'selbusca') {
                $ret .= $this->cr_selbusca();
            }
            if ($this->objeto == 'depende') {
                $ret .= $this->cr_depende();
            }
            if ($this->objeto == 'imagem') {
                $ret .= $this->cr_imagem();
            }

            if ($this->objeto == 'text_show') {
                $ret .= $this->cr_text_show();
            }

            if ($this->tipo == 'AUTOCOMPLETE') {
                $this->hint = " data-toggle='tooltip' data-original-title='".$this->CI->lang->line('ms_3caracteres')."'";
            }

            $ret .= "</div>";
        }
        return $ret;
    }

    public function cr_label($ident = '')
    {
        if($ident == ''){
            $ident = $this->id;
        }
        $label = array(
            'class'         => 'form-label p-0 m-0',
        );
        $label_text = $this->label;
        if (($this->tipo == 'file' || $this->tipo == 'imagem') && $this->oculto === false) {
            $label['class'] = "btn btn-primary";
            $label['style'] = "white-space: normal;width:$this->sizepx;padding:0.8em;";
            $label_text .= "<i class='fa fa-file-archive-o'></i> Selecionar Arquivo";
        }

        if ($this->tipo_form == 'vertical') {
            $ret = "<div class='col-12 col-lg-12 d-block'>";
        } else {
            $ret = "<div class='col-12 col-lg-2 d-block'>";
        }
    
        if ($this->repete) {
            $ret = "<div class='p-0 me-3 mb-0 me-lg-0 d-block'>";
        }
        $ret .= form_label($label_text, $ident, $label);
        $ret .= "</div>";
        return $ret;
    }

    public function cr_show(): string
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->cr_label();
        }
        $altura  = $this->tamanho.'rem';
        $largura = $this->size.'ch';

        $resp .= "<div class='border rounded bg-gradient-secondary input-group mb-lg-1 mb-2 overflow-auto' style='width: $largura !important; height: $altura !important'>";
        $resp .= $this->valor;
        $resp .= "</div>";

        return $resp;
    }

    public function cr_botao(): string
    {
        $field = array(
            'name'          => $this->nome,
            'id'            => $this->id,
            'type'          => $this->tipo,
            'class'			=> "btn $this->classe ",
            'content'       => $this->label,
            'onclick'  		=> $this->funcao_chan
        );
        if ($this->hint != '') {
            $field['data-mdb-toggle'] = "tooltip";
            $field['data-mdb-placement'] = "bottom";
            $field['title'] = $this->hint;
        }
        $resp = form_button($field);
        return $resp;
    }

    public function cr_oculto(): string
    {
        $field = array(
            'type'  		=> 'hidden',
            'name'  		=> $this->nome,
            'id'    		=> $this->nome,
            'value' 		=> $this->valor,
        );

        $resp = form_input($field);
        return $resp;
    }

    public function cr_checkbox()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->cr_label();
        }
        $largura = $this->tamanho.'ch';
        $resp .= "<div class='form-check form-switch' style='width: 10ch'>";
        $field = array(
                'name'  		=> $this->nome,
                'id'    		=> $this->id,
                'value' 		=> $this->valor,
                'data-selec' 	=> $this->selecionado, 
                'data-enabled' 	=> $this->leitura,
                'data-alter' 	=> false,
                'data-label' 	=> $this->label,
                'label' 		=> $this->label,
                'hint'  		=> $this->hint,
                'onchange' 		=> $this->funcao_chan,
                'class' 		=> "form-check-input ml-2 float-end $this->classe",
                // 'style'         => 'max-width: '.$largura
        );
        if ($this->valor == $this->selecionado) {
            $field['checked'] = true;
        }
        if ($this->obrigatorio === true) {
            $field['required'] = true;
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }

        if ($this->leitura === true) {
            $field['readonly'] = "readonly";
            $field['disabled'] = "disabled";
            $field['onfocus'] = "this.blur()";
            $field['tabindex'] = -1;
        }
        // $resp .= "<label class='form-check-label' for='".$this->id."'>".$this->label.'</label>'.form_checkbox($field);
        $resp .= form_checkbox($field);
        $resp .= "</div>";
        return $resp;
    }

    public function cr_radio()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->cr_label();
        }
        $largura = $this->tamanho.'ch';
        $resp .= "<div class='form-check form-switch form-check-inline' style='width: 50ch'>";
        $cont = 0;
        foreach ( $this->opcoes as $valor => $label ) {
            $id = $this->id.'['.$cont.']';
            $field = array(
                    'name'  		=> $this->nome,
                    'id'    		=> $id,
                    'value' 		=> $valor,
                    'data-enabled' 	=> $this->leitura,
                    'data-selec' 	=> $this->selecionado, 
                    'data-alter' 	=> false,
                    'data-salva' 	=> true,
                    // 'data-label' 	=> $valor,
                    // 'label' 		=> $valor,
                    'hint'  		=> $this->hint,
                    'onchange' 		=> $this->funcao_chan,
                    'class' 		=> "form-check-input ml-2 $this->classe"
            );
            if($valor == $this->selecionado){
                $field['checked'] = true;
            }
            if($this->obrigatorio === true){
                $field['required'] = true;
                // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
            } else {
                $field['required'] = false;
            }
            if($this->leitura === true){
                $field['readonly'] = "readonly";
                $field['onfocus'] = "this.blur()";
                $field['tabindex'] = -1;
            }
            $lab = "<label class='form-check-label px-1' for='$id'> $label </label>";
            $resp .= "<div class='d-inline-flex' style='width: 15ch'>";
            $resp .= form_radio($field).$lab;
            $resp .= '</div>';
            $cont++;
        }
        $resp .= "</div>";
        return $resp;
    }
    
    public function cr_input()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->cr_label();
        }
        $largura = $this->tamanho.'ch';
        if (session()->ismobile) {
            $largura = '';
        }
        $resp .= "<div class='input-group mb-lg-1 mb-2' style='width: $largura !important'>";
        $field = array(
            'type'  		=> $this->tipo,
            'name'  		=> $this->nome,
            'id'    		=> $this->id,
            'value' 		=> $this->valor,
            'autocomplete'  => 'off',
            'size'			=> $this->size,
            'maxlength' 	=> isset($this->max_size)?$this->max_size:$this->size,
            'hint'  		=> $this->hint,
            'onblur' 		=> $this->funcao_blur,
            'class' 		=> "form-control $this->classe",
            'data-inicial' 	=> $this->valor,
            'data-enabled' 	=> $this->leitura,
            'data-alter' 	=> false,
            'data-label' 	=> $this->label,
            'placeholder' 	=> $this->place,
        );
        if ($this->leitura === true) {
            $field['readonly'] = "readonly";
            $field['onfocus'] = "this.blur()";
            $field['tabindex'] = -1;
        }
        if ($this->obrigatorio === true && $this->tipo != 'login' && $this->tipo != 'password') {
            $field['required'] = true;
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }
        if ($this->hint != '') {
            $field['data-mdb-toggle'] = "tooltip";
            $field['data-mdb-placement'] = "top";
            $field['title'] = $this->hint;
        }

        switch ($this->tipo) {
            case 'icone':
                $field['type'] = 'text';
                $field['class'] = "form-control $this->classe icone";
                $field['aria-describedby'] = 'ig_'.$this->nome;
                $resp .= "<span class='input-group-text input-group-addon'><i class='".$this->valor."'></i></span>";
                break;
            case 'quantia':
                $field['type'] = 'text';
                $field['onkeyup'] = 'mascara(this, \'mquantia\')';
                $field['onblur'] = $this->funcao_blur;
                $field['pattern'] = '[0-9]$';
                $field['style'] = 'text-align: right';
                $field['aria-describedby'] = 'ig_'.$this->nome;
                break;
            case 'sonumero':
                $field['type']              = 'text';
                $field['onkeyup']           = 'mascara(this, \'mnum\')';
                $field['onchange']          = 'mascara(this, \'mnum\')';
                $field['pattern']           = '\\d{1,'.$this->size.'}';
                $field['style'] 			= 'text-align: right';
                $field['aria-describedby']  = 'ig_'.$this->nome;
                break;
            case 'password':
                $field['onchange']           = $this->funcao_chan;
                $field['aria-describedby'] = 'ad_'.$this->nome;
                $resp .= "<span class='input-group-text input-group-addon' id='ad_$this->nome'><i class='bi bi-key'></i></span>";
                break;
            case 'email':
                $field['type'] = 'email';
                $field['pattern'] = '^[\w\.=-]+@[\w\.-]+\.[\w]{2,3}$';
                $field['style'] = 'text-align: left';
                $field['aria-describedby'] = 'ad_'.$this->nome;
                $field['data-original-title'] = 'Informe um E-mail válido!';
                $field['title'] = 'Informe um E-mail válido!';
                $resp .= "<span class='input-group-text' id='ad_$this->nome'><i class='far fa-envelope-open' ></i></span>";
                break;
            case 'site':
            case 'url':
                $field['type'] = 'url';
                $field['pattern'] = '^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$';
                $field['style'] = 'text-align: left';
                $field['aria-describedby'] = 'ad_'.$this->nome;
                $field['data-original-title'] = 'Informe uma url válida!';
                $field['title'] = 'Informe uma url válida!';
                $resp .= "<span class='input-group-text' id='ad_$this->nome'><i class='far fa-link'></i></span>";
                break;
            case 'telefone':
            case 'fone':
                $field['type'] = 'tel';
                $field['pattern'] = '^\(\d{2}\) \d{4}\-\d{4}$';
                $field['onkeyup'] = 'mascara(this, \'mtel\')';
                $field['style'] = 'text-align: left';
                $field['aria-describedby'] = 'ad_'.$this->nome;
                $field['data-original-title'] = 'Informe um Telefone válido! (99) 9999-9999';
                $field['title'] = 'Informe um Telefone válido! (99) 9999-9999';
                $resp .= "<span class='input-group-text' id='ad_$this->nome'></span><i class='fas fa-phone' ></i></span>";
                break;
            case 'celular':
            case 'celul':
            case 'whatsapp':
            case 'whats':
                $field['type'] = 'tel';
                $field['pattern'] = '^\(\d{2}\) \d{4,5}\-\d{4}$';
                $field['onkeyup'] = 'mascara(this, \'mcel2\')';
                $field['style'] = 'text-align: left';
                $field['aria-describedby'] = 'ad_'.$this->nome;
                $field['data-original-title'] = 'Informe um Celular válido! (99) 99999-9999';
                $field['title'] = 'Informe um Celular válido! (99) 99999-9999';
                $resp .= "<span class='input-group-text' id='ad_$this->nome'><i class='fa fa-mobile-alt'></i></span>";
                break;
            case 'cnpj':
                $field['type'] = 'text';
                $field['pattern'] = '^\\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}';
                $field['onkeyup'] = 'mascara(this, \'mcnpj\')';
                $field['style'] = 'text-align: right';
                $field['aria-describedby'] = 'ad_'.$this->nome;
                $field['data-original-title'] = 'Digite o CNPJ no formato 99.999.999/9999-99';
                $field['title'] = 'Digite o CNPJ no formato 99.999.999/9999-99';
                break;
            case 'cpf':
                $field['type'] = 'text';
                $field['pattern'] = '^\\d{3}\.\d{3}\.\d{3}\-\d{2}';
                $field['onkeyup'] = 'mascara(this, \'mcpf\')';
                $field['style'] = 'text-align: right';
                $field['aria-describedby'] = 'ad_'.$this->nome;
                $field['data-original-title'] = 'Digite o CPF no formato 999.999.999-99';
                $field['title'] = 'Digite o CPF no formato 999.999.999-99';
                break;
            case 'cep':
                $field['type'] = 'text';
                $field['pattern'] = '^\\d{5}\-\d{3}$';
                $field['onkeyup'] = 'mascara(this, \'mcep\')';
                $field['style'] = 'text-align: right';
                $field['aria-describedby'] = 'ad_'.$this->nome;
                $field['data-original-title'] = 'Digite o CEP no formato 99999-999';
                $field['title'] = 'Digite o CEP no formato 99999-999';
                break;
            case 'file':
                $field['type'] = 'file';
                $field['data_folder'] = $this->pasta;
                $field['data_img_name'] = $this->img_name;
                $field['class'] = '';
                if ($this->valor != '') {
                    $ico_arq = substr($this->valor, strrpos($this->valor, '.') + 1).".png";
                } else {
                    $ico_arq = '';
                }
                
                $resp .= "<div id='view_img_".$this->nome."' class='show clearfix' style='width:200px; height:200px;' >";
                $resp .= "<img id='img_".$this->nome."' src='".base_url('uploads/tipo_down/').$ico_arq."' for='".$this->id."' class='img-thumbnail col-lg-12 col-xs-12' style='width:200px; height:200px;' alt='' /></div>";
                break;
            case 'imagem':
                $field['type'] = 'file';
                $field['data-folder'] = $this->pasta;
                $field['data-img-name'] = $this->img_name;
                $field['data-file-type'] = $this->tipo_arquivo;
                $field['accept'] = 'image/*';
                $field['capture'] = 'capture';
                $field['class'] = '';
                $resp .= "<div>A imagem será redimensionada para ".$this->size."X".$this->tamanho." proporcionalmente</div>";
                $resp .= "<div id='view_img_".$this->nome."' class='show img-thumbnail sempadding' style='width:".$this->size."px; height:".$this->tamanho."px;' >";
                $resp .= "<img id='img_".$this->nome."' src='".$this->valor."' for='".$this->id."' class='img-thumbnail sempadding' alt=''  />";
                $resp .= "</div><div class='clear'></div>";
                break;
            case 'number':
                $field['type'] = 'number';
                $field['dir'] = 'rtl';
                $field['min'] = $this->minimo;
                $field['max'] = $this->maximo;
                $field['step'] = $this->step;
                $field['onfocus'] = 'entrar_moeda(this)';
                $field['style'] = 'text-align: right';
                break;
            case 'date':
            case 'datetime-local':
                // if ($field['value'] != "") {
                //     $field['max'] = $field['value'];
                // }
                // $resp .= '<i class="fa fa-calendar-o form-control-icon" aria-hidden="true"></i>';
                break;
            case 'moeda':
                $field['type'] = 'text';
                $field['onkeyup'] = 'mascara(this, \'mvalor\')';
                $field['pattern'] = '([0-9]{1,3}\.)?[0-9]{1,3},[0-9]{2}$'; //'\\d{1,3}(?:\.\d{3})*,\d{2}$';
                $field['onchange'] = $this->funcao_chan;
                $field['data-origin'] = $this->valor;
                $field['data-person'] = '0';
                $field['onblur']    = 'sair_moeda(this)';
                $field['onfocus'] = 'entrar_moeda(this)';
                $field['class'] = $field['class'].' moeda';
                $field['style'] = 'text-align: right';
                break;
            case 'textselect': //pega o texto do select informado
                $field['type'] = 'text';
                $busca = "busca_textselect(this,\"".$this->nome."\")";
                $resp .= "<script>";
                $resp .= "jQuery('#".$this->place."').attr('onchange','".$busca."');";
                $resp .= "jQuery('#".$this->place."').trigger('change');";
                $resp .= "</script>";
                break; 
            case 'textinput': //pega o texto do select informado
                $field['type'] = 'hidden';
                $busca = "busca_textselect(this,\"".$this->nome."\")";
                $resp .= "<script>";
                $resp .= "chang_ant = jQuery('#".$this->place."').attr('onchange');";
                $resp .= "jQuery('#".$this->place."').attr('onchange','+chang_ant+'".$busca."');";
                $resp .= "jQuery('#".$this->place."').trigger('blur');";
                $resp .= "</script>";
                break;
            case 'calculo': //pega o texto do select informado
                $field['placeholder'] = '';
                $busca = "calcula(\"".$this->id."\",\"".$this->place."\",\"".$this->refer."\")";
                $resp .= "<script>";
                $resp .= "jQuery('#".$this->valor."').attr('onchange','".$busca."');";
                $resp .= "jQuery('#".$this->valor."').trigger('change');";
                $resp .= "</script>";
                break;
        }
        $resp .= form_input($field);
        if ($this->tipo == 'password') {
            $resp .= "<span name='show_password' class='input-group-text bi bi-eye-slash-fill show_password' id='ada_$this->nome' data-field='$this->nome'></span>";
        }

        $resp .= "</div>";
        
        // if($this->tipo == 'icone'){
        //     $resp .= "</div>";
        // }
        return $resp;
    }

    public function cr_daterange()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->cr_label();
        }
        $largura = $this->tamanho.'ch';
        if (session()->ismobile) {
            $largura = '';
        }
        $resp .= "<div class='input-group mb-lg-1 mb-2' style='width: $largura !important'>";
        $field = array(
            'type'  		=> 'text',
            'name'  		=> $this->nome,
            'id'    		=> $this->id,
            'value' 		=> $this->valor,
            'data-alter' 	=> false,
            'data-label' 	=> $this->label,
            'size'			=> $this->size,
            'maxlength' 	=> isset($this->max_size)?$this->max_size:$this->size,
            'class' 		=> "daterange form-control $this->classe"
        );
        if ($this->obrigatorio === true) {
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }
        $resp .= form_input($field);
        $resp .= "</div>";
        
        return $resp;
    }

    public function cr_texto()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->cr_label();
        }
        $largura = $this->tamanho.'ch';
        if (session()->ismobile) {
            $largura = '';
        }
        $resp .= "<div class='input-group mb-lg-1 mb-2' style='width: $largura !important'>";
        $field = array(
                'type'  		=> 'textarea',
                'name'  		=> $this->nome,
                'id'    		=> $this->id,
                'value' 		=> $this->valor,
                'data-enabled' 	=> $this->leitura,
                'data-alter' 	=> false,
                'data-label' 	=> $this->label,
                'placeholder' 	=> $this->place,
                'cols'			=> $this->size,
                'rows' 			=> $this->max_size,
                'hint'  		=> $this->hint,
                'onblur' 		=> $this->funcao_blur,
                'style'         => 'white-space: normal;width: auto',
                'class' 		=> "$this->classe form-control",
        );
        if ($this->leitura === true) {
            $field['readonly'] = "readonly";
            $field['onfocus'] = "this.blur()";
            $field['tabindex'] = -1;
        }
        if ($this->obrigatorio === true) {
            $field['required'] = true;
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }
        if ($this->hint != '') {
            $field['data-mdb-toggle'] = "tooltip";
            $field['data-mdb-placement'] = "bottom";
            $field['title'] = $this->hint;
        }

        $resp .= form_textarea($field);
        $resp .= "</div>";
        
        return $resp;
    }
    public function cr_textoarea()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->cr_label();
        }
        $largura = $this->tamanho.'ch';
        if (session()->ismobile) {
            $largura = '';
        }
        $resp .= "<div class='input-group mb-lg-1 mb-2' style='width: $largura !important'>";
        $field = array(
                'type'  		=> 'textarea',
                'name'  		=> $this->nome,
                'id'    		=> $this->id,
                'value' 		=> $this->valor,
                'data-enabled' 	=> $this->leitura,
                'data-alter' 	=> false,
                'data-label' 	=> $this->label,
                'placeholder' 	=> $this->place,
                'cols'			=> $this->size,
                'rows' 			=> $this->tamanho,
                'hint'  		=> $this->hint,
                'onblur' 		=> $this->funcao_blur,
                'style'         => 'white-space: normal;width: auto',
                'class' 		=> 'form-control',
        );
        if ($this->leitura === true) {
            $field['readonly'] = "readonly";
            $field['onfocus'] = "this.blur()";
            $field['tabindex'] = -1;
        }
        if ($this->obrigatorio === true) {
            $field['required'] = true;
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }
        if ($this->hint != '') {
            $field['data-mdb-toggle'] = "tooltip";
            $field['data-mdb-placement'] = "bottom";
            $field['title'] = $this->hint;
        }

        $resp .= form_textarea($field);
        $resp .= "</div>";
        
        return $resp;
    }

    public function cr_dual()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->cr_label();
        }
        $largura = $this->tamanho.'ch';
        if (session()->ismobile) {
            $largura = '';
        }
        $resp .= "<div class='input-group mb-lg-1 mb-2' style='width: $largura !important'>";

        $field = array(
                'name'  		=> $this->nome,
                'id'    		=> $this->id,
                'data-enabled' 	=> $this->leitura,
                'data-alter' 	=> false,
                'data-label' 	=> $this->label,
                'data-valor'	=> $this->selecionado,
                'data-size'	    => $this->size,
                'placeholder' 	=> $this->place,
                'hint'  		=> $this->hint,
                'multiple'  	=> "multiple",
                'onchange' 		=> $this->funcao_chan,
                'class' 		=> ' form-control form-dual'
        );
        if (!isset($this->size) || $this->size == '') {
            $this->size = -1;
        }
        if ($this->place != '') {
            $this->opcoes = array('-1 disabled'  => 'Escolha '.$this->place)+$this->opcoes;
        }

        if ($this->leitura === true) {
            $field['readonly'] = "readonly";
            $field['onfocus'] = "this.blur()";
            $field['tabindex'] = -1;
        }
        if ($this->obrigatorio === true) {
            $field['required'] = true;
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }
        if ($this->hint != '') {
            $field['data-mdb-toggle'] = "tooltip";
            $field['data-mdb-placement'] = "bottom";
            $field['title'] = $this->hint;
        }
      
        $resp .= form_dropdown($field, $this->opcoes, $this->selecionado);

        $resp .= "</div>";

        return $resp;
    }

    public function cr_multiple()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->cr_label();
        }
        $largura = $this->tamanho.'ch';
        if (session()->ismobile) {
            $largura = '';
        }
        $resp .= "<div class='input-group mb-lg-1 mb-2' style='width: $largura !important'>";

        $field = array(
                'name'  		=> $this->nome,
                'id'    		=> $this->id,
                'data-enabled' 	=> $this->leitura,
                'data-alter' 	=> false,
                'data-label' 	=> $this->label,
                'data-valor'	=> $this->selecionado,
                'placeholder' 	=> $this->place,
                'hint'  		=> $this->hint,
                'multiple'      => '',
                'data-live-search' => "true",
                'class' 		=> 'selpic form-control form-select show-tick'
        );
        if (!isset($this->size) || $this->size == '') {
            $this->size = -1;
        }
        if ($this->place != '') {
            $this->opcoes = array('""'  => 'Escolha '.$this->place)+$this->opcoes;
        }

        if ($this->leitura === true) {
            $field['readonly'] = "readonly";
            $field['onfocus'] = "this.blur()";
            $field['tabindex'] = -1;
        }
        if ($this->obrigatorio === true) {
            $field['required'] = true;
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }
        if ($this->hint != '') {
            $field['data-mdb-toggle'] = "tooltip";
            $field['data-mdb-placement'] = "bottom";
            $field['title'] = $this->hint;
        }
      
        $resp .= form_multiselect($field, $this->opcoes, $this->selecmulti);

        $resp .= "</div>";

        return $resp;
    }

    public function cr_select()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->cr_label();
        }
        $largura = $this->tamanho.'ch';
        if (session()->ismobile) {
            $largura = '';
        }
        $resp .= "<div class='input-group mb-lg-1 mb-2' style='width: $largura !important'>";

        $field = array(
                'name'  		=> $this->nome,
                'id'    		=> $this->id,
                'data-enabled' 	=> $this->leitura,
                'data-alter' 	=> false,
                'data-label' 	=> $this->label,
                'data-valor'	=> $this->selecionado,
                'placeholder' 	=> $this->place,
                'hint'  		=> $this->hint,
                'onchange' 		=> $this->funcao_chan,
                'onblur'        => $this->funcao_blur,
                'class' 		=> ' form-control form-select'
        );
        if (!isset($this->size) || $this->size == '') {
            $this->size = -1;
        }
        if ($this->place != '') {
            $this->opcoes = array(''  => 'Escolha '.$this->place)+$this->opcoes;
        }

        if ($this->leitura === true) {
            $field['readonly'] = "readonly";
            $field['onfocus'] = "this.blur()";
            $field['tabindex'] = -1;
        }
        if ($this->obrigatorio === true) {
            $field['required'] = true;
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }
        if ($this->hint != '') {
            $field['data-mdb-toggle'] = "tooltip";
            $field['data-mdb-placement'] = "bottom";
            $field['title'] = $this->hint;
        }
      
        $resp .= form_dropdown($field, $this->opcoes, $this->selecionado);

        $resp .= "</div>";

        return $resp;
    }

    public function cr_selbusca()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->cr_label('bus_'.$this->id);
        }
        $largura = $this->tamanho.'ch';
        if (session()->ismobile) {
            $largura = '';
        }
        $resp .= "<div class='mb-lg-1 mb-2' style='width: $largura !important'>";
        // CRIA O CAMPO OCULTO QUE ARMAZENA O REGISTRO SELECIONADO
        $field = array(
                'type'  		=> 'hidden',
                'name'  		=> $this->nome,
                'id'    		=> $this->nome,
                'value' 		=> $this->valor,
                'onblur'        => $this->funcao_blur,
                'onchange' 		=> $this->funcao_chan,
        );
        $resp .= form_input($field);

        // CRIA O DROPDOWN DE BUSCA
        $resp .= "<div id='db_$this->id' class='dropdown '>\n";
        $resp .= "<div class='input-group' >\n";
        $field = array(
                        'type'  		=> 'text',
                        'name'  		=> 'bus_'.$this->nome,
                        'id'    		=> 'bus_'.$this->id,
                        'size'			=> $this->size,
                        'maxlength' 	=> $this->tamanho,
                        'value'         => $this->selecionado,
                        'autocomplete'  => 'off',
                        'placeholder' 	=> 'Digite 3 letras para buscar...',
                        'onKeyUp' 	    => "buscar('$this->busca', this,'$this->nome');",
                        'class' 		=> "form-control dropdown-toggle",
                        'data-bs-toggle'=> "dropdown",
                        'aria-expanded' => "false",
                    );
        if ($this->leitura === true) {
            $field['class'] = "form-control";
            $field['data-bs-toggle'] = "";
            $field['readonly'] = "readonly";
            $field['onfocus'] = "this.blur()";
            $field['tabindex'] = -1;
        }
        if ($this->obrigatorio === true) {
            $field['required'] = true;
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }
        $resp .= form_input($field);
        if ($this->leitura === false) {
            $resp .= "<span class='input-group-text'><i class='fa fa-search'></i></span>\n";
            $resp .= "<ul id='dd_$this->nome' class='dropdown-menu w-100' aria-labelledby='bus_".$this->nome."' data-popper-placement='bottom-start' data-bs-auto-close='true' style='position: absolute; inset: 0px auto auto 0px; margin: 0px;transform: translate(0px, 40px)'>\n";
            $resp .= "    <li><h6 class='dropdown-header disabled'>Digite 3 letras para buscar...</h6></li>\n";
            $resp .= "</ul>\n";
        }
        $resp .= "</div>\n";
        $resp .= "</div>\n";
        $resp .= "</div>\n";
        return $resp;
    }

    public function cr_depende()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->cr_label();
        }
        $largura = $this->tamanho.'ch';
        if (session()->ismobile) {
            $largura = '';
        }
        $resp .= "<div class='input-group mb-lg-1 mb-2' style='width: $largura !important'>";

        $field = array(
                'name'  		=> $this->nome,
                'id'    		=> $this->id,
                'data-enabled' 	=> $this->leitura,
                'data-alter' 	=> false,
                'data-label' 	=> $this->label,
                'data-valor'	=> $this->valor,
                'placeholder' 	=> $this->place,
                'hint'  		=> $this->hint,
                'onchange' 		=> $this->funcao_chan,
                'onblur'        => $this->funcao_blur,
                'data-busca'    => $this->busca,
                'data-pai'      => $this->pai,
                'onfocus' 		=> "testa_dep('".$this->pai."')",
                'class' 		=> ' form-control form-select dependente'
        );
        if (!isset($this->size) || $this->size == '') {
            $this->size = -1;
        }
        if ($this->place != '') {
            $this->opcoes = array(''  => 'Escolha '.$this->place)+$this->opcoes;
        }

        if ($this->leitura === true) {
            $field['readonly'] = "readonly";
            $field['onfocus'] = "this.blur()";
            $field['tabindex'] = -1;
        }
        if ($this->obrigatorio === true) {
            $field['required'] = true;
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }
        if ($this->hint != '') {
            $field['data-mdb-toggle'] = "tooltip";
            $field['data-mdb-placement'] = "bottom";
            $field['title'] = $this->hint;
        }
        $resp .= form_dropdown($field, $this->opcoes, $this->valor);
        $resp .= "</div>";

        // $busca = "busca_dependente(this,\"".$this->nome."\",\"".$this->busca."\",\"".$this->valor."\")";
                    
        // $resp .= "\n<script>\n";
        // $resp .= "_chan_ant = jQuery(\"#".$this->pai."\").attr(\"onchange\"); ";
        // $resp .= "\n</script>\n";
        // $resp .= "\n<script>\n";
        // $resp .= "jQuery('#".$this->pai."').attr('onchange',_chan_ant+'; ".$busca."');";
        // $resp .= "jQuery('#".$this->pai."').trigger('change');\n";
        // $resp .= "</script>\n";


        return $resp;
    }

    public function cr_imagem()
    {
        $resp = '';
        if ($this->label != '') {
            $resp .= $this->cr_label();
        }
        $largura = $this->tamanho.'ch';        
        if (session()->ismobile) {
            $largura = '';
        }
        $resp .= "<div class='input-group mb-lg-1 mb-2' style='width: $largura !important'>";

        $field = array(
            'name'  		=> $this->nome,
            'id'    		=> $this->id,
            'data-folder' 	=> $this->pasta,
            'data-file-type' 	=> '.jpg',
            'accept' 	    => '.jpg',
            'data-alter' 	=> false,
            'data-label' 	=> $this->label,
            'data-valor'	=> $this->valor,
            'placeholder' 	=> $this->place,
            'onchange'      => $this->funcao_chan,
            'style'         => "display:none",
            'class'         => ""
        );
        if($this->leitura !== true){
            $resp .= "<label id='lbl_$this->id' class='btn btn-primary' style='white-space: normal;width:$largura; padding:0.8em;' for='$this->id' data-mdb-toggle='tooltip' data-mdb-placement='bottom' title='' data-bs-original-title='A imagem será redimensionada para $this->size X $this->tamanho proporcionalmente' aria-label='A imagem será redimensionada para $this->size X $this->tamanho proporcionalmente' ><i class=\"fas fa-image\"></i> Selecionar imagem de $this->label</label>";
        }

        $resp .= "<div id='view_img_".$this->nome."' class='show img-thumbnail sempadding' style='width:".$this->size."px; height:".$this->tamanho."px;' >";
        $resp .= "<img id='img_".$this->nome."' src='".$this->valor."' for='".$this->id."' class='img-thumbnail sempadding' alt='' style='width:".$this->size."px; height:".$this->tamanho."px;' />";
        $resp .= "</div>";

        if ($this->obrigatorio === true) {
            // $resp .= "<span class='input-group-required' ><i class='fa-solid fa-caret-right form-control-require obrigatorio' title='Campo Obrigatório'></i></span>";
        }
        $resp .= form_upload($field, $this->valor);

        $resp .= "</div>";
        return $resp;
    }

    /**
     * cr_text_show
     *  só imprime o valor
     * @return string
     */
    public function cr_text_show(): string 
    {
        $resp = $this->valor;
        return $resp;
    }
}
?>