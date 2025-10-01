<?php

require_once '../config/config.php';

try{
    
    $acao = filter_input(INPUT_POST, 'acao');
    
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
        $id_objeto =  filter_input(INPUT_POST, 'id_objeto');
        if(!is_numeric($id_objeto)){
            throw new Exception('Erro, código NI.'); /* parametro id_objeto nao-numerico */
        }
        if(!empty($id_objeto)){
            $obj->setId($id_objeto);
            $obj->carregar();
        }
    }
    
    switch ($acao) {
        case 'pesquisar':
            $obj->retornarComoArray = TRUE;
            $obj->aplicarFiltros();
            $_result = $obj->buscar();
            break;
        
        case 'incluir':
        case 'salvar':
        case 'excluir':
            $obj->setPropriedadesDadosPost();
            $_result = $obj->$acao();
            break;
        
        case 'execute':
            $metodo = filter_input(INPUT_POST, 'metodo');
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

        default:
            $_result = Mensagem::informacao("Nenhuma ação encontrada para a requisição solicitada.");
            break;
    }
} 
catch (Exception $ex) {
    $_result = Mensagem::erro($ex->getMessage());
    $_result['dados'] = array();
}
header('Content-Type: application/json');
if(isset($_result) AND !empty($_result)){
    echo jsonUnescapedUnicode($_result);
}
else{
    echo jsonUnescapedUnicode(array());
}

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