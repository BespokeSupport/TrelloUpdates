<?php
/**
 * Requests Trello API Credentials
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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BoardConfigCommand
 * @category Project_Management
 * @package  BespokeSupport\TrelloUpdates
 * @author   Richard Seymour <web@bespoke.support>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @version  Release: (as documented)
 * @link     https://github.com/BespokeSupport/TrelloUpdates
 */
class BoardConfigCommand extends Command
{
    const COMMAND = 'bs:trello:config';

    /**
     * Trello Board Key
     * @var string
     */
    protected $boardKey;

    /**
     * Pass board from board select
     * @param null|string $boardKey Board
     */
    public function __construct($boardKey = null)
    {
        $this->boardKey = $boardKey;
        parent::__construct();
    }

    /**
     * Configure
     * @return bool
     */
    public function configure()
    {
        $this->setName(self::COMMAND);
        $this->addArgument('boardKey', InputArgument::OPTIONAL, 'Trello Board Key');

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
        $boards = HelperCommand::getCachedBoards($output);

        $lists = HelperCommand::getCachedLists($output);

        $boardKey = $input->getArgument('boardKey');
        if ($boardKey) {
            $this->boardKey = $boardKey;
        }

        if (!array_key_exists($boardKey, $boards)) {
            $output->writeln("<error>".HelperCommand::ERROR_BOARDS."</error>");
            return false;
        }

        $output->writeln("<info>Lists for '{$boards[$boardKey]['name']}'</info>");

        $boardLists = $lists['lists'][$this->boardKey];

        foreach ($boardLists as $id => $listData) {
            $output->writeln("\t{$listData['name']}");
        }

        $output->writeln('');

        return true;
    }
}
