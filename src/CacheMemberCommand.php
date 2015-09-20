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
 * Class CacheMemberCommand
 * @category Project_Management
 * @package  BespokeSupport\TrelloUpdates
 * @author   Richard Seymour <web@bespoke.support>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @version  Release: (as documented)
 * @link     https://github.com/BespokeSupport/TrelloUpdates
 */
class CacheMemberCommand extends Command
{
    const COMMAND = 'bs:trello:cache:member';

    const FILE_CACHE = '/cache_member.yml';

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

        try {
            $member = $trello->get('members/me');
        } catch (\Exception $e) {
            return false;
        }

        if (!$member) {
            return false;
        }

        file_put_contents(
            dirname(__DIR__) . self::FILE_CACHE,
            Yaml::dump(
                $member
            )
        );

        return true;
    }
}
