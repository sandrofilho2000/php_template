$(document).on({
    ajaxStart: function(){
        $("body").addClass("loading"); 
    },
    ajaxStop: function(){ 
        $("body").removeClass("loading"); 
    }    
});

$(document).ready(function(){
    
    if($('[data-toggle="tooltip"]')[0]){
       $('[data-toggle="tooltip"]').tooltip(); 
    }
    
    $('a[data-page]').click(function(){
        const page = $(this).attr('data-page');
        if(isEmpty(page)){
            return;
        }
        const form = $('form[name=frmPageDirect]');
        form.find('input[name=page]').val(page);
        //form.find('input[name=css]').val($(this).attr('data-css'));
        form.submit();
    });
    //mascara para campos celular
    if($('.mask-celular')[0]){
        $('.mask-celular').mask('(00) 00000-0000');
    }
    //mascara para os campos CPF
    if($('.mask-cpf')[0]){
        $('.mask-cpf').mask('000.000.000-00', { 
            reverse: false,
            onComplete:function(cpf){
                if(!validaCPF(cpf)){
                    alertSW('O CPF informado não é válido.', 'info');
                }
            }
        });
    }
    if($('.mask-telefone')[0]){
        var behavior = function (val) {
            return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
        },
        options = {
            onKeyPress: function (val, e, field, options) {
                field.mask(behavior.apply({}, arguments), options);
            }
        };
        $('.mask-telefone').mask(behavior, options);
    }
    if($('.mask-moeda')[0]){
        $('.mask-moeda').maskMoney({
            prefix:'R$ ',
            affixesStay: false, /* defina se o prefixo e o sufixo permanecerão no valor do campo depois que o usuário sair do campo */
            precision:2, 
            decimal:',', 
            thousands:'.', 
            allowZero:true,
            allowEmpty:true
        });
    }
    //$('[data-target]').append('head');
});

function Usuario(){
    if($('#dados-user-adm')[0]){
        //obtido no arquivo index.php
        var usuario = JSON.parse($('#dados-user-adm').text());
        for(var i in usuario){
            this[i] = usuario[i];
        }
        this.possuiPerfil = function(perfil){
            return (this.tx_perfis.indexOf(perfil) !== -1);
        };
        $('#dados-user-adm').remove();
    }
}

function exibirMensagem(elemId, msg, tipo, duracao, callBackSuccess, callBackError){
    var html = '<div class="text-justify alert-dismissible fade show alert alert-'+ tipo +'" role="alert">';
    html += '<i class="bi bi-info-circle m-r-10"></i>'+ msg;
    
    html += '</div>';
    if(typeof(callBackSuccess) === 'function' && tipo === 'success'){
        callBackSuccess();
    }
    if(typeof(callBackError) === 'function' && (tipo === 'error' || tipo === 'danger')){
        callBackError();
    }
    $('#'+ elemId).html(html);
    if(typeof duracao !== 'undefined'){
        window.setTimeout(function(){
            $('#'+ elemId).html('');
        }, duracao);
    }
}

function exibirMensagemResultante(elem, objJSON, callBackSuccess, callBackError, tempo){
    var duracao = (typeof tempo === 'undefined') ? 6000 : tempo;
    $(elem).html(objJSON.html);
    if(typeof(callBackSuccess) === 'function' && objJSON.tipo === 'success'){
        callBackSuccess();
    }
    if(typeof(callBackError) === 'function' && (objJSON.tipo === 'error' || objJSON.tipo === 'danger')){
        callBackError();
    }
    /*if(objJSON.tipo !== 'success'){
        duracao = 20000;
    }*/
    window.setTimeout(function(){
        $(elem).html('');
    }, duracao);
}

function abrirPopup(URL) {
    var width = 900;
    var height = 580;
 
    var left = 99;
    var top = 99;
 
    window.open(URL, 'janela', 'width='+width+', height='+height+', top='+top+', left='+left+', scrollbars=yes, status=no, toolbar=no, location=no, directories=no, menubar=yes, resizable=no, fullscreen=no');
}

/**
 * Obtém os dados do item selecionado por elemento da linha clicado. 
 * Ex.:
 * $('#tableLista tbody').on('click', '.linkEngrenagem', function(){
 *      selecionarItemTabela(tableLista, $(this));
 * });
 * @param {type} table variável com a instância da DataTable
 * @param {type} link linhas da tabela clicada, elemento tr
 * @returns {itemSelecionado}
 */
function selecionarItemTabela(table, link){
    var itemSelecionado = table.row($(link).parents('tr')).data();
    if(typeof itemSelecionado === 'undefined'){
        itemSelecionado = table.row($(link).parents('tr').prev('tr')).data();
    }
    tableSelecionada = table;
    return itemSelecionado;
}
function selecionarLinhaTabela(table, link){
    var row = table.row($(link).parents('tr'));
    return row;
}
function atualizarDadosTabela(table, dados){
    table.clear().rows.add(dados).draw();
}
/**
 * Carrega o formulario com dados do objeto, desde que as propriedades do objeto tenham nome iguais aos nomes dos campos do formulario
 * @param {string} formId - id do formulario a ser carregado
 * @param {object} obj - objeto com os dados a serem carregados no formulário
 * @returns {undefined}
 */
