<?php
/* lib\sql\t_catraca.sql */
class Catraca extends ClasseBase
{

    public $id_catraca;
    public $dt_hora_entrada;
    public $dt_hora_saida;
    public $tx_tipo_cracha;
    public $tx_cracha;
    public $tx_nome_atendido;
    public $tx_cpf_atendido;
    public $nr_matricula;
    public $tx_tipo;
    public $tx_local;
    public $tx_imagesource;
    public $tx_observacao;
    public $tx_log;
    public $dt_ano_letivo;
    public $tx_grau;
    public $nr_serie;
    public $tx_turma;
    private $parcial_dias_letivos = null; // Propriedade para armazenar os dados (?int)
    public $dt_filtro_inicio;
    public $dt_filtro_fim;
    public $dt_matricula;
    public $data_formatada;
    public $dtsa_da;
    public $order_table_by;
    protected $dao;
    private $dados_ano_letivo = null; // Propriedade para armazenar os dados (?object)
    private $sabados_domingos_feriados_recessos = null; //(?array)
    const DIR_RELATIVO = '../modulo_administrativo/catraca/';
    const DIR_FOTOS = 'img/por_catraca/';
    const DIR_TMP_FOTOS = 'img/temp/';
    const DIR_FOTOS_POR_CPF = 'img/por_cpf/';
    private static $_tipos_crachas = array(
        'RESPONSAVEL' => 'Responsável',
        'VISITANTE' => 'Visitante',
        'PRESTADOR_SERVICO' => 'Prestador de Serviço',
        'PROFESSOR_SUBSTITUTO' => 'Professor Substituto',
        'CATEQUISTA' => 'Catequista',
        'ATIVIDADE_EXTRA' => 'Atividade Extra',
        'REFRIGERACAO' => 'Refrigeração',
        'CANTINA' => 'Cantina',
    );
    private static $_locais_catraca = array(
        'NL' => 'Unidade Novo Leblon',
        'ICSA' => 'Instituto Colégio Santo Agostinho',
        'BIBLIOTECA-NL' => 'Biblioteca - NL',
        'BIBLIOTECA-ICSA' => 'Biblioteca - ICSA'
    );
    private static $_tipos_catraca = array(
        'PRESTADOR_SERVICO' => 'Prestador de Serviço',
        'ATENDIMENTO_PROFESSOR' => 'Catraca Professor',
        'ATENDIMENTO_TESOURARIA' => 'Catraca Tesouraria',
        'ATENDIMENTO_SCRETARIA' => 'Catraca Secretaria',
        'ATENDIMENTO_DIRECAO' => 'Catraca Direção',
        'ATENDIMENTO_COORDENACAO' => 'Catraca Coordenação',
        'ATENDIMENTO_DEPARTAMENTO_PESSOAL' => 'Catraca Departamento Pessoal',
        'ATENDIMENTO_ORIENTACAO' => 'Catraca Orientação',
        'ATENDIMENTO_TECNOLOGIA' => 'Catraca Tecnologia'
    );

    protected $_tabela = array(
        'nome' => 't_catraca',
        'chave_primaria' => array('id_catraca'),
        'colunas' => array(
            'dt_hora_entrada',
            'dt_hora_saida',
            'tx_tipo_cracha',
            'tx_cracha',
            'tx_nome_atendido',
            'tx_cpf_atendido',
            'tx_tipo',
            'tx_local',
            'tx_imagesource',
            'tx_observacao',
            'tx_log'
        )
    );

    public function __construct($id = NULL)
    {
        if (!empty($id)) {
            $this->setId($id);
            $this->carregar();
        }
    }


    public function convertStringToDateTime($date)
    {
        if ($date) {
            if (is_string($date)) {
                $date = new DateTime($date);
            }
            return $date;
        }
    }
    public function convertDateTimeToString(&$date)
    {
        if ($date) {
            if ($date instanceof DateTime) {
                $date = $date->format('Y-m-d H:i:s');
            }
        }
        return $date;
    }

    /*     SELECT count(*) as total, 'EF' as serie
        FROM t_catraca
        group by data */

    private function getDadosAnoLetivo($novoFiltro = false, $dt_filtro = "")
    {
        if ($novoFiltro === true || !isset($_SESSION['dados_ano_letivo'])) {
            $_SESSION['dados_ano_letivo'] = DiarioControle::getDadosAnoLetivo($this->convertDateTimeToString($dt_filtro));
        }
        return $_SESSION['dados_ano_letivo'];
    }

    public function getSabadosDomingosFeriadosRecessos($novoFiltro = false, $data_inicio = "", $data_fim = "", $tipo_evento = "")
    {
        if ($novoFiltro === true || !isset($_SESSION['sabados_domingos_feriados_recessos'])) {
            $_SESSION['dados_ano_letivo'] = $this->getDadosAnoLetivo();

            if (empty($data_inicio)) {
                $data_inicio = $_SESSION['dados_ano_letivo']->inicioAulas;
            }
            if (empty($data_fim)) {
                $data_fim = $_SESSION['dados_ano_letivo']->terminoAulas;
            }

            $cronograma = new Cronograma();
            $sabados_e_domingos = $cronograma->getSabadosEDomingosEmUmPeriodo($data_inicio, $data_fim);
            $feriados = $cronograma->getFeriadosEmUmPeriodo($data_inicio, $data_fim);
            $recessos = $cronograma->getRecessosEmUmPeriodo($data_inicio, $data_fim);
            $sabados_domingos_feriados_recessos = array_merge(
                $sabados_e_domingos,
                $feriados,
                $recessos
            );

            $_SESSION['sabados_domingos_feriados_recessos'] = $this->removerDuplicadosPorAtributo($sabados_domingos_feriados_recessos, 'start');
        }

        $result = $_SESSION['sabados_domingos_feriados_recessos'];

        if (!empty($data_inicio) && !empty($data_fim)) {
            $result = $this->filtrarPorIntervaloDeData($result, $data_inicio, $data_fim);
        }

        if (!empty($tipo_evento)) {
            $result = $this->filtrarPorTipoEvento($result, $tipo_evento);
        }

        return $result;
    }

