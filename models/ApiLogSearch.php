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
use app\models\ApiLog;

/**
 * ApiLogSearch represents the model behind the search form about `app\models\ApiLog`.
 */
class ApiLogSearch extends ApiLog {

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['id'], 'integer'],
      [['origin', 'query_string', 'body', 'comments', 'created_at', 'updated_at'], 'safe'],
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
    $query = ApiLog::find();

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
      'pagination' => [
        'pageSize' => 25
      ],
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

    $query->andFilterWhere(['like', 'origin', $this->origin])
      ->andFilterWhere(['like', 'query_string', $this->query_string])
      ->andFilterWhere(['like', 'body', $this->body])
      ->andFilterWhere(['like', 'comments', $this->comments]);

    return $dataProvider;
  }

}
