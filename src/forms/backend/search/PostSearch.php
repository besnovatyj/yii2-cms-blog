<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\forms\backend\search;

use Besnovatyj\Blog\entities\Post;
use Besnovatyj\Blog\helpers\PostHelper;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class PostSearch extends Model
{
    public $id;
    public $title;
    public $description;
    public $content;
    public $status;
    public $taxonomy_id;
    public $comments_allowed;

    public $date_from;
    public $date_to;

    public function rules(): array
    {
        return [
//            ['taxonomy_id', 'each', 'rule' => ['integer']],
            [['id', 'status', 'comments_allowed', 'taxonomy_id'], 'integer'],
            [['title', 'description', 'content'], 'string'],
        ];
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = Post::find()->with('taxonomy');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
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
            'status' => $this->status,
            'taxonomy_id' => $this->taxonomy_id,
            'comments_allowed' => $this->comments_allowed,
        ]);

        $query
//            ->andFilterWhere(['in', 'taxonomy_id', $this->taxonomy_id])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['OR', ['like', 'title', $this->title], ['like', 'description', $this->title]]);

        return $dataProvider;
    }

    public function statusList(): array
    {
        return PostHelper::statusList();
    }

    public function commentStatusList(): array
    {
        return PostHelper::commentStatusList();
    }
}
