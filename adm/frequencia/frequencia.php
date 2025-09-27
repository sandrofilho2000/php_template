<?php
if (!isset($user)) {
    header('Location:index.php');
    exit();
}

Controller::setFileJavascript('frequencia/js/moment.js');
Controller::setFileJavascript('frequencia/js/datetime-moment.js');
Controller::setFileJavascript('frequencia/js/aluno_popup.js');
Controller::setFileJavascript('frequencia/js/frequencia.js');
Controller::setFileJavascript('../js/_funcoes_cpf.js');

$_series = Serie::buscar();
$_turmasTemp = Aluno::getTurmas();
$turmasUnicas = array();

foreach ($_turmasTemp as $turma) {
    $turmasUnicas[$turma['turma']] = $turma;
}

$dt_filtro = new DateTime();

$dt_filtro_ano = $dt_filtro->format("Y");
$dt_filtro_mes = $dt_filtro->format("m");
$dt_filtro_dia = $dt_filtro->format("d");

$dt_filtro_ano_passado = clone $dt_filtro;
$dt_filtro_ano_passado->modify("-1 year");


$dt_filtro_formatado = "$dt_filtro_ano-$dt_filtro_mes-$dt_filtro_dia";
$ano_passado_formatado = $dt_filtro_ano_passado->format("Y-m-d");


$dados_ano_letivo = DiarioControle::getDadosAnoLetivo($dt_filtro_formatado)
    ?: DiarioControle::getDadosAnoLetivo($ano_passado_formatado);

$ano_letivo = $dados_ano_letivo->anoLetivo;
$dt_filtro_minimo = $dados_ano_letivo ? $dados_ano_letivo->inicioAulas : "$dt_filtro_ano-01-01";
$dt_filtro_maximo = $dados_ano_letivo ? $dados_ano_letivo->terminoAulas : "$dt_filtro_ano-12-31";

$dt_filtro = $ano_letivo == $dt_filtro_ano ? "$dt_filtro_ano-$dt_filtro_mes-$dt_filtro_dia" : $dt_filtro_maximo;
$dt_filtro_ano = $ano_letivo;

?>
<style>
    html body.loading .overlay{
        z-index: 1100;
    }
