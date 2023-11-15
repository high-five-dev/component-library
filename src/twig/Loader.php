<?php

namespace HighFive\ComponentLibrary\twig;

use Craft;
use craft\web\twig\TemplateLoaderException;
use craft\web\View;
use HighFive\ComponentLibrary\ComponentLibrary;
use HighFive\ComponentLibrary\exceptions\ComponentException;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;
use yii\base\Exception;

class Loader implements LoaderInterface
{
    public function __construct(protected View $view) {}

    public function getSourceContext(string $name): Source
    {
        try {
            $template = $this->_findTemplate($name);
        } catch (Throwable $e) {
            throw new LoaderError($e->getMessage());
        }

        if (!is_readable($template) || !($fileContent = file_get_contents($template))) {
            throw new LoaderError(Craft::t('app', 'Unable to find the template “{template}”.', ['template' => $name]));
        }

        return new Source($fileContent, $name, $template);
    }

    public function getCacheKey(string $name): string
    {
        try {
            return $this->_findTemplate($name);
        } catch (Throwable $e) {
            return 'Template Not Found: ' . $e->getMessage();
        }
    }

    /**
     * @throws TemplateLoaderException
     * @throws ComponentException
     * @throws LoaderError
     */
    public function isFresh(string $name, int $time): bool
    {
        if (
            Craft::$app->getUpdates()->getIsCraftUpdatePending()
            && Craft::$app->getRequest()->getIsCpRequest()
        ) {
            return false;
        }

        $sourceModifiedTime = filemtime($this->_findTemplate($name));

        return $sourceModifiedTime <= $time;
    }

    /**
     * @throws Exception
     */
    public function exists(string $name): bool
    {
        return $this->view->doesTemplateExist($name);
    }

    /**
     * @throws TemplateLoaderException
     * @throws ComponentException
     * @throws LoaderError
     */
    private function _findTemplate(string $name): string
    {
        if (str_starts_with($name, '@')) {
            $template = ComponentLibrary::getInstance()?->formatters->getComponent($name);
        } else {
            $template = $this->view->resolveTemplate($name);
        }

        if (!$template) {
            throw new TemplateLoaderException($name, Craft::t('app', 'Unable to find the template “{template}”.', ['template' => $name]));
        }

        return $template;
    }
}
