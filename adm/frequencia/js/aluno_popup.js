function addSumOfRowsToStudentGraph() {
    const graph = document.querySelector("#barchart-aluno-values");
    const numberContainers = Array.from(graph.querySelectorAll("text"));
    
    const validTexts = numberContainers.filter(item =>
        !isNaN(item.textContent) && item.getAttribute("aria-hidden") !== 'true'
    );

    const half = validTexts.length > 3 ? validTexts.length / 2 : 1;

    for (let i = 0; i < half; i++) {
        const presencas = Number(validTexts[i].textContent);
        const faltas = Number(validTexts[i + half].textContent);
        const total = presencas + faltas;

        const totalSpan = document.createElement("span");
        totalSpan.innerText = total;
        totalSpan.className = `total_span total_span_${i}`;
        totalSpan.style.position = "absolute";
        totalSpan.style.right = "20px";
        totalSpan.style.top = `${Number(validTexts[i + half].getAttribute("y")) - 16}px`;

        graph.appendChild(totalSpan);
    }
}

function drawAlunosChart(grafico_dados = []) {  
    if (!grafico_dados.length) return;

    const data = google.visualization.arrayToDataTable([
        ['Aluno', 'Presenças', { role: 'annotation' }, 'Faltas', { role: 'annotation' }],
        ...grafico_dados.map(([aluno, presencas, faltas]) => [
            aluno, presencas, presencas.toString(), faltas, faltas.toString()
        ]),
    ]);
  
    const view = new google.visualization.DataView(data);
    view.setColumns([0, 1, 2, 3, 4]);
  
    const options = {
        isStacked: 'percent',
        height: 80,
        width: $(".graph-aluno-container").width(),
        legend: { position: 'top', maxLines: 3 },
        colors: ['#63ADF2', '#F26B63'],
        hAxis: {
            minValue: 0,
            ticks: [0, 0.25, 0.5, 0.75, 1]
        },
        vAxis: { title: 'Frequência' },
        annotations: {
            alwaysOutside: false,
            textStyle: {
                fontSize: 14,
                bold: false,
                color: '#eee'
            }
        }
    };

    const chart = new google.visualization.BarChart(
        document.getElementById("barchart-aluno-values")
    );
    chart.draw(view, options);
    google.visualization.events.addListener(chart, 'select', function() {
      var selection = chart.getSelection();
  
      if (selection.length > 0) {
        var selectedItem = selection[0];
        var row = selectedItem.row;
        var column = selectedItem.column;
  
        if (row !== null) {  
          if (column === 1) {
            drawAlunoTable("presencas")
            
        } else if (column === 3) {
            drawAlunoTable("faltas")
          }
        }
      }
    });
    addSumOfRowsToStudentGraph();
}

function FormataStringData(data) {
    const [ano, mes, dia] = data.split("-");
    return `${dia}/${("0" + mes).slice(-2)}/${ano}`;
}

