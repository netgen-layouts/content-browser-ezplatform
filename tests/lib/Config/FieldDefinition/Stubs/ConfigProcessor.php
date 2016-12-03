<?php

namespace Netgen\ContentBrowser\Tests\Config\FieldDefinition\Stubs;

use Netgen\ContentBrowser\Config\FieldDefinition\ConfigProcessor as BaseConfigProcessor;

class ConfigProcessor extends BaseConfigProcessor
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
     * Processes the given config.
     *
     * @param string $configName
     * @param \Netgen\ContentBrowser\Config\ConfigurationInterface $config
     *
     * @throws \Netgen\ContentBrowser\Exceptions\InvalidArgumentException If config could not be found
     */
    public function processConfig($configName, $config)
    {
        $this->getFieldDefinition($configName);

        $config->setParameter('test', 'config');
    }
}