function carregarForm(formId, obj, callback){
    var name, value, type, mask, field;
    for(var i in obj){
        name = i;
        value = obj[i];
        //campo no formato array
        if( $('#'+ formId +' [name="'+ name +'[]"]').length){
            name = name +'[]';
        }
        if( ! $('#'+ formId +' [name="'+ name +'"]').length || $('#'+ formId +' [name="'+ name +'"]').hasClass('nao-carregar-edicao')){
            continue;
        }
        type = $('#'+ formId +' [name="'+ name +'"]').attr('type');
        if(typeof type === 'undefined'){
            type = $('#'+ formId +' [name="'+ name +'"]').prop('nodeName');
        }
        field = $('#'+ formId +' [name="'+ name +'"]');
        switch (type) {
            case 'SELECT':
                if(field.hasClass('selectpicker')){
                    field.selectpicker('val', value);
                    if(field.find('option:selected').length === 0){
                        field.selectpicker('val', JSON.parse(value));
                    }
                }
                else{
                //$('#'+ formId +' [name="'+ name +'"]').selectpicker('val', value);//.trigger('change');
                    field.val(value);//.trigger('change');
                }
                break;
            case 'radio':
            case 'checkbox':
                if(typeof(value) === 'object'){
                    for(var x in value){
                        $('#'+ formId +' [name="'+ name +'"][value="'+ value[x] +'"]').prop('checked', 'checked');
                    }
                }
                else{
                    try{
                        $('#'+ formId +' [name="'+ name +'"][value="'+ value +'"]').prop('checked', 'checked');
                    }
                    catch(e){
                        console.log(e);
                    }
                }
                break;
            case 'hidden':
            case 'text':
            case 'email':
            case 'color':
            case 'number':
            case 'TEXTAREA':
            case 'date':
            case 'datetime-local':
                if(field.hasClass('formato-data') && moment(value).isValid()){
                    field.val(moment(value).format('DD/MM/YYYY'));
                }
                else if(field.hasClass('datepicker') && moment(value).isValid()){
                    mask = field.attr('formato-data');
                    field.datepicker('setDate', moment(value).format(mask));
                }
                else if(typeof field.attr('data-datetimepicker') === 'string'){
                    mask = field.attr('data-datetimepicker');
                    field.val(moment(value).format(mask));
                }
                else if(field.hasClass('moeda')){
                    field.val(value.replace('.', ','));
                }
                else{
                    field.val(value);
                }
                break;
                
            default:
                field.val(value);
        }
        
    }
    var pkName = $('#'+ formId +' input[name="id_objeto"]').attr('data-pkName');
    //console.log('pkName: '+ pkName);
    //console.log('typeof pkName: '+ typeof pkName);
    //console.log('length: '+ $('#'+ formId +' input[name="id_objeto"]').length);
    if(typeof pkName !== 'undefined' && $('#'+ formId +' input[name="id_objeto"]').length){
        //console.log('carregando id_objeto com a propriedade '+ pkName +' de valor '+ obj[pkName]);
        //console.log(obj);
       $('#'+ formId +' input[name="id_objeto"]').val(obj[pkName]);
    }
    //aplica a mascara nos campos moeda que utilizam a propriedade 
    $('#'+ formId +' input[data-maskmoney]').trigger('mask.maskMoney');
    
    if(typeof callback === 'function'){
        callback();
    }
}
/**
 * Considerando o padrao de blocos em fieldsets, oculta os fieldsets visiveis para exibir um especifico
 * @param {type} fieldsetId id do fieldset a ser exibido
 * @returns {undefined}
 */
function exibirFieldset(fieldsetId){
    $('fieldset:visible').fadeOut(function(){
        $('#'+ fieldsetId).fadeIn();
    });
}
/**
 * Reajusta a largura das colunas de modo que fique nas dimensoes definidas inicialmente
 */
function tableReajustarColunas(table, time){
    var delay = (typeof time === 'undefined') ? '500' : time;
    window.setTimeout(function(){
        table.columns.adjust().draw();
    }, delay);
}
/**
 * Funcao padrao de envio de dados por formulario para incluir ou alterar
 * @param {type} frmId
 * @param {type} instanceTable
 * @param {type} callbackSuccess
 * @param {type} acaoName
 * @returns {Boolean}
 */
function gravarFrm(frmId, instanceTable, callback, acaoName){
    const form = (typeof frmId === 'undefined') ? $('#frmDefault') : $('#'+ frmId);
    var id_objeto = form.find('[name=id_objeto]').val();
    var acao = isEmpty(id_objeto) ? 'incluir' : 'salvar';
    if(typeof acaoName !== 'undefined'){
        acao = acaoName;
    }
    if(form.find('input[name=acao]').val() === 'execute'){
        acao = 'execute';
    }
    //console.log('id_objeto: '+ form.find('input[name=id_objeto]').val());
    //return false;
    form.find('input[name=acao]').val(acao);
    form.find('button[type=submit]').attr('disabled', 'disabled');
    form.find('img.loading').fadeIn();
    form.find('div.msg').html('');
    if(typeof instanceTable !== 'undefined'){
        table = instanceTable;
    }
    if(typeof controle === 'undefined'){
        alertSW('Arquivo de controle da requisição está idefinido', 'error');
        return false;
    }
    $.ajax({
        url: controle,
        method: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: function(result){
            if(result.tipo === 'success' && isEmpty(form.find('[name=id_objeto]').val())){
                limparForm(frmId);
                //form.find('input[name=acao]').val(acao);
            }            
            if(typeof callback === 'function'){
                callback(result);
            }
            if(result.tipo === 'success' && typeof table !== 'undefined' && !isEmpty(table)){
                table.ajax.reload(null, false);
            }
            //exibirMensagemResultante(form.find('div.msg'), result);
            alertSW(result.texto, result.tipo);
        },
        complete:function(){
            form.find('button[type=submit]').removeAttr('disabled');
            form.find('img.loading').fadeOut();
            try {
                if(typeof table !== 'undefined' && !isEmpty(table)){
                    window.setTimeout(function(){
                        //setStatusButtonsDataTableDefault(table);
                        tableReajustarColunas(table);
                    }, 1000);
                }
            } catch (e) {
                console.log(e);
            }
        },
        error:function(erro, er){
            var msg = 'Erro ' + erro.status + ' - ' + erro.statusText + ' (Tipo de erro: ' + er +')';
            alert(msg);
        }
    });
    return false;
}

async function gravarFrmAsync(frmId, instanceTable, callback, acaoName) {
    const form = (typeof frmId === 'undefined') ? $('#frmDefault') : $('#' + frmId);
    var id_objeto = form.find('[name=id_objeto]').val();
    var acao = isEmpty(id_objeto) ? 'incluir' : 'salvar';

    if (typeof acaoName !== 'undefined') {
        acao = acaoName;
    }
    if (form.find('input[name=acao]').val() === 'execute') {
        acao = 'execute';
    }

    form.find('input[name=acao]').val(acao);
    form.find('button[type=submit]').attr('disabled', 'disabled');

    if (typeof instanceTable !== 'undefined') {
        table = instanceTable;
    }
    if (typeof controle === 'undefined') {
        return false;
    }

    try {
        const result = await new Promise((resolve, reject) => {
            $.ajax({
                url: controle,
                method: 'post',
                data: form.serialize(),
                dataType: 'json',
                success: function (result) {
                    if (result.tipo === 'success' && isEmpty(form.find('[name=id_objeto]').val())) {
                        limparForm(frmId);
                    }
                    if (typeof callback === 'function') {
                        callback(result);
                    }
                    if (result.tipo === 'success' && typeof table !== 'undefined' && !isEmpty(table)) {
                        table.ajax.reload(null, false);
                    }
                    resolve(result);
                },
                complete: function () {
                    form.find('button[type=submit]').removeAttr('disabled');
                    try {
                        if (typeof table !== 'undefined' && !isEmpty(table)) {
                            window.setTimeout(function () {
                                tableReajustarColunas(table);
                            }, 1000);
                        }
                    } catch (e) {
                        console.log(e);
                    }
                },
                error: function (erro, er) {
                    var msg = 'Erro ' + erro.status + ' - ' + erro.statusText + ' (Tipo de erro: ' + er + ')';
                    alert(msg);
                    reject(msg);
                }
            });
        });

        return result;
    } catch (err) {
        console.error("Erro no gravarFrmAsync:", err);
        return false;
    }
}


