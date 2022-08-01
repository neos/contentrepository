<?php

/*
 * This file is part of the Neos.ContentRepository package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

declare(strict_types=1);

namespace Neos\ContentRepository\Projection\ContentStream;

use Neos\ContentRepository\Factory\ProjectionFactoryDependencies;
use Neos\ContentRepository\Infrastructure\DbalClientInterface;
use Neos\ContentRepository\Projection\CatchUpHookFactoryInterface;
use Neos\ContentRepository\Projection\ProjectionFactoryInterface;
use Neos\ContentRepository\Projection\ProjectionInterface;
use Neos\ContentRepository\Projection\Projections;

class ContentStreamProjectionFactory implements ProjectionFactoryInterface
{
    public function __construct(
        private readonly DbalClientInterface $dbalClient
    ) {
    }

    public function build(ProjectionFactoryDependencies $projectionFactoryDependencies, array $options, CatchUpHookFactoryInterface $catchUpHookFactory, Projections $projectionsSoFar): ProjectionInterface
    {
        return new ContentStreamProjection(
            $projectionFactoryDependencies->eventNormalizer,
            $this->dbalClient,
            sprintf('neos_cr_%s_projection_%s', $projectionFactoryDependencies->contentRepositoryIdentifier, strtolower(str_replace('Projection', '', (new \ReflectionClass(ContentStreamProjection::class))->getShortName()))),
        );
    }
}