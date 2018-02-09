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

use Illuminate\Contracts\Container\Container;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use NunoMaduro\LaravelConsoleSummary\Contracts\DescriberContract;

/**
 * This is an Laravel Console Summary Command implementation.
 */
class SummaryCommand extends ListCommand
{
    /**
     * The supported format.
     */
    private const FORMAT = 'txt';

    /**
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * SummaryCommand constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct('list');

        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if ($input->getOption('format') === static::FORMAT && ! $input->getOption('raw')) {
            $this->container[DescriberContract::class]->describe($this->getApplication(), $output);
        } else {
            parent::execute($input, $output);
        }
    }
}
