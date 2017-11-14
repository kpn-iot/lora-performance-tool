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

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use app\models\Session;

/**
 * SessionSearch represents the model behind the search form about `app\models\Session`.
 */
class SessionSearch extends Session {

  public static $lastActivitySort = '(SELECT created_at FROM frames WHERE session_id = sessions.id ORDER BY created_at DESC LIMIT 1)';
  public $idArray;

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['device_id', 'id'], 'integer'],
      ['idArray', 'each', 'rule' => ['integer']],
      ['type', 'in', 'range' => array_keys(static::$typeOptions), 'allowArray' => true],
      ['vehicle_type', 'in', 'range' => array_keys(static::$vehicleTypeOptions), 'allowArray' => true],
      ['motion_indicator', 'in', 'range' => array_keys(static::$motionIndicatorOptions), 'allowArray' => true],
      [['description', 'created_at', 'updated_at'], 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios() {
    // bypass scenarios() implementation in the parent class
    return Model::scenarios();
  }

  private function _filter(&$query, $deviceIds = null) {
    // grid filtering conditions
    $query->andFilterWhere([
      'id' => (is_array($this->idArray)) ? $this->idArray : $this->id,
      'device_id' => ($deviceIds !== null) ? $deviceIds : $this->device_id,
      'type' => $this->type,
      'vehicle_type' => $this->vehicle_type,
      'motion_indicator' => $this->motion_indicator,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ]);

    $query->andFilterWhere(['like', 'description', $this->description]);
  }

  /**
   * @param array $params
   *
   * @return ActiveQuery
   */
  public function filter($params, $deviceIds = null) {
    $query = Session::find();
    $this->load($params);

    if (!$this->validate()) {
//      $query->where('0=1');
      return $query;
    }

    $this->_filter($query, $deviceIds);

    return $query;
  }

  /**
   * Creates data provider instance with search query applied
   *
   * @param array $params
   *
   * @return ActiveDataProvider
   */
  public function search($params, $deviceIds = null) {
    $query = Session::find()->with(['frames', 'device', 'firstFrame', 'lastFrame', 'frames.session']);

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => [
          'lastActivity' => SORT_DESC
        ]
      ]
    ]);

    $dataProvider->sort->attributes['lastActivity'] = [
      'asc' => [static::$lastActivitySort => SORT_ASC],
      'desc' => [static::$lastActivitySort => SORT_DESC],
      'default' => SORT_DESC
    ];

    $dataProvider->sort->attributes['firstFrame.created_at'] = [
      'asc' => ['(SELECT created_at FROM frames WHERE session_id = sessions.id ORDER BY created_at ASC LIMIT 1)' => SORT_ASC],
      'desc' => ['(SELECT created_at FROM frames WHERE session_id = sessions.id ORDER BY created_at ASC LIMIT 1)' => SORT_DESC],
      'default' => SORT_DESC
    ];

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $dataProvider;
    }

    $this->_filter($query, $deviceIds);

    return $dataProvider;
  }

}
