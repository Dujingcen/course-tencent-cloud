<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Console\Tasks;

use App\Repos\User as UserRepo;
use App\Services\Search\UserDocument;
use App\Services\Search\UserSearcher;
use App\Services\Sync\UserIndex as UserIndexSync;

class SyncUserIndexTask extends Task
{

    public function mainAction()
    {
        $redis = $this->getRedis();

        $key = $this->getSyncKey();

        $userIds = $redis->sRandMember($key, 1000);

        if (!$userIds) return;

        $userRepo = new UserRepo();

        $users = $userRepo->findByIds($userIds);

        if ($users->count() == 0) return;

        $document = new UserDocument();

        $handler = new UserSearcher();

        $index = $handler->getXS()->getIndex();

        $index->openBuffer();

        foreach ($users as $user) {

            $doc = $document->setDocument($user);

            if ($user->deleted == 0) {
                $index->update($doc);
            } else {
                $index->del($user->id);
            }
        }

        $index->closeBuffer();

        $redis->sRem($key, ...$userIds);
    }

    protected function getSyncKey()
    {
        $sync = new UserIndexSync();

        return $sync->getSyncKey();
    }

}
