<?php

namespace HighFive\ComponentLibrary;

use Craft;
use craft\base\Event;
use craft\events\RegisterTemplateRootsEvent;
use craft\web\View;
use HighFive\ComponentLibrary\services\Formatters;
use HighFive\ComponentLibrary\twig\Loader;
use yii\base\Module;

use const DIRECTORY_SEPARATOR;

/**
 * @property Formatters $formatters
 */
class ComponentLibrary extends Module
{
    public function init(): void
    {
        parent::init();

        Craft::$app->onInit(function () {
            $this->_registerServices();
            $this->_registerControllerNamespace();
            $this->_registerComponentLoader();
            $this->_registerEvents();
        });
    }

    private function _registerServices(): void
    {
        $this->setComponents($this->_getServices());
    }

    private function _registerControllerNamespace(): void
    {
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = __NAMESPACE__ . '\console\controllers';
        } else {
            $this->controllerNamespace = __NAMESPACE__ . '\controllers';
        }
    }

    private function _registerComponentLoader(): void
    {
        $view = Craft::$app->getView();
        $view->getTwig()->setLoader(new Loader($view));
    }

    private function _registerEvents(): void
    {
        foreach ($this->_getServices() as $service) {
            if (method_exists($service, 'registerEvents')) {
                $this->{$service}->registerEvents();
            }
        }

        Event::on(View::class, View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $event) {
            if (!is_dir($baseDir = $this->getBasePath() . DIRECTORY_SEPARATOR . 'templates')) {
                return;
            }

            $event->roots[$this->id] = $baseDir;
        });
    }

    /**
     * @return array<string, string>
     */
    private function _getServices(): array
    {
        return [
            'formatters' => Formatters::class,
        ];
    }
}
