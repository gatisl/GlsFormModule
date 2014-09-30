<?php

namespace GlsFormModule;

use Zend\ModuleManager\Feature\FormElementProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;

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
                'formTranslations' => 'Common\View\Helper\FormTranslations',
            )
        );
    }
}
