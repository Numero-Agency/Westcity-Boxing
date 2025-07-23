<?php

namespace Breakdance\Experiments;

/**
 * @return string[]
 */
function getExperiments() {
    /** @var string[] $experiments */
    $experiments = defined( 'BREAKDANCE_EXPERIMENTS' ) ? BREAKDANCE_EXPERIMENTS : [];
    return $experiments;
}

/**
 * @param string $experiment
 * @return bool
 */
function isExperimentEnabled( $experiment ) {
    return in_array( $experiment, getExperiments() );
}
