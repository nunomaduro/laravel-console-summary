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
use NunoMaduro\LaravelConsoleSummary\Contracts\DescriberContract;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This is an Laravel Console Summary Text Describer implementation.
 */
class Describer implements DescriberContract
{
    /**
     * The bigger command name width.
     *
     * @var int
     */
    private $width = 0;

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
     *
     * @param  \Illuminate\Console\Application  $application
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return \NunoMaduro\LaravelConsoleSummary\Contracts\DescriberContract
     */
    protected function describeTitle(Application $application, OutputInterface $output): DescriberContract
    {
        $output->write(
            "<fg=white;options=bold>{$application->getName()}</> <fg=green>{$application->getVersion()}</>\n\n"
        );

        return $this;
    }

    /**
     * Describes the application title.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return \NunoMaduro\LaravelConsoleSummary\Contracts\DescriberContract
     */
    protected function describeUsage(OutputInterface $output): DescriberContract
    {
        $binary = ARTISAN_BINARY;
        $output->write("<fg=yellow>Usage:</>\n  <fg=white;options=bold>$binary</> <command> [options] [arguments]\n");

        return $this;
    }

    /**
     * Describes the application commands.
     *
     * @param  \Illuminate\Console\Application  $application
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return \NunoMaduro\LaravelConsoleSummary\Contracts\DescriberContract
     */
    protected function describeCommands(Application $application, OutputInterface $output): DescriberContract
    {
        $this->width = 0;

        $hide = collect(config('laravel-console-summary.hide'));

        $output->write("\n<comment>Available commands:</comment>");
        $namespaces = collect($application->all())->filter(function ($command) {
            return ! $command->isHidden();
        })->filter(function ($command) use ($hide) {
            $nameParts = explode(':', $name = $command->getName());

            $hasExactMatch = $hide->contains($command->getName());
            $hasWildcardMatch = $hide->contains($nameParts[0].':*');

            return ! $hasExactMatch && ! $hasWildcardMatch;
        })->unique(function ($command) {
            return $command->getName();
        })->groupBy(function ($command) {
            $nameParts = explode(':', $name = $command->getName());
            $this->width = max($this->width, mb_strlen($name));

            return isset($nameParts[1]) ? $nameParts[0] : '';
        })->sortKeys()->each(function ($commands,$key) use ($output) {
            $output->write(sprintf(" <comment>%s</comment>\n",$key));

            $commands = $commands->toArray();

            usort($commands, function ($a, $b) {
                return strcmp($a->getName(), $b->getName());
            });

            foreach ($commands as $command) {
                $output->write(sprintf(
                    "  <fg=green>%s</>%s%s%s\n",
                    $command->getName(),
                    str_repeat(' ', $this->width - mb_strlen($command->getName()) + 1),
                    $command->getAliases() ? '<fg=cyan>[</>'.implode('|', $command->getAliases()).'<fg=cyan>]</> ' : '',
                    $command->getDescription()
                ));
            }
        });

        return $this;
    }
}
