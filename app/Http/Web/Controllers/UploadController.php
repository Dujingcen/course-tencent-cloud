<?php


namespace App\Http\Web\Controllers;

use App\Services\MyStorage as StorageService;

/**
 * @RoutePrefix("/upload")
 */
class UploadController extends Controller
{

    /**
     * @Post("/avatar/img", name="web.upload.avatar_img")
     */
    public function uploadAvatarImageAction()
    {
        $service = new StorageService();

        $key = $service->uploadAvatarImage();

        $url = $service->getCiImageUrl($key);

        if ($url) {
            return $this->jsonSuccess(['data' => ['src' => $url, 'title' => '']]);
        } else {
            return $this->jsonError(['msg' => '上传文件失败']);
        }
    }

    /**
     * @Post("/im/img", name="web.upload.im_img")
     */
    public function uploadImImageAction()
    {
    }

    /**
     * @Post("/im/file", name="web.upload.im_file")
     */
    public function uploadImFileAction()
    {

    }

}