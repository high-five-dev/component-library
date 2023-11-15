<?php

namespace HighFive\ComponentLibrary\services;

use craft\base\Component;
use craft\helpers\Json;
use HighFive\ComponentLibrary\ComponentLibrary;
use HighFive\ComponentLibrary\events\FormattersEvent;
use HighFive\ComponentLibrary\exceptions\ComponentException;
use HighFive\ComponentLibrary\exceptions\FormatterException;
use HighFive\ComponentLibrary\formatters\FormatterInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * @property-read FormatterInterface[] $formatters
 */
class Formatters extends Component
{
// Constants
    public const EVENT_REGISTER_FORMATTERS = 'registerFormatters';

    /**
     * @param array<string, mixed> $config
     *
     * @throws FormatterException
     */
    public function getFormatter(string $id, array $config): FormatterInterface
    {
        $class = $this->getFormatters()[$id] ?? null;

        if (!$class) {
            throw new FormatterException("Formatter with id {$id} not found");
        }

        return new $class($config);
    }

    /**
     * @return FormatterInterface[]
     */
    public function getFormatters(): array
    {
        if (!$this->_formatters) {
            $this->_prepareFormatters();
        }

        return $this->_formatters;
    }

    private function _prepareFormatters(): void
    {
        $event = new FormattersEvent([
            'formatters' => $this->_getDefaultFormatters(),
        ]);

        if ($this->hasEventHandlers(self::EVENT_REGISTER_FORMATTERS)) {
            $this->trigger(self::EVENT_REGISTER_FORMATTERS, $event);
        }

        foreach ($event->formatters as $formatter) {
            $this->_formatters[$formatter::getFormatterId()] = $formatter;
        }
    }

    /**
     * @return FormatterInterface[]
     */
    private function _getDefaultFormatters(): array
    {
        return [];
    }

    /**
     * @throws ComponentException
     */
    public function getComponent(string $name): string
    {
        $component = $this->getComponentMap()[$name] ?? '';

        if (!$component) {
            throw new ComponentException("Component with name {$name} not found");
        }

        return $component;
    }

    /**
     * @return array<string, string>
     *
     * @todo Check if json config is needed
     */
    public function getComponentMap(): array
    {
        if (!empty($this->_componentMap)) {
            return $this->_componentMap;
        }

        $baseDir = ComponentLibrary::getInstance()?->getBasePath() . '/templates/_components';

        if (!is_dir($baseDir)) {
            return [];
        }

        $iterator = new RecursiveDirectoryIterator($baseDir);
        foreach (new RecursiveIteratorIterator($iterator) as $filename => $file) {
            /**
             * @var SplFileInfo $file
             */
            if ($file->getExtension() !== 'json') {
                continue;
            }

            /**
             * @var string $filename
             */
            $fileContent = file_get_contents($filename);

            if (!$fileContent) {
                continue;
            }

            /**
             * @var array<string, mixed> $component
             */
            $component = Json::decode($fileContent);

            if (isset($component['handle'])) {
                $this->_componentMap['@' . $component['handle']] = str_replace('config.json', 'twig', $filename);
            }
        }

        return $this->_componentMap;
    }
    /**
     * @var FormatterInterface[]
     */
    private array $_formatters = [];
    /**
     * @var array<string, string>
     */
    private array $_componentMap = [];
}
