<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Http\Admin\Services;

use App\Builders\UserList as UserListBuilder;
use App\Caches\User as UserCache;
use App\Library\Paginator\Query as PaginateQuery;
use App\Library\Utils\Password as PasswordUtil;
use App\Library\Validators\Common as CommonValidator;
use App\Models\Account as AccountModel;
use App\Models\User as UserModel;
use App\Repos\Account as AccountRepo;
use App\Repos\Online as OnlineRepo;
use App\Repos\Role as RoleRepo;
use App\Repos\User as UserRepo;
use App\Services\Auth\Api as ApiAuth;
use App\Services\Auth\Home as HomeAuth;
use App\Validators\Account as AccountValidator;
use App\Validators\User as UserValidator;

class User extends Service
{

    public function getEduRoleTypes()
    {
        return UserModel::eduRoleTypes();
    }

    public function getAdminRoles()
    {
        $roleRepo = new RoleRepo();

        return $roleRepo->findAll(['deleted' => 0]);
    }

    public function getOnlineLogs($id)
    {
        $user = $this->findOrFail($id);

        $pageQuery = new PaginateQuery();

        $params = $pageQuery->getParams();

        $params['user_id'] = $user->id;

        $sort = $pageQuery->getSort();
        $page = $pageQuery->getPage();
        $limit = $pageQuery->getLimit();

        $onlineRepo = new OnlineRepo();

        return $onlineRepo->paginate($params, $sort, $page, $limit);
    }

    public function getUsers()
    {
        $pageQuery = new PaginateQuery();

        $params = $pageQuery->getParams();

        $accountRepo = new AccountRepo();

        /**
         * 兼容用户编号｜手机号码｜邮箱地址查询
         */
        if (!empty($params['id'])) {
            if (CommonValidator::phone($params['id'])) {
                $account = $accountRepo->findByPhone($params['id']);
                $params['id'] = $account ? $account->id : -1000;
            } elseif (CommonValidator::email($params['id'])) {
                $account = $accountRepo->findByEmail($params['id']);
                $params['id'] = $account ? $account->id : -1000;
            }
        }

        $params['deleted'] = $params['deleted'] ?? 0;

        $sort = $pageQuery->getSort();
        $page = $pageQuery->getPage();
        $limit = $pageQuery->getLimit();

        $userRepo = new UserRepo();

        $pager = $userRepo->paginate($params, $sort, $page, $limit);

        return $this->handleUsers($pager);
    }

    public function getUser($id)
    {
        return $this->findOrFail($id);
    }

    public function getAccount($id)
    {
        $accountRepo = new AccountRepo();

        return $accountRepo->findById($id);
    }

    public function createUser()
    {
        $post = $this->request->getPost();

        $accountValidator = new AccountValidator();

        $phone = $accountValidator->checkPhone($post['phone']);
        $password = $accountValidator->checkPassword($post['password']);

        $accountValidator->checkIfPhoneTaken($post['phone']);

        $userValidator = new UserValidator();

        $eduRole = $userValidator->checkEduRole($post['edu_role']);
        $adminRole = $userValidator->checkAdminRole($post['admin_role']);

        try {

            $this->db->begin();

            $account = new AccountModel();

            $salt = PasswordUtil::salt();
            $password = PasswordUtil::hash($password, $salt);

            $account->phone = $phone;
            $account->salt = $salt;
            $account->password = $password;

            if ($account->create() === false) {
                throw new \RuntimeException('Create Account Failed');
            }

            $user = $this->findOrFail($account->id);

            $user->admin_role = $adminRole;
            $user->edu_role = $eduRole;

            if ($user->update() === false) {
                throw new \RuntimeException('Update User Failed');
            }

            $this->db->commit();

            if ($adminRole > 0) {
                $this->updateAdminUserCount($adminRole);
            }

            $this->rebuildUserCache($user);

        } catch (\Exception $e) {

            $this->db->rollback();

            $logger = $this->getLogger('http');

            $logger->error('Create User Error ' . kg_json_encode([
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'message' => $e->getMessage(),
                ]));

            throw new \RuntimeException('sys.trans_rollback');
        }
    }

