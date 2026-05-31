<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\forms\backend\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use Besnovatyj\Blog\entities\taxonomy\Taxonomy;

class TaxonomySearch extends Model
{
    public $id;
    public $name;
    public $slug;
    public $title;

    public function rules(): array
    {
        return [
            [['id'], 'integer'],
            [['name', 'slug', 'title'], 'safe'],
        ];
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = Taxonomy::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['lft' => SORT_ASC]
            ],
            'pagination' => [
                'pageSize' => 100,
                'pageSizeLimit' => [15, 100],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'slug', $this->slug])
            ->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }
}
