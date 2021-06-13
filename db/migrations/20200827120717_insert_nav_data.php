<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

use Phinx\Migration\AbstractMigration;

final class InsertNavData extends AbstractMigration
{

    public function up()
    {
        $now = time();

        $rows = [
            [
                'id' => 1,
                'parent_id' => 0,
                'level' => 1,
                'name' => '首页',
                'path' => ',1,',
                'target' => '_self',
                'url' => '/',
                'position' => 1,
                'priority' => 1,
                'published' => 1,
                'create_time' => $now,
            ],
            [
                'id' => 2,
                'parent_id' => 0,
                'level' => 1,
                'name' => '课程',
                'path' => ',2,',
                'target' => '_self',
                'url' => '/course/list',
                'position' => 1,
                'priority' => 2,
                'published' => 1,
                'create_time' => $now,
            ],
            [
                'id' => 3,
                'parent_id' => 0,
                'level' => 1,
                'name' => '名师',
                'path' => ',3,',
                'target' => '_self',
                'url' => '/teacher/list',
                'position' => 1,
                'priority' => 3,
                'published' => 1,
                'create_time' => $now,
            ],
            [
                'id' => 4,
                'parent_id' => 0,
                'level' => 1,
                'name' => '群组',
                'path' => ',4,',
                'target' => '_self',
                'url' => '/im/group/list',
                'position' => 1,
                'priority' => 6,
                'published' => 1,
                'create_time' => $now,
            ],
            [
                'id' => 5,
                'parent_id' => 0,
                'level' => 1,
                'name' => '关于我们',
                'path' => ',5,',
                'target' => '_blank',
                'url' => '#',
                'position' => 2,
                'priority' => 1,
                'published' => 1,
                'create_time' => $now,
            ],
            [
                'id' => 6,
                'parent_id' => 0,
                'level' => 1,
                'name' => '联系我们',
                'path' => ',6,',
                'target' => '_blank',
                'url' => '#',
                'position' => 2,
                'priority' => 2,
                'published' => 1,
                'create_time' => $now,
            ],
            [
                'id' => 7,
                'parent_id' => 0,
                'level' => 1,
                'name' => '人才招聘',
                'path' => ',7,',
                'target' => '_blank',
                'url' => '#',
                'position' => 2,
                'priority' => 3,
                'published' => 1,
                'create_time' => $now,
            ],
            [
                'id' => 8,
                'parent_id' => 0,
                'level' => 1,
                'name' => '帮助中心',
                'path' => ',8,',
                'target' => '_blank',
                'url' => '/help',
                'position' => 2,
                'priority' => 4,
                'published' => 1,
                'create_time' => $now,
            ],
            [
                'id' => 9,
                'parent_id' => 0,
                'level' => 1,
                'name' => '友情链接',
                'path' => ',9,',
                'target' => '_blank',
                'url' => '#',
                'position' => 2,
                'priority' => 5,
                'published' => 1,
                'create_time' => $now,
            ],
        ];

        $this->table('kg_nav')->insert($rows)->save();
    }

    public function down()
    {
        $this->execute('DELETE FROM kg_nav');
    }

}
