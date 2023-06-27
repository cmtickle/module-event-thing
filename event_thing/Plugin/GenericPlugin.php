<?php declare(strict_types=1);
/*
* © Colin Tickle. See LICENSE for details.
*/
namespace Cmtickle\EventThing\Plugin;

use Cmtickle\EventThing\Transport\TransportInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

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

    /**
     * @param RemoteAddress $remoteAddress
     * @param TransportInterface $transport
     * @param string $pluggedInClass
     * @param array $functions
     */
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

    /**
     * @param $pluggedInClass
     * @return bool
     */
    protected function isDataObject($pluggedInClass): bool
    {
        return is_a($pluggedInClass, \Magento\Framework\DataObject::class, true);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function processData(array $data): array
    {
        $data['eventType'] = 'plugin';
        $data['customerIp'] = $this->remoteAddress->getRemoteAddress();
        return $this->transport->process($data);
    }

    /**
     * @param $methodName
     * @param $pluggedInClass
     * @param array $arguments
     * @return array|array[]
     */
    protected function beforeFunction($methodName, $pluggedInClass, array $arguments = []): array
    {
        $data =  [
            'pluggedInClass' => $this->getPluggedInClass(),
            'pluggedInMethod' => $methodName,
            'pluginType' => 'before',
            'dataObject' => $this->isDataObject($pluggedInClass) ? $pluggedInClass->toArray() : [],
            'arguments' => $arguments
        ];
        $processed = $this->processData($data);

        return [$processed['arguments']];
    }

    /**
     * @param $methodName
     * @param $pluggedInClass
     * @param $returnedParameters
     * @return mixed
     */
    protected function afterFunction($methodName, $pluggedInClass, $returnedParameters): mixed
    {
        $data = [
            'pluggedInClass' => $this->getPluggedInClass(),
            'pluggedInMethod' => $methodName,
            'pluginType' => 'after',
            'dataObject' => $this->isDataObject($pluggedInClass) ? $pluggedInClass->getData() : [],
            'returnedParameters' => $returnedParameters
        ];
        $processed = $this->processData($data);

        return $processed['returnedParameters'];
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return array[]|false|mixed
     */
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

    /**
     * @return string
     */
    public function getPluggedInClass(): string
    {
        return $this->pluggedInClass;
    }

    /**
     * @return array
     */
    public function getPluginMethods(): array
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