function frmCarregarPk(frmId, obj){
    let pkName = $('#'+ frmId).find('input[name="id_objeto"]').attr('data-pkName');
    if(!obj.hasOwnProperty(pkName)){
        alertSW('Propriedade não encontrada no objeto. ('+ pkName +')', 'error');
    }
    let id = obj[pkName];
    $('#'+ frmId).find('input[name="id_objeto"]').val(id);
}

function isEmpty(valor){
    if(valor === null || valor === 'null' || $.trim(valor) === '' || valor === 'undefined'){
        return true;
    }
    return false;
}

function ifEmpty(valor, alternativo){
    if(valor === null || valor === 'null' || $.trim(valor) === ''){
        return alternativo;
    }
    return valor;
}

function limparCamposResetaveis(form){
    form.find('.resetavel').val('');
    form.find('.selectpicker').selectpicker('val', '');
    form.find('input[type=checkbox]:visible').prop('checked', false);
    form.find('input[type=radio]:visible').prop('checked', false);
}

function limparForm(frmId, callback){
    const form = (typeof frmId === 'undefined') ? $('#frmDefault') : $('#'+ frmId);
    form[0].reset();
    form.find('input.resetavel').val('');
    form.find('input[name=id_objeto]').val('');
    form.find('input[name=acao]').val('incluir');
    try{
        form.find('select').val('');
        form.find('.selectpicker').selectpicker('val', '');
        form.find('.selectpicker').selectpicker('refresh');
        form.find('.msg').html('');
    } catch(e){
        
    }
    if(callback === 'function'){
        callback();
    }
}

function fecharModal(){
    /*s$('div.modal').fadeOut(function(){
        $(this).find('div.modal-container').hide();
    });*/
    $('div.modal:visible').find('div.modal-container').slideUp(200,function(){
        $('div.modal:visible').fadeOut();
    });
}

function abrirModal(id){
    $('#'+ id).fadeIn(function(){
        $(this).find('div.modal-container').slideDown(250);
    });
}

function exibirMsgPendenciaForm(target, msg, input){
    var msgObj = {
        tipo: 'error',
        texto: msg,
        html: '<div class="alert alert-danger">'+ msg +'</danger>'
    };
    var time = 8000;
    if(typeof input !== 'undefined'){
        exibirPendenciaCampo(input);
        /*input.addClass('is-invalid');
        window.setTimeout(function(){
            input.removeClass('is-invalid');
        }, time);*/
    }
    exibirMensagemResultante(target, msgObj, null, null, time);
}

function exibirPendenciaCampo(input){
    input.addClass('is-invalid');
    window.setTimeout(function(){
        input.removeClass('is-invalid');
    }, 8000);
}

function exibirGritterResultante(objJson){
    var icone = 'disquete.png';
    if(objJson.tipo !== 'success'){
        icone = 'exclamacao.png';
    }
    gerarGritter('Resultado', objJson.texto, icone);
    return objJson.tipo;
}

function gerarGritter(title, text, type, time){
    var icone = (type === 'success') ? 'disquete.png' : 'exclamacao.png';
    var image;
    try{
        image = $('img[alt="logo-csa"]').attr('src').replace('logo-2.png', icone);
    }
    catch(e){
        console.log(e);
    }
    if(typeof time === 'undefined'){
        time = 8000;
    }
    $.gritter.add({
        title: title,
        text: text,
        image: image,
        /*class_name: 'gritter-light',*/
        time:time
    });
    $('#modal-message').remove();
}

function gerarGritterResultante(obj, dir_root_image, time){
    let icone = isEmpty(dir_root_image) ? '' : dir_root_image;
    icone += (obj.tipo === 'success') ? 'img/disquete.png' : 'img/exclamacao.png';
    if(typeof time === 'undefined'){
        time = 4000;
    }
    $.gritter.add({
        title: 'Resultado',
        text: obj.texto,
        image: icone,
        time:time
    });
}
//para que o sweet alert use os estilos do bootstrap
const swalWithBootstrapButtons = Swal.mixin({
    customClass: {
        confirmButton: 'btn btn-primary ml-3',
        cancelButton: 'btn btn-danger'
    },
    buttonsStyling: false
});

function confirmSW(pergunta, callbackConfirm, callbackDecline){
    swalWithBootstrapButtons.fire({
        html: pergunta,
        showCancelButton: true,
        cancelButtonText: 'Não, cancele.',
        confirmButtonText: 'Sim, prossiga!',
        reverseButtons: true,
        icon:'question'
    }).then((result) => {
        if (result.isConfirmed) {
            callbackConfirm();
        }
        else if(typeof callbackDecline === 'function'){
            callbackDecline();
        }
    });
}

function alertTimeSW(text, type, time){
    swalWithBootstrapButtons.fire({
        html: text,
        icon: type,
        timer: time
    });
}

function alertSW(text, type){
    if(typeof type === 'undefined' || type === 'danger'){
        type = 'error';
    }
    try {
        swalWithBootstrapButtons.fire({
            html: text,
            icon: type
        });
        //swal(title, text, type);
        //$('.swal-text').html($('.swal-text').text());
    } catch (e) {
        alert(text);
    }
}

function alertSwResult(result){
    alertSW(result.texto, result.tipo);
}

function alertSwAddText(text){
    window.setTimeout(function(){
        $('#swal2-html-container').append(text);
    }, 100);
}
function alertSwReplace(text){
    window.setTimeout(function(){
        $('#swal2-html-container').html(text);
    }, 100);
}
/**
 * Abreviar nomes sem abreviar o primeiro e último nome
 * @param {type} fullName
 * @param {type} limit
 * @returns {unresolved}
 */
function abreviarNome(fullName, limit) {
    fullName = fullName.trim();
    if (fullName.length > limit) {
        var nomes = fullName.split(' ');
        var indiceAtual = nomes.length - 2;
        var proibirAbreviacao = ['DA', 'DE', 'DO', 'DOS', 'E', 'da', 'de', 'do', 'dos', 'e'];
        while(indiceAtual > 0){
            if(proibirAbreviacao.indexOf(nomes[indiceAtual]) === -1){
                nomes[indiceAtual] = nomes[indiceAtual].substr(0,1) +'.';
            }
            fullName = nomes.join(' ');
            if(fullName.length <= limit){
                return fullName;
            }
             indiceAtual--;

        }
    }
    return fullName;
}

function primeiroUltimoNome(fullname){
    var names = fullname.split(' ');
    var saida = names[0];
    var last = names.length - 1;
    saida += ' '+ names[last];
    return saida;
}

