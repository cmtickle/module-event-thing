<?php declare(strict_types=1);
/*
* © Colin Tickle. See LICENSE for details.
*/
namespace Cmtickle\EventThing\Transport;

interface TransportInterface
{
    /**
     * Send the data to some external processing system.
     * The external system
     *
     * @param array $data
     * @return array
     */
    public function process(array $data):array;
}
