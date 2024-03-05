<?php

namespace App\Controller\api;

use App\Entity\Address;
use App\Entity\Need;
use App\Entity\Organism;
use App\Entity\OrganismAdmin;
use App\Entity\User;
use App\Form\OrganismAdminType;
use App\Repository\OrganismAdminRepository;
use App\Repository\OrganismRepository;
use App\Service\UploadFile;
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
        SluggerInterface            $slugger
    ): JsonResponse
    {
        $data = $request->request->all();

        //New student
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


        return new JsonResponse(['message' => 'User created'], Response::HTTP_CREATED);
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

    //get all OrganismAdmin bei all selected needs

    /**
     * @throws \JsonException
     */
    #[Route('/filter', name: 'app_organism_get_by_needs', methods: ['POST', 'GET'])]
    public function getOrganismByNeeds(Request $request, OrganismAdminRepository $organismAdminRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $needs = $data['services'];
        $organismAdmins = $organismAdminRepository->findByServices($needs);

        $jsonContent = $this->serializer->serialize($organismAdmins, 'json',
            [AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
                return $object->getId();
            },
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['certificate', 'password', 'roles', 'organism', 'organisms', 'organismAdmins', 'student', 'students', 'organismAdmin']
            ]);

        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }


}
