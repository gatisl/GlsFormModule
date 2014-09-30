<?php

namespace GlsFormModule\View\Helper;

use Zend\View\Helper\AbstractHelper as BaseAbstractHelper;
use Zend\Form\ElementInterface;
use Common\Form\Element\TranslateableElement;
use Zend\Form\FieldsetInterface;

class FormTranslations extends BaseAbstractHelper
{
    public function __invoke(ElementInterface $element)
    {
        if (! $element instanceof TranslateableElement) {
            throw new \InvalidArgumentException('Element not instance of Common\Form\Element\TranslateableElement');
        }
        
        return $this->render($element);
    }
    
    private function render(TranslateableElement $element)
    {
        return 
            $this->renderTabs($element) .
            $this->renderTabContent($element)
        ;
    }
    
    protected function renderHelper($name, ElementInterface $element)
    {
        $helper = $this->getView()->plugin($name);
        return $helper($element);
    }
    
    protected function renderTabs(TranslateableElement $element)
    {
        $html = '<ul class="nav nav-tabs" role="tablist">';
        foreach ($element->getLocales() as $key => $locale) {
            $label = array_key_exists($locale, $element->getLocaleLabels()) ? $element->getLocaleLabels()[$locale] : $locale;
            $html .= sprintf('<li class="%s"><a href="#%s"  role="tab" data-toggle="tab" >%s</a></li>', $key === 0 ? 'active' : '', $locale, $label);
        }
        $html .= '</ul>';
        $html .= '<div class="sp23"></div>';
        
        return $html;
    }
    
    protected function renderTabContent(TranslateableElement $translateableElement)
    {
        $html = '<div class="tab-content">';
        
        foreach ($translateableElement->getLocales() as $key => $locale) {
            $html .= sprintf('<div class="tab-pane %s" id="%s">', $key === 0 ? 'active' : '', $locale);
            
            $fieldset = $translateableElement->get($locale);
            /* @var $fieldset FieldsetInterface*/
            foreach ($fieldset->getElements() as $element) {
                $html .= sprintf('<div class="form-group %s">', count($element->getMessages()) > 0 ? 'has-error' : '');
                $html .= $this->renderHelper('formLabel', $element);
                $html .= '<div class="sp7"></div>';
                $html .= $this->renderHelper('formElement', $element);
                $html .= $this->renderHelper('erpFormElementErrors', $element);
                $html .= '</div>';
            } 
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}