<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Builders;

use App\Repos\Course as CourseRepo;
use App\Repos\User as UserRepo;

class ImGroupList extends Builder
{

    public function handleGroups(array $groups)
    {
        $baseUrl = kg_cos_url();

        foreach ($groups as $key => $group) {
            $groups[$key]['avatar'] = $baseUrl . $group['avatar'];
        }

        return $groups;
    }

    public function handleCourses(array $groups)
    {
        $courses = $this->getCourses($groups);

        foreach ($groups as $key => $group) {
            $groups[$key]['course'] = $courses[$group['course_id']] ?? new \stdClass();
        }

        return $groups;
    }

    public function handleUsers(array $groups)
    {
        $users = $this->getUsers($groups);

        foreach ($groups as $key => $group) {
            $groups[$key]['owner'] = $users[$group['owner_id']] ?? new \stdClass();
        }

        return $groups;
    }

    public function getCourses(array $groups)
    {
        $ids = kg_array_column($groups, 'course_id');

        $courseRepo = new CourseRepo();

        $courses = $courseRepo->findByIds($ids, ['id', 'title']);

        $result = [];

        foreach ($courses->toArray() as $course) {
            $result[$course['id']] = $course;
        }

        return $result;
    }

    public function getUsers(array $groups)
    {
        $ids = kg_array_column($groups, 'owner_id');

        $userRepo = new UserRepo();

        $users = $userRepo->findByIds($ids, ['id', 'name', 'avatar']);

        $baseUrl = kg_cos_url();

        $result = [];

        foreach ($users->toArray() as $user) {
            $user['avatar'] = $baseUrl . $user['avatar'];
            $result[$user['id']] = $user;
        }

        return $result;
    }

}
