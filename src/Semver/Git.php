<?php

/**
 * Semver\Git class
 *
 * PHP version 5.3
 *
 * @category Semver
 * @package  Semver
 * @author   Jack Skinner <sydnerdrage@gmail.com>
 * @license  MIT http://opensource.org/licenses/MIT
 * @link     http://www.sydnerdrage.com/semver
 *
 */

namespace Semver;

/**
 * Git class
 *
 * Wraps git system calls
 *
 * @category Semver
 * @package  Semver
 * @author   Jack Skinner <sydnerdrage@gmail.com>
 * @license  MIT http://opensource.org/licenses/MIT
 * @link     http://www.sydnerdrage.com/semver
 */

class Git
{
    /**
     * Root of the git repository.
     *
     * @var string
     */
    protected $root = "";

    /**
     * Is a valid working copy?
     *
     * @var bool $wc
     */
    protected $wc = false;

    /**
     * Prefix for tagsphpcs
     *
     * @var string $tagPrefix Prefix for a tag match
     */
    protected $tagPrefix = "v";

    /**
     * Matching regex for a version tag
     *
     * @var string $tagMatch Regex for git describe --match
     */
    protected $tagMatch = 'v[0-9]\.[0-9]\.[0-9]*';

    /**
     * Git class constructor
     *
     * @param null $root Location of the .git directory
     * @param string $tagPrefix Prefix for git tag matching
     */
    public function __construct($root = null, $tagPrefix = "v")
    {
        $this->root = realpath($root);
        if ($this->exists($root . DIRECTORY_SEPARATOR . ".git")) {
            $this->wc = true;
        }
        if (is_null($root)) {
            $root = trim(`git rev-parse --show-toplevel 2>&1`);
            if ('fatal' !== substr($root, 0, 5)) {
                $this->wc = true;
                $this->root = realpath($root);
            } else {
                $this->wc = false;
                $this->root = __DIR__;
            }
        }

        $this->tagPrefix = $tagPrefix;
        $this->tagMatch = preg_quote($tagPrefix) . '[0-9]\.[0-9]\.[0-9]*';
    }

    /**
     * Test if the directory exists
     *
     * @param string $dir Directory to check
     *
     * @return bool
     */

    protected function exists($dir)
    {
        return file_exists($dir) && is_dir($dir);
    }

    /**
     * Describe the git working copy
     *
     * @return mixed
     */

    public function describe()
    {
        $describe = `cd $this->root && git describe 2>&1`;
        if (strstr($describe, "No names found")) {
            return "";
        }
        return $describe;
    }

    /**
     * Return the git root directory (containing .git/)
     *
     * @return string
     */

    public function root()
    {
        return $this->root;
    }

    /**
     * Return the status of the 'watched' directory
     *
     * @return bool
     */
    public function isWorkingCopy()
    {
        return $this->wc;
    }

    /**
     * Return the full described version of the working copy
     *
     * @return mixed
     */

    public function version()
    {

        $gdescribe = "git describe --long --tags --match $this->tagMatch 2>&1";
        $describe = `cd $this->root  && $gdescribe`;
        if (strstr($describe, 'No names found')) {
            // Nothing has been tagged yet
            return $this->noVersion();
        }

        return $describe;
    }

    /**
     * If no tagged version exists, calculate one
     *
     * @return string Version
     */
    protected function noVersion()
    {
        // Assumes nothing has been tagged
        $head = `cd $this->root && git show HEAD 2>&1`;
        if (strstr($head, 'unknown revision or path')) {
            // Nothing has been comitted yet
            return "0.0.0-0-g00000";
        } else {
            return '0.0.0-1-g00000';
        }
    }
}
