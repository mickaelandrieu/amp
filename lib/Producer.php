<?php

namespace Amp;

/**
 * Observable implementation that should not be returned from a public API, but used only internally.
 */
final class Producer implements Observable {
    use Internal\Producer {
        init as public __construct;
        emit as public;
        resolve as public;
        fail as public;
    }
}