    private function filtrarPorIntervaloDeData($eventos, $data_inicio, $data_fim)
    {
        $data_inicio = $this->convertStringToDateTime($data_inicio);
        $data_fim = $this->convertStringToDateTime($data_fim);

        $filtrados = array();
        foreach ($eventos as $evento) {
            $eventoStart = $this->convertStringToDateTime($evento['start']);
            $eventoEnd = $this->convertStringToDateTime($evento['end']);
            if ($eventoStart >= $data_inicio && $eventoEnd <= $data_fim) {
                $filtrados[] = $evento;
            }
        }

        return $filtrados;
    }

    private function filtrarPorTipoEvento($eventos, $tipo_evento)
    {
        $filtrados = array();
        foreach ($eventos as $evento) {
            if ($evento['tipo_evento'] === $tipo_evento) {
                $filtrados[] = $evento;
            }
        }

        return $filtrados;
    }

    public function getParcialDiasLetivos($novoFiltro = false, $dt_filtro = "")
    {
        if ($novoFiltro || $this->parcial_dias_letivos === null) {
            $dados_ano_letivo = $this->getDadosAnoLetivo(true, $dt_filtro);

            if (!$dados_ano_letivo) {
                return 0;
            }

            $dt_filtro_inicio = $this->convertDateTimeToString($this->dt_filtro_inicio);
            $dt_filtro_fim = $this->convertDateTimeToString($this->dt_filtro_fim);

            $sabados_domingos_feriados_recessos = $this->getSabadosDomingosFeriadosRecessos(false, $dt_filtro_inicio, $dt_filtro_fim);
            $sabados_domingos_feriados_recessos = count($sabados_domingos_feriados_recessos);

            $sabados_letivos = $this->getNumSabadosLetivos();
            $sabados_domingos_feriados_recessos = $sabados_domingos_feriados_recessos - $sabados_letivos;
            $dt_filtro_inicio = $this->convertStringToDateTime($this->dt_filtro_inicio);
            $dt_filtro_fim = $this->convertStringToDateTime($this->dt_filtro_fim);

            $intervalo = $dt_filtro_inicio->diff($dt_filtro_fim);
            $diferenca_dias_filtros = $intervalo->format('%a') + 1;

            $this->parcial_dias_letivos = $diferenca_dias_filtros - $sabados_domingos_feriados_recessos;

            if ($this->parcial_dias_letivos < 0) {
                $this->parcial_dias_letivos = 0;
            }
        }
        $_SESSION['parcial_dias_letivos'] = $this->parcial_dias_letivos;
        return $this->parcial_dias_letivos;
    }

    private function removerDuplicadosPorAtributo($array, $atributo)
    {
        $unicos = array();
        $valoresVistos = array();

        foreach ($array as $item) {
            $valorAtributo = $item[$atributo];

            // Se o valor do atributo ainda não foi visto, adiciona ao array de únicos
            if (!in_array($valorAtributo, $valoresVistos)) {
                $valoresVistos[] = $valorAtributo;
                $unicos[] = $item;
            }
        }

        return $unicos;
    }