function displayAlunoPopUp() {
    const table = $('#tbLista').DataTable(); 
    const row = $(this).closest('tr');
    const data = table.row(row).data(); 

    const dt_filtro_inicio = FormataStringData($("#dt_filtro_inicio").val());
    const dt_filtro_fim = FormataStringData($("#dt_filtro_fim").val());

    $("#small_dt_filtro_inicio").text(dt_filtro_inicio);
    $("#small_dt_filtro_fim").text(dt_filtro_fim);

    const dt_matricula = data['dt_matricula'] || $("#dt_matricula").val();
        
    const tx_aluno_nome = data['tx_aluno_nome'].split("<label")[0];
    const nr_matricula = data['nr_matricula'];
    const tx_aluno_img = `https://www.csanl.com.br/secretaria/online/_alunos_hires/${nr_matricula}.jpg`
    const tx_aluno_cpf = data['tx_aluno_cpf'];
    const grau_serie = data['grau_serie_turma'];

    $("#nr_matricula").val(nr_matricula);
    $("#dt_matricula").val(dt_matricula);
    $(".modal-content #tx_aluno_nome").text(tx_aluno_nome);
    $(".modal-content #nr_matricula").text(nr_matricula);
    $(".modal-content #tx_cpf_atendido").text(tx_aluno_cpf);
    $(".modal-content #tx_grau_serie").text(grau_serie);
    $(".modal-content #img-cadastro").attr("src", tx_aluno_img);

    

    const dataObject = {
        ...Object.fromEntries($('#frmPesquisa').serializeArray().map(({ name, value }) => [name, value])),
        metodo: 'getNumPresencasFaltasPorAluno',
        nr_matricula,
        dt_matricula,
    };

    $.fn.dataTable.moment('DD/MM/YYYY');

    requestAjax(dataObject, ({ aluno, dados: presencas, faltas }) => {
        let presencas_filterd = presencas.filter(item=>{
            return !item.falta
        })
        
        $("#alunoPresencas").dataTable().fnDestroy();
        $("#alunoPresencas").DataTable({
            pageLength: 10,
            searching: false,
            lengthChange: false,
            language: { url: language_url },
            processing: true,
            order: [[0, 'desc']],
            data: presencas,
            columns: [
                {
                    data: 'dt_entrada',
                    className: 'td-dt_hora_entrada',
                    render(data, type) {
                        return type === 'display' || type === 'filter'
                            ? moment(data, "DD/MM/YYYY").format("DD/MM/YYYY")
                            : moment(data, "DD/MM/YYYY").valueOf();
                    }
                },
                { data: 'horario_entrada', className: 'td-horario_entrada',                    
                    render(data, type, row) {
                        return row.falta ? `<span style="color: red; font-weight: bold">FALTA</span>` : data
                    } },
                { data: 'horario_saida', className: 'td-horario_saida',                    
                    render(data, type, row) {
                        return row.falta ? `<span style="color: red; font-weight: bold">FALTA</span>` : data
                    } 
                }
                 
            ]
        });

        drawAlunosChart([[tx_aluno_nome, presencas_filterd.length, faltas]]);
        $("#modalAlunoDetalhado").modal('show');
    });
}

function drawAlunoTable(togglePresencasFaltas=false){
    console.log("🚀 ~ drawAlunoTable ~ togglePresencasFaltas:", togglePresencasFaltas)
    const nr_matricula = $("#nr_matricula").val();
    const dt_matricula = $("#dt_matricula").val();
    const dt_exclusao = $("#dt_exclusao").val();

    const dataObject = {
        ...Object.fromEntries($('#frmPesquisa').serializeArray().map(({ name, value }) => [name, value])),
        metodo: 'getNumPresencasFaltasPorAluno',
        nr_matricula,
        dt_matricula,
        dt_exclusao
    };

    $.fn.dataTable.moment('DD/MM/YYYY');

    requestAjax(dataObject, ({ dados: presencas }) => {
        if(togglePresencasFaltas == "presencas"){
            presencas = presencas.filter(item=>{
                return !item.falta
            })
        }else if(togglePresencasFaltas == "faltas"){
            presencas = presencas.filter(item=>{
                return item.falta
            })
        }
        
        $("#alunoPresencas").dataTable().fnDestroy();
        $("#alunoPresencas").DataTable({
            pageLength: 10,
            searching: false,
            lengthChange: false,
            language: { url: language_url },
            processing: true,
            order: [[0, 'desc']],
            data: presencas,
            columns: [
                {
                    data: 'dt_entrada',
                    className: 'td-dt_hora_entrada',
                    render(data, type) {
                        return type === 'display' || type === 'filter'
                            ? moment(data, "DD/MM/YYYY").format("DD/MM/YYYY")
                            : moment(data, "DD/MM/YYYY").valueOf();
                    }
                },
                { data: 'horario_entrada', className: 'td-horario_entrada',                    
                    render(data, type, row) {
                        return row.falta ? `<span style="color: red; font-weight: bold">FALTA</span>` : data
                    } },
                { data: 'horario_saida', className: 'td-horario_saida',                    
                    render(data, type, row) {
                        return row.falta ? `<span style="color: red; font-weight: bold">FALTA</span>` : data
                    } 
                }
                 
            ]
        });
    });
}

$(document).ready(() => {
    google.charts.setOnLoadCallback(drawAlunosChart);
});