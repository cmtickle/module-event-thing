<?php declare(strict_types=1);
/*
* © Colin Tickle. See LICENSE for details.
*/
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
            if (!isset(self::$called[$eventPrefix . '__' . $name]) ||
                !isset(self::$called[$eventPrefix . '__' . $name][(string) $entityId])) {
                self::$called[$eventPrefix . '__' . $name][(string) $entityId] = parent::__call($name, $arguments);
            }
            return self::$called[$eventPrefix . '__' . $name][(string) $entityId];
        }
    }
}
