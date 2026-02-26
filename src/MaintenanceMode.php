<?php

declare(strict_types=1);

/**
 * Ability to easily toggle maintenance mode via CLI. To run this command:
 *
 * 		sake dev/tasks/MaintenanceMode --mode=on|off
 *
 *
 * @package maintenancemode
 *
 * @author Patrick Nelson <pat@catchyour.com>
 *
 * @since 2015-10-08
 */
namespace dljoseph\MaintenanceMode;

use Exception;
use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\SiteConfig\SiteConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class MaintenanceMode extends BuildTask
{
    protected string $title = 'Maintance Mode Task';

    protected static string $description = 'Ability to easily toggle maintenance mode via CLI.';

    protected $enabled = true;

    private static string $segment = 'MaintenanceMode';

    public function getOptions(): array
    {
        return [
            new InputOption('mode', null, InputOption::VALUE_REQUIRED, "Set maintenance mode 'on' or 'off'"),
        ];
    }

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        try {
            $arg = $input->getOption('mode');
            if (empty($arg) || !in_array(strtolower($arg), ['on', 'off'])) {
                throw new Exception("Please provide a valid --mode argument ('on' or 'off').", 1);
            }

            $arg = strtolower($arg);

            // Get and write site configuration now.
            $config = SiteConfig::current_site_config();
            $previous = (empty($config->MaintenanceMode) ? 'off' : 'on');
            $config->MaintenanceMode = ($arg === 'on');
            $config->write();

            // Output status and exit.
            if ($arg !== $previous) {
                $output->writeln(sprintf("Maintenance mode is now '%s'.", $arg));
            } else {
                $output->writeln(sprintf("NOTE: Maintenance mode was already '%s' (nothing has changed).", $arg));
            }
        } catch (Exception $exception) {
            $output->writeln('ERROR: ' . $exception->getMessage());
            if ($exception->getCode() <= 2) {
                $output->writeln('Usage: sake dev/tasks/MaintenanceMode --mode=[on|off]');
            }
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}