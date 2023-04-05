<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractController
{
    protected function jsonSuccess(array $data = []): JsonResponse
    {
        $data['success'] = true;

        return new JsonResponse(
            $data,
            Response::HTTP_OK
        );
    }

    protected function jsonError(array $data = []): JsonResponse
    {
        $data['success'] = false;

        return new JsonResponse(
            $data,
            Response::HTTP_NOT_FOUND
        );
    }
}