function nl2br (str, is_xhtml) {
    if (typeof str === 'undefined' || str === null) {
        return '';
    }
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

function alterarItem(texto, dados, url, callbackSuccess){
    confirmSW(texto, function(){
        requisicaoAjax(dados, function(result){
            alertSW(result.texto, result.tipo);
            if(typeof callbackSuccess === 'function' && result.tipo === 'success'){
                callbackSuccess(result);
            }
        }, null, null, url);
    }, function(){
        alertSW('Ok, nenhuma alteração foi realizada.', 'info');
    });
}

function requestAjax(dados, callback){
    //a variavel controle pode ser definida no arquivo js da pagina
    requisicaoAjax(dados, callback, null, null, controle);
}

function requisicaoAjax(dados, callback, loading, button, url){
    if(!isEmpty(loading)){
        loading.fadeIn();
    }
    if(!isEmpty(button)){
        button.attr('disabled', 'disabled');
    }
    if(isEmpty(url)){
        url = '../../controle/controle_default.php';
    }
    $.ajax({
        url: url,
        method: 'post',
        data: dados,
        dataType: 'json',
        success:function(result){
            if(typeof callback === 'function'){
                callback(result);
            }
        },
        error:function(erro, er){
            var msg = 'Erro ' + erro.status + ' - ' + erro.statusText + ' (Tipo de erro: ' + er +')';
            alert(msg);
        },
        complete:function(){
            if(!isEmpty(loading)){
                loading.fadeOut();
            }
            if(!isEmpty(button)){
                button.removeAttr('disabled');
            }
        }
    });
}


function requisicaoAjaxArquivo(dados, callback, url = '../controle/controle_default.php') {
    const xhr = new XMLHttpRequest();
    const formData = new FormData();
    for (const key in dados) {
        formData.append(key, dados[key]);
    }
    
    xhr.open('POST', url, true);
    xhr.responseType = 'blob'; // importante para PDFs
    
    xhr.onload = function () {
        if (xhr.status === 200) {
            const pdfBlob = xhr.response; // ✅ Agora a variável está corretamente definida
            callback(pdfBlob);
        } else {
            alert('Erro ao obter contrato: ' + xhr.status);
        }
    };
    
    xhr.onerror = function () {
        alert('Erro de rede ao obter contrato.');
    };
    
    xhr.send(formData);
}
/* function requisicaoAjaxArquivo(dados, callback, url) {
    if (!url) url = '../../controle/controle_default.php';

    const xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.responseType = 'blob'; // Importante: retorna como Blob

    xhr.onload = function () {
        if (xhr.status === 200) {
            callback(xhr.response); // resposta binária (PDF)
        } else {
            alert('Erro ' + xhr.status + ' - ' + xhr.statusText);
        }
    };

    const formData = new FormData();
    for (const chave in dados) {
        formData.append(chave, dados[chave]);
    }

    xhr.send(formData);
} */

function atualizarLinhasDataTable(table, dados, columnName, propertyName){
    alert('revisar, parece não estar funcionando');
    var obj;
    for(var i = 0; i < dados.length; i++){
        obj = dados[i];
        table.rows().every(function(rowIdx, tableLoop, rowLoop){
            var newData = this.data();
            console.log(newData[columnName] +' === '+ obj[propertyName]);
            if(newData[columnName] === obj[propertyName]){
                newData[columnName] = obj[propertyName];
                table.row(rowIdx).data(newData).draw();
            }
        });
    }
}
function formatarCPF(cpf){
    if(isEmpty(cpf)){
        return '';
    }
    //retira os caracteres indesejados...
    cpf = cpf.replace(/[^\d]/g, "");
    //realizar a formatação...
    return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
}
function validaCPF(cpf){
    //var cpf = document.form1.numero.value;
    cpf = cpf.replace('.', '');
    cpf = cpf.replace('.', '');
    cpf = cpf.replace('-', '');
    var numeros, digitos, soma, i, resultado, digitos_iguais;
    digitos_iguais = 1;
    if (cpf.length < 11)
        return false;
    for (i = 0; i < cpf.length - 1; i++)
        if (cpf.charAt(i) != cpf.charAt(i + 1))
        {
            digitos_iguais = 0;
            break;
        }
    if (!digitos_iguais)
    {
        numeros = cpf.substring(0, 9);
        digitos = cpf.substring(9);
        soma = 0;
        for (i = 10; i > 1; i--)
            soma += numeros.charAt(10 - i) * i;
        resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado != digitos.charAt(0))
            return false;
        numeros = cpf.substring(0, 10);
        soma = 0;
        for (i = 11; i > 1; i--)
            soma += numeros.charAt(11 - i) * i;
        resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado != digitos.charAt(1))
            return false;
        return true;
    } else
        return false;
}

/* Indicador de força da senha */
var forcaSenha = 0;
function passwordStrength(password, indicador) {
    var desc = [{'width':'0px'}, {'width':'20%'}, {'width':'40%'}, {'width':'60%'}, {'width':'80%'}, {'width':'100%'}];
    var descClass = ['', 'bg-danger', 'bg-danger', 'bg-warning', 'bg-primary', 'bg-success'];
    var descText = ['', 'Muito fraca', 'Fraca', 'Mediana', 'Forte', 'Muito forte'];
    var score = 0;
    //se a senha maior que 7 dá 1 ponto
    if (password.length > 7)
        score++;
    //se a senha tiver caracteres maiúsculos e minúsculos, dê 1 ponto
    if ((password.match(/[a-z]/)) && (password.match(/[A-Z]/)))
        score++;
    //se a senha tiver pelo menos um número, dê 1 ponto
    if (password.match(/\d+/))
        score++;
    //se a senha tiver pelo menos uma caracter especial, dê 1 ponto
    if (password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/))
        score++;
    //se a senha for maior que 10, dê outro 1 ponto
    if (password.length > 10)
        score++;
    if(typeof indicador === 'undefined'){
        indicador = '#jak_pstrength';
    }
    else{
        indicador = '#'+ indicador;
    }
    //indicador de exibição
    $(indicador).removeClass('bg-danger bg-warning bg-success bg-primary');
    $(indicador).addClass(descClass[score]).css(desc[score]).text(descText[score]);
    forcaSenha = score;
}

function obterTokenFormulario(){
    
}

function processoAdmissaoAlterarSenha(){
    if(forcaSenha < 4){
        alertSW('A força da senha deve ser classificada no mínimo como FORTE.');
        return false;
    }
    var form = $('#frmAlterarSenha');
    form.find('button[type=submit]').attr('disabled', 'disabled');
    form.find('img.loading').fadeIn();
    form.find('div.msg').html('');
    $.ajax({
        url: 'controle/controle_processo_admissao.php',
        method: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: function(result){
            alertSW(result.texto, result.tipo);
            if(result.tipo === 'success'){
                $('#frmAlterarSenha input[type=password]').val('');
                passwordStrength('', 'barraForcaSenha');
            }
        },
        complete:function(){
            form.find('button[type=submit]').removeAttr('disabled');
            form.find('img.loading').fadeOut();
        }
    });
    return false;
}

