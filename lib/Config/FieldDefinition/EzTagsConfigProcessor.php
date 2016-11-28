<?php

namespace Netgen\ContentBrowser\Config\FieldDefinition;

use Netgen\ContentBrowser\Item\EzTags\Item;

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
     * Returns the item type which this config processor supports.
     *
     * @return string
     */
    public function getItemType()
    {
        return Item::TYPE;
    }

    /**
     * Loads the configuration by its name.
     *
     * @param string $configName
     * @param \Netgen\ContentBrowser\Config\ConfigurationInterface $config
     *
     * @throws \Netgen\ContentBrowser\Exceptions\InvalidArgumentException If config could not be found
     */
    public function processConfig($configName, $config)
    {
        $fieldDefinition = $this->getFieldDefinition($configName);

        $config->setSections(array($fieldDefinition->fieldSettings['subTreeLimit']));
        $config->setMaxSelected($fieldDefinition->fieldSettings['maxTags']);
    }
}
