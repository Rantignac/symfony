<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Doctrine\Messenger;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

/**
 * Checks whether the connection is still open or reconnects otherwise.
 *
 * @author Fuong <insidestyles@gmail.com>
 */
class DoctrinePingConnectionMiddleware extends AbstractDoctrineMiddleware
{
    protected function handleForManager(EntityManagerInterface $entityManager, Envelope $envelope, StackInterface $stack): Envelope
    {
        if (null !== $envelope->last(ReceivedStamp::class)) {
            $this->pingConnection($entityManager);
        }

        return $stack->next()->handle($envelope, $stack);
    }

    private function pingConnection(EntityManagerInterface $entityManager)
    {
        $connection = $entityManager->getConnection();

        if (!$connection->ping()) {
            $connection->close();
            $connection->connect();
        }

        if (!$entityManager->isOpen()) {
            $this->managerRegistry->resetManager($this->entityManagerName);
        }
    }
}