</style>
<form id="frmPesquisa" method="post">
    <input type="hidden" name="acao" value="execute" />
    <input type="hidden" name="metodo" value="getNumPresencasFaltasGeral" />
    <input type="hidden" name="objeto" value="Catraca" />
    <input type="hidden" name="nr_serie" id="nr_serie" value="" />
    <input type="hidden" name="tx_grau" id="tx_grau" value="" />
    <input type="hidden" name="order_table_by" id="order_table_by" value="" />
    <input type="hidden" name="tx_matricula_atendido" id="tx_matricula_atendido" value="">

    <div class="form-group m-b-5">
        <label for="" class="m-b-1">
            Ano letivo
        </label>
        <input type="number" name="dt_ano_letivo" id="dt_ano_letivo" max="<?php echo $dt_filtro_ano; ?>"
            class="form-control form-control-sm row-sm-2" placeholder="Ano letivo" required=""
            value="<?php echo $dt_filtro_ano; ?>" />
    </div>

    <div class="form-group row m-b-5">
        <div class="col">
            <label for="" class="m-b-1">
                Início
            </label>
            <input type="date" required class="form-control form-control-sm row-sm-2" name="dt_filtro_inicio"
                id="dt_filtro_inicio" min="<?php echo $dt_filtro_minimo ?>" max="<?php echo $dt_filtro_maximo; ?>"
                value="<?php echo $dt_filtro; ?>">
        </div>

        <div class="col">
            <label for="" class="m-b-1">
                Fim
            </label>
            <input type="date" required class="form-control form-control-sm row-sm-2" name="dt_filtro_fim"
                id="dt_filtro_fim" min="<?php echo $dt_filtro_minimo ?>" max="<?php echo $dt_filtro_maximo; ?>"
                value="<?php echo $dt_filtro; ?>">
        </div>
    </div>
    <div class="form-group m-b-5">
        <label for="" class="m-b-1">
            Série
        </label>
        <select name="grau_serie" id="grau_serie" class="form-control form-control-sm mb-2" id="grau_serie">
            <option selected="true" value="">Todas</option>
            <?php foreach ($_series as $serie): ?>
                <option value="<?php
                echo htmlspecialchars(json_encode($serie), ENT_QUOTES, 'UTF-8');
                ?>">
                    <?php echo $serie->curso ?> (<?php echo $serie->ds_grau ?> - <?php echo $serie->ds_serie ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group m-b-5">
        <label for="" class="m-b-1">
            Turma
        </label>
        <select name="tx_turma" class="form-control form-control-sm mb-2" id="tx_turma">
            <option selected="true" value="">Turma (Todas)</option>
            <?php foreach ($turmasUnicas as $turma): ?>
                <option value="<?php echo $turma['turma']; ?>">
                    <?php echo $turma['turma']; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>



    <div class="btns_wrapper form-group m-b-5">
        <input type="submit" class="btn btn-primary btn-sm" id="clearForm" value="Pesquisar">
        <input type="reset" class="btn btn-outline-danger btn-sm" id="clearForm" value="Limpar Formulário">

        <button id="downloadChart" class="btn btn-warning btn-sm d-inline-flex align-items-center">
            <span class="d-inline-flex">
                <svg stroke="currentColor" fill="currentColor" stroke-width="0" version="1.1" viewBox="0 0 16 16"
                    height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 1h16v2h-16zM0 4h10v2h-10zM0 10h10v2h-10zM0 7h16v2h-16zM0 13h16v2h-16z"></path>
                </svg>
            </span>
            <span class="d-inline-block ms-4">
                Baixar Gráfico
            </span>
        </button>
        <button id="downloadTable" disabled class="btn btn-success btn-sm d-inline-flex align-items-center">
            <span class="d-inline-flex ">
                <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 384 512" height="1em"
                    width="1em" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M224 136V0H24C10.7 0 0 10.7 0 24v464c0 13.3 10.7 24 24 24h336c13.3 0 24-10.7 24-24V160H248c-13.2 0-24-10.8-24-24zm60.1 106.5L224 336l60.1 93.5c5.1 8-.6 18.5-10.1 18.5h-34.9c-4.4 0-8.5-2.4-10.6-6.3C208.9 405.5 192 373 192 373c-6.4 14.8-10 20-36.6 68.8-2.1 3.9-6.1 6.3-10.5 6.3H110c-9.5 0-15.2-10.5-10.1-18.5l60.3-93.5-60.3-93.5c-5.2-8 .6-18.5 10.1-18.5h34.8c4.4 0 8.5 2.4 10.6 6.3 26.1 48.8 20 33.6 36.6 68.5 0 0 6.1-11.7 36.6-68.5 2.1-3.9 6.2-6.3 10.6-6.3H274c9.5-.1 15.2 10.4 10.1 18.4zM384 121.9v6.1H256V0h6.1c6.4 0 12.5 2.5 17 7l97.9 98c4.5 4.5 7 10.6 7 16.9z">
                    </path>
                </svg>
            </span>
            <span class="d-inline-block ms-4">
                Baixar Tabela
            </span>
        </button>
        <!-- <button id="importData" class="btn btn-info btn-sm d-inline-flex align-items-center">
            <span class="d-inline-block ms-4">
                Importar dados
            </span>
        </button> -->
    </div>
</form>

<div class="graph-grau-serie-container my-3 overflow-hidden" style="width: 100%; height: 100%">
    <div id="barchart-grau-serie-values" style="width: 100%; height: 100%;"></div>
</div>

<div class="modal fade" tabindex="-1" id="modalAlunoDetalhado">
    <form id="frmAluno" method="post">
        <input type="hidden" name="dt_matricula" id="dt_matricula">
        <input type="hidden" name="dtsa_da" id="dtsa_da">
        <input type="hidden" name="nr_matricula" id="nr_matricula">
    </form>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Dados de <small id="small_dt_filtro_inicio">22/05/2000</small> até <small
                        id="small_dt_filtro_fim">26/05/2000</small> </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body d-flex flex-column gap-1">
                <div class="modal-body-top w-100 d-flex mb-2" style="padding-right: 10px;">
                    <img src="../img/aluno_sem_foto.png" data-default="../img/aluno_sem_foto.png" width="150px"
                        id="img-cadastro" />


                    <div class="d-flex flex-column ml-2">
                        <div class="aluno-info">
                            <table style="width: 100%">
                                <tr>
                                    <td>
                                        Nome: <span id="tx_aluno_nome"></span>
                                    </td>
                                </tr>
                                <tr>     
                                    <td>
                                        Matricula: <span id="nr_matricula"></span>
                                    </td>
                                </tr>
                                <tr>     
                                    <td>
                                        GSTN: <span id="tx_grau_serie"></span>
                                    </td>
                                </tr>
                                <tr>     
                                    <td>
                                        CPF: <span id="tx_cpf_atendido"></span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="aluno-graph">
                            <div class="graph-aluno-container my-3 overflow-hidden" style="width: 100%; height: 100%">
                                <div id="barchart-aluno-values" style="width: 100%; height: 100%;"></div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-body-bottom d-flex flex-column w-100">
                    <div class="aluno-lista-de-presenca">
                        <fieldset id="fieldAlunoPresencas">
                            <table class="table table-sm table-striped table-bordered w-100" id="alunoPresencas">
                                <thead>
                                    <th>
                                        Data
                                    </th>
                                    <th>
                                        Hora Entrada
                                    </th>
                                    <th>
                                        Hora Saída
                                    </th>
                                </thead>
                            </table>
                        </fieldset>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<fieldset id="fieldsetLista">
    <table style="display: none" id="tbLista" class="table table-sm table-striped table-bordered">
        <thead>
            <tr>
                <th></th>
                <th class="min-phone-l">Aluno</th>
                <th>Matrícula</th>
                <th>CPF</th>
                <th>GSTN</th>
                <th class="th-presencas">Presenças</th>
                <th class="th-faltas">Faltas</th>
                <th class="btn-select">
                    Selecionar
                </th>
            </tr>
        </thead>
    </table>
</fieldset>