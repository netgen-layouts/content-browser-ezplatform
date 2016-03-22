<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Config\FieldDefinition\Stubs;

use Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\ConfigLoader as BaseConfigLoader;

class ConfigLoader extends BaseConfigLoader
{
    /**
     * Returns the field type identifier for this config loader.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'field_type';
    }

    /**
     * Returns the item type which this config supports.
     *
     * @return string
     */
    public function getItemType()
    {
        return 'ezcontent';
    }

    /**
     * Loads the configuration by its name.
     *
     * @param string $configName
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\InvalidArgumentException If config could not be found
     *
     * @return array
     */
    public function loadConfig($configName)
    {
        $this->getFieldDefinition($configName);

        return array('test' => 'config');
    }
}