function carregarCaptcha(input, callback){
    if(input.length === 0){
        if(typeof callback === 'function'){
            callback();
        }
    }
    else{
        grecaptcha.ready(function(){
            grecaptcha.execute('6LdGq7MmAAAAAEj9pyRxtmKHMSwRq_m7tw9d1guj', {action: 'inscricao'}).then(function(token){
                input.val(token);
                if(typeof callback === 'function'){
                    callback();
                }
            });
        });
    }
}

function validarTipoArquivo(inputFileId){
    var arquivo = document.getElementById(inputFileId);
    var tipo = arquivo.files[0].type;
    var tipos = $.trim(arquivo.accept).split(',');
    if(tipos.indexOf(tipo) !== -1){
        return true;
    }
    return false;
}

function consultaCepReturnObj(cepPesquisar, gifLoading, callback){
    $.ajax({
        url: 'https://viacep.com.br/ws/'+ cepPesquisar.replace(/\D/g,"") +'/json/',
        headers:{
            'Accept':'json',
            'Cookie':'Version=1'
        },
        cache: false,
        type: 'get',
        dataType: 'json',
        success: function(e){
            if(e.erro){
                e.success = false;
                alertSW('O CEP não foi encontrado na base de dados dos Correios, por favor, preencha os demais campos do endereço.', 'info');
            }
            if(typeof callback === 'function'){
                callback(e);
            }
        },
        beforeSend:function(){
            if(typeof gifLoading !== 'undefined'){
                $(gifLoading).fadeIn();
            }
        },
        complete:function(){
            if(typeof gifLoading !== 'undefined'){
                $(gifLoading).fadeOut();
            }
        }
    });
}

function consultaCep(cepPesquisar, campoLogradouro, campoBairro, campoMunicipio, campoEstado, gifLoading, callback){
    $.ajax({
        url: 'https://viacep.com.br/ws/'+ cepPesquisar.replace(/\D/g,"") +'/json/',
        headers:{
            'Accept':'json',
            'Cookie':'Version=1'
        },
        cache: false,
        type: 'get',
        dataType: 'json',
        success: function(e){
            if(e.erro){
                alertSW('O CEP não foi encontrado na base de dados dos Correios, por favor, preencha os demais campos do endereço.', 'info');
            }
            else{
                campoLogradouro.val(e.logradouro);
                campoBairro.val(e.bairro);
                campoMunicipio.val(e.localidade);
                campoEstado.val(e.uf);
            }
            if(typeof callback === 'function'){
                callback(e);
            }
        },
        beforeSend:function(){
            if(typeof gifLoading !== 'undefined'){
                $(gifLoading).fadeIn();
            }
        },
        complete:function(){
            if(typeof gifLoading !== 'undefined'){
                $(gifLoading).fadeOut();
            }
        }
    });
}

function consultarEstados(){
    $.ajax({
        url: 'https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=sigla',
        headers:{
            'Accept':'json',
            'Cookie':'Version=1'
        },
        cache: false,
        type: 'get',
        dataType: 'json',
        success: function(e){
            console.log(e);
        }
    });
}
/**
 * 
 * @param {type} uf id do estado segundo o IBGE
 * @returns {undefined}
 */
function consultarMunicipiosPorUF(ufId, callback){
    $.ajax({
        url: 'https://servicodados.ibge.gov.br/api/v1/localidades/estados/'+ ufId +'/municipios?orderBy=nome',
        headers:{
            'Accept':'json',
            'Cookie':'Version=1'
        },
        cache: false,
        type: 'get',
        dataType: 'json',
        success: function(e){
            if(typeof (callback) === 'function'){
                callback(e);
            }
            else{
                return e;
            }
        }
    });
}

function getMesPorExtenso(nr_mes){
    let Mes = {
        '01': 'Janeiro',
        '02': 'Fevereiro',
        '03': 'Março',
        '04': 'Abril',
        '05': 'Maio',
        '06': 'Junho',
        '07': 'Julho',
        '08': 'Agosto',
        '09': 'Setembro',
        '10': 'Outubro',
        '11': 'Novembro',
        '12': 'Dezembro',
        '1': 'Janeiro',
        '2': 'Fevereiro',
        '3': 'Março',
        '4': 'Abril',
        '5': 'Maio',
        '6': 'Junho',
        '7': 'Julho',
        '8': 'Agosto',
        '9': 'Setembro'
    };
    if(Mes.hasOwnProperty(nr_mes)){
        return Mes[nr_mes];
    }
    return nr_mes;
}

