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
use app\models\UserLog;

/**
 * UserLogSearch represents the model behind the search form about `app\models\UserLog`.
 */
class UserLogSearch extends UserLog {

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['id'], 'integer'],
      [['ip_address', 'username', 'action', 'result', 'created_at', 'updated_at'], 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios() {
    // bypass scenarios() implementation in the parent class
    return Model::scenarios();
  }

  /**
   * Creates data provider instance with search query applied
   *
   * @param array $params
   *
   * @return ActiveDataProvider
   */
  public function search($params) {
    $query = UserLog::find();

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'sort' => [
        'defaultOrder' => [
          'created_at' => SORT_DESC
        ]
      ]
    ]);

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $dataProvider;
    }

    // grid filtering conditions
    $query->andFilterWhere([
      'id' => $this->id,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ]);

    $query->andFilterWhere(['like', 'ip_address', $this->ip_address])
      ->andFilterWhere(['like', 'username', $this->username])
      ->andFilterWhere(['like', 'action', $this->action])
      ->andFilterWhere(['like', 'result', $this->result]);

    return $dataProvider;
  }

}
