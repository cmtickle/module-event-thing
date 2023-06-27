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

        if ($event->hasData('data_object') && $dataObject = $event->getData('data_object')) {
            $data = ['eventName' => $event->getName()];
            $data['dataObject'] = (
                $this->isDataObject($dataObject::class) ?
                    $dataObject->toArray() :
                    $event->getData()
            );
            $this->processData($data);
        } else {
            $this->processData($event->getData());
        }
    }

}
