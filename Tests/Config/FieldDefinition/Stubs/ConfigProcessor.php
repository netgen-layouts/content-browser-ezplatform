<?php

namespace Netgen\Bundle\ContentBrowserBundle\Tests\Config\FieldDefinition\Stubs;

use Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition\ConfigProcessor as BaseConfigProcessor;

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
     * Returns the value type which this config supports.
     *
     * @return string
     */
    public function getValueType()
    {
        return 'ezcontent';
    }

    /**
     * Processes the given config.
     *
     * @param string $configName
     * @param \Netgen\Bundle\ContentBrowserBundle\Config\ConfigurationInterface $config
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\InvalidArgumentException If config could not be found
     */
    public function processConfig($configName, $config)
    {
        $this->getFieldDefinition($configName);

        $config->setParameter('test', 'config');
    }
}
