<?php
declare(strict_types=1);

namespace App\Controller\api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\JwtService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class JwtController extends AbstractController
{
    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifyUser($token, JwtService $jwt,
                               UserRepository $userRepository,
                               EntityManagerInterface $entityManager
    ): Response
    {
        $message = "Le lien n'est pas valide";
        //check that the token valid and has not been modified
        if($jwt->isValid($token) && !$jwt->isExpired($token) &&
            $jwt->checkToken($token, $this->getParameter('app.jwtsecret')))
        {
            $payload = $jwt->getPayload($token);

            //Recovering the user token
            $user = $userRepository->find($payload['user_id']);

            //Check that the User exists and has not yet actived his account
            if($user && !$user->isVerified()){
                $user->setVerified(true);
                $entityManager->flush($user);

                $message = '✅ Félicitations ! Votre email a été confirmé avec succès';
               // return new JsonResponse(['message' => 'Verification réussie'], Response::HTTP_OK);
                return $this->render('email/emailConfirmed.html.twig', [
                    'message' => $message
                ]);
            }
        }
        return $this->render('email/emailConfirmed.html.twig', [
            'message' => $message
        ]);
    }

    #[Route('/resend_verif/{email}', name: 'resend_verification', requirements: ['email' => '.+'])]
    public function resendVerif(SendMailService $sendMailService,
                                JwtService $jwt,
                                 string    $email,
                                EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if(!$user){
            return new JsonResponse("Aucun utilisateur trouvé pour cette email: ". $email);
        }

        if ($user->isVerified()) {
            return new JsonResponse(['warning' => 'Cet utilisateur est déjà activé'], Response::HTTP_BAD_REQUEST);
        }

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        // On crée le Payload
        $payload = [
            'user_id' => $user->getId(),
        ];

        // On génère le token
        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

        $sendMailService->send(
            'no-reply@educare.fr',
            $user->getEmail(),
            'Activation de votre compte sur le site EduCare',
            'register',
            compact('user', 'token')
        );
        return new JsonResponse(['success' => 'Email de vérification envoyé'], Response::HTTP_OK);
    }
}