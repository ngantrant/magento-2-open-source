<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Review\Test\Page\Adminhtml\RatingIndex;

/**
 * Class AssertProductRatingSuccessSaveMessage
 */
class AssertProductRatingSuccessSaveMessage extends AbstractConstraint
{
    const SUCCESS_MESSAGE = 'You saved the rating.';

    /**
     * Assert that success message is displayed after rating save
     *
     * @param RatingIndex $ratingIndex
     * @return void
     */
    public function processAssert(RatingIndex $ratingIndex)
    {
        $actualMessage = $ratingIndex->getMessagesBlock()->getSuccessMessage();
        \PHPUnit_Framework_Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Text success save message is displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Rating success save message is present.';
    }
}
