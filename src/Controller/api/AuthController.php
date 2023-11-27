<?php

namespace App\Controller\api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/auth/student')]
class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_auth_login', methods: ['GET', 'POST'])]
    public function login(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        //Extract data from the request
        $data = json_decode($request->getContent(), true);
        $role = 'student';
        //New student
        $user = new User();
        //Set student data
        $user->setEmail($data['email']);
        
        $user->setPassword($userPasswordHasher->hashPassword($user, $data['password']));
        $existingStudent = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
        if (!$existingStudent) {
            return new JsonResponse(['message' => 'Email is not registered'], JsonResponse::HTTP_CONFLICT);
        }
        if($existingStudent->getRoles()[0] === 'ROLE_ORGANISM'){
            $role = 'organism';
        }
        if ($userPasswordHasher->isPasswordValid($existingStudent, $data['password'])) {
            return new JsonResponse(['message' => $role.' logged in successfully'], JsonResponse::HTTP_CREATED);
        }
        return new JsonResponse(['message' => 'Invalid credentials'], JsonResponse::HTTP_CONFLICT);
    }


}
