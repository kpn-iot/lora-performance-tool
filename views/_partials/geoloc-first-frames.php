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
?>
<script>
  google.charts.load('current', {packages: ['corechart', 'line']});
  google.charts.setOnLoadCallback(drawAvgDistances);
  var avgDistances = <?= json_encode($avgDistances); ?>;

  function drawAvgDistances() {
    var data = new google.visualization.DataTable();
    data.addColumn('number', 'First frame nr');
    data.addColumn('number', 'Average accuracy');

    var rows = [];
    for (i in avgDistances) {
      var newRow = [parseInt(i) + 1, avgDistances[i]];
      rows.push(newRow);
    }
    data.addRows(rows);

    var options = {
      title: 'LocSolve accuracy for first frames',
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
      vAxis: {title: 'Accuracy [m]'},
      pointSize: 5,
      backgroundColor: 'transparent'
    };

    var chart = new google.visualization.LineChart(document.getElementById('avg_div'));

    chart.draw(data, options);
  }
</script>
<div id="avg_div"></div>
