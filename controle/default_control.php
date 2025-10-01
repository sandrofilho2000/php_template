<?php
require_once '../config/config.php';

try{
    $status_code = 200;
    
    if(AMBIENTE === 'PRODUCAO'){
        $status_code = 401;
        header('Access-Control-Allow-Origin: http://www.csanl.com.br/');
        #header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    }
    
    if(!Session::validateOrigin()){
        $status_code = 401;
        throw new Exception('Este host não é conhecido.');
    }
    
    $user = Session::validateUserSession();

    if($user === FALSE){
        throw new Exception('Sessão expirada, <a href="">clique aqui</a> para realizar o login novamente.');
    }
    
    $className = filter_input(INPUT_POST, 'objeto');

    if(!class_exists($className)){
        throw new Exception("Erro, código CN."); /* nome da classe inexistente */
    }
    
    $obj = new $className();
    
    $_dataSend = filter_input_array(INPUT_POST);
    
    if(array_key_exists('ano_letivo_session', $_dataSend)){
        $ano = $_dataSend['ano_letivo_session'];
        $obj->setTabelaAno($ano);
    }
    
    if(filter_input(INPUT_POST, 'id_objeto')){
        $id_objeto =  filter_input(INPUT_POST, 'id_objeto', FILTER_SANITIZE_NUMBER_INT);
        if(!is_numeric($id_objeto)){
            throw new Exception('Erro, código NI.'); /* parametro id_objeto nao-numerico */
        }
        if(!empty($id_objeto)){
            $obj->setId($id_objeto);
            $obj->carregar();
        }
    }
    
    $acao = filter_input(INPUT_POST, 'acao');
    
    switch ($acao) {
        case 'pesquisar':
            $obj->retornarComoArray = TRUE;
            $obj->aplicarFiltros();
            $_result = $obj->buscar();
            break;
        
        case 'incluir':
        case 'salvar':
        case 'excluir':
        case 'excluirLogico':
            $obj->setPropriedadesDadosPost();
            $_result = $obj->$acao();
            break;
        
        case 'execute':
            $metodo = filter_input(INPUT_POST, 'metodo', FILTER_SANITIZE_STRING);
            if(empty($metodo) OR !method_exists($obj, $metodo)){
                throw new Exception('Erro, código MI.'); /* metodo inexistente */
            }
            if(filter_has_var(INPUT_POST, 'filtros')){
                $obj->aplicarFiltros();
            }
            $obj->setPropriedadesDadosPost();
            $_result = $obj->$metodo();
            break;
        
        case 'inicializar_vazio':
            $_result = array();
            break;
        
        case 'upload':
            #var_dump($_FILES);
            #var_dump($_POST);
            if(empty($user) OR empty($user->tx_cpf)){
                throw new Exception('Não foi possível identificar as credenciais necessárias para realizar o upload.');
            }
            $obj->setPropriedadesDadosPost();
            $_result = $obj->upload($user->tx_cpf, 'file');
            
            break;
            
        case 'upload_ckeditor';
            if(empty($user) OR empty($user->tx_cpf)){
                throw new Exception('Não foi possível identificar as credenciais necessárias para realizar o upload.');
            }
            $_result['url'] = Upload::executeUpload($user->tx_cpf, 'upload');
            break;
            
        case 'inicializar_datatable_vazio':
            $_result = Mensagem::sucesso('Inicialização vazia.');
            $_result['dados'] = array();
            break;

        default:
            $_result = Mensagem::informacao("Nenhuma ação encontrada para a requisição solicitada.");
            
            break;
    }
} 
catch (Exception $ex) {
    $_result = Mensagem::erro($ex->getMessage());
    $_result['dados'] = array();
    if($status_code == 200){
        $status_code = 400;
    }
}

header('Content-Type: application/json');
//http_response_code($status_code);
echo json_encode($_result);

/* SOMENTE A PARTIR DO PHP 5.4
$show_json = json_encode($_result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if (json_last_error() == JSON_ERROR_UTF8) {
    $show_json = json_encode($_result, JSON_PARTIAL_OUTPUT_ON_ERROR );
}
if ( $show_json !== false ) {
    header('Content-Type: application/json');
    echo($show_json);
} 
else {
    die("json_encode fail: " . json_last_error_msg());
}
 * *?
 */