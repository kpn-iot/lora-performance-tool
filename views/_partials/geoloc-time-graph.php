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
/* @var $frameCollection app\models\lora\FrameCollection */

if ($frameCollection->geoloc->nrMeasurements === 0) {
  return;
}
?>

<script>
  google.charts.load('current', {packages: ['corechart', 'line']});
  google.charts.setOnLoadCallback(drawThis);
  var graphs = <?= json_encode($frameCollection->geoloc->timeGraphs); ?>;
  function drawThis() {

    var data = new google.visualization.DataTable();
    data.addColumn('datetime', 'Timestamp');
    for (i in graphs.columns) {
      data.addColumn('number', graphs.columns[i]);
    }

    var rows = [], dateTimeTemp;
    for (i in graphs.lines) {
      graphs.lines[i][0] = graphs.lines[i][0].replace(' ', 'T');
      dateTimeTemp = (graphs.lines[i][0].substr(-1) === 'Z') ? graphs.lines[i][0] : graphs.lines[i][0] + "Z";
      graphs.lines[i][0] = new Date(dateTimeTemp);
      rows.push(graphs.lines[i]);
    }
    data.addRows(rows);

    var options = {
      title: 'LocSolve accuracy over time',
      height: 300,
      width: '100%',
      legend: {
        position: 'bottom'
      },
      chartArea: {
        left: 80,
        top: 35,
        right: 10,
        height: 200
      },
      vAxis: {title: 'Accuracy [m]', minValue: 0},
      pointSize: 5,
      backgroundColor: 'transparent'
    };

    var chart = new google.visualization.LineChart(document.getElementById('line_div'));

    chart.draw(data, options);
  }
</script>
<div id="line_div"></div>
