<?php

namespace GlsFormModule\View\Helper;

use Zend\View\Helper\AbstractHelper as BaseAbstractHelper;
use Zend\Form\ElementInterface;
use GlsFormModule\Form\Element\TranslateableElement;
use Zend\Form\FieldsetInterface;

class FormTranslations extends BaseAbstractHelper
{
    private $errorViewHelper;
    
    private $num = 0;
    
    public function __construct($errorViewHelper) 
    {
        $this->errorViewHelper = $errorViewHelper;
    }

    public function __invoke(ElementInterface $element, $renderLabel = true, array $whiteList = array(), array $blackList = array())
    {
        if (! $element instanceof TranslateableElement) {
            throw new \InvalidArgumentException('Element not instance of GlsFormModule\Form\Element\TranslateableElement');
        }
        
        $this->num++;
        
        return $this->render($element, $renderLabel, $whiteList, $blackList);
    }
    
    private function render(TranslateableElement $element, $renderLabel, $whiteList, $blackList)
    {
        return 
            $this->renderTabs($element) .
            $this->renderTabContent($element, $renderLabel, $whiteList, $blackList)
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
            $html .= sprintf('<li class="%s"><a href="#%s"  role="tab" data-toggle="tab" >%s</a></li>', $key === 0 ? 'active' : '', $this->getTabName($locale), $label);
        }
        $html .= '</ul>';
        $html .= '<div class="sp23"></div>';
        
        return $html;
    }
    
    protected function renderTabContent(TranslateableElement $translateableElement, $renderLabel, $whiteList, $blackList)
    {
        $html = '<div class="tab-content">';
        
        foreach ($translateableElement->getLocales() as $key => $locale) {
            $html .= sprintf('<div class="tab-pane %s" id="%s">', $key === 0 ? 'active' : '', $this->getTabName($locale));
            
            $fieldset = $translateableElement->get($locale);
            /* @var $fieldset FieldsetInterface*/
            foreach ($fieldset->getElements() as $element) {
                
                
                if (! empty($whiteList) && ! $this->inList($whiteList, $fieldset->getName(), $element->getName())) {
                    continue;
                }
                
                if (! empty($blackList) && $this->inList($blackList, $fieldset->getName(), $element->getName())) {
                    continue;
                }
                
                $html .= sprintf('<div class="form-group %s">', count($element->getMessages()) > 0 ? 'has-error' : '');
                if ($renderLabel === true) {
                    $html .= $this->renderHelper('formLabel', $element);
                    $html .= '<div class="sp7"></div>';
                }
                
                $html .= $this->renderHelper('formElement', $element);
                $html .= $this->renderHelper($this->errorViewHelper, $element);
                $html .= '</div>';
            } 
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    private function getTabName($locale)
    {
        return sprintf('locale_tab_%d_%s', $this->num, $locale);
    }
    
    private function inList($list, $fieldsetName, $elemName)
    {
        foreach ($list as $item) {
            if (sprintf('%s[%s]', $fieldsetName, $item) === $elemName) {
                return true;
            }
        }
        return false;
    }
}