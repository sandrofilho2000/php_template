const language_url = '../../assets/plugins/DataTables-bootstrap4/json/jquery.datatables.lang.pt_br.json';
const controle = 'controle/default_control.php';


function array_move(arr, old_index, new_index) {
  while (old_index < 0) {
    old_index += arr.length;
  }
  while (new_index < 0) {
    new_index += arr.length;
  }
  if (new_index >= arr.length) {
    var k = new_index - arr.length + 1;
    while (k--) {
      arr.push(undefined);
    }
  }
  arr.splice(new_index, 0, arr.splice(old_index, 1)[0]);
  return arr; // for testing purposes
};

function getNumPresencasFaltasGeral() {
  if ($("#dt_filtro_inicio").val() > $("#dt_filtro_fim").val()) {
    alert("Por favor, verifique as datas e tente novamente!");
    $("#tbLista").dataTable().fnDestroy();
    $("#tbLista").hide();
    return;
  }

  let grau_serie = JSON.parse($("#grau_serie").val() || '{}');
  
  $("#nr_serie").val(grau_serie?.serie || '');
  $("#tx_grau").val(grau_serie?.grau || '');

  let f = $('#frmPesquisa');
  let dados = f.serialize();
  let grafico_dados = [];

  requestAjax(dados, function (response) {
    let grau_series = response.grau_series || [];
    if (!response.parcial_dias_letivos) {
      alert("Não há dias letivos nesse período!");
      $("#dt_filtro_inicio").val('');
      $("#dt_filtro_fim").val('');
      setFiltersMaxMinDate($("#dt_ano_letivo").val());
      return;
    }

    grau_series.forEach(item => {
      let turma = $("#tx_turma").val() || '';
      let { grau_serie } = item;
      let grau_serie_turma = `${grau_serie}${turma}`;
      let list = [];

      list[0] = turma ? grau_serie_turma : grau_serie;
      list[1] = Number(item.presencas);
      list[2] = Number(item.faltas);
      grafico_dados.push(list);
    });
  
  

    if (!grafico_dados.length) {
      alert("Dados não encontrados!");
      $("#tbLista").dataTable().fnDestroy();
      $("#tbLista").hide();
      return;
    }


    drawGrauSeriesChart(grafico_dados);
  });
};

function setFiltersMaxMinDate(dt_ano_letivo) {
  if (!dt_ano_letivo) return;

  let dt_filtro_inicio = $("#dt_filtro_inicio").val();
  let dt_filtro_fim = $("#dt_filtro_fim").val();

  let dataObject = {};
  dataObject['acao'] = 'pesquisar';
  dataObject['objeto'] = 'DiarioControle';
  dataObject['metodo'] = 'getDadosAnoLetivo';
  dataObject['data_alvo'] = dt_filtro_inicio;

  dt_filtro_inicio = dt_filtro_inicio.split("-");
  dt_filtro_inicio[0] = dt_ano_letivo;
  dt_filtro_inicio = dt_filtro_inicio.join('-');

  dt_filtro_fim = dt_filtro_fim.split("-");
  dt_filtro_fim[0] = dt_ano_letivo;
  dt_filtro_fim = dt_filtro_fim.join('-');

  $("#dt_filtro_inicio").val(dt_filtro_inicio);
  $("#dt_filtro_fim").val(dt_filtro_fim);

  requestAjax(dataObject, function(response) {
    const dados = response['dados'] || [];
    const anoLetivo = dados.filter(item => item.anoLetivo == dt_ano_letivo)[0];

    if (anoLetivo) {
      $("#dt_filtro_inicio").attr('min', anoLetivo['inicioAulas']).attr('max', anoLetivo['terminoAulas']);
      $("#dt_filtro_fim").attr('min', anoLetivo['inicioAulas']).attr('max', anoLetivo['terminoAulas']);

      if (dt_filtro_inicio < anoLetivo['inicioAulas']) {
        $("#dt_filtro_inicio").val(anoLetivo['inicioAulas']);
      }

      if (dt_filtro_inicio > anoLetivo['terminoAulas']) {
        $("#dt_filtro_inicio").val(anoLetivo['terminoAulas']);
      }

      if (dt_filtro_fim < anoLetivo['inicioAulas']) {
        $("#dt_filtro_fim").val(anoLetivo['inicioAulas']);
      }

      if (dt_filtro_fim > anoLetivo['terminoAulas']) {
        $("#dt_filtro_fim").val(anoLetivo['terminoAulas']);
      }
    } else {
      $("#dt_filtro_inicio").attr('min', anoLetivo['inicioAulas']).attr('max', anoLetivo['terminoAulas']);
      $("#dt_filtro_fim").attr('min', anoLetivo['inicioAulas']).attr('max', anoLetivo['terminoAulas']);
    }
  });
}

