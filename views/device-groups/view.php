<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\DeviceGroup */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Device Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?= $this->render('_view_header', [
    'model' => $model,
    'activeTab' => 'view'
]) ?>

<?= \yii\grid\GridView::widget([
    'dataProvider' => new \yii\data\ActiveDataProvider(['query' => $model->getDevices()]),
    'columns' => [
        [
            'attribute' => 'name',
            'value' => function ($deviceModel) {
              return Html::a($deviceModel->name, ['/devices/view', 'id' => $deviceModel->id]);
            },
            'format' => 'raw'
        ],
        [
            'attribute' => 'autosplit',
            'filter' => [0 => 'No', 1 => 'Yes'],
            'format' => 'raw',
            'value' => 'autosplitFormatted'
        ],
        'description:ntext',
        'device_eui',
        [
            'label' => 'Last activity',
            'attribute' => 'lastFrame.created_at',
            'format' => 'timeAgo',
        ],
        [
            'format' => 'raw',
            'value' => function ($deviceModel) use ($model) {
              return Html::a('Remove from Group', ['', 'id' => $model->id, 'deviceToRemove' => $deviceModel->id], [
                  'class' => 'btn btn-xs btn-danger',
                  'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                  'data-method' => 'post',
              ]);
            }
        ]
    ],
]); ?>

<?php \yii\bootstrap\Modal::begin([
    'toggleButton' => ['label' => 'Add new Device to Group', 'class' => 'btn btn-default'],
]);

$form = \yii\widgets\ActiveForm::begin();

$devicesNotInGroup = \app\models\Device::find()
    ->joinWith([
        'deviceGroupLinks' => function (\yii\db\ActiveQuery $query) use ($model) {
          return $query->onCondition('group_id = :group_id', ['group_id' => $model->id]);
        }
    ], true, 'LEFT JOIN')
    ->andWhere('device_group_links.group_id IS NULL')
    ->orderBy('name')
    ->asArray()
    ->all();

$devicesNotInGroup = \app\helpers\ArrayHelper::map($devicesNotInGroup, 'id', 'name');
//print_r($devicesNotInGroup);
//die();
echo Html::tag('div', \kartik\select2\Select2::widget(['name' => 'deviceToAdd', 'data' => $devicesNotInGroup, 'options' => ['placeholder' => 'Select device to add']]), ['class' => 'form-group']);

echo Html::submitButton('Add to Group', ['class' => 'btn btn-primary']);

\yii\widgets\ActiveForm::end();
\yii\bootstrap\Modal::end();
?>
