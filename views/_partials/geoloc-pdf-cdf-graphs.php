<?php
/*  _  __  ____    _   _ 
 * | |/ / |  _ \  | \ | |
 * | ' /  | |_) | |  \| |
 * | . \  |  __/  | |\  |
 * |_|\_\ |_|     |_| \_|
 * 
 * (c) 2017 KPN
 * License: GNU General Public License v3.0
 * Author: Paul Marcelis
 * 
 */

/* @var $this yii\web\View */
/* @var $stats \app\components\GeolocStats */

if (!isset($makePNG)) {
  $makePNG = false;
}
?>
<?php if ($stats->nrMeasurements === 0): ?>
  <div class="alert alert-danger text-center hidden-print">
    <p class="lead">No frames available to calculate accuracy with!</p>
  </div>
<?php else: ?>
  <h3>LocSolve Accuracy Median: <?= Yii::$app->formatter->asDistance($stats->median) ?> 
    <small>
      Average: <?= Yii::$app->formatter->asDistance($stats->average) ?> - 
      90% under: <?= Yii::$app->formatter->asDistance($stats->perc90point) ?> | 
      <?= Yii::$app->formatter->asDecimal($stats->nrLocalisations, 0) ?> LocSolves - 
      <?= Yii::$app->formatter->asDecimal($stats->percentageNrLocalisations * 100, 1) ?>% success
    </small>
  </h3>
  <script>
    google.charts.load('current', {packages: ['corechart', 'bar']});
    google.charts.setOnLoadCallback(drawGraphs);

    var pdfGraph = <?= json_encode($stats->pdf) ?>;
    var pdfOptions = {
      title: 'LocSolve accuracy pdf',
      height: 400,
      bar: {groupWidth: "95%"},
      colors: ['#4CAF50'],
      legend: {
        position: 'none'
      },
      chartArea: {
        left: 60,
        top: 35,
        right: 10,
        height: 295
      },
      vAxis: {title: 'Occurrence [%]', minValue: 0, maxValue: 100},
      backgroundColor: 'transparent',
      tooltip: {isHtml: true}
    };

    var cdfGraph = <?= json_encode($stats->cdf); ?>;
    var cdfOptions = {
      title: 'LocSolve accuracy cdf',
      height: 400,
      colors: ['#4CAF50'],
      legend: {
        position: 'none'
      },
      chartArea: {
        left: 60,
        top: 35,
        right: 10,
        height: 295
      },
      hAxis: {title: 'Accuracy [m]', minValue: 0},
      vAxis: {title: 'Occurrence [%]', maxValue: 100},
      backgroundColor: 'transparent',
      tooltip: {isHtml: true}
    };

    function drawGraphs() {

      var pdfData = new google.visualization.DataTable();
      pdfData.addColumn('string', 'Distance');
      pdfData.addColumn('number', 'Occurance');
      pdfData.addColumn({type: 'string', role: 'tooltip', p: {html: true}});

      var pdfRows = [];
      for (i in pdfGraph) {
        pdfRows.push([i, pdfGraph[i], '<div style="padding:5px;max-width:140px"><b>' + pdfGraph[i] + '%</b> is in the <br />range <b>' + i + '</b></div>']);
      }
      pdfData.addRows(pdfRows);

      var pdfChartDiv = document.getElementById('pdf_graph');
      var pdfChart = new google.visualization.ColumnChart(pdfChartDiv);

  <?php if ($makePNG): ?>
        google.visualization.events.addListener(pdfChart, 'ready', function () {
          pdfChartDiv.innerHTML = '<img src="' + pdfChart.getImageURI() + '">';
        });
  <?php endif ?>

      pdfChart.draw(pdfData, pdfOptions);


      var cdfData = new google.visualization.DataTable();
      cdfData.addColumn('number', 'Distance');
      cdfData.addColumn('number', 'Cum. Occurrence');
      cdfData.addColumn({type: 'string', role: 'tooltip', p: {html: true}});

      var cdfRows = [];
      for (i in cdfGraph) {
        cdfRows.push([cdfGraph[i]['x'], cdfGraph[i]['y'], '<div style="padding:5px;max-width:140px"><b>' + (Math.round(100*cdfGraph[i]['y'])/100) + '%</b> is under <b>' + cdfGraph[i]['x'] + 'm</b>']);
      }
      cdfData.addRows(cdfRows);

      var cdfChartDiv = document.getElementById('cdf_graph');
      var cdfChart = new google.visualization.LineChart(cdfChartDiv);

  <?php if ($makePNG): ?>
        google.visualization.events.addListener(cdfChart, 'ready', function () {
          cdfChartDiv.innerHTML = '<img src="' + cdfChart.getImageURI() + '">';
        });
  <?php endif ?>

      cdfChart.draw(cdfData, cdfOptions);
    }
  </script>
  <div style="display:inline-block;width:47%">
    <div id="pdf_graph"></div>
  </div>
  <div style="display:inline-block;width:47%;float:right">
    <div id="cdf_graph"></div>
  </div>
<?php endif ?>
