<?php

class ClasseBase {
    
    /*protected static $_tabela = array(
        'nome' => NULL,
        'chave_primaria' => array(),
        'colunas' => array()
    );
    protected static $_obrigatorios = array();*/
    
    public $queryCorrente = NULL;
    public $_filtros = array();
    public $_filtros_direto = array();
    public $retornarComoArray = FALSE;
    public $_obrigatorios = array();
    public $_campos_remover_mascara = array();
    public $_campos_formato_data = array();
    public $_campos_formato_data_hora = array();
    public $_campos_formato_json = array();
    public $_campos_formato_moeda = array();
    public $_campos_distinct = array();
    public $_params_execute = array();
    public $gerar_log_query = FALSE;
    public $autor_obrigatorio = FALSE;
    public $ano_letivo_session;
    
    public $log_prioridade = NULL;
    
    public function setTabelaAno($ano){
        //se nao vazio e diferente do ano corrente
        if(!empty($ano) AND $ano <> date('Y')){
            $tabelaAno = $this->_tabela['nome'] . $ano;
            $this->_tabela['nome'] = $tabelaAno;
        }
    }
    
    public function getQueryCorrente(){
        return $this->queryCorrente;
    }
    
    public function filtrar($nm_campo, $vl_valor, $tipo = 'IGUAL'){
        $campo = filter_var($nm_campo);
        $valor = is_array($vl_valor) ? filter_var_array($vl_valor) : filter_var($vl_valor);
        switch ($tipo) {
            case 'MAIOR':
                $this->_filtros["AND $campo > ? "] = $valor;
                break;
            case 'MENOR':
                $this->_filtros["AND $campo < ? "] = $valor;
                break;
            case 'MAIOR_IGUAL':
                $this->_filtros["AND $campo >= ? "] = $valor;
                break;
            case 'MENOR_IGUAL':
                $this->_filtros["AND $campo <= ? "] = $valor;
                break;
            case 'DIFERENTE':
                $this->_filtros["AND $campo <> ? "] = $valor;
                break;
            case 'IN':
                $this->_filtros["AND $campo IN (". implode(', ', array_fill(0, count($valor), '?')) .") "] = $valor;
                break;
            case 'NOT_IN':
                $this->_filtros["AND $campo NOT IN (". implode(', ', array_fill(0, count($valor), '?')) .") "] = $valor;
                break;
            case 'LIKE':
                $valor = mb_strtoupper($valor);
                $this->_filtros["AND UPPER($campo) LIKE ? "] = "%$valor%";
                break;
            case 'NOT LIKE':
                $valor = mb_strtoupper($valor);
                $this->_filtros["AND UPPER($campo) NOT LIKE ? "] = "%$valor%";
                break;
            case 'IS':
                array_push($this->_filtros_direto, "AND $campo IS $valor ");
                break;
            case 'EXPLICITO':
                array_push($this->_filtros_direto, "AND $valor ");
                break;
            case 'IGUAL':
                $this->_filtros["AND $campo = ? "] = $valor;
                break;
        }
    }