    public function updateUser($id)
    {
        $user = $this->findOrFail($id);

        $post = $this->request->getPost();

        $validator = new UserValidator();

        $data = [];

        if (isset($post['avatar'])) {
            $data['avatar'] = $validator->checkAvatar($post['avatar']);
        }

        if (isset($post['name'])) {
            $data['name'] = $validator->checkName($post['name']);
            if ($post['name'] != $user->name) {
                $validator->checkIfNameTaken($post['name']);
            }
        }

        if (isset($post['title'])) {
            $data['title'] = $validator->checkTitle($post['title']);
        }

        if (isset($post['about'])) {
            $data['about'] = $validator->checkAbout($post['about']);
        }

        if (isset($post['edu_role'])) {
            $data['edu_role'] = $validator->checkEduRole($post['edu_role']);
        }

        if (isset($post['admin_role'])) {
            $data['admin_role'] = $validator->checkAdminRole($post['admin_role']);
        }

        if (isset($post['vip'])) {
            $data['vip'] = $validator->checkVipStatus($post['vip']);
        }

        if (!empty($post['vip_expiry_time'])) {
            $data['vip_expiry_time'] = $validator->checkVipExpiryTime($post['vip_expiry_time']);
            if ($data['vip_expiry_time'] < time()) {
                $data['vip'] = 0;
            }
        }

        if (isset($post['locked'])) {
            $data['locked'] = $validator->checkLockStatus($post['locked']);
        }

        if (!empty($post['lock_expiry_time'])) {
            $data['lock_expiry_time'] = $validator->checkLockExpiryTime($post['lock_expiry_time']);
            if ($data['lock_expiry_time'] < time()) {
                $data['locked'] = 0;
            }
        }

        $oldAdminRole = $user->admin_role;

        $user->update($data);

        if ($user->locked == 1) {
            $this->destroyUserLogin($user);
        }

        if ($oldAdminRole > 0) {
            $this->updateAdminUserCount($oldAdminRole);
        }

        if ($user->admin_role > 0) {
            $this->updateAdminUserCount($user->admin_role);
        }

        $this->rebuildUserCache($user);

        return $user;
    }

    public function updateAccount($id)
    {
        $post = $this->request->getPost();

        $accountRepo = new AccountRepo();

        $account = $accountRepo->findById($id);

        $validator = new AccountValidator();

        $data = [];

        if (!empty($post['phone'])) {
            $data['phone'] = $validator->checkPhone($post['phone']);
            if ($post['phone'] != $account->phone) {
                $validator->checkIfPhoneTaken($post['phone']);
            }
        }

        if (!empty($post['email'])) {
            $data['email'] = $validator->checkEmail($post['email']);
            if ($post['email'] != $account->email) {
                $validator->checkIfEmailTaken($post['email']);
            }
        }

        if (!empty($post['password'])) {
            $post['password'] = $validator->checkPassword($post['password']);
            $data['salt'] = PasswordUtil::salt();
            $data['password'] = PasswordUtil::hash($post['password'], $data['salt']);
        }

        $account->update($data);

        return $account;
    }

    protected function findOrFail($id)
    {
        $validator = new UserValidator();

        return $validator->checkUser($id);
    }

    protected function rebuildUserCache(UserModel $user)
    {
        $cache = new UserCache();

        $cache->rebuild($user->id);
    }

    protected function destroyUserLogin(UserModel $user)
    {
        $homeAuth = new HomeAuth();

        $homeAuth->logoutClients($user->id);

        $apiAuth = new ApiAuth();

        $apiAuth->logoutClients($user->id);
    }

    protected function updateAdminUserCount($roleId)
    {
        $roleRepo = new RoleRepo();

        $role = $roleRepo->findById($roleId);

        if (!$role) return;

        $userCount = $roleRepo->countUsers($roleId);

        $role->user_count = $userCount;

        $role->update();
    }

    protected function handleUsers($pager)
    {
        if ($pager->total_items > 0) {

            $builder = new UserListBuilder();

            $items = $pager->items->toArray();

            $pipeA = $builder->handleUsers($items);
            $pipeB = $builder->handleAdminRoles($pipeA);
            $pipeC = $builder->handleEduRoles($pipeB);
            $pipeD = $builder->objects($pipeC);

            $pager->items = $pipeD;
        }

        return $pager;
    }

}
