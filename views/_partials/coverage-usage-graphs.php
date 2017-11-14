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
  var sfs = <?= json_encode($frameCollection->coverage->sfUsage) ?>;
  var channels = <?= json_encode($frameCollection->coverage->channelUsage) ?>;
  var gwCounts = <?= json_encode($frameCollection->coverage->gwCountPdf) ?>;

  google.charts.load('current', {packages: ['corechart', 'bar', 'line']});
  google.charts.setOnLoadCallback(function () {
    var data, rows, i, chart;
    var options = {
      colors: ['#4CAF50'],
      backgroundColor: 'transparent',
      height: 300,
      legend: {
        position: 'none'
      },
      chartArea: {
        left: 40,
        top: 35,
        right: 10,
        bottom: 40
      }
    };

    // SF GRAPH

    data = new google.visualization.DataTable();
    data.addColumn('string', 'Spreading factor');
    data.addColumn('number', 'Occurence [%]');
    rows = [];
    for (i in sfs) {
      rows.push(["SF" + i, sfs[i]]);
    }
    data.addRows(rows);
    chart = new google.visualization.ColumnChart(document.getElementById('sfs_graph'));
    chart.draw(data, options);

    // CHANNEL GRAPH

    data = new google.visualization.DataTable();
    data.addColumn('string', 'Channel');
    data.addColumn('number', 'Nr frames');
    rows = [];
    for (i in channels) {
      rows.push([i, channels[i]]);
    }
    data.addRows(rows);
    chart = new google.visualization.ColumnChart(document.getElementById('channels_graph'));
    chart.draw(data, options);

    // GATEWAY GRAPH

    data = new google.visualization.DataTable();
    data.addColumn('string', 'Gateway count');
    data.addColumn('number', 'Nr frames');
    rows = [];
    for (i in gwCounts) {
      rows.push([i, gwCounts[i]]);
    }
    data.addRows(rows);
    chart = new google.visualization.ColumnChart(document.getElementById('gwcounts_graph'));
    chart.draw(data, options);
  });
</script>
<div class="row">
  <div class="col-md-4">
    <h3>SF usage</h3>
    <div id="sfs_graph"></div>
  </div>
  <div class="col-md-4">
    <h3>Channel usage</h3>
    <div id="channels_graph"></div>
  </div>
  <div class="col-md-4">
    <h3>Gateway count</h3>
    <div id="gwcounts_graph"></div>
  </div>
</div>
