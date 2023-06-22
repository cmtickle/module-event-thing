# Event Thing for Magento 2

This module provides the core functionality of "Generic Observers", "Generic Plugins" and "Transport Adapters".

Generic Observers & Plugins enable the logic/integration touch points of Magento Plugins & Observers to be handled 
outside the core Magento application.

Transport Adapters are the communication layer between Magento and the external application which processes the Generic 
Observers & Plugins.

## Usage

## Transport

**IMPORTANT:** For data security reasons, this base module **will not** send data to an external application without 
additional development. 

To make the code functional, a Transport Adapter implementing `\Cmtickle\EventThing\Transport\TransportInterface` is 
needed. This should be configured in global di.xml within your custom codebase.

An example implementation is provided for a REST API Transport. To use the REST Transport, create the following files 
in your custom module. The custom module must depend on Cmtickle_EventThing.

app/etc/di.xml
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <preference for="Cmtickle\EventThing\Transport\TransportInterface"
                type="Cmtickle\EventThing\Transport\Rest"/>
</config>
```
app/etc/config.xml
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Store:etc/config.xsd">
    <default>
        <event_thing>
            <rest>
                <url>https://your.rest.url/endpoint</url>
            </rest>
        </event_thing>
    </default>
</config>
```

## Generic Observers

### IMPORTANT PRINCIPLES

A Generic Observer should, conceptually, be considered asynchronous. Don't rely on it running immediately, imagine that 
your observer is added to a queue.

A Geneeric Observer **MUST NOT** attempt to affect data at the runtime (it could be used to trigger an API call to 
modify data later). Use a Generic Plugin if you need to affect data at runtime.

Meaning, it should be possible for the Observer to run at any point in the future and treat data provided by the event 
as read-only.

Some possible use cases for Generic Observers:

* Exporting new orders to a third-party system on save.
* Notifying a third-party about failed payments/orders.
* Monitoring how many times a specific URL is viewed.
* Triggering a process which flushes an external page cache when a product goes out of stock/comes into stock.

### Types of Observer supported.

The types of Generic Observer supported are:

#### Cmtickle\EventThing\Observer\AbstractObserver

Extend this if you want to create a new type of Observer.

#### Cmtickle\EventThing\Observer\GenericObserver

The most basic observer, will be processed every time the event fires.

#### Cmtickle\EventThing\Observer\OncePerEventObserver

Fired only once per during each run of the application (a page load normally represents a run of the application).

### Adding an Observer

Add `events.xml` in the standard locations for Magento [see dpcumentation](https://devdocs.magento.com/guides/v2.3/ext-best-practices/extension-coding/observers-bp.html)

For example, to send the order data once after load:
```xml
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Event/etc/events.xsd">
    <event name="sales_order_load_after">
        <observer name="event_thing_sales_order_load_after" instance="Cmtickle\EventThing\Observer\OncePerEventObserver"/>
    </event>
</config>
```

## Generic Plugins

### IMPORTANT PRINCIPLES

As we should [avoid using the 'around' Plugin type](https://devdocs.magento.com/guides/v2.3/ext-best-practices/extension-coding/common-programming-bp.html#using-around-plugins)
these are not currently supported by Event Thing.

Plugins should be used when you need to change data during runtime.

As plugins are executed during runtime, they can affect performance. Avoid slow API calls in your Plugin processing.

### Supported Generic Plugin classes.

#### Cmtickle\EventThing\Plugin\GenericPlugin

Will process data on every occurrence of your Plugin being called.

#### Cmtickle\EventThing\Plugin\OncePerEntityPlugin

Will process your Plugin once per application run for each entity (an entity is a Product, Category, Order etc.).

### Adding a Plugin

Each Generic Plugin needs two elements, both are added to di.xml in the standard places:
* A VirtualType which declares which class of Generic Plugin to use, which class, method and Plugin type to use.
* The Plugin definition itself, which refers to the VirtualType.

For example, to process a Generic Plugin for the Product Class on `afterGetPrice` and `afterLoad`::
```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <virtualType name="Cmtickle\EventThing\Plugin\Product" type="Cmtickle\EventThing\Plugin\GenericPlugin">
        <arguments>
            <argument name="pluggedInClass" xsi:type="string">Magento\Catalog\Model\Product</argument>
            <argument name="functions" xsi:type="array">
                <item name="after" xsi:type="array">
                    <item name="1" xsi:type="string">load</item>
                    <item name="2" xsi:type="string">getPrice</item>
                </item>
            </argument>
        </arguments>
    </virtualType>
    
    <type name="Magento\Catalog\Model\Product">
        <plugin name="cmtickle_product_plugin" 
                type="Cmtickle\EventThing\Plugin\Product"/>
    </type>
</config>
```