function drawGrauSeriesChart(grafico_dados = []) {
  if ($("#tbLista tbody").length) {
    if ($("#tx_grau").val() && $("#nr_serie").val() || $("#tx_turma").val()) {
      getNumPresencasFaltasPorGrauSerie($("#tx_grau").val(), $("#nr_serie").val(), $("#tx_turma").val());
    }
  }

  if (!grafico_dados.length) {
    return;
  }

  var data = google.visualization.arrayToDataTable([
    ['Turma', 'Presenças', { role: 'annotation' }, 'Faltas', { role: 'annotation' }],
    ...grafico_dados.map(([turma, presencas, faltas]) => [turma, presencas, presencas.toString(), faltas, faltas.toString()])
  ]);

  var view = new google.visualization.DataView(data);
  view.setColumns([0, 1, 2, 3, 4]);

  var options = {
    isStacked: 'percent',
    height: 750,
    width: $(".graph-grau-serie-container").width(),
    legend: { position: 'top', maxLines: 3 },
    colors: ['#63ADF2', '#F26B63'],
    hAxis: {
      minValue: 0,
      ticks: [0, .25, .5, .75, 1]
    },
    vAxis: {
      title: 'Frequência'
    },
    annotations: {
      alwaysOutside: false,
      textStyle: {
        fontSize: 14,
        bold: false,
        color: '#eee'
      }
    }
  };

  try{
    const graphContainer = document.getElementById("barchart-grau-serie-values")
  
    var chart = new google.visualization.BarChart(graphContainer);
    chart.draw(view, options);
  
    google.visualization.events.addListener(chart, 'select', function() {
      var selection = chart.getSelection();
  
      if (selection.length > 0) {
        var selectedItem = selection[0];
        var row = selectedItem.row;
        var column = selectedItem.column;
  
        if (row !== null) {
          var turma = data.getValue(row, 0);
  
          $("#tbLista").removeClass("presencas faltas");
  
          if (column === 1) {
            $("#tbLista").addClass("presencas");
            $('#order_table_by').val('presencas');
          } else if (column === 3) {
            $("#tbLista").addClass("faltas");
            $('#order_table_by').val('faltas');
          }
  
          var grau = turma.charAt(0);
          var serie = turma.charAt(1);
  
          getNumPresencasFaltasPorGrauSerie(grau, serie);
        }
      }
    });

    addSumOfRowsToGraph();
  
    $("#downloadChart").unbind().click(function(e) {
      e.preventDefault();
      var imgUri = chart.getImageURI();
      var link = document.createElement("a");
      link.href = imgUri;
      let file_name = `Frequencia (${$('#dt_filtro_inicio').val()}_${$('#dt_filtro_fim').val()})`;
  
      if ($("#grau_serie").val() || $("#tx_turma").val()) {
        let grau_serie = $("#grau_serie").val() ? JSON.parse($("#grau_serie").val()) : {};
        let grau = grau_serie['grau'] || '';
        let serie = grau_serie['serie'] || '';
  
        file_name += ` - ${grau}${serie} ${$("#tx_turma").val()}`;
      }
  
      file_name += '.png';
      link.download = file_name;
      link.click();
    });
  
  }catch(e){
    console.error("Error: ", e)
  }
}

function fixGoogleVisualizationTooltip() {
  const GoogleVisualizationTooltip = document.querySelector(".google-visualization-tooltip");
  if (GoogleVisualizationTooltip) {
    $(".total_span").hide();
  } else {
    $(".total_span").show();
  }
}

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

function addSumOfRowsToGraph() {
  const graph = document.querySelector("#barchart-grau-serie-values");
  if (!graph) return;

  const dataContainer = graph.querySelectorAll("g")[5];
  if (!dataContainer) return;

  const numberContainers = dataContainer.querySelectorAll("text");
  const texts = [];

  numberContainers.forEach((item) => {
    const content = item.textContent?.trim();
    if (content && !isNaN(content) && item.getAttribute("aria-hidden") !== "true") {
      texts.push(item);
    }
  });

  const halfLength = texts.length > 3 ? Math.floor(texts.length / 2) : 1;

  for (let i = 0; i < halfLength; i++) {
    const presencasText = texts[i]?.textContent || "0";
    const faltasText = texts[i + halfLength]?.textContent || "0";

    const presencas = Number(presencasText);
    const faltas = Number(faltasText);
    const total = presencas + faltas;

    const totalSpan = document.createElement("span");
    totalSpan.innerText = total;
    totalSpan.classList.add("total_span", `total_span_${i}`);
    totalSpan.style.position = "absolute";
    totalSpan.style.right = "12%";

    const y = texts[i + halfLength]?.getAttribute("y");
    if (y) {
      totalSpan.style.top = `${Number(y) - 16}px`;
      graph.appendChild(totalSpan);
    }
  }
}

