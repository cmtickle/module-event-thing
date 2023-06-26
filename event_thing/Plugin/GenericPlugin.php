<?php declare(strict_types=1);
/*
* © Colin Tickle. See LICENSE for details.
*/
namespace Cmtickle\EventThing\Plugin;

class GenericPlugin
{
    protected \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress;
    protected \Cmtickle\EventThing\Transport\TransportInterface $transport;

    /**
     * key => 'before|after|around', value => ['functionName']
     * @var array[array] $functions
     */
    protected array $functions = [];
    protected array|null $pluginMethods = null;
    protected string $pluggedInClass;

    public function __construct(
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Cmtickle\EventThing\Transport\TransportInterface $transport,
        string $pluggedInClass,
        array $functions
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->transport = $transport;
        $this->pluggedInClass = $pluggedInClass;
        $this->functions = $functions;
    }

    protected function isDataObject($pluggedInClass)
    {
        return is_a($pluggedInClass, \Magento\Framework\DataObject::class, true);
    }

    protected function processData(array $data)
    {
        $data['event_type'] = 'plugin';
        $data['customer_ip'] = $this->remoteAddress->getRemoteAddress();
        return $this->transport->process($data);
    }

    protected function beforeFunction($methodName, $pluggedInClass, array $arguments = [])
    {
        if ($this->isDataObject($pluggedInClass)) {
            $data =  [
                'pluginClass' => self::class,
                'originalClass' => $this->getPluggedInClass(),
                'method' => $methodName,
                'dataObject' => $pluggedInClass->toArray(),
                'arguments' => $arguments
            ];
            $processed = $this->processData($data);
        }

        return isset($processed) ? [$processed['arguments']] : [$arguments];
    }

    protected function afterFunction($methodName, $pluggedInClass, $returnedParameters)
    {
        if ($this->isDataObject($pluggedInClass)) {
            $data = [
                'pluginClass' => self::class,
                'originalClass' => $this->getPluggedInClass(),
                'method' => $methodName,
                'dataObject' => $pluggedInClass->getData(),
                'returnedParameters' => $returnedParameters
            ];
            $processed = $this->processData($data);
        }

        return isset($processed) ? $processed['returnedParameters'] : $returnedParameters;
    }

    public function __call(string $name, array $arguments)
    {
        switch(substr($name, 0, 5)) {
            case 'after':
                return $this->afterFunction($name, ... $arguments);
                break;
            case 'befor':
                $pluggedInClass = array_shift($arguments);
                $arguments = $arguments ?? [];
                return $this->beforeFunction($name, $pluggedInClass, $arguments);
                break;
            default:
                return end($arguments);
        }
    }

    public function getPluggedInClass()
    {
        return $this->pluggedInClass;
    }

    public function getPluginMethods():array
    {
        if (!(null === $this->pluginMethods)) {
            return $this->pluginMethods;
        }
        $this->pluginMethods = [];
        foreach ($this->functions as $pluginType => $functions) {
            foreach ($functions as $index => $function) {
                $this->pluginMethods[] = strtolower($pluginType).ucfirst($function);
            }
        }
        return $this->pluginMethods;
    }
}
