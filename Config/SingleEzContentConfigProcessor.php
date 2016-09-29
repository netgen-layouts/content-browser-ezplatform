<?php

namespace Netgen\Bundle\ContentBrowserBundle\Config;

use Netgen\Bundle\ContentBrowserBundle\Item\EzContent\Item;

class SingleEzContentConfigProcessor implements ConfigProcessorInterface
{
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
     * Returns if the processor supports the config with provided name.
     *
     * @param string $configName
     *
     * @return bool
     */
    public function supports($configName)
    {
        return $configName === 'ezcontent-single';
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
        $config->setMaxSelected(1);
    }
}
