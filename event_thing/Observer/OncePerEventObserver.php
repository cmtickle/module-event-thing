<?php declare(strict_types=1);
/*
* © Colin Tickle. See LICENSE for details.
*/
namespace Cmtickle\EventThing\Observer;

use Magento\Framework\Event\Observer;

class OncePerEventObserver extends GenericObserver
{
    private static array $called = [];

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        $eventName = $observer->getEvent()->getName();
        if (isset(self::$called[$eventName])) {
            return;
        } else {
            self::$called[$eventName] = true;
        }

        parent::execute($observer);
    }

}
