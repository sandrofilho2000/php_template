<?php

class Dao
{

    private static $_questions = array();
    private static $_ordenacao = array();
    private static $limite = '';
    private static $agrupamento = '';
    private static $campos_distinct = array();
    private static $dataTable = NULL;
    public static $gerar_log_query = FALSE;
    private static $_resultDataTable = array(
        'draw' => NULL,
        'recordsTotal' => NULL,
        'recordsFiltered' => NULL,
        'data' => array()
    );

    private static function debugSQL($sql, $_params = NULL)
    {
        if (is_null($_params) or empty($_params)) {
            return $sql;
        }
        $_parts = explode('?', $sql);
        $query = '';
        foreach ($_parts as $indice => $trechoSQL) {
            $query .= "$trechoSQL ";
            if (array_key_exists($indice, $_params)) {
                $query .= "'{$_params[$indice]}' ";
            }
        }
        return $query;
    }

    private static function gerarLogException(Exception $ex, $sql, $params)
    {

        $log = $ex->getMessage() . PHP_EOL;
        $log .= self::debugSQL($sql, $params);
        registrarLog('Erro na execução da query', $log, QUERY_LOG);

        /*$titulo = '<p>Erro na execucao da query:</p>'. PHP_EOL;
        $texto = $ex->getMessage(). PHP_EOL;
        $texto .= self::debugSQL($sql, $params);
        $texto .= PHP_EOL;
        $texto .= PHP_EOL;*/
        //registrarLog($titulo, $texto, QUERY_LOG);
        if (AMBIENTE == 'PRODUCAO') {
            return '<p>Consulte o log para verificar o problema abaixo:</p><br/>' . $ex->getMessage();
        }
        $saida = "<p>{$ex->getMessage()}</p>";
        $saida .= self::debugSQL($sql, $params);
        return $saida;
    }

