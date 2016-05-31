<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\DateTime;

use Magento\Setup\Model\ObjectManagerProvider;

/**
 * Provider of DateTime instance
 */
class DateTimeProvider
{
    /**
     * Timezone provider
     *
<<<<<<< HEAD
     * @var TimezoneProvider
=======
     * @var TimeZoneProvider
>>>>>>> develop
     */
    private $tzProvider;

    /**
     * Object Manager provider
     *
     * @var ObjectManagerProvider
     */
    private $objectManagerProvider;

    /**
     * DateTime instance
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * Init
     *
<<<<<<< HEAD
     * @param TimezoneProvider $tzProvider
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(TimezoneProvider $tzProvider, ObjectManagerProvider $objectManagerProvider)
=======
     * @param TimeZoneProvider $tzProvider
     * @param ObjectManagerProvider $objectManagerProvider
     */
    public function __construct(TimeZoneProvider $tzProvider, ObjectManagerProvider $objectManagerProvider)
>>>>>>> develop
    {
        $this->tzProvider = $tzProvider;
        $this->objectManagerProvider = $objectManagerProvider;
    }

    /**
     * Get instance of DateTime
     *
     * @return \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public function get()
    {
        if (!$this->dateTime) {
            $this->dateTime = $this->objectManagerProvider->get()->create(
                'Magento\Framework\Stdlib\DateTime\DateTime',
                ['localeDate' => $this->tzProvider->get()]
            );
        }
        return $this->dateTime;
    }
}