    public function pesquisarBase($somenteDados = FALSE)
    {
        try {
            $this->queryCorrente = "
                SELECT DISTINCT * FROM (
                    SELECT nomcontra AS tx_nome, 
                           CONCAT(SUBSTR(cpfcontra, 1, 3), '.', SUBSTR(cpfcontra, 4, 3), '.', SUBSTR(cpfcontra, 7, 3), '-', SUBSTR(cpfcontra, -2)) AS tx_cpf
                    FROM Alunos 
                    WHERE NOT ISNULL(nomcontra) AND LENGTH(cpfcontra) = 11
                    /*AND cpfcontra NOT IN (SELECT tx_cpf_atendido FROM t_catraca)*/
                    UNION
                    SELECT paialuno AS tx_nome,
                           CONCAT(SUBSTR(cpfpaalu, 1, 3), '.', SUBSTR(cpfpaalu, 4, 3), '.', SUBSTR(cpfpaalu, 7, 3), '-', SUBSTR(cpfpaalu, -2)) AS tx_cpf
                    FROM Alunos
                    WHERE NOT ISNULL(paialuno) AND LENGTH(cpfpaalu) = 11
                    /*AND cpfpaalu NOT IN (SELECT tx_cpf_atendido FROM t_catraca)*/
                    UNION
                    SELECT maealuno AS tx_nome,
                           CONCAT(SUBSTR(cpfmaalu, 1, 3), '.', SUBSTR(cpfmaalu, 4, 3), '.', SUBSTR(cpfmaalu, 7, 3), '-', SUBSTR(cpfmaalu, -2)) AS tx_cpf
                    FROM Alunos
                    WHERE NOT ISNULL(maealuno) AND LENGTH(cpfmaalu) = 11
                    /*AND cpfmaalu NOT IN (SELECT tx_cpf_atendido FROM t_catraca)*/
                    UNION
                    SELECT tx_nome_atendido AS tx_nome, tx_cpf_atendido AS tx_cpf
                    FROM t_catraca
                ) x WHERE 1=1
            ";
            return $this->buscar($somenteDados);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getPresencasPorTurma($tx_grau, $nr_serie)
    {
        $dt_filtro_inicio = $this->convertDateTimeToString($this->dt_filtro_inicio);
        $dt_filtro_inicio .= " 00:00:00";

        $dt_filtro_fim = $this->convertDateTimeToString($this->dt_filtro_fim);
        $dt_filtro_fim .= " 23:59:59";

        $tx_turma = $this->tx_turma;
        $dt_ano_letivo = $this->dt_ano_letivo;
        try {
            // Construção da consulta SQL
            $this->queryCorrente = "SELECT " .
                "CONCAT(m.tx_grau, m.nr_serie) AS grau_serie, " .
                "COUNT(DISTINCT c.id_catraca) AS presencas  " .
                "FROM " .
                "t_catraca AS c " .
                "JOIN " .
                "t_matricula AS m  " .
                "ON c.nr_matricula = m.nr_matricula " .
                "LEFT JOIN Alunos AS a " .
                "ON m.nr_matricula = a.matricula " .
                "WHERE m.tx_aluno_cpf NOT LIKE '..-' " .
                "AND DAYOFWEEK(c.dt_hora_entrada) NOT IN (1) " .
                "AND m.nr_ano_letivo = '$dt_ano_letivo' " .
                "AND a.dtsa_da IS NULL " .
                "AND " .
                "((c.dt_hora_entrada BETWEEN '$dt_filtro_inicio' AND '$dt_filtro_fim' AND c.dt_hora_saida <= '$dt_filtro_fim') " .
                "OR " .
                "(c.dt_hora_entrada BETWEEN '$dt_filtro_inicio' AND '$dt_filtro_fim' AND c.dt_hora_saida IS NULL) ) ";

            // Filtros opcionais para grau, série e turma
            if ($tx_grau) {
                $this->filtrar("m.tx_grau", $tx_grau, "IGUAL");
            }
            if ($nr_serie) {
                $this->filtrar("m.nr_serie", $nr_serie, "IGUAL");
            }
            if ($tx_turma) {
                $this->filtrar("m.tx_turma", $tx_turma, "IGUAL");
            }

            // Agrupamento por grau e série
            $this->agrupar("m.tx_grau, m.nr_serie");

            // Executa a consulta
            $result = $this->buscar(true);

            $presencas = 0;

            if (!empty($result) && isset($result[0]->presencas)) {
                $presencas = $result[0]->presencas;
            }

            if (isset($presencas) && is_numeric($presencas)) {
                return (int)$presencas;
            } else {
                return 0;
            }

        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getNumSabadosLetivos()
    {
        $dt_filtro_inicio = $this->convertDateTimeToString($this->dt_filtro_inicio) . " 00:00:00";
        $dt_filtro_fim    = $this->convertDateTimeToString($this->dt_filtro_fim) . " 23:59:59";

        $sabados = $this->getSabadosDomingosFeriadosRecessos(false, $dt_filtro_inicio, $dt_filtro_fim, "S");
        $num_sabados_letivos = 0;

        foreach($sabados as $sabado){
            $sabado_start = $this->convertDateTimeToString($sabado['start']);
            $sabado_start .= " 00:00:00";
            $sabado_end   = $this->convertDateTimeToString($sabado['end']);
            $sabado_end .= " 23:59:59";

            $this->queryCorrente = "
                SELECT * FROM t_catraca AS c
                WHERE DAYOFWEEK(c.dt_hora_entrada) = 7
                AND (
                    (c.dt_hora_entrada BETWEEN '$sabado_start' AND '$sabado_end' AND c.dt_hora_saida <= '$sabado_end')
                    OR
                    (c.dt_hora_entrada BETWEEN '$sabado_start' AND '$sabado_end' AND c.dt_hora_saida IS NULL)
                )
            ";

            $result = $this->buscar(true);
            $contar = count($result);
            
            if ($contar != 0) {
                $num_sabados_letivos++;
            }
        }

        return $num_sabados_letivos;
    }

    public function importarDadosParaCatraca($somenteDados = false)
    {
        try {
            $query = "SELECT IdUsuario, Data, Hora, IdEvento FROM TransacoesDispositivos WHERE idUsuario = '00000011697' LIMIT 100";
            $result = $this->buscar($query);

            if (!$result) {
                throw new Exception("Nenhuma transação encontrada.");
            }

            $usuariosProcessados = array();

            foreach ($result as $transacao) {
                $idUsuario = (int) $transacao['IdUsuario'];
                $data = $transacao['Data'];
                $hora = trim($transacao['Hora']);
                $evento = (int) $transacao['IdEvento'];

                if (!isset($usuariosProcessados[$idUsuario][$data])) {
                    $usuariosProcessados[$idUsuario][$data] = array(
                        'entrada' => null,
                        'saida' => null
                    );
                }

                if (in_array($evento, array(1, 3))){ // Entrada
                    if (!$usuariosProcessados[$idUsuario][$data]['entrada']) {
                        $usuariosProcessados[$idUsuario][$data]['entrada'] = "$data $hora";
                    }
                } elseif (in_array($evento, array(2, 4))){ // Saída
                    if (!$usuariosProcessados[$idUsuario][$data]['saida']) {
                        $usuariosProcessados[$idUsuario][$data]['saida'] = "$data $hora";
                    }
                }
            }

            // Inserir os registros na tabela t_catraca
            foreach ($usuariosProcessados as $idUsuario => $datas) {
                foreach ($datas as $data => $horarios) {
                    $dtEntrada = !empty($horarios['entrada']) ? $horarios['entrada'] : null;
                    $dtSaida = !empty($horarios['saida']) ? $horarios['saida'] : null;

                    if ($dtEntrada || $dtSaida) {
                        // Criar um novo objeto da classe TransacoesDispositivos (ou a classe correspondente)
                        $objCatraca = new self();
                        $objCatraca->nr_matricula = $idUsuario;
                        $objCatraca->dt_hora_entrada = $dtEntrada;
                        $objCatraca->dt_hora_saida = $dtSaida;

                        // Inserir no banco de dados usando a estrutura de DAO
                        $objCatraca->incluir();
                    }
                }
            }

            return "Importação concluída com sucesso!";

        } catch (Exception $ex) {
            throw new Exception("Erro ao importar: " . $ex->getMessage());
        }
    }


    public function ignorarDiasDesdeDeQueAlunosSairam($tx_grau = "", $nr_serie = "", $matricula = "", $dtsa_da = "")
    {
        $dt_ano_letivo = $this->dt_ano_letivo;
        $tx_turma = $this->tx_turma;
        $dt_filtro_inicio = $this->convertStringToDateTime($this->dt_filtro_inicio);
        $dt_filtro_fim = $this->convertStringToDateTime($this->dt_filtro_fim);
        $dtsa_da = $this->convertStringToDateTime($dtsa_da);
        $dias_desde_de_aluno_sair = 0;

        if ($dtsa_da) {
            $dtsa_da = $this->convertStringToDateTime($dtsa_da);
            $CONDICAO_1 = $dtsa_da > $dt_filtro_inicio;
            $CONDICAO_2 = $dtsa_da <= $dt_filtro_fim;
            $FILTRO_ENGLOBA_SAIDA = $CONDICAO_1 && $CONDICAO_2;

            if ($FILTRO_ENGLOBA_SAIDA) {
                $sabados_domingos_feriados_recessos = count($this->getSabadosDomingosFeriadosRecessos($dtsa_da->format("Y-m-d"), $dt_filtro_fim->format("Y-m-d")));
                #$dias_desde_saida = ($dtsa_da)->diff($dt_filtro_fim)->days + 1;
                $intervalo = $dtsa_da->diff($dt_filtro_fim);
                $dias_desde_saida = $intervalo->format('%a') + 1;
                return $dias_desde_saida - $sabados_domingos_feriados_recessos;
            }
        }

        try {
            $this->queryCorrente = "SELECT a.dtsa_da FROM t_matricula m "
            . "LEFT JOIN Alunos a "
            . "ON m.nr_matricula = a.matricula "
            . "WHERE 1=1 ";

            $this->filtrar("a.dtsa_da", $this->convertDateTimeToString($dt_filtro_inicio), "MAIOR_IGUAL");
            $this->filtrar("a.dtsa_da", $this->convertDateTimeToString($dt_filtro_fim), "MENOR_IGUAL");
            $this->filtrar("m.nr_ano_letivo", $dt_ano_letivo, "IGUAL");

            if ($matricula) {
                $this->filtrar("m.nr_matricula", $matricula, "IGUAL");
            }
            if ($tx_grau) {
                $this->filtrar("m.tx_grau", $tx_grau, "IGUAL");
            }
            if ($nr_serie) {
                $this->filtrar("m.nr_serie", $nr_serie, "IGUAL");
            }
            if ($tx_turma) {
                $this->filtrar("m.tx_turma", $tx_turma, "IGUAL");
            }

            $result = $this->buscar(true);

            if (!$result) {
                return 0;
            }

            foreach ($result as $item) {
                if (!empty($item->dtsa_da)) {
                    $dt_filtro_inicio = $this->convertStringToDateTime($dt_filtro_inicio);
                    $dt_filtro_fim = $this->convertStringToDateTime($dt_filtro_fim);
                    $dtsa_da = $this->convertStringToDateTime($item->dtsa_da);
                    $CONDICAO_1 = $dtsa_da > $dt_filtro_inicio;
                    $CONDICAO_2 = $dtsa_da <= $dt_filtro_fim;
                    $FILTRO_ENGLOBA_SAIDA = $CONDICAO_1 && $CONDICAO_2;

                    if ($FILTRO_ENGLOBA_SAIDA) {
                        $sabados_domingos_feriados_recessos = count($this->getSabadosDomingosFeriadosRecessos($dt_filtro_inicio->format("Y-m-d"), $dtsa_da->format("Y-m-d")));
                        #$dias_desde_saida = ($dt_filtro_inicio)->diff($dtsa_da)->days;
                        $intervalo = $dt_filtro_inicio->diff($dtsa_da);
                        $dias_desde_saida = $intervalo->format('%a');
                        $dias_desde_de_aluno_sair += $dias_desde_saida;
                        $dias_desde_de_aluno_sair -= $sabados_domingos_feriados_recessos;
                    }
                }
            }

            return $dias_desde_de_aluno_sair;

        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }


    public function ignorarDiasAntesDeAlunosEntrarem($tx_grau = "", $nr_serie = "", $matricula = "", $dt_matricula = "")
    {
        $dt_ano_letivo = $this->dt_ano_letivo;
        $dt_filtro_inicio = $this->convertStringToDateTime($this->dt_filtro_inicio);
        $dt_filtro_fim = $this->convertStringToDateTime($this->dt_filtro_fim);
        $tx_turma = $this->tx_turma;
        $dados_ano_letivo = $_SESSION['dados_ano_letivo'];
        $parcial_dias_letivos = $_SESSION['parcial_dias_letivos'];
        $dias_antes_de_aluno_entrar = 0;

        if ($dt_matricula) {
            $dt_matricula = $this->convertStringToDateTime($dt_matricula);
            $CONDICAO_1 = $dt_filtro_fim >= $dt_matricula;
            $CONDICAO_2 = $dt_matricula > $dt_filtro_inicio;
            $FILTRO_ENGLOBA_MATRICULA = $CONDICAO_1 && $CONDICAO_2;

            if ($FILTRO_ENGLOBA_MATRICULA) {
                $sabados_domingos_feriados_recessos = count($this->getSabadosDomingosFeriadosRecessos($dt_filtro_inicio, $dt_matricula));
                #$dias_antes_entrada = ($dt_filtro_inicio)->diff($dt_matricula)->days;
                $intervalo = $dt_filtro_inicio->diff($dt_matricula);
                $dias_antes_entrada = $intervalo->format('%a');
                return $dias_antes_entrada - $sabados_domingos_feriados_recessos;
            }
            return $parcial_dias_letivos;
        }

        try {
            $this->queryCorrente = "SELECT dt_matricula FROM t_matricula as m WHERE 1=1 ";
            $this->filtrar("dt_matricula", $this->convertDateTimeToString($dados_ano_letivo->inicioAulas), "MAIOR_IGUAL");
            $this->filtrar("dt_matricula", $this->convertDateTimeToString($dados_ano_letivo->terminoAulas), "MENOR_IGUAL");
            $this->filtrar("nr_ano_letivo", $dt_ano_letivo, "IGUAL");

            if ($matricula) {
                $this->filtrar("m.nr_matricula", $matricula, "IGUAL");
            }
            if ($tx_grau) {
                $this->filtrar("tx_grau", $tx_grau, "IGUAL");
            }
            if ($nr_serie) {
                $this->filtrar("nr_serie", $nr_serie, "IGUAL");
            }
            if ($tx_turma) {
                $this->filtrar("tx_turma", $tx_turma, "IGUAL");
            }

            $result = $this->buscar(true);

            if (!$result) {
                return 0; // Retorna 0 se não houver alunos
            }

            foreach ($result as $item) {
                if (!empty($item->dt_matricula)) {
                    $dt_filtro_inicio = $this->convertStringToDateTime($dt_filtro_inicio);
                    $dt_filtro_fim = $this->convertStringToDateTime($dt_filtro_fim);
                    $dt_matricula = $this->convertStringToDateTime($item->dt_matricula);
                    $CONDICAO_1 = $dt_filtro_fim >= $dt_matricula;
                    $CONDICAO_2 = $dt_matricula > $dt_filtro_inicio;
                    $FILTRO_ENGLOBA_MATRICULA = $CONDICAO_1 && $CONDICAO_2;

                    if ($FILTRO_ENGLOBA_MATRICULA) {
                        $sabados_domingos_feriados_recessos = count($this->getSabadosDomingosFeriadosRecessos($dt_filtro_inicio, $dt_matricula));
                        #$dias_antes_entrada = ($dt_filtro_inicio)->diff($dt_matricula)->days;
                        $intervalo = $dt_filtro_inicio->diff($dt_matricula);
                        $dias_antes_entrada = $intervalo->format('%a');
                        $dias_antes_de_aluno_entrar += $dias_antes_entrada;
                        $dias_antes_de_aluno_entrar -= $sabados_domingos_feriados_recessos;
                    }

                }
            }

            return $dias_antes_de_aluno_entrar;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * Função auxiliar para calcular os dias antes da entrada
     */


    public function getNumAlunosPorGrauSerie($tx_grau, $nr_serie)
    {
        $dt_ano_letivo = $this->dt_ano_letivo;
        $tx_turma = $this->tx_turma;
        $dt_filtro_inicio = $this->convertStringToDateTime($this->dt_filtro_inicio);
        $dt_filtro_fim = $this->convertStringToDateTime($this->dt_filtro_fim);

        try {
            $queryBase = "SELECT "
                . "COUNT(DISTINCT m.nr_matricula) AS numero_de_alunos "
                . "FROM "
                . "t_matricula AS m "
                . "LEFT JOIN Alunos a "
                . "ON m.nr_matricula = a.matricula "
                . "WHERE 1=1 "
                . "AND a.dtsa_da IS NULL ";
            $this->queryCorrente = $queryBase;

            // Filtros
            $this->filtrar("m.tx_grau", $tx_grau, "IGUAL");
            $this->filtrar("m.nr_serie", $nr_serie, "IGUAL");
            $this->filtrar("m.nr_ano_letivo", $dt_ano_letivo, "IGUAL");
            $this->filtrar("m.tx_aluno_cpf", '..-', "NOT LIKE");
            if ($tx_turma) {
                $this->filtrar("m.tx_turma", $tx_turma, "IGUAL");
            }

            $num_alunos_total = 0;
            $alunos_que_entraram = 0;
            $alunos_que_sairam = 0;

            $num_alunos = $this->buscar(true);

            //ALUNOS QUE ENTRARAM
            $this->queryCorrente = $queryBase;
            $this->filtrar("m.tx_grau", $tx_grau, "IGUAL");
            $this->filtrar("m.nr_serie", $nr_serie, "IGUAL");
            $this->filtrar("m.nr_ano_letivo", $dt_ano_letivo, "IGUAL");
            $this->filtrar("m.tx_aluno_cpf", '..-', "NOT LIKE");
            $this->filtrar("m.dt_matricula", $this->convertDateTimeToString($dt_filtro_fim), "MAIOR");
            $alunos_que_entraram = $this->buscar(true);

            //ALUNOS QUE SAIRAM
            $this->queryCorrente = $queryBase;
            $this->filtrar("m.tx_grau", $tx_grau, "IGUAL");
            $this->filtrar("m.nr_serie", $nr_serie, "IGUAL");
            $this->filtrar("m.nr_ano_letivo", $dt_ano_letivo, "IGUAL");
            $this->filtrar("m.tx_aluno_cpf", '..-', "NOT LIKE");
            $this->filtrar("a.dtsa_da", $this->convertDateTimeToString($dt_filtro_inicio), "MENOR_IGUAL");
            $alunos_que_sairam = $this->buscar(true);

            // Verifica se há resultados e atribui o número de alunos
            if (!empty($num_alunos) && isset($num_alunos[0]->numero_de_alunos)) {
                $num_alunos_total = $num_alunos[0]->numero_de_alunos;
            }
            if (!empty($alunos_que_entraram) && isset($alunos_que_entraram[0]->numero_de_alunos)) {
                $alunos_que_entraram = $alunos_que_entraram[0]->numero_de_alunos;
            }
            if (!empty($alunos_que_sairam) && isset($alunos_que_sairam[0]->numero_de_alunos)) {
                $alunos_que_sairam = $alunos_que_sairam[0]->numero_de_alunos;
            }

            $num_alunos_total -= $alunos_que_entraram;
            $num_alunos_total -= $alunos_que_sairam;

            return $num_alunos_total;

        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getNumPresencasFaltasGeral()
    {
        $grau_series = $this->getGrauSeries();
        $dt_filtro_inicio = $this->dt_filtro_inicio;
        $dt_filtro_fim = $this->dt_filtro_fim;
        $parcial_dias_letivos = $this->getParcialDiasLetivos(true, $dt_filtro_inicio);
        $this->getDadosAnoLetivo(true, $dt_filtro_inicio);
        $this->getSabadosDomingosFeriadosRecessos(true);

        foreach ($grau_series as &$grau_serie) {
            $dias_desde_de_aluno_sair = $this->ignorarDiasDesdeDeQueAlunosSairam($grau_serie['grau'], $grau_serie['serie']);
            $dias_antes_de_aluno_entrar = $this->ignorarDiasAntesDeAlunosEntrarem($grau_serie['grau'], $grau_serie['serie']);

            $num_alunos = $this->getNumAlunosPorGrauSerie($grau_serie['grau'], $grau_serie['serie']);
            $grau_serie['num_alunos'] = $num_alunos;

            $presencas = $this->getPresencasPorTurma($grau_serie['grau'], $grau_serie['serie']);
            $grau_serie['presencas'] = $presencas;

            $faltas = ($num_alunos * $parcial_dias_letivos);
            $faltas = $presencas - $faltas - $dias_desde_de_aluno_sair - $dias_antes_de_aluno_entrar;

            if($faltas < 0){
                $faltas *= -1;
            }

            $grau_serie['faltas'] = $faltas;
        }

        return array('grau_series' => $grau_series, 'parcial_dias_letivos' => $parcial_dias_letivos);
    }

    public function getGrauSeries($somenteDados = FALSE)
    {
        $tx_grau = $this->tx_grau;
        $nr_serie = $this->nr_serie;
        $tx_turma = $this->tx_turma;

        try {
            $aluno = new Aluno();
            $aluno->queryCorrente = "SELECT distinct grau, serie, CONCAT(grau, serie) AS grau_serie "
                . "FROM Alunos as a "
                . "WHERE serie NOT IN('0', '') "
                . "AND NOT (grau = 'M' AND serie = '9') ";

            $aluno->agrupar('a.grau, a.serie');
            $aluno->retornarComoArray = TRUE;

            if ($tx_grau) {
                $aluno->filtrar('grau', $tx_grau);
            }

            if ($nr_serie) {
                $aluno->filtrar('serie', $nr_serie);
            }

            if ($tx_turma) {
                $aluno->filtrar('turma', $tx_turma);
            }

            $result = $aluno->buscar(true);
            return $result;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getNumPresencasFaltasPorGrauSerie($somenteDados = FALSE)
    {
        $dt_filtro_inicio = $this->convertDateTimeToString($this->dt_filtro_inicio);
        $dt_filtro_inicio .= " 00:00:00";

        $dt_filtro_fim = $this->convertDateTimeToString($this->dt_filtro_fim);
        $dt_filtro_fim .= " 23:59:59";

        $dt_ano_letivo = $this->dt_ano_letivo;
        $tx_grau = $this->tx_grau;
        $nr_serie = $this->nr_serie;
        $tx_turma = $this->tx_turma;
        $order_table_by = $this->order_table_by;
        $parcial_dias_letivos = $this->getParcialDiasLetivos(true, $dt_filtro_inicio);

        try {
            // Construção da consulta SQL
            $this->queryCorrente = "SELECT " .
                "COUNT(DISTINCT c.dt_hora_entrada) AS presencas, " .
                "m.tx_grau, " .
                "m.nr_serie, " .
                "m.tx_aluno_nome, " .
                "m.nr_matricula, " .
                "m.tx_turma, " .
                "m.tx_aluno_cpf, " .
                "m.dt_matricula, " .
                "a.dtsa_da as dtsa_da, " .
                "CONCAT(m.tx_grau, m.nr_serie) AS grau_serie, " .
                "CONCAT(m.tx_grau, m.nr_serie, m.tx_turma, m.tx_numero) AS grau_serie_turma " .
                "FROM t_matricula AS m " .
                "LEFT JOIN t_catraca AS c " .
                "ON m.nr_matricula = c.nr_matricula " .
                "AND ( " .
                "  (c.dt_hora_entrada BETWEEN '$dt_filtro_inicio' AND '$dt_filtro_fim' AND (c.dt_hora_saida <= '$dt_filtro_fim' OR c.dt_hora_saida IS NULL)) " .
                ") " .
                "LEFT JOIN Alunos AS a " .
                "ON m.nr_matricula = a.matricula " .
                "WHERE 1=1 " .
                "AND a.dtamatr IS NOT NULL " .
                "AND a.dtsa_da IS NULL ";

            $this->filtrar("m.nr_ano_letivo", $dt_ano_letivo, "IGUAL");
            $this->filtrar("m.tx_aluno_cpf", '..-', "NOT LIKE");
            $this->filtrar("m.dt_matricula", $this->convertDateTimeToString($dt_filtro_fim), "MENOR_IGUAL");

            if ($tx_grau) {
                $this->filtrar("m.tx_grau", $tx_grau, "IGUAL");
            }
            if ($nr_serie) {
                $this->filtrar("m.nr_serie", $nr_serie, "IGUAL");
            }
            if ($tx_turma) {
                $this->filtrar("m.tx_turma", $tx_turma, "IGUAL");
            }
            if ($order_table_by == 'faltas') {
                $this->ordenar("COUNT(DISTINCT c.id_catraca)", "desc");
            }


            $this->agrupar("m.tx_aluno_nome, m.nr_matricula, m.tx_turma, m.tx_aluno_cpf, m.tx_grau, m.nr_serie");

            // Executa a consulta
            $result = $this->buscar($somenteDados);

            foreach ($result['dados'] as &$dado) {
                $dado->faltas = $parcial_dias_letivos - $dado->presencas;
                $dt_matricula = $this->convertStringToDateTime($dado->dt_matricula);
                $dt_filtro_inicio = $this->convertStringToDateTime($dt_filtro_inicio);
                $dt_filtro_fim = $this->convertStringToDateTime($dt_filtro_fim);
                $CONDICAO_1 = $dt_filtro_fim >= $dt_matricula;
                $CONDICAO_2 = $dt_matricula > $dt_filtro_inicio;
                $FILTRO_ENGLOBA_MATRICULA = $CONDICAO_1 && $CONDICAO_2;

                if ($FILTRO_ENGLOBA_MATRICULA) {
                    $dias_antes_de_aluno_entrar = $this->ignorarDiasAntesDeAlunosEntrarem($dado->dt_matricula);
                    $dado->faltas -= $dias_antes_de_aluno_entrar;
                }

                $dtsa_da = $this->convertStringToDateTime($dado->dtsa_da);
                
                $CONDICAO_1 = $dtsa_da > $dt_filtro_inicio;
                $CONDICAO_2 = $dtsa_da <= $dt_filtro_fim;
                $FILTRO_ENGLOBA_SAIDA = $CONDICAO_1 && $CONDICAO_2;

                if ($dado->nr_matricula == 24551) {
                    $nr_matricula = $dado->nr_matricula;
                }

                if ($FILTRO_ENGLOBA_SAIDA) {
                    $dias_desde_de_aluno_sair = $this->ignorarDiasDesdeDeQueAlunosSairam($dado->dtsa_da);
                    $dado->faltas -= $dias_desde_de_aluno_sair;
                }
            }

            /* remover quem não faz parte da massa de dados do gráfico */
            if ($order_table_by === 'faltas' || $order_table_by === 'presencas') {
                usort($result['dados'], function ($a, $b) use ($order_table_by) {
                    if ($a->$order_table_by == $b->$order_table_by) {
                        return 0;
                    }
                    return ($a->$order_table_by < $b->$order_table_by) ? 1 : -1; // Ordem decrescente
                });
            }

            if ($order_table_by === 'faltas') {
                #$result['dados'] = array_filter($result['dados'], fn($dado) => $dado->faltas > 0);
                $result['dados'] = array_filter($result['dados'], function($dado) {
                    return $dado->faltas > 0;
                });
                
            }

            if ($order_table_by === 'presencas') {
                #$result['dados'] = array_filter($result['dados'], fn($dado) => $dado->presencas > 0);
                $result['dados'] = array_filter($result['dados'], function($dado) {
                    return $dado->presencas > 0;
                });
            }

            if ($order_table_by === 'faltas' || $order_table_by === 'presencas') {
                /*usort($result['dados'], function ($a, $b) use ($order_table_by) {
                    return $b->$order_table_by <=> $a->$order_table_by; // Ordena em ordem decrescente
                });*/
                usort($result['dados'], function ($a, $b) use ($order_table_by) {
                    if ($a->$order_table_by == $b->$order_table_by) {
                        return 0;
                    }
                    return ($a->$order_table_by < $b->$order_table_by) ? 1 : -1; // Ordem decrescente
                });
            }

            return $result;

        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function removerDuplicadosPorDtHoraEntrada($lista) {
        $vistas = array();
        $resultado = array();

        foreach ($lista as $item) {
            if (!in_array($item->dt_hora_entrada, $vistas)) {
                $vistas[] = $item->dt_hora_entrada;
                $resultado[] = $item;
            }
        }

        return $resultado;
    }

    public function preencherLacunasDeDias($lista, $dt_inicio, $dt_fim) {
        $dias_existentes = array();

        // Mapear os dias que já existem na lista
        foreach ($lista as $item) {
            $dataObj = new DateTime($item->dt_hora_entrada);
            $data = $dataObj->format('d/m/Y');
            $dias_existentes[$data] = $item;
        }

        // Preparar intervalo de datas
        $inicio = clone $dt_inicio;
        $fim = clone $dt_fim;
        $fim->modify('+1 day'); // inclui o último dia

        $intervalo = new DateInterval('P1D');
        $periodo = new DatePeriod($inicio, $intervalo, $fim);

        $resultado = array();

        foreach ($periodo as $data) {
            $data_str = $data->format('d/m/Y');
            $data_iso = $data->format('Y-m-d');

            if (isset($dias_existentes[$data_str])) {
                $resultado[] = $dias_existentes[$data_str];
            } else {
                $objeto_lacuna = new stdClass();
                $objeto_lacuna->dt_entrada = $data_str . ' 00:00:00';
                $objeto_lacuna->dt_saida = $data_str . ' 00:00:00';
                $objeto_lacuna->horario_entrada = '00:00';
                $objeto_lacuna->horario_saida = '00:00';
                $objeto_lacuna->falta = true;
                $objeto_lacuna->data_formatada = $data_iso . ' 00:00:00';

                $resultado[] = $objeto_lacuna;
            }
        }

        // Remove o último dia do resultado
        array_pop($resultado);

        return $resultado;
    }

    protected $datas_proibidas_temp = array();

    public function filtrarDatas($datas, $sabados_domingos_feriados_recessos) {
        $this->datas_proibidas_temp = array();

        foreach ($sabados_domingos_feriados_recessos as $evento) {
            if (!empty($evento['start'])) {
                $this->datas_proibidas_temp[] = date('Y-m-d', strtotime($evento['start']));
            }
            if (!empty($evento['end'])) {
                $this->datas_proibidas_temp[] = date('Y-m-d', strtotime($evento['end']));
            }
        }

        // Remove duplicadas
        $this->datas_proibidas_temp = array_unique($this->datas_proibidas_temp);

        // Filtra manualmente
        $resultado = array();
        foreach ($datas as $item) {
            $data_item = date('Y-m-d', strtotime($item->data_formatada));
            if (!in_array($data_item, $this->datas_proibidas_temp)) {
                $resultado[] = $item;
            }
        }

        return array_values($resultado); // reindexa os índices
    }

    public function getNumPresencasFaltasPorAluno($somenteDados = FALSE)
    {
        $dt_ano_letivo = $this->dt_ano_letivo;

        $dt_filtro_inicio = $this->convertDateTimeToString($this->dt_filtro_inicio);
        $dt_filtro_inicio .= " 00:00:00";

        $dt_filtro_fim = $this->convertDateTimeToString($this->dt_filtro_fim);
        $dt_filtro_fim .= " 23:59:59";

        $nr_matricula = $this->nr_matricula;

        $parcial_dias_letivos = $this->getParcialDiasLetivos(true, $dt_filtro_inicio);

        try {
            // Construindo a query
            $this->queryCorrente = "SELECT " .
                "c.dt_hora_entrada, " .
                "c.dt_hora_saida, " .
                "a.dtsa_da " .
                "FROM t_catraca AS c " .
                "LEFT JOIN t_matricula AS m ". 
                "ON c.nr_matricula = m.nr_matricula " .
                "LEFT JOIN Alunos AS a " .
                "ON m.nr_matricula = a.matricula " .
                "WHERE c.nr_matricula = '$nr_matricula' " .
                "AND a.dtamatr IS NOT NULL " .
                "AND a.dtsa_da IS NULL " .
                "AND ( " .
                "(c.dt_hora_entrada BETWEEN '$dt_filtro_inicio' AND '$dt_filtro_fim' AND c.dt_hora_saida <= '$dt_filtro_fim') " .
                "OR " .
                "(c.dt_hora_entrada BETWEEN '$dt_filtro_inicio' AND '$dt_filtro_fim' AND c.dt_hora_saida IS NULL) ) " .
                "AND m.nr_ano_letivo = '$dt_ano_letivo' ";

            $result = $this->buscar($somenteDados);
            $dt_filtro_inicio = $this->convertStringToDateTime($dt_filtro_inicio);
            $dt_filtro_fim = $this->convertStringToDateTime($dt_filtro_fim);
            $alunos = new Aluno();
            $alunos->matricula = $nr_matricula;
            #$aluno = $alunos->getAlunos()[0];
            $_alunos = $alunos->getAlunos();
            $aluno = isset($_alunos[0]) ? $_alunos[0] : null;

            $aluno->photo = $aluno->obterFoto();
            $result['aluno'] = $aluno;

            $presencas = count($result['dados']);
            if($presencas > 2){
                $presencas -= 1;
            }
            $result['parcial_dias_letivos'] = $parcial_dias_letivos;

            // Tratamento da data de matrícula e exclusão
            if (!$this->dt_matricula || $this->dtsa_da) {
                $this->queryCorrente = "SELECT m.dt_matricula, m.dtsa_da 
                                        FROM t_matricula AS m 
                                        WHERE m.nr_matricula = '$nr_matricula' 
                                          AND m.nr_ano_letivo = '$dt_ano_letivo'";

                $matricula_exclusao = $this->buscar(true);
                $matricula_exclusao = $matricula_exclusao[0];

                $dt_matricula = $this->convertStringToDateTime($matricula_exclusao->dt_matricula);
                $dtsa_da = $this->convertStringToDateTime($matricula_exclusao->dtsa_da);
            }

            $result['faltas'] = max(0, $parcial_dias_letivos - $presencas);

            foreach ($result['dados'] as &$presence) {
                $dt_hora_entrada = $this->convertStringToDateTime($presence->dt_hora_entrada);
                $presence->dt_entrada = $dt_hora_entrada->format('d/m/Y');
                $presence->horario_entrada = $dt_hora_entrada->format('H:i');

                if (!empty($presence->dt_hora_saida)) {
                    $dt_hora_saida = $this->convertStringToDateTime($presence->dt_hora_saida);
                    $presence->horario_saida = $dt_hora_saida->format('H:i');
                } else {
                    $presence->horario_saida = null;
                }
            }

            // Ordenação por data de entrada
            /*usort($result['dados'], fn($a, $b) => strtotime($b->dt_hora_entrada) - strtotime($a->dt_hora_entrada));*/

            usort($result['dados'], function($a, $b) {
                return strtotime($b->dt_hora_entrada) - strtotime($a->dt_hora_entrada);
            });
            
            $dados = $this->removerDuplicadosPorDtHoraEntrada($result['dados']);
            $dt_filtro_inicio = $dt_filtro_inicio;
            $dt_filtro_fim = $dt_filtro_fim;

            $datas = $this->preencherLacunasDeDias($dados, $dt_filtro_inicio, $dt_filtro_fim);
            $sabados_domingos_feriados_recessos = $this->getSabadosDomingosFeriadosRecessos(false, 
            $dt_filtro_inicio, $dt_filtro_fim);
            $datas_filtradas = $this->filtrarDatas($datas, $sabados_domingos_feriados_recessos);
            $result['dados'] = $datas_filtradas;

            return $result;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }



    public static function getLocais()
    {
        asort(self::$_locais_catraca);
        return self::$_locais_catraca;
    }

    public static function getTipos()
    {
        //aplicacao de ordenacao pelo valor mantendo a relacao entre chaves e valores
        asort(self::$_tipos_catraca);
        $_tipos_catraca = self::$_tipos_catraca;
        //para ficar por ultimo
        $_tipos_catraca['OUTROS'] = 'Outros';
        return $_tipos_catraca;
    }

    public static function getTiposCrachas()
    {
        //aplicacao de ordenacao pelos valores
        asort(self::$_tipos_crachas);
        $_tipos = self::$_tipos_crachas;
        return $_tipos;
    }

    public function incluir()
    {
        try {
            $this->moverImagem();
            return $this->incluir();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function salvar($tipoLog = 'ALTERAR', $msgSuccess = 'Dados gravados com sucesso.')
    {
        try {
            $this->moverImagem();
            return $this->salvar($tipoLog, $msgSuccess);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function uploadImagePOST()
    {
        try {
            $img = $_POST['imgBase64'];
            $img = str_replace('data:image/png;base64', '', $img);
            $img = str_replace(' ', '+', $img);

            $fileData = base64_decode($img);
            $filename = self::DIR_TMP_FOTOS . date('U') . '.png';

            file_put_contents(self::DIR_RELATIVO . $filename, $fileData);
            $_result = Mensagem::sucesso('Arquivo gerado com sucesso.');
            $_result['filename'] = $filename;
            return $_result;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    private function obterProximoID()
    {
        $query = "SELECT MAX(id_catraca) AS max_id FROM t_catraca ";
        $_result = Dao::select($query, array());
        return $_result[0]['max_id'] + 1;
    }

    private function getIdCatracaOuProximoId()
    {
        if (!empty($this->id_catraca)) {
            return $this->id_catraca;
        }
        return $this->obterProximoID();
    }

    private function moverImagem()
    {

        if (empty($this->tx_imagesource) or !file_exists(self::DIR_RELATIVO . $this->tx_imagesource)) {
            return;
        }
        $_pathinfo = pathinfo(self::DIR_RELATIVO . $this->tx_imagesource);
        $origem = self::DIR_RELATIVO . $this->tx_imagesource;
        $destino = self::DIR_RELATIVO . self::DIR_FOTOS . $this->getIdCatracaOuProximoId() . '.' . $_pathinfo['extension'];
        if ($origem == $destino) {
            return;
        }
        $dirFotoCPF = self::DIR_RELATIVO . self::DIR_FOTOS_POR_CPF . removerMascara($this->tx_cpf_atendido) . '.' . $_pathinfo['extension'];
        try {
            if (!copy($origem, $destino)) {
                throw new Exception('Não foi possível realizar a cópia da imagem.');
            }
            copy($origem, $dirFotoCPF);
            $_pathinfoDestino = pathinfo($destino);
            $this->tx_imagesource = self::DIR_FOTOS . $_pathinfoDestino['basename'];
            $this->limparDirImgTemp();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    private function limparDirImgTemp()
    {
        $dir = dir(self::DIR_RELATIVO . self::DIR_TMP_FOTOS);

        while (($arquivo = $dir->read()) !== false) {
            if ($arquivo == '.' or $arquivo == '..') {
                continue;
            }
            $filesource = self::DIR_RELATIVO . self::DIR_TMP_FOTOS . $arquivo;
            $extensao = pathinfo($filesource, PATHINFO_EXTENSION);
            if ($extensao === 'png') {
                unlink($filesource);
            }
        }
    }

    public function pesquisarImagem()
    {
        $filename = self::DIR_RELATIVO . $this->tx_imagesource;
        if (file_exists($filename)) {
            return Mensagem::sucesso('Imagem encontrada.');
        }
        return Mensagem::informacao('Imagem não encontrada.');
    }
}
