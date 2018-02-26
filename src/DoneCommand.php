<?php
/**
 * Removes people from DONE tasks
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
 * Class DoneCommand
 * @category Project_Management
 * @package  BespokeSupport\TrelloUpdates
 * @author   Richard Seymour <web@bespoke.support>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @version  Release: (as documented)
 * @link     https://github.com/BespokeSupport/TrelloUpdates
 */
class DoneCommand extends Command
{
    const COMMAND = 'bs:trello:done';

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
        $this->addArgument('boardKey', InputArgument::REQUIRED, 'Trello Board Key');

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

        $boardKey = $input->getArgument('boardKey');

        $config = HelperCommand::getConfig();
        if (empty($config['boards'][$boardKey])) {
            $output->writeln("<error>Board not in config</error>");
            return false;
        }

        $boardConfig = $config['boards'][$boardKey];

        $cachedBoards = HelperCommand::getCachedBoards($output);

        $cachedLists = HelperCommand::getCachedLists($output);

        $boardLists = $cachedLists[$boardKey];

        $lists = [];
        $cards = [];

        foreach ($boardLists as $listKey => $listData) {
            $lists[$listKey] = $listData['name'];
            $cards[$listKey] = [];
        }

        $trelloCards = $trello->getBoard($boardKey)->getCards();

        foreach ($trelloCards as $card) {
            if (!array_key_exists($card->idList, $lists)) {
                continue;
            }

            $cards[$card->idList][] = [
                'id' => $card->id,
                'url' => $card->shortUrl,
                'name' => $card->name,
            ];
        }

        $send = '';
        foreach ($cards as $list => $listCards) {
            $send .= "<h3>{$lists[$list]}</h3>";
            foreach ($listCards as $card) {
                $send .= "<a href='{$card['url']}' style='margin-left:30px'>{$card['name']}</a><br/>";
            }
        }


        return true;
    }
}
