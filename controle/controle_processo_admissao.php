<?php
require_once '../config/config.php';

try{
    if(AMBIENTE === 'PRODUCAO'){
        header('Access-Control-Allow-Origin: http://www.csanl.com.br/');
    }
    if(!Session::validateOrigin()){
        throw new Exception('Acesso não autorizado.');
    }
    if(!filter_has_var(INPUT_POST, 'recaptcha_token')){
        validarCaptcha();
    }
    $className = filter_input(INPUT_POST, 'objeto', FILTER_SANITIZE_STRING);
    if(!class_exists($className)){
        throw new Exception("Erro, código CI."); /* nome da classe inexistente */
    }
    $obj = new $className();
    $_dataSend = filter_input_array(INPUT_POST);
    
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
    $acao = filter_input(INPUT_POST, 'acao', FILTER_SANITIZE_STRING);
    switch ($acao) {
        case 'incluir':
            $obj->setPropriedadesDadosPost();
            $_result = $obj->$acao();
            break;

        default:
            $_result = Mensagem::informacao("Nenhuma ação encontrada para a requisição solicitada.");
            break;
    }
} 
catch (Exception $ex) {
    $_result = Mensagem::erro($ex->getMessage());
    $_result['dados'] = array();
}

//header('Content-Type: application/json');
echo json_encode($_result);