var dataTablesFrk = {
    table: null,
    currentRow: null,
    addEventPopover: function(){
        $('[data-toggle="popover"]').popover({
            trigger: 'focus'
        });
    },
    selectItem: function(table, link){
        this.table = table;
        this.currentRow = $(link).parents('tr');
        var itemSelecionado = table.row($(link).parents('tr')).data();
        if(typeof itemSelecionado === 'undefined'){
            itemSelecionado = table.row($(link).parents('tr').prev('tr')).data();
        }
        tableSelecionada = table;
        return itemSelecionado;
    },
    adjustColumns: function(table){
        table.columns.adjust().draw();
    },
    addFilterColumns: function(table, tableId, noColumns){
        window.setTimeout(function(){
            /*let tableHeader = $('#'+ tableId).parents('div.dataTables_scroll').find('div.dataTables_scrollHead table');
            if(tableHeader.length === 0){
                tableHeader = $('#'+ tableId);
            }
            tableHeader.find('th').each(function(i){*/
            $('#'+ tableId +' thead tr th').each(function(i){
                if(typeof noColumns === 'undefined'){
                    noColumns = [1000];
                }
                if(noColumns.indexOf(i) === -1){
                    //tableHeader.find('thead tr th:nth('+ nr +')').append('<input type="text" placeholder="Filtrar" class="filter-column form-control form-control-sm p-l-3 p-r-2 p-t-0 p-b-0 m-t-4" />');
                    //nr++;
                    //let style = (i === 1) ? 'width:50px !important' : '';
                    $(this).append( '<input type="text" placeholder="Filtrar" class="filter-column form-control form-control-sm p-l-3 p-r-2 p-t-0 p-b-0 m-t-4" />' );
                    $('input', this).on('keyup change', function(){
                        if (table.column(i).search() !== this.value) {
                            table.column(i).search(this.value).draw();
                        }
                    });
                }
            });
        }, 1000);
    },
    addFilterSelectColumns: function(table, tableId, columns, denyEmpty){
        window.setTimeout(function(){
            let dados = table.rows().data();
            let obj;
            let prop;
            let row;
            let opt = [];
            let opts = [];
            $('#'+ tableId +' thead tr th').each(function(i){
                //console.log(columns);
                for(let x = 0; x < columns.length; x++){
                    obj = columns[x];
                    if(obj.hasOwnProperty(i)){
                        opt = [];
                        //opts = ['<option value="">Selecione</option>'];
                        opts = [];
                        if(typeof denyEmpty !== 'undefined'){
                            opts.push('<option value="">Selecione</option>');
                        }
                        prop = obj[i];
                        for(let z = 0; z < dados.length; z++){
                            row = dados[z];
                            //console.log(row);
                            if(isEmpty(row[prop])){
                                continue;
                            }
                            opt = '<option value="'+ row[prop] +'">'+ row[prop] +'</option>';
                            if(opts.indexOf(opt) === -1){
                                opts.push(opt);
                            }
                        }
                        if(opts.length){
                            opts.sort();
                            $('#filter_'+ prop).remove();
                            $(this).append('<select id="filter_'+ prop +'" class="filter-column form-control form-control-sm p-l-3 p-r-2 p-t-0 p-b-0 m-t-4 width-80">'+ opts.join('') +'</select>'); 
                            $('select', this).on('change', function(){
                                if (table.column(i).search() !== this.value) {
                                    table.column(i).search(this.value).draw();
                                }
                            });
                            $('#filter_'+ prop).trigger('change');
                        }
                    }
                }
            });
        }, 1000);
    },
    updateDados: function(novosDados, pkName){
        //para usar deve-se atribuir a instancia da tabela na propriedade conforme abaixo
        //dataTablesFrk.table = tbInscricoes;
        //em seguida chamar o metodo
        //dataTablesFrk.updateDados(response.dados[i], 'id_inscricao');
        if(dataTablesFrk.table.rows('.selected').data().length > 0){
            dataTablesFrk.table.rows('.selected').every(function(rowIdx, tableLoop, rowLoop){
                var data = this.data();
                //console.log(parseInt(data[pkName]) +' === '+ novosDados[pkName]);
                if(parseInt(data[pkName]) === parseInt(novosDados[pkName])){
                    dataTablesFrk.table.row(rowIdx).data(novosDados).deselect();
                }
            });
        }
        else{
            dataTablesFrk.table.rows().every(function(rowIdx, tableLoop, rowLoop){
                var data = this.data();
                //console.log(parseInt(data[pkName]) +' === '+ novosDados[pkName]);
                if(parseInt(data[pkName]) === novosDados[pkName]){
                    dataTablesFrk.table.row(rowIdx).data(novosDados);
                }
            });
        }
    },
    /** Atualiza apenas a linha selecionada evitando uma nova requisicao ajax.
     * O uso desta funcao deve combinado com funcao dataTablesFrk.selectItem 
     * que carregarah as propriedades 'table' e 'currentRow'
     * @param {type} frmId - ID do formulario que contem os dados a serem enviados
     * @param {type} callback - funcao, opcional, a ser executada no caso de sucesso.
     * @returns {Boolean}
     */
    updateRow: function(frmId, callback, remove){
        if(typeof controle === 'undefined'){
            alertSW('Arquivo de controle da requisição está indefinido', 'error');
            return false;
        }
        let form = $('#'+ frmId);
        form.find('button[type=submit]').attr('disabled', 'disabled');
        form.find('img.loading').fadeIn();
        $.ajax({
            url: controle,
            method: 'post',
            data: form.serialize(),
            dataType: 'json',
            success: function(result){
                alertSwResult(result);
                if(result.tipo === 'success' && isEmpty(form.find('[name=id_objeto]').val())){
                    limparForm(frmId);
                }            
                if(typeof callback === 'function'){
                    callback(result);
                }
                try{
                    if(!isEmpty(dataTablesFrk.table)){
                        dataTablesFrk.table.ajax.reload();
                    }
                    else if(result.tipo === 'success' && !isEmpty(dataTablesFrk.table) && !isEmpty(dataTablesFrk.currentRow) && typeof remove !== 'undefined'){
                        dataTablesFrk.table.row(dataTablesFrk.currentRow).remove().draw();
                    }
                    else if(result.tipo === 'success' && !isEmpty(dataTablesFrk.table) && !isEmpty(dataTablesFrk.currentRow)){
                        dataTablesFrk.table.row(dataTablesFrk.currentRow).data(result.dados);
                    }
                }
                catch(e){
                    if(result.tipo === 'success' && !isEmpty(dataTablesFrk.table) && !isEmpty(dataTablesFrk.currentRow) && typeof remove !== 'undefined'){
                        dataTablesFrk.table.row(dataTablesFrk.currentRow).remove().draw();
                    }
                    else if(result.tipo === 'success' && !isEmpty(dataTablesFrk.table) && !isEmpty(dataTablesFrk.currentRow)){
                        dataTablesFrk.table.row(dataTablesFrk.currentRow).data(result.dados);
                    }
                }
                /*if(result.tipo === 'success' && !isEmpty(dataTablesFrk.table) && !isEmpty(dataTablesFrk.currentRow) && typeof remove !== 'undefined'){
                    dataTablesFrk.table.row(dataTablesFrk.currentRow).remove().draw();
                }
                else if(result.tipo === 'success' && !isEmpty(dataTablesFrk.table) && !isEmpty(dataTablesFrk.currentRow)){
                    dataTablesFrk.table.row(dataTablesFrk.currentRow).data(result.dados);
                }
                else if(!isEmpty(dataTablesFrk.table)){
                    dataTablesFrk.table.ajax.reload();
                }*/
            },
            complete:function(){
                form.find('button[type=submit]').removeAttr('disabled');
                form.find('img.loading').fadeOut();
            },
            error:function(erro, er){
                var msg = 'Erro ' + erro.status + ' - ' + erro.statusText + ' (Tipo de erro: ' + er +')';
                alert(msg);
            }
        });
        return false;
    },
    removeRow: function(frmId, callback){
        if(typeof controle === 'undefined'){
            alertSW('Arquivo de controle da requisição está idefinido', 'error');
            return false;
        }
        let form = $('#'+ frmId);
        form.find('button[type=submit]').attr('disabled', 'disabled');
        form.find('img.loading').fadeIn();
        $.ajax({
            url: controle,
            method: 'post',
            data: form.serialize(),
            dataType: 'json',
            success: function(result){
                alertSwResult(result);
                if(result.tipo === 'success' && isEmpty(form.find('[name=id_objeto]').val())){
                    limparForm(frmId);
                }            
                if(typeof callback === 'function'){
                    callback(result);
                }
                if(result.tipo === 'success' && !isEmpty(dataTablesFrk.table) && !isEmpty(dataTablesFrk.currentRow)){
                    dataTablesFrk.table.row(dataTablesFrk.currentRow).remove().draw();
                }
            },
            complete:function(){
                form.find('button[type=submit]').removeAttr('disabled');
                form.find('img.loading').fadeOut();
            },
            error:function(erro, er){
                var msg = 'Erro ' + erro.status + ' - ' + erro.statusText + ' (Tipo de erro: ' + er +')';
                alert(msg);
            }
        });
        return false;
    },
    checkAllClicked:function(checkbox, table){
        var checked = checkbox.is(':checked');
        if(checked){
            table.rows({search:'applied'}).select();
        }
        else{
            table.rows({search:'applied'}).deselect();
        }
    },
    deselecRows:function(table){
        table.rows().deselect();
    },
    dropDownLinkEditar: '<a class="dropdown-item p-l-10 p-r-10 linkEditar" href="javascript:;"><i class="bi bi-pencil-square mr-2"></i> Editar</a>',
    
    dropDownLinkExcluir: '<a class="dropdown-item p-l-10 p-r-10 linkExcluir" href="javascript:;"><i class="bi bi-trash3 mr-2"></i>Excluir</a>',
    
    gerarLinkDropdown: function(label, classNames, icone){
        return '<a class="dropdown-item p-l-10 p-r-10 '+ classNames +'" href="javascript:;"><i class="bi '+ icone +' mr-2"></i> '+ label +'</a>';
    },
    getItem: function(value, property){
        let dados = dataTablesFrk.table.rows().data();
        for(let i = 0; i < dados.length; i++){
            //console.log(dados[i][property] +' ('+ typeof(dados[i][property]) +') === '+ value +'('+ typeof(value) +')');
            if(dados[i][property] == value){
                return dados[i];
            }
        }
        return null;
    }
};

