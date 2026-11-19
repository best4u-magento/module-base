<?php
/**
 * Copyright © Best4u Media B.V. All rights reserved.
 */

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Best4u_Base', __DIR__);

// Compatibility with version 1.x: class names with the old namespace Best4u\Backend\ (e.g. in custom code or
// content saved in the database) are loaded from the new namespace Best4u\Base\. The old name becomes an alias of
// the new class.
// @deprecated Will be removed in the next major version.
spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'Best4u\\Backend\\')) {
        $newClass = 'Best4u\\Base\\' . substr($class, 15);
        if (class_exists($newClass) || interface_exists($newClass) || trait_exists($newClass)) {
            class_alias($newClass, $class);
        }
    }
});
