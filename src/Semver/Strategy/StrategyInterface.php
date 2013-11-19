<?php

/**
 *  Semver\Strategy\StrategyInterface  class
 *
 * PHP version 5.5
 *
 * @category 
 * @package  semver
 * @author   Jack Skinner <sydnerdrage@gmail.com>
 * @license  MIT http://opensource.org/licenses/MIT
 * @link     http://www.sydnerdrage.com/semver
 *
 */

namespace Semver\Strategy;

 /**
 * StrategyInterface class
 *
 * Represents a DVCS strategy
 *
 * @category Semver
 * @package  semver
 * @author   Jack Skinner <sydnerdrage@gmail.com>
 * @license  MIT http://opensource.org/licenses/MIT
 * @link     http://www.sydnerdrage.com/semver
 */

interface StrategyInterface
{
    /**
     * @return mixed Return the latest version in the repository
     */
    public function latestVersion();

    /**
     * @return mixed Return a list of all versions in the repository
     */
    public function listVersions();
}