function ordenar(a, b) {
    if(typeof campoOrdenacao === 'undefined'){
        alertSW('Uso incorreto da função <b>ordenar</p>. É necessário definir o valor na variável <b>campoOrdenacao</b> para que a função saiba qual propriedade deve ser comparada para a ordenação.', 'error');
        return false;
    }
    if (a[campoOrdenacao] < b[campoOrdenacao]){
        return -1;
    }
    if (a[campoOrdenacao] > b[campoOrdenacao]){
        return 1;
    }
    return 0;
}

function scrollToElement(id){
    let container = $('body');
    let scrollTo = $('#'+ id);
    // Calculating new position of scrollbar
    let position = scrollTo.offset().top - container.offset().top + container.scrollTop();
    // Setting the value of scrollbar
    container.scrollTop(position);
}

function desmarcarCheckbox(checkbox){
    if(checkbox.is(':checked')){
        checkbox.click();
    }
}

function updateSlimscroll(element){
    let height = window.innerHeight - 150;
    element.slimscroll({
        height: height
    });
}

function removerFormatoMoeda(valor){
    if(typeof valor === 'string'){
        valor = valor.replace('R$ ', '').replace('.', '').replace(',', '.');
        return parseFloat(valor).toFixed(2);
    }
    else{
        if( typeof valor === 'number' ){
            return parseFloat(valor).toFixed(2);
        }
        else{
            return 0.00;
        }
    }
}

function formatMoeda(valor){
    return Intl.NumberFormat('pt-br', {style: 'currency', currency: 'BRL'}).format(valor);
}

function appendSubtituloPagina(texto){
    $('#sub-titulo-pagina').append('<i class="bi bi-chevron-double-right mr-2 f-s-16"></i><span class="text-primary">'+ texto +'</span>');
}

function adicionarSubtituloPagina(texto){
    $('#sub-titulo-pagina').html('<i class="bi bi-chevron-double-right mr-2 f-s-16"></i><span class="text-primary">'+ texto +'</span>');
}

function removerSubtitulo(){
    $('#sub-titulo-pagina').text('');
}
function alterarTituloPagina(html){
    $('#titulo-pagina').html(html);
}
function setTituloPagina(texto){
    $('#titulo-pagina').text(texto);
    removerSubtitulo();
}
//usado por exemplo nos arquivos admissao.php e rematricula.php
function enviarEmailsEmLote(instanceForm, instanceTable, pkName){
    
    let f = instanceForm;
    let tb = instanceTable;
    let html = '';
    let item;
    let itens = tb.rows('.selected').data();
    if(itens.length === 0){
        alertSW('É necessário selecionar algum item da lista para continuar.', 'info');
        return;
    }
    for(let i = 0; i < itens.length; i++){
        item = itens[i];
        html += '<input type="hidden" name="_lista_objetos_id[]" value="'+ item[pkName] +'" />';
    }
    $('#containerInscricoesEnviarEmail').html(html);
    let dados = f.serialize();
    f.find('button[type=submit]').addClass('disabled');
    f.find('img.loading').fadeIn();
    requestAjax(dados, function(response){
        alertSwResult(response);
        if(response.dados.hasOwnProperty('ENVIADOS')){
            let enviados = response.dados['ENVIADOS'];
            //comparacao dos id com email enviado para deselecionar na linha
            tb.rows('.selected').every(function(rowIdx, tableLoop, rowLoop){
                let obj = this.data();
                let row = tb.row(rowIdx);
                if(enviados.indexOf(obj[pkName]) !== -1){
                    row.deselect();
                }
            });
        }
        f.find('button[type=submit]').removeClass('disabled');
        f.find('img.loading').fadeOut();
    });
    return false;
}

