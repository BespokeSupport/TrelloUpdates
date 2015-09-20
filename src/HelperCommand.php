<?php
/**
 * Trello API Management
 *
 * PHP version 5.4
 *
 * @category Project_Management
 * @package  TrelloUpdates
 * @author   Richard Seymour <web@bespoke.support>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @version  GIT: $Id
 * @link     https://github.com/BespokeSupport/TrelloUpdates
 */

namespace BespokeSupport\TrelloUpdates;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Trello\Client;

/**
 * Class HelperCommand
 * @category Project_Management
 * @package  BespokeSupport\TrelloUpdates
 * @author   Richard Seymour <web@bespoke.support>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @version  Release: (as documented)
 * @link     https://github.com/BespokeSupport/TrelloUpdates
 */
class HelperCommand extends Command
{
    const COMMAND = 'bs:trello';
    const ERROR_CREDENTIALS = 'Invalid Credentials. Run bs:trello:credentials';
    const ERROR_MEMBER = 'Invalid User/Member. Run bs:trello:cache:member';
    const ERROR_BOARDS = 'Invalid Boards. Run bs:trello:cache:boards';
    const ERROR_LIST = 'Invalid Lists. Run bs:trello:cache:lists';

    const API_URL_BASE = 'https://trello.com/1';

    public static $ignoredBoards = [
        '51fd80084fd3cf0553000a76', // Welcome Board
    ];

    /**
     * Configure
     * @return bool
     */
    public function configure()
    {
        $this->setName(self::COMMAND);

        return true;
    }

    /**
     * Run the command
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     * @return bool
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $credentialsFile = dirname(__DIR__) . CredentialsCommand::FILE_CREDENTIALS;

        if (!file_exists($credentialsFile)) {
            $command = new ArrayInput(['command' => 'bs:trello:credentials']);
            $this->getApplication()->run($command, $output);
        }

        if (!file_exists($credentialsFile)) {
            $output->writeln('<error>' . self::ERROR_CREDENTIALS . '</error>');

            return false;
        }

        $credentials = Yaml::parse(file_get_contents($credentialsFile));

        if (!is_array($credentials)
            || empty($credentials['api_key'])
            || empty($credentials['api_token'])
        ) {
            $output->writeln('<error>' . self::ERROR_CREDENTIALS . '</error>');

            return false;
        }

        $member = self::getTrelloMember($output, false);
        if (!$member) {
            $command = new ArrayInput(['command' => 'bs:trello:cache:member']);
            $this->getApplication()->run($command, $output);
            $member = self::getTrelloMember($output);
            if (!$member) {
                return false;
            }
        }

        $boards = self::getCachedBoards($output, false);
        if (!$boards) {
            $command = new ArrayInput(['command' => 'bs:trello:cache:boards']);
            $this->getApplication()->run($command, $output);
            $boards = self::getCachedBoards($output);
            if (!$boards) {
                return false;
            }
        }

        $lists = self::getCachedLists($output, false, false);
        if (!$lists) {
            $command = new ArrayInput(['command' => 'bs:trello:cache:lists']);
            $this->getApplication()->run($command, $output);
            $lists = self::getCachedLists($output);
            if (!$lists) {
                return false;
            }
        }

        $command = new ArrayInput(['command' => 'bs:trello:select']);
        $this->getApplication()->run($command, $output);

        return true;
    }

    /**
     * Get Trello Client
     * @param OutputInterface $output Output
     * @return null|Client
     */
    public static function getTrelloClient(OutputInterface $output)
    {
        $credentials = self::getTrelloCredentials($output);

        if (!is_array($credentials)
            || empty($credentials['api_key'])
            || empty($credentials['api_token'])
        ) {
            $error = self::ERROR_CREDENTIALS;
            $output->writeln("<error>$error</error>");

            return null;
        }

        $trello = new Client(
            $credentials['api_key'],
            $credentials['api_token'],
            null // secret not needed
        );

        return $trello;
    }

    /**
     * Get array of credentials
     * @param OutputInterface $output Output
     * @return bool|array
     */
    public static function getTrelloCredentials(OutputInterface $output)
    {
        $credentialsFile = dirname(__DIR__) . CredentialsCommand::FILE_CREDENTIALS;

        if (!file_exists($credentialsFile)) {
            $error = self::ERROR_CREDENTIALS;
            $output->writeln("<error>$error</error>");

            return false;
        }

        $credentials = Yaml::parse(file_get_contents($credentialsFile));

        if (!is_array($credentials)
            || empty($credentials['api_key'])
            || empty($credentials['api_token'])
        ) {
            $error = self::ERROR_CREDENTIALS;
            $output->writeln("<error>$error</error>");

            return false;
        }

        return $credentials;
    }

    /**
     * Get Trello User (Member)
     * @param OutputInterface $output Output
     * @param bool            $alert  Alert user on fail
     * @return array|bool
     */
    public static function getTrelloMember(OutputInterface $output, $alert = true)
    {
        $file = dirname(__DIR__) . CacheMemberCommand::FILE_CACHE;

        if (!file_exists($file)) {
            if ($alert) {
                $error = self::ERROR_MEMBER;
                $output->writeln("<error>$error</error>");
            }
            return false;
        }

        $member = Yaml::parse(
            file_get_contents(
                dirname(__DIR__) . CacheMemberCommand::FILE_CACHE
            )
        );

        if (!$member || empty($member['idBoards'])) {
            $error = self::ERROR_MEMBER;
            $output->writeln("<error>$error</error>");

            return false;
        }

        return $member;
    }

    /**
     * Get Trello Boards for User
     * @param OutputInterface $output Output
     * @param bool            $alert  Alert on fail
     * @return array|bool
     */
    public static function getCachedBoards(OutputInterface $output, $alert = true)
    {
        $file = dirname(__DIR__) . CacheBoardsCommand::FILE_CACHE;

        if (!file_exists($file)) {
            if ($alert) {
                $error = self::ERROR_BOARDS;
                $output->writeln("<error>$error</error>");
            }

            return false;
        }

        $boards = Yaml::parse(
            file_get_contents(
                dirname(__DIR__) . CacheBoardsCommand::FILE_CACHE
            )
        );

        if (!$boards) {
            $error = self::ERROR_BOARDS;
            $output->writeln("<error>$error</error>");

            return false;
        }

        return $boards;
    }

    /**
     * Get Trello Lists for User
     * @param OutputInterface $output        Output
     * @param bool            $fetchOrCreate Create file if not found
     * @param bool            $alert         Alert file if not found
     * @return array|bool
     */
    public static function getCachedLists(
        OutputInterface $output,
        $fetchOrCreate = false,
        $alert = true
    ) {
        $file = dirname(__DIR__) . CacheListsCommand::FILE_CACHE;

        if (!file_exists($file)) {
            if ($fetchOrCreate) {
                $listCache = [
                    'lists' => []
                ];
                file_put_contents(
                    dirname(__DIR__) . CacheListsCommand::FILE_CACHE,
                    Yaml::dump($listCache)
                );

                return $listCache;
            } else {
                if ($alert) {
                    $error = self::ERROR_LIST;
                    $output->writeln("<error>$error</error>");
                }

                return false;
            }
        }

        $lists = Yaml::parse(
            file_get_contents(
                dirname(__DIR__) . CacheListsCommand::FILE_CACHE
            )
        );

        if (!$lists) {
            $error = self::ERROR_LIST;
            $output->writeln("<error>$error</error>");

            return false;
        }

        return $lists;
    }
}
