<?php declare(strict_types=1);
/*
* © Colin Tickle. See LICENSE for details.
*/
namespace Cmtickle\EventThing\Transport;

class DummyTransport implements TransportInterface
{
    public function process(array $data): array
    {
        return $data;
    }
}