function MD5(d){var r = M(V(Y(X(d),8*d.length)));return r.toLowerCase()};function M(d){for(var _,m="0123456789ABCDEF",f="",r=0;r<d.length;r++)_=d.charCodeAt(r),f+=m.charAt(_>>>4&15)+m.charAt(15&_);return f}function X(d){for(var _=Array(d.length>>2),m=0;m<_.length;m++)_[m]=0;for(m=0;m<8*d.length;m+=8)_[m>>5]|=(255&d.charCodeAt(m/8))<<m%32;return _}function V(d){for(var _="",m=0;m<32*d.length;m+=8)_+=String.fromCharCode(d[m>>5]>>>m%32&255);return _}function Y(d,_){d[_>>5]|=128<<_%32,d[14+(_+64>>>9<<4)]=_;for(var m=1732584193,f=-271733879,r=-1732584194,i=271733878,n=0;n<d.length;n+=16){var h=m,t=f,g=r,e=i;f=md5_ii(f=md5_ii(f=md5_ii(f=md5_ii(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_ff(f=md5_ff(f=md5_ff(f=md5_ff(f,r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+0],7,-680876936),f,r,d[n+1],12,-389564586),m,f,d[n+2],17,606105819),i,m,d[n+3],22,-1044525330),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+4],7,-176418897),f,r,d[n+5],12,1200080426),m,f,d[n+6],17,-1473231341),i,m,d[n+7],22,-45705983),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+8],7,1770035416),f,r,d[n+9],12,-1958414417),m,f,d[n+10],17,-42063),i,m,d[n+11],22,-1990404162),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+12],7,1804603682),f,r,d[n+13],12,-40341101),m,f,d[n+14],17,-1502002290),i,m,d[n+15],22,1236535329),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+1],5,-165796510),f,r,d[n+6],9,-1069501632),m,f,d[n+11],14,643717713),i,m,d[n+0],20,-373897302),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+5],5,-701558691),f,r,d[n+10],9,38016083),m,f,d[n+15],14,-660478335),i,m,d[n+4],20,-405537848),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+9],5,568446438),f,r,d[n+14],9,-1019803690),m,f,d[n+3],14,-187363961),i,m,d[n+8],20,1163531501),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+13],5,-1444681467),f,r,d[n+2],9,-51403784),m,f,d[n+7],14,1735328473),i,m,d[n+12],20,-1926607734),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+5],4,-378558),f,r,d[n+8],11,-2022574463),m,f,d[n+11],16,1839030562),i,m,d[n+14],23,-35309556),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+1],4,-1530992060),f,r,d[n+4],11,1272893353),m,f,d[n+7],16,-155497632),i,m,d[n+10],23,-1094730640),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+13],4,681279174),f,r,d[n+0],11,-358537222),m,f,d[n+3],16,-722521979),i,m,d[n+6],23,76029189),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+9],4,-640364487),f,r,d[n+12],11,-421815835),m,f,d[n+15],16,530742520),i,m,d[n+2],23,-995338651),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+0],6,-198630844),f,r,d[n+7],10,1126891415),m,f,d[n+14],15,-1416354905),i,m,d[n+5],21,-57434055),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+12],6,1700485571),f,r,d[n+3],10,-1894986606),m,f,d[n+10],15,-1051523),i,m,d[n+1],21,-2054922799),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+8],6,1873313359),f,r,d[n+15],10,-30611744),m,f,d[n+6],15,-1560198380),i,m,d[n+13],21,1309151649),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+4],6,-145523070),f,r,d[n+11],10,-1120210379),m,f,d[n+2],15,718787259),i,m,d[n+9],21,-343485551),m=safe_add(m,h),f=safe_add(f,t),r=safe_add(r,g),i=safe_add(i,e)}return Array(m,f,r,i)}function md5_cmn(d,_,m,f,r,i){return safe_add(bit_rol(safe_add(safe_add(_,d),safe_add(f,i)),r),m)}function md5_ff(d,_,m,f,r,i,n){return md5_cmn(_&m|~_&f,d,_,r,i,n)}function md5_gg(d,_,m,f,r,i,n){return md5_cmn(_&f|m&~f,d,_,r,i,n)}function md5_hh(d,_,m,f,r,i,n){return md5_cmn(_^m^f,d,_,r,i,n)}function md5_ii(d,_,m,f,r,i,n){return md5_cmn(m^(_|~f),d,_,r,i,n)}function safe_add(d,_){var m=(65535&d)+(65535&_);return(d>>16)+(_>>16)+(m>>16)<<16|65535&m}function bit_rol(d,_){return d<<_|d>>>32-_}

Number.prototype.formatMoney = function(places, symbol, thousand, decimal) {
    places = !isNaN(places = Math.abs(places)) ? places : 2;
    symbol = symbol !== undefined ? symbol : "$";
    thousand = thousand || ",";
    decimal = decimal || ".";
    var number = this, 
        negative = number < 0 ? "-" : "",
        i = parseInt(number = Math.abs(+number || 0).toFixed(places), 10) + "",
        j = (j = i.length) > 3 ? j % 3 : 0;
    return symbol + negative + (j ? i.substr(0, j) + thousand : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousand) + (places ? decimal + Math.abs(number - i).toFixed(places).slice(2) : "");
};

const parseJwt = (token) => {
    try {
        return JSON.parse(atob(token.split('.')[1]));
    } catch (e) {
        return null;
    }
};

var AnoLetivoSession = {
    /**
     * Obtém o ano letivo selecionado na caixa de seleção ano_letivo_session
     * @returns {jQuery}
     */
    obterAno: function(){
        return $('#ano_letivo_session').selectpicker('val');
    },
    /**
     * Altera o ano letivo na sessão e recarrega a página atual
     * @returns {undefined}
     */
    alterarAno: function(callback, reloadPage){
        let pergunta = 'Confirma a alteração do ano letivo?';
        if(reloadPage){
            pergunta += '</br>Isso recarregará a página atual.';
        }
        confirmSW(pergunta, function(){
            let dados = {
                objeto: 'Session',
                acao: 'adicionar_sessao',
                session_var_name: 'ano_letivo_session',
                session_var_value: $('#ano_letivo_session').selectpicker('val')
            };
            requisicaoAjax(dados, function(response){
                if(typeof callback === 'function'){
                    callback();
                }
                if(response.tipo === 'success' && reloadPage){
                    $('.menu-lateral a.active').click();
                }
            }, null, null, '../adm/controle/default_control.php');
        });
    },
    /**
     * Adiciona o campo do ano letivo no formulario informado
     * @param {type} f instância do formulário a ser adicionado o campo para o ano da sessão
     * @returns {undefined}
     */
    adicionarNoForm:function(f){
        //adiciona o campo caso nao exista
        if(!f.find('input[name="ano_letivo_session"]').length){
            f.append('<input type="hidden" name="ano_letivo_session" value="" />');
        }
        let ano = this.obterAno();
        //carrega o ano letivo no campo do formulario
        f.find('input[name="ano_letivo_session"]').val(ano);
    },
    /**
     * Exibe a caixa de seleção do ano letivo
     * @returns {undefined}
     */
    exibirCaixaSelecao:function(){
        $('#containerAnoLetivoSession').slideDown();
    }
};

function ajustarCodificacao(str){
    console.log(str);
    let search = ['Ã�', 'Ãƒ'];
    let replace = ['Í', 'Ã'];
    let indice = search.indexOf(str);
    
    if(indice !== -1){
        console.log(search[indice]);
        return str.replace(search[indice], replace[indice]);
    }
    return str;
    
    
}

function getPopover(texto, posicao){
    let pos = (typeof posicao === 'undefined') ? 'left' : posicao;
    let saida = ' data-toggle="popover" data-placement="'+ pos +'" data-content="'+ texto +'"';
    return saida;
}

function gravarParametro(name, value){
    let dados = {
        objeto: 'Parametro',
        acao: 'execute',
        metodo: 'gravar',
        tx_nome: name,
        tx_valor: value
    };
    requestAjax(dados, function(response){
        alertSwResult(response);
    });
    return false;
}

function getParametro(name, callback){
    let dados = {
        objeto: 'Parametro',
        acao: 'pesquisar',
        'filtros[IGUAL][tx_nome]': name
    };
    requestAjax(dados, function(response){
        let saida = null;
        if(response.tipo === 'success' && response.dados.length ){
            saida = JSON.parse(response.dados[0]['tx_valor']);
        }
        if(typeof(callback) === 'function'){
            callback(saida);
        }
    });
}

function setTitleApp(html){
    $('#title-app').html(html).css({'padding-bottom':'none'}).removeClass('pb-2');
}

function checkFileExists(url, callback) {
    $.ajax({
        url: url,
        type: 'HEAD',
        success: function () {
            callback(true); // File exists
        },
        error: function () {
            callback(false); // File does not exist or an error occurred
        }
    });
}