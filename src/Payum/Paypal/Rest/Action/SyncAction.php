<?php

namespace Payum\Paypal\Rest\Action;

use PayPal\Api\Payment as PaypalPayment;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Sync;

class SyncAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request)
    {
        /** @var Sync $request */
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaypalPayment $model */
        $model = $request->getModel();

        $payment = PaypalPayment::get($model->id);

        $model->fromArray($payment->toArray());
    }

    public function supports($request)
    {
        return $request instanceof Sync &&
            $request->getModel() instanceof PaypalPayment
        ;
    }
}
