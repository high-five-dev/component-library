<?php

namespace HighFive\ComponentLibrary\formatters;

use Twig\Markup;

interface FormatterInterface
{
    public static function getFormatterId(): string;

    public function getComponentId(): string;

    /**
     * @return array<string, mixed>
     */
    public function getData(): array;

    public function getHtml(): Markup;
}
