<?php

namespace Payum\Payex\Tests\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHumanStatus;
use Payum\Payex\Action\PaymentDetailsStatusAction;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Api\RecurringApi;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class PaymentDetailsStatusActionTest extends TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new ReflectionClass(PaymentDetailsStatusAction::class);

        $this->assertTrue($rc->isSubclassOf(ActionInterface::class));
    }

    public function testShouldSupportGetStatusRequestWithEmptyArrayAsModel()
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertTrue($action->supports(new GetHumanStatus([])));
    }

    public function testShouldNotSupportGetStatusRequestWithArrayAsModelIfAutoPaySet()
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new GetHumanStatus([
            'autoPay' => true,
        ])));
    }

    public function testShouldSupportGetStatusRequestWithArrayAsModelIfAutoPaySetToFalse()
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertTrue($action->supports(new GetHumanStatus([
            'autoPay' => false,
        ])));
    }

    public function testShouldSupportGetStatusRequestWithArrayAsModelIfRecurringSetToTrueAndAutoPaySet()
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertTrue($action->supports(new GetHumanStatus([
            'autoPay' => true,
            'recurring' => true,
        ])));
    }

    public function testShouldNotSupportAnythingNotStatusRequest()
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testShouldNotSupportStatusRequestWithNotArrayAccessModel()
    {
        $action = new PaymentDetailsStatusAction();

        $this->assertFalse($action->supports(new GetHumanStatus(new stdClass())));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new PaymentDetailsStatusAction();

        $action->execute(new stdClass());
    }

    public function testShouldMarkNewIfDetailsEmpty()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkUnknownIfOrderStatusNotSupported()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'orderStatus' => 'not-supported-status',
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markNew();

        $action->execute($status);

        $this->assertTrue($status->isUnknown());
    }

    public function testShouldMarkUnknownIfOrderStatusSupportedButTransactionStatusNotSupported()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'transactionStatus' => 'not-supported-status',
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markNew();

        $action->execute($status);

        $this->assertTrue($status->isUnknown());
    }

    public function testShouldMarkNewIfOrderStatusNotSet()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isNew());
    }

    public function testShouldMarkCapturedTwoPhaseTransaction()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_AUTHORIZATION,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_AUTHORIZE,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldMarkFailedTwoPhaseTransactionIfTransactionStatusNotAuthorize()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_AUTHORIZATION,
            'transactionStatus' => 'not-authorize-status',
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    public function testShouldMarkCapturedOnePhaseTransaction()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_SALE,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_SALE,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldMarkFailedOnePhaseTransactionIfTransactionStatusNotSale()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_SALE,
            'transactionStatus' => 'not-sale-status',
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    public function testShouldMarkCanceledIfTransactionStatusCanceled()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_CANCEL,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    public function testShouldMarkCanceledIfTransactionStatusFailedButErrorDetailsTellCanceled()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_CANCEL,
            'errorDetails' => [
                'transactionErrorCode' => OrderApi::TRANSACTIONERRORCODE_OPERATIONCANCELLEDBYCUSTOMER,
            ],
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    public function testShouldMarkFailedIfTransactionStatusFailed()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_FAILURE,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    public function testShouldMarkPendingIfOrderStatusProgressing()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'orderStatus' => OrderApi::ORDERSTATUS_PROCESSING,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isPending());
    }

    public function testShouldMarkExpiredIfOrderStatusNotFound()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'orderStatus' => OrderApi::ORDERSTATUS_NOT_FOUND,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isExpired());
    }

    public function testShouldMarkFailedIfErrorCodeNotOk()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'errorCode' => 'not-ok',
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }

    public function testShouldMarkCapturedIfErrorCodeOk()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'errorCode' => OrderApi::ERRORCODE_OK,
            'purchaseOperation' => OrderApi::PURCHASEOPERATION_SALE,
            'transactionStatus' => OrderApi::TRANSACTIONSTATUS_SALE,
            'orderStatus' => OrderApi::ORDERSTATUS_COMPLETED,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCaptured());
    }

    public function testShouldMarkCanceledIfRecurringStatusIsStoppedByMerchant()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'recurringStatus' => RecurringApi::RECURRINGSTATUS_STOPPEDBYMERCHANT,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    public function testShouldMarkCanceledIfRecurringStatusIsStoppedByAdmin()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'recurringStatus' => RecurringApi::RECURRINGSTATUS_STOPPEDBYADMIN,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    public function testShouldMarkCanceledIfRecurringStatusIsStoppedByClient()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'recurringStatus' => RecurringApi::RECURRINGSTATUS_STOPPEDBYCLIENT,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    public function testShouldMarkCanceledIfRecurringStatusIsStoppedBySystem()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'recurringStatus' => RecurringApi::RECURRINGSTATUS_STOPPEDBYSYSTEM,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isCanceled());
    }

    public function testShouldMarkFailedIfRecurringStatusIsFailed()
    {
        $action = new PaymentDetailsStatusAction();

        $status = new GetHumanStatus([
            'recurringStatus' => RecurringApi::RECURRINGSTATUS_FAILED,
            'orderId' => 'anId',
            'autoPay' => false,
        ]);

        //guard
        $status->markUnknown();

        $action->execute($status);

        $this->assertTrue($status->isFailed());
    }
}
