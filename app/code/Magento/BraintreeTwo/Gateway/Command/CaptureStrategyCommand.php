<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Command;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Payment\Gateway\Command;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * Class CaptureStrategyCommand
 */
class CaptureStrategyCommand implements CommandInterface
{
    /**
     * Braintree authorize and capture command
     */
    const SALE = 'sale';

    /**
     * Braintree capture command
     */
    const CAPTURE = 'settlement';

    /**
     * Braintree clone transaction command
     */
    const CLONE_TRANSACTION = 'clone';

    /**
     * @var \Magento\Payment\Gateway\Command\CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var \Magento\Sales\Api\TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param CommandPoolInterface $commandPool
     * @param TransactionRepositoryInterface $repository
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        TransactionRepositoryInterface $repository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->commandPool = $commandPool;
        $this->transactionRepository = $repository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface $paymentDO */
        $paymentDO = SubjectReader::readPayment($commandSubject);

        /** @var \Magento\Sales\Model\Order\Payment $paymentInfo */
        $paymentInfo = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($paymentInfo);

        $command = $this->getCommand($paymentInfo);
        return $this->commandPool->get($command)->execute($commandSubject);
    }

    /**
     * Get execution command name
     * @param Payment $payment
     * @return string
     */
    private function getCommand(Payment $payment)
    {
        // if auth transaction is not exists execute authorize&capture command
        if (!$payment->getAuthorizationTransaction()) {
            return self::SALE;
        }

        if (!$this->isExistsCaptureTransaction($payment)) {
            return self::CAPTURE;
        }

        return self::CLONE_TRANSACTION;
    }

    /**
     * Check if capture transaction already exists
     * @param Payment $payment
     * @return bool
     */
    private function isExistsCaptureTransaction(Payment $payment)
    {
        $filters[] = $this->filterBuilder->setField('payment_id')
            ->setValue($payment->getId())
            ->create();

        $filters[] = $this->filterBuilder->setField('txn_type')
            ->setValue(Transaction::TYPE_CAPTURE)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder->addFilters($filters)
            ->create();

        $count = $this->transactionRepository->getList($searchCriteria)->getTotalCount();
        return (boolean) $count;
    }
}