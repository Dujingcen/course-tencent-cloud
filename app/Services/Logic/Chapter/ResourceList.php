<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Services\Logic\Chapter;

use App\Builders\ResourceList as ResourceListBuilder;
use App\Repos\Resource as ResourceRepo;
use App\Services\Logic\ChapterTrait;
use App\Services\Logic\Service as LogicService;

class ResourceList extends LogicService
{

    use ChapterTrait;

    public function handle($id)
    {
        $chapter = $this->checkChapter($id);

        $resourceRepo = new ResourceRepo();

        $resources = $resourceRepo->findByChapterId($chapter->id);

        if ($resources->count() == 0) {
            return [];
        }

        $builder = new ResourceListBuilder();

        $relations = $resources->toArray();

        return $builder->getUploads($relations);
    }

}
