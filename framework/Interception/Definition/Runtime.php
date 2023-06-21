<?php
/**
 * \Reflection based plugin method list. Uses reflection to retrieve list of interception methods defined in plugin.
 * Should be only used in development mode, because it reads method list on every request which is expensive.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Modifications for Event Thing © Colin Tickle. See LICENSE for details.
 */

namespace Magento\Framework\Interception\Definition;

use Cmtickle\EventThing\Plugin\GenericPlugin;
use Magento\Framework\App\Arguments\ValidationState;
use Magento\Framework\Interception\DefinitionInterface;

class Runtime implements DefinitionInterface
{
    private static array|null $backtrace = null;
    /**
     * @var array
     */
    protected $_typesByPrefixes = [
        'befor' => self::LISTENER_BEFORE,
        'aroun' => self::LISTENER_AROUND,
        'after' => self::LISTENER_AFTER,
    ];

    /**
     * Plugin method service prefix lengths
     *
     * @var array
     */
    protected $prefixLengths = [
        self::LISTENER_BEFORE => 6,
        self::LISTENER_AROUND => 6,
        self::LISTENER_AFTER => 5,
    ];
    protected \Cmtickle\EventThing\Helper\VirtualPlugins $virtualPlugins;

    public function __construct() {
        $this->virtualPlugins = new \Cmtickle\EventThing\Helper\VirtualPlugins();
    }

    protected function getBacktrace()
    {
        if(null === self::$backtrace) {
            self::$backtrace = debug_backtrace();
        }

        return self::$backtrace;
    }

    /**
     * @description : By the time this class is called, only the base class is known.
     *  This function uses the stack trace to work out the intended class for the Plugin.
     *
     * @return mixed|void
     */
    protected function getPluggedInClass()
    {
        $backtrace = $this->getBacktrace();
        foreach ($backtrace as $trace) {
            if ($trace['class'] == 'Magento\Framework\Interception\PluginListGenerator' &&
                $trace['function'] == 'inheritPlugins') {
                return $trace['args'][0];
            }
        }
    }

    protected function getDynamicClassMethods(string $pluggedInClass = '')
    {
        if (!$pluggedInClass) {
            $pluggedInClass = $this->getPluggedInClass();
        }
        $dynamicPlugin  = $this->virtualPlugins->getDynamicVirtualPlugin($pluggedInClass, 'class');
        return $dynamicPlugin ? $dynamicPlugin->getPluginMethods() : [];
    }

    /**
     * Retrieve list of methods
     *
     * @param string $type
     * @return string[]
     */
    public function getMethodList($type)
    {
        $methods = [];
        if (str_starts_with($type, "Cmtickle\EventThing\Plugin")) {
            $allMethods = $this->getDynamicClassMethods();
        } elseif (class_exists($type)) {
            $allMethods = get_class_methods($type);
        } else {
            $dynamicPlugin = $this->virtualPlugins->getDynamicVirtualPlugin($type, 'virtual_type');
            $allMethods = $dynamicPlugin->getPluginMethods();
        }
        if ($allMethods) {
            foreach ($allMethods as $method) {
                $prefix = substr($method, 0, 5);
                if (isset($this->_typesByPrefixes[$prefix])) {
                    $methodName = \lcfirst(substr($method, $this->prefixLengths[$this->_typesByPrefixes[$prefix]]));
                    $methods[$methodName] = isset(
                        $methods[$methodName]
                    ) ? $methods[$methodName] | $this->_typesByPrefixes[$prefix] : $this->_typesByPrefixes[$prefix];
                }
            }
        }
        return $methods;
    }
}
