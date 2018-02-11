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
use Symfony\Component\Console\Output\OutputInterface;
use NunoMaduro\LaravelConsoleSummary\Contracts\DescriberContract;

/**
 * This is an Laravel Console Summary Text Describer implementation.
 */
class Describer implements DescriberContract
{
    /**
     * The bigger command name width.
     *
     * @internal
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

        $output->write("\n");
    }

    /**
     * Describes the application title.
     *
     * @param \Illuminate\Console\Application $application
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \NunoMaduro\LaravelConsoleSummary\Contracts\DescriberContract
     */
    protected function describeTitle(Application $application, OutputInterface $output): DescriberContract
    {
        $output->write(
            "\n<fg=white;options=bold>{$application->getName()} </> <fg=green;options=bold>{$application->getVersion()}</>\n\n"
        );

        return $this;
    }

    /**
     * Describes the application title.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \NunoMaduro\LaravelConsoleSummary\Contracts\DescriberContract
     */
    protected function describeUsage(OutputInterface $output): DescriberContract
    {
        $binary = ARTISAN_BINARY;
        $output->write("<fg=yellow;options=bold>USAGE:</> $binary <command> [options] [arguments]\n");

        return $this;
    }

    /**
     * Describes the application commands.
     *
     * @param \Illuminate\Console\Application $application
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \NunoMaduro\LaravelConsoleSummary\Contracts\DescriberContract
     */
    protected function describeCommands(Application $application, OutputInterface $output): DescriberContract
    {
        $this->width = 0;

        $namespaces = collect($application->all())->groupBy(function ($command) {
            $nameParts = explode(':', $name = $command->getName());
            $this->width = max($this->width, mb_strlen($name));

            return isset($nameParts[1]) ? $nameParts[0] : '';
        })->toArray();

        ksort($namespaces);
        collect($namespaces)->map(function ($commands) use ($output) {
            $output->write("\n");

            collect($commands)->each(function ($command) use ($output) {
                $output->write(sprintf(
                    "  <fg=green>%s</>%s%s\n",
                    $command->getName(),
                    str_repeat(' ', $this->width - mb_strlen($command->getName()) + 1),
                    $command->getDescription()
                ));
            });
        });

        return $this;
    }
}
