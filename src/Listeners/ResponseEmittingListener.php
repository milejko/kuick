<?php

/**
 * Kuick Framework (https://github.com/milejko/kuick-framework)
 *
 * @link       https://github.com/milejko/kuick-framework
 * @copyright  Copyright (c) 2010-2025 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://github.com/milejko/kuick-framework?tab=MIT-1-ov-file#readme New BSD License
 */

namespace Kuick\Framework\Listeners;

use Kuick\Framework\Events\ResponseCreatedEvent;
use Kuick\Framework\Events\ResponseEmittedEvent;
use Kuick\Http\Server\ResponseEmitter;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

final class ResponseEmittingListener
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ResponseCreatedEvent $responseCreatedEvent): void
    {
        $response = $responseCreatedEvent->getResponse();
        // emmit response
        (new ResponseEmitter())->emitResponse($response);
        $this->logger->info('Response emitted succesfully:', [
            'code' => $response->getStatusCode(),
            'content-type' => $response->getHeaderLine('Content-Type'),
            'body-size' => $response->getBody()->getSize(),
        ]);
        $this->eventDispatcher->dispatch(new ResponseEmittedEvent($response));
    }
}