    public static function select($sql, $params, $tipo = NULL, $omitir_codificacao = FALSE)
    {
        try {
            if (!empty(self::$dataTable)) {
                self::$dataTable->recordsTotal = self::contarResultado($sql, $params);
                self::$dataTable->recordsFiltered = self::$dataTable->recordsTotal;
                $sql .= self::$limite;
                $pdo = Conexao::getInstance($omitir_codificacao)->prepare($sql);
                $pdo->execute($params);
                self::$dataTable->data = empty($tipo) ? $pdo->fetchAll(PDO::FETCH_ASSOC) : $pdo->fetchAll(PDO::FETCH_CLASS, $tipo);
                $result = self::$dataTable;
            } else {
                $pdo = Conexao::getInstance($omitir_codificacao)->prepare($sql);
                $pdo->execute($params);
                $result = empty($tipo) ? $pdo->fetchAll(PDO::FETCH_ASSOC) : $pdo->fetchAll(PDO::FETCH_CLASS, $tipo);
            }
            if (self::$gerar_log_query) {
                $log = self::debugSQL($sql, $params);
                registrarLog('DEBUG', $log, QUERY_LOG);
            }
            self::resetarPropriedades();
            return $result;
        } catch (PDOException $ex) {
            $saida = self::gerarLogException($ex, $sql, $params);
            throw new Exception($saida);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
        /*finally {
            $log = self::debugSQL($sql, $params);
            registrarLog('DEBUG', $log, QUERY_LOG);
        }*/
    }

    public static function executeConnectIso($query, $_params = NULL, $gerar_log = FALSE)
    {
        try {
            $conexao = Conexao::getInstanceIso();
            $stmt = $conexao->prepare($query);
            $stmt->execute($_params);
            if (self::$gerar_log_query or $gerar_log) {
                $log = self::debugSQL($query, $_params);
                registrarLog('DEBUG', $log, QUERY_LOG);
            }
            return $stmt;
        } catch (PDOException $ex) {
            $saida = self::gerarLogException($ex, $query, $_params);
            throw new Exception($saida);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function execute($query, $_params = NULL, $gerar_log = FALSE)
    {
        try {
            $conexao = Conexao::getInstance();
            $stmt = $conexao->prepare($query);
            $stmt->execute($_params);
            if (self::$gerar_log_query or $gerar_log) {
                $log = self::debugSQL($query, $_params);
                registrarLog('DEBUG', $log, QUERY_LOG);
            }
            return $stmt;
        } catch (PDOException $ex) {
            $saida = self::gerarLogException($ex, $query, $_params);
            throw new Exception($saida);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    public static function insert($sql, $params = NULL, $omitir_codificacao = FALSE)
    {
        try {
            $conexao = Conexao::getInstance($omitir_codificacao);
            $query = $conexao->prepare($sql);
            $query->execute($params);
            return $conexao->lastInsertId();
        } catch (PDOException $ex) {
            $saida = self::gerarLogException($ex, $sql, $params);
            throw new Exception($saida);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    public static function update($sql, $params = NULL, $omitir_codificacao = FALSE)
    {
        try {
            $query = Conexao::getInstance($omitir_codificacao)->prepare($sql);
            $query->execute($params);
            return $query->rowCount();
        } catch (PDOException $ex) {
            $saida = self::gerarLogException($ex, $sql, $params);
            throw new Exception($saida);
        } catch (Exception $ex) {
            $saida = self::gerarLogException($ex, $sql, $params);
            throw new Exception($saida);
        }
    }

    public static function delete($sql, $params = NULL)
    {
        try {
            $query = Conexao::getInstance()->prepare($sql);
            $query->execute($params);
            return $query->rowCount();
        } catch (PDOException $ex) {
            $saida = self::gerarLogException($ex, $sql, $params);
            throw new Exception($saida);
        } catch (Exception $ex) {
            $saida = self::gerarLogException($ex, $sql, $params);
            throw new Exception($saida);
        }
    }

    public static function carregarNomesColunas(ClasseBase $obj)
    {
        $tablename = $obj->getNomeTabela();
        $sql = "SHOW COLUMNS FROM $tablename";
        $_result = Dao::select($sql, NULL);
        foreach ($_result as $_row) {
            if ($_row['Field'] <> $obj->getNomeChavePrimaria()) {
                array_push($obj->_tabela['colunas'], $_row['Field']);
            }
        }
    }


    public static function buscar(ClasseBase $obj)
    {
        //var_dump($obj); die();
        try {
            $query = $obj->getQueryCorrente();
            if (empty($query)) {
                $query = 'SELECT ';
                $chaves = $obj->getNomeChavePrimaria();
                if (!empty($chaves)) {
                    $query .= $chaves;
                }
                $_colunas = $obj->getColunas();
                if (count($_colunas)) {
                    if (!empty($chaves)) {
                        $query .= ', ';
                    }
                    $query .= join(', ', $obj->getColunas());
                }
                $query .= ' FROM ' . $obj->getNomeTabela() . ' WHERE 1=1 ';
            }
            $query .= self::getCondicoes($obj);
            $query .= self::getGroupBy();
            $query .= self::getOrdem();
            $query = self::getQueryDistinct($query);
            $tipo = (gettype($obj) == 'object' && !$obj->retornarComoArray) ? get_class($obj) : NULL;
            $omitir_codificacao = property_exists($obj, 'omitir_codificacao') ? $obj->omitir_codificacao : FALSE;
            self::$gerar_log_query = $obj->gerar_log_query;
            $_result = self::select($query, array_values(self::$_questions), $tipo, $omitir_codificacao);
            return $_result;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    public static function incluir(ClasseBase $obj)
    {
        $_campos = $obj->getColunas();
        $_values = array();
        foreach ($_campos as $campo) {
            array_push($_values, $obj->$campo);
        }
        $valChavePrimaria = $obj->getValorChavePrimaria();
        if (!empty($valChavePrimaria)) {
            array_push($_campos, $obj->getNomeChavePrimaria());
            array_push($_values, $obj->getValorChavePrimaria());
        }
        $questionMarks = join(', ', array_pad(array(), count($_values), "?"));
        $query = "INSERT INTO {$obj->getNomeTabela()} (" . join(', ', $_campos) . ") VALUES ($questionMarks)";
        self::$gerar_log_query = $obj->gerar_log_query;
        try {
            $id = $obj->getNomeChavePrimaria();
            $obj->$id = self::insert($query, $_values);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    public static function salvar(ClasseBase $obj)
    {
        $_nm_campos = $obj->getColunas();
        $_values = array();
        $_campos = array();
        $_chaves_primarias = array();
        #prepara os campos a serem atualizados, constantes do indice 'campos' do array $_tabela da classe
        foreach ($_nm_campos as $nm_campo) {
            array_push($_values, $obj->$nm_campo);
            array_push($_campos, "$nm_campo = ?");
        }
        #prepara os campos que sao chaves primarias, constantes do indice 'campos' do array $_tabela da classe que pode ser composta
        $chaves_primarias = $obj->getNomeChavePrimaria();
        foreach (explode(', ', $chaves_primarias) as $chave) {
            array_push($_values, $obj->$chave);
            array_push($_chaves_primarias, "$chave = ?");
        }
        self::$gerar_log_query = $obj->gerar_log_query;
        $query = "UPDATE {$obj->getNomeTabela()} SET " . join(", ", $_campos) . " WHERE " . join(" AND ", $_chaves_primarias);
        try {
            return self::update($query, $_values);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    public static function excluir(ClasseBase $obj)
    {
        $_values = array();
        $_chaves_primarias = array();
        $nomeTabela = $obj->getNomeTabela();
        $chavesPrimarias = explode(', ', $obj->getNomeChavePrimaria());
        foreach ($chavesPrimarias as $chave) {
            array_push($_values, $obj->$chave);
            array_push($_chaves_primarias, "$chave = ?");
        }
        $query = "DELETE FROM $nomeTabela WHERE " . join(', ', $_chaves_primarias);
        self::$gerar_log_query = $obj->gerar_log_query;
        try {
            return self::delete($query, $_values);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    private static function getCondicoes(ClasseBase $obj)
    {
        $condicoes = '';
        self::$_questions = array();
        if (count($obj->_filtros)) {
            foreach ($obj->_filtros as $clausula => $valor) {
                $condicoes .= $clausula;
                if (is_array($valor)) {
                    foreach ($valor as $val) {
                        array_push(self::$_questions, $val);
                    }
                } else {
                    array_push(self::$_questions, $valor);
                }
            }
        }
        if (count($obj->_filtros_direto)) {
            $condicoes .= implode(' ', $obj->_filtros_direto);
        }
        $obj->_filtros = array();
        $obj->_filtros_direto = array();
        return $condicoes;
    }
    private static function getGroupBy()
    {
        if (!empty(self::$agrupamento)) {
            return 'GROUP BY ' . self::$agrupamento;
        }
        return '';
    }

    private static function getOrdem()
    {
        $ordenacao = "";
        if (count(self::$_ordenacao)) {
            $ordenacao .= " ORDER BY " . implode(', ', self::$_ordenacao) . " ";
        }
        return $ordenacao;
    }
    public static function setParamsObjDataTable(DataTable $dataTable, $obj = NULL)
    {
        //limitando o resultado da consulta
        if ($dataTable->start != '' and $dataTable->length != '') {
            self::setLimite($dataTable->start, $dataTable->length);
        }
        //adicionando a ordenacao do dataTable na query
        if (count($dataTable->_order)) {
            foreach ($dataTable->_order as $ordem) {
                self::setOrdem($dataTable->getNameColumn($ordem['column']), $ordem['dir']);
            }
        }
        //adicionando a pesquisa geral nos campos definidos como pesquisaveis
        if (!empty($dataTable->_search) and isset($obj)) {
            $_condicoes = array();
            foreach ($dataTable->_columns as $_column) {
                if ($_column['searchable'] == 'true' or $_column['searchable'] == true) {
                    $_condicoes[] = "{$_column['data']} LIKE '%{$dataTable->_search}%'";
                }
            }
            if (count($_condicoes)) {
                $tx_condicoes = '(' . join(' OR ', $_condicoes) . ')';
                $obj->filtrar(date('U'), $tx_condicoes, 'EXPLICITO');
            }
        }
        //var_dump($dataTable);
        //self::$_resultDataTable['draw'] = $dataTable->draw;
        self::$dataTable = $dataTable;
    }
    public static function setLimite($inicio, $qtde)
    {
        //$this->limite = "LIMIT $inicio, $qtde";
        self::$limite = "LIMIT $inicio, $qtde";
    }
    public static function setOrdem($nm_campo, $direcao)
    {
        array_push(self::$_ordenacao, "$nm_campo " . strtoupper($direcao));
    }
    public static function setAgrupamento($agrupamento)
    {
        self::$agrupamento = $agrupamento;
    }
    private static function contarResultado($sqlOriginal, $parametros = NULL)
    {
        $pdo = Conexao::getInstance();
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM ($sqlOriginal) x");
        $stmt->execute(array_values($parametros));
        return (int) ($stmt->fetchColumn());
    }
    private static function resetarPropriedades()
    {
        self::$_questions = array();
        self::$_ordenacao = array();
        self::$agrupamento = '';
        self::$limite = '';
        self::$_resultDataTable = array(
            'draw' => NULL,
            'recordsTotal' => NULL,
            'recordsFiltered' => NULL,
            'data' => array()
        );
    }
    /**
     * Usado para tornar a query original numa subquery para obter o agrupamento definido
     * @param type $_campos
     */
    public static function setDistinct($_campos)
    {
        if (!is_array($_campos)) {
            throw new Exception('O parâmetros informado na execução do método ' . __METHOD__ . ' deve ser um array.');
        }
        foreach ($_campos as $campo) {
            array_push(self::$campos_distinct, $campo);
        }
    }

    private static function getQueryDistinct($query)
    {
        if (count(self::$campos_distinct) == 0) {
            return $query;
        }
        $campos = join(', ', self::$campos_distinct);
        $query_distinct = "SELECT DISTINCT $campos FROM ({$query}) tabela ";
        self::$campos_distinct = array();
        return $query_distinct;
    }
}
