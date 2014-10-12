<?php

namespace GlsFormModule;

use Zend\ModuleManager\Feature\FormElementProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use GlsFormModule\View\Helper\FormTranslations;

class Module implements FormElementProviderInterface, ViewHelperProviderInterface
{
    public function getFormElementConfig()
    {
        return array(
            'invokables' => array(
                'translateable_field' => 'GlsFormModule\Form\Element\TranslateableElement'
            )
        );
    }
    
    public function getViewHelperConfig()
    {
        return array(
            'invokables' => array(
            
            ),
            'factories' => array(  
                'formTranslations' => function ($pluginManager) {
                    $config = $pluginManager->getServiceLocator()->get('config');
                    $errorViewHelper = isset($config['gls_form']['errorViewHelper']) ? $config['gls_form']['errorViewHelper'] : 'formelementerrors';
                    return new FormTranslations($errorViewHelper);
                },
            ),
        );
    }
}
