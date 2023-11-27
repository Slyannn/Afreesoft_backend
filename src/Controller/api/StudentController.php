<?php

namespace App\Controller\api;

use App\Entity\Address;
use App\Entity\Need;
use App\Entity\Student;
use App\Entity\User;
use App\Repository\NeedRepository;
use App\Service\SignupUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/student')]
class StudentController extends AbstractController
{


    /**
     * @throws \JsonException
     */
    #[Route('/signup', name: 'app_student_signup', methods: ['GET', 'POST'])]
    public function signup(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $entityManager): JsonResponse
    {
        //Extract data from the request
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        //New student
        $student = new Student();
        //Set student data
        $student->setFirstname($data['firstname']);
        $student->setLastname($data['lastname']);
        $student->setUniversity($data['university']);

        //New address
        $address = new Address();
        $user = new User();
        return (new SignupUser())->signupUser($data, $student, 'ROLE_STUDENT', $user, $address, $entityManager, $userPasswordHasher);
    }

    //get Student by email
    #[Route('/{email}', name: 'app_student_get', requirements: ['email' => '\S+@\S+\.\S+'], methods: ['GET'])]
    public function getStudent(string $email, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return new JsonResponse(['message' => 'Student not found'], Response::HTTP_NOT_FOUND);
        }
        //dataProvider
        /** @var User $user */
        $user = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'password' => $user->getPassword(),
            'address' => [
                'id' => $user->getAddress()?->getId(),
                'street' => $user->getAddress()?->getStreet(),
                'city' => $user->getAddress()?->getCity(),
                'zipCode' => $user->getAddress()?->getZipCode(),
                'country' => $user->getAddress()?->getCountry(),
            ],
            'student' => [
                'id' => $user->getStudent()?->getId(),
                'firstname' => $user->getStudent()?->getFirstname(),
                'lastname' => $user->getStudent()?->getLastname(),
                'university' => $user->getStudent()?->getUniversity(),
                'enable' => $user->getStudent()?->isEnable(),
                'createdAt' =>$user->getStudent()?->getCreateAt(),
                'needs' => array_map(function ($need) {
                    return [
                        'id' => $need->getId(),
                        'name' => $need->getName(),
                    ];
                }, $user->getStudent()?->getNeeds()->toArray())
            ],

        ];
        return new JsonResponse($user, Response::HTTP_OK);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/{id}/need', name: 'app_student_need_add', methods: ['GET', 'POST'])]
    public function addNedd(
        Request $request,
        Student $student,
        EntityManagerInterface $entityManager,
        NeedRepository $needRepository
    ): JsonResponse
    {
        //Student with $id add some need
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        /** @var Need $need */
        $need = $needRepository->find($data['need']['id']);

        $student->addNeed($need);
        $need->addStudent($student);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Need added'], Response::HTTP_CREATED);
    }

    //remove need
    #[Route('/{id}/need/{need}', name: 'app_student_need_remove', methods: ['DELETE'])]
    public function removeNeed(
        Student $student,
        Need $need,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $student->removeNeed($need);
        $need->removeStudent($student);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Need removed'], Response::HTTP_NO_CONTENT);
    }



}
