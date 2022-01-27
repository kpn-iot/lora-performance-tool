<?php

use app\helpers\Html;

if (!isset($showBins)) {
  $showBins = false;
}

/** @var $formModel \app\models\forms\DeviceGroupGraphForm */
?>
<?php $form = \yii\widgets\ActiveForm::begin(['method' => 'GET']) ?>
<div class="flex">
    <div>
      <?= $form->field($formModel, 'startDateTime')->textInput() ?>
    </div>
    <div>
      <?= $form->field($formModel, 'endDateTime')->textInput() ?>
    </div>
  <?php if ($showBins): ?>
      <div>
        <?= $form->field($formModel, 'bins')->textInput() ?>
      </div>
  <?php endif ?>
    <div style="padding-top:23px">
        <div class="btn-toolbar">
          <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
          <?= Html::button('Reset time window', ['class' => 'btn btn-default', 'onClick' => '$("#devicegroupgraphform-startdatetime,#devicegroupgraphform-enddatetime").val("");this.form.submit()']) ?>
        </div>
    </div>
</div>
<hr/>
<?php \yii\widgets\ActiveForm::end() ?>
<style>
    div.flex {
        display: flex;
    }

    div.flex > * {
        margin: 0 8px;
    }
</style>
