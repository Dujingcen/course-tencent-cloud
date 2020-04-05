<?php

namespace App\Http\Web\Controllers;

use App\Models\Order as OrderModel;
use Home\Services\Order as OrderService;

/**
 * @RoutePrefix("/order")
 */
class OrderController extends Controller
{

    /**
     * @Post("/confirm", name="web.order.confirm")
     */
    public function confirmAction()
    {
        $itemType = $this->request->get('item_type');
        $itemId = $this->request->get('item_id');

        $service = new OrderService();

        switch ($itemType) {

            case OrderModel::ITEM_ITEM_COURSE:

                $course = $service->getCourse($itemId);

                $this->view->course = $course;

                break;

            case OrderModel::ITEM_ITEM_PACKAGE:

                $package = $service->getPackage($itemId);
                $courses = $service->getPackageCourses($itemId);

                $this->view->package = $package;
                $this->view->courses = $courses;

                break;

            case OrderModel::ITEM_ITEM_REWARD:

                $course = $service->getCourse($itemId);

                $this->view->course = $course;

                break;
        }
    }

    /**
     * @Post("/create", name="web.order.create")
     */
    public function createAction()
    {
        $service = new OrderService();

        $order = $service->create();

        return $this->response->ajaxSuccess($order);
    }

    /**
     * @Get("/cashier", name="web.order.cashier")
     */
    public function cashierAction()
    {
        $service = new OrderService();

        $tradeNo = $this->request->getQuery('trade_no');

        $order = $service->getOrder($tradeNo);
        $orderItems = $service->getOrderItems($order->id);

        $this->view->order = $order;
        $this->view->orderItems = $orderItems;

        return $this->jsonSuccess($order->toArray());
    }

    /**
     * @Post("/pay", name="web.order.pay")
     */
    public function payAction()
    {
        $service = new OrderService();

        $tradeNo = $this->request->getPost('trade_no');
        $payChannel = $this->request->getPost('pay_channel');

        $order = $service->getOrder($tradeNo);

        $qrCodeText = $service->qrCode($tradeNo, $payChannel);

        //$qrCodeUrl = "http://qr.liantu.com/api.php?text={$qrCodeText}";

        $this->view->order = $order;
        $this->view->qrCodeText = $qrCodeText;
    }

    /**
     * @Post("/notify/{channel}", name="web.order.notify")
     */
    public function notifyAction($channel)
    {
        $service = new OrderService();

        $service->notify($channel);
    }

    /**
     * @Post("/status", name="web.order.status")
     */
    public function statusAction()
    {
        $service = new OrderService();

        $tradeNo = $this->request->getPost('trade_no');

        $order = $service->getOrder($tradeNo);

        $this->response->ajaxSuccess([
            'status' => $order->status
        ]);
    }

    /**
     * @Post("/cancel", name="web.order.cancel")
     */
    public function cancelAction()
    {
        $service = new OrderService();

        $order = $service->cancel();

        return $this->jsonSuccess($order->toArray());
    }

}