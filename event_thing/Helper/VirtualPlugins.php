<?php declare(strict_types=1);
/*
* © Colin Tickle. See LICENSE for details.
*/
namespace Cmtickle\EventThing\Helper;

use Magento\Framework\App\ObjectManager;
use Cmtickle\EventThing\Plugin\GenericPlugin;

class VirtualPlugins
{
    const IDENTIFIER_CLASS = 'class';
    const IDENTIFIER_VIRTUAL_TYPE = 'virtual_type';
    private static ObjectManager|null $objectManager = null;

    /**
     * ObjectManager is needed as this class is called statically.
     * This class is only used during DI compilation.
     * Therefor, application performance at runtime is not affected.
     *
     * @return ObjectManager
     */
    private static function getObjectManager():ObjectManager
    {
        if (null === self::$objectManager) {
            self::$objectManager = ObjectManager::getInstance();
        }

        return self::$objectManager;
    }

    /**
     * @param string $pluggedInClass
     * @param string $identifier
     * @return mixed
     * @throws \Exception
     */
    public static function getDynamicVirtualPlugin(string $pluggedInClass, string $identifier):GenericPlugin
    {
        $config = self::getObjectManager()->get('Magento\Framework\ObjectManager\ConfigInterface');
        $virtualTypes = $config->getVirtualTypes();
        $virtualPlugins = [];
        foreach ($virtualTypes as $virtualType => $pluggedInVirtualType) {
            if (!is_a($pluggedInVirtualType, GenericPlugin::class, true)) {
                continue;
            }
            $virtualPlugin = self::getObjectManager()->get($virtualType);
            if (self::IDENTIFIER_CLASS == $identifier && $pluggedInClass == $virtualPlugin->getPluggedInClass()) {
                $virtualPlugins[] = $virtualPlugin;
            } elseif (self::IDENTIFIER_VIRTUAL_TYPE == $identifier && $virtualType == $pluggedInClass) {
                $virtualPlugins[] = $virtualPlugin;
            }
        }
        $virtualPluginCount = count($virtualPlugins);
        if(1 !== $virtualPluginCount) {
            throw new \Exception('One Dynamic Virtual Plugin must be defined for ' . $pluggedInClass .
                ', ' . $virtualPluginCount . ' defined.' );
        }

        return $virtualPlugins[0];
    }
}
