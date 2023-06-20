<?php

namespace Cmtickle\EventThing\Transport;

class BaseTransport implements TransportInterface
{
    public function send(array $data):array
    {
        return $data;
    }
}
