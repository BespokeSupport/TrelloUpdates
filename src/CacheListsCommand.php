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
use Symfony\Component\Yaml\Yaml;

/**
 * Class CacheListsCommand
 * @category Project_Management
 * @package  BespokeSupport\TrelloUpdates
 * @author   Richard Seymour <web@bespoke.support>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @version  Release: (as documented)
 * @link     https://github.com/BespokeSupport/TrelloUpdates
 */
class CacheListsCommand extends Command
{
    const COMMAND = 'bs:trello:cache:lists';

    const FILE_CACHE = '/cache_lists.yml';

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
        $trello = HelperCommand::getTrelloClient($output);

        if (!$trello) {
            return false;
        }

        $boards = HelperCommand::getCachedBoards($output);

        if (!$boards) {
            return false;
        }

        $listCache = [
            'lists' => []
        ];

        foreach ($boards['boards'] as $board => $data) {
            $trelloBoard = $trello->getBoard($board);

            if (!$trelloBoard) {
                $error = "Board $board failed to load";
                $output->writeln("<error>$error</error>");
                continue;
            }

            $lists = $trelloBoard->getLists();

            $listCache['lists'][$board] = [];

            foreach ($lists as $list) {
                $listCache['lists'][$board][$list->id] = [
                    'name' => $list->name,
                    'pos' => $list->pos,
                    'closed' => $list->closed
                ];
            }
        }

        file_put_contents(
            dirname(__DIR__) . self::FILE_CACHE,
            Yaml::dump($listCache, 2, 4, false, true)
        );

        return true;
    }
}
