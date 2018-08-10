Netgen Content Browser & eZ Platform installation instructions
==============================================================

Use Composer
------------

Run the following command to install Netgen Content Browser & eZ Platform
integration:

```
composer require netgen/content-browser-ezplatform
```

Activate the bundle
-------------------

Activate the integration bundle in your kernel class:

```
...

$bundles[] = new Netgen\Bundle\ContentBrowserBundle\NetgenContentBrowserBundle();
$bundles[] = new Netgen\Bundle\ContentBrowserEzPlatformBundle\NetgenContentBrowserEzPlatformBundle();

return $bundles;
```