function getNumPresencasFaltasPorGrauSerie(grau, serie) {
  let f = $('#frmPesquisa');
  let data = f.serializeArray();
  
  let dataObject = {};
  data.forEach(function(item) {
    dataObject[item.name] = item.value;
  });

  dataObject['metodo'] = 'getNumPresencasFaltasPorGrauSerie';
  dataObject['tx_grau'] = grau;
  dataObject['nr_serie'] = serie;

  if ($("#tx_turma").val()) {
    dataObject['tx_turma'] = $("#tx_turma").val();
  }

  $("#tbLista").show();
  $("#tbLista").dataTable().fnDestroy();
  $('#tbLista').DataTable({
    lengthMenu: [10, 20, 25, 50, 100, 500],
    pageLength: 20,
    language: { url: language_url },
    processing: true,
    order: [[5, 'desc']],
    ajax: {
      cache: false,
      url: controle,
      type: 'post',
      dataSrc: 'dados',
      data: dataObject
    },
    columns: [
      { 
        data: 'tx_aluno_img', 
        orderable: false,
        searchable: false,
        className: 'td-img',
        render: function(data, type, row, meta) {
          console.log("🚀 ~ getNumPresencasFaltasPorGrauSerie ~ row:", row)

          let img = `<img src="https://www.csanl.com.br/secretaria/online/_alunos_hires/${row.nr_matricula}.jpg" width="30" height="30" style="max-height: 40px; border-radius: 50%; max-width: 40px;min-width: 40px;min-height: 40px;object-fit: cover;">`;
          
          return img;
        }
      },
      { 
        data: 'tx_aluno_nome',
        className: 'td-tx_aluno_nome',
      },
      { data: 'nr_matricula', className: 'td-nr_matricula' }, 
      { data: 'tx_aluno_cpf', className: 'td-tx_aluno_cpf' }, 
      { 
        data: 'grau_serie_turma',
        className: 'td-grau_serie_turma',
        render: function(data, type, row, meta) {
          return row.grau_serie_turma || row.grau_serie;
        }
      }, 
      { data: 'presencas', className: 'td-presencas' },
      { data: 'faltas', className: 'td-faltas' },
      {
        data: null,
        render: function(data, type, row, meta) {
          return '<button type="button" class="btn btn-sm btn-info btn-selecionar-aluno f-s-10"><i class="bi bi-check mr-1"></i>SELECIONAR</button>';
        }
      },
    ],
    drawCallback: function () {
      $(".foto-funcionario").each(function () {
          obterFoto(this);
      });
    }
  });

  $("#downloadTable").prop("disabled", false);
  $('#tbLista tbody').unbind().on('click', 'tr', displayAlunoPopUp);
}

$(document).ready(function() {
  google.charts.load("current", { packages: ["corechart"] });

  getNumPresencasFaltasGeral();

  $('#frmPesquisa').submit(function(e) {
    e.preventDefault();
    getNumPresencasFaltasGeral();
  });

  $("#downloadTable").unbind().click(function(e) {
    e.preventDefault();

    if (!$("#tbLista tbody tr").length) {
      alert("Não há dados para serem exibidos!");
      $("#downloadTable").prop("disabled", true);
      return;
    }

    if ($.fn.DataTable.isDataTable("#tbLista")) {
      var table = $("#tbLista").DataTable();
      table.page.len(-1).draw();
    }

    var rows = [];
    var header = [];

    $('#tbLista thead tr th:not(.btn-select)').each(function() {
      if ($(this).text()) {
        header.push($(this).text());
      }
    });
    rows.push(header);

    $('#tbLista tbody tr').each(function() {
      var row = [];
      const columns = ['tx_aluno_nome', 'presencas', 'tx_aluno_cpf', 'grau_serie_turma', 'nr_matricula', 'faltas', 'turma'];

      columns.forEach((item) => {
        try {
          let text = $(this).find(`.td-${item}`).text().split("SAIU EM")[0];
          text = $(this).find(`.td-${item}`).text().split("ENTROU EM")[0];
          row.push(text);
        } catch(e) {
          console.log("Erro ao acessar coluna: ", item);
        }
      });

      row = array_move(row, 4, 1);
      row = array_move(row, 2, 4);
      rows.push(row);
    });

    var ws = XLSX.utils.aoa_to_sheet(rows);
    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Tabela');

    let file_name = `Frequencia (${$('#dt_filtro_inicio').val()}_${$('#dt_filtro_fim').val()}).xlsx`;

    XLSX.writeFile(wb, file_name);

    if ($.fn.DataTable.isDataTable("#tbLista")) {
      table.page.len(10).draw();
    }
  });

  $("#dt_ano_letivo").change((e) => {
    setFiltersMaxMinDate(e.target.value);
  });

  $('#frmPesquisa').on('reset', function() {
    setTimeout(() => {
      setFiltersMaxMinDate($("#dt_ano_letivo").val());
    }, 40);
  });

  $("#barchart-grau-serie-values").on("mousemove", fixGoogleVisualizationTooltip);
});
