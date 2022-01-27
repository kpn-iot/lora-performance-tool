<?php

namespace app\controllers;

use app\models\DeviceGroupLink;
use app\models\forms\DeviceGroupGraphForm;
use app\models\Session;
use Yii;
use app\models\DeviceGroup;
use app\models\DeviceGroupSearch;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * DeviceGroupsController implements the CRUD actions for DeviceGroup model.
 */
class DeviceGroupsController extends Controller {
  /**
   * {@inheritdoc}
   */
  public function behaviors() {
    return [
      'access' => [
        'class' => AccessControl::className(),
        'rules' => [
          [
            'allow' => true,
            'roles' => ['@']
          ]
        ],
      ],
      'verbs' => [
        'class' => VerbFilter::className(),
        'actions' => [
          'delete' => ['POST'],
        ],
      ],
    ];
  }

  /**
   * Lists all DeviceGroup models.
   * @return mixed
   */
  public function actionIndex() {
    $searchModel = new DeviceGroupSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

    return $this->render('index', [
      'searchModel' => $searchModel,
      'dataProvider' => $dataProvider,
    ]);
  }

  /**
   * Displays a single DeviceGroup model.
   * @param integer $id
   * @return mixed
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionView($id) {
    if (Yii::$app->request->isPost) {
      $deviceToAdd = Yii::$app->request->post('deviceToAdd');
      if ($deviceToAdd === null) {
        $deviceToRemove = Yii::$app->request->get('deviceToRemove');
        if ($deviceToRemove === null) {
          return $this->refresh();
        }
        $link = DeviceGroupLink::findOne(['group_id' => $id, 'device_id' => $deviceToRemove]);
        $link->delete();

        Yii::$app->session->addFlash('success', 'Device successfully removed from Group.');
        return $this->refresh();
      }
      $newLink = new DeviceGroupLink();
      $newLink->group_id = $id;
      $newLink->device_id = $deviceToAdd;
      if ($newLink->save()) {
        Yii::$app->session->addFlash('success', 'Device successfully added to Group.');
      }
      return $this->refresh();
    }

    return $this->render('view', [
      'model' => $this->findModel($id),
    ]);
  }

  public function actionDailyStats($id) {
    $model = $this->findModel($id);

    $formModel = new DeviceGroupGraphForm();
    $formModel->load(Yii::$app->request->get());
    $formOk = $formModel->validate();

    if ($formOk) {
      $data = $model->getDailyStatsData($formModel);
    } else {
      $data = null;
    }

    return $this->render('daily-stats', [
      'model' => $model,
      'formModel' => $formModel,
      'data' => $data,
    ]);
  }

  public function actionHistogram($id) {
    $model = $this->findModel($id);

    $formModel = new DeviceGroupGraphForm();
    $formModel->load(Yii::$app->request->get());
    $formOk = $formModel->validate();

    if ($formOk) {
      $bins = $model->getAccuracyHistogramData($formModel);
      $bins = $bins['aggregated'];
    } else {
      $bins = [];
    }

    return $this->render('histogram', [
      'model' => $model,
      'formModel' => $formModel,
      'bins' => $bins,
    ]);
  }

  public function actionGraphs($id) {
    $model = $this->findModel($id);

    $formModel = new DeviceGroupGraphForm();
    $formModel->load(Yii::$app->request->get());
    $formModel->validate();

    $data = $model->getDailyStatsData($formModel);
    if ($data === null) {
      return $this->render('graphs', [
        'model' => $model,
        'formModel' => $formModel,
        'nrResults' => 0,
      ]);
    }
    $data = $data['daily'];

    return $this->render('graphs', [
      'model' => $model,
      'formModel' => $formModel,
      'nrResults' => count($data),
      'dropRateGraphData' => $this->tableDataToGraphData($data, function ($row) {
        return 'Data';
      }, 'date', function ($row) {
        return (double)$row['drop_rate'];
      }),
      'medianAccuracyGraphData' => $this->tableDataToGraphData($data, function ($row) {
        return 'Data';
      }, 'date', function ($row) {
        return (double)$row['geoloc_acc_median_avg'];
      }),
      'averageAccuracyGraphData' => $this->tableDataToGraphData($data, function ($row) {
        return 'Data';
      }, 'date', function ($row) {
        return (double)$row['geoloc_acc_avg'];
      }),
      'successRateGraphData' => $this->tableDataToGraphData($data, function ($row) {
        return 'Data';
      }, 'date', function ($row) {
        return (double)$row['geoloc_success_rate'];
      }),
      'gwCountGraphData' => $this->tableDataToGraphData($data, function ($row) {
        return 'Data';
      }, 'date', function ($row) {
        return (double)$row['gw_count_avg'];
      }),
      'snrGraphData' => $this->tableDataToGraphData($data, function ($row) {
        return 'Data';
      }, 'date', function ($row) {
        return (double)$row['snr_avg'];
      }),
    ]);
  }

  public function actionRaw($id) {
    $model = $this->findModel($id);

    list ($formModel, $query) = $this->getSessionData($id);
    $sessionData = $query->all();

    return $this->render('raw', [
      'model' => $model,
      'formModel' => $formModel,
      'nrSessions' => count($sessionData),
      'accuracyGraphData' => $this->tableDataToGraphData($sessionData, function ($row) {
        return $row->device->name;
      }, function ($row) {
        return $row->prop->session_date_at;
      }, function ($row) {
        return (double)$row->prop->geoloc_accuracy_median;
      }),
      'successRateGraphData' => $this->tableDataToGraphData($sessionData, function ($row) {
        return $row->device->name;
      }, function ($row) {
        return $row->prop->session_date_at;
      }, function ($row) {
        return (double)$row->prop->geoloc_success_rate;
      }),
      'dropRateGraphData' => $this->tableDataToGraphData($sessionData, function ($row) {
        return $row->device->name;
      }, function ($row) {
        return $row->prop->session_date_at;
      }, function ($row) {
        return 100 - (double)$row->prop->frame_reception_ratio;
      }),
    ]);
  }

  public function actionTable($id) {
    $model = $this->findModel($id);

    list ($formModel, $sessionData) = $this->getSessionData($id);

    $dataProvider = new ActiveDataProvider([
      'query' => $sessionData,
    ]);

    return $this->render('table', [
      'model' => $model,
      'formModel' => $formModel,
      'sessionsProvider' => $dataProvider,
    ]);
  }

  private function getSessionData($id) {
    $formModel = new DeviceGroupGraphForm();
    $formModel->load(Yii::$app->request->get());
    $formModel->validate();

    /** @var Session[] $query */
    $query = Session::find()
      ->with(['device'])
      ->joinWith('properties', true)
      ->joinWith(['device.deviceGroupLinks'], false, 'INNER JOIN')
      ->andWhere(['group_id' => $id])
      ->andWhere(['>=', 'session_date_at', $formModel->startDateTime])
      ->andWhere(['<=', 'session_date_at', $formModel->endDateTime])
      ->orderBy('session_date_at');

