<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Repos;

use App\Library\Paginator\Adapter\QueryBuilder as PagerQueryBuilder;
use App\Models\ImNotice as ImNoticeModel;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset;
use Phalcon\Mvc\Model\ResultsetInterface;

class ImNotice extends Repository
{

    public function paginate($where = [], $sort = 'latest', $page = 1, $limit = 15)
    {
        $builder = $this->modelsManager->createBuilder();

        $builder->from(ImNoticeModel::class);

        $builder->where('1 = 1');

        if (!empty($where['sender_id'])) {
            $builder->andWhere('sender_id = :sender_id:', ['sender_id' => $where['sender_id']]);
        }

        if (!empty($where['receiver_id'])) {
            $builder->andWhere('receiver_id = :receiver_id:', ['receiver_id' => $where['receiver_id']]);
        }

        switch ($sort) {
            case 'oldest':
                $orderBy = 'id ASC';
                break;
            default:
                $orderBy = 'id DESC';
                break;
        }

        $builder->orderBy($orderBy);

        $pager = new PagerQueryBuilder([
            'builder' => $builder,
            'page' => $page,
            'limit' => $limit,
        ]);

        return $pager->paginate();
    }

    /**
     * @param int $id
     * @return ImNoticeModel|Model|bool
     */
    public function findById($id)
    {
        return ImNoticeModel::findFirst([
            'conditions' => 'id = :id:',
            'bind' => ['id' => $id],
        ]);
    }

    /**
     * @param array $ids
     * @param string|array $columns
     * @return ResultsetInterface|Resultset|ImNoticeModel[]
     */
    public function findByIds($ids, $columns = '*')
    {
        return ImNoticeModel::query()
            ->columns($columns)
            ->inWhere('id', $ids)
            ->execute();
    }

}
