<?php

namespace App\Controller\api;

use App\Entity\Review;
use App\Repository\OrganismRepository;
use App\Repository\ReviewRepository;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route('/api/reviews')]
class ReviewController extends AbstractController
{
    private Serializer $serializer;
    //construct the controller Autowiring Serializer and NeedRepository
    public function __construct(
        private readonly ReviewRepository $reviewRepository
    )
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }


    #[Route('/add', name: 'app_add_review', methods: ['POST','GET'])]
    public function index(
        Request $request,
        StudentRepository $studentRepository,
        OrganismRepository $organismRepository,
        EntityManagerInterface      $entityManager,
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $author = $studentRepository->find($data['author_id']);
        $organism = $organismRepository->find($data['organism_id']);

        $review = new Review();
        $review->setContent($data['content']);
        $review->setTitle($data['title']);
        $review->setAuthor($author);
        $review->setNote($data['note']);
        $review->setOrganism($organism);

        $author?->addReview($review);
        $organism?->addReview($review);

        $entityManager->persist($review);
        $entityManager->flush();

        return new JsonResponse(['message' => 'review posted'], Response::HTTP_CREATED);
    }

    #[Route('/', name: 'app_all_review', methods: ['GET'])]
    public function reviews()
    {
        $reviews = $this->reviewRepository->findAll();

        $jsonContent = $this->serializer->serialize($reviews, 'json', [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
                return $object->getId();
            },
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['enable', 'createAt', 'user', 'students', 'organismAdmins' ]
        ] );

        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }



}
