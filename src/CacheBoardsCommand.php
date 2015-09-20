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
 * Class CacheBoardsCommand
 * @category Project_Management
 * @package  BespokeSupport\TrelloUpdates
 * @author   Richard Seymour <web@bespoke.support>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @version  Release: (as documented)
 * @link     https://github.com/BespokeSupport/TrelloUpdates
 */
class CacheBoardsCommand extends Command
{
    const COMMAND = 'bs:trello:cache:boards';

    const FILE_CACHE = '/cache_boards.yml';

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

        $member = HelperCommand::getTrelloMember($output);

        if (!$member || empty($member['idBoards'])) {
            return false;
        }

        $boards = [
            'boards' => []
        ];

        foreach ($member['idBoards'] as $board) {
            if (in_array($board, HelperCommand::$ignoredBoards)) {
                continue;
            }

            $trelloBoard = $trello->getBoard($board);

            if (!$trelloBoard) {
                $error = "Board $board failed to load";
                $output->writeln("<error>$error</error>");
                continue;
            }

            if ($trelloBoard->closed) {
                continue;
            }

            $boards['boards'][$trelloBoard->id] = [
                'id' => $trelloBoard->id,
                'name' => $trelloBoard->name,
                'desc' => $trelloBoard->desc,
                'url' => $trelloBoard->url,
                'prefs' => $trelloBoard->prefs,
                'labelNames' => $trelloBoard->labelNames,
            ];
        }

        file_put_contents(
            dirname(__DIR__) . self::FILE_CACHE,
            Yaml::dump($boards)
        );

        return true;
    }
}
