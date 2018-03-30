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
    'nrFrames',
    'frr',
    [
      'label' => 'Nr Devices',
      'value' => $model->frameCollection->nrDevices
    ],
    [
      'label' => 'Average gateway count',
      'attribute' => 'avgGwCount'
    ],
    [
      'label' => 'Average RSSI',
      'value' => $model->prop->rssi_average . " dBm"
    ],
    [
      'label' => 'Average SNR',
      'value' => $model->prop->snr_average . " dB"
    ],
    'created_at:timeAgo',
    'runtime',
    [
      'label' => 'First frame',
      'attribute' => 'prop.first_frame_at',
      'formatter' => 'datetime'
    ],
    [
      'label' => 'Last frame',
      'attribute' => 'prop.last_frame_at',
      'formatter' => 'datetime'
    ]
  ],
]);
