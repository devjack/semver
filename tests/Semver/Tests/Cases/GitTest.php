<?php
/**
 * Semver\Tests\Git class
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

namespace Semver\Tests\Cases;

use Semver\Git;

/**
 * Tests for the \Semver\Git
 *
 * Represents a given version
 *
 * @category Semver
 * @package  Semver
 * @author   Jack Skinner <sydnerdrage@gmail.com>
 * @license  MIT http://opensource.org/licenses/MIT
 * @link     http://www.sydnerdrage.com/semver
 */

class GitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Project root
     *
     * @var string $root
     */
    protected $root = "";


    /**
     * Test is running from a git WC?
     *
     * @var bool $wc
     */
    protected $wc = false;

    /**
     * Temporary working directory to test with
     *
     * @var string $tmpwc
     */
    protected $tmpwc = "";

    /**
     * List of temp locations created
     *
     * @var array $tmps
     */
    protected $tmps = array();

    /**
     * Setup the test case
     *
     * @return null
     */
    public function setUp()
    {
        if ('git version' !== substr(`git --version`, 0, 11)) {
            $this->markTestSkipped("Git is not installed");
        }

        $this->wc = ('fatal' === substr(`git rev-parse --show-toplevel 2>&1`, 0, 5));

        // Create a temporary git repository to test with
        $temp = $this->createTempDirectory();
        $this->gitInit($temp);
        $this->tmpwc = $temp;

        $this->root = realpath(__DIR__ . "../../../../../");
    }

    /**
     * Clean up after ourselves
     *
     * @return null
     */
    public function tearDown()
    {
        $this->cleanupTempDirectory($this->tmpwc);
        foreach ($this->tmps as $d) {
            $this->cleanupTempDirectory($d);
        }
    }

    /**
     * Removes the temporary directory
     *
     * @param String $dir Directory to clean
     *
     * @return null
     */
    protected function cleanupTempDirectory($dir)
    {
        if (in_array($dir, $this->tmps)) {
            ` rm -rf $dir`;
            unset($this->tmps[$dir]);
        }
    }

    /**
     * Create an empty git repository
     *
     * @param string $wc Working Copy location
     *
     * @return null
     */
    protected function gitInit($wc)
    {
        `cd $wc && git init .`;
    }

    /**
     * Commits random content into a repository to ensure it's not empty
     *
     * @param string $wc Working Copy dir
     *
     * @return null
     */
    protected function addACommitTo($wc)
    {
        `cd $wc && echo test >> test.txt && git add test.txt && git commit -m"test"`;
    }

    /**
     * Tags HEAD as $tag in the $wc working copy provided
     *
     * @param string $wc  Working Copy
     * @param string $tag Tag name
     *
     * @return null
     */
    protected function tagACommitIn($wc, $tag)
    {
        `cd $wc && git tag $tag -m"test tag" HEAD`;
    }


    /**
     * Create a temporary directory to test non git working copy
     *
     * @return string
     * @throws \AccessDeniedException
     */
    protected function createTempDirectory()
    {
        $ds = DIRECTORY_SEPARATOR;
        $tmp = sys_get_temp_dir() . $ds . "Semvertest" . mt_rand(0, 1000);
        if (file_exists($tmp) && is_dir($tmp)) {
            `rm $tmp -r`;
        }
        mkdir($tmp);
        if (is_dir($tmp)) {
            $this->tmps[] = $tmp;
            return $tmp;
        }
        throw new \AccessDeniedException("Could not create tmp directory $tmp");
    }

    /**
     * Tests if the constructed object represents a working copy
     *
     * @return null
     */

    public function testDirIsWorkingCopy()
    {

        $dir = $this->tmpwc;

        $git = new Git($dir);
        $expects = realpath($dir);
        $this->assertEquals($expects, $git->root());
        $this->assertEquals(true, $git->isWorkingCopy());
    }

    /**
     * Tests if the constructed object doesn't represent a working copy
     *
     * @return null
     */
    public function testDirIsNotAWorkingCopy()
    {
        $temp = $this->createTempDirectory();
        $expects = realpath($temp);

        $git = new Git($temp);
        $this->assertEquals($expects, $git->root());
        $this->assertEquals(false, $git->isWorkingCopy());
    }

    /**
     * Tests if the default directory is a working copy
     *
     * @return null
     */

    public function testDefaultDirIsAWorkingCopy()
    {
        $git = new Git();
        $expects = $this->root;
        $cmd = "cd $expects && git rev-parse --show-toplevel 2>&1";
        if ('fatal' === substr(`$cmd`, 0, 5)) {
            $reason = "Test is not running from a git working directory.";
            $this->markTestSkipped($reasons);
        }

        $this->assertEquals($expects, $git->root());
    }

    /**
     * Tests if the default directroy is not a working copy
     *
     * @return null
     */

    public function testDefaultDirIsNotAWorkingCopy()
    {
        $git = new Git();
        $expects = $this->root . "/src/Semver";
        if ('fatal' !== substr(`git rev-parse --show-toplevel 2>&1`, 0, 5)) {
            $this->markTestSkipped("Test is running from a git working directory.");
        }
        $this->assertEquals($expects, $git->root());
    }


    /**
     * Tests git git describe on a tagless repository
     *
     * @return null;
     */
    public function testTaglessDescribe()
    {
        $wc = $this->createTempDirectory();
        $this->gitInit($wc);
        $git = new Git($wc);

        $this->addACommitTo($wc);
        echo $git->describe();
        $this->assertEmpty($git->describe());
    }

    /**
     * Tests versions given a working copy state:
     *  - no commits
     *  - some commits but no tag
     *  - valid tagged version
     * Note: assumes default prefix of "v"
     *
     * @return null
     */
    public function testversionFromWorkingCopyState()
    {
        // We'll do this in a fresh test location
        $wc = $this->createTempDirectory();
        $this->gitInit($wc);
        $git = new Git($wc);

        $this->assertEquals("0.0.0-0-g00000", $git->version());

        $this->addACommitTo($wc);
        $this->assertEquals("0.0.0-1-g00000", $git->version());

        $this->tagACommitIn($wc, "v0.0.1");
        $this->assertContains("v0.0.1-0", $git->version());

        $this->cleanupTempDirectory($wc);
    }

    /**
     * Tests versions for a non-default prefix
     *
     * @return null
     */
    public function testversionWithNonDefaultTagPrefix()
    {
        // We'll do this in a fresh test location
        $wc = $this->createTempDirectory();
        $this->gitInit($wc);
        $git = new Git($wc, 'release-');

        $this->assertEquals("0.0.0-0-g00000", $git->version());

        $this->addACommitTo($wc);
        $this->assertEquals("0.0.0-1-g00000", $git->version());

        $this->tagACommitIn($wc, "release-0.0.1");
        $this->assertContains("release-0.0.1-0", $git->version());

        $this->cleanupTempDirectory($wc);
    }
}
