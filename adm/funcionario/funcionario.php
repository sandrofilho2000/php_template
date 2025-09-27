<?php

if (!isset($user) or !$user->isPossuiPermissaoAcessarPagina('funcionario/funcionario.php')) {
    header('Location:' . Controller::getUrlHomePage());
    exit();
}

Controller::setFileJavascript('../js/jquery.slimscroll.min.js');
Controller::setFileJavascript('../js/_funcoes_cpf.js');
Controller::setFileJavascript('funcionario/js/funcionario.js');

$_etnias = getEtnias();


?>
<style>
    .funcionario_image_label {
        position: relative;
        display: block;
        margin-bottom: 5px !important;
        cursor: pointer;
        width: 160px;
    }


    #funcionario_image {
        max-width: 160px;
        object-fit: contain;
    }

    .funcionario_image_label:hover::after {
        opacity: 1;
        transition: 0.5s;
    }

    .funcionario_image_label::after {
        content: "Clique para alterar";
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        opacity: 0;
        background: #ccc;
        top: 0;
        left: 0;
        position: absolute;
    }

    .bts_wrapper {
        width: 160px;
        display: flex;
        justify-content: space-between;
    }

    @media (min-width: 768px) {
        .{
            margin-left: -15px !important;
        }
    }
</style>
<div id="containerPesquisar">
    <form id="frmPesquisar" onsubmit="return pesquisar()" class="form-horizontal">
        <input type="hidden" name="objeto" value="Funcionario" />
        <input type="hidden" name="acao" value="execute" />
        <input type="hidden" name="metodo" value="pesquisar" />
        <div class="row">
            <label class="col-form-label control-label col-sm-1 text-right pr-0">Nome <span
                    class="text-danger">*</span></label>
            <div class="col-sm-2">
                <input type="text" name="filtros[LIKE][nome]" class="form-control form-control-sm resetavel bg-silver-lighter" />
            </div>
            <label class="col-form-label control-label col-sm-1 text-right pr-0">Saiu</label>
            <div class="col-sm-2">
                <select name="filtros[EXPLICITO][EXPLICITO]" class="form-control form-control-sm">
                    <option value="1=1">Todos</option>
                    <option value="ISNULL(dtsaida)" selected>Não</option>
                    <option value="NOT ISNULL(dtsaida)">Sim</option>
                </select>
            </div>
            <div class="col-sm-2">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search mr-1"></i> Pesquisar
                </button>
                <img src="../img/ajaxload-05.gif" class="loading collapse" />
            </div>
        </div>
    </form>
    <hr style="margin-top:25px" />
    <table id="tbFuncionarios" class="table table-sm table-bordered table-striped" style="width:100%">
        <thead>
            <tr>
                <th style="width:70px">Matrícula</th>
                <th nowrap>Foto</th>
                <th nowrap>Funcionario</th>
                <th nowrap>Função</th>
                <th>Telefone</th>
                <th>Celular</th>
                <th>Email</th>
            </tr>
        </thead>
    </table>
