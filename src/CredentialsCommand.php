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
use Symfony\Component\Yaml\Yaml;

/**
 * Class CredentialsCommand
 * @category Project_Management
 * @package  BespokeSupport\TrelloUpdates
 * @author   Richard Seymour <web@bespoke.support>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @version  Release: (as documented)
 * @link     https://github.com/BespokeSupport/TrelloUpdates
 */
class CredentialsCommand extends Command
{
    const COMMAND = 'bs:trello:credentials';
    const PACKAGE_NAME = 'BespokeSupportTrelloEmails';
    const FILE_CREDENTIALS = '/credentials.yml';
    const URL_TOKEN = '/connect?key=%s&name=%s&response_type=token&expiration=never';
    const HELP_TOKEN = 'Visit the following URL then copy & paste your access token';

    protected $credentials = [
        'api_key' => null,
        'api_token' => null
    ];

    /**
     * Configure the command
     * @return void
     */
    public function configure()
    {
        $this->setName(static::COMMAND);
    }

    /**
     * Check for credentials.yml
     * @return bool
     */
    public static function configExists()
    {
        return file_exists(dirname(dirname(__FILE__)) . self::FILE_CREDENTIALS);
    }

    /**
     * Question
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     * @return bool
     */
    protected function questionKey(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $question = new Question('Trello API Key: ', false);

        $answer = $helper->ask($input, $output, $question);

        if ($answer) {
            $this->credentials['api_key'] = $answer;

            return true;
        } else {
            $output->writeln("<error>Please enter the API Key</error>");
            $this->questionKey($input, $output);
        }

        return false;
    }

    /**
     * Question
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     * @return bool
     */
    protected function questionToken(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $output->writeln('');

        $output->writeln('<info>' . self::HELP_TOKEN . '</info>');

        $tokenUrl = sprintf(
            HelperCommand::API_URL_BASE . self::URL_TOKEN,
            $this->credentials['api_key'],
            self::PACKAGE_NAME
        );

        $output->writeln('<info>' . $tokenUrl . '</info>');

        $output->writeln('');

        $question = new Question('Trello API Token: ', false);

        $answer = $helper->ask($input, $output, $question);

        if ($answer) {
            $this->credentials['api_token'] = $answer;

            return true;
        } else {
            $output->writeln("<error>Please enter the API Token</error>");
            $this->questionToken($input, $output);
        }

        return false;
    }

    /**
     * Question
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     * @return bool
     */
    protected function startQuestions(InputInterface $input, OutputInterface $output)
    {
        $this->questionKey($input, $output);

        $this->questionToken($input, $output);

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
        $output->writeln('');
        $output->writeln("<info>Trello Email Updates</info>");

        $help = <<<TAG
This application connects to your Trello account and emails you with Card Lists.
It will ask you for your API Key & provide you with a URL to access.
The URL will grant the application the ability to download your Boards and Cards.
TAG;

        $output->writeln($help);
        $output->writeln('API Key Available : https://trello.com/app-key');
        $output->writeln('');

        $this->startQuestions($input, $output);

        if ($this->credentials['api_key'] && $this->credentials['api_token']) {
            file_put_contents(
                dirname(__DIR__) . self::FILE_CREDENTIALS,
                Yaml::dump($this->credentials)
            );
        }

        return true;
    }
}
