<?php declare(strict_types=1);

namespace Gos\Bundle\WebSocketBundle\Pusher\Wamp;

use Gos\Bundle\WebSocketBundle\Pusher\AbstractPusher;
use Gos\Bundle\WebSocketBundle\Pusher\Message;
use Gos\Bundle\WebSocketBundle\Router\WampRouter;
use Gos\Component\WebSocketClient\Wamp\Client;
use Symfony\Component\Serializer\SerializerInterface;

final class WampPusher extends AbstractPusher
{
    /**
     * @var Client
     */
    private $connection;

    /**
     * @var WampConnectionFactoryInterface
     */
    private $connectionFactory;

    public function __construct(
        WampRouter $router,
        SerializerInterface $serializer,
        WampConnectionFactoryInterface $connectionFactory
    ) {
        parent::__construct($router, $serializer);

        $this->connectionFactory = $connectionFactory;
    }

    protected function doPush(Message $message, array $context): void
    {
        if (false === $this->isConnected()) {
            $this->connection = $this->connectionFactory->createConnection();
            $this->connection->connect();
            $this->setConnected();
        }

        $this->connection->publish($message->getTopic(), $this->serializer->serialize($message->getData(), 'json'));
    }

    public function close(): void
    {
        if (false === $this->isConnected()) {
            return;
        }

        $this->connection->disconnect();
    }
}
