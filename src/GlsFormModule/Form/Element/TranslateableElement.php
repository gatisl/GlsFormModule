<?php

namespace GlsFormModule\Form\Element;

use Zend\Form\Fieldset;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Doctrine\Common\Collections\ArrayCollection;

class TranslateableElement extends Fieldset
{
    private $locales = array();
    
    private $translateableFields = array();
    
    private $personalTranslationClass = 'Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation';
    
    private $localeLabels = array();
    
    private $inputFilter = array();
    
    /**
     * @var EntityManager 
     */
    private $entityManager;
    
    public function setLocales($locales) 
    {
        $this->locales = $locales;
    }
    
    public function getLocales() 
    {
        return $this->locales;
    }

    public function setTranslateableFields($translateableFields) 
    {
        $this->translateableFields = $translateableFields;
    }
    
    public function getTranslateableFields() 
    {
        return $this->translateableFields;
    }
    
    public function setEntityManager(EntityManager $entityManager) 
    {
        $this->entityManager = $entityManager;
    }

    public function getEntityManager() 
    {
        return $this->entityManager;
    }
    
    public function setPersonalTranslationClass($personalTranslationClass) 
    {
        $this->personalTranslationClass = $personalTranslationClass;
    }

    public function getPersonalTranslationClass() 
    {
        return $this->personalTranslationClass;
    }
    
    public function setLocaleLabels($localeLabels) 
    {
        $this->localeLabels = $localeLabels;
    }

    public function getLocaleLabels() 
    {
        return $this->localeLabels;
    }
    
    public function setInputFilter($inputFilter) 
    {
        $this->inputFilter = $inputFilter;
    }
    
    public function getInputFilter() 
    {
        return $this->inputFilter;
    }

                            
    /**
     * Accepted options for TranslateableElement:
     * - locales
     * - fields
     * - personalTranslationClass
     * - localeLabels
     * - entityManager
     * - inputFilter
     * 
     * @param array|Traversable $options
     * @return Translateable
     */
    public function setOptions($options)
    {
        parent::setOptions($options);

        if (isset($options['locales'])) {
            $this->setLocales($options['locales']);
        }

        if (isset($options['fields'])) {
            $this->setTranslateableFields($options['fields']);
        }
        
        if (isset($options['entityManager'])) {
            $this->setEntityManager($options['entityManager']);
        }
        
        if (isset($options['personalTranslationClass'])) {
            $this->setPersonalTranslationClass($options['personalTranslationClass']);
        }
        
        if (isset($options['localeLabels'])) {
            $this->setLocaleLabels($options['localeLabels']);
        }
        
        if (isset($options['inputFilter'])) {
            $this->setInputFilter($options['inputFilter']);
        }

        return $this;
    }
    
    public function setObject($object)
    {
        $this->object = $object;
        return $this;
    }
    
    private function createLocaleFieldset($locale)
    {
        $factory = $this->getFormFactory();
        $fieldset = $factory->createFieldset(array( 'name' => $locale, 'type' => 'Zend\Form\InputFilterProviderFieldset' ));
        /* @var $fieldset \Zend\Form\InputFilterProviderFieldset */
        $fieldset->setInputFilterSpecification($this->inputFilter);
        foreach ($this->getTranslateableFields() as $field) {
            $fieldset->add($field);
        }
        return $fieldset;
    }
    
    private function translationsToArray($translations)
    {
        $data = array();
        foreach ($translations as $translation) {
            /* @var $translation \Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation */
            $content = $translation->getContent();
            $locale = $translation->getLocale();
            $field = $translation->getField();
            if (! isset($data[$locale])) {
                $data[$locale] = array();
            }
            $data[$locale][$field] = $content;
        }
        return $data;
    }
    
    /**
     * Populate values
     *
     * @param array|Traversable $data
     * @return void
     */
    public function populateValues($data)
    {
        foreach ($this->getLocales() as $locale) {
            $fieldset = $this->createLocaleFieldset($locale);
            $this->add($fieldset);
            $fieldset->populateValues(isset($data[$locale]) ? $data[$locale] : array());
        }
    }
    
    public function allowObjectBinding($object)
    {
        return true;
    }
    
    /**
     * @return array
     */
    public function extract()
    {
        return $this->translationsToArray($this->object);
    }
    
    /**
     * Checks if this fieldset can bind data
     *
     * @return bool
     */
    public function allowValueBinding()
    {
        return true;
    }
    
    /**
     * Bind values to the bound object
     *
     * @param array $values
     * @return mixed|void
     */
    public function bindValues(array $values = array())
    {
        $collection = new ArrayCollection();
        
        $hydrator = new DoctrineObject($this->getEntityManager(), true);
        
        $findId = function($translations, $locale, $field) {
            foreach($translations as $trans) {
                if ($trans->getLocale() == $locale && $trans->getField() == $field) {
                    return $trans->getId();
                }
            }
            return null;
        };
        
        foreach ($values as $locale => $data) {
            foreach ($data as $field => $value) {
                $translation = new $this->personalTranslationClass();
                $transData = array('locale' => $locale, 'field' => $field, 'content' => $value, 'id' => $findId($this->object, $locale, $field));
                $collection->add($hydrator->hydrate($transData, $translation));
            }
        }
        return $collection;
    }
}
