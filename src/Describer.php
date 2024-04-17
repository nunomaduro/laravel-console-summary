<?php

declare(strict_types=1);

/**
 * This file is part of Laravel Console Summary.
 *
 * (c) Nuno Maduro <enunomaduro@gmail.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace NunoMaduro\LaravelConsoleSummary;

use Illuminate\Console\Application;
use Illuminate\Contracts\Config\Repository;
use NunoMaduro\LaravelConsoleSummary\Contracts\DescriberContract;
use Symfony\Component\Console\Output\OutputInterface;

class Describer implements DescriberContract
{
    /**
     * The bigger command name width.
     */
    private int $width = 0;

    public function __construct(private readonly Repository $config)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function describe(Application $application, OutputInterface $output): void
    {
        $this->describeTitle($application, $output)
            ->describeUsage($output)
            ->describeCommands($application, $output);
    }

    /**
     * Describes the application title.
     */
    protected function describeTitle(Application $application, OutputInterface $output): DescriberContract
    {
        $output->write(
            "\n  <fg=white;options=bold>{$application->getName()} </> <fg=green;options=bold>{$application->getVersion()}</>\n\n"
        );

        return $this;
    }

    /**
     * Describes the application title.
     */
    protected function describeUsage(OutputInterface $output): DescriberContract
    {
        $binary = $this->config->get('laravel-console-summary.binary', ARTISAN_BINARY);
        $output->write("  <fg=yellow;options=bold>USAGE:</> {$binary} <command> [options] [arguments]\n");

        return $this;
    }

    /**
     * Describes the application commands.
     */
    protected function describeCommands(Application $application, OutputInterface $output): DescriberContract
    {
        $this->width = 0;

        $hide = collect($this->config->get('laravel-console-summary.hide', []));

        collect($application->all())->filter(fn ($command) => ! $command->isHidden())->filter($this->filterCommands($hide))
            ->unique(fn ($command) => $command->getName())->groupBy($this->groupCommands())
            ->sortKeys()->each($this->describeCommandGroup($output));

        return $this;
    }

    protected function filterCommands(\Illuminate\Support\Collection $hide): \Closure
    {
        return function ($command) use ($hide) {
            $nameParts = explode(':', $name = $command->getName());

            $hasExactMatch = $hide->contains($command->getName());
            $hasWildcardMatch = $hide->contains($nameParts[0].':*');

            return ! $hasExactMatch && ! $hasWildcardMatch;
        };
    }

    protected function groupCommands(): \Closure
    {
        return function ($command) {
            $nameParts = explode(':', $name = $command->getName());
            $this->width = max($this->width, mb_strlen($name));

            return isset($nameParts[1]) ? $nameParts[0] : '';
        };
    }

    protected function describeCommandGroup(OutputInterface $output): \Closure
    {
        return function ($commands) use ($output) {
            $output->write("\n");

            $commands = $commands->toArray();

            $this->sortCommandsInGroup($commands);

            foreach ($commands as $command) {
                $output->write(sprintf(
                    "  <fg=green>%s</>%s%s%s\n",
                    $command->getName(),
                    str_repeat(' ', $this->width - mb_strlen($command->getName()) + 1),
                    $command->getAliases() ? '<fg=cyan>[</>'.implode('|', $command->getAliases()).'<fg=cyan>]</> ' : '',
                    $command->getDescription()
                ));
            }
        };
    }

    protected static function sortCommandsInGroup(array &$commands): void
    {
        usort($commands, function ($a, $b) {
            return strcmp($a->getName(), $b->getName());
        });
    }
}
