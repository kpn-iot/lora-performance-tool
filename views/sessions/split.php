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
/* @var $model app\models\Session */

$this->title = "Split " . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Devices', 'url' => ['/devices/index']];
$this->params['breadcrumbs'][] = ['label' => $model->device->name, 'url' => ['/devices/view', 'id' => $model->device_id]];
$this->params['breadcrumbs'][] = ['label' => 'Sessions', 'url' => ['index', 'SessionSearch[device_id]' => $model->device_id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="well">
  Enter here the frame counter value (FCntUp) at which you want to start the new session:
  <form method="POST">
    <input id="form-token" type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>"/>
    <p>
      <input type="text" class="form-control" name="frame_counter" />
    </p>
    <p>
      <input type="submit" class="btn btn-primary" value="Split session" />
    </p>
  </form>

</div>

<?= $this->render('/_partials/geoloc-table', ['frameCollection' => $model->frameCollection]) ?>