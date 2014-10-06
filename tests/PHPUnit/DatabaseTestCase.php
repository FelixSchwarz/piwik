<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Config;
use Piwik\Db;
use Piwik\Tests\Fixture;
use Piwik\Tests\IntegrationTestCase;

/**
 * Tests extending DatabaseTestCase are much slower to run: the setUp will
 * create all Piwik tables in a freshly empty test database.
 *
 * This allows each test method to start from a clean DB and setup initial state to
 * then test it.
 *
 */
class DatabaseTestCase extends IntegrationTestCase
{
    /**
     * @var Fixture
     */
    public static $fixture;
    public static $tableData;

    /**
     * Implementation details:
     *
     * To increase speed of tests, database setup is done once in setUpBeforeClass.
     * Afterwards, the content of the tables is stored in a static class variable,
     * self::$tableData. Before each individual test, the database tables are
     * truncated and the data in self::$tableData is restored.
     *
     * If your test modifies table columns, you will need to recreate the database
     * completely. This can be accomplished by:
     *
     *     public function setUp()
     *     {
     *         self::$fixture->performSetUp();
     *     }
     *
     *     public function tearDown()
     *     {
     *         parent::tearDown();
     *         self::$fixture->performTearDown();
     *     }
     */
    public static function setUpBeforeClass()
    {
        static::configureFixture(static::$fixture);
        parent::setUpBeforeClass();

        self::$tableData = self::getDbTablesWithData();
    }

    public static function tearDownAfterClass()
    {
        self::$tableData = array();
    }

    /**
     * Setup the database and create the base tables for all tests
     */
    public function setUp()
    {
        parent::setUp();

        Config::getInstance()->setTestEnvironment();

        if (!empty(self::$tableData)) {
            self::restoreDbTables(self::$tableData);
        }
    }

    /**
     * Resets all caches and drops the database
     */
    public function tearDown()
    {
        self::$fixture->clearInMemoryCaches();

        parent::tearDown();
    }

    protected static function configureFixture($fixture)
    {
        $fixture->loadTranslations = false;
        $fixture->createSuperUser = false;
        $fixture->configureComponents = false;
    }
}

DatabaseTestCase::$fixture = new Fixture();