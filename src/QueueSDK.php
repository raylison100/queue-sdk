<?php

declare(strict_types=1);

/**
 * Queue SDK - PHP Event Messaging Library
 *
 * Um SDK PHP robusto para consumo de eventos de mensageria
 * com suporte a Apache Kafka, Amazon SQS e outras implementações.
 *
 * @package QueueSDK
 * @version 1.0.0
 * @author  Your Name
 * @license MIT
 */

// Autoload principais classes do SDK
require_once __DIR__ . '/Contracts/QueueInterface.php';
require_once __DIR__ . '/Contracts/EventHandleInterface.php';
require_once __DIR__ . '/DTOs/ConsumerMessageQueueDTO.php';
require_once __DIR__ . '/DTOs/PublishMessageQueueDTO.php';
require_once __DIR__ . '/Queues/AbstractQueue.php';
require_once __DIR__ . '/Strategies/AbstractEventStrategy.php';
require_once __DIR__ . '/Factories/EventStrategyFactory.php';
require_once __DIR__ . '/Consumers/AbstractQueueConsumer.php';

// Implementações disponíveis
require_once __DIR__ . '/Queues/SqsQueueSDK.php';
require_once __DIR__ . '/Queues/KafkaQueue.php';
require_once __DIR__ . '/Strategies/ExampleEventStrategy.php';
