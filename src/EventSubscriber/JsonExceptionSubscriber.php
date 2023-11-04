<?php 

namespace App\EventSubscriber ;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class JsonExceptionSubscriber
{

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        $response = new JsonResponse();
        $response->setContent((json_encode(
            [
                
                'message' => $exception->getMessage()
            ])
            ));
            

            if($exception instanceof HttpExceptionInterface){
                $response->setStatusCode($exception->getStatusCode());
                $response->headers->replace($exception->getHeaders());
            }
            else{
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $event->setResponse($response);
    }


   
}