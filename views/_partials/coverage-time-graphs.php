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

/* @var $frameCollection app\models\lora\FrameCollection */
?>
<script>
  var graphs = <?= json_encode($frameCollection->coverage->graphs) ?>;
  var timeline = <?= json_encode($frameCollection->coverage->timeline) ?>;

  google.charts.load('current', {packages: ['corechart', 'bar', 'line']});
  google.charts.setOnLoadCallback(function () {
    var data, rows, i, chart;
    var lineOptions = {
      height: 300,
      width: '100%',
      legend: {
        position: 'bottom'
      },
      chartArea: {
        left: 80,
        top: 10,
        right: 10,
        height: 200
      },
      pointSize: 5,
      backgroundColor: 'transparent'
    };
    var timelineOptions = {
      height: 300,
      width: '100%',
      legend: {
        position: 'bottom'
      },
      chartArea: {
        left: 80,
        top: 10,
        right: 10,
        height: 200
      },
      pointSize: 5,
      backgroundColor: 'transparent',
      seriesType: 'line',
      series: {2: {type: 'bars'}}
    };

    // TIMELINE GRAPH

    data = new google.visualization.DataTable();
    data.addColumn('datetime', 'Time');
    data.addColumn('number', 'SF');
    data.addColumn('number', 'Gateway count');
    data.addColumn('number', 'Channel');

    rows = [];
    for (i in timeline) {
      timeline[i][0] = new Date(timeline[i][0]);
      rows.push(timeline[i]);
    }
    data.addRows(rows);

    chart = new google.visualization.LineChart(document.getElementById('timeline_graph'));
    chart.draw(data, timelineOptions);

    // RSSI GRAPH

    data = new google.visualization.DataTable();
    data.addColumn('datetime', 'Time');
    for (i in graphs.columns) {
      data.addColumn('number', graphs.columns[i]);
    }

    rows = [];
    for (i in graphs.lines.rssi) {
      graphs.lines.rssi[i][0] = new Date(graphs.lines.rssi[i][0]);
      rows.push(graphs.lines.rssi[i]);
    }
    data.addRows(rows);

    chart = new google.visualization.LineChart(document.getElementById('rssis_graph'));
    chart.draw(data, lineOptions);

    // SNR GRAPH

    data = new google.visualization.DataTable();
    data.addColumn('datetime', 'Time');
    for (i in graphs.columns) {
      data.addColumn('number', graphs.columns[i]);
    }

    rows = [];
    for (i in graphs.lines.snr) {
      graphs.lines.snr[i][0] = new Date(graphs.lines.snr[i][0]);
      rows.push(graphs.lines.snr[i]);
    }
    data.addRows(rows);

    chart = new google.visualization.LineChart(document.getElementById('snrs_graph'));
    chart.draw(data, lineOptions);
  });
</script>
<div class="row">
  <div class="col-md-12">
    <h3>Timeline</h3>
    <div id="timeline_graph"></div>
  </div>
  <div class="col-md-12">
    <h3>RSSI over time</h3>
    <div id="rssis_graph"></div>
  </div>
  <div class="col-md-12">
    <h3>SNR over time</h3>
    <div id="snrs_graph"></div>
  </div>
</div>
