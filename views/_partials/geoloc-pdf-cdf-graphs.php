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
?>
<?php if ($stats->nrMeasurements === 0): ?>
  <div class="alert alert-danger text-center hidden-print">
    <p class="lead">No frames available to calculate accuracy with!</p>
  </div>
<?php else: ?>
  <h3>Average GeoLoc accuracy: <?= Yii::$app->formatter->asDecimal($stats->average, 1) ?>m 
    <small><?= $stats->nrLocalisations ?> LocSolves, <?= Yii::$app->formatter->asDecimal($stats->percentageNrLocalisations * 100, 1) ?>%</small></h3>
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
      vAxis: {title: 'Occurance [#]'},
      backgroundColor: 'transparent'
    };

    var cdfGraph = <?= json_encode($stats->cdf); ?>;
    var cdfOptions = {
      title: 'Cumulative LocSolve cdf',
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
      vAxis: {title: 'Cumulative occurance [%]'},
      backgroundColor: 'transparent'
    };

    function drawGraphs() {

      var pdfData = new google.visualization.DataTable();
      pdfData.addColumn('string', 'Distance');
      pdfData.addColumn('number', 'Occurance');

      var pdfRows = [];
      for (i in pdfGraph) {
        pdfRows.push([i, pdfGraph[i]]);
      }
      pdfData.addRows(pdfRows);

      var pdfChartDiv = document.getElementById('pdf_graph');
      var pdfChart = new google.visualization.ColumnChart(pdfChartDiv);
      pdfChart.draw(pdfData, pdfOptions);


      var cdfData = new google.visualization.DataTable();
      cdfData.addColumn('number', 'Distance');
      cdfData.addColumn('number', 'Cum. occurance');

      var cdfRows = [];
      for (i in cdfGraph) {
        cdfRows.push([cdfGraph[i]['x'], cdfGraph[i]['y']]);
      }
      cdfData.addRows(cdfRows);

      var cdfChartDiv = document.getElementById('cdf_graph');
      var cdfChart = new google.visualization.LineChart(cdfChartDiv);
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
