<?php

namespace App\Controller\api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\JwtService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


#[Route('/api/auth')]
class AuthController extends AbstractController
{
    private Serializer $serializer;

    //construct the controller Autowiring Serializer and NeedRepository
    public function __construct()
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/login', name: 'app_auth_login', methods: ['GET', 'POST'])]
    public function login(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        JwtService $jwt,

    ): JsonResponse
    {
        //Extract data from the request
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        //New student
        $user = new User();
        //Set student data
        $user->setEmail($data['email']);

        $user->setPassword($userPasswordHasher->hashPassword($user, $data['password']));
        $existingStudent = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
        if (!$existingStudent) {
            return new JsonResponse(['message' => 'Email is not registered'], Response::HTTP_CONFLICT);
        }

        if ($userPasswordHasher->isPasswordValid($existingStudent, $data['password'])) {
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];
            $payload =[
                'user_id' => $existingStudent->getId()
            ];
            $token = $jwt->generate($header,$payload, $this->getParameter('app.jwtsecret'));

            return new JsonResponse(compact('token'), Response::HTTP_OK);
        }
        return new JsonResponse(['message' => 'Invalid credentials'], Response::HTTP_CONFLICT);
    }

    #[Route('/currentUser/{token}', name: 'current_user', methods: ['GET'])]
    public function currentUser($token,
                                 UserRepository $userRepository,
                                 JwtService $jwt,

    ): JsonResponse
    {
        if($jwt->isValid($token) && !$jwt->isExpired($token) &&
                $jwt->checkToken($token, $this->getParameter('app.jwtsecret'))) {
            $payload = $jwt->getPayload($token);
            //Recovering the user token
            $user = $userRepository->find($payload['user_id']);

            if ($user) {
                if ($user->getRoles()[0] === 'ROLE_ORGANISM') {
                    $jsonContent = $this->serializer->serialize($user, 'json', [
                        AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
                            return $object->getId();
                        },
                        AbstractNormalizer::IGNORED_ATTRIBUTES => ['student', 'students', 'profile', 'organismAdmins', 'organisms', 'userIdentifier', 'user']
                    ]);

                } else {
                    //Serialize $user
                    $jsonContent = $this->serializer->serialize($user, 'json', [
                        AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
                            return $object->getId();
                        },
                        AbstractNormalizer::IGNORED_ATTRIBUTES => ['organism', 'createAt', 'organismAdmins', 'students', 'organisms', 'user']
                    ]);
                }

                return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
            }
            return new JsonResponse(['error' => 'user not found'], Response::HTTP_NOT_FOUND);
        }
        // If the user is not authenticated, return an error response
        return new JsonResponse(['error' => 'token is null'], Response::HTTP_UNAUTHORIZED);
    }

}