    public function buscar($somenteDados = FALSE){
        try{
            $_result = Mensagem::sucesso("Pesquisa realizada com sucesso.");
            $_result['dados'] = Dao::buscar($this);
            if($_result['dados'] instanceof DataTable){
                self::setarParametroDoDataTable($_result);
            }
            return $somenteDados ? $_result['dados'] : $_result;
        } 
        catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function setarParametroDoDataTable(&$_result){
        $dataTable = $_result['dados'];
        $_result['draw'] = $dataTable->draw;
        $_result['length'] = $dataTable->length;
        $_result['start'] = $dataTable->start;
        $_result['recordsTotal'] = $dataTable->recordsTotal;
        $_result['recordsFiltered'] = $dataTable->recordsFiltered;        
    }
    
    public function incluir(){
        try{
            $this->formatarPropriedades();
            $this->validarCamposObrigatorios();
            $this->setPropriedadesVaziasParaNull();
            $this->setLog('INCLUIR');
            Dao::incluir($this);
            $_result = Mensagem::sucesso('Dados incluídos com sucesso.');
            $_result['dados'] = $this->converterArray();
            return $_result;
        } 
        catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    public function salvar($tipoLog = 'ALTERAR', $msgSuccess = 'Dados gravados com sucesso.'){
        try{
            $this->formatarPropriedades();
            $this->validarCamposObrigatorios();
            $this->validarChavePrimaria();
            $this->setPropriedadesVaziasParaNull();
            $this->setLog($tipoLog);
            $_result = Mensagem::sucesso($msgSuccess);
            $_result['dados'] = Dao::salvar($this);
            if($_result['dados']){
                $_result['dados'] = $this->converterArray();
            }
            return $_result;
        } 
        catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    public function excluir(){
        try{
            $this->validarChavePrimaria();
            if(Dao::excluir($this)){
                return Mensagem::sucesso("Dados removidos com sucesso.");
            }
            return Mensagem::erro('Não foi possível remover o item solicitado.');
        } 
        catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    public function excluirLogico(){
        try{
            $this->validarChavePrimaria();
            if(isset($this->flag_exclusao)){
                foreach($this->flag_exclusao as $propriedade => $valor){
                    if(property_exists($this, $propriedade)){
                        $this->$propriedade = $valor;
                    }
                }
            }
            if(property_exists($this, 'dt_exclusao')){
                $this->dt_exclusao = date('Y-m-d H:i:s');
            }
            $_result = Mensagem::sucesso("Dados excluídos com sucesso.");
            $this->setLog('EXCLUIR');
            Dao::salvar($this);
            return $_result;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    private function validarChavePrimaria(){
        $valorChave = $this->getValorChavePrimaria();
        if(empty($valorChave)){
            throw new Exception("A chave primária do objeto (". $this->getNomeChavePrimaria() .") não foi carregada.");
        }
    }
    private function validarCamposObrigatorios(){
        if(property_exists($this, '_obrigatorios') && is_array($this->_obrigatorios) && count($this->_obrigatorios)){
            foreach($this->_obrigatorios as $campo => $descricao){
                $conteudo_campo = trim($this->$campo);
                if(empty($conteudo_campo) and $conteudo_campo !== '0' and $conteudo_campo !== 0){
                    throw new Exception("O campo $descricao deve ser preenchido.");
                }
            }
        }
    }
    public function carregar(){
        $valChavePrimaria = $this->getValorChavePrimaria();
        if(empty($valChavePrimaria)){
            throw new Exception('A chave do objeto deve estar carregada. ('. $this->getNomeChavePrimaria() .')');
        }
        $this->filtrar($this->getNomeChavePrimaria(), $this->getValorChavePrimaria(), 'IGUAL');
        
        $_result = $this->buscar();
        
        /*if($this instanceof Notificacao){
            var_dump($this->getNomeChavePrimaria(), $this->getValorChavePrimaria(), $_result);
        }*/
        
        if(array_key_exists('dados', $_result) && count($_result['dados']) == 1){
            $obj = $_result['dados'][0];
            foreach($obj as $chave => $valor){
                if($chave == '_tabela'){
                    continue;
                }
                $this->$chave = $valor;
            }
        }
    }
    public function getNomeTabela(){
        return $this->_tabela['nome'];
    }
    public function getNomeChavePrimaria(){
        if(count($this->_tabela['chave_primaria']) === 1){
            return $this->_tabela['chave_primaria'][0];
        }
        return join(', ', $this->_tabela['chave_primaria']);
    }
    public function getValorChavePrimaria(){        
        if(count($this->_tabela['chave_primaria']) === 1){
            $chave_primaria = $this->getNomeChavePrimaria();
            return $this->$chave_primaria;
        }
        else if(count($this->_tabela['chave_primaria']) > 1){
            die('A tabela '. $this->getNomeTabela() .' possui chave primária composta.');
        }
        die('A classe não possui o índice "chave_primaria" da propriedade "$_tabela" definido.');
    }
    public function getColunas(){
        return $this->_tabela['colunas'];
    }
    public function setId($id){
        $chave_primaria = $this->getNomeChavePrimaria();
        $this->$chave_primaria = $id;
    }
    public function setPropriedadesDadosPost($fonte = INPUT_POST){
        $_dados = filter_input_array($fonte);
        foreach($_dados as $chave => $valor){
            if(property_exists($this, $chave)){
                if(is_array($valor)){
                    $this->$chave = $_dados[$chave];
                }
                else{
                    $this->$chave = filter_input($fonte, $chave);
                    #var_dump("$chave = ". filter_input($fonte, $chave));
                }
            }
        }
    }
    public function setPropriedades($_dados){
        foreach($_dados as $chave => $valor){
            if(property_exists($this, $chave)){
                if(is_array($valor)){
                    $this->$chave = $_dados[$chave];
                }
                else{
                    $this->$chave = $valor;
                    #var_dump("$chave = ". filter_input($fonte, $chave));
                }
            }
        }
    }
    public function carregarPropriedadesCriptogradas($user, $_dados){
        foreach($_dados as $chave => $valor){
            if(property_exists($this, $chave)){
                if(is_array($valor)){
                    $this->$chave = $_dados[$chave];
                }
                else{
                    $this->$chave = $user->decrypt($valor);
                    var_dump("$chave = ". $user->decrypt($valor));
                }
            }
        }
    }
    public function aplicarFiltros($fonte = INPUT_POST){
        if(filter_has_var($fonte, 'aplicarPaginacaoNoResultado')){
            $dataTable = DataTable::gerarFromPost();
            Dao::setParamsObjDataTable($dataTable, $this);
        }
        $_dados = filter_input_array($fonte);
        if(filter_has_var($fonte, 'ordem')){
            foreach($_dados['ordem'] as $campo => $direcao){
                $this->ordenar($campo, $direcao);
            }
        }
        if(filter_has_var($fonte, 'retornarArray')){
            $this->retornarComoArray = TRUE;
        }
        if(filter_has_var($fonte, 'aplicarDistinct')){
            $this->aplicarDistinct($_dados['aplicarDistinct']);
        }
        if(filter_has_var($fonte, 'filtros')){
            foreach($_dados['filtros'] as $tipoFiltro => $_filtro){
                foreach($_filtro as $campo => $valor){
                    if($valor != ''){
                        $this->filtrar($campo, $valor, $tipoFiltro);
                    }
                }
            }
        }
    }
    public function ordenar($nm_campo, $direcao = 'asc'){
        //$campo_ordem = $this->getNmTabela() .'.'. $nm_campo;
        Dao::setOrdem($nm_campo, $direcao);
    }
    public function agrupar($agrupamento){
        Dao::setAgrupamento($agrupamento);
    }
    public function removerFiltros(){
        $this->_filtros = array();
        $this->_filtros_direto = array();
    }

    public function setLog($tipoLog){
        if(!in_array('tx_log', $this->getColunas())){
            return;
        }
        $_log = array();
        $pkVal = $this->getValorChavePrimaria();
        if(!empty($pkVal) && !empty($this->tx_log)){
            $_log = json_decode($this->tx_log, TRUE);
        }
        $usuario = Sessao::obterUsuarioSessao();
        if(empty($usuario) OR $usuario->nm_usuario == 'Desconhecido'){
            $user = Session::obterUserSession();
            if($user){
                $usuario = new stdClass();
                $usuario->nm_usuario = $user->tx_nome;
            }
            else{
                $userPA = ProcessoAdmissaoSession::obterUserSession();
                if($userPA){
                    $usuario = new stdClass();
                    $usuario->nm_usuario = $userPA->tx_nome;
                }
            }
        }
        if($usuario->nm_usuario === 'Desconhecido'){
            //obtem o usuario da nova estruutura do SO
            $user = UserSO::getUserSession();
            if($user){
                $usuario->nm_usuario = $user->nomeUsuario;
            }
        }
        if($this->autor_obrigatorio AND (empty($usuario) OR $usuario == 'Desconhecido')){
            throw new Exception('Não foi possível identificar os dados de autoria na sessão, por favor, faça o login.');
        }
        if(empty($usuario)){
            return;
        }
        $tipo_log = !empty($this->log_prioridade) ? $this->log_prioridade : $tipoLog;
        
        $nm_usuario = mb_strtoupper($usuario->nm_usuario, 'UTF-8');
        
        if(isset($this->log_novo)){
            $_log[date('Y-m-d H:i:s')] = array($nm_usuario => $tipo_log);
        }
        else{
            $_log[$tipo_log] = array('HORA' => date('Y-m-d H:i:s'), 'AUTOR' => $nm_usuario);
        }
        $this->tx_log = jsonUnescapedUnicode($_log);//json_encode($_log, JSON_UNESCAPED_UNICODE);
    }

    public function setLogPrioridade($sigla){
        $log = strtoupper(trim($sigla));
        $find = ' '; // espaço vazio
        $replace = '_'; // valor vazio
        $this->log_prioridade = str_replace($find, $replace, $log); 
    }
    /*public function converterArray(){
        $_propriedade = $this->getColunas();
        $_dados = array();
        $nomePk = $this->getNomeChavePrimaria();
        $_dados[$nomePk] = $this->getValorChavePrimaria();
        foreach($_propriedade as $prop){
            #if($prop <> 'tx_log'){
                $_dados[$prop] = $this->$prop;
            #}
        }
        return $_dados;
    }*/
    
   public function converterArray(){
        $_propriedades = get_object_vars($this);
        $nomePk = $this->getNomeChavePrimaria();
        $_propriedades[$nomePk] = $this->getValorChavePrimaria();
        $_remover = array(
            'queryCorrente', '_filtros', '_filtros_direto', 'retornarComoArray', 
            '_obrigatorios', '_campos_formato_data', '_campos_formato_data_hora', '_campos_formato_json', 
            '_campos_formato_moeda', '_campos_distinct', '_params_execute', 'gerar_log_query', 
            'autor_obrigatorio', 'log_prioridade', 'dao', '_tabela'
        );
        foreach($_remover as $prop){
            if(array_key_exists($prop, $_propriedades)){
                unset($_propriedades[$prop]);
            }
        }
        return $_propriedades;
   }
    public function setPropriedadesVaziasParaNull(){
        $_propriedades = $this->_tabela['colunas'];
        foreach($_propriedades as $propriedade){
            if(property_exists($this, $propriedade) && $this->$propriedade === ''){
                $this->$propriedade = NULL;
            }
        }
    }
    public static function getCamposDefault(){
        
    }
    
    public function formatarPropriedades(){
        $_propriedades_data_hora = $this->_campos_formato_data_hora;
        $_propriedades_data = $this->_campos_formato_data;
        $_propriedades_json = $this->_campos_formato_json;
        $_propriedades_moeda = $this->_campos_formato_moeda;
        $_propriedades_mascara = $this->_campos_remover_mascara;
        try{
            if(count($_propriedades_data)){
                foreach($_propriedades_data as $propriedade){
                    if(!empty($this->$propriedade) && isDate($this->$propriedade, 'd/m/Y')){
                        $this->$propriedade = parseDate($this->$propriedade, 'Y-m-d');
                    }
                }
            }
            if(count($_propriedades_data_hora)){
                foreach($_propriedades_data_hora as $propriedade){
                    if(!empty($this->$propriedade) && isDate($this->$propriedade, 'd/m/Y H:i:s')){
                        $this->$propriedade = parseDate($this->$propriedade, 'Y-m-d H:i:s');
                    }
                }
            }
            if(count($_propriedades_json)){
                foreach($_propriedades_json as $propriedade){
                    if(is_array($this->$propriedade) && count($this->$propriedade) == 0 OR empty($this->$propriedade)){
                        $this->$propriedade = NULL;
                    }
                    if(is_array($this->$propriedade)){
                        //$this->$propriedade = json_encode($this->$propriedade);
                        $this->$propriedade = jsonUnescapedUnicode($this->$propriedade);
                    }
                }
            }
            if(count($_propriedades_moeda)){
                foreach($_propriedades_moeda as $propriedade){
                    if(!empty($this->$propriedade)){
                        $this->$propriedade = parseMoney($this->$propriedade);
                    }
                    else{
                        $this->$propriedade = 0.00;
                    }
                }
            }
            if(count($_propriedades_mascara)){
                foreach($_propriedades_mascara as $propriedade){
                    if(!empty($this->$propriedade)){
                        $this->$propriedade = removerMascara($this->$propriedade);
                    }
                }
            }
        }
        catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    
    public function getLog($acao = NULL){
        if(empty($this->tx_log) OR !property_exists($this, 'tx_log')){
            return '';
        }
        $_log = json_decode($this->tx_log, TRUE);
        if(empty($acao)){
            $_saida = array();
            foreach($_log as $nome_acao => $_log_acao){
                array_push($_saida, '<strong>AÇÃO</strong>: '. $nome_acao .' |  <strong>HORÁRIO</strong>: '. parseDate($_log_acao['HORA'], 'd/m/Y H:i:s') .' | <strong>AUTOR</strong>: '. $_log_acao['AUTOR']);
            }
            return join('<br/>', $_saida);
        }
        else if(array_key_exists($acao, $_log)){
            if(!empty($acao)){
                return 'HORÁRIO: '. parseDate($_log[$acao]['HORA'], 'd/m/Y H:i:s') .' | AUTOR: '. $_log[$acao]['AUTOR'];
            }
            else{
                
            }
        }
        return '';
    }
    /**
     * Obtem o resultado da consulta por Distinct
     * @param array $_campos campos a serem utilizados na clausula DISTINCT 
     */
    public function aplicarDistinct($_campos){
        Dao::setDistinct($_campos);
    }
    
    public static function getAll($class_name, $_filters = array(), $_orders = array(), $out_array = FALSE){
        if(!class_exists($class_name)){
            die("Classe $class_name não encontrada.");
        }
        $obj = new $class_name();
        try{
            if(!empty($_orders)){
                foreach($_orders as $campo => $direcao){
                    $obj->ordenar($campo, $direcao);
                }
            }
            if(!empty($_filters)){
                foreach($_filters as $tipo => $_filtro){
                    foreach($_filtro as $campo => $valor){
                        if($valor != ''){
                            $obj->filtrar($campo, $valor, $tipo);
                        }
                    }
                }
            }
            if($out_array){
                $obj->retornarComoArray = TRUE;
            }
            return $obj->buscar(TRUE);
        } 
        catch (Exception $ex) {
            die($ex->getMessage());
        }
    }
}
