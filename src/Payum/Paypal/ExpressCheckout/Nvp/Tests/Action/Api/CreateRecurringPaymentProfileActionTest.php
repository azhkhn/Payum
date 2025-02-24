<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateRecurringPaymentProfileAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateRecurringPaymentProfile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class CreateRecurringPaymentProfileActionTest extends TestCase
{
    public function testShouldImplementActionInterface()
    {
        $rc = new ReflectionClass(CreateRecurringPaymentProfileAction::class);

        $this->assertTrue($rc->implementsInterface(ActionInterface::class));
    }

    public function testShouldImplementApoAwareInterface()
    {
        $rc = new ReflectionClass(CreateRecurringPaymentProfileAction::class);

        $this->assertTrue($rc->implementsInterface(ApiAwareInterface::class));
    }

    public function testShouldCreateRecurringPaymentProfileRequestAndArrayAccessAsModel()
    {
        $action = new CreateRecurringPaymentProfileAction();

        $this->assertTrue($action->supports(new CreateRecurringPaymentProfile($this->createMock(ArrayAccess::class))));
    }

    public function testShouldNotSupportAnythingNotCreateRecurringPaymentProfileRequest()
    {
        $action = new CreateRecurringPaymentProfileAction();

        $this->assertFalse($action->supports(new stdClass()));
    }

    public function testThrowIfNotSupportedRequestGivenAsArgumentForExecute()
    {
        $this->expectException(RequestNotSupportedException::class);
        $action = new CreateRecurringPaymentProfileAction();

        $action->execute(new stdClass());
    }

    public function testThrowIfTokenNotSetInModel()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The TOKEN, PROFILESTARTDATE, DESC, BILLINGPERIOD, BILLINGFREQUENCY, AMT, CURRENCYCODE fields are required.');
        $action = new CreateRecurringPaymentProfileAction();

        $action->execute(new CreateRecurringPaymentProfile([]));
    }

    public function testThrowIfRequiredFieldMissing()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The PROFILESTARTDATE, DESC, BILLINGPERIOD, BILLINGFREQUENCY, AMT, CURRENCYCODE fields are required.');
        $action = new CreateRecurringPaymentProfileAction();

        $action->execute(new CreateRecurringPaymentProfile([
            'TOKEN' => 'aToken',
        ]));
    }

    public function testShouldCallApiCreateRecurringPaymentsProfileMethodWithExpectedRequiredArguments()
    {
        $testCase = $this;

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('createRecurringPaymentsProfile')
            ->willReturnCallback(function (array $fields) use ($testCase) {
                $testCase->assertArrayHasKey('TOKEN', $fields);
                $testCase->assertSame('theToken', $fields['TOKEN']);

                $testCase->assertArrayHasKey('PROFILESTARTDATE', $fields);
                $testCase->assertSame('theStartDate', $fields['PROFILESTARTDATE']);

                $testCase->assertArrayHasKey('DESC', $fields);
                $testCase->assertSame('theDesc', $fields['DESC']);

                $testCase->assertArrayHasKey('BILLINGPERIOD', $fields);
                $testCase->assertSame('thePeriod', $fields['BILLINGPERIOD']);

                $testCase->assertArrayHasKey('BILLINGFREQUENCY', $fields);
                $testCase->assertSame('theFrequency', $fields['BILLINGFREQUENCY']);

                $testCase->assertArrayHasKey('AMT', $fields);
                $testCase->assertSame('theAmt', $fields['AMT']);

                $testCase->assertArrayHasKey('CURRENCYCODE', $fields);
                $testCase->assertSame('theCurr', $fields['CURRENCYCODE']);

                $testCase->assertArrayHasKey('EMAIL', $fields);
                $testCase->assertSame('theEmail', $fields['EMAIL']);

                $testCase->assertArrayHasKey('STREET', $fields);
                $testCase->assertSame('theStreet', $fields['STREET']);

                $testCase->assertArrayHasKey('CITY', $fields);
                $testCase->assertSame('theCity', $fields['CITY']);

                $testCase->assertArrayHasKey('COUNTRYCODE', $fields);
                $testCase->assertSame('theCountry', $fields['COUNTRYCODE']);

                $testCase->assertArrayHasKey('ZIP', $fields);
                $testCase->assertSame('theZip', $fields['ZIP']);

                return [];
            })
        ;

        $action = new CreateRecurringPaymentProfileAction();
        $action->setApi($apiMock);

        $request = new CreateRecurringPaymentProfile([
            'TOKEN' => 'theToken',
            'PROFILESTARTDATE' => 'theStartDate',
            'DESC' => 'theDesc',
            'BILLINGPERIOD' => 'thePeriod',
            'BILLINGFREQUENCY' => 'theFrequency',
            'AMT' => 'theAmt',
            'CURRENCYCODE' => 'theCurr',
            'EMAIL' => 'theEmail',
            'STREET' => 'theStreet',
            'CITY' => 'theCity',
            'COUNTRYCODE' => 'theCountry',
            'ZIP' => 'theZip',
        ]);

        $action->execute($request);
    }

    public function testShouldCallApiCreateRecurringPaymentsProfileMethodAndUpdateModelFromResponse()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->once())
            ->method('createRecurringPaymentsProfile')
            ->willReturnCallback(function () {
                return [
                    'PROFILEID' => 'theId',
                    'PROFILESTATUS' => 'theStatus',
                ];
            })
        ;

        $action = new CreateRecurringPaymentProfileAction();
        $action->setApi($apiMock);

        $request = new CreateRecurringPaymentProfile([
            'TOKEN' => 'theToken',
            'PROFILESTARTDATE' => 'theStartDate',
            'DESC' => 'theDesc',
            'BILLINGPERIOD' => 'thePeriod',
            'BILLINGFREQUENCY' => 'theFrequency',
            'AMT' => 'theAmt',
            'CURRENCYCODE' => 'theCurr',
            'EMAIL' => 'theEmail',
            'STREET' => 'theStreet',
            'CITY' => 'theCity',
            'COUNTRYCODE' => 'theCountry',
            'ZIP' => 'theZip',
        ]);

        $action->execute($request);

        $model = $request->getModel();
        $this->assertArrayHasKey('PROFILEID', $model);
        $this->assertSame('theId', $model['PROFILEID']);

        $this->assertArrayHasKey('PROFILESTATUS', $model);
        $this->assertSame('theStatus', $model['PROFILESTATUS']);
    }

    /**
     * @return MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class, [], [], '', false);
    }
}
