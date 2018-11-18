<?php

namespace Phpactor\Extension\CompletionRpc\Handler;

use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Core\TypedCompletorRegistry;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\ReturnResponse;

class CompleteHandler implements Handler
{
    const NAME = 'complete';
    const PARAM_SOURCE = 'source';
    const PARAM_OFFSET = 'offset';
    const PARAM_TYPE = 'type';

    /**
     * @var TypedCompletorRegistry
     */
    private $registry;

    public function __construct(TypedCompletorRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver)
    {
        $resolver->setRequired([
            self::PARAM_SOURCE,
            self::PARAM_OFFSET,
        ]);

        $resolver->setDefaults([
            self::PARAM_TYPE => 'php'
        ]);
    }

    public function handle(array $arguments)
    {
        $suggestions = $this->registry->completorForType($arguments['type'])->complete(
            $arguments[self::PARAM_SOURCE],
            $arguments[self::PARAM_OFFSET]
        );

        $suggestions = array_map(function (Suggestion $suggestion) {
            return $suggestion->toArray();
        }, iterator_to_array($suggestions));

        return ReturnResponse::fromValue([
            'suggestions' => $suggestions,
            'issues' => [],
        ]);
    }
}
