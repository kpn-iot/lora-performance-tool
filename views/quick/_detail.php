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

/* @var $model \app\models\Quick */

use yii\widgets\DetailView;

echo DetailView::widget([
  'model' => $model,
  'attributes' => [
    'name:ntext',
    'typeFormatted:raw',
    'updated_at:dateTime',
    [
      'label' => 'Nr Devices',
      'value' => $model->frameCollection->nrDevices
    ],
    'nrFrames',
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
    ]
  ],
]);
