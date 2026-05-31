<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Blog\forms\backend\search;

use Besnovatyj\Blog\entities\Comment;
use Besnovatyj\Blog\helpers\CommentHelper;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class CommentSearch extends Model
{
    public $id;
    public $text;
    public $active;
    public $post_id;

    public function rules(): array
    {
        return [
            [['id', 'post_id', 'active'], 'integer'],
            [['text'], 'safe'],
        ];
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = Comment::find()->with(['post']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'key' => function (Comment $comment) {
                return [
                    'post_id' => $comment->post_id,
                    'id' => $comment->id,
                ];
            },
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'post_id' => $this->post_id,
            'active' => $this->active,
        ]);

        $query
            ->andFilterWhere(['like', 'text', $this->text]);

        return $dataProvider;
    }

    public function statusList(): array
    {
        return CommentHelper::statusList();
    }
}
