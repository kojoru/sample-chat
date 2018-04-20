<?php

namespace SampleChat\Core;


use GuzzleHttp\Psr7\BufferStream;
use GuzzleHttp\Psr7\Response;
use JsonMapper;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RequestMapper
{
    /* @var JsonMapper */
    private $jsonMapper;

    function __construct(JsonMapper $jsonMapper)
    {
        $this->jsonMapper = $jsonMapper;
    }

    /**
     * @param RequestInterface $request
     * @param $template
     * @return object
     * @throws \JsonMapper_Exception
     */
    public function requestToDto(RequestInterface $request, $template)
    {
        if ($template === null) {
            return null;
        }

        $json = json_decode($request->getBody());
        return $this->jsonMapper->map($json, $template);

    }

    public function dtoToResponse($dto): ResponseInterface
    {
        $response = new Response();
        $stream = new BufferStream();
        $stream->write(json_encode($dto));

        return $response->withBody($stream)->withAddedHeader('Content-Type', 'application/json');
    }
}
