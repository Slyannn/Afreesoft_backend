<?php

namespace App\Controller\api;

use App\Entity\Address;
use App\Entity\Message;
use App\Entity\Need;
use App\Entity\Organism;
use App\Entity\OrganismAdmin;
use App\Entity\User;
use App\Form\OrganismAdminType;
use App\Repository\OrganismAdminRepository;
use App\Repository\OrganismRepository;
use App\Service\JwtService;
use App\Service\SendMailService;
use App\Service\UploadFile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/api/organism')]
class OrganismController extends AbstractController
{
    private Serializer $serializer;

    //construct the controller Autowiring Serializer and NeedRepository
    public function __construct()
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    #[Route('/signup', name: 'app_organism_signup', methods: ['GET', 'POST'])]
    public function signup(
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface      $entityManager,
        UploadFile                  $uploadFile,
        SluggerInterface            $slugger,
        JwtService                  $jwt,
        SendMailService             $sendMailService
    ): JsonResponse
    {
        $data = $request->request->all();

        $organismAdmin = new OrganismAdmin();
        $form = $this->createForm(OrganismAdminType::class, $organismAdmin);

        $form->submit($data);
        $organismAdmin->setName($data['name']);
        $organismAdmin->setOrganismEmail($data['organismEmail']);
        $organismAdmin->setPhone($data['phone']);
        $organismAdmin->setDescription($data['description']);

        $logoFile = $request->files->get('logo');
        if ($logoFile) {
            $logoFileName = $uploadFile->uploadedFilename($logoFile, $slugger, 'logo');
            $organismAdmin->setLogo($logoFileName);
        }

        $address = new Address();
        $address->setStreet($data['address']['street']);
        $address->setCity($data['address']['city']);
        $address->setZipcode($data['address']['zipcode']);
        $address->setCountry($data['address']['country']);

        $entityManager->persist($address);
        $organismAdmin->setAddress($address);

        //add all services
        foreach ($data['services'] as $service) {
            //find need with id
            /** @var Need $need */
            $need = $entityManager->getRepository(Need::class)->find($service['id']);
            if ($need !== null) {
                //add need to organism
                $need->addOrganismAdmin($organismAdmin);
                $organismAdmin->addService($need);
            } else {
                return new JsonResponse(['message' => 'Service not found'], Response::HTTP_NOT_FOUND);
            }
        }

        $profile = new Organism();
        $certificateFile = $request->files->get('profile')['certificate'];
        if ($certificateFile) {
            $certificateFileName = $uploadFile->uploadedFilename($certificateFile, $slugger, 'certificate');
            $profile->setCertificate($certificateFileName);
        }

        $user = new User();
        $user->setEmail($data['profile']['user']['email']);
        $user->setPassword($userPasswordHasher->hashPassword($user, $data['profile']['user']['password']));
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
        if ($existingUser) {
            return new JsonResponse(['message' => 'Email is already registered'], Response::HTTP_CONFLICT);
        }
        $user->setRoles(['ROLE_ORGANISM']);

        $entityManager->persist($user);
        $entityManager->persist($profile);

        $entityManager->persist($organismAdmin);

        //a la fin avant le flush
        $address->addOrganismAdmin($organismAdmin);
        $profile->setOrganismAdmin($organismAdmin);
        $user->setOrganism($profile);
        $entityManager->flush();

        //genereted the jwt
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        $payload =[
            'user_id' => $user->getId()
        ];

        $token = $jwt->generate($header,$payload, $this->getParameter('app.jwtsecret'));

        //send email
        $sendMailService->send(
            'no-reply@educare.fr',
            $user->getEmail(),
            'Activation de votre compte sur le site EduCare',
            'register',
            compact('user', 'token')
        );
        return new JsonResponse(['message' => 'User created'], Response::HTTP_CREATED);
    }