    return [$formModel, $query];
  }

  /**
   * @param $dataSet
   * @param callable|string $seriesLabel
   * @param callable|string $xValue
   * @param callable|string $yValue
   */
  private function tableDataToGraphData($dataSet, $seriesLabel, $xValue, $yValue) {
    $data = [];
    // Group data set per series
    foreach ($dataSet as $row) {
      $key = is_callable($seriesLabel) ? $seriesLabel($row) : $row[$seriesLabel];
      if (!isset($data[$key])) {
        $data[$key] = [];
      }
      $data[$key][] = $row;
    }

    $columns = [];
    $lines = [];
    $colSkip = 0;
    $nrSeries = count($data);
    foreach ($data as $label => $series) {
      $columns[] = $label;
      foreach ($series as $row) {
        $value = is_callable($yValue) ? $yValue($row) : $row[$yValue];
        if ($value === null) {
          continue;
        }
        $newLine = [(isset($row[$xValue]) ? $row[$xValue] : $xValue($row))];
        for ($i = 0; $i < $colSkip; $i++) {
          $newLine[] = null;
        }
        $newLine[] = $value;
        for ($i = $colSkip + 1; $i < $nrSeries; $i++) {
          $newLine[] = null;
        }
        $lines[] = $newLine;
      }
      $colSkip += 1;
    }
    return [
      'columns' => $columns,
      'lines' => $lines
    ];
  }

  /**
   * Creates a new DeviceGroup model.
   * If creation is successful, the browser will be redirected to the 'view' page.
   * @return mixed
   */
  public function actionCreate() {
    $model = new DeviceGroup();

    if ($model->load(Yii::$app->request->post()) && $model->save()) {
      return $this->redirect(['view', 'id' => $model->id]);
    }

    return $this->render('create', [
      'model' => $model,
    ]);
  }

  /**
   * Updates an existing DeviceGroup model.
   * If update is successful, the browser will be redirected to the 'view' page.
   * @param integer $id
   * @return mixed
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionUpdate($id) {
    $model = $this->findModel($id);

    if ($model->load(Yii::$app->request->post()) && $model->save()) {
      return $this->redirect(['view', 'id' => $model->id]);
    }

    return $this->render('update', [
      'model' => $model,
    ]);
  }

  /**
   * Deletes an existing DeviceGroup model.
   * If deletion is successful, the browser will be redirected to the 'index' page.
   * @param integer $id
   * @return mixed
   * @throws NotFoundHttpException if the model cannot be found
   */
  public function actionDelete($id) {
    $this->findModel($id)->delete();

    return $this->redirect(['index']);
  }

  /**
   * Finds the DeviceGroup model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return DeviceGroup the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id) {
    if (($model = DeviceGroup::findOne($id)) !== null) {
      return $model;
    }

    throw new NotFoundHttpException('The requested page does not exist.');
  }
}
