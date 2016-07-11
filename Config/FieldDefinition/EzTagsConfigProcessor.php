<?php

namespace Netgen\Bundle\ContentBrowserBundle\Config\FieldDefinition;

use Netgen\Bundle\ContentBrowserBundle\Item\EzTags\Item;

class EzTagsConfigProcessor extends ConfigProcessor
{
    /**
     * Returns the field type identifier for this config processor.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return 'eztags';
    }

    /**
     * Returns the value type which this config processor supports.
     *
     * @return string
     */
    public function getValueType()
    {
        return Item::TYPE;
    }

    /**
     * Loads the configuration by its name.
     *
     * @param string $configName
     * @param \Netgen\Bundle\ContentBrowserBundle\Config\ConfigurationInterface $config
     *
     * @throws \Netgen\Bundle\ContentBrowserBundle\Exceptions\InvalidArgumentException If config could not be found
     */
    public function processConfig($configName, $config)
    {
        $fieldDefinition = $this->getFieldDefinition($configName);

        $config->setSections(array($fieldDefinition->fieldSettings['subTreeLimit']));
        $config->setMaxSelected($fieldDefinition->fieldSettings['maxTags']);
    }
}