    //update Organism
    #[Route('/update/{id}', name: 'app_organism_update', methods: ['PUT'])]
    public function updateOrganism(
        OrganismAdmin $organismAdmin,
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher,
        UploadFile $uploadFile,
        SluggerInterface $slugger
    ): JsonResponse
    {
        try {
            // Get updated data from request
            $data = $request->request->all();

            // Handle form validation (essential for security)
            $form = $this->createForm(OrganismAdminType::class, $organismAdmin);
            $form->submit($data);

            if ($form->isSubmitted() && $form->isValid()) {
                // Update organism details
                $organismAdmin->setName($data['name']);
                $organismAdmin->setOrganismEmail($data['organismEmail']);
                $organismAdmin->setPhone($data['phone']);
                $organismAdmin->setDescription($data['description']);

                // Handle logo update
                $logoFile = $request->files->get('logo');
                if ($logoFile) {
                    $logoFileName = $uploadFile->uploadedFilename($logoFile, $slugger, 'logo');
                    $organismAdmin->setLogo($logoFileName);
                }

                // Update address
                if (isset($data['address'])) {
                    $address = $organismAdmin->getAddress();
                    $address?->setStreet($data['address']['street'] );
                    $address?->setCity($data['address']['city'] );
                    $address?->setZipCode($data['address']['zipCode'] );
                    $address?->setCountry($data['address']['country'] );
                }

                // Update services
                $organismAdmin->getServices()->clear();
                foreach ($data['services'] as $service) {
                    $need = $entityManager->getRepository(Need::class)->find($service['id']);
                    if ($need) {
                        $need->addOrganismAdmin($organismAdmin);
                        $organismAdmin->addService($need);
                    } else {
                        throw new NotFoundHttpException('Service not found');
                    }
                }

                // Update profile and user
                $profile = $organismAdmin->getProfile();
                if(isset($data['profile']['certificate'])){
                    $profile?->setCertificate($data['profile']['certificate']);
                }

                if (isset($data['profile']['user']['password'])) {
                    $user = $profile?->getUser();
                    $user?->setPassword($userPasswordHasher->hashPassword($user, $data['profile']['user']['password']));
                }

                // Persist changes and flush
                $entityManager->flush();

                return new JsonResponse(['message' => 'Organism updated'], Response::HTTP_OK);
            }

            return new JsonResponse(['errors' => $form->getErrors(true)], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Error updating organism'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    //Get All Organisms
    #[Route('/all', name: 'app_organism_get_all', methods: ['GET'])]
    public function getAllOrganisms(OrganismAdminRepository $organismAdminRepository): JsonResponse
    {
        $organisms = $organismAdminRepository->findAll();
        $jsonContent = $this->serializer->serialize($organisms, 'json',
            [AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
                return $object->getId();
            },
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['certificate', 'password', 'roles', 'organism', 'organisms', 'organismAdmins', 'student', 'students', 'organismAdmin']
            ]);

        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }

    #[Route('/sendMessage', name: 'app_send_message', methods: ['POST'])]
    public function sendMessage(
        SendMailService             $sendMailService,
        Request                     $request,
        EntityManagerInterface      $entityManager,
    )
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $message = new Message();
        $message->setSender($data['sender']);
        $message->setReceiver($data['receiver']);

        $message->setSubject($data['subject']);
        $message->setContent($data['content']);

        /** @var User $existingUser */
        $existingUser= $entityManager->getRepository(User::class)->findOneBy(['email' => $message->getSender()]);

       // $organismAdmin = $entityManager->getRepository(OrganismAdmin::class)->findOneBy(['organismEmail' => $message->getSender()]);

        $entityManager->persist($message);
        $entityManager->flush();

        //send email to organism
        $sendMailService->send(
            $message->getSender(),
            $message->getReceiver(),
            $message->getSubject(),
            'contactOrganism',
            [
                'content' => $message->getContent(),
                'from' => $existingUser,
            ]
        );

        //copy message
        $sendMailService->send(
            'no-reply@educare.fr',
            $message->getSender(),
            "Confirmation d'envoie de message",
            'copyMessage',
            [
                'content' => $message->getContent(),
                'to' => $message->getReceiver(),
                'from' => $existingUser
            ]
        );

        return new JsonResponse(['message' => 'Votre message a bien été envoyé'], Response::HTTP_OK);
    }

}
