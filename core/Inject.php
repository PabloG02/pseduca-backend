<?php

namespace Core;

use Attribute;

/**
 * Attribute for marking dependencies to be injected.
 *
 * This attribute can be used to specify dependency injection
 * on class properties or constructor parameters.
 */
#[Attribute]
class Inject {
    public function __construct(
        public ?string $service = null
    ) {}
}
