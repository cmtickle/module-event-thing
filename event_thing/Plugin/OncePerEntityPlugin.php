<?php

namespace Cmtickle\EventThing\Plugin;

use Magento\Framework\DataObject;

class OncePerEntityPlugin extends GenericPlugin
{
    protected static array $called = [];

    public function __call(string $name, array $arguments)
    {
        /** @var DataObject $pluggedInClass */
        $pluggedInClass = $arguments[0];
        if ($this->isDataObject($pluggedInClass)) {
            $entityId = $pluggedInClass->getData($pluggedInClass->getResource()->getIdFieldName());
            $eventPrefix = $pluggedInClass->getEventPrefix();
            if (!isset(self::$called[$eventPrefix . '__' . $name])) {
                self::$called[$eventPrefix . '__' . $name][(string) $entityId] = true;
                return parent::__call($name, $arguments);
            } elseif (!isset(self::$called[$eventPrefix . '__' . $name][(string) $entityId])) {
                self::$called[$eventPrefix . '__' . $name][(string) $entityId] = true;
                return parent::__call($name, $arguments);
            } else {
                return end($arguments);
            }
        }
    }
}
