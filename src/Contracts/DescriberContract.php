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

namespace NunoMaduro\LaravelConsoleSummary\Contracts;

use Illuminate\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;

interface DescriberContract
{
    /**
     * Describes the provided laravel console application
     * using the provided output.
     */
    public function describe(Application $application, OutputInterface $output): void;
}
