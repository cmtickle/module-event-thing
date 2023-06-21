<?php declare(strict_types=1);
/*
* © Colin Tickle. See LICENSE for details.
*/
namespace Cmtickle\EventThing\Observer;

use Cmtickle\EventThing\Transport\TransportInterface;

abstract class AbstractObserver implements \Magento\Framework\Event\ObserverInterface
{
    protected \Cmtickle\EventThing\Transport\TransportInterface $transport;

    /**
     * @param TransportInterface $transport
     */
    public function __construct(
        \Cmtickle\EventThing\Transport\TransportInterface $transport
    ) {
        $this->transport = $transport;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function processData(array $data): array
    {
        $data['event_type'] = 'observer';
        return $this->transport->send($data);
    }

    /**
     * @param object|string $pluggedInClass
     * @return bool
     */
    protected function isDataObject(object|string $pluggedInClass): bool
    {
        return is_a($pluggedInClass, \Magento\Framework\DataObject::class, true);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    abstract public function execute(\Magento\Framework\Event\Observer $observer): void;
}
