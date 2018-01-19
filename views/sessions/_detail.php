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

use yii\widgets\DetailView;
use app\helpers\Html;

echo DetailView::widget([
  'model' => $model,
  'attributes' => [
    [
      'label' => 'Device',
      'format' => 'raw',
      'attribute' => 'device.name',
      'value' => function($data) {
        return Html::a($data->device->name, ['/devices/view', 'id' => $data->device_id]);
      }
    ],
    'description:ntext',
    'typeFormatted:raw',
    'vehicleTypeFormatted:raw',
    'motionIndicatorReadable',
    'countUpRange',
    'interval',
    'sf',
    'frr',
    'frrRel',
    [
      'label' => 'Nr Devices',
      'value' => $model->frameCollection->nrDevices
    ],
    [
      'label' => 'Average gateway count',
      'attribute' => 'frameCollection.coverage.avgGwCount'
    ],
    [
      'label' => 'Average RSSI',
      'value' => $model->frameCollection->coverage->avgRssi . " dBm"
    ],
    [
      'label' => 'Average SNR',
      'value' => $model->frameCollection->coverage->avgSnr . " dB"
    ],
    'created_at:timeAgo',
    'runtime',
    [
      'label' => 'First frame',
      'attribute' => 'firstFrame.created_at',
      'formatter' => 'dateTime'
    ],
    [
      'label' => 'Last frame',
      'attribute' => 'lastFrame.created_at',
      'formatter' => 'dateTime'
    ]
  ],
]);
