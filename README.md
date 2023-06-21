# Event Thing for Magento 2

This module provides the core functionality of "Generic Observers", "Generic Plugins" and "Transport Adapters".

Generic Observers & Plugins enable the logic/integration touch points of Magento Plugins & Observers to be handled 
outside the core Magento application.

Transport Adapters are the communication layer between Magento and the external application which processes the Generic 
Observers & Plugins.

## Usage

For data security reasons, this base module **will not** send data to an external application without additional 
development. 

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
