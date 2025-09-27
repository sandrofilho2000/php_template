var controle = 'controle/default_control.php';
const language_url = '../assets/plugins/DataTables-bootstrap4/json/jquery.datatables.lang.pt_br.json';
const baseImgSrc = "https://www.csanl.com.br/professores/online/principal/_fotos_professor/";

var tbFuncionarios, funcionario;


function obterFoto(element) {
    let matricula = element.getAttribute('data-matricula') || $("input[name='matricula']").val();
    if (!matricula) return null;

    const extensions = [
        "JPG",
        "jpg",
        "jpeg",
        "JPEG",
        "png",
        "PNG",
    ];

    let index = 0;

    function tryNext() {
        if (index >= extensions.length) {
            element.src = "../img/funcionarios/funcionario_sem_foto.png";
            return;
        }

        const currentUrl = baseImgSrc + matricula +'.' + extensions[index];
        const img = new Image();

        img.onload = function () {
            element.src = currentUrl;
        };

        img.onerror = function () {
            index++;
            tryNext();
        };

        img.src = currentUrl;
    }

    tryNext();
}


function aplicarMascaraCPF() {
    // Remove tudo que não for número
    valor = document.querySelector('input[name="cpf"]').value
    if(!valor) return
    valor = valor.replace(/\D/g, "");

    // Aplica a máscara no formato 000.000.000-00
    document.querySelector('input[name="cpf"]').value = valor
        .replace(/^(\d{3})(\d)/, "$1.$2")
        .replace(/^(\d{3})\.(\d{3})(\d)/, "$1.$2.$3")
        .replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d)/, "$1.$2.$3-$4");
}


function aplicarMascaraCEP(valor) {
    valor = document.querySelector('input[name="cep"]').value
    if(!valor) return
    valor = valor.replace(/\D/g, "");
    document.querySelector('input[name="cep"]').value = valor.replace(/^(\d{5})(\d)/, "$1-$2");
}


$(document).ready(function () {
    const container = document.querySelector("#containerFicha");
    if (container) {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach(async (mutation) => {
                if (mutation.attributeName === "style") {
                    aplicarMascaraCPF()
                    aplicarMascaraCEP()
                    if (container.style.display == "none"){
                        $("#funcionario_image").attr("src", "../img/funcionarios/funcionario_sem_foto.png")
                    }else{
                        $("input[name='tx_img_delete']").val("");
                        setData();
                        obterFoto($("#funcionario_image")[0]);
                    }
                }
            });
        });

        observer.observe(container, { attributes: true, attributeFilter: ["style"] });

        function setData() {
            let dtsaida = $("#dtsaida").val();
            if (dtsaida) {
                let formatted_data = moment(dtsaida, 'YYYY-MM-DD').format('YYYY-MM-DD');
                $("#data_saida").val(formatted_data);
            }
        }

    } else {
        console.warn("Elemento #containerFicha não encontrado!");
    }

    $("#data_saida").off().change(function (e) {
        let dt_saida = e.target.value?.trim();

        if (!dt_saida) {
            $("#dtsaida").val("");
            return;
        }

        let formatted_data = dt_saida.replaceAll("-", "");
        $("#dtsaida").val(formatted_data);
    });

    $('#tabFuncionario').tabs();

    tbFuncionarios = $('#tbFuncionarios').DataTable({
        dom: '<"pull-right m-b-5"B>frtip',
        searching: false,
        lengthMenu: [10, 25, 50, 100, 500],
        language: { url: language_url },
        order: [[1, 'asc']],
        data: [],
        columns: [
            { data: 'matricula' },
            {
                data: 'foto',
                render: function (data, type, row) {
                    return `<img 
                    data-matricula="${row.matricula}" 
                    src="../img/funcionarios/funcionario_sem_foto.png"
                    class="foto-funcionario img-fluid rounded-circle d-block m-auto" 
                    style="max-height: 40px;max-width: 40px;min-width: 40px;min-height: 40px;object-fit: cover;" 
                    width="40" height="40" 
                    alt="Foto" 
                    loading="lazy"
                >`;
                }
            },
            {
                data: 'nome',
                render: function (data, type, row, meta) {
                    var saida = '<a href="javascript:;" class="link-selecionar">' + data + '</a>';
                    if (!isEmpty(row.dtsaida)) {
                        saida += '<label class="badge badge-info pull-right f-s-9 p-r-5 m-t-5">SAIU EM ' + moment(row.dtsaida, 'YYYYMMDD').format('DD/MM/YYYY') + '</label>';
                    }
                    saida += '<span id="lblSemFoto-' + row.matricula + '"></label>';
                    return saida;
                },
                className: 'dt-nowrap'
            },
            { data: 'funcao' },
            { data: 'telefone' },
            { data: 'celular' },
            { data: 'email' }
        ],
        buttons: [
            {
                text: '<i class="bi bi-plus"></i> Novo Funcionario',
                className: 'btn btn-sm btn-info',
                action: function (e, dt, node, config) {
                    funcionario = null;
                    limparForm('frmFuncionario');
                    $('#input_matricula').prop('disabled', false);
                    $('#nome_funcionario').text('');
                    $('.chk-dias-colegio').removeAttr('checked');
                    $('#containerPesquisar').fadeOut(function () {
                        $('#containerFicha').fadeIn();
                    });
                }
            }
        ],
        drawCallback: function () {
            $(".foto-funcionario").each(function () {
                obterFoto(this);
            });
        }
    });

    $('#tbFuncionarios tbody').on('click', '.link-selecionar', function () {
        funcionario = selecionarItemTabela(tbFuncionarios, $(this));
        exibirDadosItemSelecionado();
    });

    $("#open_foto_modal").click(function (e) {
        e.preventDefault();
        $("#tx_image").click();
    });

    $("#funcionario_image").click(function () {
        $("#tx_image").click();
    });


    $("#remove_foto").click(function (e) {
        e.preventDefault();
        $("#funcionario_image").attr("src", "../img/funcionarios/funcionario_sem_foto.png");
        let matricula = $("input[name='matricula']").val();
        $("input[name='tx_img_delete']").val(matricula);
    });

    let frm;
    $('input[name="cep"]').mask('00000-000', {
        onKeyPress: function (cep, event, currentField, options) {
            frm = currentField.parents('form');
        },
        onComplete: function (cep) {
            let logradouro = frm.find('input[name="endereco"]');
            let bairro = frm.find('input[name="bairro"]');
            let municipio = frm.find('input[name="cidade"]');
            let estado = frm.find('input[name="estado"]');
            let gif = frm.find('.loading-cep-ceraluno');
            consultaCep(cep, logradouro, bairro, municipio, estado, null, function (result) {
                if (!result.erro) {
                    frm.find('input[name="numero"]').focus();
                }
            });
        }
    });

    pesquisar();

});

