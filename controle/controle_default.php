<?php

require_once '../config/config.php';

try{
    
    $acao = filter_input(INPUT_POST, 'acao');
    
    if($acao === 'inicializar_vazio'){
        throw new Exception("Inicialização vazia do DataTable.");
    }
    
    $className = filter_input(INPUT_POST, 'objeto');

    if(!class_exists($className)){
        throw new Exception("Classe $className inexistente.");
    }
    
    /*if(Session::validateUserSession() === FALSE){
        throw new Exception('Sessão expirada, <a href="login.php">clique aqui</a> para efetuar o login novamente.');
    }*/
    
    $obj = new $className();
    
    if(filter_input(INPUT_POST, 'id_objeto')){
        $id_objeto =  filter_input(INPUT_POST, 'id_objeto');
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
            if(!empty($metodo)){
                $metodo_existe = method_exists($obj, $metodo);
                if($metodo_existe){
                    if(filter_has_var(INPUT_POST, 'filtros')){
                        $obj->aplicarFiltros();
                    }
                    $obj->setPropriedadesDadosPost();
                    $_result = $obj->$metodo();
                }
            }
            if(!isset($_result)){
                $_result = Mensagem::informacao("Não foi possível executar o método solicitado.");
            }
            break;
        
        case 'inicializar_vazio':
            $_result = array(
                'dados' => array()
            );
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