<?php declare(strict_types=1);
/*
* © Colin Tickle. See LICENSE for details.
*/
namespace Cmtickle\EventThing\Observer;

use Magento\Framework\Event\Observer;

class GenericObserver extends AbstractObserver
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        $event = $observer->getEvent();

        if ($event->hasData('data_object') && $dataObject = (object) $event->getData('data_object')) {
            $data = ['event_name' => $event->getName()];
            $data['data_object'] = (
                $this->isDataObject($dataObject::class) ?
                    $dataObject->toArray() :
                    $dataObject->getData()
            );
            $this->processData($data);
        }
    }

}
