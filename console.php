<?php
/**
 * Trello Board + List Updates
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

use BespokeSupport\TrelloUpdates\BoardSelectCommand;
use BespokeSupport\TrelloUpdates\CacheBoardsCommand;
use BespokeSupport\TrelloUpdates\CacheListsCommand;
use BespokeSupport\TrelloUpdates\CacheMemberCommand;
use BespokeSupport\TrelloUpdates\CredentialsCommand;
use BespokeSupport\TrelloUpdates\HelperCommand;

require_once dirname(__FILE__) . '/vendor/autoload.php';

$application = new \Symfony\Component\Console\Application();

// let commands run after each other
$application->setAutoExit(false);

// master command
$application->add(new HelperCommand());

// sub command
$application->add(new CredentialsCommand());

// sub command
$application->add(new CacheMemberCommand());

// sub command
$application->add(new CacheBoardsCommand());

// sub command
$application->add(new CacheListsCommand());

// sub command
$application->add(new BoardSelectCommand());

// run it
$application->run();
