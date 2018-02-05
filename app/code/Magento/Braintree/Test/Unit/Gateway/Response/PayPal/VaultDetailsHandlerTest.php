<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Response\PayPal;

use Braintree\Transaction;
use Braintree\Transaction\PayPalDetails;
use Magento\Braintree\Gateway\Response\PayPal\VaultDetailsHandler;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\PaymentToken;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class VaultDetailsHandlerTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VaultDetailsHandlerTest extends TestCase
{
    private static $transactionId = '1n2suy';

    /**
     * @var PaymentDataObjectInterface|MockObject
     */
    private $paymentDataObject;

    /**
     * @var Payment|MockObject
     */
    private $paymentInfo;

    /**
     * @var PaymentTokenFactoryInterface|MockObject
     */
    private $paymentTokenFactory;

    /**
     * @var PaymentTokenInterface|MockObject
     */
    protected $paymentToken;

    /**
     * @var OrderPaymentExtension|MockObject
     */
    private $paymentExtension;

    /**
     * @var OrderPaymentExtensionInterfaceFactory|MockObject
     */
    private $paymentExtensionFactory;

    /**
     * @var VaultDetailsHandler
     */
    private $handler;

    /**
     * @var DateTimeFactory|MockObject
     */
    private $dateTimeFactory;

    /**
     * @var array
     */
    private $subject = [];

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->paymentDataObject = $this->getMockForAbstractClass(PaymentDataObjectInterface::class);

        $this->paymentInfo = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();

        $this->paymentToken = $objectManager->getObject(PaymentToken::class);

        $this->paymentTokenFactory = $this->getMockBuilder(PaymentTokenFactoryInterface::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentExtension = $this->getMockBuilder(OrderPaymentExtensionInterface::class)
            ->setMethods(['setVaultPaymentToken', 'getVaultPaymentToken'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentExtensionFactory = $this->getMockBuilder(OrderPaymentExtensionInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->subject = [
            'payment' => $this->paymentDataObject,
        ];

        $this->dateTimeFactory = $this->getMockBuilder(DateTimeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        
        $this->handler = new VaultDetailsHandler(
            $this->paymentTokenFactory,
            $this->paymentExtensionFactory,
            new SubjectReader(),
            $this->dateTimeFactory
        );
    }

    public function testHandle()
    {
        /** @var Transaction $transaction */
        $transaction = $this->getTransaction();
        $response = [
            'object' => $transaction
        ];

        $this->paymentExtension->method('setVaultPaymentToken')
            ->with($this->paymentToken);
        $this->paymentExtension->method('getVaultPaymentToken')
            ->willReturn($this->paymentToken);
        
        $this->paymentDataObject->method('getPayment')
            ->willReturn($this->paymentInfo);

        $this->paymentTokenFactory->method('create')
            ->willReturn($this->paymentToken);

        $this->paymentExtensionFactory->method('create')
            ->willReturn($this->paymentExtension);

        $dateTime = new \DateTime('2016-07-05 00:00:00', new \DateTimeZone('UTC'));
        $expirationDate = '2017-07-05 00:00:00';
        $this->dateTimeFactory->method('create')
            ->willReturn($dateTime);
        
        $this->handler->handle($this->subject, $response);

        $extensionAttributes = $this->paymentInfo->getExtensionAttributes();
        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $extensionAttributes->getVaultPaymentToken();
        self::assertNotNull($paymentToken);

        $tokenDetails = json_decode($paymentToken->getTokenDetails(), true);

        self::assertSame($this->paymentToken, $paymentToken);
        self::assertEquals($transaction->transaction->paypalDetails->token, $paymentToken->getGatewayToken());
        self::assertEquals($transaction->transaction->paypalDetails->payerEmail, $tokenDetails['payerEmail']);
        self::assertEquals($expirationDate, $paymentToken->getExpiresAt());
    }

    /**
     * @covers \Magento\Braintree\Gateway\Response\PayPal\VaultDetailsHandler::handle
     */
    public function testHandleWithoutToken()
    {
        $transaction = $this->getTransaction();
        $transaction->transaction->paypalDetails->token = null;

        $response = [
            'object' => $transaction
        ];

        $this->paymentDataObject->method('getPayment')
            ->willReturn($this->paymentInfo);

        $this->paymentTokenFactory->expects(self::never())
            ->method('create');

        $this->dateTimeFactory->expects(self::never())
            ->method('create');

        $this->handler->handle($this->subject, $response);
        self::assertNull($this->paymentInfo->getExtensionAttributes());
    }

    /**
     * Create Braintree transaction
     * @return \stdClass
     */
    private function getTransaction()
    {
        $attributes = [
            'id' => self::$transactionId,
            'paypalDetails' => $this->getPayPalDetails()
        ];

        $transaction = Transaction::factory($attributes);
        $obj = new \stdClass();
        $obj->transaction = $transaction;

        return $obj;
    }

    /**
     * Get PayPal transaction details
     * @return PayPalDetails
     */
    private function getPayPalDetails()
    {
        $attributes = [
            'token' => 'rc39al',
            'payerEmail' => 'john.doe@example.com'
        ];

        $details = new PayPalDetails($attributes);

        return $details;
    }
}
