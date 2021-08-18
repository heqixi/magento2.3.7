<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_FacebookPixel
 */


declare(strict_types=1);

namespace Amasty\FacebookPixel\Observer;

use Amasty\FacebookPixel\Model\EventData\EventDataGeneratorPool;
use Amasty\FacebookPixel\Model\EventSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;
use Psr\Log\LoggerInterface;

class AddToCartEvent implements ObserverInterface
{
    /**
     * @var EventDataGeneratorPool
     */
    private $eventDataGeneratorPool;

    /**
     * @var EventSession
     */
    private $eventSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EventDataGeneratorPool $eventDataGeneratorPool,
        EventSession $eventSession,
        LoggerInterface $logger
    ) {
        $this->eventDataGeneratorPool = $eventDataGeneratorPool;
        $this->eventSession = $eventSession;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var Item[] $item */
        $items = $observer->getData('items');
        if ($items) {
            try {
                $eventDataGenerator = $this->eventDataGeneratorPool->getDataGenerator('addToCart');
                if (!$eventDataGenerator->isEventEnabled()) {
                    return;
                }

                $this->eventSession->setEvent(
                    [
                        'event_action' => $eventDataGenerator->getEventAction(),
                        'event_type' => $eventDataGenerator->getEventType(),
                        'event_data' => $eventDataGenerator->getEventData($items)
                    ]
                );
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }
}
