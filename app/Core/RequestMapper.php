<?php

namespace SampleChat\Core;


use GuzzleHttp\Psr7\BufferStream;
use GuzzleHttp\Psr7\Response;
use JsonMapper;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
    public function requestToDto(ServerRequestInterface $request, $template)
    {
        if ($template === null) {
            return null;
        }

        $json = json_decode($request->getBody());
        foreach ($request->getQueryParams() as $paramName => $param) {
            $camelParamName = $this->camelize($paramName);
            if (property_exists($template, $camelParamName)) {
                $template->$camelParamName = $param;
            }
        }

        if ($json) {
            return $this->jsonMapper->map($json, $template);
        }
        return $template;
    }

    public function dtoToResponse($dto): ResponseInterface
    {
        $response = new Response();
        $stream = new BufferStream();
        $stream->write(json_encode($dto));

        return $response->withBody($stream)->withAddedHeader('Content-Type', 'application/json');
    }

    // https://stackoverflow.com/a/28731633/319229
    private function camelize($word)
    {
        $word = preg_replace_callback(
            "/(^|_)([a-z])/",
            function ($m) {
                return strtoupper("$m[2]");
            },
            $word
        );
        return lcfirst($word);
    }
}