function pesquisar() {
    var f = $('#frmPesquisar');
    requisicaoAjax(f.serialize(), exibirResultadoPesquisa, f.find('.loading'), f.find('button[type="submit"]'), controle);
    return false;
}

function exibirResultadoPesquisa(result) {
    if (result.tipo !== 'success') {
        alertSW(result.texto, result.tipo);
    }
    else {
        if (result.dados.length === 0) {
            alertSW('Nenhum registro encontrado com os dados informados.');
        }
        tbFuncionarios.clear().rows.add(result.dados).draw();
        if (result.dados.length === 1) {
            funcionario = result.dados[0];
            exibirDadosItemSelecionado();
        }
    }
}

function getDescricao(campo, valor) {
    var campos = {
        sexo: {
            'F': 'FEMININO',
            'M': 'MASCULINO'
        }
    };
    if (campos.hasOwnProperty(campo) && campos[campo].hasOwnProperty(valor)) {
        return campos[campo][valor];
    }
    return valor;
}

function exibirResultadoPesquisa(result) {
    if (result.tipo !== 'success') {
        alertSW(result.texto, result.tipo);
    } else {
        if (result.dados.length === 0) {
            alertSW('Nenhum registro encontrado com os dados informados.');
        }
        tbFuncionarios.clear().rows.add(result.dados).draw();
        if (result.dados.length === 1) {
            funcionario = result.dados[0];
            exibirDadosItemSelecionado();
        }
    }
};

function getDescricao(campo, valor) {
    var campos = {
        sexo: {
            'F': 'FEMININO',
            'M': 'MASCULINO'
        }
    };
    if (campos.hasOwnProperty(campo) && campos[campo].hasOwnProperty(valor)) {
        return campos[campo][valor];
    }
    return valor;
};

function exibirDadosItemSelecionado() {
    $('#nome_funcionario').html(funcionario.nome);
    $('#input_matricula').prop('disabled', funcionario && funcionario.codigo);
    var f = $('#frmFuncionario');
    for (var i in funcionario) {
        if (f.find('input[name="' + i + '"]')[0]) {
            f.find('input[name="' + i + '"]').val(getDescricao(i, funcionario[i]));
        }
        if (f.find('select[name="' + i + '"]')[0]) {
            f.find('select[name="' + i + '"]').val(funcionario[i]);
        }
    }
    carregarForm('frmFuncionario', funcionario, function () {
        $('#foto').attr('src', funcionario.foto);
    });
    $('#containerPesquisar').fadeOut(function () {
        $('#containerFicha').fadeIn();
    });
};

function novaPesquisa() {
    limparCamposResetaveis($('#frmFuncionario'));
    $('#foto').attr('src', $('#foto').attr('data-default'));
    $('#containerFicha').fadeOut(function () {
        $('#containerPesquisar').fadeIn();
    });
};

function marcarDiasColegio() {
    if (isEmpty(funcionario)) {
        return;
    }
    var prop;
    $('.chk-dias-colegio').each(function () {
        prop = $(this).attr('name');
        if (funcionario.hasOwnProperty(prop) && funcionario[prop] === 'X') {
            $(this).attr('checked', 'checked');
        } else {
            $(this).removeAttr('checked');
        }
    });
};