</div>
<div id="containerFicha" class="collapse">

        <div class="tab-content" id="myTabContent"
            style="background-color:#fff; border-left: 1px solid #dee2e6; border-right: 1px solid #dee2e6;  border-bottom: 1px solid #dee2e6; padding: 20px">
            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                <form enctype="multipart/form-data" id="frmFuncionario" onsubmit="return gravar()"
                    class="form-horizontal">
                    <input type="hidden" name="objeto" value="Funcionario" />
                    <input type="hidden" name="acao" value="" />
                    <input type="hidden" name="id_objeto" value="" data-pkName="codigo" />
                    <input type="hidden" name="tx_img_delete" value="" />
                    <div
                        style="height:500px; overflow-y:scroll; overflow-x:hidden; padding-right:10px; margin-bottom:20px">
                       
                        <!-- DADOS GERAIS -->
                        <fieldset style="padding:5px; padding-bottom:20px; border:1px solid #DFDFDF; margin:20px; margin-top:0; min-height:400px !important">
                            <legend class="pl-2 pr-2 f-s-20 f-s-20 f-w-600">FUNCIONÁRIO(A): <span id="nome_funcionario" class="text-primary">DADOS DO FUNCIONÁRIO</span></legend>
                            <div class="d-block d-md-flex flex-md-row p-md-0">
                                <div class="p-20 p-md-0" style="margin-bottom: 20px;padding-right: 20px;flex-direction: column;display: flex;align-items: center;">
                                    <label class="funcionario_image_label">
                                        <input class="d-none" type="file" name="tx_image" id="tx_image">
                                        <img src="../img/funcionarios/funcionario_sem_foto.png"
                                            id="funcionario_image" alt="foto" width="160px"
                                            data-default="../img/funcionarios/funcionario_sem_foto.png" id="foto"
                                            style="width:100%;" />
                                    </label>
                                    <div class="bts_wrapper flex-column">

                                        <button id="open_foto_modal" class="btn btn-sm btn-primary   mb-1">
                                            Nova foto
                                        </button>

                                        <button id="remove_foto" class="btn btn-sm btn-danger  mb-1">
                                            Excluir foto
                                        </button>

                                        <input type="text" name="matricula" placeholder="Matrícula" class="form-control form-control-sm m-b-3 text-center f-w-600 w-100 f-s-16" readonly="" style="width:160px !important">

                                        <p class="m-b-1 f-w-600 w-100 f-s-10 text-danger" style="letter-spacing:1px; margin-left:30px; margin-right:30px; width:160px !important">DATA DE SAÍDA</p>

                                        <input name="data_saida" id="data_saida" type="date" class="form-control form-control-sm text-center f-w-600 w-100 f-s-12 text-danger" style="width:160px !important">

                                    </div>
                                </div>
                                <div class="col p-0">
                                    <div class="row m-0 m-b-5 col-md-12 align-items-center">
                                        <label class="col-form-label col-form-label-sm text-left text-lg-right">Nome</label>
                                        <div class="col-12 p-0">
                                            <input type="text" name="nome" class="form-control form-control-sm resetavel bg-silver-lighter" maxlength="45" />
                                        </div>
                                    </div>    

                                    <div class="row m-0 m-b-5">
                                        <div class="col-md-6 align-items-center">
                                            <label class="col-form-label col-form-label-sm text-left text-lg-right">Sexo</label>
                                            <div class="col-12 p-0">
                                                <select name="sexo" class="form-control form-control-sm resetavel bg-silver-lighter" required>
                                                <option value="M">Masculino</option>
                                                <option value="F">Feminino</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- CPF -->
                                        <div class="col-md-6 align-items-center">
                                            <label class="col-form-label col-form-label-sm text-left text-lg-right">
                                                CPF<span class="text-danger m-l-5">*</span>
                                            </label>
                                            <div class="col-12 p-0">
                                                <input type="text" name="cpf" required class="form-control form-control-sm resetavel mask-cpf bg-silver-lighter" maxlength="14" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row m-0 m-b-5">
                                        <div class="col-md-6 align-items-center">
                                            <label class="col-form-label col-form-label-sm text-left text-lg-right">Telefone</label>
                                            <div class="col-12 p-0">
                                                <input type="text" name="telefone" class="form-control bg-silver-lighter form-control-sm resetavel mask-telefone" maxlength="14" />
                                            </div>
                                        </div>
                                        <div class="col-md-6 align-items-center">
                                            <label class="col-form-label col-form-label-sm text-left text-lg-right">Celular<span class="text-danger m-l-5">*</span></label>
                                            <div class="col-12 p-0">
                                                <input type="text" name="celular" required class="form-control form-control-sm resetavel mask-celular bg-silver-lighter" maxlength="15" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row m-0 m-b-5">
                                    <div class="col-md-6 align-items-center">                                        
                                        <label class="col-form-label col-form-label-sm text-left text-lg-right">Estado civíl</label>
                                            <div class="col-12 p-0">
                                                <select name="estadoCivil" id="estadoCivil" class="form-control form-control-sm resetavel bg-silver-lighter">
                                                    <option value="">Selecione...</option>
                                                    <option value="solteiro">Solteiro(a)</option>
                                                    <option value="casado">Casado(a)</option>
                                                    <option value="separado">Separado(a)</option>
                                                    <option value="divorciado">Divorciado(a)</option>
                                                    <option value="viuvo">Viúvo(a)</option>
                                                    <option value="uniao_estavel">União Estável</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 align-items-center">
                                            <label class="col-form-label col-form-label-sm text-left text-lg-right">Cônjuge</label>
                                            <div class="col-12 p-0">
                                                <input type="text" name="conjuge" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row m-0 m-b-5">
                                        <div class="col-md-12  align-items-center">
                                            <label class="col-form-label col-form-label-sm text-left text-lg-right">Mãe</label>
                                            <div class="col-12 p-0">
                                                <input type="text" name="mae" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row m-0 m-b-5">
                                        <div class="col-md-6 align-items-center">
                                            <label class="col-form-label col-form-label-sm text-left text-lg-right">Nascimento<span class="text-danger m-l-5">*</span></label>
                                            <div class="col-12 p-0">
                                                <input type="date" name="nascimento" required class="form-control form-control-sm resetavel bg-silver-lighter" maxlength="10" />
                                            </div>
                                        </div>
                                        <div class="col-md-6 align-items-center">
                                            <label class="col-form-label col-form-label-sm text-left text-lg-right">Admissão<span class="text-danger m-l-5">*</span></label>
                                            <div class="col-12 p-0">
                                                <input type="date" name="admissao" required class="form-control form-control-sm resetavel bg-silver-lighter" maxlength="10" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row m-0 m-b-5">
                                        <div class="col-md-6 align-items-center">
                                            <label class="col-form-label col-form-label-sm text-left text-lg-right">Cor/Raça</label>
                                            <div class="col-12 p-0">
                                                <select name="cor" class="form-control form-control-sm resetavel bg-silver-lighter">
                                                    <option value="">Selecione</option>
                                                    <?php foreach($_etnias as $chave => $valor): ?>
                                                        <option value="<?= $chave ?>">
                                                            <?= $valor ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div> 
                                        <div class="col-md-6 align-items-center">
                                            <label class="col-form-label col-form-label-sm text-left text-lg-right">Nacionalidade</label>
                                            <div class="col-12 p-0">
                                                <input type="text" name="nacionalidade" list="nacionalidades" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                                <datalist id="nacionalidades">
                                                    <!-- América do Sul -->
                                                    <option value="Argentina">
                                                    <option value="Boliviana">
                                                    <option value="Brasileira">
                                                    <option value="Chilena">
                                                    <option value="Colombiana">
                                                    <option value="Equatoriana">
                                                    <option value="Guianense">
                                                    <option value="Paraguaia">
                                                    <option value="Peruana">
                                                    <option value="Surinamesa">
                                                    <option value="Uruguaia">
                                                    <option value="Venezuelana">

                                                    <!-- América Central -->
                                                    <option value="Antiguana">
                                                    <option value="Barbadiana">
                                                    <option value="Belizenha">
                                                    <option value="Costarriquenha">
                                                    <option value="Cubana">
                                                    <option value="Dominicana">
                                                    <option value="Granadina">
                                                    <option value="Guatemalteca">
                                                    <option value="Hondurenha">
                                                    <option value="Jamaicana">
                                                    <option value="Mexicana">
                                                    <option value="Nicaraguense">
                                                    <option value="Panamenha">
                                                    <option value="Portorriquenha">
                                                    <option value="Salvadorenha">
                                                    <option value="São-cristovense">
                                                    <option value="São-vicentina">
                                                    <option value="Santa-lucense">
                                                    <option value="Trinitária e Tobagense">

                                                    <!-- América do Norte -->
                                                    <option value="Canadense">
                                                    <option value="Estadunidense">
                                                    <option value="Bermudense">
                                                    <option value="Groenlandesa">

                                                    <!-- África -->
                                                    <option value="Sul-Africana">
                                                    <option value="Nigeriana">
                                                    <option value="Egípcia">
                                                    <option value="Angolana">
                                                    <option value="Moçambicana">
                                                    <option value="Marroquina">
                                                    <option value="Ganesa">
                                                    <option value="Etíope">

                                                    <!-- Europa -->
                                                    <option value="Portuguesa">
                                                    <option value="Espanhola">
                                                    <option value="Francesa">
                                                    <option value="Italiana">
                                                    <option value="Alemã">
                                                    <option value="Inglesa">
                                                    <option value="Irlandesa">
                                                    <option value="Sueca">
                                                    <option value="Norueguesa">
                                                    <option value="Russa">

                                                    <!-- Ásia -->
                                                    <option value="Chinesa">
                                                    <option value="Japonesa">
                                                    <option value="Indiana">
                                                    <option value="Coreana">
                                                    <option value="Paquistanesa">
                                                    <option value="Filipina">
                                                    <option value="Tailandesa">
                                                    <option value="Vietnamita">
                                                </datalist>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row m-0 m-b-5">
                                        <div class="col-md-6 align-items-center">
                                            <label class="col-form-label col-form-label-sm text-left text-lg-right p-l-0">Naturalidade</label>
                                        <div class="col-12 p-0">
                                            <select name="naturalidade" class="form-control form-control-sm resetavel bg-silver-lighter">
                                                <option value="">Selecione</option>
                                                <option value="AC">AC</option>
                                                <option value="AL">AL</option>
                                                <option value="AP">AP</option>
                                                <option value="AM">AM</option>
                                                <option value="BA">BA</option>
                                                <option value="CE">CE</option>
                                                <option value="DF">DF</option>
                                                <option value="ES">ES</option>
                                                <option value="GO">GO</option>
                                                <option value="MA">MA</option>
                                                <option value="MT">MT</option>
                                                <option value="MS">MS</option>
                                                <option value="MG">MG</option>
                                                <option value="PA">PA</option>
                                                <option value="PB">PB</option>
                                                <option value="PR">PR</option>
                                                <option value="PE">PE</option>
                                                <option value="PI">PI</option>
                                                <option value="RJ">RJ</option>
                                                <option value="RN">RN</option>
                                                <option value="RS">RS</option>
                                                <option value="RO">RO</option>
                                                <option value="RR">RR</option>
                                                <option value="SC">SC</option>
                                                <option value="SP">SP</option>
                                                <option value="SE">SE</option>
                                                <option value="TO">TO</option>
                                            </select>
                                        </div>
                                        </div>
                                        <div class="col-md-6 align-items-center">
                                            <label class="col-form-label col-form-label-sm text-left text-lg-right">Munic. Nasc.</label>
                                            <div class="col-12 p-0">
                                                <input type="text" name="municipio_nascimento" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                            </div>
                                        </div>  
                                    </div>

                                    <div class="row m-0 m-b-5">
                                        <div class="col-md-6 align-items-center">
                                            <label class="col-form-label col-form-label-sm text-left text-lg-right">Turno<span class="text-danger m-l-5">*</span></label>
                                            <div class="col-12 p-0">
                                                <select name="turno" class="form-control form-control-sm resetavel bg-silver-lighter" required>
                                                    <option value="INTEGRAL">Integral</option>
                                                    <option value="MANHÃ">Manhã</option>
                                                    <option value="TARDE">Tarde</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6 align-items-center">
                                            <label class="col-form-label col-form-label-sm text-left text-lg-right">Função<span class="text-danger m-l-5">*</span></label>
                                            <div class="col-12 p-0">
                                                <input type="text" name="funcao" required class="form-control form-control-sm resetavel bg-silver-lighter" maxlength="45" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row m-0 m-b-5">    
                                        <div class="col-md-6 align-items-center">
                                            <label class="col-form-label col-form-label-sm text-left text-lg-right">Contato Emergência</label>
                                            <div class="col-12 p-0">
                                                <input type="text" name="contatoEmergenciaNome" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                            </div>
                                        </div>
                                        <div class="col-md-6 align-items-center">
                                            <label class="col-form-label col-form-label-sm text-left text-lg-right">Celular Emergência</label>
                                            <div class="col-12 p-0">
                                                <input type="text" name="contatoEmergenciaCelular" class="form-control form-control-sm resetavel mask-celular bg-silver-lighter" maxlength="15"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row m-0 m-b-5">
                                        <div class="col-md-6 align-items-center">
                                            <label class="col-form-label col-form-label-sm text-left text-lg-right">Email<span class="text-danger m-l-5">*</span></label>
                                            <div class="col-12 p-0">
                                                <input type="email" name="email" required class="form-control form-control-sm resetavel bg-silver-lighter" style="text-transform:lowercase" />
                                            </div>
                                        </div>
                                        <div class="col-md-6 align-items-center">
                                            <label class="col-form-label col-form-label-sm text-left text-lg-right">Email Colégio</label>
                                            <div class="col-12 p-0">
                                                <input type="email" name="emailColegio" class="form-control form-control-sm resetavel bg-silver-lighter" style="text-transform:lowercase" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row m-0 m-b-5">
                                        <div class="col-md-6 align-items-center">
                                            <label class="col-form-label col-form-label-sm text-left text-lg-right">Email Colégio 2</label>
                                            <div class="col-12 p-0">
                                                <input type="email" name="emailColegio2" class="form-control form-control-sm resetavel bg-silver-lighter" style="text-transform:lowercase" />
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </fieldset>

                        <!-- DADOS DO ENDERECO RESIDENCIAL -->
                        <fieldset style="padding:20px; border:1px solid #DFDFDF; margin:20px;">
                            <legend class="pl-2 pr-2 f-w-600">ENDEREÇO</legend>
                            <div class="row m-b-5">
                                <label class="col-form-label col-sm-1 text-right p-r-0">CEP<span class="text-danger m-l-5">*</label>
                                <div class="col-sm-2 p-r-0">
                                    <input type="text" name="cep" class="form-control form-control-sm resetavel bg-silver-lighter" required />
                                </div>
                            </div>
                            <div class="row m-b-5">
                                <label class="col-form-label col-sm-1 text-right p-r-0">Logradouro<span class="text-danger m-l-5">*</label>
                                <div class="col-sm-4">
                                    <input type="text" name="endereco" class="form-control form-control-sm resetavel bg-silver-lighter" required />
                                </div>
                                <label class="col-form-label col-sm-1 text-right p-r-0">Número<span class="text-danger m-l-5">*</label>
                                <div class="col-sm-3">
                                    <input type="text" name="numero" class="form-control form-control-sm resetavel bg-silver-lighter" placeholder="" required />
                                </div>
                                <label class="col-form-label col-sm-1 text-right p-r-0">Compl.</label>
                                <div class="col-sm-2">
                                    <input type="text" name="complemento" class="form-control form-control-sm resetavel bg-silver-lighter" placeholder="Ex.: apto 202" />
                                </div>
                            </div>
                            <div class="row m-b-5">
                                <label class="col-form-label col-sm-1 text-right p-r-0">Bairro<span class="text-danger m-l-5">*</label>
                                <div class="col-sm-4">
                                    <input type="text" name="bairro" class="form-control form-control-sm resetavel bg-silver-lighter" required />
                                </div>
                                <label class="col-form-label col-sm-1 text-right p-r-0">Cidade<span class="text-danger m-l-5">*</label>
                                <div class="col-sm-3">
                                    <input type="text" name="cidade" class="form-control form-control-sm resetavel bg-silver-lighter" required />
                                </div>
                                <label class="col-form-label col-sm-1 text-right p-r-0">Estado<span class="text-danger m-l-5">*</label>
                                <div class="col-sm-2">
                                    <select name="estado" class="form-control form-control-sm resetavel bg-silver-lighter" required>
                                        <option value="">Selecione</option>
                                        <option value="AC">AC</option>
                                        <option value="AL">AL</option>
                                        <option value="AP">AP</option>
                                        <option value="AM">AM</option>
                                        <option value="BA">BA</option>
                                        <option value="CE">CE</option>
                                        <option value="DF">DF</option>
                                        <option value="ES">ES</option>
                                        <option value="GO">GO</option>
                                        <option value="MA">MA</option>
                                        <option value="MT">MT</option>
                                        <option value="MS">MS</option>
                                        <option value="MG">MG</option>
                                        <option value="PA">PA</option>
                                        <option value="PB">PB</option>
                                        <option value="PR">PR</option>
                                        <option value="PE">PE</option>
                                        <option value="PI">PI</option>
                                        <option value="RJ">RJ</option>
                                        <option value="RN">RN</option>
                                        <option value="RS">RS</option>
                                        <option value="RO">RO</option>
                                        <option value="RR">RR</option>
                                        <option value="SC">SC</option>
                                        <option value="SP">SP</option>
                                        <option value="SE">SE</option>
                                        <option value="TO">TO</option>
                                    </select>
                                </div>
                            </div>
                        </fieldset>

                        <!-- DADOS DO VEÍCULO PARTICULAR -->
                        <fieldset style="padding:20px; border:1px solid #DFDFDF; margin:20px;">
                            <legend class="pl-2 pr-2 f-w-600">VEÍCULO</legend>
                            <div class="row m-b-5">
                                <label class="col-form-label col-sm-1 text-right p-r-0">Modelo</label>
                                <div class="col-sm-4">
                                    <input type="text" name="carro" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                </div>
                                <label class="col-form-label col-sm-4 text-right p-md-0">Placa</label>
                                <div class="col-sm-3">
                                    <input type="text" name="placa_carro" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                </div>
                            </div>
                        </fieldset>

                        <!-- DADOS DA CERTIDAO DE CASAMENTO -->
                        <fieldset class="fieldset-form-step" style="padding:20px; border:1px solid #DFDFDF; margin:20px;">
                            <legend class="pl-2 pr-2 f-w-600">CERTIDÃO DE NASCIMENTO/CASAMENTO</legend>
                            <div class="row m-b-5">
                                <label class="col-form-label col-sm-1 text-right p-r-0">Termo</label>
                                <div class="col-sm-5">
                                    <input type="number" name="num_termo" class="form-control form-control-sm resetavel bg-silver-lighter" maxlength="7" />
                                </div>
                                <label class="col-form-label col-sm-1 text-right p-r-0">Cartório</label>
                                <div class="col-sm-5">
                                    <input type="text" name="nome_cart" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                </div>
                            </div>
                            <div class="row m-b-5">
                                <label class="col-form-label col-sm-1 text-right p-r-0">Folha</label>
                                <div class="col-sm-2">
                                    <input type="text" name="folha" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                </div>
                                <label class="col-form-label col-sm-2 text-right p-md-0">Livro</label>
                                <div class="col-sm-2">
                                    <input type="text" name="livro" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                </div>

                                <label class="col-form-label col-sm-2 text-right p-md-0">Emissão</label>
                                <div class="col-sm-3">
                                    <input type="date" name="emissao_cert" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                </div>
                            </div>
                        </fieldset>

                        <!-- DADOS DA CARTEIRA DE IDENTIDADE/CNH -->
                        <fieldset style="padding:20px; border:1px solid #DFDFDF; margin:20px;">
                            <legend class="pl-2 pr-2 f-w-600">IDENTIDADE/CNH</legend>
                            <div class="row m-b-5">
                                <label class="col-form-label col-sm-1 text-right p-r-0">Número</label>
                                <div class="col-sm-4">
                                    <input type="text" name="num_ident" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                </div>
                                <label class="col-form-label col-sm-3 text-right p-md-0">Orgão</label>
                                <div class="col-sm-4">
                                    <input type="text" name="compl_ident" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                </div>
                            </div>
                            <div class="row m-b-5">
                                <label class="col-form-label col-sm-1 text-right p-r-0">UF</label>
                                <div class="col-sm-4">
                                    <select name="uf_identidade" class="form-control form-control-sm resetavel bg-silver-lighter">
                                        <option value="">Selecione</option>
                                        <option value="AC">AC</option>
                                        <option value="AL">AL</option>
                                        <option value="AP">AP</option>
                                        <option value="AM">AM</option>
                                        <option value="BA">BA</option>
                                        <option value="CE">CE</option>
                                        <option value="DF">DF</option>
                                        <option value="ES">ES</option>
                                        <option value="GO">GO</option>
                                        <option value="MA">MA</option>
                                        <option value="MT">MT</option>
                                        <option value="MS">MS</option>
                                        <option value="MG">MG</option>
                                        <option value="PA">PA</option>
                                        <option value="PB">PB</option>
                                        <option value="PR">PR</option>
                                        <option value="PE">PE</option>
                                        <option value="PI">PI</option>
                                        <option value="RJ">RJ</option>
                                        <option value="RN">RN</option>
                                        <option value="RS">RS</option>
                                        <option value="RO">RO</option>
                                        <option value="RR">RR</option>
                                        <option value="SC">SC</option>
                                        <option value="SP">SP</option>
                                        <option value="SE">SE</option>
                                        <option value="TO">TO</option>
                                    </select>
                                </div>
                                <label class="col-form-label col-sm-3 text-right p-r-0">Emissão</label>
                                <div class="col-sm-4">
                                    <input type="date" name="dt_ident" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                </div>
                            </div>
                        </fieldset>

                        <!-- DADOS TÍTULO DE ELEITOR -->
                        <fieldset class="fieldset-form-step" style="padding:20px; border:1px solid #DFDFDF; margin:20px;">
                            <legend class="pl-2 pr-2 f-w-600">TÍTULO DE ELEITOR</legend>
                            <div class="row m-b-5">
                                <label class="col-form-label col-sm-1 text-right p-r-0">Título</label>
                                <div class="col-sm-3">
                                    <input type="text" name="num_titulo" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                </div>
                                <label class="col-form-label col-sm-1 text-right p-r-0">Zona</label>
                                <div class="col-sm-3">
                                    <input type="text" name="zona" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                </div>
                                <label class="col-form-label col-sm-1 text-right p-r-0">Seção</label>
                                <div class="col-sm-3">
                                    <input type="text" name="secao" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                </div>
                            </div>
                        </fieldset>

                        <!-- DADOS CLT -->
                        <fieldset class="fieldset-form-step" style="padding:20px; border:1px solid #DFDFDF; margin:20px;">
                            <legend class="pl-2 pr-2 f-w-600">CARTEIRA DE TRABALHO</legend>
                            <div class="row m-b-5">
                                <label class="col-form-label col-sm-1 text-right p-r-0">Número</label>
                                <div class="col-sm-4">
                                    <input type="text" name="num_ctps" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                </div>
                                <label class="col-form-label col-sm-3 text-right p-r-0">Série</label>
                                <div class="col-sm-4">
                                    <input type="text" name="serie_ctps" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                </div>
                            </div>
                            <div class="row m-b-5">    
                                <label class="col-form-label col-sm-1 text-right p-r-0">Emissão</label>
                                <div class="col-sm-4">
                                    <input type="date" name="dt_ctps" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                </div>
                                <label class="col-form-label col-sm-3 text-right p-md-0">Pis</label>
                                <div class="col-sm-4">
                                    <input type="text" name="pis" class="form-control form-control-sm resetavel bg-silver-lighter" />
                                </div>
                            </div>
                        </fieldset>
                    </div>
                    <hr />
                    <button type="button" class="btn btn-sm btn-default" onclick="novaPesquisa()">
                        <i class="bi bi-arrow-left mr-1"></i>Lista de Funcionários
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-save m-r-5"></i> Gravar
                    </button>
                </form>
            </div>
        </div>
    
</div>
