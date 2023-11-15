<?php

namespace HighFive\ComponentLibrary\events;

use craft\base\Event;
use HighFive\ComponentLibrary\formatters\FormatterInterface;

class FormattersEvent extends Event
{
    /**
     * @var FormatterInterface[]
     */
    public array $formatters = [];
}
