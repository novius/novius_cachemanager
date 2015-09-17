<?php

namespace Novius\CacheManager;

use Nos\Orm_Behaviour;
use Nos\Tools_Enhancer;

class Orm_Behaviour_CacheManager extends Orm_Behaviour
{
    public function __construct($class)
    {
        parent::__construct($class);
    }

    protected function cleanCache($item)
    {
        $urls = array();
        $base = \Uri::base(false);
        foreach ($this->_properties['relations'] as $property) {
            $subItems = $this->resolve($item, $property);
            foreach ($subItems as $subItem) {
                $urlEnhConfig = $subItem->behaviours('Nos\Orm_Behaviour_Urlenhancer');
                if (!empty($urlEnhConfig)) {
                    foreach ($urlEnhConfig['enhancers'] as $enhancer_name) {
                        foreach (Tools_Enhancer::url_item($enhancer_name, $subItem) as $url) {
                            $cache_path = \Nos\FrontCache::getPathFromUrl($base, parse_url($url, PHP_URL_PATH));
                            $urls[]     = $cache_path;
                        }
                    }
                }
            }
        }
        $urls = array_unique($urls);
        foreach ($urls as $url) {
            \Nos\FrontCache::forge($url)->delete();
        }
    }

    protected function resolve($item, $property)
    {
        $items    = array();
        $arrowPos = strpos($property, '->');
        if ($arrowPos !== false) {
            $propertyMatch = mb_substr($property, 0, $arrowPos);
        } else {
            $propertyMatch = $property;
        }
        $match = $item->$propertyMatch;

        if (empty($match)) {
            return array();
        }
        if ($arrowPos !== false) {
            if (is_array($match)) {
                foreach ($match as $matched) {
                    $items = \Arr::merge($items, $this->resolve($matched, mb_substr($property, $arrowPos + 2)));
                }
            } else {
                $items = \Arr::merge($items, $this->resolve($match, mb_substr($property, $arrowPos + 2)));
            }
        } else {
            if (is_array($match)) {
                $items = \Arr::merge($items, $match);
            } else {
                $items[] = $match;
            }
        }
        return $items;
    }

    public function after_update($item)
    {
        $this->cleanCache($item);
    }

    public function after_delete(\Nos\Orm\Model $item)
    {
        $this->cleanCache($item);
    }
}