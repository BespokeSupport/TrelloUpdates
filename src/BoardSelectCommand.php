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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class BoardSelectCommand
 * @category Project_Management
 * @package  BespokeSupport\TrelloUpdates
 * @author   Richard Seymour <web@bespoke.support>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @version  Release: (as documented)
 * @link     https://github.com/BespokeSupport/TrelloUpdates
 */
class BoardSelectCommand extends Command
{
    const COMMAND = 'bs:trello:select';

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
     * Which Board to look at
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     * @return bool
     */
    public function selectBoard(InputInterface $input, OutputInterface $output)
    {

        $trello = HelperCommand::getTrelloClient($output);

        if (!$trello) {
            return false;
        }

        $boards = HelperCommand::getCachedBoards($output);

        if (!$boards) {
            return false;
        }

        $keys = array_keys($boards);

        $output->writeln("<info>Select a board to work with:</info>");

        foreach ($keys as $key => $board) {
            $num = $key + 1;
            $output->writeln("\t ($num)\t {$boards[$board]['name']}");
        }

        $helper = $this->getHelper('question');
        $question = new Question('Enter a Board : ');
        $answer = (int)$helper->ask($input, $output, $question);

        if (!$answer) {
            return false;
        }

        $key = $answer - 1;

        $board = $boards[$keys[$key]];

        return $board;
    }

    /**
     * Run the command
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     * @return bool
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        while (($board = $this->selectBoard($input, $output))) {
            $output->writeln('');
            $output->writeln("<info>{$board['name']}</info>");

            $lists = HelperCommand::getCachedLists($output);

            $boardLists = $lists[$board['id']];

            foreach ($boardLists as $listData) {
                $output->writeln("\t{$listData['name']}");
            }

            $output->writeln('');
        }

        $output->writeln('');

        return true;
    }
}
