<?php

namespace App\Controller\api;

use App\Repository\NeedRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route('/api/needs')]
class NeedApiController extends AbstractController
{
    private Serializer $serializer;
    //construct the controller Autowiring Serializer and NeedRepository
    public function __construct(
        private readonly NeedRepository $needRepository
    )
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    //get All needs
    #[Route('/', name: 'app_need_all', methods: ['GET'])]
    public function getAllNeeds(): JsonResponse
    {
        $needs = $this->needRepository->findAll();

        $jsonContent = $this->serializer->serialize($needs, 'json', [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
                return $object->getId();
            },
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['enable', 'createAt', 'user', 'students', 'organismAdmins' ]
           ] );

        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }

}