function gerarMatricula() {
  return Math.floor(10000000 + Math.random() * 90000000);
}

function gravar() {
    var matricula = $('input[name="matricula"]').val();

    // Limpar máscara do CPF
    let cpfInput = document.querySelector('input[name="cpf"]');
    let cpfRaw = cpfInput.value.replace(/[.\-]/g, "");
    cpfInput.value = cpfRaw;

    // Limpar máscara do CEP
    let cepInput = document.querySelector('input[name="cep"]');
    let cepRaw = cepInput.value.replace(/[\-]/g, "");
    cepInput.value = cepRaw;

    if(!$("id_objeto").val() && !matricula){
        matricula = gerarMatricula()
        $('input[name="matricula"]').val(gerarMatricula())
    }

    // Validação da matrícula (8 dígitos)
    if (!/^\d{8}$/.test(matricula) && !/^\d{7}$/.test(matricula)) {
        alertSW('A matrícula deve conter exatamente 8 dígitos numéricos.');
        return false;
    }

    // Verificar duplicidade de matrícula se for novo cadastro
    if (!$('input[name="id_objeto"]').val()) {
        var podeGravar = false;

        $.ajax({
            url: controle,
            type: 'POST',
            async: false,
            data: {
                objeto: 'Funcionario',
                acao: 'pesquisar',
                'filtros[IGUAL][matricula]': matricula,
                'campos': 'matricula'
            },
            success: function(response) {
                if (response.dados && response.dados.length > 0) {
                    alertSW('Esta matrícula já está cadastrada para outro funcionário!');
                    podeGravar = false;
                } else {
                    podeGravar = true;
                }
            },
            error: function() {
                alertSW('Erro ao verificar matrícula. Tente novamente.');
                podeGravar = false;
            }
        });

        if (!podeGravar) {
            return false;
        }
    }

    // Continua com o processo de gravação
    salvarFoto();
    deletarFoto();
    gravarFrm('frmFuncionario', null, function(response) {
        pesquisar();
        if (isEmpty($('#frmFuncionario').find('input[name=id_objeto]').val())) {
            funcionario = response.dados[0];
        }
    });

    // Reaplicar máscaras depois da gravação
    aplicarMascaraCEP();

    return false;
}

function salvarDados() {
    salvarFoto();
    deletarFoto();
    gravarFrm('frmFuncionario', null, function(response) {
        pesquisar();
        if (isEmpty($('#frmFuncionario').find('input[name=id_objeto]').val())) {
            funcionario = response.dados[0];
        }
    });
}

function salvarFoto() {
    let matricula = $("input[name='matricula']").val();
    if (!matricula) return;
    let f = document.querySelector('#frmFuncionario');
    if (!f) {
        console.error("Erro: Formulário #frmFuncionario não encontrado!");
        return;
    }
    let fileInput = f.querySelector('input[name="tx_image"]');
    if (!fileInput || fileInput.files.length === 0) {
        console.error("Erro: Nenhuma imagem selecionada!");
        return;
    }
    let file = fileInput.files[0];
    convertToJpg(file, function (jpgBlob) {
        let formData = new FormData(f);
        formData.append("tx_image", jpgBlob, "foto.jpg");
        formData.append('objeto', 'Funcionario');
        formData.append('metodo', 'salvarFoto');
        formData.append('acao', 'execute');
        $.ajax({
            url: controle,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                $(".overlay").hide();
            },
            error: function (erro, er) {
                $(".overlay").hide();
                console.error('Erro:', erro.status, erro.statusText, '(Tipo de erro: ' + er + ')');
            }
        });
    });
};

function deletarFoto() {
    let tx_img_delete = $("input[name='tx_img_delete']").val();
    if (!tx_img_delete) return;
    let dataObject = {};
    dataObject['metodo'] = 'deletarFoto';
    dataObject['objeto'] = 'Funcionario';
    dataObject['acao'] = 'execute';
    dataObject['tx_img_delete'] = tx_img_delete;
    $("#tx_image").attr("type", "text")
    $("#tx_image").attr("type", "file")
    requestAjax(dataObject, function (response) { });
};

function ativarAbaDados() {
    $('#home-tab').click();
};

$("#tx_image").on("change", function (event) {
    let file = event.target.files[0];
    if (file) {
        let imageUrl = URL.createObjectURL(file);
        $("#funcionario_image").attr("src", imageUrl);
    }
});
// Máscara para matrícula (8 dígitos)
$('.mask-matricula').mask('00000000', {reverse: false});

// Impede a alteração da matrícula se já existir um funcionário
$(document).on('change', 'input[name="matricula"]', function() {
    if (funcionario && funcionario.matricula) {
        if ($(this).val() !== funcionario.matricula) {
            alertSW('Não é permitido alterar a matrícula de um funcionário existente.');
            $(this).val(funcionario.matricula);
        }
    }
});
